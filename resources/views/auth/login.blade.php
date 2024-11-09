<!DOCTYPE html>
<html lang="en">
   <head>
      <!-- Meta Information -->
      <meta charset="UTF-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
      <title>NMU Housing - Login</title>
      <!-- Favicon -->
      <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">
      <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
      <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16x16.png') }}">
      <!-- CSS Files -->
      <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
      <link href="{{ asset('css/icons.css') }}" rel="stylesheet" type="text/css">
      <link rel="stylesheet" href="{{ asset('css/authenication.css') }}">
      <link href="{{ asset('css/style.css') }}" rel="stylesheet" type="text/css">
      <!-- Load SweetAlert2 from CDN -->
      <script src="{{ asset('plugins/sweet-alert2/sweetalert2.all.min.js') }}"></script>
   </head>
   <body class="vertical-layout">
      <div id="containerbar" class="containerbar authenticate-bg">
         <div class="container">
            <div class="auth-box login-box">
               <div class="row no-gutters align-items-center justify-content-center flex-column-reverse">
                  <div class="col-12 col-md-6">
                     <div class="auth-box-right">
                        <div class="card text-start">
                           <div class="card-body">
                              <div class="auth-logo text-center">
                                 <a href="index.html">
                                 <img src="{{ asset('images/logo.png') }}" class="img-fluid" alt="logo">
                                 </a>
                              </div>
                              <h4 class="text-primary mb-4">Welcome back 👋!</h4>
                              <form method="POST" action="{{ route('login') }}">
                                 @csrf
                                 @if (session('success'))
                                 <script>
                                    Swal.fire({
                                        toast: true,
                                        icon: 'success',
                                        title: 'Success!',
                                        text: '{{ session('success') }}',
                                        position: 'top-start',  // Position the toast at the top-right
                                        showConfirmButton: false,  // No confirmation button
                                        timer: 8000,  // Auto-close after 3 seconds
                                        timerProgressBar: true,  // Show progress bar
                                    });
                                 </script>
                                 @endif
                                 <!-- SweetAlert for Validation Errors -->
                                 @if ($errors->any())
                                 <script>
                                    Swal.fire({
                                        toast: true,
                                        icon: 'error',
                                        title: 'Error!',
                                        text: '{{ $errors->first() }}',
                                        position: 'top-start',  // Position the toast at the top-right
                                        showConfirmButton: false,  // No confirmation button
                                        timer: 3000,  // Auto-close after 3 seconds
                                        timerProgressBar: true,  // Show progress bar
                                    });
                                 </script>
                                 @endif
                                 <div class="form-floating mb-3">
                                    <input type="text" class="form-control text-secondary" id="floatingInput" name="identifier" placeholder="name@example.com" required>
                                    <label for="floatingInput">Email / National ID</label>
                                 </div>
                                 <div class="form-floating mb-3">
                                    <input type="password" class="form-control" id="floatingPassword" name="password" placeholder="Password" required>
                                    <label for="floatingPassword">Password</label>
                                 </div>
                                
                                 <div class="d-grid mb-4">
                                    <button class="btn btn-primary font-18" type="submit">Log in</button>
                                 </div>
                              </form>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="col-12 col-md-6 d-md-block d-none">
                     <div class="auth-box-right">
                        <img src="{{ asset('images/authentication/login-hero.svg') }}" alt="Login Image">
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!-- Footer Section -->
      <footer class="text-center mt-2">
         <div class="container">
            <p class="text-muted">&copy;2024 New Mansoura University. All Rights Reserved.</p>
         </div>
      </footer>
      <!-- JavaScript Files -->
      <script src="{{ asset('js/jquery.min.js') }}"></script>
      <script src="{{ asset('js/popper.min.js') }}"></script>
      <script src="{{ asset('js/bootstrap.bundle.js') }}"></script>
   </body>
</html>