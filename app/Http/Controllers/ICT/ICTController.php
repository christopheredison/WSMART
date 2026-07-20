<?php

namespace App\Http\Controllers\ICT;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ICTPlan;
use App\Models\ICTPlanControl;
use App\Models\IdentifikasiRisiko;
use App\Models\ProjectRisk;
use App\Models\PeristiwaRisiko;
use App\Models\Unit;
use App\Models\Project;
use App\Models\KontrolEksisting;
use App\Models\ProjectKontrolEksisting;
use App\Models\ICTDo;
use App\Models\ICTReport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class ICTController extends Controller
{
    public function index()
    {
        $query = ICTPlan::with(['planControls']);

        if (Gate::denies('ict_approval')) {
            $query->whereIn('status', ['draft', 'rejected']);
        }

        $ictPlans = $query->get();
        $hasPendingApproval = ICTPlan::where('status', 'pending_approval')->exists();
        $hasDrafts = ICTPlan::where('status', 'draft')->exists();
        $hasRejected = ICTPlan::where('status', 'rejected')->exists();

        $data = [];
        foreach ($ictPlans as $plan) {
            $riskDetail = $this->resolveRiskDetail($plan);

            $keyControls = $plan->planControls->pluck('key_control')->implode(', ');

            $data[] = [
                'id' => $plan->id,
                'tahun_pelaporan' => $plan->tahun_pelaporan ?? '-', // TAMBAHAN
                'sasaran_bumn' => $plan->sasaran_bumn,
                'peristiwa_risiko' => $riskDetail['peristiwa_risiko'],
                'lokasi_risiko' => $riskDetail['lokasi_risiko'],
                'business_process' => $plan->business_process,
                'key_controls' => $keyControls,
                'metode_pengujian' => $plan->metode_pengujian,
                'status' => $plan->status,
                'rejection_reason' => $plan->rejection_reason,
            ];
        }

        return view('ict.index', compact('data', 'hasPendingApproval', 'hasDrafts', 'hasRejected'));
    }

    public function create()
    {
        $this->authorize('ict_input');

        if (ICTPlan::where('status', 'pending_approval')->exists()) {
            return redirect()->route('ict.index')->with('error', 'Tidak dapat menambah data baru. Terdapat data yang sedang menunggu persetujuan.');
        }

        // HANYA AMBIL DARI RISK REGISTER KORPORAT (Unit = 1)
        $identifikasiRisikos = IdentifikasiRisiko::select('id', 'peristiwa_risiko')
                                ->where('unit_id', 1)->get();

        return view('ict.create', compact('identifikasiRisikos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tahun_pelaporan' => 'required|numeric|min:2025',
            'sasaran_bumn' => 'required',
            'risiko_mode' => 'required|in:existing,manual',
            'risiko_id' => 'required_if:risiko_mode,existing|nullable',
            'peristiwa_risiko_manual' => 'required_if:risiko_mode,manual|nullable|string',
            'lokasi_risiko' => 'required|string',
            'business_process' => 'required',
            'metode_pengujian' => 'required',
            'key_control_id' => 'required|array',
            'key_control' => 'required|array',
            'key_control.*' => 'required|string',
        ]);

        $isManualRisk = $request->risiko_mode === 'manual';

        $ictPlan = ICTPlan::create([
            'tahun_pelaporan' => $request->tahun_pelaporan,
            'sasaran_bumn' => $request->sasaran_bumn,
            'risiko_id' => $isManualRisk ? null : $request->risiko_id,
            'peristiwa_risiko' => $isManualRisk ? $request->peristiwa_risiko_manual : null,
            'lokasi_risiko' => $request->lokasi_risiko,
            'type' => 1, // KUNCI KE TYPE 1 (Korporat)
            'business_process' => $request->business_process,
            'metode_pengujian' => $request->metode_pengujian,
        ]);

        foreach ($request->key_control_id as $index => $keyControlId) {
            ICTPlanControl::create([
                'ict_plan_id' => $ictPlan->id,
                'key_control_id' => $keyControlId,
                'key_control' => $request->key_control[$index],
            ]);
        }

        return redirect()->route('ict.index')->with('success', 'Data ICT Plan berhasil disimpan');
    }

    public function edit($id)
    {
        $this->authorize('ict_input');
        $ictPlan = ICTPlan::with('planControls')->findOrFail($id);

        if (!in_array($ictPlan->status, ['draft', 'rejected'])) {
            return redirect()->route('ict.index')->with('error', 'Data ini tidak dapat diedit karena statusnya sudah diproses.');
        }

        // HANYA AMBIL DARI RISK REGISTER KORPORAT (Unit = 1)
        $identifikasiRisikos = IdentifikasiRisiko::select('id', 'peristiwa_risiko')
                                ->where('unit_id', 1)->get();

        return view('ict.edit', compact('ictPlan', 'identifikasiRisikos'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('ict_input');
        $ictPlan = ICTPlan::findOrFail($id);

        $request->validate([
            'tahun_pelaporan' => 'required|numeric|min:2025',
            'sasaran_bumn' => 'required',
            'risiko_mode' => 'required|in:existing,manual',
            'risiko_id' => 'required_if:risiko_mode,existing|nullable',
            'peristiwa_risiko_manual' => 'required_if:risiko_mode,manual|nullable|string',
            'lokasi_risiko' => 'required|string',
            'business_process' => 'required',
            'metode_pengujian' => 'required',
            'key_control_id' => 'required|array',
            'key_control' => 'required|array',
            'key_control.*' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $oldMode = empty($ictPlan->risiko_id) ? 'manual' : 'existing';
            $newMode = $request->risiko_mode;
            $newRisikoId = $newMode === 'existing' ? $request->risiko_id : null;
            $newPeristiwaManual = $newMode === 'manual' ? $request->peristiwa_risiko_manual : null;

            $riskChanged = ($oldMode !== $newMode)
                || ((string) $ictPlan->risiko_id !== (string) $newRisikoId)
                || ((string) ($ictPlan->peristiwa_risiko ?? '') !== (string) ($newPeristiwaManual ?? ''))
                || ((string) ($ictPlan->lokasi_risiko ?? '') !== (string) $request->lokasi_risiko)
                || ($ictPlan->type != 1);

            $ictPlan->update([
                'tahun_pelaporan' => $request->tahun_pelaporan,
                'sasaran_bumn' => $request->sasaran_bumn,
                'risiko_id' => $newRisikoId,
                'peristiwa_risiko' => $newPeristiwaManual,
                'lokasi_risiko' => $request->lokasi_risiko,
                'type' => 1, // KUNCI KE TYPE 1 (Korporat)
                'business_process' => $request->business_process,
                'metode_pengujian' => $request->metode_pengujian,
                'status' => ($ictPlan->status == 'rejected') ? 'draft' : $ictPlan->status,
            ]);

            if ($riskChanged) {
                foreach($ictPlan->planControls as $control) {
                    $control->dos()->delete();
                    $control->delete();
                }

                foreach ($request->key_control_id as $index => $keyControlId) {
                    ICTPlanControl::create([
                        'ict_plan_id' => $ictPlan->id,
                        'key_control_id' => $keyControlId,
                        'key_control' => $request->key_control[$index],
                    ]);
                }
            } else {
                $submittedIds = array_filter($request->ict_plan_control_id ?? []);
                $controlsToDelete = ICTPlanControl::where('ict_plan_id', $ictPlan->id)
                                    ->whereNotIn('id', $submittedIds)
                                    ->get();

                foreach($controlsToDelete as $delControl) {
                    $delControl->dos()->delete();
                    $delControl->delete();
                }

                foreach ($request->key_control_id as $index => $masterKeyId) {
                    $currentId = isset($request->ict_plan_control_id[$index]) ? $request->ict_plan_control_id[$index] : null;

                    if ($currentId) {
                        ICTPlanControl::where('id', $currentId)->update([
                            'key_control_id' => $masterKeyId,
                            'key_control' => $request->key_control[$index],
                        ]);
                    } else {
                        ICTPlanControl::create([
                            'ict_plan_id' => $ictPlan->id,
                            'key_control_id' => $masterKeyId,
                            'key_control' => $request->key_control[$index],
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('ict.show', $ictPlan->id)->with('success', 'Data ICT Plan berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    public function testing($id)
    {
        // Ambil data ICTPlan dengan relasi planControls
        $ictPlan = ICTPlan::with(['planControls.latestDo'])->findOrFail($id);

        $riskDetail = $this->resolveRiskDetail($ictPlan);
        $peristiwaRisiko = $riskDetail['peristiwa_risiko'];
        $lokasiRisiko = $riskDetail['lokasi_risiko'];

        // Ambil data Jabatan untuk dropdown
        $jabatans = \App\Models\Jabatan::orderBy('name')->get();

        return view('ict.pelaksanaan', compact('ictPlan', 'peristiwaRisiko', 'lokasiRisiko', 'jabatans'));
    }

    public function storeTesting(Request $request, $id)
    {
        $action = $request->input('action'); // 'draft' atau 'submit'

        // 1. Definisi Rules Dasar
        $rules = [
            'plan_control_id'   => 'required|array',
            'plan_control_id.*' => 'required|exists:ict_plan_controls,id',
        ];

        // 2. List field yang akan divalidasi
        $fields = [
            'jenis_kontrol',
            'bentuk_kontrol',
            'level_pengendalian',
            'kecukupan_desain_pengendalian_1',
            'kecukupan_desain_pengendalian_2',
            'kecukupan_desain_pengendalian_3',
            'kecukupan_desain_pengendalian_4',
            'kecukupan_desain_pengendalian_akhir',
            'efektivitas_desain_pengendalian_1',
            'efektivitas_desain_pengendalian_2',
            'efektivitas_desain_pengendalian_3',
            'efektivitas_desain_pengendalian_akhir',
            'kesimpulan_akhir',
            'hasil_temuan',
            'rencana_tindak_lanjut',
            'batas_waktu_penyelesaian',
            // 'penanggung_jawab_jabatan_id' // Kita validasi manual di bawah agar lebih fleksibel
        ];

        // 3. Generate Rules untuk Array
        foreach ($fields as $field) {
            // Pastikan field dikirim sebagai array
            $rules[$field] = 'array';

            if ($action === 'draft') {
                // Draft: Isinya boleh null/kosong
                $rules["{$field}.*"] = 'nullable';
            } else {
                // Submit: Isinya WAJIB terisi
                $rules["{$field}.*"] = 'required';
            }
        }

        // 4. Validasi Spesifik (Opsional tapi disarankan)
        if ($action !== 'draft') {
            // Validasi format tanggal untuk batas waktu
            $rules['batas_waktu_penyelesaian.*'] = 'required|date';
        }

        // Jalankan Validasi
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);

        // Custom Validation untuk Penanggung Jawab (karena ada logic OR)
        $validator->after(function ($validator) use ($request, $action) {
            if ($action !== 'draft' && $request->has('plan_control_id')) {
                foreach ($request->plan_control_id as $key => $val) {
                    $jabatanId = $request->penanggung_jawab_jabatan_id[$key] ?? null;
                    $manualName = $request->penanggung_jawab[$key] ?? null;

                    // Jika SUBMIT, salah satu dari Jabatan atau Nama Manual harus diisi
                    if (empty($jabatanId) && empty($manualName)) {
                        $validator->errors()->add("penanggung_jawab.{$key}", "Penanggung Jawab pada baris ke-" . ($key + 1) . " wajib diisi.");
                    }
                }
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', 'Mohon lengkapi data yang wajib diisi.');
        }

        try {
            DB::beginTransaction();

            foreach ($request->plan_control_id as $index => $planControlId) {

                // Logic Penanggung Jawab
                $penanggungJawabName = null;
                $jabatanId = $request->penanggung_jawab_jabatan_id[$index] ?? null;

                if ($jabatanId) {
                    $jabatan = \App\Models\Jabatan::find($jabatanId);
                    $penanggungJawabName = $jabatan ? $jabatan->name : null;
                } else {
                    $penanggungJawabName = $request->penanggung_jawab[$index] ?? null;
                }

                $getValue = function($fieldName) use ($request, $index) {
                    return isset($request->{$fieldName}[$index]) ? $request->{$fieldName}[$index] : null;
                };

                ICTDo::updateOrCreate(
                    ['plan_control_id' => $planControlId],
                    [
                        'jenis_kontrol' => $getValue('jenis_kontrol'),
                        'bentuk_kontrol' => $getValue('bentuk_kontrol'),
                        'level_pengendalian' => $getValue('level_pengendalian'),

                        'kecukupan_desain_pengendalian_1' => $getValue('kecukupan_desain_pengendalian_1'),
                        'kecukupan_desain_pengendalian_2' => $getValue('kecukupan_desain_pengendalian_2'),
                        'kecukupan_desain_pengendalian_3' => $getValue('kecukupan_desain_pengendalian_3'),
                        'kecukupan_desain_pengendalian_4' => $getValue('kecukupan_desain_pengendalian_4'),
                        'kecukupan_desain_pengendalian_akhir' => $getValue('kecukupan_desain_pengendalian_akhir'),

                        'efektivitas_desain_pengendalian_1' => $getValue('efektivitas_desain_pengendalian_1'),
                        'efektivitas_desain_pengendalian_2' => $getValue('efektivitas_desain_pengendalian_2'),
                        'efektivitas_desain_pengendalian_3' => $getValue('efektivitas_desain_pengendalian_3'),
                        'efektivitas_desain_pengendalian_akhir' => $getValue('efektivitas_desain_pengendalian_akhir'),

                        'kesimpulan_akhir' => $getValue('kesimpulan_akhir'),
                        'hasil_temuan' => $getValue('hasil_temuan'),
                        'rencana_tindak_lanjut' => $getValue('rencana_tindak_lanjut'),
                        'batas_waktu_penyelesaian' => $getValue('batas_waktu_penyelesaian'),

                        'penanggung_jawab' => $penanggungJawabName,
                        'penanggung_jawab_jabatan_id' => $jabatanId,
                    ]
                );
            }

            // Jika action submit, kita bisa update status parent
            if ($action !== 'draft') {
                // Optional: Update status ICTPlan jika semua sudah diisi
                // ICTPlan::where('id', $id)->update(['status' => 'pending_approval']);
            }

            DB::commit();

            $message = ($action === 'draft')
                ? 'Draft pengujian berhasil diperbarui.'
                : 'Data pengujian ICT Plan berhasil disimpan.';

            return redirect()->route('ict.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function report($id)
    {
        // Ambil data ICTPlan dengan relasi planControls
        $ictPlan = ICTPlan::with(['planControls.dos'])->findOrFail($id);

        $riskDetail = $this->resolveRiskDetail($ictPlan);
        $peristiwaRisiko = $riskDetail['peristiwa_risiko'];
        $lokasiRisiko = $riskDetail['lokasi_risiko'];

        // Ambil data ICTReport jika sudah ada
        $ictReport = ICTReport::where('ict_plan_id', $id)->first();

        // Buat rangkuman dari keterangan ICTPlan dan hasil temuan pelaksanaan
        $rangkuman = '';

        // Tambahkan keterangan dari ICTPlan
        $rangkuman .= "Sasaran BUMN: {$ictPlan->sasaran_bumn}\n";
        $rangkuman .= "Business Process/Peristiwa Risiko: {$ictPlan->business_process}\n";
        $rangkuman .= "Metode Pengujian: {$ictPlan->metode_pengujian}\n\n";

        // Tambahkan hasil temuan dari semua key control
        $rangkuman .= "Hasil Temuan Pelaksanaan:\n";
        foreach ($ictPlan->planControls as $index => $planControl) {
            // Ambil data ICTDo terbaru untuk key control ini
            $ictDo = $planControl->dos()->latest()->first();
            if ($ictDo) {
                $rangkuman .= "Key Control #{$index}: {$planControl->key_control}\n";
                $rangkuman .= "Kesimpulan: {$ictDo->kesimpulan_akhir}\n";
                $rangkuman .= "Hasil Temuan: {$ictDo->hasil_temuan}\n\n";
            }
        }

        return view('ict.report', compact('ictPlan', 'peristiwaRisiko', 'lokasiRisiko', 'ictReport', 'rangkuman'));
    }

    public function storeReport(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'status_tindak_lanjut' => 'required',
            'rencana_tindak_lanjut' => 'required',
            'realisasi_tindak_lanjut' => 'required',
        ]);

        // Cek apakah sudah ada report untuk ICTPlan ini
        $ictReport = ICTReport::where('ict_plan_id', $id)->first();

        if ($ictReport) {
            // Update report yang sudah ada
            $ictReport->update([
                'status_tindak_lanjut' => $request->status_tindak_lanjut,
                'keterangan' => $request->rencana_tindak_lanjut,
                'realisasi_tindak_lanjut' => $request->realisasi_tindak_lanjut,
            ]);
        } else {
            // Buat report baru
            ICTReport::create([
                'ict_plan_id' => $id,
                'status_tindak_lanjut' => $request->status_tindak_lanjut,
                'keterangan' => $request->rencana_tindak_lanjut,
                'realisasi_tindak_lanjut' => $request->realisasi_tindak_lanjut,
            ]);
        }

        return redirect()->route('ict.index')->with('success', 'Laporan ICT berhasil disimpan');
    }

    public function show($id)
    {
        // Ambil data ICTPlan dengan relasi planControls dan dos
        $ictPlan = ICTPlan::with(['planControls.dos'])->findOrFail($id);

        $riskDetail = $this->resolveRiskDetail($ictPlan);
        $peristiwaRisiko = $riskDetail['peristiwa_risiko'];
        $lokasiRisiko = $riskDetail['lokasi_risiko'];

        // Ambil data ICTReport jika sudah ada
        $ictReport = ICTReport::where('ict_plan_id', $id)->first();

        return view('ict.show', compact('ictPlan', 'peristiwaRisiko', 'lokasiRisiko', 'ictReport'));
    }

    private function resolveRiskDetail(ICTPlan $plan): array
    {
        if (!empty($plan->peristiwa_risiko)) {
            return [
                'peristiwa_risiko' => $plan->peristiwa_risiko,
                'lokasi_risiko' => $plan->lokasi_risiko ?: '-',
            ];
        }

        $peristiwaRisiko = '-';
        $lokasiRisiko = $plan->lokasi_risiko ?: '-';

        if ($plan->type == 1 && !empty($plan->risiko_id)) {
            $identifikasiRisiko = IdentifikasiRisiko::find($plan->risiko_id);
            if ($identifikasiRisiko) {
                $peristiwaRisiko = $identifikasiRisiko->peristiwa_risiko;
                if (empty($plan->lokasi_risiko)) {
                    $unit = Unit::find($identifikasiRisiko->unit_id);
                    $lokasiRisiko = $unit ? $unit->name : '-';
                }
            }
        } elseif ($plan->type == 2 && !empty($plan->risiko_id)) {
            $projectRisk = ProjectRisk::find($plan->risiko_id);
            if ($projectRisk) {
                $peristiwaRisikoObj = PeristiwaRisiko::find($projectRisk->peristiwa_risiko_id);
                $peristiwaRisiko = $peristiwaRisikoObj ? $peristiwaRisikoObj->title : '-';
                if (empty($plan->lokasi_risiko)) {
                    $project = Project::find($projectRisk->project_id);
                    $lokasiRisiko = $project ? $project->name : '-';
                }
            }
        }

        return [
            'peristiwa_risiko' => $peristiwaRisiko,
            'lokasi_risiko' => $lokasiRisiko ?: '-',
        ];
    }

    public function destroy($id)
    {
        // Ambil data ICTPlan
        $ictPlan = ICTPlan::findOrFail($id);

        // Mulai transaksi database untuk memastikan semua operasi berhasil atau gagal bersama
        \DB::beginTransaction();

        try {
            // 1. Hapus semua ICTReport terkait
            ICTReport::where('ict_plan_id', $id)->delete();

            // 2. Ambil semua ICTPlanControl terkait
            $planControls = ICTPlanControl::where('ict_plan_id', $id)->get();

            // 3. Untuk setiap ICTPlanControl, hapus ICTDo terkait
            foreach ($planControls as $planControl) {
                ICTDo::where('plan_control_id', $planControl->id)->delete();
            }

            // 4. Hapus semua ICTPlanControl terkait
            ICTPlanControl::where('ict_plan_id', $id)->delete();

            // 5. Terakhir, hapus ICTPlan
            $ictPlan->delete();

            // Commit transaksi jika semua operasi berhasil
            \DB::commit();

            return redirect()->route('ict.index')->with('success', 'ICT Plan beserta data terkait berhasil dihapus');
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi kesalahan
            \DB::rollback();

            return redirect()->route('ict.index')->with('error', 'Terjadi kesalahan saat menghapus ICT Plan: ' . $e->getMessage());
        }
    }

    public function submitAll(Request $request)
    {
        $this->authorize('ict_input');

        ICTPlan::whereIn('status', ['draft', 'rejected'])->update([
            'status' => 'pending_approval',
            // 'rejection_reason' => null
        ]);

        return redirect()->route('ict.index')->with('success', 'Semua data ICT Plan berhasil dikirim untuk verifikasi.');
    }

    public function approveAll(Request $request)
    {
        $this->authorize('ict_approval');

        $plansToApprove = ICTPlan::where('status', 'pending_approval')->pluck('sasaran_bumn');

        if ($plansToApprove->isEmpty()) {
            return redirect()->route('ict.index')->with('error', 'Tidak ada data yang perlu disetujui saat ini.');
        }

        $approvedCount = $plansToApprove->count();
        ICTPlan::where('status', 'pending_approval')->update(['status' => 'approved']);
        $approvedItemsString = $plansToApprove->implode(', ');
        $successMessage = "Berhasil menyetujui {$approvedCount} data ICT Plan: {$approvedItemsString}.";

        return redirect()->route('ict.index')->with('success', $successMessage);
    }

    public function rejectAll(Request $request)
    {
        $this->authorize('ict_approval');

        $plansToReject = ICTPlan::where('status', 'pending_approval')->pluck('sasaran_bumn');

        if ($plansToReject->isEmpty()) {
            return redirect()->route('ict.index')->with('error', 'Tidak ada data yang perlu ditolak saat ini.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|min:10',
        ]);

        $rejectedCount = $plansToReject->count();
        ICTPlan::where('status', 'pending_approval')->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);
        $rejectedItemsString = $plansToReject->implode(', ');
        $successMessage = "Berhasil menolak {$rejectedCount} data ICT Plan: {$rejectedItemsString}.";

        return redirect()->route('ict.index')->with('success', $successMessage);
    }
}
