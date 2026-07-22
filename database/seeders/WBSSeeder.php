<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WBS;
use Illuminate\Support\Facades\DB;

class WBSSeeder extends Seeder
{
    public function run(): void
    {
        // Data mentah dari input Anda
        $rawData = "
IP-0001	Finalisasi kontrak
IP-0002	Identifikasi peluang proyek
IP-0003	Kajian kelayakan awal
IP-0004	Klarifikasi dan negosiasi
IP-0005	Pengumpulan informasi awal
IP-0006	Penyusunan dokumen penawaran
IP-0007	Alternatif Desain
IP-0008	Desain & Layout
IP-0009	Desain Struktur
IP-0010	Jadwal Maintenance
IP-0011	Jadwal Proyek / Gantt / CPM
IP-0012	Value Engineering
IP-0013	IMB / PBG / Izin Konstruksi
IP-0014	Izin Keselamatan
IP-0015	Izin Lahan
IP-0016	Izin Lingkungan
IP-0017	Perizinan Energi
IP-0018	Logistik & Transportasi
IP-0019	AMDAL / UKL-UPL
IP-0020	Adjustment
IP-0021	Akses Jalan Sementara
IP-0022	Analisis Dampak Sosial
IP-0023	BOQ & RAB
IP-0024	Bangunan Penunjang
IP-0025	Bangunan Turbine Hall / Control Room
IP-0026	Bangunan Utama
IP-0027	Boiler / Heat Exchanger / Cooling
IP-0028	Check & Approval
IP-0029	Check & Report
IP-0030	Clearing & Grubbing
IP-0031	Control Panel & PLC
IP-0032	Control Room
IP-0033	Cut & Fill
IP-0034	Data Geoteknik
IP-0035	Diagram Alir
IP-0036	Diagram Kabel
IP-0037	Dokumen Teknis
IP-0038	Dry / No-Load Test
IP-0039	ESDM / PLN
IP-0040	Ekonomi & Finansial
IP-0041	Elektrikal
IP-0042	Equipment Installation
IP-0043	Erection & Foundation
IP-0044	Estimasi Biaya
IP-0045	Final Documentation
IP-0046	Fire, Safety, HSE
IP-0047	Gardu Induk / Substation
IP-0048	Grid Integration
IP-0049	Grounding & Lightning Protection
IP-0050	Gudang & Workshop
IP-0051	Inspeksi Berkala
IP-0052	Instalasi & Alignment
IP-0053	Instrumentasi & SCADA
IP-0054	Kabel Daya & Kontrol
IP-0055	Kantor Lapangan
IP-0056	Konfigurasi Sistem
IP-0057	Konsep Instrumentasi / SCADA
IP-0058	Konsep Sistem Elektrikal
IP-0059	Konsep Sistem Mekanik
IP-0060	LCOE / NPV / IRR
IP-0061	Lay & Welding
IP-0062	Layout Area Pembangkit
IP-0063	Layout Pembangkit
IP-0064	Leveling
IP-0065	Lingkungan
IP-0066	Load Test
IP-0067	Mekanik
IP-0068	Mounting & Cabling
IP-0069	Network Diagram
IP-0070	Paving / Stabilization
IP-0071	Pekerjaan Tanah
IP-0072	Pemadatan & Cut-Fill
IP-0073	Penentuan Lokasi
IP-0074	Penggantian Komponen
IP-0075	Pengiriman Material
IP-0076	Penyimpanan Material
IP-0077	Peralatan & Machinery
IP-0078	Perbaikan Minor
IP-0079	Performance Verification
IP-0080	Perhitungan Volume
IP-0081	Persetujuan
IP-0082	Personel & Tim Proyek
IP-0083	Pile / Raft / Mat Slab
IP-0084	Piping & Valve
IP-0085	Pondasi
IP-0086	Pondasi Turbin / Generator
IP-0087	Preliminary Design
IP-0088	Pressure Test & Alignment
IP-0089	Pulling & Termination
IP-0090	Rekrutmen & Penempatan
IP-0091	Reliability Run (RR)
IP-0092	Sensor & Metering
IP-0093	Setting Out / Benchmark
IP-0094	Sipil & Bangunan
IP-0095	Solar PV / Wind / Battery
IP-0096	Stringing & Jointing
IP-0097	Stringing & Splicing
IP-0098	Survey Detail
IP-0099	Survey Hidrologi / Solar / Wind / Steam
IP-0100	Survey Lokasi & Resource
IP-0101	Survey Topografi
IP-0102	Switchgear / Transformer / Panel
IP-0103	Synchronization
IP-0104	Teknis
IP-0105	Tower / Pole
IP-0106	Transportasi & Instalasi
IP-0107	Turbine / Generator / Boiler / Heat Exchanger
IP-0108	Turbine Hall
IP-0109	Vegetasi
IP-0110	Wiring & Loop Check
IP-0111	Workshop / Storage
IP-0112	Installation & Testing
IP-0113	Integration & Testing
IP-0114	Trial Run
IP-0115	Asset Handover
BG-0001	Proses mendapatkan proyek
BG-0002	Identifikasi peluang proyek
BG-0003	Pengumpulan informasi awal
BG-0004	Kajian kelayakan awal
BG-0005	Penyusunan dokumen penawaran
BG-0006	Klarifikasi dan negosiasi
BG-0007	Finalisasi kontrak
BG-0008	Kick-off internal proyek
BG-0009	Persiapan awal proyek
BG-0010	Pembentukan organisasi proyek
BG-0011	Mobilisasi personel
BG-0012	Mobilisasi peralatan & fasilitas
BG-0013	Pengurusan perizinan awal
BG-0014	Penyusunan rencana K3L
BG-0015	Penyusunan rencana mutu
BG-0016	Penyusunan rencana keselamatan kerja
BG-0017	Pembersihan area & pemagaran
BG-0018	Rencana Mutu Proyek
BG-0019	Pembersihan & pemagaran area
BG-0020	Izin Lahan
BG-0021	Pemeriksaan dokumen kontrak & desain
BG-0022	Engineering & project planning
BG-0023	Shop drawing (arsitektur, struktur, MEP)
BG-0024	Penyusunan BIM Model
BG-0025	Metode kerja (WMS)
BG-0026	Early procurement
BG-0027	Koordinasi dengan owner/konsultan
BG-0028	Rencana Anggaran Biaya
BG-0029	Master Schedule
BG-0030	Pengendalian risiko proyek
BG-0031	Finalisasi design approval
BG-0032	Pekerjaan fisik konstruksi
BG-0033	Pekerjaan Awal
BG-0034	Direksi keet
BG-0035	Fasilitas kerja & utilitas sementara
BG-0036	Pengukuran & layout
BG-0037	Pekerjaan Tanah
BG-0038	Galian tanah
BG-0039	Urugan kembali
BG-0040	Perbaikan tanah
BG-0041	Pondasi & Struktur Bawah
BG-0042	Pondasi (bore pile/pancang/footplat)
BG-0043	Pile cap
BG-0044	Tie beam
BG-0045	Basement (jika ada)
BG-0046	Struktur Atas
BG-0047	Kolom
BG-0048	Balok
BG-0049	Pelat lantai
BG-0050	Tangga beton
BG-0051	Core lift & shaft
BG-0052	Pekerjaan Arsitektur
BG-0053	Dinding (bata/drywall)
BG-0054	Plester & acian
BG-0055	Lantai (keramik/granit/vinyl/epoxy)
BG-0056	Plafon
BG-0057	Pengecatan
BG-0058	Pintu & jendela
BG-0059	Kusen, aluminium, kaca
BG-0060	Fasad (ACP/curtain wall/sun shading)
BG-0061	Mekanikal
BG-0062	HVAC
BG-0063	Lift & eskalator
BG-0064	Mekanikal pemipaan
BG-0065	Elektrikal
BG-0066	Panel & MDB
BG-0067	Wiring listrik
BG-0068	Penerangan
BG-0069	Power outlet
BG-0070	Grounding
BG-0071	Genset
BG-0072	Plumbing & Sanitary
BG-0073	Air bersih
BG-0074	Air kotor
BG-0075	Drainase
BG-0076	Septic tank/grease trap
BG-0077	Fire Fighting
BG-0078	Hydrant
BG-0079	Sprinkler
BG-0080	Fire alarm
BG-0081	APAR
BG-0082	Pekerjaan Luar
BG-0083	Landscape
BG-0084	Paving
BG-0085	Parkir
BG-0086	Drainase luar
BG-0087	Pagar finishing
BG-0088	Mekanikal
BG-0089	Elektrikal
BG-0090	Plumbing
BG-0091	Fire system
BG-0092	Elevator/escalator
BG-0093	Pemeriksaan berkala
BG-0094	Perbaikan kerusakan minor
BG-0095	Monitoring fasilitas MEP
BG-0096	FHO – Final Hand Over
BG-0097	06. Testing & Commissioning
BG-0098	PHO – Serah Terima Sementara
BG-0099	Closing administrasi DLP
BG-0100	Masa pemeliharaan proyek
BG-0101	Dokumentasi pemeliharaan
IN-0001	Proses mendapatkan proyek
IN-0002	Identifikasi peluang proyek
IN-0003	Pengumpulan informasi awal
IN-0004	Kajian kelayakan awal
IN-0005	Penyusunan dokumen penawaran
IN-0006	Klarifikasi dan negosiasi
IN-0007	Finalisasi kontrak
IN-0008	Klarifikasi, Negosiasi & Penetapan Kontrak
IN-0009	Negosiasi & Kontrak
IN-0010	Izin Lahan
IN-0011	Mobilisasi Peralatan
IN-0012	Pengurusan Perizinan Teknis & Lingkungan
IN-0013	Perizinan Lingkungan & Lalu Lintas
IN-0014	Desain Bangunan Pelengkap Infrastruktur
IN-0015	Desain Drainase & Bangunan Pelengkap
IN-0016	Desain Geometrik
IN-0017	Desain Hidrologi
IN-0018	Desain Perkerasan Jalan
IN-0019	Desain Sistem Air Minum
IN-0020	Desain Sistem Persampahan & TPS/TPA
IN-0021	Desain Sistem Sanitasi & Limbah Cair
IN-0022	Desain Sistem SDA & Irigasi
IN-0023	Desain Struktur
IN-0024	Desain Utilitas
IN-0025	Penyusunan Jadwal Pelaksanaan
IN-0026	Perencanaan Detail
IN-0027	Mobilisasi Personil
IN-0028	Mobilisasi Personil & Peralatan
IN-0029	Penunjukan Subkon/Penyedia (Early Procurement)
IN-0030	Bangunan Pengatur (Headwork)
IN-0031	Bendung/Bendung Gerak
IN-0032	Drainase Jalan
IN-0033	Drainase Kawasan
IN-0034	Drainase Lingkungan
IN-0035	Fasilitas Pejalan Kaki
IN-0036	Fasilitas Publik (Alun-alun, Taman)
IN-0037	Fasilitas Umum & Sosial
IN-0038	Health, Safety, Environment (HSE)
IN-0039	Infrastruktur Utilitas Dasar
IN-0040	Inspeksi Berkala
IN-0041	Instalasi Pengolahan Air (IPA)
IN-0042	Instalasi Pengolahan Air Limbah (IPAL)
IN-0043	Intake Air Baku
IN-0044	Investigasi Tanah & Hidrologi
IN-0045	Jalan & Akses Internal
IN-0046	Jalan Lingkungan
IN-0047	Jaringan Air Limbah (JAL)
IN-0048	Jaringan Distribusi
IN-0049	Jaringan Energi & Telekomunikasi
IN-0050	Lampu Penerangan Jalan
IN-0051	Lokasi Komposting & Fasilitas Daur Ulang
IN-0052	Manajemen Lalu Lintas / Pengaturan Akses
IN-0053	Manajemen Lalulintas
IN-0054	Manajemen Mutu Material & Pekerjaan
IN-0055	Manhole & Inspection Chamber
IN-0056	Monitoring Kerusakan
IN-0057	Monitoring Perkerasan
IN-0058	Monitoring Struktur
IN-0059	Normalisasi Sungai
IN-0060	Pekerjaan Abutment
IN-0061	Pekerjaan Deck Slab
IN-0062	Pekerjaan Expansion Joint
IN-0063	Pekerjaan Gelagar (Girder)
IN-0064	Pekerjaan Guardrail & Median
IN-0065	Pekerjaan Landscape & Rehabilitasi Lahan
IN-0066	Pekerjaan Lapis Pondasi Atas (Basecourse)
IN-0067	Pekerjaan Lapis Pondasi Bawah (Subbase)
IN-0068	Pekerjaan Lindi & Gas Metan
IN-0069	Pekerjaan Marka & Rambu
IN-0070	Pekerjaan Perkerasan Aspal
IN-0071	Pekerjaan Pilar
IN-0072	Pekerjaan Pondasi (Tiang/Borepile)
IN-0073	Pekerjaan Railing & Finishing
IN-0074	Pekerjaan Tanah (Cut & Fill)
IN-0075	Pekerjaan Timbunan Badan Jalan
IN-0076	Pelaporan Kemajuan (Progress Report)
IN-0077	Pemadatan
IN-0078	Pembentukan Tim Proyek
IN-0079	Pembersihan Lahan
IN-0080	Pembersihan Lahan (Land Clearing)
IN-0081	Pembuatan Akses Kerja
IN-0082	Pembuatan Keet, Workshop, Gudang
IN-0083	Pemeliharaan Sistem Mekanikal & Elektrikal
IN-0084	Penataan Jalur Pejalan Kaki & Pedestrian
IN-0085	Penataan Kawasan
IN-0086	Penerangan Jalan Umum (PJU)
IN-0087	Pengendalian Biaya (Cost Control)
IN-0088	Pengendalian Proyek
IN-0089	Pengendalian Waktu (Schedule Control)
IN-0090	Penggalian & Timbunan
IN-0091	Pengukuran Awal (Setting Out)
IN-0092	Pengukuran Topografi
IN-0093	Pengumpulan Data Teknis
IN-0094	Pengurusan Dokumen
IN-0095	Penyusunan Proposal Teknis & Komersial
IN-0096	Perbaikan Kerusakan Minor
IN-0097	Perbaikan Minor
IN-0098	Perhitungan BOQ
IN-0099	Perkerasan Kaku
IN-0100	Perkerasan Lentur
IN-0101	Perolehan Proyek
IN-0102	Persiapan Proyek
IN-0103	Pondasi
IN-0104	Quality Control & Quality Assurance
IN-0105	RAB & Time Schedule
IN-0106	Railing
IN-0107	Rambu Jalan
IN-0108	Renovasi/Restorasi Bangunan Budaya
IN-0109	Reservoir / Ground Tank / Elevated Tank
IN-0110	Risk Management
IN-0111	RKS, Gambar Kerja, dan RAB
IN-0112	Ruang Terbuka Hijau (RTH)
IN-0113	Rumah Pompa & Mekanik-Elektrikal
IN-0114	Saluran Primer & Sekunder
IN-0115	Saluran Samping & Cross Drain
IN-0116	Saluran Terbuka
IN-0117	Saluran Tertutup
IN-0118	Sambungan Rumah (SR)
IN-0119	Sewer Pumping Station (SPS)
IN-0120	Sistem Air Bersih & Air Baku
IN-0121	Sistem Pengelolaan Limbah Kawasan
IN-0122	Sistem Pipa Transmisi
IN-0123	Struktur
IN-0124	Studi Kelayakan
IN-0125	Substructure
IN-0126	Superstructure
IN-0127	Survey Eksisting (Topografi, Hidrologi, Lingkungan)
IN-0128	Survey Lahan & Kondisi Eksisting
IN-0129	Survey Topografi & Geoteknik
IN-0130	Survey Topografi Detail
IN-0131	Tanggul & Proteksi Banjir
IN-0132	TPA/Landfill
IN-0133	TPS 3R
IN-0134	Transfer Station
IN-0135	Vegetasi
IN-0136	Uji Fungsi & Commissioning
IN-0137	As-Built Drawing
IN-0138	Serah Terima
IN-0139	Serah Terima Akhir (FHO)
IN-0140	Serah Terima Pertama (PHO)
IN-0141	Laporan Akhir Pemeliharaan
IN-0142	Laporan Akhir Pemeliharaan
IN-0143	Pemeliharaan
IN-0144	Pintu Air & Mekanisme Operasional";

        // Memproses string menjadi array
        $lines = explode("\n", trim($rawData));

        // Disable Model Events agar lebih cepat jika data banyak
        WBS::withoutEvents(function () use ($lines) {
            foreach ($lines as $line) {
                // Pisahkan berdasarkan tab atau spasi berulang
                $parts = preg_split('/\s+(?=\S)/', trim($line), 2);

                if (count($parts) >= 2) {
                    $code = trim($parts[0]);
                    $name = trim($parts[1]);

                    WBS::firstOrCreate(
                        ['code' => $code], // Cek unique code
                        [
                            'name' => $name,
                            'is_active' => true,
                        ]
                    );
                }
            }
        });
    }
}
