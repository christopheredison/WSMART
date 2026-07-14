@php
    $ghostSession = session('ghost_login');
@endphp

@if($ghostSession)
  <div class="container-fluid pt-3">
    <div class="alert alert-warning border border-warning-subtle d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-0">
      <div>
        <div class="fw-bold">Mode Ghost Login aktif</div>
        <div class="small">
          Anda sedang login sebagai <strong>{{ $ghostSession['impersonated_user_name'] ?? auth()->user()?->name }}</strong>
          @if(!empty($ghostSession['impersonated_user_email']))
            ({{ $ghostSession['impersonated_user_email'] }})
          @endif
          dan akun asli Anda adalah <strong>{{ $ghostSession['original_user_name'] ?? '-' }}</strong>.
        </div>
      </div>
      <form method="POST" action="{{ route('ghost-login.destroy') }}" class="mb-0">
        @csrf
        <button type="submit" class="btn btn-dark btn-sm">
          Keluar Ghost Login
        </button>
      </form>
    </div>
  </div>
@endif
