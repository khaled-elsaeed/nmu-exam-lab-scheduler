<!-- Start Sidebar -->
<div class="sidebar">
    <!-- Start Logobar -->
    <div class="logobar">
        <a href="{{ route('student.home') }}" class="logo logo-large">
            <img src="{{ asset('images/logo-wide.png') }}" class="img-fluid" alt="logo">
        </a>
        <a href="{{ route('student.home') }}" class="logo logo-small">
            <img src="{{ asset('images/logo.png') }}" class="img-fluid" alt="logo">
        </a>
    </div>

    <!-- Start Navigationbar -->
    <div class="navigationbar">
        <ul class="vertical-menu">

            <li>
                <a href="#">
                    <img src="{{ asset('images/svg-icon/form_elements.svg') }}" class="img-fluid" alt="questionnaires">
                    <span>Questionnaires</span>
                </a>
            </li>

            <li>
                <a href="#">
                    <img src="{{ asset('images/svg-icon/layouts.svg') }}" class="img-fluid" alt="results">
                    <span>History</span>
                </a>
            </li>

            <li>
                <a href="#">
                    <img src="{{ asset('images/svg-icon/user.svg') }}" class="img-fluid" alt="profile">
                    <span>Profile</span>
                </a>
            </li>

            <li>
                <a href="javaScript:void();">
                    <img src="{{ asset('images/svg-icon/settings.svg') }}" class="img-fluid" alt="settings">
                    <span>Settings</span>
                    <i class="feather icon-chevron-right pull-right"></i>
                </a>
                <ul class="vertical-submenu">
                    <li><a href="#">Change Password</a></li>
                    <li><a href="#">Notification Settings</a></li>
                </ul>
            </li>

            <li>
                <a href="#">
                    <img src="{{ asset('images/svg-icon/logout.svg') }}" class="img-fluid" alt="logout">
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>
    <!-- End Navigationbar -->
</div>
<!-- End Sidebar -->
