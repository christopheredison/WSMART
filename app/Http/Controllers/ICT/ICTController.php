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

class ICTController extends Controller
{
    public function index()
    {
        // Ambil data ICTPlan dengan relasi planControls
        $ictPlans = ICTPlan::with(['planControls'])->get();
        
        // Data untuk tampilan
        $data = [];
        
        foreach ($ictPlans as $plan) {
            $peristiwaRisiko = '-';
            $lokasiRisiko = '-';
            
            // Tentukan peristiwa risiko berdasarkan type
            if ($plan->type == 1) {
                // Ambil dari IdentifikasiRisiko
                $identifikasiRisiko = IdentifikasiRisiko::find($plan->risiko_id);
                if ($identifikasiRisiko) {
                    $peristiwaRisiko = $identifikasiRisiko->peristiwa_risiko;
                    $unit = Unit::find($identifikasiRisiko->unit_id);
                    $lokasiRisiko = $unit ? $unit->name : '-';
                }
            } elseif ($plan->type == 2) {
                // Ambil dari ProjectRisk
                $projectRisk = ProjectRisk::find($plan->risiko_id);
                if ($projectRisk) {
                    $peristiwaRisikoObj = PeristiwaRisiko::find($projectRisk->peristiwa_risiko_id);
                    $peristiwaRisiko = $peristiwaRisikoObj ? $peristiwaRisikoObj->title : '-';
                    $project = Project::find($projectRisk->project_id);
                    $lokasiRisiko = $project ? $project->name : '-';
                }
            }
            
            // Ambil key controls
            $keyControls = $plan->planControls->pluck('key_control')->implode(', ');
            
            $data[] = [
                'id' => $plan->id,
                'sasaran_bumn' => $plan->sasaran_bumn,
                'peristiwa_risiko' => $peristiwaRisiko,
                'lokasi_risiko' => $lokasiRisiko,
                'business_process' => $plan->business_process,
                'key_controls' => $keyControls,
                'metode_pengujian' => $plan->metode_pengujian,
            ];
        }
        
        return view('ict.index', compact('data'));
    }

    public function create()
    {
        // Data untuk dropdown type
        $types = [
            1 => 'Unit',
            2 => 'Proyek'
        ];
        
        // Data untuk dropdown peristiwa risiko unit
        $identifikasiRisikos = IdentifikasiRisiko::select('id', 'peristiwa_risiko')->get();
        
        // Data untuk dropdown peristiwa risiko proyek
        $projectRisks = ProjectRisk::with('peristiwaRisiko')->get();
        
        return view('ict.create', compact('types', 'identifikasiRisikos', 'projectRisks'));
    }
    
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'sasaran_bumn' => 'required',
            'type' => 'required|in:1,2',
            'risiko_id' => 'required',
            'business_process' => 'required',
            'metode_pengujian' => 'required',
            'key_control_id' => 'required|array',
            'key_control' => 'required|array',
        ]);
        
        // Simpan data ICTPlan
        $ictPlan = ICTPlan::create([
            'sasaran_bumn' => $request->sasaran_bumn,
            'risiko_id' => $request->risiko_id,
            'type' => $request->type,
            'business_process' => $request->business_process,
            'metode_pengujian' => $request->metode_pengujian,
        ]);
        
        // Simpan data ICTPlanControl
        foreach ($request->key_control_id as $index => $keyControlId) {
            ICTPlanControl::create([
                'ict_plan_id' => $ictPlan->id,
                'key_control_id' => $keyControlId,
                'key_control' => $request->key_control[$index],
            ]);
        }
        
        return redirect()->route('ict.index')->with('success', 'Data ICT Plan berhasil disimpan');
    }

    public function testing($id)
    {
        // Ambil data ICTPlan dengan relasi planControls
        $ictPlan = ICTPlan::with(['planControls'])->findOrFail($id);
        
        // Tentukan peristiwa risiko dan lokasi risiko berdasarkan type
        $peristiwaRisiko = '-';
        $lokasiRisiko = '-';
        
        if ($ictPlan->type == 1) {
            // Ambil dari IdentifikasiRisiko
            $identifikasiRisiko = IdentifikasiRisiko::find($ictPlan->risiko_id);
            if ($identifikasiRisiko) {
                $peristiwaRisiko = $identifikasiRisiko->peristiwa_risiko;
                $unit = Unit::find($identifikasiRisiko->unit_id);
                $lokasiRisiko = $unit ? $unit->name : '-';
            }
        } elseif ($ictPlan->type == 2) {
            // Ambil dari ProjectRisk
            $projectRisk = ProjectRisk::find($ictPlan->risiko_id);
            if ($projectRisk) {
                $peristiwaRisikoObj = PeristiwaRisiko::find($projectRisk->peristiwa_risiko_id);
                $peristiwaRisiko = $peristiwaRisikoObj ? $peristiwaRisikoObj->title : '-';
                $project = Project::find($projectRisk->project_id);
                $lokasiRisiko = $project ? $project->name : '-';
            }
        }
        
        return view('ict.pelaksanaan', compact('ictPlan', 'peristiwaRisiko', 'lokasiRisiko'));
    }

    public function storeTesting(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'plan_control_id' => 'required|array',
            'jenis_kontrol' => 'required|array',
            'bentuk_kontrol' => 'required|array',
            'level_pengendalian' => 'required|array',
            'kecukupan_desain_pengendalian_1' => 'required|array',
            'kecukupan_desain_pengendalian_2' => 'required|array',
            'kecukupan_desain_pengendalian_3' => 'required|array',
            'kecukupan_desain_pengendalian_4' => 'required|array',
            'kecukupan_desain_pengendalian_akhir' => 'required|array',
            'efektivitas_desain_pengendalian_1' => 'required|array',
            'efektivitas_desain_pengendalian_2' => 'required|array',
            'efektivitas_desain_pengendalian_3' => 'required|array',
            'efektivitas_desain_pengendalian_akhir' => 'required|array',
            'kesimpulan_akhir' => 'required|array',
            'hasil_temuan' => 'required|array',
            'rencana_tindak_lanjut' => 'required|array',
            'batas_waktu_penyelesaian' => 'required|array',
            'penanggung_jawab' => 'required|array',
        ]);
        
        // Simpan data ICTDo untuk setiap key control
        foreach ($request->plan_control_id as $index => $planControlId) {
            ICTDo::create([
                'plan_control_id' => $planControlId,
                'jenis_kontrol' => $request->jenis_kontrol[$index],
                'bentuk_kontrol' => $request->bentuk_kontrol[$index],
                'level_pengendalian' => $request->level_pengendalian[$index],
                'kecukupan_desain_pengendalian_1' => $request->kecukupan_desain_pengendalian_1[$index],
                'kecukupan_desain_pengendalian_2' => $request->kecukupan_desain_pengendalian_2[$index],
                'kecukupan_desain_pengendalian_3' => $request->kecukupan_desain_pengendalian_3[$index],
                'kecukupan_desain_pengendalian_4' => $request->kecukupan_desain_pengendalian_4[$index],
                'kecukupan_desain_pengendalian_akhir' => $request->kecukupan_desain_pengendalian_akhir[$index],
                'efektivitas_desain_pengendalian_1' => $request->efektivitas_desain_pengendalian_1[$index],
                'efektivitas_desain_pengendalian_2' => $request->efektivitas_desain_pengendalian_2[$index],
                'efektivitas_desain_pengendalian_3' => $request->efektivitas_desain_pengendalian_3[$index],
                'efektivitas_desain_pengendalian_akhir' => $request->efektivitas_desain_pengendalian_akhir[$index],
                'kesimpulan_akhir' => $request->kesimpulan_akhir[$index],
                'hasil_temuan' => $request->hasil_temuan[$index],
                'rencana_tindak_lanjut' => $request->rencana_tindak_lanjut[$index],
                'batas_waktu_penyelesaian' => $request->batas_waktu_penyelesaian[$index],
                'penanggung_jawab' => $request->penanggung_jawab[$index],
            ]);
        }
        
        return redirect()->route('ict.index')->with('success', 'Data pengujian ICT Plan berhasil disimpan');
    }

    public function report($id)
    {
        // Ambil data ICTPlan dengan relasi planControls
        $ictPlan = ICTPlan::with(['planControls.dos'])->findOrFail($id);
        
        // Tentukan peristiwa risiko dan lokasi risiko berdasarkan type
        $peristiwaRisiko = '-';
        $lokasiRisiko = '-';
        
        if ($ictPlan->type == 1) {
            // Ambil dari IdentifikasiRisiko
            $identifikasiRisiko = IdentifikasiRisiko::find($ictPlan->risiko_id);
            if ($identifikasiRisiko) {
                $peristiwaRisiko = $identifikasiRisiko->peristiwa_risiko;
                $unit = Unit::find($identifikasiRisiko->unit_id);
                $lokasiRisiko = $unit ? $unit->name : '-';
            }
        } elseif ($ictPlan->type == 2) {
            // Ambil dari ProjectRisk
            $projectRisk = ProjectRisk::find($ictPlan->risiko_id);
            if ($projectRisk) {
                $peristiwaRisikoObj = PeristiwaRisiko::find($projectRisk->peristiwa_risiko_id);
                $peristiwaRisiko = $peristiwaRisikoObj ? $peristiwaRisikoObj->title : '-';
                $project = Project::find($projectRisk->project_id);
                $lokasiRisiko = $project ? $project->name : '-';
            }
        }
        
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
            'keterangan' => 'required',
        ]);
        
        // Cek apakah sudah ada report untuk ICTPlan ini
        $ictReport = ICTReport::where('ict_plan_id', $id)->first();
        
        if ($ictReport) {
            // Update report yang sudah ada
            $ictReport->update([
                'status_tindak_lanjut' => $request->status_tindak_lanjut,
                'keterangan' => $request->keterangan,
            ]);
        } else {
            // Buat report baru
            ICTReport::create([
                'ict_plan_id' => $id,
                'status_tindak_lanjut' => $request->status_tindak_lanjut,
                'keterangan' => $request->keterangan,
            ]);
        }
        
        return redirect()->route('ict.index')->with('success', 'Laporan ICT berhasil disimpan');
    }

    public function show($id)
    {
        // Ambil data ICTPlan dengan relasi planControls dan dos
        $ictPlan = ICTPlan::with(['planControls.dos'])->findOrFail($id);
        
        // Tentukan peristiwa risiko dan lokasi risiko berdasarkan type
        $peristiwaRisiko = '-';
        $lokasiRisiko = '-';
        
        if ($ictPlan->type == 1) {
            // Ambil dari IdentifikasiRisiko
            $identifikasiRisiko = IdentifikasiRisiko::find($ictPlan->risiko_id);
            if ($identifikasiRisiko) {
                $peristiwaRisiko = $identifikasiRisiko->peristiwa_risiko;
                $unit = Unit::find($identifikasiRisiko->unit_id);
                $lokasiRisiko = $unit ? $unit->name : '-';
            }
        } elseif ($ictPlan->type == 2) {
            // Ambil dari ProjectRisk
            $projectRisk = ProjectRisk::find($ictPlan->risiko_id);
            if ($projectRisk) {
                $peristiwaRisikoObj = PeristiwaRisiko::find($projectRisk->peristiwa_risiko_id);
                $peristiwaRisiko = $peristiwaRisikoObj ? $peristiwaRisikoObj->title : '-';
                $project = Project::find($projectRisk->project_id);
                $lokasiRisiko = $project ? $project->name : '-';
            }
        }
        
        // Ambil data ICTReport jika sudah ada
        $ictReport = ICTReport::where('ict_plan_id', $id)->first();
        
        return view('ict.show', compact('ictPlan', 'peristiwaRisiko', 'lokasiRisiko', 'ictReport'));
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
}
