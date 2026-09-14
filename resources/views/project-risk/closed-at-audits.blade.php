@extends('layouts.default')

@section('dashboard')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <div class="d-flex align-items-center gap-3">
          <div class="lead__icon bg-info-subtle">
            <div class="svg-icon svg-icon-info">
              @include('partials.icon-layer')
            </div>
          </div>
          <div>
            <h2 class="h4 mb-0">Audit Log Sync Closed At Risiko Proyek</h2>
            <small class="text-muted">Riwayat perubahan closed_at dari command project-risks:sync-closed-at</small>
          </div>
        </div>
      </div>

      <div class="card-body">
        <form method="GET" action="{{ route('project-risks.closed-at-audits') }}" class="row g-2 align-items-end mb-3">
          <div class="col-md-5 col-lg-4">
            <label for="project_id" class="form-label">Project</label>
            <select name="project_id" id="project_id" class="form-select">
              <option value="">Semua Project</option>
              @foreach ($projects as $project)
                <option value="{{ $project->id }}" {{ (string) $projectId === (string) $project->id ? 'selected' : '' }}>
                  {{ $project->project_code }} - {{ $project->project_name }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3 col-lg-2">
            <label for="risk_id" class="form-label">Risk ID</label>
            <input type="number" name="risk_id" id="risk_id" class="form-control" value="{{ $riskId }}" placeholder="ID risiko">
          </div>
          <div class="col-auto">
            <button class="btn btn-submit" type="submit">
              <span class="bx bx-filter-alt"></span>
              <span class="ms-1">Terapkan</span>
            </button>
          </div>
        </form>

        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th style="width: 72px;">#</th>
                <th style="min-width: 160px;">Waktu</th>
                <th style="min-width: 100px;">Event</th>
                <th style="min-width: 240px;">Project</th>
                <th style="min-width: 280px;">Risiko</th>
                <th style="min-width: 140px;">Pengguna</th>
                <th style="min-width: 420px;">Perubahan closed_at</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($audits as $index => $audit)
                @php
                  $oldValues = is_array($audit->old_values) ? $audit->old_values : (json_decode($audit->old_values ?? '[]', true) ?: []);
                  $newValues = is_array($audit->new_values) ? $audit->new_values : (json_decode($audit->new_values ?? '[]', true) ?: []);
                  $changedKeys = array_values(array_unique(array_merge(array_keys($oldValues), array_keys($newValues))));
                  $rowNumber = ($audits->firstItem() ?? 1) + $index;
                  $project = data_get($audit, 'projectRisk.project');
                  $risk = $audit->projectRisk;
                  $formatValue = function ($value) {
                      if (is_null($value) || $value === '') {
                          return '-';
                      }
                      if (is_bool($value)) {
                          return $value ? 'true' : 'false';
                      }
                      if (is_array($value)) {
                          return json_encode($value, JSON_UNESCAPED_UNICODE);
                      }
                      return (string) $value;
                  };
                @endphp
                <tr>
                  <td>{{ $rowNumber }}</td>
                  <td>{{ optional($audit->created_at)->format('Y-m-d H:i:s') ?? '-' }}</td>
                  <td>
                    <span class="badge bg-primary text-white">{{ $audit->event_label }}</span>
                  </td>
                  <td>
                    <div class="fw-semibold">{{ $project->project_code ?? '-' }}</div>
                    <div class="text-muted">{{ $project->project_name ?? '-' }}</div>
                  </td>
                  <td>
                    <div class="fw-semibold">ID {{ $audit->auditable_id }}</div>
                    <div class="text-muted">{{ $risk->deskripsi_peristiwa_risiko ?? $risk->rencana_kegiatan ?? '-' }}</div>
                  </td>
                  <td>{{ data_get($audit, 'user.name', 'System / Console') }}</td>
                  <td>
                    <div>
                      <span class="text-danger">{{ $formatValue($oldValues['closed_at'] ?? null) }}</span>
                      <span class="mx-1">&rarr;</span>
                      <span class="text-success">{{ $formatValue($newValues['closed_at'] ?? null) }}</span>
                    </div>
                    @if (!empty($newValues['monitoring_month']) || !empty($newValues['monitoring_tahun']))
                      <small class="text-muted">
                        Monitoring: {{ $newValues['monitoring_month'] ?? '-' }}/{{ $newValues['monitoring_tahun'] ?? '-' }}
                        @if (!empty($newValues['monitoring_id']))
                          (ID {{ $newValues['monitoring_id'] }})
                        @endif
                      </small>
                    @endif
                    @if (count($changedKeys) > 1)
                      <details class="mt-1">
                        <summary>{{ count($changedKeys) }} field tercatat</summary>
                        <ul class="mb-0 mt-2 ps-3">
                          @foreach ($changedKeys as $key)
                            <li>
                              <strong>{{ str_replace('_', ' ', $key) }}</strong>:
                              <span class="text-danger">{{ $formatValue($oldValues[$key] ?? null) }}</span>
                              <span class="mx-1">&rarr;</span>
                              <span class="text-success">{{ $formatValue($newValues[$key] ?? null) }}</span>
                            </li>
                          @endforeach
                        </ul>
                      </details>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center py-4 text-muted">
                    Belum ada audit log sync closed_at.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-center mt-3">
          {!! $audits->links() !!}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
