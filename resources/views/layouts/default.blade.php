@extends('layouts.layout')

@section('main-content')
@include('navbars.navbar-vertical')
<div class="content">
  @include('navbars.navber-top-default')
  @include('partials.ghost-login-banner')
  <main id="top" class="main" style="position: relative;">
    <div class="container-fluid" data-layout="container-fluid" style="position: static;">
      <div style="position: absolute; top: 0;">
        {{ Breadcrumbs::render() }}
      </div>
      @yield('dashboard')
    </div>
  </main>
  @include('partials.footer')
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $.ajax({
        url: "{{ route('tasks.count') }}",
        type: 'GET',
        success: function(response) {
            if(response.count > 0) {
                const badge = $('#navbar-task-count');
                badge.text(response.count);
                badge.show();

                badge.addClass('animate-pulse');
            }
        },
        error: function(err) {
            console.log('Gagal memuat task count');
        }
    });
});
</script>
@endpush
