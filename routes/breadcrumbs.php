<?php

use App\Models\ProjectRisk;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

// Home
Breadcrumbs::for('home', function (BreadcrumbTrail $trail) {
    $trail->push('Home', route('home'));
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
Breadcrumbs::for('project-led.index', function (BreadcrumbTrail $trail) {
    $trail->push('Loss Event Project', route('project-led.index'));
});

Breadcrumbs::for('project-led.create', function (BreadcrumbTrail $trail) {
    $trail->parent('project-led.index');
    $trail->push('Tambah Loss Event Project', route('project-led.create'));
});

Breadcrumbs::for('project-led.edit', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('project-led.index');
    $trail->push('Tambah Loss Event Project', route('project-led.edit', $id));
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
