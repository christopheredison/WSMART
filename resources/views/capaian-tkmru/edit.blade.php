@extends('layouts.default')

@section('dashboard')
<main class="main" id="top">
    <div class="container-fluid" data-layout="container-fluid">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-3">
                        <li class="breadcrumb-item"><a href="{{ route('capaian-tkmru.index') }}" class="fs-2">Capaian TKMRU</a></li>
                        <li class="breadcrumb-item active fs-2" aria-current="page">Edit</li>
                    </ol>
                </nav>
              <div class="card mb-3 btn-reveal-trigger">
                <div class="card-header position-relative min-vh-25">
                    <form method="POST" action="{{ route('capaian-tkmru.update', $capaianTkmru) }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group mb-3">
                            <label class="form-label">Periode</label>
                            <select class="form-select js-example-basic-single" name="periode_id">
                                <option selected disabled>Select Periode</option>
                                @foreach ($periodes as $id => $name)
                                    <option value="{{ $id }}" {{ $id == $capaianTkmru->periode_id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Unit</label>
                            <select class="form-select js-example-basic-single" name="unit_id">
                                <option selected disabled>Select Unit</option>
                                @foreach ($units as $unitType)
                                    <optgroup label="{{ $unitType->first()->unitType->name }}">
                                        @foreach ($unitType as $unit)
                                            <option value="{{ $unit->id }}" {{ $unit->id == $capaianTkmru->unit_id ? 'selected' : '' }}>{{ $unit->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label" for="tanggal_data">Tanggal Data</label>
                            <input class="form-control" id="tanggal_data" name="tanggal_data" type="date"
                                value="{{ old('tanggal_data', $capaianTkmru->tanggal_data->format('Y-m-d')) }}" />
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label" for="capaian">Capaian TKMRU</label>
                            <input class="form-control" id="capaian" name="capaian" type="number" step="0.01"
                                value="{{ old('capaian', $capaianTkmru->capaian) }}" />
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label" for="description">Deskripsi</label>
                            <input class="form-control" id="description" name="description" type="text"
                                value="{{ old('description', $capaianTkmru->description) }}" />
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('capaian-tkmru.index') }}" class="btn btn-secondary">Kembali</a>
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
