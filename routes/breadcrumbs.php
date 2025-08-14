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
Breadcrumbs::for('unit-led.index-by-unit', function (BreadcrumbTrail $trail, $unitId) {
    $trail->parent('risk-register-unit.periods');
    $trail->push('Loss Event Divisi', route('unit-led.index-by-unit', [$unitId]));
});

// Periode Divisi > Loss Event Divisi > Tambah Loss Event Divisi
Breadcrumbs::for('unit-led.create', function (BreadcrumbTrail $trail, $unitId) {
    // Kirimkan $unitId ke parent, karena parent membutuhkannya
    $trail->parent('unit-led.index-by-unit', $unitId); 
    // Route 'create' tidak butuh parameter
    $trail->push('Tambah Loss Event Divisi', route('unit-led.create')); 
});

// Periode Divisi > Risk Register Divisi >Kamus Risiko Unit
Breadcrumbs::for('kamus-risiko-unit.index', function (BreadcrumbTrail $trail) {
    $trail->parent('risk-register-unit.index');
    $trail->push('Kamus Risiko Unit', route('kamus-risiko-unit.index'));
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

Breadcrumbs::for('project-led.create', function (BreadcrumbTrail $trail) {
    $trail->parent('project-led.index');
    $trail->push('Tambah Loss Event Project', route('project-led.create'));
});

Breadcrumbs::for('project-led.edit', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('project-led.index');
    $trail->push('Tambah Loss Event Project', route('project-led.edit', $id));
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
