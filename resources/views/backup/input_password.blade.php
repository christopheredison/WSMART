@extends('layouts.master')
@section('title') Backup & Restore @endsection
@section('css')
    <!-- DataTables -->
    <link href="{{ URL::asset('/assets/libs/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('/assets/libs/bootstrap-datepicker/bootstrap-datepicker.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('/assets/libs/sweetalert2/sweetalert2.min.css')}}" rel="stylesheet" type="text/css" />
    <style type="text/css">
        .datepicker.datepicker-dropdown {
            z-index: 1151 !important; /* has to be larger than 1050 */
        }
    </style>
@endsection
@section('content')
@component('components.breadcrumb')
    @slot('title') Backup & Restore @endslot
    @slot('li_1') Setelan @endslot
    @slot('li_2') Backup & Restore @endslot
@endcomponent

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body"  style="padding-bottom:60px;">
                <div class="media my-2">
                    <div class="media-body">
                        <h4 class="unit-title">Backup & Restore</h4>
                    </div>
                </div>
                <p class="card-title-desc">
                </p>
                <form action="{{route('backups.store')}}" method="POST">
                    <div>Masukan password untuk melanjutkan</div>
                    <div class="form-group">
                        <input style="max-width: 300px;" class="form-control" type="password" name="password" required>
                    </div>
                    @csrf
                    <button type="submit" class="btn btn-primary">Lanjut</button>
                </form>
                </div>
            </div>
        </div>
    </div> <!-- end col -->
</div> <!-- end row -->
@endsection

@section('script')
    <!-- Required datatable js -->
    <script src="{{ URL::asset('/assets/libs/datatables/datatables.min.js')}}"></script>
    <script src="{{ URL::asset('/assets/libs/jszip/jszip.min.js')}}"></script>
    <script src="{{ URL::asset('/assets/libs/pdfmake/pdfmake.min.js')}}"></script>
    <script src="{{ URL::asset('/assets/libs/bootstrap-datepicker/bootstrap-datepicker.min.js')}}"></script>
    <script src="{{ URL::asset('/assets/libs/sweetalert2/sweetalert2.min.js')}}"></script>

    <script type="text/javascript">
        function restoreBackup(id) {
            Swal.fire({
                title: 'Restore backup?',
                text: "Aplikasi tidak akan dapat digunakan selama proses restore data. Tetap lanjutkan?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#fcb92c',
                cancelButtonColor: '#74788d',
                confirmButtonText: 'Ya, lanjutkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('backups.restore') }}",
                        type: "POST",
                        data: {
                            _token: '{{csrf_token()}}',
                            filename: id,
                        },
                        success: function (response) {
                            if(response.status == "success"){
                                Swal.fire('Success!', response.message, "success");
                                $('#form-detail :input').val('');
                                $('#modal-detail').modal('hide');
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
    </script>
@endsection