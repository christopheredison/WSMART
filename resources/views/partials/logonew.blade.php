@php
  // default width 160 jika tidak dikirim dari include
  $width = $width ?? 160;
  // Anda bisa tambahkan $bg jika butuh background, dsb
@endphp

<img
  src="{{ asset('assets/img/logo.png') }}"
  width="{{ $width }}"
  alt="Logo"
  style="display: inline-block;"
/>
