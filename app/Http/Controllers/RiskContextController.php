<?php

namespace App\Http\Controllers;

use App\Models\RiskContext;
use App\Models\RiskContextMember;
use App\Models\RiskContextStakeholderInternal;
use App\Models\RiskContextStakeholderExternal;
use App\Models\Unit;
use App\Models\Periode;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;
use App\Models\User;

class RiskContextController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $unit = $user->unit;
        $periodes = Periode::orderBy('tahun', 'desc')->get();

        $riskContexts = RiskContext::where('unit_id', $unit->id)
            ->with(['periode', 'pimpinanTertinggi', 'members.jabatan', 'stakeholderInternals', 'stakeholderExternals'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('risk-context.index', compact('riskContexts', 'periodes', 'unit'));
    }

    public function detail($periodeId, $unitId)
    {
        // $user = Auth::user();
        // $unit = $user->unit;
        $unit = Unit::find($unitId);
        $periode = Periode::find($periodeId);
        $periodes = Periode::orderBy('tahun', 'desc')->get();

        $riskContexts = RiskContext::where('unit_id', $unitId)
            ->where('periode_id', $periodeId)
            ->with(['periode', 'pimpinanTertinggi', 'members.jabatan', 'stakeholderInternals', 'stakeholderExternals'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('risk-context.detail', compact('riskContexts', 'periodes', 'unit', 'periode'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $unit = $request->unit_id ? $request->unit_id : $user->unit;
        $periodes = Periode::orderBy('tahun', 'desc')->get();
        $jabatans = Jabatan::where('jabatan_type', 1)->orderBy('name')->get();

        $selectedPeriode = null;
        if ($request->periode_id) {
            $selectedPeriode = Periode::find($request->periode_id);
        } else {
            $selectedPeriode = Periode::orderBy('tahun', 'desc')->first();
        }

        // Check if risk context already exists for this unit and periode
        $existingContext = RiskContext::where('unit_id', $unit->id)
            ->where('periode_id', $selectedPeriode->id)
            ->first();

        if ($existingContext) {
            return redirect()->route('risk-context.edit', $existingContext->id)
                ->with('info', 'Risk Context untuk periode ini sudah ada. Anda dapat mengeditnya.');
        }

        return view('risk-context.create', compact('unit', 'periodes', 'jabatans', 'selectedPeriode'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'periode_id' => 'required|exists:periodes,id',
            'nilai' => 'nullable|string',
            'pimpinan_tertinggi_jabatan_id' => 'nullable|exists:jabatans,id',
            'sponsor' => 'nullable|string',
            'deskripsi' => 'nullable|string',
            'tujuan' => 'nullable|string',
            'lingkup_pekerjaan' => 'nullable|string',
            'pekerjaan_luar_lingkup' => 'nullable|string',
            'sasaran' => 'nullable|string',
            'batasan' => 'nullable|string',
            'asumsi_dasar' => 'nullable|string',
            'member_nama.*' => 'nullable|string',
            'member_jabatan_id.*' => 'nullable|exists:jabatans,id',
            'stakeholder_internal_stakeholder.*' => 'nullable|string',
            'stakeholder_internal_peran.*' => 'nullable|string',
            'stakeholder_internal_komunikasi.*' => 'nullable|string',
            'stakeholder_external_stakeholder.*' => 'nullable|string',
            'stakeholder_external_peran.*' => 'nullable|string',
            'stakeholder_external_komunikasi.*' => 'nullable|string',
        ]);

        $user = Auth::user();
        $unit = $user->unit;

        DB::beginTransaction();
        try {
            // Create risk context
            $riskContext = RiskContext::create([
                'unit_id' => $unit->id,
                'periode_id' => $request->periode_id,
                'nilai' => $request->nilai,
                'pimpinan_tertinggi_jabatan_id' => $request->pimpinan_tertinggi_jabatan_id,
                'sponsor' => $request->sponsor,
                'deskripsi' => $request->deskripsi,
                'tujuan' => $request->tujuan,
                'lingkup_pekerjaan' => $request->lingkup_pekerjaan,
                'pekerjaan_luar_lingkup' => $request->pekerjaan_luar_lingkup,
                'sasaran' => $request->sasaran,
                'batasan' => $request->batasan,
                'asumsi_dasar' => $request->asumsi_dasar,
            ]);

            // Save members
            if ($request->member_nama) {
                foreach ($request->member_nama as $index => $nama) {
                    if ($nama && isset($request->member_jabatan_id[$index])) {
                        RiskContextMember::create([
                            'risk_context_id' => $riskContext->id,
                            'nama' => $nama,
                            'jabatan_id' => $request->member_jabatan_id[$index],
                        ]);
                    }
                }
            }

            // Save stakeholder internal
            if ($request->stakeholder_internal_stakeholder) {
                foreach ($request->stakeholder_internal_stakeholder as $index => $stakeholder) {
                    if ($stakeholder && isset($request->stakeholder_internal_peran[$index]) && isset($request->stakeholder_internal_komunikasi[$index])) {
                        RiskContextStakeholderInternal::create([
                            'risk_context_id' => $riskContext->id,
                            'stakeholder' => $stakeholder,
                            'peran' => $request->stakeholder_internal_peran[$index],
                            'komunikasi' => $request->stakeholder_internal_komunikasi[$index],
                        ]);
                    }
                }
            }

            // Save stakeholder external
            if ($request->stakeholder_external_stakeholder) {
                foreach ($request->stakeholder_external_stakeholder as $index => $stakeholder) {
                    if ($stakeholder && isset($request->stakeholder_external_peran[$index]) && isset($request->stakeholder_external_komunikasi[$index])) {
                        RiskContextStakeholderExternal::create([
                            'risk_context_id' => $riskContext->id,
                            'stakeholder' => $stakeholder,
                            'peran' => $request->stakeholder_external_peran[$index],
                            'komunikasi' => $request->stakeholder_external_komunikasi[$index],
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('risk-context.index')
                ->with('success', 'Risk Context berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $user = Auth::user();
        $unit = $user->unit;

        $riskContext = RiskContext::where('unit_id', $unit->id)
            ->with(['members.jabatan', 'stakeholderInternals', 'stakeholderExternals', 'periode', 'pimpinanTertinggi'])
            ->findOrFail($id);

        $periodes = Periode::orderBy('tahun', 'desc')->get();
        $jabatans = Jabatan::where('jabatan_type', 1)->orderBy('name')->get();

        return view('risk-context.edit', compact('riskContext', 'unit', 'periodes', 'jabatans'));
    }

    public function updateOrCreate(Request $request)
    {
        $user = Auth::user();
        $unit_id = $request->unit_id ? $request->unit_id : $user->unit_id;
        $unit = Unit::find($unit_id);
        $periodes = Periode::orderBy('tahun', 'desc')->get();
        $jabatans = Jabatan::where('jabatan_type', 1)->orderBy('name')->get();

        $selectedPeriode = null;
        if ($request->periode_id) {
            $selectedPeriode = Periode::find($request->periode_id);
        } else {
            $selectedPeriode = Periode::orderBy('tahun', 'desc')->first();
        }

        // Check if risk context already exists for this unit and periode
        $riskContext = RiskContext::where('unit_id', $unit_id)
            ->where('periode_id', $selectedPeriode->id)
            ->with(['members.jabatan', 'stakeholderInternals', 'stakeholderExternals', 'periode', 'pimpinanTertinggi'])
            ->first();

        $isEdit = $riskContext ? true : false;

        return view('risk-context.update', compact('unit', 'periodes', 'jabatans', 'selectedPeriode', 'riskContext', 'isEdit'));
    }

    public function storeOrUpdate(Request $request)
    {
        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'periode_id' => 'required|exists:periodes,id',
            'nilai' => 'nullable|string',
            'pimpinan_tertinggi_jabatan_id' => 'nullable|exists:jabatans,id',
            'sponsor' => 'nullable|string',
            'deskripsi' => 'nullable|string',
            'tujuan' => 'nullable|string',
            'lingkup_pekerjaan' => 'nullable|string',
            'pekerjaan_luar_lingkup' => 'nullable|string',
            'sasaran' => 'nullable|string',
            'batasan' => 'nullable|string',
            'asumsi_dasar' => 'nullable|string',
            'member_nama.*' => 'nullable|string',
            'member_jabatan_id.*' => 'nullable|exists:jabatans,id',
            'stakeholder_internal_stakeholder.*' => 'nullable|string',
            'stakeholder_internal_peran.*' => 'nullable|string',
            'stakeholder_internal_komunikasi.*' => 'nullable|string',
            'stakeholder_external_stakeholder.*' => 'nullable|string',
            'stakeholder_external_peran.*' => 'nullable|string',
            'stakeholder_external_komunikasi.*' => 'nullable|string',
        ]);

        $user = Auth::user();
        $unit_id = $request->unit_id ? $request->unit_id : $user->unit_id;
        $unit = Unit::find($unit_id);

        DB::beginTransaction();
        try {
            // Check if risk context already exists
            $riskContext = RiskContext::where('unit_id', $unit->id)
                ->where('periode_id', $request->periode_id)
                ->first();

            if ($riskContext) {
                // Update existing risk context
                $riskContext->update([
                    'nilai' => $request->nilai,
                    'pimpinan_tertinggi_jabatan_id' => $request->pimpinan_tertinggi_jabatan_id,
                    'sponsor' => $request->sponsor,
                    'deskripsi' => $request->deskripsi,
                    'tujuan' => $request->tujuan,
                    'lingkup_pekerjaan' => $request->lingkup_pekerjaan,
                    'pekerjaan_luar_lingkup' => $request->pekerjaan_luar_lingkup,
                    'sasaran' => $request->sasaran,
                    'batasan' => $request->batasan,
                    'asumsi_dasar' => $request->asumsi_dasar,
                ]);

                // Delete existing related data
                $riskContext->members()->delete();
                $riskContext->stakeholderInternals()->delete();
                $riskContext->stakeholderExternals()->delete();

                $message = 'Risk Context berhasil diperbarui.';
            } else {
                // Create new risk context
                $riskContext = RiskContext::create([
                    'unit_id' => $unit->id,
                    'periode_id' => $request->periode_id,
                    'nilai' => $request->nilai,
                    'pimpinan_tertinggi_jabatan_id' => $request->pimpinan_tertinggi_jabatan_id,
                    'sponsor' => $request->sponsor,
                    'deskripsi' => $request->deskripsi,
                    'tujuan' => $request->tujuan,
                    'lingkup_pekerjaan' => $request->lingkup_pekerjaan,
                    'pekerjaan_luar_lingkup' => $request->pekerjaan_luar_lingkup,
                    'sasaran' => $request->sasaran,
                    'batasan' => $request->batasan,
                    'asumsi_dasar' => $request->asumsi_dasar,
                ]);

                $message = 'Risk Context berhasil dibuat.';
            }

            // Save members
            if ($request->member_nama) {
                foreach ($request->member_nama as $index => $nama) {
                    if ($nama && isset($request->member_jabatan_id[$index])) {
                        RiskContextMember::create([
                            'risk_context_id' => $riskContext->id,
                            'nama' => $nama,
                            'jabatan_id' => $request->member_jabatan_id[$index],
                        ]);
                    }
                }
            }

            // Save stakeholder internal
            if ($request->stakeholder_internal_stakeholder) {
                foreach ($request->stakeholder_internal_stakeholder as $index => $stakeholder) {
                    if ($stakeholder && isset($request->stakeholder_internal_peran[$index]) && isset($request->stakeholder_internal_komunikasi[$index])) {
                        RiskContextStakeholderInternal::create([
                            'risk_context_id' => $riskContext->id,
                            'stakeholder' => $stakeholder,
                            'peran' => $request->stakeholder_internal_peran[$index],
                            'komunikasi' => $request->stakeholder_internal_komunikasi[$index],
                        ]);
                    }
                }
            }

            // Save stakeholder external
            if ($request->stakeholder_external_stakeholder) {
                foreach ($request->stakeholder_external_stakeholder as $index => $stakeholder) {
                    if ($stakeholder && isset($request->stakeholder_external_peran[$index]) && isset($request->stakeholder_external_komunikasi[$index])) {
                        RiskContextStakeholderExternal::create([
                            'risk_context_id' => $riskContext->id,
                            'stakeholder' => $stakeholder,
                            'peran' => $request->stakeholder_external_peran[$index],
                            'komunikasi' => $request->stakeholder_external_komunikasi[$index],
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('risk-context.detail', ['periodeId' => $request->periode_id, 'unitId' => $unit->id])
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $user = Auth::user();
        $unit = $user->unit;

        $riskContext = RiskContext::where('unit_id', $unit->id)
            ->with(['members.jabatan', 'stakeholderInternals', 'stakeholderExternals', 'periode', 'pimpinanTertinggi', 'unit'])
            ->findOrFail($id);

        return view('risk-context.show', compact('riskContext'));
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $unit = $user->unit;

        $riskContext = RiskContext::where('unit_id', $unit->id)->findOrFail($id);
        $riskContext->delete();

        return redirect()->route('risk-context.index')
            ->with('success', 'Risk Context berhasil dihapus.');
    }

    public function detailAnper($periodeId, $unitId) {
        $unit = Unit::find($unitId);
        $periode = Periode::find($periodeId);
        $periodes = Periode::orderBy('tahun', 'desc')->get();

        $riskContexts = RiskContext::where('unit_id', $unitId)
            ->where('periode_id', $periodeId)
            ->with(['periode', 'pimpinanTertinggi', 'members.jabatan', 'stakeholderInternals', 'stakeholderExternals'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('risk-context-anper.detail', compact('riskContexts', 'periodes', 'unit', 'periode'));
    }

    public function updateOrCreateAnper(Request $request)
    {
        $user = Auth::user();
        $unit_id = $request->unit_id ? $request->unit_id : $user->unit_id;
        $unit = Unit::find($unit_id);
        $periodes = Periode::orderBy('tahun', 'desc')->get();
        $jabatans = Jabatan::where('jabatan_type', 1)->orderBy('name')->get();

        $selectedPeriode = null;
        if ($request->periode_id) {
            $selectedPeriode = Periode::find($request->periode_id);
        } else {
            $selectedPeriode = Periode::orderBy('tahun', 'desc')->first();
        }

        // Check if risk context already exists for this unit and periode
        $riskContext = RiskContext::where('unit_id', $unit_id)
            ->where('periode_id', $selectedPeriode->id)
            ->with(['members.jabatan', 'stakeholderInternals', 'stakeholderExternals', 'periode', 'pimpinanTertinggi'])
            ->first();

        $isEdit = $riskContext ? true : false;

        return view('risk-context-anper.update', compact('unit', 'periodes', 'jabatans', 'selectedPeriode', 'riskContext', 'isEdit'));
    }

    public function storeOrUpdateAnper(Request $request)
    {
        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'periode_id' => 'required|exists:periodes,id',
            'nilai' => 'nullable|string',
            'pimpinan_tertinggi_jabatan_id' => 'nullable|exists:jabatans,id',
            'sponsor' => 'nullable|string',
            'deskripsi' => 'nullable|string',
            'tujuan' => 'nullable|string',
            'lingkup_pekerjaan' => 'nullable|string',
            'pekerjaan_luar_lingkup' => 'nullable|string',
            'sasaran' => 'nullable|string',
            'batasan' => 'nullable|string',
            'asumsi_dasar' => 'nullable|string',
            'member_nama.*' => 'nullable|string',
            'member_jabatan_id.*' => 'nullable|exists:jabatans,id',
            'stakeholder_internal_stakeholder.*' => 'nullable|string',
            'stakeholder_internal_peran.*' => 'nullable|string',
            'stakeholder_internal_komunikasi.*' => 'nullable|string',
            'stakeholder_external_stakeholder.*' => 'nullable|string',
            'stakeholder_external_peran.*' => 'nullable|string',
            'stakeholder_external_komunikasi.*' => 'nullable|string',
        ]);

        $user = Auth::user();
        $unit_id = $request->unit_id ? $request->unit_id : $user->unit_id;
        $unit = Unit::find($unit_id);

        DB::beginTransaction();
        try {
            // Check if risk context already exists
            $riskContext = RiskContext::where('unit_id', $unit->id)
                ->where('periode_id', $request->periode_id)
                ->first();

            if ($riskContext) {
                // Update existing risk context
                $riskContext->update([
                    'nilai' => $request->nilai,
                    'pimpinan_tertinggi_jabatan_id' => $request->pimpinan_tertinggi_jabatan_id,
                    'sponsor' => $request->sponsor,
                    'deskripsi' => $request->deskripsi,
                    'tujuan' => $request->tujuan,
                    'lingkup_pekerjaan' => $request->lingkup_pekerjaan,
                    'pekerjaan_luar_lingkup' => $request->pekerjaan_luar_lingkup,
                    'sasaran' => $request->sasaran,
                    'batasan' => $request->batasan,
                    'asumsi_dasar' => $request->asumsi_dasar,
                    'status' => RiskContext::STATUS_DRAFT,
                ]);

                // Delete existing related data
                $riskContext->members()->delete();
                $riskContext->stakeholderInternals()->delete();
                $riskContext->stakeholderExternals()->delete();

                $message = 'Risk Context berhasil diperbarui.';
            } else {
                // Create new risk context
                $riskContext = RiskContext::create([
                    'unit_id' => $unit->id,
                    'periode_id' => $request->periode_id,
                    'nilai' => $request->nilai,
                    'pimpinan_tertinggi_jabatan_id' => $request->pimpinan_tertinggi_jabatan_id,
                    'sponsor' => $request->sponsor,
                    'deskripsi' => $request->deskripsi,
                    'tujuan' => $request->tujuan,
                    'lingkup_pekerjaan' => $request->lingkup_pekerjaan,
                    'pekerjaan_luar_lingkup' => $request->pekerjaan_luar_lingkup,
                    'sasaran' => $request->sasaran,
                    'batasan' => $request->batasan,
                    'asumsi_dasar' => $request->asumsi_dasar,
                ]);

                $message = 'Risk Context berhasil dibuat.';
            }

            // Save members
            if ($request->member_nama) {
                foreach ($request->member_nama as $index => $nama) {
                    if ($nama && isset($request->member_jabatan_id[$index])) {
                        RiskContextMember::create([
                            'risk_context_id' => $riskContext->id,
                            'nama' => $nama,
                            'jabatan_id' => $request->member_jabatan_id[$index],
                        ]);
                    }
                }
            }

            // Save stakeholder internal
            if ($request->stakeholder_internal_stakeholder) {
                foreach ($request->stakeholder_internal_stakeholder as $index => $stakeholder) {
                    if ($stakeholder && isset($request->stakeholder_internal_peran[$index]) && isset($request->stakeholder_internal_komunikasi[$index])) {
                        RiskContextStakeholderInternal::create([
                            'risk_context_id' => $riskContext->id,
                            'stakeholder' => $stakeholder,
                            'peran' => $request->stakeholder_internal_peran[$index],
                            'komunikasi' => $request->stakeholder_internal_komunikasi[$index],
                        ]);
                    }
                }
            }

            // Save stakeholder external
            if ($request->stakeholder_external_stakeholder) {
                foreach ($request->stakeholder_external_stakeholder as $index => $stakeholder) {
                    if ($stakeholder && isset($request->stakeholder_external_peran[$index]) && isset($request->stakeholder_external_komunikasi[$index])) {
                        RiskContextStakeholderExternal::create([
                            'risk_context_id' => $riskContext->id,
                            'stakeholder' => $stakeholder,
                            'peran' => $request->stakeholder_external_peran[$index],
                            'komunikasi' => $request->stakeholder_external_komunikasi[$index],
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('risk-context-anper.detail', ['periodeId' => $request->periode_id, 'unitId' => $unit->id])
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    // SUBMIT (Risk Officer: Level 1)
    public function submit($id)
    {
        $user = Auth::user();

        // Cek Level Officer (1)
        if ($user->level_id != 1) {
            return back()->with('error', 'Akses Ditolak. Hanya Risk Officer yang dapat mengajukan verifikasi.');
        }

        $context = RiskContext::find($id);

        if (!$context) {
            return back()->with('error', 'Data Risk Context tidak ditemukan.');
        }

        // Cek Unit sama dengan User
        // if ($context->unit_id != $user->unit_id) {
        //     return back()->with('error', 'Akses Ditolak. Anda tidak dapat mengajukan data dari unit lain.');
        // }

        // Validasi Status
        if ($context->status !== RiskContext::STATUS_DRAFT && $context->status !== RiskContext::STATUS_REVISION) {
            return back()->with('error', 'Status dokumen tidak valid untuk diajukan.');
        }

        $context->update([
            'status' => RiskContext::STATUS_SUBMITTED,
            'catatan_perbaikan' => null
        ]);

        $unit = $context->unit;

        $targetLink = '#';
        if ($unit->unit_type_id == 1) {
            $targetLink = route('risk-context.detail', [
                'periodeId' => $context->periode_id,
                'unitId' => $context->unit_id
            ]);
        } else if ($unit->unit_type_id == 2) {
            $targetLink = route('risk-context-anper.detail', [
                'periodeId' => $context->periode_id,
                'unitId' => $context->unit_id
            ]);
        }

        $riskOwners = User::where('level_id', 2)
            ->where('unit_id', $context->unit_id)
            ->with(['unit'])
            ->get();


        foreach ($riskOwners as $riskOwner) {
            Notification::create([
                'user_id' => $riskOwner->id,
                'title'   => 'Verifikasi Risk Context',
                'message' => 'Risk Context divisi ' . $unit->name . ' menunggu verifikasi Anda.',
                'icon'    => 'bx bx-check-circle',
                'link'    => $targetLink,
                'read_at' => null,
            ]);
        }

        return back()->with('success', 'Risk Context berhasil diajukan ke Risk Owner.');
    }

    // VERIFY (Risk Owner: Level 2)
    public function verify($id)
    {
        $user = Auth::user();

        // Cek Level Owner (2)
        if ($user->level_id != 2) {
            return back()->with('error', 'Akses Ditolak. Hanya Risk Owner yang dapat melakukan verifikasi.');
        }

        $context = RiskContext::find($id);
        if (!$context) {
            return back()->with('error', 'Data Risk Context tidak ditemukan.');
        }

        if (!$this->canManageContextAsOwner($user, $context)) {
            return back()->with('error', 'Akses Ditolak. Anda tidak dapat memverifikasi data unit tersebut.');
        }

        if ($context->status !== RiskContext::STATUS_SUBMITTED) {
            return back()->with('error', 'Dokumen belum diajukan.');
        }

        $context->update([
            'status' => RiskContext::STATUS_VERIFIED,
            'verified_by' => $user->id,
            'verified_at' => now(),
            'catatan_perbaikan' => null
        ]);

        $unit = $context->unit;

        $targetLink = '#';
        if ($unit->unit_type_id == 1) {
          $targetLink = route('risk-context.detail', [
            'periodeId' => $context->periode_id,
            'unitId' => $context->unit_id
          ]);
        } else if ($unit->unit_type_id == 2) {
          $targetLink = route('risk-context-anper.detail', [
            'periodeId' => $context->periode_id,
            'unitId' => $context->unit_id
          ]);
        }

        $riskOfficers = User::where('level_id', 1)
            ->where('unit_id', $context->unit_id)
            ->get();

        foreach ($riskOfficers as $officer) {
            Notification::create([
                'user_id' => $officer->id,
                'title'   => 'Risk Context Disetujui',
                'message' => 'Risk Context divisi ' . $unit->name . ' telah diverifikasi.',
                'icon'    => 'bx bx-check-double',
                'link'    => $targetLink,
                'read_at' => null,
            ]);
        }

        return back()->with('success', 'Risk Context berhasil diverifikasi.');
    }

    // REVISI (Risk Owner: Level 2)
    public function reject(Request $request, $id)
    {
        $user = Auth::user();

        if ($user->level_id != 2) {
            return back()->with('error', 'Akses Ditolak. Hanya Risk Owner yang dapat melakukan revisi.');
        }

        $request->validate([
            'catatan_perbaikan' => 'required|string'
        ]);

        $context = RiskContext::find($id);
        if (!$context) {
            return back()->with('error', 'Data Risk Context tidak ditemukan.');
        }

        if (!$this->canManageContextAsOwner($user, $context)) {
            return back()->with('error', 'Akses Ditolak. Anda tidak dapat merevisi data unit tersebut.');
        }

        // if ($context->status !== RiskContext::STATUS_SUBMITTED && $context->status !== RiskContext::STATUS_VERIFIED) {
        //     return back()->with('error', 'Dokumen belum diajukan.');
        // }

        $context->update([
            'status' => RiskContext::STATUS_REVISION,
            'catatan_perbaikan' => $request->catatan_perbaikan,
            'verified_by' => null,
            'verified_at' => null
        ]);

        $unit = $context->unit;

        $targetLink = '#';
        if ($unit->unit_type_id == 1) {
          $targetLink = route('risk-context.detail', [
            'periodeId' => $context->periode_id,
            'unitId' => $context->unit_id
          ]);
        } else if ($unit->unit_type_id == 2) {
          $targetLink = route('risk-context-anper.detail', [
            'periodeId' => $context->periode_id,
            'unitId' => $context->unit_id
          ]);
        }

        $riskOfficers = User::where('level_id', 1)
            ->where('unit_id', $context->unit_id)
            ->get();

        foreach ($riskOfficers as $officer) {
            Notification::create([
                'user_id' => $officer->id,
                'title'   => 'Revisi Risk Context',
                'message' => 'Perbaikan diperlukan pada divisi ' . $unit->name . '.',
                'icon'    => 'bx bx-check-double',
                'link'    => $targetLink,
                'read_at' => null,
            ]);
        }

        return back()->with('success', 'Risk Context dikembalikan untuk perbaikan.');
    }

    private function canManageContextAsOwner($user, RiskContext $context): bool
    {
        if ((int) $user->unit_id === (int) $context->unit_id) {
            return true;
        }

        // Owner MR dapat mengelola context lintas unit saat punya permission verifikasi MR.
        if ($user->can('verification_mr') && optional($user->unit)->unit_mr == 1) {
            return true;
        }

        return false;
    }
}
