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
            <h2 class="h4 mb-0">Log Perubahan Project</h2>
            <small class="text-muted">Riwayat perubahan dari sinkronisasi dan update manual</small>
          </div>
          <div class="ms-auto">
            <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary btn-sm">
              <span class="bx bx-arrow-back"></span>
              <span class="ms-1">Kembali ke Project</span>
            </a>
          </div>
        </div>
      </div>

      <div class="card-body">
        <form method="GET" action="{{ route('projects.logs') }}" class="row g-2 align-items-end mb-3">
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
            <label for="event" class="form-label">Event</label>
            <select name="event" id="event" class="form-select">
              <option value="">Semua Event</option>
              <option value="created" {{ $event === 'created' ? 'selected' : '' }}>Created</option>
              <option value="updated" {{ $event === 'updated' ? 'selected' : '' }}>Updated</option>
              <option value="deleted" {{ $event === 'deleted' ? 'selected' : '' }}>Deleted</option>
            </select>
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
                <th style="min-width: 120px;">Event</th>
                <th style="min-width: 280px;">Project</th>
                <th style="min-width: 180px;">Pengguna</th>
                <th style="min-width: 460px;">Perubahan</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($audits as $index => $audit)
                @php
                  $oldValues = is_array($audit->old_values) ? $audit->old_values : (json_decode($audit->old_values ?? '[]', true) ?: []);
                  $newValues = is_array($audit->new_values) ? $audit->new_values : (json_decode($audit->new_values ?? '[]', true) ?: []);
                  $changedKeys = array_values(array_unique(array_merge(array_keys($oldValues), array_keys($newValues))));
                  $rowNumber = ($audits->firstItem() ?? 1) + $index;
                  $projectName = data_get($audit, 'auditable.project_name') ?? data_get($newValues, 'project_name') ?? data_get($oldValues, 'project_name') ?? '-';
                  $projectCode = data_get($audit, 'auditable.project_code') ?? data_get($newValues, 'project_code') ?? data_get($oldValues, 'project_code') ?? '-';
                  $profitCenter = data_get($audit, 'auditable.profit_center') ?? data_get($newValues, 'profit_center') ?? data_get($oldValues, 'profit_center') ?? '-';
                  $eventClass = match ($audit->event) {
                      'created' => 'bg-success text-white',
                      'deleted' => 'bg-danger text-white',
                      default => 'bg-primary text-white',
                  };
                  $formatValue = function ($value) {
                      if (is_null($value)) {
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
                    <span class="badge {{ $eventClass }}">
                      {{ $audit->event_label }}
                    </span>
                  </td>
                  <td>
                    <div class="fw-semibold">
                      {{ $projectCode }}
                      <span class="text-muted">| {{ $profitCenter }}</span>
                    </div>
                    <div class="text-muted">{{ $projectName }}</div>
                  </td>
                  <td>{{ data_get($audit, 'user.name', 'System') }}</td>
                  <td>
                    @if (count($changedKeys) === 0)
                      <span class="text-muted">Tidak ada detail perubahan</span>
                    @else
                      <details>
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
                  <td colspan="6" class="text-center py-4 text-muted">
                    Belum ada log perubahan project.
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
