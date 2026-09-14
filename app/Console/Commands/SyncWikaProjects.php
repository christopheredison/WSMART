<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Project;
use App\Models\Unit;
use App\Models\ProjectPeriodeList;
use App\Supports\ApiWika;
use Carbon\Carbon;

class SyncWikaProjects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:sync-wika';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi otomatis data project dari API WIKA';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Gunakan channel log khusus (opsional, jika tidak ada akan pakai default)
        $logChannel = Log::channel('sync_wika_log');
        $logChannel->info('[Scheduler Sync WIKA] Proses sinkronisasi dimulai.');
        $this->info('Mulai menarik data dari API WIKA...');

        @set_time_limit(600); // Set time limit karena proses mungkin memakan waktu
        $apiWika = new ApiWika();

        try {
            $projectDatas = $apiWika->getProjects();
        } catch (\Exception $e) {
            $logChannel->error('[Scheduler Sync WIKA] Gagal mengambil data: ' . $e->getMessage());
            $this->error('Gagal mengambil data project: ' . $e->getMessage());
            return self::FAILURE;
        }

        if (empty($projectDatas)) {
            $logChannel->info('[Scheduler Sync WIKA] Data Project dari API WIKA kosong.');
            $this->info('Data Project dari API WIKA kosong.');
            return self::SUCCESS;
        }

        $countUpdated = 0;
        $failedProjects = [];

        foreach ($projectDatas as $projectData) {
            DB::beginTransaction();

            try {
                $profitCenter = $projectData['profit_center'] ?? null;
                $kodeSpk = $projectData['kode_spk'] ?? '-';
                $namaSpk = $projectData['nama_spk_full'] ?? ($projectData['project_name'] ?? '-');

                // 1. CEK UNIT / DIVISI TERLEBIH DAHULU
                $divisiUnit = null;
                $costCenterParent = $projectData['divisisap'] ?? null;

                if (!empty($costCenterParent)) {
                    $divisiUnit = Unit::where('cost_center', $costCenterParent)->first();
                }

                if (!$divisiUnit) {
                    throw new \Exception("Unit/Divisi dengan Cost Center '{$costCenterParent}' tidak ditemukan di sistem.");
                }

                $nilaiKontrak = ['nk' => 0, 'nilai_ok_porsi' => 0];
                if ($profitCenter) {
                    $nilaiKontrak = $this->fetchNilaiKontrakRecursive($apiWika, $profitCenter);
                }

                $nkTotal = (float) $nilaiKontrak['nk'];
                $jenisKontrakName = empty($projectData['jenis_kontrak_name']) ? '' : (is_array($projectData['jenis_kontrak_name']) ? implode(', ', $projectData['jenis_kontrak_name']) : $projectData['jenis_kontrak_name']);
                $pembayaranName = empty($projectData['pembayaran_name']) ? '' : (is_array($projectData['pembayaran_name']) ? implode(', ', $projectData['pembayaran_name']) : $projectData['pembayaran_name']);

                $persentase = $this->hitungPersentaseBatasanBiaya($jenisKontrakName, $pembayaranName);
                $batasanBiaya = $nkTotal * $persentase;

                // Simpan / Update Project
                $project = Project::updateOrCreate([
                    'project_code' => $kodeSpk,
                ], [
                    'project_name'   => $namaSpk,
                    'type'           => Project::TYPE_HAS_RKB_RKN,
                    'project_status' => 1,
                    'profit_center'  => $profitCenter,
                    'nk'             => $nkTotal,
                    'nilai_ok_porsi' => $nilaiKontrak['nilai_ok_porsi'],
                    'batasan_biaya_perlakuan_risiko' => $batasanBiaya,
                    'cost_center_parent' => $costCenterParent,
                    'masa_pelaksanaan_start' => $projectData['tgl_mulai'] ?? null,
                    'masa_pelaksanaan_end' => !empty($projectData['tgl_selesai'])
                        ? Carbon::parse($projectData['tgl_selesai'])->addDays((int) \App\Models\GlobalSetting::getValue('project_status_threshold_days', 45))->toDateString()
                        : null,
                    'tanggal_mulai' => $projectData['tanggal_mulai'] ?? null,
                    'meta'           => $projectData,
                ]);

                // Update Periode List
                $projectPeriodeList = ProjectPeriodeList::updateOrCreate([
                    'project_id' => $project->id,
                    'periode_id' => null,
                ], [
                    'unit_id' => $divisiUnit->id,
                ]);

                // Recalculate Risks
                if ($projectPeriodeList) {
                    $projectPeriodeList->recalculateAllRisks();
                }

                DB::commit();
                $countUpdated++;

            } catch (\Exception $e) {
                DB::rollBack();
                $failedProjects[] = [
                    'kode' => $projectData['kode_spk'] ?? '-',
                    'nama' => $projectData['nama_spk_full'] ?? ($projectData['project_name'] ?? '-'),
                    'alasan' => $e->getMessage()
                ];
            }
        }

        // Tulis laporan ke Log
        $messageSummary = sprintf("Sinkronisasi Selesai. Berhasil: %d, Gagal: %d.", $countUpdated, count($failedProjects));
        $logChannel->info('[Scheduler Sync WIKA] ' . $messageSummary);
        $this->info($messageSummary);

        // Jika ada yang gagal, tulis detail gagalnya ke log
        if (count($failedProjects) > 0) {
            $logChannel->warning('[Scheduler Sync WIKA] Terdapat project yang gagal disinkronisasi:', $failedProjects);
        }

        return self::SUCCESS;
    }

    /**
     * Copy dari ProjectController: fetchNilaiKontrakRecursive
     */
    private function fetchNilaiKontrakRecursive($apiWika, $profitCenter)
    {
        $dateCheck = Carbon::now();
        $maxRetries = 10;

        for ($i = 0; $i < $maxRetries; $i++) {
            $currentPeriod = $dateCheck->format('Ym');

            try {
                $response = $apiWika->getHasilUsahaProject($currentPeriod, $profitCenter);

                if (isset($response['status']) && $response['status'] && isset($response['data'])) {
                    $data = $response['data'];
                    $statusAutorisasi = $data['status_autorisasi'] ?? 'OPEN';
                    $kontrakReviewTotal = $data['hasil_usaha']['kontrak_review_total'] ?? 0;
                    $kontrakReviewPorsi = $data['hasil_usaha']['kontrak_review'] ?? 0;

                    if ($statusAutorisasi === 'AUTORISASI' && ($kontrakReviewTotal !== 0 || $kontrakReviewPorsi !== 0)) {
                        return [
                            'nk' => (float) $kontrakReviewTotal,
                            'nilai_ok_porsi' => (float) $kontrakReviewPorsi,
                        ];
                    }
                }
            } catch (\Exception $e) {
                // Abaikan error api per-bulan, lanjut loop
            }

            $dateCheck->subMonth();
        }

        return [
            'nk' => 0,
            'nilai_ok_porsi' => 0
        ];
    }

    /**
     * Copy dari ProjectController: hitungPersentaseBatasanBiaya
     */
    private function hitungPersentaseBatasanBiaya($jenisKontrak, $caraPembayaran)
    {
        $kontrak = strtolower(trim($jenisKontrak));
        $bayar = strtolower(trim($caraPembayaran));

        $persentase = 0.0;

        if (str_contains($kontrak, 'lumpsum') || str_contains($kontrak, 'lump sum')) {
            if (str_contains($bayar, 'monthly')) $persentase = 1.0;
            elseif (str_contains($bayar, 'milestone')) $persentase = 1.25;
            elseif (str_contains($bayar, 'cpf') || str_contains($bayar, 'turn key') || str_contains($bayar, 'turnkey')) $persentase = 1.50;
        } elseif (str_contains($kontrak, 'mix') || str_contains($kontrak, 'gabungan')) {
            if (str_contains($bayar, 'monthly')) $persentase = 0.75;
            elseif (str_contains($bayar, 'milestone')) $persentase = 1.0;
            elseif (str_contains($bayar, 'cpf') || str_contains($bayar, 'turn key') || str_contains($bayar, 'turnkey')) $persentase = 1.25;
        } elseif (str_contains($kontrak, 'cost-plus') || str_contains($kontrak, 'cost plus')) {
            if (str_contains($bayar, 'monthly')) $persentase = 0.13;
            elseif (str_contains($bayar, 'milestone')) $persentase = 0.25;
            elseif (str_contains($bayar, 'cpf') || str_contains($bayar, 'turn key') || str_contains($bayar, 'turnkey')) $persentase = 0.50;
        } elseif (str_contains($kontrak, 'o & m') || str_contains($kontrak, 'o&m') || str_contains($kontrak, 'operasional')) {
            if (str_contains($bayar, 'monthly')) $persentase = 0.25;
            elseif (str_contains($bayar, 'milestone')) $persentase = 0.50;
            elseif (str_contains($bayar, 'cpf') || str_contains($bayar, 'turn key') || str_contains($bayar, 'turnkey')) $persentase = 0.75;
        } elseif (str_contains($kontrak, 'unit price') || str_contains($kontrak, 'harga satuan')) {
            if (str_contains($bayar, 'monthly')) $persentase = 0.25;
            elseif (str_contains($bayar, 'milestone')) $persentase = 0.50;
            elseif (str_contains($bayar, 'cpf') || str_contains($bayar, 'turn key') || str_contains($bayar, 'turnkey')) $persentase = 0.75;
        }

        return $persentase / 100;
    }
}
