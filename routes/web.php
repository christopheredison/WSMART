<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\NotificationController;
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
use App\Http\Controllers\Master\UnitRelationController;
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
use App\Http\Controllers\Master\JabatanController;
use App\Http\Controllers\Master\JenisKontrolEksistingController;
use App\Http\Controllers\Master\JenisRencanaPerlakuanRisikoController;
use App\Http\Controllers\Master\KontrolEksistingController;
use App\Http\Controllers\Master\MasterKriController;
use App\Http\Controllers\Master\OpsiPerlakuanRisikoController;
use App\Http\Controllers\Master\TaksonomiRisikoController;
use App\Http\Controllers\Master\PenilaianEfektivitasKontrolController;
use App\Http\Controllers\Master\ProjectController;
use App\Http\Controllers\Master\ProjectDivisiController;
use App\Http\Controllers\Master\ProjectLocationController;
use App\Http\Controllers\Master\ProjectSektorController;
use App\Http\Controllers\Master\ProjectTypeController;
use App\Http\Controllers\Master\ProjectHasilUsahaController;
use App\Http\Controllers\Master\UnitHasilUsahaController;
use App\Http\Controllers\Master\QuestionController;
use App\Http\Controllers\Master\RMIPeriodController;
use App\Http\Controllers\Master\WBSController;
use App\Http\Controllers\Project\ProjectPeriodeListController;
use App\Http\Controllers\OpportunityController;
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
use App\Http\Controllers\RiskRegisterUnitController;
use App\Http\Controllers\RiskRegisterUnitMonitoringController;
use App\Http\Controllers\UnitLEDController;
use App\Http\Controllers\RiskRegisterApController;
use App\Http\Controllers\RiskRegisterApMonitoringController;
use App\Http\Controllers\ApLEDController;
use App\Http\Controllers\KamusRisikoProjectController;
use App\Http\Controllers\KamusRisikoUnitController;
use App\Http\Controllers\KamusRisikoApController;
use App\Http\Controllers\RekomendasiRisikoController;
use App\Http\Controllers\RiskContextController;
use App\Http\Controllers\ProjectRiskContextController;
use App\Http\Controllers\CorporateLEDController;
use App\Http\Controllers\RiskRegisterCorporateMonitoringController;
use App\Http\Controllers\RMI\KuesionerPublikController;
use App\Http\Controllers\RMI\KuesionerRespondenController;
use App\Http\Controllers\TaskController;

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
Route::get('/loginnonsso', function () {
    if (Auth::check()) {
        // Jika pengguna sudah login, arahkan ke halaman home
        return redirect()->route('home');
    } else {
        // Jika pengguna belum login, arahkan ke halaman welcome
        return view('welcome');
        //return redirect('https://wzone.wika.co.id/');
    }
});

Route::get('/', function () {
    if (Auth::check()) {
        // Jika pengguna sudah login, arahkan ke halaman home
        return redirect()->route('home');
    } else {
        // Jika pengguna belum login, arahkan ke halaman welcome
        //return view('welcome');
        return redirect('https://new-portal.wika.co.id/');
    }
});
Route::get('/top-navbar', function () {
    return view('navbar-top');
});
Route::get('/combo-navbar', function () {
    return view('navbar-combo');
});

Route::get('/callback-sso', [LoginController::class, 'callbackSSO']);

Auth::routes();

Route::group(['middleware' => ['auth']], function () {

    Route::post('/opportunities', [OpportunityController::class, 'store'])->name('opportunities.store');
    Route::put('/opportunities/{id}', [OpportunityController::class, 'update'])->name('opportunities.update');
    Route::delete('/opportunities/{id}', [OpportunityController::class, 'destroy'])->name('opportunities.destroy');
    Route::get('/opportunities/monitoring/{risikoId}', [OpportunityController::class, 'getOpportunities'])->name('opportunities.get');

    // Get Risiko ID from Monitoring
    Route::get('/monitoring/{id}/get-risiko-id', [RiskRegisterUnitMonitoringController::class, 'getRisikoId'])->name('monitoring.get-risiko-id');

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

    Route::get('/dashboard-unit', [HomeController::class, 'dashboardUnit'])->name('dashboard-unit');

    Route::get('/dashboard-proyek', [HomeController::class, 'dashboardProyek'])->name('dashboard-proyek');

    Route::get('/dashboard-corporate', [HomeController::class, 'dashboardCorporate'])->name('dashboard-corporate');

    Route::get('/dashboard-anper', function () {
      return view('dashboard-anper');
    });

    Route::get('/dashboard-kri-unit', [HomeController::class, 'dashboardKriUnit'])->name('dashboard-kri-unit');
    Route::get('/dashboard-kri-project', [HomeController::class, 'dashboardKriProject'])->name('dashboard-kri-project');

    Route::get('/executive-summary-corporate', [HomeController::class, 'executiveSummaryCorporate'])->name('executive-summary-corporate');
    Route::get('/executive-summary-corporate-population', [HomeController::class, 'executiveSummaryCorporatePopulation'])->name('executive-summary-corporate-population');
    Route::get('/executive-summary-unit', [HomeController::class, 'executiveSummaryUnit'])->name('executive-summary-unit');
    Route::get('/executive-summary-anper', [HomeController::class, 'executiveSummaryAnper'])->name('executive-summary-anper');
    Route::get('/executive-summary-project', [HomeController::class, 'executiveSummaryProject'])->name('executive-summary-project');

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
            Route::post('/unit/sync', [UnitController::class, 'sync'])->name('unit.sync');
            Route::post('/unit', [UnitController::class, 'store'])->name('unit.store');
            Route::get('/unit/{unit}/edit', [UnitController::class, 'edit'])->name('unit.edit');
            Route::put('/unit/{unit}', [UnitController::class, 'update'])->name('unit.update');
            Route::delete('/unit/{unit}', [UnitController::class, 'destroy'])->name('unit.destroy');
            Route::post('/unit/{id}/restore', [UnitController::class, 'restore'])->name('unit.restore');

            // Unit Relations
            Route::get('/unit/{unit}/relations', [UnitRelationController::class, 'index'])->name('unit.relations.index');
            Route::post('/unit/{unit}/relations', [UnitRelationController::class, 'store'])->name('unit.relations.store');
            Route::delete('/unit/{unit}/relations/{relation}', [UnitRelationController::class, 'destroy'])->name('unit.relations.destroy');

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
    Route::resource('jabatan', JabatanController::class)->except(['create', 'show', 'destroy']);

    Route::resource('project-divisi', ProjectDivisiController::class)->except(['create', 'show', 'edit']);
    Route::resource('project-sektor', ProjectSektorController::class)->except(['create', 'show', 'edit']);
    Route::resource('project-hasil-usaha', ProjectHasilUsahaController::class)->except(['create', 'show']);
    Route::get('project-hasil-usaha/data', [ProjectHasilUsahaController::class, 'data'])->name('project-hasil-usaha.data');
    Route::post('project-hasil-usaha/sync-all', [ProjectHasilUsahaController::class, 'syncAll'])->name('project-hasil-usaha.sync-all');
    Route::resource('hasil-usaha-divisi', UnitHasilUsahaController::class)->except(['create', 'show']);
    Route::get('hasil-usaha-divisi/data', [UnitHasilUsahaController::class, 'data'])->name('hasil-usaha-divisi.data');
    Route::get('projects/{project}/detail', [ProjectController::class, 'detail'])->name('projects.detail');
    Route::resource('projects', ProjectController::class)->except(['create', 'show', 'edit', 'destroy']);
    Route::resource('projects/{project}/risks', ProjectRiskController::class)->names('projects.risks');
    Route::get('projects/{project}/risks/{risk}/view', [ProjectRiskController::class, 'view'])->name('projects.risks.view');
    Route::get('projects/{project}/risks/{risk}/rencana', [ProjectRiskController::class, 'rencana'])->name('projects.risks.rencana')->middleware('can:project_risk_edit');
    Route::post('projects/{project}/risks/{risk}/rencana', [ProjectRiskController::class, 'doRencana'])->name('projects.risks.do-rencana')->middleware('can:project_risk_edit');
    Route::get('projects/{project}/risks/{risk}/analisa', [ProjectRiskController::class, 'analisa'])->name('projects.risks.analisa')->middleware('can:project_risk_edit');
    Route::post('projects/{project}/risks/{risk}/analisa', [ProjectRiskController::class, 'doAnalisa'])->name('projects.risks.do-analisa')->middleware('can:project_risk_edit');
    Route::post('projects/{project}/risks/import-tender', [ProjectRiskController::class, 'importTender'])->name('projects.risks.import-tender');
    Route::post('project-risk/{id}/verifikasi', [ProjectRiskController::class, 'verifikasi'])->name('project-risk.verifikasi');
    Route::post('projects/risks/eskalasi', [ProjectRiskController::class, 'eskalasi'])->name('projects.risks.eskalasi');
    Route::post('project/{project}/risks/bulk-verifikasi', [ProjectRiskController::class, 'bulkVerifikasi'])->name('projects.risks.bulk-verifikasi');

    Route::resource('projects/{project}/monitorings', ProjectRiskMonitoringController::class)->names('projects.monitorings')->only(['index', 'show', 'edit', 'update']);
    Route::resource('projects-monitorings/{monitoring}/q-{quarter}/documents', ProjectRiskMonitoringDocumentController::class)->names('projects.monitorings.documents')->only(['index', 'show', 'store', 'destroy']);
    Route::prefix('projects/{project}/monitorings')->name('projects.monitorings.')->group(function () {
        Route::post('send-all', [ProjectRiskMonitoringController::class, 'sendAllMonitoring'])->name('send.all');
        Route::post('bulk-verify', [ProjectRiskMonitoringController::class, 'bulkVerifyMonitoring'])->name('bulk-verify');
        Route::post('{monitoring}/verify', [ProjectRiskMonitoringController::class, 'verifyMonitoring'])->name('verify');
        Route::get('{riskId}/notes', [ProjectRiskMonitoringController::class, 'getNotes'])->name('notes');
    });
    Route::get('projects/{project}/risks/{risk}/loss-events/create', [ProjectLEDController::class, 'riskChangeToLed'])->name('projects.loss-events.create')->middleware('can:project_risk_edit');
    Route::post('projects/{project}/risks/{risk}/loss-events', [ProjectLEDController::class, 'riskChangeToLedStore'])->name('projects.loss-events.store')->middleware('can:project_risk_edit');
    Route::post('projects/risks/send', [ProjectRiskController::class, 'send'])->name('projects.risks.send');
    Route::get('projects/{project}/risks/{risk}/notes', [ProjectRiskController::class, 'getRiskNotes'])->name('projects.risks.notes');
    Route::post('projects/sync-wika', [ProjectController::class, 'syncWika'])->name('projects.sync-wika');

    Route::resource('master-kri', MasterKriController::class)->except(['create', 'show', 'edit']);
    Route::resource('project-periode-list', ProjectPeriodeListController::class)->except(['create', 'edit']);
    Route::get('project-periode-list/{id}/recalculate', [ProjectPeriodeListController::class, 'recalculate'])->name('project-periode-list.recalculate');
    Route::resource('jenis-kontrol-eksisting', JenisKontrolEksistingController::class)->except(['create', 'show', 'edit']);
    Route::resource('kontrol-eksisting', KontrolEksistingController::class)->except(['create', 'show', 'edit']);
    Route::resource('taksonomi-risiko', TaksonomiRisikoController::class)->except(['create', 'show', 'edit']);
    Route::resource('penilaian-efektivitas-kontrol', PenilaianEfektivitasKontrolController::class)->except(['create', 'show', 'edit']);
    Route::resource('jenis-rencana-perlakuan-risiko', JenisRencanaPerlakuanRisikoController::class)->except(['create', 'show', 'edit']);
    Route::resource('opsi-perlakuan-risiko', OpsiPerlakuanRisikoController::class)->except(['create', 'show', 'edit']);
    Route::resource('project-type', ProjectTypeController::class)->except(['create', 'show', 'edit']);
    Route::resource('project-location', ProjectLocationController::class)->except(['create', 'show', 'edit']);
    Route::resource('wbs', WBSController::class)->except(['create', 'show']);
    Route::get('wbs/data', [WBSController::class, 'data'])->name('wbs.data');
    Route::get('rmi-period/{id}/question', [RMIPeriodController::class, 'question'])->name('rmi-period.question');
    Route::post('rmi-period/{id}/question', [RMIPeriodController::class, 'updateQuestion'])->name('rmi-period.update-question');
    Route::resource('rmi-period', RMIPeriodController::class)->except(['create', 'edit']);
    Route::resource('question', QuestionController::class)->except(['create', 'edit']);
    Route::resource('kuesioner', KuesionerController::class)->except(['create', 'store', 'destroy']);
    Route::post('kuesioner-responden/{resource}/approve', [KuesionerRespondenController::class, 'approve'])->name('kuesioner-responden.approve');
    Route::post('kuesioner-responden/{resource}/reject', [KuesionerRespondenController::class, 'reject'])->name('kuesioner-responden.reject');
    Route::post('kuesioner-responden/{resource}/reset', [KuesionerRespondenController::class, 'reset'])->name('kuesioner-responden.reset');
    Route::resource('kuesioner-responden', KuesionerRespondenController::class)->except(['create', 'store', 'destroy']);

    Route::get('project-led/{project}/create', [ProjectLEDController::class, 'create'])->name('project-led.create');
    Route::get('project-led/{id}', [ProjectLEDController::class, 'show'])->name('project-led.show');
    Route::get('project-led/{project}/edit/{id}', [ProjectLEDController::class, 'edit'])->name('project-led.edit');
    Route::post('project-led', [ProjectLEDController::class, 'store'])->name('project-led.store');
    Route::delete('project-led/{id}', [ProjectLEDController::class, 'destroy'])->name('project-led.destroy');
    Route::resource('project-led', ProjectLEDController::class)->except(['create', 'show', 'edit']);
    Route::get('project-leds/{projectId}', [ProjectLEDController::class, 'index'])->name('project-led.index-by-project');
    Route::group(['prefix' => 'project-led'], function () {
        Route::get('/files/{id}', [ProjectLEDController::class, 'getFiles'])->name('project-led.files.get');
        Route::post('/files/store', [ProjectLEDController::class, 'storeFile'])->name('project-led.files.store');
        Route::delete('/files/{id}', [ProjectLEDController::class, 'destroyFile'])->name('project-led.files.destroy');
    });

    Route::match(['get', 'post'], '/kamus-risiko-project', [KamusRisikoProjectController::class, 'index'])->name('kamus-risiko-project.index');
    Route::post('kamus-risiko-project/add-risk', [KamusRisikoProjectController::class, 'addRisk'])->name('kamus-risiko-project.add-risk');
    Route::post('kamus-risiko-project/export', [KamusRisikoProjectController::class, 'exportExcel'])->name('kamus-risiko-project.export');

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

    Route::post('/rencana-perlakuan-dampak/tambah', [ProjectRiskController::class, 'simpanRencanaPerlakuanDampak']);
    Route::get('/rencana-perlakuan-dampak/{id}', [ProjectRiskController::class, 'editRencanaPerlakuanDampak']);
    Route::put('/rencana-perlakuan-dampak/{id}', [ProjectRiskController::class, 'updateRencanaPerlakuanDampak']);
    Route::delete('/rencana-perlakuan-dampak/{id}', [ProjectRiskController::class, 'hapusRencanaPerlakuanDampak']);

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
    Route::post('penilaian-rmi/{id}/aspek-kinerja', [PenilaianRMIController::class,'storeAspekKinerja'])->name('penilaian-rmi.aspek-kinerja.store');
    Route::delete('penilaian-rmi/{id}/aspek-kinerja/delete-document/{docId}', [PenilaianRMIController::class,'deleteAspekKinerjaDocument'])->name('penilaian-rmi.aspek-kinerja.delete-document');
    Route::put('penilaian-rmi/{period}/update-penilaian', [PenilaianRMIController::class, 'updatePenilaian'])
    ->name('penilaian-rmi.update-penilaian');
    Route::get('penilaian-rmi/{periodId}/get-risk-data', [PenilaianRMIController::class, 'getRiskData'])
    ->name('penilaian-rmi.get-risk-data');

    Route::prefix('penilaian-rmi')->name('penilaian-rmi.')->group(function () {
      Route::get('/evidence/{periodId}/{parameterId}', [App\Http\Controllers\PenilaianRMIController::class, 'getEvidence'])
          ->name('evidence.list');
      Route::post('/evidence/store', [App\Http\Controllers\PenilaianRMIController::class, 'storeEvidence'])
          ->name('evidence.store');
      Route::delete('/evidence/delete/{id}', [App\Http\Controllers\PenilaianRMIController::class, 'deleteEvidence'])
          ->name('evidence.delete');
    });

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

    Route::get('unit-led/', [UnitLEDController::class, 'index'])->name('unit-led.index');
    Route::get('unit-led/{periode}', [UnitLEDController::class, 'index'])->name('unit-led.index-by-periode');
    Route::get('unit-led/{periode}/create', [UnitLEDController::class, 'create'])->name('unit-led.create');
    Route::post('unit-led', [UnitLEDController::class, 'store'])->name('unit-led.store');
    Route::get('unit-led/{periode}/{id}/show', [UnitLEDController::class, 'show'])->name('unit-led.show');
    Route::get('unit-led/{periode}/{id}/edit', [UnitLEDController::class, 'edit'])->name('unit-led.edit');
    Route::put('unit-led/{id}', [UnitLEDController::class, 'update'])->name('unit-led.update');
    Route::delete('unit-led/{id}', [UnitLEDController::class, 'destroy'])->name('unit-led.destroy');
    Route::group(['prefix' => 'unit-led'], function () {
        Route::get('/files/{id}', [UnitLEDController::class, 'getFiles'])->name('unit-led.files.get');
        Route::post('/files/store', [UnitLEDController::class, 'storeFile'])->name('unit-led.files.store');
        Route::delete('/files/{id}', [UnitLEDController::class, 'destroyFile'])->name('unit-led.files.destroy');
    });

    Route::match(['get', 'post'], 'kamus-risiko-unit', [KamusRisikoUnitController::class, 'index'])->name('kamus-risiko-unit.index');
    Route::post('kamus-risiko-unit/add-risk', [KamusRisikoUnitController::class, 'addRisk'])->name('kamus-risiko-unit.add-risk');
    Route::post('kamus-risiko-unit/export', [KamusRisikoUnitController::class, 'exportExcel'])->name('kamus-risiko-unit.export');

    Route::middleware('can:backups.index')->resource('backups', BackupController::class);
	Route::post('backups/restore', [BackupController::class, 'restore'])->name('backups.restore');

  Route::prefix('laporan')->group(function () {
    Route::get('korporat', [App\Http\Controllers\LaporanController::class, 'korporat'])->name('laporan.korporat');
    Route::post('korporat', [App\Http\Controllers\LaporanController::class, 'korporatExport'])->name('laporan.korporat.export');
    Route::get('unit', [App\Http\Controllers\LaporanController::class, 'unit'])->name('laporan.unit');
    Route::post('unit', [App\Http\Controllers\LaporanController::class, 'unitExport'])->name('laporan.unit.export');
    Route::get('ap', [App\Http\Controllers\LaporanController::class, 'ap'])->name('laporan.ap');
    Route::post('ap', [App\Http\Controllers\LaporanController::class, 'apExport'])->name('laporan.ap.export');
    Route::get('project', [App\Http\Controllers\LaporanController::class, 'project'])->name('laporan.project');
    Route::post('project', [App\Http\Controllers\LaporanController::class, 'projectExport'])->name('laporan.project.export');
    Route::post('project/export-led', [App\Http\Controllers\LaporanController::class, 'projectLedExport'])->name('laporan.project.export_led');
  });

  Route::prefix('risk-context')->group(function () {
      Route::get('/', [RiskContextController::class, 'index'])->name('risk-context.index');
      Route::get('/{periodeId}/{unitId}', [RiskContextController::class, 'detail'])->name('risk-context.detail');
      Route::get('/create', [RiskContextController::class, 'create'])->name('risk-context.create');
      Route::post('/store', [RiskContextController::class, 'store'])->name('risk-context.store');
      Route::get('/edit/{id}', [RiskContextController::class, 'edit'])->name('risk-context.edit');
      Route::put('/update/{id}', [RiskContextController::class, 'update'])->name('risk-context.update');
      Route::get('/show/{id}', [RiskContextController::class, 'show'])->name('risk-context.show');
      Route::delete('/destroy/{id}', [RiskContextController::class, 'destroy'])->name('risk-context.destroy');

      // New routes for update or create functionality
      Route::get('/update-or-create', [RiskContextController::class, 'updateOrCreate'])->name('risk-context.update-or-create');
      Route::post('/store-or-update', [RiskContextController::class, 'storeOrUpdate'])->name('risk-context.store-or-update');

      Route::post('/{id}/submit', [RiskContextController::class, 'submit'])->name('risk-context.submit');
      Route::post('/{id}/verify', [RiskContextController::class, 'verify'])->name('risk-context.verify');
      Route::post('/{id}/reject', [RiskContextController::class, 'reject'])->name('risk-context.reject');
  });

  Route::prefix('risk-context-anper')->group(function () {
      Route::get('/{periodeId}/{unitId}', [RiskContextController::class, 'detailAnper'])->name('risk-context-anper.detail');
      Route::get('/update-or-create', [RiskContextController::class, 'updateOrCreateAnper'])->name('risk-context-anper.update-or-create');
      Route::post('/store-or-update', [RiskContextController::class, 'storeOrUpdateAnper'])->name('risk-context-anper.store-or-update');

      Route::post('/{id}/submit', [RiskContextController::class, 'submit'])->name('risk-context-anper.submit');
      Route::post('/{id}/verify', [RiskContextController::class, 'verify'])->name('risk-context-anper.verify');
      Route::post('/{id}/reject', [RiskContextController::class, 'reject'])->name('risk-context-anper.reject');
  });

  Route::prefix('project-risk-context')->group(function () {
      Route::get('/', [ProjectRiskContextController::class, 'index'])->name('project-risk-context.index');
      Route::get('/project/{projectId}', [ProjectRiskContextController::class, 'indexByProjectPeriode'])->name('project-risk-context.index-by-project-periode');
      Route::get('/create', [ProjectRiskContextController::class, 'create'])->name('project-risk-context.create');
      Route::post('/store', [ProjectRiskContextController::class, 'store'])->name('project-risk-context.store');
      Route::get('/edit/{id}', [ProjectRiskContextController::class, 'edit'])->name('project-risk-context.edit');
      Route::put('/update/{id}', [ProjectRiskContextController::class, 'update'])->name('project-risk-context.update');
      Route::get('/show/{id}', [ProjectRiskContextController::class, 'show'])->name('project-risk-context.show');
      Route::delete('/destroy/{id}', [ProjectRiskContextController::class, 'destroy'])->name('project-risk-context.destroy');

      Route::get('/update-or-create', [ProjectRiskContextController::class, 'updateOrCreate'])->name('project-risk-context.update-or-create');
      Route::post('/store-or-update', [ProjectRiskContextController::class, 'storeOrUpdate'])->name('project-risk-context.store-or-update');

      // Eskalasi
      Route::post('/{id}/submit', [ProjectRiskContextController::class, 'submit'])->name('project-risk-context.submit');
      Route::post('/{id}/verify', [ProjectRiskContextController::class, 'verify'])->name('project-risk-context.verify');
      Route::post('/{id}/reject', [ProjectRiskContextController::class, 'reject'])->name('project-risk-context.reject');
  });
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

Route::prefix('risk-register-unit')->middleware('auth')->group(function () {
    Route::get('/periods', [RiskRegisterUnitController::class, 'RiskPeriodeList'])->name('risk-register-unit.periods');
    Route::get('/periods/{period}', [RiskRegisterUnitController::class, 'riskPeriodeDashboard'])->name('risk-register-unit.periods.show');
    Route::resource('/periods/{period}/monitorings', RiskRegisterUnitMonitoringController::class)
            ->names('risk-register-unit.monitorings')
            ->only(['index', 'show', 'edit', 'update']);
    Route::prefix('risk-register-unit/{period}/monitorings')->name('risk-register-unit.monitorings.')->group(function () {
        Route::post('send-all', [RiskRegisterUnitMonitoringController::class, 'sendAllMonitoring'])->name('send.all');
        Route::post('{monitoring}/verify', [RiskRegisterUnitMonitoringController::class, 'verifyMonitoring'])->name('verify');
        Route::get('{risk}/notes', [RiskRegisterUnitMonitoringController::class, 'getNotes'])->name('notes');
    });
    Route::get('/', [RiskRegisterUnitController::class, 'index'])->name('risk-register-unit.index');
    Route::get('/create', [RiskRegisterUnitController::class, 'create'])->name('risk-register-unit.create');
    Route::post('/', [RiskRegisterUnitController::class, 'store'])->name('risk-register-unit.store');
    Route::get('/{riskRegister}/edit', [RiskRegisterUnitController::class, 'edit'])->name('risk-register-unit.edit');
    Route::put('/{riskRegister}', [RiskRegisterUnitController::class, 'update'])->name('risk-register-unit.update');
    Route::delete('/{riskRegister}', [RiskRegisterUnitController::class, 'destroy'])->name('risk-register-unit.destroy');
    Route::get('/{riskRegister}/perencanaan', [RiskRegisterUnitController::class, 'perencanaan'])->name('risk-register-unit.perencanaan');
    Route::post('/{riskRegister}/perencanaan', [RiskRegisterUnitController::class, 'doPerencanaan'])->name('risk-register-unit.do-perencanaan');
    Route::delete('/{riskRegister}/perencanaan/{id}', [RiskRegisterUnitController::class, 'hapusRencanaPerlakuan'])->name('risk-register-unit.hapus-rencana-perlakuan');
    Route::get('/{riskRegister}/perencanaan/{id}/edit', [RiskRegisterUnitController::class, 'editRencanaPerlakuan'])->name('risk-register-unit.edit-rencana-perlakuan');
    Route::put('/{riskRegister}/perencanaan/{id}', [RiskRegisterUnitController::class, 'updateRencanaPerlakuan'])->name('risk-register-unit.update-rencana-perlakuan');
    Route::get('/{riskRegister}/view', [RiskRegisterUnitController::class, 'view'])->name('risk-register-unit.view');
    Route::post('/send', [RiskRegisterUnitController::class, 'send'])->name('risk-register-unit.send');
    Route::post('/draft', [RiskRegisterUnitController::class, 'storeAsDraft'])->name('risk-register-unit.store-as-draft');
    Route::get('/{riskRegister}/notes', [RiskRegisterUnitController::class, 'getRiskNotes'])->name('risk-register-unit.notes');

    Route::get('/{riskRegister}/analisa', [RiskRegisterUnitController::class, 'analisa'])->name('risk-register-unit.analisa');
    Route::post('/{riskRegister}/analisa', [RiskRegisterUnitController::class, 'doAnalisa'])->name('risk-register-unit.do-analisa');

    Route::post('/{riskRegister}/verifikasi', [RiskRegisterUnitController::class, 'verifikasi'])->name('risk-register-unit.verifikasi');
    Route::post('/bulk-verifikasi', [RiskRegisterUnitController::class, 'bulkVerifikasi'])->name('risk-register-unit.bulk-verifikasi');

    Route::get('/{riskRegister}/loss-events/create', [UnitLEDController::class, 'riskChangeToLed'])->name('risk-register-unit.loss-events.create')->middleware('can:risk_register_list');
    Route::post('/{riskRegister}/loss-events', [UnitLEDController::class, 'riskChangeToLedStore'])->name('risk-register-unit.loss-events.store')->middleware('can:risk_register_list');

    Route::post('/rencana-perlakuan-dampak/tambah', [RiskRegisterUnitController::class, 'simpanRencanaPerlakuanDampak']);
    Route::get('/rencana-perlakuan-dampak/{id}', [RiskRegisterUnitController::class, 'editRencanaPerlakuanDampak']);
    Route::put('/rencana-perlakuan-dampak/{id}', [RiskRegisterUnitController::class, 'updateRencanaPerlakuanDampak']);
    Route::delete('/rencana-perlakuan-dampak/{id}', [RiskRegisterUnitController::class, 'hapusRencanaPerlakuanDampak']);
});

Route::prefix('risk-register-ap')->group(function () {
    Route::get('/periods', [RiskRegisterApController::class, 'RiskPeriodeList'])->name('risk-register-ap.periods');
    Route::get('/periods/{period}', [RiskRegisterApController::class, 'riskPeriodeDashboard'])->name('risk-register-ap.periods.show');
    Route::resource('/periods/{period}/monitorings', RiskRegisterApMonitoringController::class)
            ->names('risk-register-ap.monitorings')
            ->only(['index', 'show', 'edit', 'update']);
    Route::prefix('risk-register-ap/{period}/monitorings')->name('risk-register-ap.monitorings.')->group(function () {
        Route::post('send-all', [RiskRegisterApMonitoringController::class, 'sendAllMonitoring'])->name('send.all');
        Route::post('{monitoring}/verify', [RiskRegisterApMonitoringController::class, 'verifyMonitoring'])->name('verify');
        Route::get('{risk}/notes', [RiskRegisterApMonitoringController::class, 'getNotes'])->name('notes');
    });
    Route::get('/', [RiskRegisterApController::class, 'index'])->name('risk-register-ap.index');
    Route::get('/create', [RiskRegisterApController::class, 'create'])->name('risk-register-ap.create');
    Route::post('/', [RiskRegisterApController::class, 'store'])->name('risk-register-ap.store');
    Route::get('/{riskRegister}/edit', [RiskRegisterApController::class, 'edit'])->name('risk-register-ap.edit');
    Route::put('/{riskRegister}', [RiskRegisterApController::class, 'update'])->name('risk-register-ap.update');
    Route::delete('/{riskRegister}', [RiskRegisterApController::class, 'destroy'])->name('risk-register-ap.destroy');
    Route::get('/{riskRegister}/perencanaan', [RiskRegisterApController::class, 'perencanaan'])->name('risk-register-ap.perencanaan');
    Route::post('/{riskRegister}/perencanaan', [RiskRegisterApController::class, 'doPerencanaan'])->name('risk-register-ap.do-perencanaan');
    Route::delete('/{riskRegister}/perencanaan/{id}', [RiskRegisterApController::class, 'hapusRencanaPerlakuan'])->name('risk-register-ap.hapus-rencana-perlakuan');
    Route::get('/{riskRegister}/perencanaan/{id}/edit', [RiskRegisterApController::class, 'editRencanaPerlakuan'])->name('risk-register-ap.edit-rencana-perlakuan');
    Route::put('/{riskRegister}/perencanaan/{id}', [RiskRegisterApController::class, 'updateRencanaPerlakuan'])->name('risk-register-ap.update-rencana-perlakuan');
    Route::get('/{riskRegister}/view', [RiskRegisterApController::class, 'view'])->name('risk-register-ap.view');
    Route::post('/send', [RiskRegisterApController::class, 'send'])->name('risk-register-ap.send');
    Route::post('/draft', [RiskRegisterApController::class, 'storeAsDraft'])->name('risk-register-ap.store-as-draft');
    Route::get('/{riskRegister}/notes', [RiskRegisterApController::class, 'getRiskNotes'])->name('risk-register-ap.notes');

    Route::get('/{riskRegister}/analisa', [RiskRegisterApController::class, 'analisa'])->name('risk-register-ap.analisa');
    Route::post('/{riskRegister}/analisa', [RiskRegisterApController::class, 'doAnalisa'])->name('risk-register-ap.do-analisa');

    Route::post('/{riskRegister}/verifikasi', [RiskRegisterApController::class, 'verifikasi'])->name('risk-register-ap.verifikasi');
    Route::post('/bulk-verifikasi', [RiskRegisterApController::class, 'bulkVerifikasi'])->name('risk-register-ap.bulk-verifikasi');

    Route::get('/{riskRegister}/loss-events/create', [ApLEDController::class, 'riskChangeToLed'])->name('risk-register-ap.loss-events.create')->middleware('can:risk_register_list');
    Route::post('/{riskRegister}/loss-events', [ApLEDController::class, 'riskChangeToLedStore'])->name('risk-register-ap.loss-events.store')->middleware('can:risk_register_list');
});

Route::match(['get', 'post'], 'kamus-risiko-ap', [KamusRisikoApController::class, 'index'])->name('kamus-risiko-ap.index');
Route::post('kamus-risiko-ap/add-risk', [KamusRisikoApController::class, 'addRisk'])->name('kamus-risiko-ap.add-risk');
Route::post('kamus-risiko-ap/export', [KamusRisikoApController::class, 'exportExcel'])->name('kamus-risiko-ap.export');
Route::get('ap-led/', [ApLEDController::class, 'index'])->name('ap-led.index');
Route::get('ap-led/{periode}', [ApLEDController::class, 'index'])->name('ap-led.index-by-periode');
Route::get('ap-led/{periode}/create', [ApLEDController::class, 'create'])->name('ap-led.create');
Route::post('ap-led', [ApLEDController::class, 'store'])->name('ap-led.store');
Route::get('ap-led/{periode}/{id}/show', [ApLEDController::class, 'show'])->name('ap-led.show');
Route::get('ap-led/{periode}/{id}/edit', [ApLEDController::class, 'edit'])->name('ap-led.edit');
Route::put('ap-led/{id}', [ApLEDController::class, 'update'])->name('ap-led.update');
Route::delete('ap-led/{id}', [ApLEDController::class, 'destroy'])->name('ap-led.destroy');
Route::group(['prefix' => 'ap-led'], function () {
    Route::get('/files/{id}', [ApLEDController::class, 'getFiles'])->name('ap-led.files.get');
    Route::post('/files/store', [ApLEDController::class, 'storeFile'])->name('ap-led.files.store');
    Route::delete('/files/{id}', [ApLEDController::class, 'destroyFile'])->name('ap-led.files.destroy');
});

Route::prefix('rekomendasi-risiko')->name('rekomendasi-risiko.')->group(function () {
    Route::get('/', [RekomendasiRisikoController::class, 'index'])->name('index');
    Route::post('/', [RekomendasiRisikoController::class, 'store'])->name('store');
    Route::get('/{unit}/{periode}/create', [RekomendasiRisikoController::class, 'create'])->name('create');
    Route::get('/{rekomendasi}/edit', [RekomendasiRisikoController::class, 'edit'])->name('edit');

    Route::put('/{rekomendasi}', [RekomendasiRisikoController::class, 'update'])->name('update');
    Route::delete('/{rekomendasi}', [RekomendasiRisikoController::class, 'destroy'])->name('destroy');
    Route::post('/{rekomendasi}/publish', [RekomendasiRisikoController::class, 'publish'])->name('publish');

    Route::get('/{rekomendasi}', [RekomendasiRisikoController::class, 'view'])->name('view');
    Route::get('/{unit}/{periode}', [RekomendasiRisikoController::class, 'show'])->name('show');
});

Route::group(['prefix' => 'ict', 'as' => 'ict.'], function () {
    Route::get('/', [\App\Http\Controllers\ICT\ICTController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\ICT\ICTController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\ICT\ICTController::class, 'store'])->name('store');
    Route::get('/{ictPlan}', [\App\Http\Controllers\ICT\ICTController::class, 'show'])->name('show');
    Route::get('/{ictPlan}/edit', [\App\Http\Controllers\ICT\ICTController::class, 'edit'])->name('edit');
    Route::put('/{ictPlan}', [\App\Http\Controllers\ICT\ICTController::class, 'update'])->name('update');
    Route::delete('/{ictPlan}', [\App\Http\Controllers\ICT\ICTController::class, 'destroy'])->name('destroy');
    Route::get('/{ictPlan}/testing', [\App\Http\Controllers\ICT\ICTController::class, 'testing'])->name('testing');
    Route::post('/{ictPlan}/testing', [\App\Http\Controllers\ICT\ICTController::class, 'storeTesting'])->name('store-testing');
    Route::get('/{ictPlan}/report', [\App\Http\Controllers\ICT\ICTController::class, 'report'])->name('report');
    Route::post('/{ictPlan}/report', [\App\Http\Controllers\ICT\ICTController::class, 'storeReport'])->name('store-report');
    Route::post('/submit-all', [\App\Http\Controllers\ICT\ICTController::class, 'submitAll'])->name('submitAll');
    Route::post('/approve-all', [\App\Http\Controllers\ICT\ICTController::class, 'approveAll'])->name('approveAll');
    Route::post('/reject-all', [\App\Http\Controllers\ICT\ICTController::class, 'rejectAll'])->name('rejectAll');
});
// Tambahkan di dalam grup middleware auth
// Route::get('corporate-risk', [App\Http\Controllers\./,::class, 'index'])->name('corporate-risk.index');
Route::post('corporate-risk/update-to-corporate', [App\Http\Controllers\CorporateRiskController::class, 'updateToCorporate'])->name('corporate-risk.update-to-corporate');
Route::post('corporate-risk/ranking-risiko', [App\Http\Controllers\CorporateRiskController::class, 'rankingRisiko'])->name('corporate-risk.ranking-risiko');
Route::post('corporate-risk/confirm-corporate', [App\Http\Controllers\CorporateRiskController::class, 'confirmCorporateRisks'])->name('corporate-risk.confirm-corporate');
Route::post('corporate-risk/revert-from-corporate', [App\Http\Controllers\CorporateRiskController::class, 'revertFromCorporate'])->name('corporate-risk.revert-from-corporate');
Route::get('/download-tender-template', [ProjectRiskController::class, 'downloadTenderTemplate'])->name('download-tender-template');

Route::prefix('corporate-risk')->name('corporate-risk.')->middleware(['auth'])->group(function () {
    Route::get('periods', [App\Http\Controllers\CorporateRiskController::class, 'riskPeriodeList'])->name('periods');
    Route::get('/periods/{period}', [App\Http\Controllers\CorporateRiskController::class, 'riskPeriodeDashboard'])->name('periods.show');

    Route::resource('/periods/{period}/monitorings', RiskRegisterCorporateMonitoringController::class)
            ->names('monitorings')
            ->only(['index', 'show', 'edit', 'update']);

    Route::get('/', [App\Http\Controllers\CorporateRiskController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\CorporateRiskController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\CorporateRiskController::class, 'store'])->name('store');
    Route::get('/{riskRegister}/edit', [App\Http\Controllers\CorporateRiskController::class, 'edit'])->name('edit');
    Route::put('/{riskRegister}', [App\Http\Controllers\CorporateRiskController::class, 'update'])->name('update');
    Route::delete('/{riskRegister}', [App\Http\Controllers\CorporateRiskController::class, 'destroy'])->name('destroy');
    Route::get('/{riskRegister}/view', [App\Http\Controllers\CorporateRiskController::class, 'view'])->name('view');

    Route::get('/top-down', [App\Http\Controllers\CorporateRiskController::class, 'topDown'])->name('top-down');
    Route::post('/top-down', [App\Http\Controllers\CorporateRiskController::class, 'storeTopDown'])->name('store-top-down');

    Route::get('/{riskRegister}/analisa', [App\Http\Controllers\CorporateRiskController::class, 'analisa'])->name('analisa');
    Route::post('/{riskRegister}/analisa', [App\Http\Controllers\CorporateRiskController::class, 'doAnalisa'])->name('do-analisa');

    Route::get('/{riskRegister}/perencanaan', [App\Http\Controllers\CorporateRiskController::class, 'perencanaan'])->name('perencanaan');
    Route::post('/{riskRegister}/perencanaan', [App\Http\Controllers\CorporateRiskController::class, 'doPerencanaan'])->name('do-perencanaan');
    Route::delete('/{riskRegister}/perencanaan/{id}', [App\Http\Controllers\CorporateRiskController::class, 'hapusRencanaPerlakuan'])->name('hapus-rencana-perlakuan');
    Route::get('/{riskRegister}/perencanaan/{id}/edit', [App\Http\Controllers\CorporateRiskController::class, 'editRencanaPerlakuan'])->name('edit-rencana-perlakuan');
    Route::put('/{riskRegister}/perencanaan/{id}', [App\Http\Controllers\CorporateRiskController::class, 'updateRencanaPerlakuan'])->name('update-rencana-perlakuan');

    Route::get('/get-division-risks/{unit}', [App\Http\Controllers\CorporateRiskController::class, 'getDivisionRisks'])->name('get-division-risks');
    Route::get('/get-ap-risks/{unit}', [App\Http\Controllers\CorporateRiskController::class, 'getApRisks'])->name('get-ap-risks');
});

Route::prefix('corporate-led')->name('corporate-led.')->middleware(['auth'])->group(function () {
    Route::get('/{periode}', [CorporateLEDController::class, 'index'])->name('index');
    Route::get('/{periode}/create', [CorporateLEDController::class, 'create'])->name('create');
    Route::post('/', [CorporateLEDController::class, 'store'])->name('store');
    Route::get('/{periode}/{id}/show', [CorporateLEDController::class, 'show'])->name('show');
    Route::get('/{periode}/{id}/edit', [CorporateLEDController::class, 'edit'])->name('edit');
    Route::put('/{id}', [CorporateLEDController::class, 'update'])->name('update');
    Route::delete('/{id}', [CorporateLEDController::class, 'destroy'])->name('destroy');
    Route::get('/files/{id}', [CorporateLEDController::class, 'getFiles'])->name('files.get');
    Route::post('/files/store', [CorporateLEDController::class, 'storeFile'])->name('files.store');
    Route::delete('/files/{id}', [CorporateLEDController::class, 'destroyFile'])->name('files.destroy');
});

// Notification Routes
Route::prefix('notifications')->middleware(['auth'])->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/unread', [NotificationController::class, 'getUnreadNotifications'])->name('notifications.unread_count');
    Route::get('/{id}', [NotificationController::class, 'show'])->name('notifications.show');
    Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/{id}/unread', [NotificationController::class, 'markAsUnread'])->name('notifications.unread');
    Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    Route::post('/mark-multiple-read', [NotificationController::class, 'markMultipleAsRead'])->name('notifications.markMultipleAsRead');
    Route::post('/mark-multiple-unread', [NotificationController::class, 'markMultipleAsUnread'])->name('notifications.markMultipleAsUnread');
});

Route::prefix('kuesioner-publik')->as('kuesioner-publik.')->group(function () {
    Route::get('{token}/register', [KuesionerPublikController::class, 'register'])->name('register');
    Route::post('{token}/register', [KuesionerPublikController::class, 'doRegister'])->name('do-register');
    Route::get('{token}/verify', [KuesionerPublikController::class, 'verify'])->name('verify');
    Route::get('{token}/fill', [KuesionerPublikController::class, 'fill'])->name('fill');
    Route::put('{token}/fill', [KuesionerPublikController::class, 'update'])->name('update');
});

Route::prefix('my-tasks')->middleware(['auth'])->group(function () {
    Route::get('/', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/count', [TaskController::class, 'getCount'])->name('tasks.count');
});

Route::get('/logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index'])->middleware(['auth']);
