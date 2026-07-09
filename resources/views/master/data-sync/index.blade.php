@extends('layouts.default')

@section('dashboard')
@include('partials.success-message')

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex flex-between-center">
        <div>
          <h2 class="h4 mb-1">Sinkronisasi Data</h2>
          <p class="mb-0 text-muted">Sinkronisasi Divisi, Project, dan User dari sumber data terintegrasi.</p>
        </div>
      </div>

      <form method="POST" action="{{ route('data-sync.sync') }}">
        @csrf
        <div class="card-body">
          <div class="mb-4">
            <label class="form-label fw-semibold d-block">Pilih Data yang Disinkronisasi</label>

            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" value="division" id="sync-division" name="sync_types[]"
                {{ in_array('division', old('sync_types', [])) ? 'checked' : '' }}>
              <label class="form-check-label" for="sync-division">Divisi</label>
            </div>

            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" value="project" id="sync-project" name="sync_types[]"
                {{ in_array('project', old('sync_types', [])) ? 'checked' : '' }}>
              <label class="form-check-label" for="sync-project">Project</label>
            </div>

            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="user" id="sync-user" name="sync_types[]"
                {{ in_array('user', old('sync_types', [])) ? 'checked' : '' }}>
              <label class="form-check-label" for="sync-user">User</label>
            </div>

            @error('sync_types')
            <div class="text-danger small mt-2">{{ $message }}</div>
            @enderror
          </div>

          <div id="user-sync-area" class="border rounded p-3 bg-light-subtle">
            <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
              <div>
                <h3 class="h6 mb-1">User Terpilih</h3>
                <p class="mb-0 text-muted small">Data yang akan disinkronkan: Unit, Jabatan, dan Assignment Project.</p>
              </div>
              <button type="button" class="btn btn-outline-primary btn-sm" id="btn-open-user-modal">
                <span class="bx bx-user-check"></span>
                <span class="ms-1">Pilih User</span>
              </button>
            </div>

            <div id="selected-users-wrapper" class="mt-3">
              <div id="selected-users-empty" class="text-muted small">Belum ada user dipilih.</div>
              <div id="selected-users-list" class="d-flex flex-wrap gap-2"></div>
            </div>

            <div id="selected-user-inputs"></div>

            @error('user_ids')
            <div class="text-danger small mt-2">{{ $message }}</div>
            @enderror
          </div>

          @if(session('sync_summary'))
            @php($summary = session('sync_summary'))
            <hr class="my-4">
            <h3 class="h6 mb-3">Ringkasan Hasil Sinkronisasi</h3>
            <div class="row g-3">
              @if(!is_null($summary['division'] ?? null))
              <div class="col-md-4">
                <div class="border rounded p-3 h-100">
                  <div class="fw-semibold mb-2">Divisi</div>
                  @if(($summary['division']['success'] ?? false))
                    <span class="badge bg-success">Berhasil</span>
                  @else
                    <span class="badge bg-danger">Gagal</span>
                    <p class="small text-danger mb-0 mt-2">{{ $summary['division']['message'] ?? 'Terjadi kesalahan.' }}</p>
                  @endif
                </div>
              </div>
              @endif

              @if(!is_null($summary['project'] ?? null))
              <div class="col-md-4">
                <div class="border rounded p-3 h-100">
                  <div class="fw-semibold mb-2">Project</div>
                  @if(($summary['project']['success'] ?? false))
                    <span class="badge bg-success">Berhasil</span>
                  @else
                    <span class="badge bg-danger">Gagal</span>
                  @endif
                  <p class="small mb-0 mt-2">
                    Updated: {{ $summary['project']['count_updated'] ?? 0 }},
                    Failed: {{ $summary['project']['count_failed'] ?? 0 }}
                  </p>
                  @if(!empty($summary['project']['message'] ?? null))
                    <p class="small text-danger mb-0 mt-1">{{ $summary['project']['message'] }}</p>
                  @endif
                </div>
              </div>
              @endif

              @if(!is_null($summary['user'] ?? null))
              <div class="col-md-4">
                <div class="border rounded p-3 h-100">
                  <div class="fw-semibold mb-2">User</div>
                  @if(($summary['user']['success'] ?? false))
                    <span class="badge bg-success">Berhasil</span>
                  @else
                    <span class="badge bg-warning text-dark">Selesai Sebagian</span>
                  @endif
                  <p class="small mb-0 mt-2">
                    Total: {{ $summary['user']['total'] ?? 0 }},
                    Updated: {{ $summary['user']['updated'] ?? 0 }},
                    Failed: {{ $summary['user']['failed'] ?? 0 }}
                  </p>

                  @if(!empty($summary['user']['failed_users'] ?? []))
                    <div class="small mt-2" style="max-height: 130px; overflow-y: auto;">
                      @foreach($summary['user']['failed_users'] as $failedUser)
                        <div class="text-danger">
                          {{ $failedUser['name'] ?? '-' }} ({{ $failedUser['nip'] ?? '-' }}): {{ $failedUser['message'] ?? '-' }}
                        </div>
                      @endforeach
                    </div>
                  @endif
                </div>
              </div>
              @endif
            </div>
          @endif
        </div>

        <div class="card-footer border-none">
          <button type="submit" class="btn btn-primary">
            <span class="bx bx-sync"></span>
            <span class="ms-1">Mulai Sinkronisasi</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="userPickerModal" tabindex="-1" aria-labelledby="userPickerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="userPickerModalLabel">Pilih User untuk Sinkronisasi</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <input type="text" class="form-control" id="user-search-input" placeholder="Cari berdasarkan NIP atau Nama">
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th style="width: 60px;">
                  <input type="checkbox" id="check-all-users">
                </th>
                <th>NIP</th>
                <th>Nama</th>
                <th>Email</th>
              </tr>
            </thead>
            <tbody id="user-picker-table-body">
              @foreach($users as $user)
              <tr data-search="{{ strtolower(($user->nip ?? '') . ' ' . ($user->name ?? '')) }}">
                <td>
                  <input type="checkbox" class="form-check-input user-picker-checkbox"
                    value="{{ $user->id }}"
                    data-name="{{ $user->name }}"
                    data-nip="{{ $user->nip }}"
                    data-email="{{ $user->email }}"
                    {{ in_array($user->id, old('user_ids', [])) ? 'checked' : '' }}>
                </td>
                <td>{{ $user->nip ?? '-' }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email ?? '-' }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btn-save-user-selection">Simpan Pilihan</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  $(function() {
    const userModal = new bootstrap.Modal(document.getElementById('userPickerModal'));
    const oldSelectedUserIds = @json(array_map('intval', old('user_ids', [])));

    const selectedUsers = {};

    oldSelectedUserIds.forEach(function(userId) {
      const checkbox = $('.user-picker-checkbox[value="' + userId + '"]');
      if (checkbox.length) {
        selectedUsers[userId] = {
          id: userId,
          name: checkbox.data('name') || '-',
          nip: checkbox.data('nip') || '-',
          email: checkbox.data('email') || '-',
        };
      }
    });

    function renderSelectedUsers() {
      const listWrapper = $('#selected-users-list');
      const emptyState = $('#selected-users-empty');
      const hiddenInputs = $('#selected-user-inputs');
      const selectedValues = Object.values(selectedUsers);

      listWrapper.empty();
      hiddenInputs.empty();

      if (selectedValues.length === 0) {
        emptyState.removeClass('d-none');
        return;
      }

      emptyState.addClass('d-none');

      selectedValues.forEach(function(user) {
        const badge = $('<span class="badge bg-primary d-inline-flex align-items-center gap-1"></span>');
        badge.text((user.nip ? '[' + user.nip + '] ' : '') + user.name);
        listWrapper.append(badge);

        hiddenInputs.append('<input type="hidden" name="user_ids[]" value="' + user.id + '">');
      });
    }

    function toggleUserArea() {
      const isUserSyncChecked = $('#sync-user').is(':checked');
      $('#user-sync-area').toggleClass('opacity-50', !isUserSyncChecked);
      $('#btn-open-user-modal').prop('disabled', !isUserSyncChecked);
    }

    $('#sync-user').on('change', toggleUserArea);

    $('#btn-open-user-modal').on('click', function() {
      if (!$('#sync-user').is(':checked')) {
        return;
      }
      userModal.show();
    });

    $('#check-all-users').on('change', function() {
      const shouldCheck = $(this).is(':checked');
      $('#user-picker-table-body tr:visible .user-picker-checkbox').prop('checked', shouldCheck);
    });

    $('#user-search-input').on('keyup', function() {
      const query = ($(this).val() || '').toLowerCase().trim();

      $('#user-picker-table-body tr').each(function() {
        const searchText = $(this).data('search');
        const isVisible = !query || (searchText && searchText.includes(query));
        $(this).toggle(isVisible);
      });
    });

    $('#btn-save-user-selection').on('click', function() {
      $('.user-picker-checkbox').each(function() {
        const userId = parseInt($(this).val(), 10);

        if ($(this).is(':checked')) {
          selectedUsers[userId] = {
            id: userId,
            name: $(this).data('name') || '-',
            nip: $(this).data('nip') || '-',
            email: $(this).data('email') || '-',
          };
        } else {
          delete selectedUsers[userId];
        }
      });

      renderSelectedUsers();
      userModal.hide();
    });

    toggleUserArea();
    renderSelectedUsers();
  });
</script>
@endpush
