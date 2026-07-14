<?php

namespace App\Models;

use App\Supports\ApiHC;
use App\Supports\ApiWika;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class Unit extends Model implements AuditableContract
{
    use Auditable, HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $table = 'units';

    protected $fillable = [
        'unit_api_id',
        'unit_type_id',
        'name',
        'parent_id',
        'unit_deskripsi',
        'persubarea_sap',
        'persubarea_deskripsi', 
        'persubarea_type',
        'company_sap',
        'company_deskripsi',
        'cost_center',
        'cost_center_deskripsi',
        'cost_center_abbrevation',
        'cost_center_type',
        'cost_center_parent',
        'cost_center_parent_deskripsi',
        'unit_mr',
        'valid_from',
        'valid_to',
        'status',
    ];

    protected $casts = [
        'valid_from' => 'date:Y-m-d',
        'valid_to' => 'date:Y-m-d',
    ];

    public const UNIT_TYPE_DIVISION = 1;
    public const UNIT_TYPE_DEPARTMENT = 2;
    public const UNIT_TYPE_PROJECT = 3;

    public static function sync() {
        $apiHC = new ApiHC();
        $units = $apiHC->apiRequest('GET', '/', [
            'method' => 'get_assign_ccplace',
            'page' => 1,
            'limit' => 999999,
            'key' => 'GZrmL5TH',
            'company_sap' => 'A000',
            // 'cost_center_type' => 'Department',
            // 'persubarea_type' => 'Divisi Operasi',
            // 'persubarea_type' => 'Divisi Fungsi',
        ]);

        Log::channel('unit_sync')->info('Unit HC API response fetched', [
            'total' => is_array($units['data'] ?? null) ? count($units['data']) : 0,
            'message' => $units['message'] ?? null,
            'sample_first' => $units['data'][0] ?? null,
        ]);
        Log::channel('unit_sync')->debug('Unit HC API raw data', [
            'data' => $units['data'] ?? [],
        ]);

        if (!($units['data'] ?? [])) {
            throw new \Exception('Unit List API return empty data. Message: ' . ($units['message'] ?? 'No message'));
        }

        $unitTypes = [
            'Divisi' => self::UNIT_TYPE_DIVISION,
            'Department' => self::UNIT_TYPE_DEPARTMENT,
            'Project' => self::UNIT_TYPE_PROJECT, // ambil dari api.wika
        ];

        $divisiUnits = [];

        $unitData = $units['data'];

        $unitData = collect($unitData)->filter(function ($unit) {
            $costCenterParentDesc = trim((string) ($unit['cost_center_parent_deskripsi'] ?? ''));

            return $unit['company_sap'] == 'A000'
                && $unit['cost_center_parent'] != ""
                && $costCenterParentDesc !== '';
        })->keyBy('cost_center_parent')->values()->toArray();
        dd($unitData);

        $today = now()->toDateString();

        foreach ($unitData as $unit) {
            $cost_center_parent = $unit['cost_center_parent'];
            $unit_name = $unit['cost_center_parent_deskripsi'];

            // Handle cost_center_parent = 0 for Internal Audit
            if ($cost_center_parent === '0') {
                $cost_center_parent = $unit['cost_center'];
                $unit_name = $unit['cost_center_deskripsi'];
            }

            $validTo = null;
            if (!empty($unit['valid_to'])) {
                try {
                    $validTo = \Carbon\Carbon::parse($unit['valid_to'])->toDateString();
                } catch (\Throwable $e) {
                    $validTo = null;
                }
            }

            // Hanya sinkronkan unit yang masih valid berdasarkan valid_to
            if ($validTo && $validTo < $today) {
                continue;
            }

            $validFrom = null;
            if (!empty($unit['valid_from'])) {
                try {
                    $validFrom = \Carbon\Carbon::parse($unit['valid_from'])->toDateString();
                } catch (\Throwable $e) {
                    $validFrom = null;
                }
            }

            $payload = [
                // 'unit_api_id' => $unit['unit_id'],
                'name' => $unit_name,
                'unit_type_id' => self::UNIT_TYPE_DIVISION,
                'parent_id' => 0,
                // 'unit_deskripsi' => $unit['unit_deskripsi'] ?? null,
                // 'persubarea_sap' => $unit['persubarea_sap'] ?? null,
                // 'persubarea_deskripsi' => $unit['persubarea_deskripsi'] ?? null,
                'persubarea_type' => $unit['persubarea_type'] ?? null,
                'company_sap' => $unit['company_sap'] ?? null,
                'company_deskripsi' => $unit['company_deskripsi'] ?? null,
                // 'cost_center' => $unit['cost_center'] ?? null,
                // 'cost_center_deskripsi' => $unit['cost_center_deskripsi'] ?? null,
                // 'cost_center_abbrevation' => $unit['cost_center_abbrevation'] ?? null,
                'cost_center_type' => $unit['cost_center_type'] ?? null,
                // 'cost_center_parent' => $unit['cost_center_parent'] ?? null,
                // 'cost_center_parent_deskripsi' => $unit['cost_center_parent_deskripsi'] ?? null,
                'valid_from' => $validFrom,
                'valid_to' => $validTo,
                'status' => $unit['status'] ?? null,
            ];

            $existingUnit = Unit::query()
                ->where('cost_center', $cost_center_parent)
                ->whereNull('deleted_at')
                ->where(function ($query) use ($today) {
                    $query->whereNull('valid_to')
                        ->orWhereDate('valid_to', '>=', $today);
                })
                ->first();

            if ($existingUnit) {
                $existingUnit->fill($payload)->save();
                $syncedUnit = $existingUnit;
            } else {
                $syncedUnit = Unit::create(array_merge(
                    ['cost_center' => $cost_center_parent],
                    $payload
                ));
            }

            $divisiUnits[$syncedUnit->cost_center] = $syncedUnit;
        }

        /*
        $projectDatas = (new ApiWika())->getProjects();
        foreach ($projectDatas as $projectData) {
            $divisiUnit = $divisiUnits[$projectData['divisisap']] ?? null;
            $project = Project::updateOrCreate([
                'project_code' => $projectData['kode_spk'],
            ], [
                'project_name' => $projectData['nama_spk_full'],
                'type' => Project::TYPE_HAS_RKB_RKN,
                'project_status' => 1,
                'nk' => 0,
                'meta' => $projectData,
            ]);

            ProjectPeriodeList::updateOrCreate([
                'project_id' => $project->id,
                'periode_id' => null,
            ], [
                'unit_id' => $divisiUnit?->id,
            ]);
        }
        */
    }

    public function unitType()
    {
        return $this->belongsTo(UnitType::class, 'unit_type_id');
    }

    public function parent()
    {
        return $this->belongsTo(Unit::class, 'parent_id');
    }

    public function children()
    {
        return $this->HasMany(Unit::class, 'parent_id');
    }

    public function rekomendasiRisikos()
    {
        return $this->hasMany(RekomendasiRisiko::class, 'unit_id');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'cost_center_parent', 'cost_center');
    }

    public function auditTrails(): HasMany
    {
        return $this->hasMany(Audit::class, 'unit_id')
            ->latest('id');
    }
}
