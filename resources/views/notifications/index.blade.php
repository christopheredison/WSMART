@extends('layouts.default')

@section('title', 'Semua Notifikasi')

@push('styles')
<style>
    /* Custom CSS untuk truncate 3 baris */
    .message-truncate-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        white-space: normal;
        max-width: 600px; /* Atur lebar maksimal agar wrap bekerja */
    }
</style>
@endpush

@section('dashboard')
<input type="hidden" name="_token" value="{{ csrf_token() }}">

<div class="card mb-3">
    <div class="card-header">
        <div class="row flex-between-center">
            <div class="col-auto">
                <h5 class="mb-0">Semua Notifikasi</h5>
            </div>
            <div class="col-auto d-flex">
                <button class="btn btn-sm btn-outline-primary" id="markAllAsRead">
                    <span class="fas fa-check me-1"></span>Tandai Semua Dibaca
                </button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm mb-0 fs--1 table-striped" id="table-notifikasi">
                <thead class="bg-200">
                    <tr>
                        <th class="white-space-nowrap">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" data-bulk-select='{"body":"table-notifikasi-body","actions":"table-notifikasi-actions","replacedElement":"table-notifikasi-replace-element"}' />
                            </div>
                        </th>
                        <th class="text-center">Status</th>
                        <th>Notifikasi</th>
                        <th style="min-width: 150px;">Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="table-notifikasi-body">
                @if(isset($notifications) && $notifications->count() > 0)
                    @foreach($notifications as $notification)
                    <tr class="{{ is_null($notification->read_at) ? 'fw-bold' : '' }}" data-id="{{ $notification->id }}">
                        <td class="white-space-nowrap align-middle">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" id="checkbox-{{ $notification->id }}"
                                    data-bulk-select-row="data-bulk-select-row" />
                            </div>
                        </td>
                        <td class="status text-center align-middle">
                            @if(is_null($notification->read_at))
                                <span class="badge rounded-pill bg-info">Baru</span>
                            @else
                                <span class="badge rounded-pill bg-success">Dibaca</span>
                            @endif
                        </td>
                        <td class="notifikasi align-middle py-3">
                            <div class="d-flex align-items-start">
                                <div class="avatar avatar-xl me-3 mt-1">
                                    <div class="avatar-name rounded-circle bg-soft-primary text-primary">
                                        <span class="{{ $notification->icon ?? 'bx bx-bell' }} fs-1"></span>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-1">{{ $notification->title }}</h6>
                                    {{-- Truncate 3 baris --}}
                                    <p class="mb-0 text-800 message-truncate-3">
                                        {{ $notification->message }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        {{-- Format Tanggal DD MMM YYYY HH:mm --}}
                        <td class="tanggal align-middle">
                            {{ \Carbon\Carbon::parse($notification->created_at)->setTimezone('Asia/Jakarta')->translatedFormat('d M Y H:i') }} WIB
                        </td>
                        <td class="white-space-nowrap align-middle">
                            {{-- Tombol Lihat Langsung Redirect --}}
                            <a href="{{ $notification->link ?? '#' }}"
                              class="btn-input-icon view-notification-link"
                              data-id="{{ $notification->id }}"
                              data-read="{{ !is_null($notification->read_at) }}">
                                <span class="bx bx-link-external" data-bs-toggle="tooltip" title="Buka Tautan"></span>
                            </a>

                            @if(is_null($notification->read_at))
                                <button class="btn-input-icon mark-as-read" data-id="{{ $notification->id }}">
                                    <span class="bx bx-check text-success" data-bs-toggle="tooltip" title="Tandai Dibaca"></span>
                                </button>
                            @else
                                <button class="btn-input-icon mark-as-unread" data-id="{{ $notification->id }}">
                                    <span class="bx bx-undo text-warning" data-bs-toggle="tooltip" title="Tandai Belum Dibaca"></span>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <div class="d-flex flex-column align-items-center">
                                <span class="bx bx-bell-off fs-3 mb-2"></span>
                                <h6>Tidak ada notifikasi</h6>
                                <p class="mb-0 text-500">Anda belum memiliki notifikasi saat ini</p>
                            </div>
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-light">
        <div class="d-flex justify-content-center">
            <div id="table-notifikasi-actions" class="d-none">
                <div class="d-flex">
                    <select class="form-select form-select-sm" aria-label="Bulk actions">
                        <option selected>Pilih Tindakan</option>
                        <option value="mark-as-read">Tandai Dibaca</option>
                        <option value="mark-as-unread">Tandai Belum Dibaca</option>
                    </select>
                    <button class="btn btn-falcon-default btn-sm ms-2" type="button">Terapkan</button>
                </div>
            </div>
            {{-- Menggunakan null coalescing operator agar tidak error jika variable links tidak ada --}}
            <div id="table-notifikasi-replace-element">
                {!! $notifications->links() ?? '' !!}
            </div>
        </div>
    </div>
</div>

{{-- MODAL DIHAPUS SESUAI REQUEST --}}

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var token = $('input[name="_token"]').val();

        // 1. Handle Klik Tombol Link (Mata/Link)
        $('.view-notification-link').on('click', function(e) {
            const link = $(this).attr('href');
            const id = $(this).data('id');
            const isRead = $(this).data('read');

            // Jika link valid dan belum dibaca
            if (link && link !== '#' && !isRead) {
                e.preventDefault(); // Cegah redirect default dulu

                // Mark as read via AJAX
                $.ajax({
                    url: '/notifications/' + id + '/read',
                    type: 'POST',
                    data: { _token: token },
                    success: function() {
                        // Setelah sukses mark read, baru redirect
                        window.location.href = link;
                    },
                    error: function() {
                        // Fallback jika error, tetap redirect
                        window.location.href = link;
                    }
                });
            }
            // Jika sudah dibaca, biarkan href default bekerja
        });

        // 2. Mark Single Read
        $('.mark-as-read').on('click', function() {
            var id = $(this).data('id');
            // Logic AJAX sama seperti sebelumnya, reload page
            $.ajax({
                url: '/notifications/' + id + '/read',
                type: 'POST',
                data: { _token: token },
                success: function(response) {
                    if (response.success) location.reload();
                }
            });
        });

        // 3. Mark Single Unread
        $('.mark-as-unread').on('click', function() {
            var id = $(this).data('id');
            $.ajax({
                url: '/notifications/' + id + '/unread',
                type: 'POST',
                data: { _token: token },
                success: function(response) {
                    if (response.success) location.reload();
                }
            });
        });

        // 4. Mark All Read
        $('#markAllAsRead').on('click', function(e) {
            e.preventDefault();
            $.ajax({
                url: '/notifications/read-all',
                type: 'POST',
                data: { _token: token },
                success: function(response) {
                    if (response.success) location.reload();
                }
            });
        });

        // Init Tooltip
        $('[data-bs-toggle="tooltip"]').tooltip();

        // Handle Bulk Actions (sama seperti sebelumnya)
        $('#table-notifikasi-actions button').click(function() {
            var action = $('#table-notifikasi-actions select').val();
            var selectedIds = [];
            $('input[data-bulk-select-row]:checked').each(function() {
                selectedIds.push($(this).closest('tr').data('id'));
            });

            if (selectedIds.length === 0) return alert('Pilih notifikasi dulu');

            var url = action === 'mark-as-read' ? "{{ route('notifications.markMultipleAsRead') }}" : "{{ route('notifications.markMultipleAsUnread') }}";

            $.ajax({
                url: url,
                type: 'POST',
                data: { _token: token, ids: selectedIds },
                success: function(res) { if(res.success) location.reload(); }
            });
        });
    });
</script>
@endpush
