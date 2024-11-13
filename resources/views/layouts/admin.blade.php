<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="UTF-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
     
      <meta name="author" content="Themesbox">
      <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
      <meta name="csrf-token" content="{{ csrf_token() }}">
      
      <!-- Dynamic Page Title -->
      <title>NMU Exam Lab Sheduler - @yield('title', 'Default Title')</title>

      <!-- Icons -->
      <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">
      <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
      <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16x16.png') }}">

      <!-- Global CSS -->
      <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
      <link href="{{ asset('css/icons.css') }}" rel="stylesheet" type="text/css">
      <link href="{{ asset('css/flag-icon.min.css') }}" rel="stylesheet" type="text/css">
      <link href="{{ asset('css/style.css') }}" rel="stylesheet" type="text/css">

      <!-- Page-Specific CSS -->
      @yield('links')

   </head>
   <body class="vertical-layout">
      <!-- Start Containerbar -->
      <div id="containerbar">
         <!-- Start Leftbar -->
         <div class="leftbar">
            <!-- Start Sidebar -->
            <x-admin-sidebar />
            <!-- End Sidebar -->
         </div>
         <!-- End Leftbar -->

         <!-- Start Rightbar -->
         <div class="rightbar">
            <!-- Start Topbar Mobile -->
            <x-mobile-topbar />
            <!-- Start Topbar -->
            <x-topbar />
            <!-- End Topbar -->

            <!-- Start Contentbar -->    
            <div class="contentbar">
               @yield('content') <!-- Page content will be injected here -->
            </div>
            <!-- End Contentbar -->

            <!-- Start Footerbar -->
            <x-footbar />
            <!-- End Footerbar -->
         </div>
         <!-- End Rightbar -->
      </div>
      <!-- End Containerbar -->

      <!-- Global JS -->
      <script src="{{ asset('js/jquery.min.js') }}"></script>
      <script src="{{ asset('js/popper.min.js') }}"></script>
      <script src="{{ asset('js/bootstrap.bundle.js') }}"></script>
      <script src="{{ asset('js/modernizr.min.js') }}"></script>
      <script src="{{ asset('js/detect.js') }}"></script>
      <script src="{{ asset('js/jquery.slimscroll.js') }}"></script>
      <script src="{{ asset('js/vertical-menu.js') }}"></script>
    <!-- Page-Specific JS -->
    @yield('scripts')
      <!-- Core JS -->
      <script src="{{ asset('js/core.js') }}"></script>

      

   </body>
</html>
