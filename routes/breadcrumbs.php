<?php

use App\Models\ProjectRisk;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

// Home
Breadcrumbs::for('home', function (BreadcrumbTrail $trail) {
    $trail->push('Home', route('home'));
});

// Periode Divisi
Breadcrumbs::for('risk-register-unit.periods', function (BreadcrumbTrail $trail) {
    $trail->push('Periode Divisi', route('risk-register-unit.periods'));
});

// Periode Divisi > View Periode Divisi
Breadcrumbs::for('risk-register-unit.periods.show', function (BreadcrumbTrail $trail, $period) {
    $trail->parent('risk-register-unit.periods');
    $trail->push('Risk Register Divisi', route('risk-register-unit.periods.show', [$period]));
});

// Periode Divisi > Risk Register Divisi
Breadcrumbs::for('risk-register-unit.index', function (BreadcrumbTrail $trail) {
    $trail->parent('risk-register-unit.periods');
    $trail->push('Risk Register Divisi', route('risk-register-unit.index'));
});

// Periode Divisi > Risk Register Divisi > Tambah Risk Register
Breadcrumbs::for('risk-register-unit.create', function (BreadcrumbTrail $trail) {
    $trail->parent('risk-register-unit.index');
    $trail->push('Tambah Risk Register', route('risk-register-unit.create'));
});

// Periode Divisi > Risk Register Divisi > Edit Risk Register
Breadcrumbs::for('risk-register-unit.edit', function (BreadcrumbTrail $trail, $riskRegister) {
    $trail->parent('risk-register-unit.index');
    $trail->push('Edit Risk Register', route('risk-register-unit.edit', [$riskRegister]));
});

// Periode Divisi > Risk Register Divisi > Analisa Risiko
Breadcrumbs::for('risk-register-unit.analisa', function (BreadcrumbTrail $trail, $riskRegister) {
    $trail->parent('risk-register-unit.index');
    $trail->push('Analisa Risiko', route('risk-register-unit.analisa', [$riskRegister]));
});

// Periode Divisi > Risk Register Divisi > Perencanaan Risiko
Breadcrumbs::for('risk-register-unit.perencanaan', function (BreadcrumbTrail $trail, $riskRegister) {
    $trail->parent('risk-register-unit.index');
    $trail->push('Rencana Perlakuan Risiko', route('risk-register-unit.perencanaan', [$riskRegister]));
});

// Periode Divisi > Risk Register Divisi > View Risiko
Breadcrumbs::for('risk-register-unit.view', function (BreadcrumbTrail $trail, $riskRegister) {
    $trail->parent('risk-register-unit.index');
    $trail->push('View Risiko', route('risk-register-unit.view', [$riskRegister]));
});

// Periode Divisi > Data Monitoring
Breadcrumbs::for('risk-register-unit.monitorings.index', function (BreadcrumbTrail $trail, $period) {
    $trail->parent('risk-register-unit.periods');
    $trail->push('Data Monitoring', route('risk-register-unit.monitorings.index', [$period]));
});

// Periode Divisi > Data Monitoring > View Data Monitoring
Breadcrumbs::for('risk-register-unit.monitorings.show', function (BreadcrumbTrail $trail, $period, $monitoring) {
    $trail->parent('risk-register-unit.monitorings.index', $period);
    $trail->push('View Data Monitoring', route('risk-register-unit.monitorings.show', [$period, $monitoring]));
});

// Periode Divisi > Data Monitoring > Edit Data Monitoring
Breadcrumbs::for('risk-register-unit.monitorings.edit', function (BreadcrumbTrail $trail, $period, $monitoring) {
    $trail->parent('risk-register-unit.monitorings.index', $period);
    $trail->push('Edit Data Monitoring', route('risk-register-unit.monitorings.edit', [$period, $monitoring]));
});

// Periode Divisi > Loss Event Divisi
Breadcrumbs::for('unit-led.index-by-periode', function (BreadcrumbTrail $trail, $periode) {
    $trail->parent('risk-register-unit.periods');
    $trail->push('Loss Event Divisi', route('unit-led.index-by-periode', $periode));
});

// Periode Divisi > Loss Event Divisi > Tambah Loss Event Divisi
Breadcrumbs::for('unit-led.create', function (BreadcrumbTrail $trail, $periode) {
    $trail->parent('unit-led.index-by-periode', $periode);
    $trail->push('Tambah Loss Event Divisi', route('unit-led.create', $periode));
});

// Periode Divisi > Loss Event Divisi > Edit Loss Event Divisi
Breadcrumbs::for('unit-led.edit', function (BreadcrumbTrail $trail, $periode, $id) {
    $trail->parent('unit-led.index-by-periode', $periode);
    $trail->push('Edit Loss Event Divisi', route('unit-led.edit', [$periode, $id]));
});

// Periode Divisi > Loss Event Divisi > Detail Loss Event Divisi
Breadcrumbs::for('unit-led.show', function (BreadcrumbTrail $trail, $periode, $id) {
    $trail->parent('unit-led.index-by-periode', $periode);
    $trail->push('Detail Loss Event Divisi', route('unit-led.show', [$periode, $id]));
});

// Periode Divisi > Risk Register Divisi > Kamus Risiko Unit
Breadcrumbs::for('kamus-risiko-unit.index', function (BreadcrumbTrail $trail) {
    $trail->parent('risk-register-unit.index');
    $trail->push('Kamus Risiko Unit', route('kamus-risiko-unit.index'));
});

// Periode Divisi > Risk Context Divisi
Breadcrumbs::for('risk-context.index-by-periode-unit', function (BreadcrumbTrail $trail, $periodeId, $unitId) {
    $trail->parent('risk-register-unit.periods');
    $trail->push('Risk Context Divisi', route('risk-context.index-by-periode-unit', [$periodeId, $unitId]));
});


// Periode Anak Perusahaan
Breadcrumbs::for('risk-register-ap.periods', function (BreadcrumbTrail $trail) {
    $trail->push('Periode Anak Perusahaan', route('risk-register-ap.periods'));
});

// Periode Anak Perusahaan > View Periode Anak Perusahaan
Breadcrumbs::for('risk-register-ap.periods.show', function (BreadcrumbTrail $trail, $period) {
    $trail->parent('risk-register-ap.periods');
    $trail->push('Risk Register Anak Perusahaan', route('risk-register-ap.periods.show', [$period]));
});

// Periode Anak Perusahaan > Risk Register Anak Perusahaan
Breadcrumbs::for('risk-register-ap.index', function (BreadcrumbTrail $trail) {
    $trail->parent('risk-register-ap.periods');
    $trail->push('Risk Register Anak Perusahaan', route('risk-register-ap.index'));
});

// Periode Anak Perusahaan > Risk Register Anak Perusahaan > Tambah Risk Register
Breadcrumbs::for('risk-register-ap.create', function (BreadcrumbTrail $trail) {
    $trail->parent('risk-register-ap.index');
    $trail->push('Tambah Risk Register', route('risk-register-ap.create'));
});

// Periode Anak Perusahaan > Risk Register Anak Perusahaan > Edit Risk Register
Breadcrumbs::for('risk-register-ap.edit', function (BreadcrumbTrail $trail, $riskRegister) {
    $trail->parent('risk-register-ap.index');
    $trail->push('Edit Risk Register', route('risk-register-ap.edit', [$riskRegister]));
});

// Periode Anak Perusahaan > Risk Register Anak Perusahaan > Analisa Risiko
Breadcrumbs::for('risk-register-ap.analisa', function (BreadcrumbTrail $trail, $riskRegister) {
    $trail->parent('risk-register-ap.index');
    $trail->push('Analisa Risiko', route('risk-register-ap.analisa', [$riskRegister]));
});

// Periode Anak Perusahaan > Risk Register Anak Perusahaan > Perencanaan Risiko
Breadcrumbs::for('risk-register-ap.perencanaan', function (BreadcrumbTrail $trail, $riskRegister) {
    $trail->parent('risk-register-ap.index');
    $trail->push('Rencana Perlakuan Risiko', route('risk-register-ap.perencanaan', [$riskRegister]));
});

// Periode Anak Perusahaan > Risk Register Anak Perusahaan > View Risiko
Breadcrumbs::for('risk-register-ap.view', function (BreadcrumbTrail $trail, $riskRegister) {
    $trail->parent('risk-register-ap.index');
    $trail->push('View Risiko', route('risk-register-ap.view', [$riskRegister]));
});

// Periode Anak Perusahaan > Data Monitoring
Breadcrumbs::for('risk-register-ap.monitorings.index', function (BreadcrumbTrail $trail, $period) {
    $trail->parent('risk-register-ap.periods');
    $trail->push('Data Monitoring', route('risk-register-ap.monitorings.index', [$period]));
});

// Periode Anak Perusahaan > Data Monitoring > View Data Monitoring
Breadcrumbs::for('risk-register-ap.monitorings.show', function (BreadcrumbTrail $trail, $period, $monitoring) {
    $trail->parent('risk-register-ap.monitorings.index', $period);
    $trail->push('View Data Monitoring', route('risk-register-ap.monitorings.show', [$period, $monitoring]));
});

// Periode Anak Perusahaan > Data Monitoring > Edit Data Monitoring
Breadcrumbs::for('risk-register-ap.monitorings.edit', function (BreadcrumbTrail $trail, $period, $monitoring) {
    $trail->parent('risk-register-ap.monitorings.index', $period);
    $trail->push('Edit Data Monitoring', route('risk-register-ap.monitorings.edit', [$period, $monitoring]));
});

// Periode Anak Perusahaan > Loss Event Anak Perusahaan
Breadcrumbs::for('ap-led.index-by-periode', function (BreadcrumbTrail $trail, $periode) {
    $trail->parent('risk-register-ap.periods');
    $trail->push('Loss Event Anak Perusahaan', route('ap-led.index-by-periode', [$periode]));
});

// Periode Anak Perusahaan > Loss Event Anak Perusahaan > Tambah Loss Event Anak Perusahaan
Breadcrumbs::for('ap-led.create', function (BreadcrumbTrail $trail, $periode) {
    $trail->parent('ap-led.index-by-periode', $periode);
    $trail->push('Tambah Loss Event Anak Perusahaan', route('ap-led.create', [$periode]));
});

// Periode Anak Perusahaan > Loss Event Anak Perusahaan > Edit Loss Event Anak Perusahaan
Breadcrumbs::for('ap-led.edit', function (BreadcrumbTrail $trail, $periode, $id) {
    $trail->parent('ap-led.index-by-periode', $periode);
    $trail->push('Edit Loss Event Anak Perusahaan', route('ap-led.edit', [$periode, $id]));
});

// Periode Anak Perusahaan > Loss Event Anak Perusahaan > Detail Loss Event Anak Perusahaan
Breadcrumbs::for('ap-led.show', function (BreadcrumbTrail $trail, $periode, $id) {
    $trail->parent('ap-led.index-by-periode', $periode);
    $trail->push('Detail Loss Event Anak Perusahaan', route('ap-led.show', [$periode, $id]));
});

// Periode Anak Perusahaan > Risk Register Anak Perusahaan > Kamus Risiko Unit
Breadcrumbs::for('kamus-risiko-ap.index', function (BreadcrumbTrail $trail) {
    $trail->parent('risk-register-ap.index');
    $trail->push('Kamus Risiko Anak Perusahaan', route('kamus-risiko-ap.index'));
});

// Project Periode List
Breadcrumbs::for('project-periode-list.index', function (BreadcrumbTrail $trail) {
    $trail->push('Project List', route('project-periode-list.index'));
});

// project-periode-list.show
Breadcrumbs::for('project-periode-list.show', function (BreadcrumbTrail $trail, $project) {
    $trail->parent('project-periode-list.index');
    $trail->push('Project Detail', route('project-periode-list.show', $project));
});

// Project List > Risk Register
Breadcrumbs::for('projects.risks.index', function (BreadcrumbTrail $trail, $project) {
    $trail->parent('project-periode-list.index');
    $trail->push('Risk Register', route('projects.risks.index', $project));
});

// Project List > Risk Register > Detail Risiko
Breadcrumbs::for('projects.risks.view', function (BreadcrumbTrail $trail, $project, $risk) {
    $trail->parent('projects.risks.index', $project);
    $trail->push('Detail Risiko', route('projects.risks.view', [$project, $risk]));
});

// Project List > Risk Register > Analisa
Breadcrumbs::for('projects.risks.analisa', function (BreadcrumbTrail $trail, $project, $risk) {
    $trail->parent('projects.risks.index', $project);
    $trail->push('Analisa', route('projects.risks.analisa', [$project, $risk]));
});

// projects.risks.rencana
Breadcrumbs::for('projects.risks.rencana', function (BreadcrumbTrail $trail, $project, $risk) {
    $trail->parent('projects.risks.index', $project);
    $trail->push('Rencana', route('projects.risks.rencana', [$project, $risk]));
});

// projects.risks.create
Breadcrumbs::for('projects.risks.create', function (BreadcrumbTrail $trail, $project) {
    $trail->parent('projects.risks.index', $project);
    $trail->push('Tambah Risiko Project', route('projects.risks.create', $project));
});

// projects.risks.edit
Breadcrumbs::for('projects.risks.edit', function (BreadcrumbTrail $trail, $project, $risk) {
    $trail->parent('projects.risks.index', $project);
    $trail->push('Edit Risiko Project', route('projects.risks.edit', [$project, $risk]));
});

// projects.monitorings.index
Breadcrumbs::for('projects.monitorings.index', function (BreadcrumbTrail $trail, $project) {
    $trail->parent('project-periode-list.index');
    $trail->push('Data Monitoring', route('projects.monitorings.index', $project));
});

// projects.monitorings.show
Breadcrumbs::for('projects.monitorings.show', function (BreadcrumbTrail $trail, $project, $monitoring) {
    $trail->parent('projects.monitorings.index', $project);
    $trail->push('Detail Monitoring', route('projects.monitorings.show', [$project, $monitoring]));
});

// projects.monitorings.edit
Breadcrumbs::for('projects.monitorings.edit', function (BreadcrumbTrail $trail, $project, $monitoring) {
    $trail->parent('projects.monitorings.index', $project);
    $trail->push('Monitoring', route('projects.monitorings.edit', [$project, $monitoring]));
});

// projects.monitorings.documents.index
Breadcrumbs::for('projects.monitorings.documents.index', function (BreadcrumbTrail $trail, $monitoring, $quarter) {
    $projectRisk = ProjectRisk::find($monitoring);
    $trail->parent('projects.monitorings.index', ['project' => $projectRisk->project_periode_list_id]);
    $trail->push('Dokumen Monitoring', route('projects.monitorings.documents.index', ['monitoring' => $monitoring, 'quarter' => $quarter]));
});

// projects.leds.index
Breadcrumbs::for('project-led.index-by-project', function (BreadcrumbTrail $trail, $projectId) {
    $trail->parent('project-periode-list.index');
    $trail->push('Loss Event Project', route('project-led.index-by-project', $projectId));
});

Breadcrumbs::for('project-led.create', function (BreadcrumbTrail $trail, $projectId) {
    $trail->parent('project-led.index-by-project', $projectId);
    $trail->push('Tambah Loss Event Project', route('project-led.create', ['project' => $projectId]));
});

Breadcrumbs::for('project-led.edit', function (BreadcrumbTrail $trail, $projectId, $id) {
    $trail->parent('project-led.index-by-project', $projectId);
    $trail->push('Edit Loss Event Project', route('project-led.edit', ['project' => $projectId, $id]));
});

Breadcrumbs::for('project-led.show', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('project-led.index');
    $trail->push('Detail Loss Event Project', route('project-led.show', $id));
});

// Kamus Risiko Project
Breadcrumbs::for('kamus-risiko-project.index', function (BreadcrumbTrail $trail) {
    $trail->parent('project-periode-list.index');
    $trail->push('Kamus Risiko Project', route('kamus-risiko-project.index'));
});

// rmi-period.index
Breadcrumbs::for('rmi-period.index', function (BreadcrumbTrail $trail) {
    $trail->push('Periode RMI', route('rmi-period.index'));
});

// rmi-period.show
Breadcrumbs::for('rmi-period.show', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('rmi-period.index');
    $trail->push('Detail Periode RMI', route('rmi-period.show', $id));
});

// rmi-period.question
Breadcrumbs::for('rmi-period.question', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('rmi-period.index');
    $trail->push('Set Pertanyaan', route('rmi-period.question', $id));
});

// question.index
Breadcrumbs::for('question.index', function (BreadcrumbTrail $trail) {
    $trail->push('Pertanyaan Survey', route('question.index'));
});

// question.show
Breadcrumbs::for('question.show', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('question.index');
    $trail->push('Detail Pertanyaan Survey', route('question.show', $id));
});


// Rekomendasi Risiko
Breadcrumbs::for('rekomendasi-risiko.index', function (BreadcrumbTrail $trail) {
    $trail->push('Rekomendasi Risiko', route('rekomendasi-risiko.index'));
});

// Rekomendasi Risiko > {Nama Divisi} Periode {Tahun}
Breadcrumbs::for('rekomendasi-risiko.show', function (BreadcrumbTrail $trail, $unit, $periode) {
    $trail->parent('rekomendasi-risiko.index');
    $trail->push(
        "Divisi {$unit->name} Periode {$periode->tahun}", 
        route('rekomendasi-risiko.show', [$unit, $periode])
    );
});

// Rekomendasi Risiko > {Nama Divisi} Periode {Tahun} > Tambah
Breadcrumbs::for('rekomendasi-risiko.create', function (BreadcrumbTrail $trail, $unit, $periode) {
    $trail->parent('rekomendasi-risiko.show', $unit, $periode);
    $trail->push(
        'Tambah Rekomendasi', 
        route('rekomendasi-risiko.create', [$unit, $periode])
    );
});

// Rekomendasi Risiko > {Nama Divisi} Periode {Tahun} > Edit
Breadcrumbs::for('rekomendasi-risiko.edit', function (BreadcrumbTrail $trail, $rekomendasi) {
    // Memuat relasi agar bisa mendapatkan unit dan periode
    $rekomendasi->load('unit', 'periode');
    $trail->parent('rekomendasi-risiko.show', $rekomendasi->unit, $rekomendasi->periode);
    $trail->push(
        'Edit Rekomendasi', 
        route('rekomendasi-risiko.edit', $rekomendasi)
    );
});

// Rekomendasi Risiko > {Nama Divisi} Periode {Tahun} > View
Breadcrumbs::for('rekomendasi-risiko.view', function (BreadcrumbTrail $trail, $rekomendasi) {
    // Memuat relasi agar bisa mendapatkan unit dan periode
    $rekomendasi->load('unit', 'periode');
    $trail->parent('rekomendasi-risiko.show', $rekomendasi->unit, $rekomendasi->periode);
    $trail->push(
        'View Rekomendasi', 
        route('rekomendasi-risiko.view', $rekomendasi)
    );
});
