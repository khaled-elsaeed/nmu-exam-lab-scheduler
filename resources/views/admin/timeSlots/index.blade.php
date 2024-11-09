@extends('layouts.admin')
@section('title', 'Time Slots Management')

@section('links')
<!-- DataTables CSS -->
<link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('plugins/datatables/buttons.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('css/custom-datatable.css') }}" rel="stylesheet" type="text/css" />
<style>
    .loading {
        pointer-events: none; /* Disable button interactions */
    }
</style>
@endsection

@section('content')
<!-- Start row -->
<div class="row">
   <!-- Start col -->
   <div class="col-lg-12">
      <!-- Start row -->
      <div class="row">
         <!-- Start col -->
         <div class="col-lg-3 col-md-6 mb-2">
            <div class="card m-b-30">
               <div class="card-body">
                  <div class="row align-items-center">
                     <div class="col-5">
                        <span class="action-icon badge badge-primary-inverse me-0"><i class="feather icon-user"></i></span>
                     </div>
                     <div class="col-7 text-end mt-2 mb-2">
                        <h5 class="card-title font-14">Total Slots</h5>
                        <h4 class="mb-0">{{ $totalSlots }}</h4>
                     </div>
                  </div>
               </div>
               <div class="card-footer">
                  <div class="row align-items-center">
                     <div class="col-6 text-start">
                        <span class="font-13">Total Students Signed Up</span>
                     </div>
                     <div class="col-6 text-end">
                        <span class="font-13">{{ $totalSignedUp }}</span>
                     </div>
                  </div>
                  <div class="row align-items-center">
                     <div class="col-9 text-start">
                        <span class="font-13">Available Student Places</span>
                     </div>
                     <div class="col-3 text-end">
                        <span class="font-13">{{ $availableStudentPlaces }}</span>
                     </div>
                  </div>
                  <div class="row align-items-center">
                     <div class="col-9 text-start">
                        <span class="font-13">Empty Slots</span>
                     </div>
                     <div class="col-3 text-end">
                        <span class="font-13">{{ $emptySlots }}</span>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <!-- End col -->
         <!-- Other cards and columns can go here -->
      </div>
      <!-- End row -->
   </div>
   <!-- End col -->
</div>
<!-- End row -->


<!-- Start row -->
<div class="row">
    <!-- Start col -->
    <div class="col-lg-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-3">
            <!-- Title on the Left -->
            <h2 class="page-title text-primary mb-2 mb-md-0">Time Slots Management</h2>
            <!-- Generate Time Slots Button on the Right -->
            <button class="btn btn-outline-primary" id="generateTimeSlotsButton" type="button">
                <i class="fa fa-plus"></i> Generate Time Slots
            </button>
        </div>
        
    </div>
    <!-- End col -->
</div>

<!-- Start row for Time Slots Table -->
<div class="row">
    <div class="col-lg-12">
        <div class="card m-b-30 table-card">
            <div class="card-body table-container">
                <div class="table-responsive">
                    <table id="default-datatable" class="display table table-bordered">
                        <thead>
                            <tr>
                                <th>Slot ID</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($timeSlots as $slot)
                                <tr>
                                    <td>{{ $slot->id }}</td>
                                    <td>{{ $slot->start_time }}</td>
                                    <td>{{ $slot->end_time }}</td>
                                    <td>{{ ucfirst($slot->status) }}</td>
                                    <td>
                                        <!-- Edit Time Slot Button -->
                                        <button type="button" class="btn btn-round btn-primary-rgba" title="Edit Time Slot">
                                            <i class="feather icon-edit"></i>
                                        </button>
                                        
                                        <!-- Delete Time Slot Button -->
                                        <button type="button" class="btn btn-round btn-danger-rgba" title="Delete Time Slot">
                                            <i class="feather icon-trash-2"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End row for Time Slots Table -->

@endsection

@section('scripts')
<!-- Datatable JS -->
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('plugins/datatables/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables/jszip.min.js') }}"></script>
<script src="{{ asset('plugins/datatables/pdfmake.min.js') }}"></script>
<script src="{{ asset('plugins/datatables/vfs_fonts.js') }}"></script>
<script src="{{ asset('plugins/datatables/buttons.html5.min.js') }}"></script>
<script src="{{ asset('plugins/datatables/buttons.print.min.js') }}"></script>
<script src="{{ asset('plugins/datatables/buttons.colVis.min.js') }}"></script>
<script src="{{ asset('plugins/datatables/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('plugins/datatables/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>

<script>
    // Initialize DataTable
    $(document).ready(function() {
        $('#default-datatable').DataTable();
    });

    // Generate Time Slots Button Click
    $('#generateTimeSlotsButton').on('click', function() {
        swal({
            title: 'Generate Time Slots',
            text: "Do you want to create new time slots?",
            showCancelButton: true,
            confirmButtonText: 'Yes, generate!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            
                $.ajax({
                    url: "{{ route('generateTimeSlots') }}", // Named route
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    beforeSend: function() {
                        $('#generateTimeSlotsButton').addClass('loading').text('Generating...');
                    },
                    success: function(response) {
                        swal('Generated!', 'Time slots have been created.', 'success');
                        location.reload(); // Reload page to see new slots
                    },
                    error: function() {
                        swal('Error!', 'An error occurred while generating time slots.', 'error');
                    },
                    complete: function() {
                        $('#generateTimeSlotsButton').removeClass('loading').text('Generate Time Slots');
                    }
                });
            
        });
    });

    

</script>
@endsection
