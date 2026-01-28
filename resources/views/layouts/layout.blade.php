<!DOCTYPE html>
<html lang="en-US" dir="ltr" class="no-scroll-y">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- ===============================================-->
  <!--    Document Title-->
  <!-- ===============================================-->
  <title>WIKA Sistem MAnagement Risiko Terintegrasi (W-SMART)</title>

  <!-- ===============================================-->
  <!--    Favicons-->
  <!-- ===============================================-->
  <link rel="apple-touch-icon" sizes="180x180" href="/assets/img/favicons/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/assets/img/favicons/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/assets/img/favicons/favicon-16x16.png">
  <link rel="shortcut icon" type="image/x-icon" href="/assets/img/favicons/favicon.ico">
  <link rel="manifest" href="/assets/img/favicons/manifest.json">
  <meta name="msapplication-TileImage" content="/assets/img/favicons/mstile-150x150.png">
  <meta name="theme-color" content="#ffffff">
  <script src="/assets/js/config.js"></script>
  <script src="/vendors/overlayscrollbars/OverlayScrollbars.min.js"></script>
  <script src="/vendors/simplebar/simplebar.min.js"></script>

  <!-- ===============================================-->
  <!--    Stylesheets-->
  <!-- ===============================================-->
  <link rel="stylesheet" href="/vendors/overlayscrollbars/OverlayScrollbars.min.css">
  <link rel="stylesheet" href="/assets/css/user-rtl.css" id="user-style-rtl">
  <link rel="stylesheet" href="/assets/css/user.css" id="user-style-default">
  <link rel="stylesheet" href="/vendors/select2/select2.min.css" />
  <link rel="stylesheet" href="/vendors/flatpickr/flatpickr.min.css">
  <link rel="stylesheet" href="/vendors/datatables/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="/vendors/datatables/searchPanes.bootstrap5.min.css" />
  <link rel="stylesheet" href="/vendors/datatables/select.bootstrap5.min.css" />
  <link rel="stylesheet" href="/vendors/boxicons-2.1.4/css/boxicons.min.css">
  <link rel="stylesheet" href="/assets/css/theme-rtl.css" id="style-rtl">
  <link rel="stylesheet" href="/vendors/sweetalert2/sweetalert2.min.css">
  <link rel="stylesheet" href="/assets/css/theme.css" id="style-default">
  <link rel="stylesheet" href="/assets/css/theme-custom.css">
  <script>
  var isRTL = JSON.parse(localStorage.getItem('isRTL'));
  if (isRTL) {
    var linkDefault = document.getElementById('style-default');
    var userLinkDefault = document.getElementById('user-style-default');
    linkDefault.setAttribute('disabled', true);
    userLinkDefault.setAttribute('disabled', true);
    document.querySelector('html').setAttribute('dir', 'rtl');
  } else {
    var linkRTL = document.getElementById('style-rtl');
    var userLinkRTL = document.getElementById('user-style-rtl');
    linkRTL.setAttribute('disabled', true);
    userLinkRTL.setAttribute('disabled', true);
  }
  </script>
  @stack('styles')
  <!-- Preloader -->
  <section>
    <div id="preloader">
      <div id="preloader" class="preloader">
        <div class="animation-preloader">
          <div class="spinner"></div>
          <div class="txt-loading">
            <span data-text-preloader="L" class="letters-loading">L</span>
            <span data-text-preloader="O" class="letters-loading">O</span>
            <span data-text-preloader="A" class="letters-loading">A</span>
            <span data-text-preloader="D" class="letters-loading">D</span>
            <span data-text-preloader="I" class="letters-loading">I</span>
            <span data-text-preloader="N" class="letters-loading">N</span>
            <span data-text-preloader="G" class="letters-loading">G</span>
          </div>
        </div>
        <div class="loader-section section-left"></div>
        <div class="loader-section section-right"></div>
      </div>
    </div>
  </section>
</head>

<body>
  <!-- ====================== Main Content ========================-->
  @yield('main-content')
  <!-- ====================== End of Main Content ========================-->

  <script src="/vendors/popper/popper.min.js"></script>
  <script src="/vendors/bootstrap/bootstrap.min.js"></script>
  <script src="/vendors/anchorjs/anchor.min.js"></script>
  <script src="/vendors/is/is.min.js"></script>
  <script src="/vendors/echarts/echarts.min.js"></script>
  <script src="/vendors/lodash/lodash.min.js"></script>
  <script src="/assets/js/polyfill.min.js"></script>
  <script src="/vendors/flatpickr/flatpickr.min.js"></script>
  <script src="/assets/js/jquery.3.7.1.min.js"></script>
  <script src="/vendors/datatables/dataTables.min.js"></script>
  <script src="/vendors/datatables/dataTables.bootstrap5.js"></script>
  <script src="/vendors/datatables/dataTables.searchPanes.min.js"></script>
  <script src="/vendors/datatables/searchPanes.bootstrap5.min.js"></script>
  <script src="/vendors/datatables/dataTables.select.min.js"></script>
  <script src="/vendors/datatables/select.bootstrap5.min.js"></script>
  <script src="/vendors/list.js/list.min.js"></script>
  <script src="/vendors/select2/select2.js"></script>
  <script src="/vendors/sweetalert2/sweetalert2.min.js"></script>
  <script src="/assets/js/theme.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/dayjs@1/plugin/customParseFormat.js"></script>
  <script src="/js/notification-handler.js"></script>
  @yield('scripts')
  @stack('scripts')
</body>

</html>
