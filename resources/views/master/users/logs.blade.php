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
            <h2 class="h4 mb-0">Log Perubahan User</h2>
            <small class="text-muted">Riwayat perubahan data user dari create, update, delete, dan restore.</small>
          </div>
          <div class="ms-auto d-flex gap-2">
            <a href="{{ route('users.logs') }}" class="btn btn-outline-primary btn-sm">
              <span class="bx bx-history"></span>
              <span class="ms-1">Semua Log User</span>
            </a>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
              <span class="bx bx-arrow-back"></span>
              <span class="ms-1">Kembali ke User</span>
            </a>
          </div>
        </div>
      </div>

      <div class="card-body">
        <form method="GET" action="{{ route('users.logs') }}" class="row g-2 align-items-end mb-3">
          <div class="col-md-5 col-lg-4">
            <label for="user_id" class="form-label">User</label>
            <select name="user_id" id="user_id" class="form-select">
              <option value="">Semua User</option>
              @foreach ($users as $user)
                <option value="{{ $user->id }}" {{ (string) $userId === (string) $user->id ? 'selected' : '' }}>
                  {{ $user->nip ? $user->nip . ' - ' : '' }}{{ $user->name }}{{ $user->email ? ' (' . $user->email . ')' : '' }}
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
              <option value="restored" {{ $event === 'restored' ? 'selected' : '' }}>Restored</option>
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
                <th style="min-width: 320px;">User</th>
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
                  $displayName = data_get($audit, 'auditable.name') ?? data_get($newValues, 'name') ?? data_get($oldValues, 'name') ?? '-';
                  $displayNip = data_get($audit, 'auditable.nip') ?? data_get($newValues, 'nip') ?? data_get($oldValues, 'nip');
                  $displayEmail = data_get($audit, 'auditable.email') ?? data_get($newValues, 'email') ?? data_get($oldValues, 'email');
                  $eventClass = match ($audit->event) {
                      'created' => 'bg-success text-white',
                      'deleted' => 'bg-danger text-white',
                      'restored' => 'bg-warning text-dark',
                      default => 'bg-primary text-white',
                  };
                  $formatValue = function ($value, $key = null) use ($relationMaps) {
                      if (is_null($value)) {
                          return '-';
                      }
                      if (is_bool($value)) {
                          return $value ? 'true' : 'false';
                      }
                      if ($key === 'user_projects') {
                          $projectMap = $relationMaps['user_projects'] ?? [];
                          $projectIds = is_array($value) ? $value : [];

                          if (empty($projectIds)) {
                              return '-';
                          }

                          $labels = collect($projectIds)->map(function ($id) use ($projectMap) {
                              $projectId = (int) $id;
                              return $projectMap[$projectId] ?? ('#' . $projectId);
                          })->all();

                          return implode(', ', $labels);
                      }
                      if (is_array($value)) {
                          return json_encode($value, JSON_UNESCAPED_UNICODE);
                      }

                      if ($key && isset($relationMaps[$key])) {
                          $label = $relationMaps[$key][(int) $value] ?? null;
                          return $label ? $label . ' (#' . $value . ')' : '#' . $value;
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
                    <div class="fw-semibold">{{ $displayName }}</div>
                    <div class="text-muted">{{ $displayNip ?: '-' }}</div>
                    <div class="text-muted">{{ $displayEmail ?: '-' }}</div>
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
                              <span class="text-danger">{{ $formatValue($oldValues[$key] ?? null, $key) }}</span>
                              <span class="mx-1">&rarr;</span>
                              <span class="text-success">{{ $formatValue($newValues[$key] ?? null, $key) }}</span>
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
                    Belum ada log perubahan user.
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
