<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification; // Sesuaikan jika namespace model Notification Anda berbeda
use App\Models\ProjectRisk;

class FixNotificationRequestEdit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:fix-request-edit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memperbaiki pesan notifikasi Request Edit Risiko lama agar menyertakan nama proyek.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Memulai pembaruan data notifikasi...");

        // Ambil semua notifikasi dengan judul 'Request Edit Risiko'
        $notifications = Notification::where('title', 'Request Edit Risiko')->get();
        $count = 0;

        foreach ($notifications as $notif) {
            // Ekstrak risk_id dari link query parameter ?verify_request_edit=xxx
            $urlParts = parse_url($notif->link);

            if (isset($urlParts['query'])) {
                parse_str($urlParts['query'], $query);

                if (isset($query['verify_request_edit'])) {
                    $riskId = $query['verify_request_edit'];
                    $risk = ProjectRisk::with(['peristiwaRisiko', 'project'])->find($riskId);

                    if ($risk) {
                        // Ekstrak alasan dari pesan notifikasi lama (untuk menjaga reason aslinya)
                        $alasan = '';
                        if (preg_match('/Alasan:\s*(.*)/', $notif->message, $matches)) {
                            $alasan = $matches[1];
                        } else {
                            $alasan = $risk->request_edit_reason ?? '-';
                        }

                        $riskName = $risk->peristiwa_risiko_id == 0
                            ? $risk->rencana_kegiatan
                            : ($risk->peristiwaRisiko->title ?? 'Risiko Proyek');

                        $projectName = $risk->project->project_name ?? 'Proyek Tidak Diketahui';

                        // Format pesan baru
                        $newMessage = 'Risk Officer Proyek mengajukan request edit untuk risiko (' . $riskName . ') pada proyek ' . $projectName . '. Alasan: ' . $alasan;

                        // Simpan pembaruan
                        $notif->update(['message' => $newMessage]);
                        $count++;
                    }
                }
            }
        }

        $this->info("Selesai! Berhasil memperbarui {$count} notifikasi.");
    }
}
