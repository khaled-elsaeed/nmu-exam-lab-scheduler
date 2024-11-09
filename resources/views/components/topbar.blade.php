<!-- Start Topbar -->
<div class="topbar">
   <!-- Start row -->
   <div class="row align-items-center">
      <!-- Start col -->
      <div class="col-md-12 align-self-center">
         <div class="togglebar">
            <ul class="list-inline mb-0">
               <li class="list-inline-item">
                  <div class="menubar">
                     <a class="menu-hamburger" href="javascript:void();">
                     <img src="{{ asset('images/svg-icon/close.svg') }}" class="img-fluid menu-hamburger-close" alt="close">
                     <img src="{{ asset('images/svg-icon/collapse.svg') }}" class="img-fluid menu-hamburger-collapse" alt="collapse">
                     </a>
                  </div>
               </li>
            </ul>
         </div>
         <div class="infobar">
   <ul class="list-inline mb-0">
      <li class="list-inline-item">
         <div class="profilebar d-flex align-items-center">
            <!-- User Profile Icon -->
            <img src="{{ asset('images/users/profile.svg') }}" class="img-fluid me-2" alt="profile" style="margin-top:0px !important">

            <!-- Username -->
            <span>{{ auth()->user()->username }}</span>
         </div>
      </li>
   </ul>
</div>

      </div>
      <!-- End col -->
   </div>
   <!-- End row -->
</div>
<!-- End Topbar -->