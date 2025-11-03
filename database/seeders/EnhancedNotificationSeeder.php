<?php

namespace Database\Seeders;

use App\Models\Notification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EnhancedNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Hapus notifikasi yang ada terlebih dahulu
        DB::table('notifications')->truncate();

        // Membuat notifikasi dengan data yang lebih detail
        $notifications = [
            [
                'user_id' => 1,
                'title' => 'Risiko Baru',
                'message' => 'Risiko baru telah ditambahkan pada proyek Pembangunan Gedung A dengan tingkat keparahan tinggi',
                'type' => 'risiko',
                'severity' => 'high',
                'color' => 'danger',
                'icon' => 'bx bx-error-circle',
                'link' => '/risks/view/1',
                'created_at' => now()->subMinutes(10),
            ],
            [
                'user_id' => 1,
                'title' => 'KRI Melewati Threshold',
                'message' => 'KRI Proyek X telah melewati batas threshold sebesar 85% dari target yang ditetapkan',
                'type' => 'kri',
                'severity' => 'medium',
                'color' => 'warning',
                'icon' => 'bx bx-line-chart',
                'link' => '/kri/view/1',
                'created_at' => now()->subHours(1),
            ],
            [
                'user_id' => 1,
                'title' => 'Laporan Diperbarui',
                'message' => 'Laporan risiko Divisi Infrastruktur telah diperbarui dengan 5 risiko baru teridentifikasi',
                'type' => 'laporan',
                'severity' => 'low',
                'color' => 'info',
                'icon' => 'bx bx-file',
                'link' => '/reports/view/1',
                'created_at' => now()->subDays(1),
                'read_at' => now()->subHours(12),
            ],
            [
                'user_id' => 1,
                'title' => 'Mitigasi Risiko',
                'message' => 'Tindakan mitigasi risiko "Keterlambatan Material" telah selesai dilaksanakan dengan efektivitas 90%',
                'type' => 'mitigasi',
                'severity' => 'medium',
                'color' => 'success',
                'icon' => 'bx bx-check-shield',
                'link' => '/risks/mitigation/1',
                'created_at' => now()->subDays(2),
            ],
            [
                'user_id' => 1,
                'title' => 'Evaluasi Risiko',
                'message' => 'Evaluasi risiko triwulan untuk Unit Bisnis D telah selesai dengan skor risiko keseluruhan 78/100',
                'type' => 'evaluasi',
                'severity' => 'low',
                'color' => 'primary',
                'icon' => 'bx bx-analyse',
                'link' => '/evaluation/view/1',
                'created_at' => now()->subDays(3),
            ],
            [
                'user_id' => 1,
                'title' => 'Risiko Kritis Teridentifikasi',
                'message' => 'Risiko kritis teridentifikasi pada Proyek Jalan Tol dengan potensi kerugian finansial Rp 2.5 Miliar',
                'type' => 'risiko',
                'severity' => 'high',
                'color' => 'danger',
                'icon' => 'bx bx-error-circle',
                'link' => '/risks/view/2',
                'created_at' => now()->subDays(4),
            ],
            [
                'user_id' => 1,
                'title' => 'KRI Mendekati Threshold',
                'message' => 'KRI "Keterlambatan Pembayaran" mendekati threshold (90%) pada Proyek Pembangunan Bandara',
                'type' => 'kri',
                'severity' => 'medium',
                'color' => 'warning',
                'icon' => 'bx bx-line-chart',
                'link' => '/kri/view/2',
                'created_at' => now()->subDays(5),
                'read_at' => now()->subDays(4),
            ],
            [
                'user_id' => 1,
                'title' => 'Laporan Bulanan',
                'message' => 'Laporan bulanan risiko telah tersedia dengan 12 risiko teridentifikasi dan 8 tindakan mitigasi',
                'type' => 'laporan',
                'severity' => 'low',
                'color' => 'info',
                'icon' => 'bx bx-file',
                'link' => '/reports/download/1',
                'created_at' => now()->subDays(7),
            ],
            [
                'user_id' => 1,
                'title' => 'Mitigasi Risiko Selesai',
                'message' => 'Seluruh tindakan mitigasi untuk Proyek Pembangunan Jembatan telah selesai dengan tingkat keberhasilan 95%',
                'type' => 'mitigasi',
                'severity' => 'low',
                'color' => 'success',
                'icon' => 'bx bx-check-shield',
                'link' => '/risks/mitigation/3',
                'created_at' => now()->subDays(8),
                'read_at' => now()->subDays(7),
            ],
            [
                'user_id' => 1,
                'title' => 'Evaluasi Kinerja Risiko',
                'message' => 'Evaluasi kinerja pengelolaan risiko Q3 2025 menunjukkan peningkatan efektivitas sebesar 15% dari periode sebelumnya',
                'type' => 'evaluasi',
                'severity' => 'medium',
                'color' => 'primary',
                'icon' => 'bx bx-analyse',
                'link' => '/evaluation/quarterly/3',
                'created_at' => now()->subDays(10),
                'read_at' => now()->subDays(9),
            ],
        ];

        foreach ($notifications as $notification) {
            Notification::create($notification);
        }
    }
}