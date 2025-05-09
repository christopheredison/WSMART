<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Project;
use App\Models\LossEventProject;

class LossEventProjectImportController extends Controller
{
    /**
     * POST /api/loss-event-projects/import
     */
    public function __invoke(Request $request)
    {
        // 1. Validasi file Excel
        $request->validate([
            'file' => 'required|file|mimes:xls,xlsx'
        ]);

        // 2. Load spreadsheet
        $path       = $request->file('file')->getRealPath();
        $spreadsheet= IOFactory::load($path);
        $sheet      = $spreadsheet->getActiveSheet();

        // 3. Ambil seluruh baris menjadi array, menggunakan kolom A–F
        //    baris 1 dianggap header: Kode, Taksonomi ID, ... Jumlah Kejadian
        $rows = $sheet->toArray(null, true, true, true);

        foreach ($rows as $idx => $row) {
            if ($idx === 1) {
                // lewati header
                continue;
            }

            // 4. Map kolom ke variabel
            $kode                   = trim($row['A'] ?? '');
            $taksonomiId            = $row['C'] ?? null;
            $konstruksiSpesifikId   = $row['E'] ?? null;
            $deskripsi              = $row['G'] ?? null;
            $tahun                  = $row['H'] ?? null;
            //$jumlahKejadian         = $row['I'] ?? null;

            // raw value
            $rawJumlah = trim($row['I'] ?? '');
            $jumlahKejadian = 0;
            // bersihkan: jika bukan angka, ubah ke null (atau 0)
            if (is_numeric($rawJumlah)) {
                $jumlahKejadian = (int) $rawJumlah;
            } else {
                $jumlahKejadian = 0;
            }

            if ($kode === '') {
                // lewati baris kosong
                continue;
            }

            // 5. Cari Project berdasarkan kode
            $project = Project::where('project_code', $kode)->first();
            if (! $project) {
                // (opsional) catat ke log jika tidak ditemukan
                \Log::warning("Project dengan code “{$kode}” tidak ditemukan, baris {$idx} di-skip.");
                continue;
            }

            // 6. Simpan ke tabel loss_event_projects
            LossEventProject::updateOrCreate(
                [
                  'project_id'             => $project->id,
                  'peristiwa_risiko_id'    => $taksonomiId,
                  'project_sektor_id'      => $konstruksiSpesifikId,
                  'tahun'                  => $tahun,
                ],
                [
                  'deskripsi_kejadian'     => $deskripsi,
                  'jumlah_kejadian'        => $jumlahKejadian,
                ]
            );
        }

        return response()->json(['message' => 'Import selesai'], 200);
    }
}
