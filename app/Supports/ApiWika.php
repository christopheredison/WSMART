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
                // PERBAIKAN: Hapus ->keyBy('kode_spk') agar urutan indeks array numerik murni
                // sehingga urutan periodenya tetap konsisten saat digabungkan
                ->toArray();
        };

        // Ambil Data -2 Bulan (Paling Lama)
        $result2 = $this->apiRequest('GET', 'proyek', [
            'period' => date('Ym', strtotime('-2 month')),
        ]);
        $result2 = $processData($result2);

        // Ambil Data -1 Bulan
        $result1 = $this->apiRequest('GET', 'proyek', [
            'period' => date('Ym', strtotime('-1 month')),
        ]);
        $result1 = $processData($result1);

        // Ambil Data Bulan Ini (Paling Baru)
        $result0 = $this->apiRequest('GET', 'proyek', [
            'period' => date('Ym'),
        ]);
        $result0 = $processData($result0);

        // Gabungkan berurutan secara flat (urutan: terlama ke terbaru)
        $allData = array_merge(
            $result2,
            $result1,
            $result0
        );

        $finalResult = collect($allData)
            ->groupBy(function ($item) {
                // Jika profit_center tidak kosong, group berdasarkan profit_center.
                // Jika kosong/null, group berdasarkan kode_spk unik agar tidak saling menggulung data kosong.
                return !empty($item['profit_center']) ? $item['profit_center'] : 'EMPTY_' . $item['kode_spk'];
            })
            ->map(function ($group) {
                // Karena data digabung dari bulan terlama ke terbaru, maka data di urutan
                // paling akhir (.last()) dari grup ini dipastikan adalah data proyek yang paling ter-update.
                return $group->last();
            })
            ->values()
            ->toArray();

        return $finalResult;
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

}
