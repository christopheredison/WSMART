<?php

namespace App\Supports;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ApiWika
{
    protected $username = null;
    protected $password = null;
    protected $baseUrl = null;
    protected $client = null;
    protected $authPath = 'api/auth';

    public function __construct() {
        $this->username = config('api_wika.username');
        $this->password = config('api_wika.password');
        $this->baseUrl = 'https://api.wika.co.id/services/wikapis/';
        $this->client = new Client();
    }

    public function apiRequest($method, $url, $body = [], $headers = [], $requestType = 'json')
    {
        $client = $this->client;
        $headers['Authorization'] = 'Basic ' . base64_encode($this->username . ':' . $this->password);

        $url = $this->baseUrl . trim($url, '/');
        $options = [
            'headers' => $headers,
        ];

        if (strtoupper($method) === 'GET') {
            $requestType = 'query';
        }

        switch ($requestType) {
            case 'json':
                $options[RequestOptions::JSON] = $body;
                break;
            case 'form-data':
                $options[RequestOptions::FORM_PARAMS] = $body;
                break;
            default:
                $options[RequestOptions::QUERY] = $body;
                break;
        }

        $response = null;
        try {
            $response = $client->request($method, $url, $options);
            $contents = $response->getBody()->getContents();
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $response = $e->getResponse();
            $contents = $response ? $response->getBody()->getContents() : null;
        } catch (\Exception $e) {
            $contents = null;
            Log::error($e->getMessage());
        }

        if ($response && str_contains(implode(' ', $response->getHeader('Content-Type')), 'application/json')) {
            return json_decode($contents, true) ?? $contents;
        }

        return $contents;
    }

    public function getProjects()
    {
        $processData = function ($apiResult) {
        $data = $apiResult['data'] ?? [];

        return collect($data)
                // 1. Pastikan kode_spk ada dan tidak null
                ->whereNotNull('kode_spk')
                // 2. Pastikan kode_spk adalah String atau Angka (bukan Array)
                ->filter(function ($item) {
                    return is_string($item['kode_spk']) || is_numeric($item['kode_spk']);
                })
                // 3. Baru lakukan keyBy
                ->keyBy('kode_spk')
                ->toArray();
        };

        // Ambil Data -2 Bulan
        $result2 = $this->apiRequest('GET', 'proyek', [
            'period' => date('Ym', strtotime('-2 month')),
        ]);
        $result2 = $processData($result2);

        // Ambil Data -1 Bulan
        $result1 = $this->apiRequest('GET', 'proyek', [
            'period' => date('Ym', strtotime('-1 month')),
        ]);
        $result1 = $processData($result1);

        // // Ambil Data Bulan Ini
        $result0 = $this->apiRequest('GET', 'proyek', [
            'period' => date('Ym'),
        ]);
        $result0 = $processData($result0);

        // Gabungkan
        $result = array_merge(
          $result2,
          $result1,
          $result0
        );

        return array_values($result);
    }

    public function getKPI($period)
    {
        $result = $this->apiRequest('GET', 'scorecard/kpi', [
            'period' => $period,
        ]);

        return $result['data'] ?? [];
    }

    public function getKPIRev($tahun, $profit_center)
    {
        $result = $this->apiRequest('GET', 'scorecard/target_akhir_tahun/kpi', [
            'tahun' => $tahun,
            'profit_center' => $profit_center,
        ]);

        return $result;
    }

    public function getHasilUsahaProject($period, $profit_center)
    {
        $result = $this->apiRequest('GET', 'data/proyek/hasil_usaha', [
            'period' => $period,
            'profit_center' => $profit_center,
        ]);

        return $result;
    }

    private function fetchNilaiKontrakRecursive($apiWika, $profitCenter)
    {
        // Mulai dari bulan ini
        $dateCheck = Carbon::now();

        // Batas mundur: Bulan ini + 3 bulan ke belakang = 4 kali pengecekan total
        // Iterasi 0: Bulan Ini
        // Iterasi 1: -1 Bulan
        // Iterasi 2: -2 Bulan
        // Iterasi 3: -3 Bulan
        $maxRetries = 4;

        for ($i = 0; $i < $maxRetries; $i++) {
            $currentPeriod = $dateCheck->format('Ym');

            try {
                $response = $apiWika->getHasilUsahaProject($currentPeriod, $profitCenter);

                // Cek apakah response valid dan ada datanya
                if (isset($response['status']) && $response['status'] && isset($response['data'])) {

                    $data = $response['data'];
                    $statusAutorisasi = $data['status_autorisasi'] ?? 'OPEN'; // Default OPEN jika null

                    // LOGIC UTAMA:
                    // Jika AUTORISASI -> Ambil nilainya, return immediately.
                    if ($statusAutorisasi === 'AUTORISASI') {
                        $nilai = $data['hasil_usaha']['kontrak_review'] ?? 0;
                        return (float) $nilai;
                    }

                    // Jika OPEN -> Biarkan loop berlanjut (akan mundur 1 bulan di bawah)
                    // Jika CLOSE -> Tergantung bisnis proses, biasanya dianggap final (bisa diambil),
                    // tapi sesuai request Anda fokus di AUTORISASI vs OPEN.
                }

            } catch (\Exception $e) {
                // Jika error API (misal timeout/not found), anggap tidak ada data di bulan ini
                // Lanjut cek bulan sebelumnya
                // Log::warning("Gagal fetch NK {$profitCenter} periode {$currentPeriod}: " . $e->getMessage());
            }

            // Mundur 1 bulan untuk iterasi berikutnya
            $dateCheck->subMonth();
        }

        // Jika sudah mundur 3 kali (total 4 attempt) dan tidak ketemu 'AUTORISASI', return 0
        return 0;
    }
}
