<!-- Start Sidebar -->
<div class="sidebar">
    <!-- Start Logobar -->
    <div class="logobar">
        <a href="{{ route('admin.home') }}" class="logo logo-large">
            <img src="{{ asset('images/logo-wide.png') }}" class="img-fluid" alt="NMU Logo">
        </a>
        <a href="{{ route('admin.home') }}" class="logo logo-small">
            <img src="{{ asset('images/logo.png') }}" class="img-fluid" alt="NMU Logo">
        </a>
    </div>
    <!-- End Logobar -->

    <!-- Start Navigationbar -->
    <div class="navigationbar">
        <ul class="vertical-menu">

            <!-- Exam Settings and Labs (Visible only for 'admin' role) -->
            @if(auth()->user()->hasRole('admin'))
                <li>
                    <a href="{{ route('exam-settings.show') }}">
                        <img src="{{ asset('images/svg-icon/settings.svg') }}" class="img-fluid" alt="exam settings">
                        <span>Exam Settings</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('labs.index') }}">
                        <img src="{{ asset('images/svg-icon/basic.svg') }}" class="img-fluid" alt="Labs">
                        <span>Labs</span>
                    </a>
                </li>
                <!-- Sessions Section for Admin -->

                <li>
                    <a href="{{ route('admin.quizzes.index') }}">
                        <img src="{{ asset('images/svg-icon/tables.svg') }}" class="img-fluid" alt="quizzes">
                        <span>Quizzes</span>
                    </a>
                </li>

                <li>
                    <a href="#">
                        <img src="{{ asset('images/svg-icon/layouts.svg') }}" class="img-fluid" alt="sessions">
                        <span>Sessions</span>
                        <i class="feather icon-chevron-right pull-right"></i>
                    </a>
                    <ul class="vertical-submenu">
                        <li><a href="{{ route('sessions.index') }}">View sessions</a></li>
                        <li><a href="{{ route('sessions.reserve') }}">Reserve</a></li>

                        <li><a href="{{ route('sessions.withQuizzes') }}">Reservations</a></li>
                    </ul>
                </li>
            @endif


            @if(auth()->user() && auth()->user()->hasRole('faculty'))
    <li>
        <a href="{{ route('admin.quizzes.index') }}">
            <img src="{{ asset('images/svg-icon/tables.svg') }}" class="img-fluid" alt="quizzes">
            <span>Quizzes</span>
        </a>
    </li>
    <li>
        <a href="{{ route('sessions.reserve') }}">
            <img src="{{ asset('images/svg-icon/layouts.svg') }}" class="img-fluid" alt="sessions">
            <span>Sessions</span>
        </a>
    </li>
@endif



               

        
            <!-- Logout Link -->
            <li>
                <a href="#" onclick="logout()">
                    <img src="{{ asset('images/svg-icon/logout.svg') }}" class="img-fluid" alt="logout">
                    <span>Logout</span>
                </a>
            </li>

        </ul>
    </div>
    <!-- End Navigationbar -->
</div>
<!-- End Sidebar -->

<!-- Logout Script -->
<script>
    function logout() {
        if (confirm("Are you sure you want to logout?")) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('logout') }}"; 
            form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}">`;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
