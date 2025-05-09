@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row gy-5">
  <div class="col-12">
    <div class="card border-0 shadow">
      <div class="card-header position-relative min-vh-25 py-0 mb-10 border-0">
        <div class="cover-image">
          <div class="bg-holder" style="background-image:url(../../../assets/img/user-profile-bg.webp);">
          </div>
          <div class="cover-image-file-input position-absolute right-0 top-0">
            <input class="upload-form" id="upload-cover-image" type="file" hidden="hidden" />
            {{-- <button class="btn btn-muted btn-sm d-flex align-items-center gap-2" type="button" id="upload-button">
              <span class="bx bx-camera fs-3"></span>
              <span>Change cover photo</span>--}}
            </button>
          </div>
        </div>
        <div class="row gx-5 avatar-profile mt-10">
          <div class="col-auto">
            <div class="avatar avatar-5xl shadow img-thumbnail rounded-circle">
              <div class="h-100 w-100 rounded-circle overflow-hidden position-relative">
                <img src="{{ asset('assets/img/avatar.webp') }}" alt="" data-dz-thumbnail="data-dz-thumbnail" />
                <input class="d-none" id="profile-image" type="file" />
                {{--
                <div id="uploadProfileButton" class="overlay-icon d-flex flex-center h-100">
                  <span class="bg-holder overlay overlay-0"></span>
                  <span class="z-index-1 text-white dark__text-white text-center">
                    <span class="bx bx-camera fs-2"></span>
                    <div class="d-block">
                      <input class="uploadProfileForm" type="file" id="profile-image" name="" hidden="hidden" />
                      <span class="py-2 text-white">Upload</span>
                    </div>
                  </span>
                </div>
                --}}
              </div>
            </div>
          </div>
          <div class="col d-flex">
            <h4 class="mt-auto mb-5">{{ $data->name }}</h4>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="row g-3 gx-md-7">
          <div class="col-md-auto d-flex align-items-center gap-2">
            <h6 class="mb-0">Email:</h6>
            <h6 class="mb-0 fw-normal">{{ $data->email }}</h6>
          </div>
          <div class="col-md-auto d-flex align-items-center gap-2">
            <h6 class="mb-0">Phone:</h6>
            <h6 class="mb-0 fw-normal">{{ !empty($data->phone) ? $data->phone : '-' }}</h6>
          </div>
          <div class="col-md-auto d-flex align-items-center gap-2">
            <h6 class="mb-0">NIK:</h6>
            <h6 class="mb-0 fw-normal">{{ !empty($data->nik) ? $data->nik : '-' }}</h6>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12">
    {!! Form::model($data, ['method' => 'PATCH', 'route' => ['profile'], 'id' => 'profileForm']) !!}
    <div class="card">
      <div class="card-header d-flex flex-between-center">
        <h2 class="h3">Update Data</h2>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="alert alert-warning mb-2">
          Kosongkan field Password bila tidak ingin mengubah password anda
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <div class="form-floating mb-3">
              {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
              <label>Name</label>
            </div>
            <div class="form-floating mb-3">
              {!! Form::text('phone', null, array('placeholder' => 'Phone','class' => 'form-control'))
              !!}
              <label>Phone</label>
            </div>
            <div class="form-floating">
              {!! Form::text('nik', null, array('placeholder' => 'NIK','class' => 'form-control')) !!}
              <label>NIK</label>
            </div>
          </div>
          {{--
            <div class="col-md-6">
              <div class="form-floating mb-3">
                {!! Form::text('password', null, array('placeholder' => 'Password','class' => 'form-control')) !!}
                <label>Password</label>
              </div>
              <div class="form-floating">
                {!! Form::text('password_confirmation', null, array('placeholder' => 'Confirm Password','class' =>
                'form-control')) !!}
                <label>Confirm Password</label>
              </div>
            </div>
            --}}
          <div class="col-md-6">
            <div class="form-floating password-input-col mb-3">
              {!! Form::password('password', array('placeholder' => 'Password', 'class' => 'form-control
              form_password')) !!}
              <i id="pass" class='bx bx-show-alt'></i>
              <label>Password</label>
            </div>
            <div class="form-floating password-input-col">
              {!! Form::password('password_confirmation', array('placeholder' => 'Confirm Password', 'class' =>
              'form-control form_confirmation')) !!}
              <i id="conf_pass" class='bx bx-show-alt'></i>
              <label>Confirm Password</label>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer pt-0 border-0">
        <button class="btn btn-submit" type="submit">Update</button>
      </div>
    </div>
    {!! Form::close() !!}
  </div>
</div>
@endsection
@section('scripts')
<script>
$(document).ready(function() {
  document.querySelector('#profileForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Mencegah form submission otomatis

    Swal.fire({
      title: "Apakah Anda yakin?",
      text: "Profile anda akan diupdate",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Ya, Update Profile!",
      cancelButtonText: "Tidak, batal",
    }).then((result) => {
      if (result.isConfirmed) {
        this.submit(); // Kirim form jika dikonfirmasi
      }
    });
  });
});
</script>
<script>
const photoFileBtn = document.querySelector(".uploadProfileForm");
const profileBtn = document.getElementById("uploadProfileButton");
const cstTxt = document.getElementById("file-title");

profileBtn &&
  profileBtn.addEventListener("click", function() {
    photoFileBtn.click();
  });

photoFileBtn &&
  photoFileBtn.addEventListener("change", function() {
    if (photoFileBtn.value) {
      cstTxt.innerHTML = photoFileBtn.value.match(
        /[\/\\]([\w\d\s\.\-\(\)]+)$/
      )[1];
    } else {
      cstTxt.innerHTML = "No file chosen, yet.";
    }
  });
</script>


@endsection
