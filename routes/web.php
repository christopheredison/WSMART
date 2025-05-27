<?php

use App\Http\Controllers\CapaianTckController;
use App\Http\Controllers\CapaianTkmruController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Master\AreaDampakController;
use App\Http\Controllers\Master\SkalaDampakController;
use App\Http\Controllers\Master\SkalaProbabilitasController;
use App\Http\Controllers\Master\SikapRisikoController;
use App\Http\Controllers\Master\PeriodeController;
use App\Http\Controllers\Master\RencanaKegiatanController;
use App\Http\Controllers\Master\PeristiwaRisikoController;
use App\Http\Controllers\Master\KategoriRisikoController;
use App\Http\Controllers\Master\JenisRisikoController;
use App\Http\Controllers\Master\MasterRisikoController;
use App\Http\Controllers\Master\RiskMapController;
use App\Http\Controllers\Master\RiskSettingController;
use App\Http\Controllers\Master\UserController;
use App\Http\Controllers\Master\TckController;
use App\Http\Controllers\Master\RoleController;
use App\Http\Controllers\Master\UnitController;
use App\Http\Controllers\Master\UnitTypeController;
use App\Http\Controllers\RiskOfficer\RiskRegisterController as RiskRegisterOfficer;
use App\Http\Controllers\RiskOfficer\RiskMonitoringController;
use App\Http\Controllers\RiskChampion\RankingController as RankingRiskChampion;
use App\Http\Controllers\RiskOwner\PrioritasRisikoController;
use App\Http\Controllers\Universitas\UniversitasController;
use App\Http\Controllers\RiskOfficer\LossEventDatabaseController;
use App\Http\Controllers\StrategiRisiko\StrategiRisikoController;
use App\Http\Controllers\VerifikatorController;
use App\Http\Controllers\ValidatorController;
use App\Http\Controllers\LossEventController;
use App\Http\Controllers\Master\JenisKontrolEksistingController;
use App\Http\Controllers\Master\JenisRencanaPerlakuanRisikoController;
use App\Http\Controllers\Master\KontrolEksistingController;
use App\Http\Controllers\Master\MasterKriController;
use App\Http\Controllers\Master\OpsiPerlakuanRisikoController;
use App\Http\Controllers\Master\PenilaianEfektivitasKontrolController;
use App\Http\Controllers\Master\ProjectController;
use App\Http\Controllers\Master\ProjectDivisiController;
use App\Http\Controllers\Master\ProjectLocationController;
use App\Http\Controllers\Master\ProjectSektorController;
use App\Http\Controllers\Master\ProjectTypeController;
use App\Http\Controllers\Master\QuestionController;
use App\Http\Controllers\Master\RMIPeriodController;
use App\Http\Controllers\Project\ProjectPeriodeListController;
use App\Http\Controllers\Project\ProjectRiskController;
use App\Http\Controllers\Project\ProjectRiskMonitoringController;
use App\Http\Controllers\Project\ProjectRiskMonitoringDocumentController;
use App\Http\Controllers\Project\ProjectLEDController;
use App\Http\Controllers\RiskOfficer\RiskRegisterController;
use App\Http\Controllers\RMI\KuesionerController;
use App\Http\Controllers\MetrikStrategiRisikoController;
use App\Models\ProjectSektor;
use App\Http\Controllers\SasaranStrategiBisnisController;
use App\Http\Controllers\PenilaianRMIController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if (Auth::check()) {
        // Jika pengguna sudah login, arahkan ke halaman home
        return redirect()->route('home');
    } else {
        // Jika pengguna belum login, arahkan ke halaman welcome
        return view('welcome');
    }
});
Route::get('/top-navbar', function () {
    return view('navbar-top');
});
Route::get('/combo-navbar', function () {
    return view('navbar-combo');
});

Auth::routes();

Route::group(['middleware' => ['auth']], function() {
    Route::get('/get-sektors/{divisiId}', function ($divisiId) {
        $sektors = ProjectSektor::where('project_divisi_id', $divisiId)
                    ->orderBy('sektor_name')
                    ->pluck('sektor_name', 'id');
    
        return response()->json($sektors);
    });
    
    Route::get('/risk-map', function () {
        return view('risk-map');
    });
    // profile
    Route::get('/profile', [HomeController::class, 'profile'])->name('profile');
    Route::patch('/profile', [HomeController::class, 'update'])->name('profile');

    Route::get('/home/{data?}', [HomeController::class, 'index'])->name('home');
    Route::get('/profil-risiko', [HomeController::class, 'profilRisiko'])->name('profil-risiko');

    Route::get('/struktur-tata-kelola-risiko', function () {
        return view('risk-governance.struktur-tata-kelola-risiko');
    });

    Route::get('/taksonomi-risiko', function () {
        return view('risk-governance.taksonomi-risiko');
    });

    Route::get('/proses-manajemen-risiko', function () {
        return view('risk-governance.proses-manajemen-risiko');
    });

    Route::get('/peraturan-mwa', function () {
        return view('risk-governance.peraturan-mwa');
    });

    Route::get('/dashboard-unit', function () {
      return view('dashboard-unit');
    });

    Route::get('/dashboard-proyek', [HomeController::class, 'dashboardProyek'])->name('dashboard-proyek');

    Route::get('/dashboard-anper', function () {
      return view('dashboard-anper');
    });

    Route::get('/dashboard-kri', function () {
      return view('dashboard-kri');
    });

    Route::group(['middleware' => ['can:manajemen_user']],function ()
    {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::get('/users/remote-users', [UserController::class, 'searchRemoteUser'])->name('users.search-remote-user');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');

        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/roles/store', [RoleController::class, 'store'])->name('roles.store');
        Route::get('/roles/edit/{role}', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/update/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/destroy/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
        Route::post('/roles/{id}/restore', [RoleController::class, 'restore'])->name('roles.restore');
    });
    Route::group(['middleware' => ['can:manajemen_master']],function ()
    {
        Route::get('/universitas', [UniversitasController::class, 'index'])->name('universitas.index');
        Route::post('/universitas/ranking', [UniversitasController::class, 'ranking'])->name('universitas.ranking');
        Route::post('/universitas/send', [UniversitasController::class, 'send'])->name('universitas.send');
        Route::post('/universitas/konfirmasi', [UniversitasController::class, 'konfirmasi'])->name('universitas.konfirmasi');

        Route::get('/master-risiko', [MasterRisikoController::class, 'index'])->name('master-risiko.index');
        Route::get('/master-risiko/{masterRisiko}/view', [MasterRisikoController::class, 'view'])->name('master-risiko.view');

        Route::get('/peristiwa-risiko', [PeristiwaRisikoController::class, 'index'])->name('peristiwa-risiko.index');
        Route::get('/peristiwa-risiko/create', [PeristiwaRisikoController::class, 'create'])->name('peristiwa-risiko.create');
        Route::post('/peristiwa-risiko', [PeristiwaRisikoController::class, 'store'])->name('peristiwa-risiko.store');
        Route::get('/peristiwa-risiko/{peristiwaRisiko}/edit', [PeristiwaRisikoController::class, 'edit'])->name('peristiwa-risiko.edit');
        Route::put('/peristiwa-risiko/{peristiwaRisiko}', [PeristiwaRisikoController::class, 'update'])->name('peristiwa-risiko.update');
        Route::delete('/peristiwa-risiko/{peristiwaRisiko}', [PeristiwaRisikoController::class, 'destroy'])->name('peristiwa-risiko.destroy');
        Route::get('/get-jenis-risiko/{kategoriRisikoId}', [PeristiwaRisikoController::class, 'getJenisRisiko'])->name('get-jenis-risiko');
        Route::post('/peristiwa-risiko/{id}/restore', [PeristiwaRisikoController::class, 'restore'])->name('peristiwa-risiko.restore');

        Route::get('/kategori-risiko', [KategoriRisikoController::class, 'index'])->name('kategori-risiko.index');
        Route::get('/kategori-risiko/create', [KategoriRisikoController::class, 'create'])->name('kategori-risiko.create');
        Route::post('/kategori-risiko', [KategoriRisikoController::class, 'store'])->name('kategori-risiko.store');
        Route::get('/kategori-risiko/{kategoriRisiko}/edit', [KategoriRisikoController::class, 'edit'])->name('kategori-risiko.edit');
        Route::put('/kategori-risiko/{kategoriRisiko}', [KategoriRisikoController::class, 'update'])->name('kategori-risiko.update');
        Route::delete('/kategori-risiko/{kategoriRisiko}', [KategoriRisikoController::class, 'destroy'])->name('kategori-risiko.destroy');
        Route::post('/kategori-risiko/{id}/restore', [KategoriRisikoController::class, 'restore'])->name('kategori-risiko.restore');

        Route::get('/jenis-risiko', [JenisRisikoController::class, 'index'])->name('jenis-risiko.index');
        Route::get('/jenis-risiko/create', [JenisRisikoController::class, 'create'])->name('jenis-risiko.create');
        Route::post('/jenis-risiko', [JenisRisikoController::class, 'store'])->name('jenis-risiko.store');
        Route::get('/jenis-risiko/{jenisRisiko}/edit', [JenisRisikoController::class, 'edit'])->name('jenis-risiko.edit');
        Route::put('/jenis-risiko/{jenisRisiko}', [JenisRisikoController::class, 'update'])->name('jenis-risiko.update');
        Route::delete('/jenis-risiko/{jenisRisiko}', [JenisRisikoController::class, 'destroy'])->name('jenis-risiko.destroy');
        Route::post('/jenis-risiko/{id}/restore', [JenisRisikoController::class, 'restore'])->name('jenis-risiko.restore');

        Route::get('/rencana-kegiatan', [RencanaKegiatanController::class, 'index'])->name('rencana-kegiatan.index');
        Route::get('/rencana-kegiatan/create', [RencanaKegiatanController::class, 'create'])->name('rencana-kegiatan.create');
        Route::post('/rencana-kegiatan', [RencanaKegiatanController::class, 'store'])->name('rencana-kegiatan.store');
        Route::get('/rencana-kegiatan/{rencanaKegiatan}/edit', [RencanaKegiatanController::class, 'edit'])->name('rencana-kegiatan.edit');
        Route::put('/rencana-kegiatan/{rencanaKegiatan}', [RencanaKegiatanController::class, 'update'])->name('rencana-kegiatan.update');
        Route::delete('/rencana-kegiatan/{rencanaKegiatan}', [RencanaKegiatanController::class, 'destroy'])->name('rencana-kegiatan.destroy');
        Route::post('/rencana-kegiatan/{id}/restore', [RencanaKegiatanController::class, 'restore'])->name('rencana-kegiatan.restore');
        Route::get('/periode/{periode}/ambang-batas', [PeriodeController::class, 'getAmbangBatas'])->name('periode.get-ambang-batas');

        Route::get('/periode', [PeriodeController::class, 'index'])->name('periode.index');
        Route::get('/periode/create', [PeriodeController::class, 'create'])->name('periode.create');
        Route::post('/periode', [PeriodeController::class, 'store'])->name('periode.store');
        Route::get('/periode/{periode}/edit', [PeriodeController::class, 'edit'])->name('periode.edit');
        Route::put('/periode/{periode}', [PeriodeController::class, 'update'])->name('periode.update');
        Route::delete('/periode/{periode}', [PeriodeController::class, 'destroy'])->name('periode.destroy');
        Route::post('/periode/change-active-period', [PeriodeController::class, 'changeActivePeriod'])->name('change-active-period');
        Route::post('/periode/{id}/restore', [PeriodeController::class, 'restore'])->name('periode.restore');
        Route::put('/periode/{periode}/update-ambang-batas', [PeriodeController::class, 'updateAmbangBatas'])->name('periode.update-ambang-batas');
        Route::put('/periode/{periode}/update-risk-limit', [PeriodeController::class, 'updateRiskLimit'])->name('periode.update-risk-limit');

        Route::get('/sikap-risiko', [SikapRisikoController::class, 'index'])->name('sikap-risiko.index');
        Route::get('/sikap-risiko/create', [SikapRisikoController::class, 'create'])->name('sikap-risiko.create');
        Route::post('/sikap-risiko', [SikapRisikoController::class, 'store'])->name('sikap-risiko.store');
        Route::get('/sikap-risiko/{sikapRisiko}/edit', [SikapRisikoController::class, 'edit'])->name('sikap-risiko.edit');
        Route::put('/sikap-risiko/{sikapRisiko}', [SikapRisikoController::class, 'update'])->name('sikap-risiko.update');
        Route::delete('/sikap-risiko/{sikapRisiko}', [SikapRisikoController::class, 'destroy'])->name('sikap-risiko.destroy');
        Route::post('/sikap-risiko/{id}/restore', [SikapRisikoController::class, 'restore'])->name('sikap-risiko.restore');

        Route::get('/skala-probabilitas', [SkalaProbabilitasController::class, 'index'])->name('skala-probabilitas.index');
        Route::get('/skala-probabilitas/create', [SkalaProbabilitasController::class, 'create'])->name('skala-probabilitas.create');
        Route::post('/skala-probabilitas', [SkalaProbabilitasController::class, 'store'])->name('skala-probabilitas.store');
        Route::get('/skala-probabilitas/{skalaProbabilitas}/edit', [SkalaProbabilitasController::class, 'edit'])->name('skala-probabilitas.edit');
        Route::put('/skala-probabilitas/{skalaProbabilitas}', [SkalaProbabilitasController::class, 'update'])->name('skala-probabilitas.update');
        Route::delete('/skala-probabilitas/{skalaProbabilitas}', [SkalaProbabilitasController::class, 'destroy'])->name('skala-probabilitas.destroy');
        Route::post('/skala-probabilitas/{id}/restore', [SkalaProbabilitasController::class, 'restore'])->name('skala-probabilitas.restore');

        Route::get('/skala-dampak', [SkalaDampakController::class, 'index'])->name('skala-dampak.index');
        Route::get('/skala-dampak/create', [SkalaDampakController::class, 'create'])->name('skala-dampak.create');
        Route::post('/skala-dampak', [SkalaDampakController::class, 'store'])->name('skala-dampak.store');
        Route::get('/skala-dampak/{skalaDampak}/edit', [SkalaDampakController::class, 'edit'])->name('skala-dampak.edit');
        Route::put('/skala-dampak/{skalaDampak}', [SkalaDampakController::class, 'update'])->name('skala-dampak.update');
        Route::delete('/skala-dampak/{skalaDampak}', [SkalaDampakController::class, 'destroy'])->name('skala-dampak.destroy');
        Route::post('/skala-dampak/{id}/restore', [SkalaDampakController::class, 'restore'])->name('skala-dampak.restore');

        Route::get('/area-dampak', [AreaDampakController::class, 'index'])->name('area-dampak.index');
        Route::get('/area-dampak/create', [AreaDampakController::class, 'create'])->name('area-dampak.create');
        Route::post('/area-dampak', [AreaDampakController::class, 'store'])->name('area-dampak.store');
        Route::get('/area-dampak/{areaDampak}/edit', [AreaDampakController::class, 'edit'])->name('area-dampak.edit');
        Route::put('/area-dampak/{areaDampak}', [AreaDampakController::class, 'update'])->name('area-dampak.update');
        Route::delete('/area-dampak/{areaDampak}', [AreaDampakController::class, 'destroy'])->name('area-dampak.destroy');
        Route::post('/area-dampak/{id}/restore', [AreaDampakController::class, 'restore'])->name('area-dampak.restore');

        Route::get('/tck', [TckController::class, 'index'])->name('tck.index');
        Route::get('/tck/create', [TckController::class, 'create'])->name('tck.create');
        Route::post('/tck', [TckController::class, 'store'])->name('tck.store');
        Route::get('/tck/{tck}/edit', [TckController::class, 'edit'])->name('tck.edit');
        Route::put('/tck/{tck}', [TckController::class, 'update'])->name('tck.update');
        Route::delete('/tck/{tck}', [TckController::class, 'destroy'])->name('tck.destroy');
        Route::post('/tck/{id}/restore', [TckController::class, 'restore'])->name('tck.restore');

        Route::group(['middleware' => ['can:manajemen_unit']],function ()
        {
            Route::get('/unit', [UnitController::class, 'index'])->name('unit.index');
            Route::get('/unit/create', [UnitController::class, 'create'])->name('unit.create');
            Route::post('/unit', [UnitController::class, 'store'])->name('unit.store');
            Route::get('/unit/{unit}/edit', [UnitController::class, 'edit'])->name('unit.edit');
            Route::put('/unit/{unit}', [UnitController::class, 'update'])->name('unit.update');
            Route::delete('/unit/{unit}', [UnitController::class, 'destroy'])->name('unit.destroy');
            Route::post('/unit/{id}/restore', [UnitController::class, 'restore'])->name('unit.restore');

            Route::get('/unit-type', [UnitTypeController::class, 'index'])->name('unit-type.index');
            Route::get('/unit-type/create', [UnitTypeController::class, 'create'])->name('unit-type.create');
            Route::post('/unit-type', [UnitTypeController::class, 'store'])->name('unit-type.store');
            Route::get('/unit-type/{unitType}/edit', [UnitTypeController::class, 'edit'])->name('unit-type.edit');
            Route::put('/unit-type/{unitType}', [UnitTypeController::class, 'update'])->name('unit-type.update');
            Route::delete('/unit-type/{unitType}', [UnitTypeController::class, 'destroy'])->name('unit-type.destroy');
            Route::post('/unit-type/{id}/restore', [UnitTypeController::class, 'restore'])->name('unit-type.restore');
        });
    });
    Route::group(['middleware' => ['can:risk_register_list']],function ()
    {
        Route::post('/temp-save', [RiskRegisterController::class, 'tempSave'])->name('temp-save');
        Route::get('/risk-register', [RiskRegisterOfficer::class, 'index'])->name('risk-register.index');
        Route::get('/risk-register/create', [RiskRegisterOfficer::class, 'create'])->name('risk-register.create');
        Route::post('/risk-register', [RiskRegisterOfficer::class, 'store'])->name('risk-register.store');
        Route::get('/risk-register/{riskRegister}/edit', [RiskRegisterOfficer::class, 'edit'])->name('risk-register.edit');
        Route::put('/risk-register/{riskRegister}', [RiskRegisterOfficer::class, 'update'])->name('risk-register.update');
        Route::post('/risk-register/draft', [RiskRegisterOfficer::class, 'storeAsDraft'])->name('risk-register.store-as-draft');
        Route::delete('/risk-register/{riskRegister}', [RiskRegisterOfficer::class, 'destroy'])->name('risk-register.destroy');
        Route::get('/risk-register/{riskRegister}/view', [RiskRegisterOfficer::class, 'view'])->name('risk-register.view');
        Route::get('/risk-register/{riskRegister}/kuantifikasi', [RiskRegisterOfficer::class, 'kuantifikasi'])->name('risk-register.kuantifikasi');
        Route::put('/risk-register-kuantifikasi/{riskRegister}', [RiskRegisterOfficer::class, 'updateKuantifikasi'])->name('risk-register-kuantifikasi.update');
        Route::get('/risk-register/{riskRegister}/perencanaan', [RiskRegisterOfficer::class, 'perencanaan'])->name('risk-register.perencanaan');
        Route::put('/risk-register-perencanaan/{riskRegister}', [RiskRegisterOfficer::class, 'updatePerencanaan'])->name('risk-register-perencanaan.update');
        Route::post('/check-deskripsi-peristiwa', [RiskRegisterOfficer::class, 'checkDeskripsiPeristiwa'])->name('check.deskripsi.peristiwa');
        Route::post('/risk-register/send', [RiskRegisterOfficer::class, 'send'])->name('risk-register.send');
        Route::get('/risk-register/{id}/editPerlakuanRisiko', [RiskRegisterOfficer::class, 'editPerlakuanRisiko'])->name('risk-register.editPerlakuanRisiko');
        Route::put('/risk-register/{id}/updatePerlakuanRisiko', [RiskRegisterOfficer::class, 'updatePerlakuanRisiko'])->name('risk-register.updatePerlakuanRisiko');
        Route::get('/get-jenis-risiko/{kategoriRisikoId}', [RiskRegisterOfficer::class, 'getJenisRisiko'])->name('get-jenis-risiko');
        Route::get('/get-peristiwa-risiko/{jenisRisikoId}', [RiskRegisterOfficer::class, 'getPeristiwaRisiko'])->name('get-peristiwa-risiko');
        Route::get('/get-risk-map', [RiskRegisterOfficer::class, 'getRiskMapData'])->name('get-risk-map');
        Route::post('/get-skala-probabilitas', [RiskRegisterOfficer::class, 'getSkalaProbabilitas'])->name('get.skala.probabilitas');
        Route::post('/get-skala-probabilitas-q1', [RiskRegisterOfficer::class, 'getSkalaProbabilitasQ1'])->name('get.skala.probabilitas.q1');
        Route::post('/get-skala-probabilitas-q2', [RiskRegisterOfficer::class, 'getSkalaProbabilitasQ2'])->name('get.skala.probabilitas.q2');
        Route::post('/get-skala-probabilitas-q3', [RiskRegisterOfficer::class, 'getSkalaProbabilitasQ3'])->name('get.skala.probabilitas.q3');
        Route::post('/get-skala-probabilitas-q4', [RiskRegisterOfficer::class, 'getSkalaProbabilitasQ4'])->name('get.skala.probabilitas.q4');

        Route::get('/strategi-risiko', [StrategiRisikoController::class, 'index'])->name('strategi-risiko.index');
        Route::get('/strategi-risiko/create', [StrategiRisikoController::class, 'create'])->name('strategi-risiko.create');
        Route::post('/strategi-risiko', [StrategiRisikoController::class, 'store'])->name('strategi-risiko.store');
        Route::put('/strategi-risiko', [StrategiRisikoController::class, 'update'])->name('strategi-risiko.update');

        Route::post('/get-skala-dampak', [RiskRegisterOfficer::class, 'getSkalaDampak'])->name('get.skala.dampak');
    });
    Route::group(['middleware' => ['can:lost_event_list']],function ()
    {
        Route::get('/loss-event-database', [LossEventDatabaseController::class, 'index'])->name('loss-event-database.index');
        Route::get('/loss-event-database/create', [LossEventDatabaseController::class, 'create'])->name('loss-event-database.create');
        Route::post('/loss-event-database', [LossEventDatabaseController::class, 'store'])->name('loss-event-database.store');
        Route::delete('/loss-event-database/{lossEvent}', [LossEventDatabaseController::class, 'destroy'])->name('loss-event-database.destroy');
        Route::get('/get-jenis-risiko/{kategoriRisikoId}', [LossEventDatabaseController::class, 'getJenisRisiko'])->name('get-jenis-risiko');
        Route::get('/loss-event-database/{id}/edit', [LossEventDatabaseController::class, 'edit'])->name('loss-event-database.edit');
        Route::put('/loss-event-database/{id}', [LossEventDatabaseController::class, 'update'])->name('loss-event-database.update');
        Route::get('loss-event-database/{id}/editRekomendasi', [LossEventDatabaseController::class, 'editRekomendasi'])->name('loss-event-database.editRekomendasi');
        Route::put('loss-event-database/{id}/updateRekomendasi', [LossEventDatabaseController::class, 'updateRekomendasi'])->name('loss-event-database.updateRekomendasi');
        Route::post('loss-event-files', [LossEventDatabaseController::class, 'storeFile'])->name('loss-event-files.store');
        Route::delete('loss-event-files/{file}', [LossEventDatabaseController::class, 'destroyFile'])->name('loss-event-files.destroy');
    });
    Route::group(['middleware' => ['can:risk_monitoring_list']],function ()
    {
        Route::get('/risk-monitoring', [RiskMonitoringController::class, 'index'])->name('risk-monitoring.index');
        Route::get('/risk-monitoring/{riskMonitoring}/edit', [RiskMonitoringController::class, 'edit'])->name('risk-monitoring.edit');
        Route::put('/risk-monitoring/{riskMonitoring}', [RiskMonitoringController::class, 'update'])->name('risk-monitoring.update');
        //Route::get('/risk-monitoring/{id}/editStatusKri', [RiskMonitoringController::class, 'editStatusKri'])->name('risk-monitoring.editStatusKri');
        Route::get('/risk-monitoring/{id}/{periode_monitoring}/editStatusKri', [RiskMonitoringController::class, 'editStatusKri'])->name('risk-monitoring.editStatusKri');
        Route::put('/risk-monitoring/{id}/updateStatusKri', [RiskMonitoringController::class, 'updateStatusKri'])->name('risk-monitoring.updateStatusKri');
        //Route::get('/risk-monitoring/{id}/editRealisasi', [RiskMonitoringController::class, 'editRealisasi'])->name('risk-monitoring.editRealisasi');
        Route::get('/risk-monitoring/{id}/{periode_monitoring}/editRealisasi', [RiskMonitoringController::class, 'editRealisasi'])->name('risk-monitoring.editRealisasi');
        Route::put('/risk-monitoring/{id}/updateRealisasi', [RiskMonitoringController::class, 'updateRealisasi'])->name('risk-monitoring.updateRealisasi');
        Route::post('/get-realisasi-skala-probabilitas', [RiskMonitoringController::class, 'getRealisasiSkalaProbabilitas'])->name('get.realisasi.skala.probabilitas');
        Route::get('/get-risk-map-monitoring', [RiskMonitoringController::class, 'getRiskMapData'])->name('get-risk-map.monitoring');
        Route::post('/risk-monitoring/upload', [RiskMonitoringController::class, 'upload'])->name('risk-monitoring.upload');
        Route::delete('/risk-monitoring/file/{id}', [RiskMonitoringController::class, 'deleteFile'])->name('risk-monitoring.delete-file');
    });

    Route::group(['middleware' => ['can:ranking_risiko_view']],function ()
    {
        Route::get('/risk-champion', [RankingRiskChampion::class, 'index'])->name('risk-champion.index');
        Route::post('/risk-champion/ranking', [RankingRiskChampion::class, 'ranking'])->name('risk-champion.ranking');
        Route::post('/risk-champion/send', [RankingRiskChampion::class, 'send'])->name('risk-champion.send');
        Route::post('/risk-champion/return', [RankingRiskChampion::class, 'return'])->name('risk-champion.return');
    });
    Route::group(['middleware' => ['can:prioritas_risiko_view']],function ()
    {
        Route::get('/risk-owner', [PrioritasRisikoController::class, 'index'])->name('risk-owner.index');
        Route::get('/risk-owner/{id}/edit', [PrioritasRisikoController::class, 'edit'])->name('risk-owner.edit');
        Route::put('/risk-owner/{id}', [PrioritasRisikoController::class, 'verif'])->name('risk-owner.verif');
        Route::post('/risk-owner/send', [PrioritasRisikoController::class, 'send'])->name('risk-owner.send');
        Route::post('/risk-owner/send-universitas', [PrioritasRisikoController::class, 'sendUniversitas'])->name('risk-owner.send-universitas');
        Route::post('/risk-owner/konfirmasi', [PrioritasRisikoController::class, 'konfirmasi'])->name('risk-owner.konfirmasi');
        Route::get('/risk-owner/{id}/view', [PrioritasRisikoController::class, 'view'])->name('risk-owner.view');
        Route::post('/risk-owner/return', [PrioritasRisikoController::class, 'return'])->name('risk-owner.return');
    });

    Route::resource('capaian-tck', CapaianTckController::class);
    Route::resource('capaian-tkmru', CapaianTkmruController::class);

    Route::resource('project-divisi', ProjectDivisiController::class)->except(['create', 'show', 'edit']);
    Route::resource('project-sektor', ProjectSektorController::class)->except(['create', 'show', 'edit']);
    Route::resource('projects', ProjectController::class)->except(['create', 'show', 'edit', 'destroy']);
    Route::resource('projects/{project}/risks', ProjectRiskController::class)->names('projects.risks');
    Route::get('projects/{project}/risks/{risk}/rencana', [ProjectRiskController::class, 'rencana'])->name('projects.risks.rencana')->middleware('can:project_risk_edit');
    Route::post('projects/{project}/risks/{risk}/rencana', [ProjectRiskController::class, 'doRencana'])->name('projects.risks.do-rencana')->middleware('can:project_risk_edit');
    Route::get('projects/{project}/risks/{risk}/analisa', [ProjectRiskController::class, 'analisa'])->name('projects.risks.analisa')->middleware('can:project_risk_edit');
    Route::post('projects/{project}/risks/{risk}/analisa', [ProjectRiskController::class, 'doAnalisa'])->name('projects.risks.do-analisa')->middleware('can:project_risk_edit');
    Route::resource('projects/{project}/monitorings', ProjectRiskMonitoringController::class)->names('projects.monitorings')->only(['index', 'show', 'edit', 'update']);
    Route::resource('projects-monitorings/{monitoring}/q-{quarter}/documents', ProjectRiskMonitoringDocumentController::class)->names('projects.monitorings.documents')->only(['index', 'show', 'store', 'destroy']);
    Route::resource('master-kri', MasterKriController::class)->except(['create', 'show', 'edit']);
    Route::resource('project-periode-list', ProjectPeriodeListController::class)->except(['create', 'edit']);
    Route::resource('jenis-kontrol-eksisting', JenisKontrolEksistingController::class)->except(['create', 'show', 'edit']);
    Route::resource('kontrol-eksisting', KontrolEksistingController::class)->except(['create', 'show', 'edit']);
    Route::resource('penilaian-efektivitas-kontrol', PenilaianEfektivitasKontrolController::class)->except(['create', 'show', 'edit']);
    Route::resource('jenis-rencana-perlakuan-risiko', JenisRencanaPerlakuanRisikoController::class)->except(['create', 'show', 'edit']);
    Route::resource('opsi-perlakuan-risiko', OpsiPerlakuanRisikoController::class)->except(['create', 'show', 'edit']);
    Route::resource('project-type', ProjectTypeController::class)->except(['create', 'show', 'edit']);
    Route::resource('project-location', ProjectLocationController::class)->except(['create', 'show', 'edit']);
    Route::get('rmi-period/{id}/question', [RMIPeriodController::class, 'question'])->name('rmi-period.question');
    Route::post('rmi-period/{id}/question', [RMIPeriodController::class, 'updateQuestion'])->name('rmi-period.update-question');
    Route::resource('rmi-period', RMIPeriodController::class)->except(['create', 'edit']);
    Route::resource('question', QuestionController::class)->except(['create', 'edit']);
    Route::resource('kuesioner', KuesionerController::class)->except(['create', 'store', 'destroy']);

    Route::get('project-led/create', [ProjectLEDController::class, 'create'])->name('project-led.create');
    Route::get('project-led/{id}/edit', [ProjectLEDController::class, 'edit'])->name('project-led.edit');
    Route::post('project-led', [ProjectLEDController::class, 'store'])->name('project-led.store');
    Route::delete('project-led/{id}', [ProjectLEDController::class, 'destroy'])->name('project-led.destroy');
    Route::resource('project-led', ProjectLEDController::class)->except(['create', 'show', 'edit']);

    Route::get('/risk-map-setting', [RiskMapController::class, 'index'])->name('risk-map-setting.index');
    Route::put('/risk-map-setting/update', [RiskMapController::class, 'update'])->name('risk-map-setting.update');

    Route::get('/risk-control-setting', [RiskSettingController::class, 'index'])->name('risk-control-setting.index');
    Route::get('risk-control-setting/{id}/edit', [RiskSettingController::class, 'edit'])->name('risk-control-setting.edit');
    Route::put('risk-control-setting/{id}', [RiskSettingController::class, 'update'])->name('risk-control-setting.update');
    Route::delete('/risk-setting-child/{id}', [RiskSettingController::class, 'destroy'])->name('risk-setting-child.destroy');

    Route::get('/verifikator', [VerifikatorController::class, 'index'])->name('verifikator.index');
    Route::get('/validator', [ValidatorController::class, 'index'])->name('validator.index');

    Route::post('/rencana-perlakuan/tambah', [ProjectRiskController::class, 'simpanRencanaPerlakuan']);
    Route::delete('/rencana-perlakuan/{id}', [ProjectRiskController::class, 'hapusRencanaPerlakuan']);

    Route::put('/rencana-perlakuan/{id}', [ProjectRiskController::class, 'updateRencanaPerlakuan'])->name('rencana-perlakuan.update');
    Route::get('/rencana-perlakuan/{id}', [ProjectRiskController::class, 'editRencanaPerlakuan']);

    Route::get('/master-kri/{peristiwaRisikoId}', [MasterKriController::class, 'getByPeristiwaRisiko']);

    Route::get('/get-kontrol-eksisting', [ProjectRiskController::class, 'getKontrolEksisting']);
    Route::get('/get-kualitatif-res', [ProjectRiskController::class, 'getSkalaDampakResidual'])->name('get.kualitatif.res');

    Route::post('/calculate-poisson', [ProjectRiskController::class, 'calculatePoisson'])
    ->name('calculate-poisson');

    Route::post('/calculate-poisson-residual', [ProjectRiskController::class, 'calculatePoissonRes'])
    ->name('calculate-poisson-residual');

    Route::resource('penilaian-rmi', 'App\Http\Controllers\PenilaianRMIController');
    // Penilaian Aspek Dinamis
    Route::get('penilaian-rmi/{id}/aspek-dinamis', 'App\Http\Controllers\PenilaianRMIController@aspekDinamis')
        ->name('penilaian-rmi.aspek-dinamis');
    Route::post('penilaian-rmi/{id}/save-aspek-dinamis', 'App\Http\Controllers\PenilaianRMIController@saveAspekDinamis')
        ->name('penilaian-rmi.save-aspek-dinamis');
    Route::get('/penilaian-rmi/get-gap-analysis/{criteriaId}', [PenilaianRMIController::class, 'getGapAnalysis'])->name('penilaian-rmi.get-gap-analysis');
    Route::post('/penilaian-rmi/delete-document/{docId}', [PenilaianRMIController::class, 'deleteDocument'])->name('penilaian-rmi.delete-document');
    Route::get('penilaian-rmi/{id}/aspek-kinerja', 'App\Http\Controllers\PenilaianRMIController@aspekKinerja')
        ->name('penilaian-rmi.aspek-kinerja');
    // Proses simpan Aspek Kinerja
    Route::post('{id}/aspek-kinerja', [PenilaianRMIController::class,'storeAspekKinerja'])
         ->name('penilaian-rmi.aspek-kinerja.store');    

    // Metrik Strategi Risiko
    Route::get('metrik-strategi-risiko/{id}/parameter', 'App\Http\Controllers\MetrikStrategiRisikoController@parameter')
        ->name('metrik-strategi-risiko.parameter');
    Route::resource('metrik-strategi-risiko', MetrikStrategiRisikoController::class);
    Route::put('metrik-strategi-risiko/{metrikStrategiRisiko}/update-parameter', [MetrikStrategiRisikoController::class, 'updateParameter'])
        ->name('metrik-strategi-risiko.update-parameter');  
        
    Route::get('/metrik-strategi-risiko/{metrikStrategiRisiko}/parameter', [MetrikStrategiRisikoController::class, 'parameter'])
        ->name('metrik-strategi-risiko.parameter');
    Route::post('/metrik-strategi-risiko/{metrikStrategiRisiko}/parameter', [MetrikStrategiRisikoController::class, 'storeParameter'])
        ->name('metrik-strategi-risiko.store-parameter');
    Route::delete('/metrik-strategi-risiko/{metrikStrategiRisiko}/parameter/{parameter}', [MetrikStrategiRisikoController::class, 'destroyParameter'])
        ->name('metrik-strategi-risiko.destroy-parameter'); 
        
    // Resource index, create, store
    Route::resource('sasaran-strategi', SasaranStrategiBisnisController::class)
         ->only(['index','create','store'])
         ->names([
             'index'  => 'sasaran-strategi.index',
             'create' => 'sasaran-strategi.create',
             'store'  => 'sasaran-strategi.store',
         ]);

    Route::delete('/sasaran-strategi/{sasaran}', [SasaranStrategiBisnisController::class, 'destroy'])->name('sasaran-strategi.destroy');
    Route::put('/strategi-bisnis/{strategiBisnis}', [SasaranStrategiBisnisController::class, 'updateStatus'])->name('strategi-bisnis.update-status');
});

// Route untuk Measurement Parameter
Route::group(['prefix' => 'master', 'middleware' => ['auth']], function () {
    // Resource route untuk measurement-parameter
    Route::resource('measurement-parameter', 'App\Http\Controllers\Master\MeasurementParameterController');
    
    // Route tambahan untuk measurement-parameter
    Route::post('measurement-parameter/{id}/restore', 'App\Http\Controllers\Master\MeasurementParameterController@restore')
        ->name('measurement-parameter.restore');
    Route::get('measurement-parameter/{id}/set-criteria', 'App\Http\Controllers\Master\MeasurementParameterController@setCriteria')
        ->name('measurement-parameter.set-criteria');
    Route::post('measurement-parameter/{id}/store-criteria', 'App\Http\Controllers\Master\MeasurementParameterController@storeCriteria')
        ->name('measurement-parameter.store-criteria');
    Route::get('measurement-parameter/{parameterId}/delete-criteria/{criteriaId}', 'App\Http\Controllers\Master\MeasurementParameterController@deleteCriteria')
        ->name('measurement-parameter.delete-criteria');
});