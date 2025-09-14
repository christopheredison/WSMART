<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SkalaParameter;

class SkalaParameterSeeder extends Seeder
{
    public function run(): void
    {
        SkalaParameter::query()->delete();

        $parameters = [
            'Kemungkinan terjadi' => [
                ['tingkat' => 1, 'skala' => 'Sangat jarang terjadi', 'min' => 0.00, 'max' => 19.99, 'deskripsi' => 'Risiko mungkin terjadi sangat jarang, paling banyak satu kali dalam setahun'],
                ['tingkat' => 2, 'skala' => 'Jarang terjadi', 'min' => 20.00, 'max' => 39.99, 'deskripsi' => 'Risiko mungkin terjadi hanya sekali dalam 6 bulan'],
                ['tingkat' => 3, 'skala' => 'Bisa terjadi', 'min' => 40.00, 'max' => 59.99, 'deskripsi' => 'Risiko pernah terjadi namun tidak sering, sekali dalam  4 bulan'],
                ['tingkat' => 4, 'skala' => 'Sangat mungkin terjadi', 'min' => 60.00, 'max' => 79.99, 'deskripsi' => 'Risiko pernah terjadi sekali dalam 2 bulan'],
                ['tingkat' => 5, 'skala' => 'Hampir pasti terjadi', 'min' => 80.00, 'max' => 100.00, 'deskripsi' => 'Risiko pernah terjadi sekali dalam 1 bulan'],
            ],
            'Frekuensi kejadian' => [
                ['tingkat' => 1, 'skala' => 'Sangat jarang terjadi', 'min' => 0.00, 'max' => 19.99, 'deskripsi' => '< 1 permil dalam frekuensi kerjadian/jumlah transaksi'],
                ['tingkat' => 2, 'skala' => 'Jarang terjadi', 'min' => 20.00, 'max' => 39.99, 'deskripsi' => 'Dari 1 permil s/d 1% dari frekuensi kejadian/jumlah transaksi'],
                ['tingkat' => 3, 'skala' => 'Bisa terjadi', 'min' => 40.00, 'max' => 59.99, 'deskripsi' => 'Di atas 1% s/d 5% dari frekuensi kejadian/jumlah transaksi'],
                ['tingkat' => 4, 'skala' => 'Sangat mungkin terjadi', 'min' => 60.00, 'max' => 79.99, 'deskripsi' => 'Di atas 5% s/d 10% dari frekuensi kejadian/jumlah transaksi'],
                ['tingkat' => 5, 'skala' => 'Hampir pasti terjadi', 'min' => 80.00, 'max' => 100.00, 'deskripsi' => '> 10% dari frekuensi kejadian/jumlah transaksi'],
            ],
            'Persentase' => [
                ['tingkat' => 1, 'skala' => 'Sangat jarang terjadi', 'min' => 0.00, 'max' => 19.99, 'deskripsi' => 'Probabilitas kejadian Risiko di bawah 20%'],
                ['tingkat' => 2, 'skala' => 'Jarang terjadi', 'min' => 20.00, 'max' => 39.99, 'deskripsi' => 'Probabilitas kejadian Risiko dari 20% sampai dengan 40%'],
                ['tingkat' => 3, 'skala' => 'Bisa terjadi', 'min' => 40.00, 'max' => 59.99, 'deskripsi' => 'Probabilitas kejadian Risiko antara 40% sampai dengan 60%'],
                ['tingkat' => 4, 'skala' => 'Sangat mungkin terjadi', 'min' => 60.00, 'max' => 79.99, 'deskripsi' => 'Probabilitas kejadian Risiko antara 60% sampai dengan 80%'],
                ['tingkat' => 5, 'skala' => 'Hampir pasti terjadi', 'min' => 80.00, 'max' => 100.00, 'deskripsi' => 'Probabilitas kejadian Risiko antara 80% sampai dengan 100%'],
            ],
        ];

        foreach ($parameters as $type => $scales) {
            foreach ($scales as $scale) {
                SkalaParameter::create([
                    'type_parameter' => $type,
                    'tingkat' => $scale['tingkat'],
                    'skala' => $scale['skala'],
                    'min' => $scale['min'],
                    'max' => $scale['max'],
                    'deskripsi' => $scale['deskripsi'],
                ]);
            }
        }
    }
}