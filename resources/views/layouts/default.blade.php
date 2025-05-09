@extends('layouts.layout')

@section('main-content')
@include('navbars.navbar-vertical')
<div class="content">
  @include('navbars.navber-top-default')
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