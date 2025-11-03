<?php

namespace Database\Seeders;

use App\Models\Notification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Membuat notifikasi dummy untuk user dengan id = 1
        $notifications = [
            [
                'user_id' => 1,
                'title' => 'Risiko Baru',
                'message' => 'Risiko baru telah ditambahkan pada proyek Pembangunan Gedung A',
                'icon' => '📝',
                'link' => '/risks/view/1',
                'created_at' => now()->subMinutes(10),
            ],
            [
                'user_id' => 1,
                'title' => 'KRI Melewati Threshold',
                'message' => 'KRI Proyek X telah melewati batas threshold',
                'icon' => '⚠️',
                'link' => '/kri/view/1',
                'created_at' => now()->subHours(1),
            ],
            [
                'user_id' => 1,
                'title' => 'Laporan Diperbarui',
                'message' => 'Laporan risiko Divisi Infrastruktur telah diperbarui',
                'icon' => '📊',
                'link' => '/reports/view/1',
                'created_at' => now()->subDays(1),
                'read_at' => now()->subHours(12),
            ],
            [
                'user_id' => 1,
                'title' => 'Mitigasi Risiko',
                'message' => 'Tindakan mitigasi risiko telah diperbarui pada Proyek Y',
                'icon' => '🛡️',
                'link' => '/risks/mitigation/1',
                'created_at' => now()->subDays(2),
            ],
            [
                'user_id' => 1,
                'title' => 'Evaluasi Risiko',
                'message' => 'Evaluasi risiko triwulan telah selesai',
                'icon' => '📋',
                'link' => '/evaluation/view/1',
                'created_at' => now()->subDays(3),
                'read_at' => now()->subDays(2),
            ],
            [
                'user_id' => 1,
                'title' => 'Persetujuan Diperlukan',
                'message' => 'Dokumen risiko memerlukan persetujuan Anda',
                'icon' => '✅',
                'link' => '/approval/view/1',
                'created_at' => now()->subDays(4),
            ],
            [
                'user_id' => 1,
                'title' => 'Pengingat Deadline',
                'message' => 'Deadline untuk pengisian risiko proyek akan berakhir besok',
                'icon' => '⏰',
                'link' => '/projects/risks/1',
                'created_at' => now()->subDays(5),
            ],
            [
                'user_id' => 1,
                'title' => 'Perubahan Status Risiko',
                'message' => 'Status risiko pada Proyek Z telah berubah menjadi "Mitigasi"',
                'icon' => '🔄',
                'link' => '/risks/status/1',
                'created_at' => now()->subDays(6),
                'read_at' => now()->subDays(5),
            ],
            [
                'user_id' => 1,
                'title' => 'Laporan Bulanan',
                'message' => 'Laporan bulanan risiko telah tersedia untuk diunduh',
                'icon' => '📥',
                'link' => '/reports/download/1',
                'created_at' => now()->subDays(7),
            ],
            [
                'user_id' => 1,
                'title' => 'Pembaruan Sistem',
                'message' => 'Sistem manajemen risiko telah diperbarui ke versi terbaru',
                'icon' => '🔄',
                'link' => '/system/updates',
                'created_at' => now()->subDays(8),
                'read_at' => now()->subDays(7),
            ],
        ];

        foreach ($notifications as $notification) {
            Notification::create($notification);
        }
    }
}