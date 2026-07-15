<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\Jabatan;
use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use App\Supports\ApiHC;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DataSyncController extends Controller
{
    public function index()
    {
        abort_unless(Gate::allows('data_sync_access'), 403);

        $users = User::query()
            ->select('id', 'name', 'nip', 'email')
            ->orderBy('name')
            ->get();

        return view('master.data-sync.index', compact('users'));
    }

    public function sync(Request $request): RedirectResponse
    {
        abort_unless(Gate::allows('data_sync_access'), 403);

        $validated = $request->validate([
            'sync_types' => ['required', 'array', 'min:1'],
            'sync_types.*' => ['in:division,project,user'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $syncTypes = collect($validated['sync_types']);
        $summary = [
            'division' => null,
            'project' => null,
            'user' => null,
        ];

        if ($syncTypes->contains('division')) {
            try {
                Unit::sync();
                $summary['division'] = ['success' => true];
            } catch (\Throwable $e) {
                $summary['division'] = [
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }

        if ($syncTypes->contains('project')) {
            try {
                $projectController = app(ProjectController::class);
                $response = $projectController->syncWika($request);
                $projectData = $this->decodeJsonResponse($response);

                $summary['project'] = [
                    'success' => (bool) ($projectData['success'] ?? false),
                    'count_updated' => (int) ($projectData['count_updated'] ?? 0),
                    'count_failed' => (int) ($projectData['count_failed'] ?? 0),
                ];
            } catch (\Throwable $e) {
                $summary['project'] = [
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }

        if ($syncTypes->contains('user')) {
            $selectedUserIds = $validated['user_ids'] ?? [];

            if (empty($selectedUserIds)) {
                return back()->withErrors([
                    'user_ids' => 'Pilih minimal 1 user untuk sinkronisasi data user.',
                ])->withInput();
            }

            $summary['user'] = $this->syncSelectedUsers($selectedUserIds);
        }

        return redirect()
            ->route('data-sync.index')
            ->with('success', 'Sinkronisasi selesai diproses.')
            ->with('sync_summary', $summary);
    }

    private function syncSelectedUsers(array $userIds): array
    {
        $api = new ApiHC();

        $users = User::query()
            ->whereIn('id', $userIds)
            ->get();

        $result = [
            'success' => true,
            'total' => $users->count(),
            'updated' => 0,
            'failed' => 0,
            'failed_users' => [],
        ];

        foreach ($users as $user) {
            try {
                if (empty($user->nip)) {
                    throw new \RuntimeException('NIP kosong, tidak bisa sinkronisasi ke API HC.');
                }

                $response = $api->apiRequest('GET', '/', [
                    'method' => 'get_pegawai',
                    'nip' => $user->nip,
                ]);

                $remoteUser = $response['data'][0] ?? null;
                if (!$remoteUser) {
                    throw new \RuntimeException('Data user tidak ditemukan di API HC.');
                }

                $resolvedUnit = null;
                $costCenterParent = $remoteUser['cost_center_parent'] ?? null;
                if (!empty($costCenterParent)) {
                    $resolvedUnit = Unit::where('cost_center', $costCenterParent)->first();
                }

                if (!$resolvedUnit) {
                    throw new \RuntimeException("Unit dengan cost center parent '{$costCenterParent}' tidak ditemukan.");
                }

                $jabatan = null;
                $jabatanCode = $remoteUser['kd_jabatan'] ?? null;
                if ($jabatanCode) {
                    $jabatan = Jabatan::where('code', $jabatanCode)->first();
                }

                $costCenter = $remoteUser['cost_center'] ?? null;
                $costCentersArray = [];
                if ($costCenter) {
                    $costCentersArray = is_array($costCenter)
                        ? $costCenter
                        : array_filter(array_map('trim', explode(',', (string) $costCenter)));
                }

                $resolvedProjectIds = [];
                if (!empty($costCentersArray)) {
                    $resolvedProjectIds = Project::query()
                        ->whereIn('profit_center', $costCentersArray)
                        ->pluck('id')
                        ->toArray();
                }

                $oldProjectIds = $this->normalizeProjectIds(
                    $user->projects()->pluck('projects.id')->toArray()
                );
                $newProjectIds = $this->normalizeProjectIds($resolvedProjectIds);

                DB::transaction(function () use ($user, $resolvedUnit, $jabatan, $resolvedProjectIds): void {
                    $user->update([
                        'unit_id' => $resolvedUnit->id,
                        'unit_type_id' => $resolvedUnit->unit_type_id,
                        'parent_id' => $resolvedUnit->parent_id,
                        'jabatan_id' => $jabatan?->id,
                    ]);

                    $user->projects()->sync($resolvedProjectIds);
                });

                $this->writeUserProjectsAudit($user, $oldProjectIds, $newProjectIds);

                $result['updated']++;
            } catch (\Throwable $e) {
                $result['failed']++;
                $result['success'] = false;
                $result['failed_users'][] = [
                    'name' => $user->name,
                    'nip' => $user->nip,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $result;
    }

    private function decodeJsonResponse(JsonResponse $response): array
    {
        $payload = $response->getData(true);

        return is_array($payload) ? $payload : [];
    }

    private function normalizeProjectIds($projectIds): array
    {
        $values = is_array($projectIds) ? $projectIds : [];

        return collect($values)
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function writeUserProjectsAudit(User $user, array $oldProjectIds, array $newProjectIds): void
    {
        if ($oldProjectIds === $newProjectIds) {
            return;
        }

        $authUser = Auth::user();
        $ipAddress = request()->ip();
        $ipAddress = is_string($ipAddress) ? trim($ipAddress) : null;
        $ipAddress = filter_var($ipAddress, FILTER_VALIDATE_IP) ? $ipAddress : null;

        Audit::query()->create([
            'user_type' => $authUser ? get_class($authUser) : null,
            'user_id' => $authUser?->id,
            'event' => 'updated',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'old_values' => json_encode(['user_projects' => $oldProjectIds], JSON_UNESCAPED_UNICODE),
            'new_values' => json_encode(['user_projects' => $newProjectIds], JSON_UNESCAPED_UNICODE),
            'url' => request()->fullUrl(),
            'ip_address' => $ipAddress,
            'user_agent' => request()->userAgent(),
            'unit_id' => $user->unit_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
