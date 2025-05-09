@if ($message = Session::get('success'))
<div class="alert alert-success d-flex align-items-center mt-0 mb-xl-3" role="alert">
  <div class="svg-icon svg-icon-success">
    @include('partials.check-shield')
  </div>
  <p class="mb-0 flex-1">{{ $message }}</p>
  <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if ($message = Session::get('error'))
<div class="alert alert-danger d-flex align-items-center mt-0 mb-xl-3" role="alert">
  <div class="svg-icon svg-icon-danger">
    @include('partials.icon-alert')
  </div>
  <p class="mb-0 flex-1">{{ $message }}</p>
  <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif