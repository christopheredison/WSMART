@extends('layouts.default')

@section('title', 'Semua Notifikasi')

@push('scripts')
<script>
    $(document).ready(function() {
        // Ambil token dari form yang ada di halaman
        var token = $('input[name="_token"]').val();
        
        // Fungsi untuk menandai notifikasi sebagai dibaca
        $('.mark-as-read').on('click', function() {
            var id = $(this).data('id');
            var row = $(this).closest('tr');
            
            $.ajax({
                url: '/notifications/' + id + '/read',
                type: 'POST',
                data: {
                    _token: token
                },
                success: function(response) {
                    if (response.success) {
                        // Perbarui tampilan
                        row.removeClass('fw-bold');
                        row.find('td:first-child .badge').removeClass('bg-primary').addClass('bg-secondary').text('Dibaca');
                        
                        // Ganti tombol
                        var markAsReadBtn = row.find('.mark-as-read');
                        markAsReadBtn.removeClass('btn-falcon-success mark-as-read').addClass('btn-falcon-warning mark-as-unread');
                        markAsReadBtn.html('<span class="fas fa-undo"></span>');
                        
                        // Tambahkan event listener baru
                        markAsReadBtn.off('click').on('click', function() {
                            var id = $(this).data('id');
                            // Panggil fungsi langsung
                            var row = $(this).closest('tr');
                            
                            $.ajax({
                                url: '/notifications/' + id + '/unread',
                                type: 'POST',
                                data: {
                                    _token: token
                                },
                                success: function(response) {
                                    if (response.success) {
                                        // Reload halaman untuk menampilkan perubahan
                                        location.reload();
                                    }
                                },
                                error: function(xhr) {
                                    console.error('Error marking notification as unread:', xhr.responseText);
                                }
                            });
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Error marking notification as read:', xhr.responseText);
                    alert('Terjadi kesalahan saat menandai notifikasi sebagai dibaca.');
                }
            });
        });
        
        // Fungsi untuk menandai notifikasi sebagai belum dibaca
        $('.mark-as-unread').on('click', function() {
            var id = $(this).data('id');
            var row = $(this).closest('tr');
            
            $.ajax({
                url: '/notifications/' + id + '/unread',
                type: 'POST',
                data: {
                    _token: token
                },
                success: function(response) {
                    if (response.success) {
                        // Perbarui tampilan
                        row.addClass('fw-bold');
                        row.find('td:first-child .badge').removeClass('bg-secondary').addClass('bg-primary').text('Baru');
                        
                        // Ganti tombol
                        var markAsUnreadBtn = row.find('.mark-as-unread');
                        markAsUnreadBtn.removeClass('btn-falcon-warning mark-as-unread').addClass('btn-falcon-success mark-as-read');
                        markAsUnreadBtn.html('<span class="fas fa-check"></span>');
                        
                        // Tambahkan event listener baru
                        markAsUnreadBtn.off('click').on('click', function() {
                            var id = $(this).data('id');
                            // Panggil fungsi langsung
                            var row = $(this).closest('tr');
                            
                            $.ajax({
                                url: '/notifications/' + id + '/read',
                                type: 'POST',
                                data: {
                                    _token: token
                                },
                                success: function(response) {
                                    if (response.success) {
                                        // Reload halaman untuk menampilkan perubahan
                                        location.reload();
                                    }
                                },
                                error: function(xhr) {
                                    console.error('Error marking notification as read:', xhr.responseText);
                                }
                            });
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Error marking notification as unread:', xhr.responseText);
                    alert('Terjadi kesalahan saat menandai notifikasi sebagai belum dibaca.');
                }
            });
        });
        
        // Fungsi untuk menandai semua notifikasi sebagai dibaca
        $('#markAllAsRead').on('click', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: '/notifications/read-all',
                type: 'POST',
                data: {
                    _token: token
                },
                success: function(response) {
                    if (response.success) {
                        // Reload halaman untuk menampilkan perubahan
                        location.reload();
                    }
                },
                error: function(xhr) {
                    console.error('Error marking all notifications as read:', xhr.responseText);
                    alert('Terjadi kesalahan saat menandai semua notifikasi sebagai dibaca.');
                }
            });
        });
    });
</script>
@endpush

@section('dashboard')
<!-- Hidden CSRF token field -->
<input type="hidden" name="_token" value="{{ csrf_token() }}">

<div class="card mb-3">
    <div class="card-header">
        <div class="row flex-between-center">
            <div class="col-auto">
                <h5 class="mb-0">Semua Notifikasi</h5>
            </div>
            <div class="col-auto d-flex">
                <button class="btn btn-falcon-default btn-sm me-1" id="markAllAsRead">
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
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="table-notifikasi-body">
                @forelse($notifications as $notification)
                <tr class="{{ is_null($notification->read_at) ? 'fw-bold' : '' }}" data-id="{{ $notification->id }}">
                        <td class="white-space-nowrap">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" id="checkbox-{{ $notification->id }}"
                                    data-bulk-select-row="data-bulk-select-row" />
                            </div>
                        </td>
                        <td class="status text-center">
                            @if(is_null($notification->read_at))
                                <span class="badge rounded-pill bg-primary">Baru</span>
                            @else
                                <span class="badge rounded-pill bg-secondary">Dibaca</span>
                            @endif
                        </td>
                        <td class="notifikasi">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-xl me-2">
                                    <div class="avatar-name rounded-circle bg-soft-primary text-primary">
                                        <span class="{{ $notification->icon ?? 'bx bx-bell' }}"></span>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $notification->title }}</h6>
                                    <p class="mb-0 text-truncate" style="max-width: 500px;">{{ $notification->message }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="tanggal">{{ $notification->created_at->diffForHumans() }}</td>
                        <td class="white-space-nowrap">
                            <button class="btn-input-icon view-notification" 
                                    data-id="{{ $notification->id }}"
                                    data-title="{{ $notification->title }}"
                                    data-message="{{ $notification->message }}"
                                    data-time="{{ $notification->created_at->diffForHumans() }}"
                                    data-read="{{ !is_null($notification->read_at) }}"
                                    data-link="{{ $notification->link ?? '#' }}">
                                <span class="bx bx-show" data-bs-toggle="tooltip" title="Lihat"></span>
                            </button>
                            
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
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <div class="d-flex flex-column align-items-center">
                                <span class="bx bx-bell-off fs-3 mb-2"></span>
                                <h6>Tidak ada notifikasi</h6>
                                <p class="mb-0 text-500">Anda belum memiliki notifikasi saat ini</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
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
            <div id="table-notifikasi-replace-element">{{ $notifications->links() }}</div>
        </div>
    </div>
</div>

<!-- Modal Detail Notifikasi -->
<div class="modal fade" id="notificationDetailModal" tabindex="-1" role="dialog" aria-labelledby="notificationDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notificationDetailModalLabel">Detail Notifikasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="notification-detail">
                    <h6 id="notification-title"></h6>
                    <p id="notification-message"></p>
                    <div class="notification-meta">
                        <small id="notification-time"></small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" id="toggleReadStatus" class="btn btn-outline-primary">Tandai Belum Dibaca</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#table-notifikasi').DataTable({
            dom: "<'row mx-1'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'table-responsive'tr>" +
                 "<'row mx-1 align-items-center justify-content-center'<'col-sm-12 col-md-6'i><'col-sm-12 col-md-6'p>>",
            language: {
                paginate: {
                    previous: '<span class="fas fa-chevron-left"></span>',
                    next: '<span class="fas fa-chevron-right"></span>'
                },
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ entri",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
                infoFiltered: "(disaring dari _MAX_ total entri)",
                zeroRecords: "Tidak ada data yang cocok",
                emptyTable: "Tidak ada data di tabel"
            },
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            order: [[3, 'desc']]
        });
        
        // Tampilkan modal detail notifikasi
        $('.view-notification').on('click', function() {
            const id = $(this).data('id');
            const title = $(this).data('title');
            const message = $(this).data('message');
            const time = $(this).data('time');
            const read = $(this).data('read');
            const link = $(this).data('link');
            
            $('#notification-title').text(title);
            $('#notification-message').text(message);
            $('#notification-time').text(time);
            $('#notification-link').attr('href', link);
            
            // Sesuaikan tombol toggle read status
            if (read) {
                $('#toggleReadStatus').text('Tandai Belum Dibaca');
                $('#toggleReadStatus').data('action', 'unread');
            } else {
                $('#toggleReadStatus').text('Tandai Sudah Dibaca');
                $('#toggleReadStatus').data('action', 'read');
            }
            
            $('#toggleReadStatus').data('id', id);
            // Gunakan Bootstrap 5 API dengan backdrop non-locking agar tidak mengunci layar
            const modalElement = document.getElementById('notificationDetailModal');
            const modalOptions = { backdrop: false, keyboard: true };
            const detailModal = new bootstrap.Modal(modalElement, modalOptions);
            detailModal.show();
            
            // Jika notifikasi belum dibaca, tandai sebagai dibaca
            if (!read) {
                markAsRead(id);
            }
        });
        
        // Toggle status baca notifikasi
        $('#toggleReadStatus').on('click', function() {
            const id = $(this).data('id');
            const action = $(this).data('action');
            
            if (action === 'read') {
                markAsRead(id);
                $(this).text('Tandai Belum Dibaca');
                $(this).data('action', 'unread');
            } else {
                markAsUnread(id);
                $(this).text('Tandai Sudah Dibaca');
                $(this).data('action', 'read');
            }
        });
        
        // Tandai notifikasi sebagai dibaca
        $('.mark-as-read').on('click', function() {
            const id = $(this).data('id');
            markAsRead(id);
        });
        
        // Tandai notifikasi sebagai belum dibaca
        $('.mark-as-unread').on('click', function() {
            const id = $(this).data('id');
            markAsUnread(id);
        });
        
        // Tandai semua notifikasi sebagai dibaca
        $('#markAllAsRead').on('click', function(e) {
            e.preventDefault();
            markAllAsRead();
        });
        
        // Fungsi untuk menandai notifikasi sebagai dibaca
        function markAsRead(id) {
            $.ajax({
                url: "{{ route('notifications.read', ':id') }}".replace(':id', id),
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        const row = $('tr[data-id="' + id + '"]');
                        row.removeClass('fw-bold');
                        row.find('td.status .badge').removeClass('bg-primary').addClass('bg-secondary').text('Dibaca');
                        
                        // Ubah tombol mark-as-read menjadi mark-as-unread
                        const markButton = row.find('.mark-as-read');
                        markButton.removeClass('mark-as-read').addClass('mark-as-unread');
                        markButton.html('<span class="bx bx-undo text-warning" data-bs-toggle="tooltip" title="Tandai Belum Dibaca"></span>');
                        
                        // Update event handler
                        markButton.off('click').on('click', function() {
                            markAsUnread(id);
                        });
                        
                        // Update jumlah notifikasi di navbar
                        updateNotificationCount();
                        
                        // Reinitialize tooltips
                        $('[data-bs-toggle="tooltip"]').tooltip();
                    }
                }
            });
        }
        
        // Fungsi untuk menandai notifikasi sebagai belum dibaca
        function markAsUnread(id) {
            $.ajax({
                url: "{{ route('notifications.unread', ':id') }}".replace(':id', id),
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        const row = $('tr[data-id="' + id + '"]');
                        row.addClass('fw-bold');
                        row.find('td.status .badge').removeClass('bg-secondary').addClass('bg-primary').text('Baru');
                        
                        // Ubah tombol mark-as-unread menjadi mark-as-read
                        const markButton = row.find('.mark-as-unread');
                        markButton.removeClass('mark-as-unread').addClass('mark-as-read');
                        markButton.html('<span class="bx bx-check text-success" data-bs-toggle="tooltip" title="Tandai Dibaca"></span>');
                        
                        // Update event handler
                        markButton.off('click').on('click', function() {
                            markAsRead(id);
                        });
                        
                        // Update jumlah notifikasi di navbar
                        updateNotificationCount();
                        
                        // Reinitialize tooltips
                        $('[data-bs-toggle="tooltip"]').tooltip();
                    }
                }
            });
        }
        
        // Fungsi untuk menandai semua notifikasi sebagai dibaca
        function markAllAsRead() {
            $.ajax({
                url: "{{ route('notifications.readAll') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        // Refresh halaman untuk menampilkan perubahan
                        location.reload();
                    }
                }
            });
        }
        
        // Fungsi untuk memperbarui jumlah notifikasi di navbar
        function updateNotificationCount() {
            $.ajax({
                url: "{{ route('notifications.unread_count') }}",
                type: 'GET',
                success: function(response) {
                    const count = response.count;
                    const badge = $('.notification-indicator-number');
                    
                    if (count > 0) {
                        badge.text(count).show();
                    } else {
                        badge.text('0').hide();
                    }
                }
            });
        }
        
        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
        
        // Bulk select functionality
        $('[data-bulk-select-row]').click(function (e) {
            e.stopPropagation();
        });
        
        // Handle bulk actions
        $('#table-notifikasi-actions button').click(function() {
            var action = $('#table-notifikasi-actions select').val();
            var selectedIds = [];
            
            $('input[data-bulk-select-row]:checked').each(function() {
                var id = $(this).closest('tr').data('id');
                selectedIds.push(id);
            });
            
            if (selectedIds.length === 0) {
                alert('Silakan pilih notifikasi terlebih dahulu');
                return;
            }
            
            if (action === 'mark-as-read') {
                markMultipleAsRead(selectedIds);
            } else if (action === 'mark-as-unread') {
                markMultipleAsUnread(selectedIds);
            }
        });
        
        function markMultipleAsRead(ids) {
            $.ajax({
                url: "{{ route('notifications.markMultipleAsRead') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    ids: ids
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                }
            });
        }
        
        function markMultipleAsUnread(ids) {
            $.ajax({
                url: "{{ route('notifications.markMultipleAsUnread') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    ids: ids
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                }
            });
        }
    });
</script>
@endsection