@extends('layouts.default')
@section('title') Backup & Restore @endsection
@push('styles')
    <style type="text/css">
        .datepicker.datepicker-dropdown {
            z-index: 1151 !important; /* has to be larger than 1050 */
        }
    </style>
@endpush
@section('dashboard')
@include('partials.success-message')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body"  style="padding-bottom:60px;">
                <div class="d-flex my-2">
                    <div class="flex-grow-1">
                        <h4 class="unit-title">Backup & Restore</h4>
                    </div>
                </div>
                <p class="card-title-desc">
                </p>
                <ul class="nav nav-tabs" role="tablist">
                     <li class="nav-item">
                        <a class="nav-link active px-3" data-bs-toggle="tab" href="#backup" role="tab">
                            <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                            <span class="d-none d-sm-block">Backup</span>
                        </a>
                    </li> 
                    <li class="nav-item">
                        <a class="nav-link px-3" data-bs-toggle="tab" href="#restore-from-file" role="tab">
                            <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                            <span class="d-none d-sm-block">Restore from file</span>
                        </a>
                    </li>
                </ul>
                <div class="tab-content p-3 text-muted">
                     <div class="tab-pane active" id="backup" role="tabpanel">
                        <form action="{{route('backups.store')}}" method="POST">
                            @csrf
                            <div class="row mb-2">
                                <div class="col-md-2">
                                    <label class="form-label">Backup Name</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="backup_name" class="form-control" required value="{{date('YmdHis').rand(1000,9999)}}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2">
                                    <label class="form-label">Backup Password</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="zip_password" class="form-control" required>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="offset-md-2 col">
                                    <button type="submit" class="btn btn-sm btn-primary">Mulai Backup</button>
                                </div>
                            </div>
                        </form>
                    </div>
                     <div class="tab-pane" id="restore-from-file" role="tabpanel">
                        <div class="alert alert-warning">Mohon diperhatikan bahwa:
                            <ul>
                                <li>Aplikasi tidak akan dapat digunakan selama proses restore data</li>
                                <li>Pastikan upload file hasil backup dari halaman ini</li>
                                <li>Mengupload hasil backup database lain dapat merusak sistem</li>
                            </ul> 
                        </div>
                        <form action="{{route('backups.restore')}}" method="POST" enctype="multipart/form-data" required>
                            @csrf
                            <div class="row mb-3">
                                <div class="col-md-2">
                                    <label class="form-label">Backup File</label>
                                </div>
                                <div class="col-4">
                                    <input type="file" name="file" class="form-control" accept=".zip">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2">
                                    <label class="form-label">Backup Password</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="password" name="zip_password" class="form-control" required>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="offset-md-2 col">
                                    <button type="submit" class="btn btn-sm btn-primary">Upload & Restore</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-body"  style="padding-bottom:60px;">
                <div class="d-flex my-2">
                    <div class="flex-grow-1">
                        <h4 class="unit-title">Backup History</h4>
                    </div>
                </div>
                <p class="card-title-desc">
                </p>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Backup File</th>
                            <th>Status</th>
                            <th>Log</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!$backups) 
                        <tr>
                            <td colspan="4" class="text-center"><em class="text-muted">Belum ada backup</em></td>
                        </tr>
                        @endif
                        @foreach($backups as $backup)
                        <tr>
                            <td>{{$backup['name']}}</td>
                            <td>{{['success' => 'Tersedia', 'in_progress' => 'Berproses', 'failed' => 'Gagal'][$backup['status']] ?? '-'}}</td>
                            <td>
                                <button type="button" onclick="viewBackupLog('{{$backup['filename']}}')" class="btn btn-sm btn-info">View Backup Log</button>
                                @if($backup['restore_log'])
                                <button type="button" onclick="viewRestoreLog('{{$backup['filename']}}')" class="btn btn-sm btn-info">View Restore Log</button>
                                @endif
                            </td>
                            <td>
                                @if($backup['status'] == 'success')
                                <button type="button" onclick="restoreBackup('{{$backup['filename']}}')" class="btn btn-sm btn-warning">Restore</button>
                                <a href="{{route('backups.show', $backup['filename'])}}" class="btn btn-sm btn-primary">Unduh</a>
                                @endif
                                <button type="button" onclick="deleteBackup('{{$backup['filename']}}')" class="btn btn-sm btn-danger">Hapus</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div> <!-- end col -->
</div> <!-- end row -->
@endsection

@push('scripts')

    <script type="text/javascript">
        // Initialize Bootstrap 5 tabs
        document.addEventListener('DOMContentLoaded', function() {
            // Get all tab triggers
            const tabTriggers = document.querySelectorAll('[data-bs-toggle="tab"]');
            
            // Add click event listeners to each tab trigger
            tabTriggers.forEach(function(tabTrigger) {
                tabTrigger.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remove active class from all tabs and content
                    document.querySelectorAll('.nav-link').forEach(function(navLink) {
                        navLink.classList.remove('active');
                    });
                    document.querySelectorAll('.tab-pane').forEach(function(tabPane) {
                        tabPane.classList.remove('active');
                    });
                    
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Show corresponding content
                    const targetId = this.getAttribute('href');
                    const targetContent = document.querySelector(targetId);
                    if (targetContent) {
                        targetContent.classList.add('active');
                    }
                });
            });
        });

        function restoreBackup(id) {
            Swal.fire({
                title: 'Restore backup?',
                text: "Aplikasi tidak akan dapat digunakan selama proses restore data. Masukan password backup untuk melanjutkan.",
                icon: 'warning',
                input: 'text',
                showCancelButton: true,
                confirmButtonColor: '#fcb92c',
                cancelButtonColor: '#74788d',
                confirmButtonText: 'Lanjutkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('backups.restore') }}",
                        type: "POST",
                        data: {
                            _token: '{{csrf_token()}}',
                            filename: id,
                            zip_password: result.value,
                        },
                        success: function (response) {
                            if(response.status == "success"){
                                Swal.fire('Success!', response.message, "success");
                                $('#form-detail :input').val('');
                                // Bootstrap 5 modal hide method
                                const modal = bootstrap.Modal.getInstance(document.getElementById('modal-detail'));
                                if (modal) {
                                    modal.hide();
                                }
                                $("#backup-table").DataTable().ajax.reload();
                            }
                            else{
                                Swal.fire('Error!', response.message ? response.message : 'Gagal menghapus backup', "error");
                            }
                            
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            Swal.fire('Error!', jqXHR.responseJSON?.message ? jqXHR.responseJSON.message : "Gagal menghapus backup", "error");
                        }
                    });
                }
            });
        }

        function deleteBackup(id) {
            Swal.fire({
                title: 'Hapus backup?',
                text: "Tindakan ini tidak dapat dikembalikan!",
                icon: 'danger',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('backups.destroy', '::id::') }}".replace('::id::', id),
                        type: "DELETE",
                        data: {
                            _token: '{{csrf_token()}}',
                        },
                        success: function (response) {
                            if(response.status == "success"){
                                Swal.fire('Success!', response.message, "success").then(() => location.reload());
                            }
                            else{
                                Swal.fire('Error!', response.message ? response.message : 'Gagal menghapus backup', "error");
                            }
                            
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            Swal.fire('Error!', jqXHR.responseJSON?.message ? jqXHR.responseJSON.message : "Gagal menghapus backup", "error");
                        }
                    });
                }
            });
        }

        backups = @json($backups);
        function viewBackupLog(id) {
            console.log(backups[id]);
            Swal.fire({
                title: 'View log',
                html: backups[id].backup_log.replace(/\n/g, '<br/>'),
                icon: 'info',
                confirmButtonText: 'Tutup'
            });
        }

        function viewRestoreLog(id) {
            console.log(backups[id]);
            Swal.fire({
                title: 'View log',
                html: backups[id].restore_log.replace(/\n/g, '<br/>'),
                icon: 'info',
                confirmButtonText: 'Tutup'
            });
        }
    </script>
@endpush