@extends('layouts.default')

@section('dashboard')
<main class="main" id="top">
    <div class="container-fluid" data-layout="container-fluid">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-3">
                        <li class="breadcrumb-item"><a href="{{ route('rencana-kegiatan.index') }}" class="fs-2">Rencana Kegiatan</a></li>
                        <li class="breadcrumb-item active fs-2" aria-current="page">Create</li>
                    </ol>
                </nav>
              <div class="card mb-3 btn-reveal-trigger">
                <div class="card-header position-relative min-vh-25">
                    <form method="POST" action="{{ route('rencana-kegiatan.store') }}">
                        @csrf
                        <div class="form-group mb-3">
                            <label class="form-label" for="title">Title</label>
                            <input class="form-control" id="title" name="title" type="text" placeholder="Masukkan Title" value="{{ old('title') }}"/>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('rencana-kegiatan.index') }}" class="btn btn-secondary">Kembali</a>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
              </div>
            </div>
        </div>
    </div>
</main>
@endsection
