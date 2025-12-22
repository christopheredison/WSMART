<?php

namespace Database\Seeders;

use App\Models\TaksonomiRisiko;
use Illuminate\Database\Seeder;

class TaksonomiRisikoSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'nama' => 'Strategic Risk',
                'deskripsi' => 'Risiko Strategik adalah risiko akibat ketidaktepatan dalam pengambilan dan/atau pelaksanaan suatu keputusan strategik serta kegagalan dalam mengantisipasi perubahan lingkungan bisnis (termasuk didalamnya demand changes, supply/competition, business model disruption, climate/natural disaster, terrorism threat).',
            ],
            [
                'nama' => 'Market Risk',
                'deskripsi' => 'Risiko Pasar adalah potensi kerugian karena pergerakan variabel pasar atau faktor pasar dari posisi, portofolio ataupun business model yang dimiliki. Variabel pasar yang dimaksud adalah suku bunga, nilai tukar, perubahan harga komoditas, perubahan harga option, atau perubahan harga underlying (real estate, equity, dll).',
            ],
            [
                'nama' => 'Financial Risk',
                'deskripsi' => 'Risiko Keuangan adalah risiko akibat ketidakmampuan mengoptimalkan atau kegagalan mendapatkan pendanaan eksternal atau sumber pendanaan internal dari arus kas dan/atau aset likuid perusahaan, dalam rangka mendukung kegiatan operasi atau pemenuhan kewajiban. Termasuk dalam bagian dari risiko keuangan adalah kegagalan memenuhi kewajiban yang jatuh tempo, bagi perusahaan yang bergerak di bidang usaha asuransi adalah kegagalan dalam memenuhi kewajiban kepada pemegang polis.',
            ],
            [
                'nama' => 'Credit/Counterparty Credit Risk',
                'deskripsi' => 'Risiko Kredit/Counterparty Kredit adalah risiko akibat kegagalan pihak lain dalam memenuhi kewajiban kepada perusahaan. Termasuk dalam risiko kredit/counterparty kredit adalah concentration risk, counterparty credit risk, settlement risk dan country risk (transfer risk, sovereign risk, dan macroeconomic risk).',
            ],
            [
                'nama' => 'Operational Risk',
                'deskripsi' => 'Risiko Operasional adalah risiko akibat ketidakcukupan dan/atau tidak berfungsinya proses internal, kesalahan manusia, kegagalan sistem, dan/atau adanya kejadian eksternal yang mempengaruhi operasional perusahaan. Termasuk dalam risiko operasional yaitu terkait keamanan siber.',
            ],
            [
                'nama' => 'Investment/Project Risk',
                'deskripsi' => 'Risiko Investasi/Proyek adalah risiko perubahan ekspektasi return dari suatu investasi atau proyek akibat terjadi cost overrun, specification changes, time delay, kegagalan proses assessment risiko awal.',
            ],
            [
                'nama' => 'Reputational Risk',
                'deskripsi' => 'Risiko Reputasi adalah risiko akibat menurunnya tingkat kepercayaan stakeholder yang bersumber dari persepsi negative, kegagalan menangani krisis, penurunan brand values, rusaknya citra perusahaan.',
            ],
            [
                'nama' => 'Regulatory, Legal and Compliance Risk',
                'deskripsi' => 'Risiko Peraturan, Hukum dan Kepatuhan adalah risiko akibat terjadi perubahan peraturan perundang-undangan, tuntutan hukum dan/atau kelemahan aspek yuridis (contract, legal system, dispute & litigation), ketidakpatuhan dan/atau tidak melaksanakan peraturan perundang-undangan dan ketentuan yang berlaku.',
            ],
        ];

        foreach ($data as $item) {
            TaksonomiRisiko::create($item);
        }
    }
}
