@extends('layouts.admin')

@section('title', 'Sessions Management')

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

   /* Card styling for session details */
.card-body {
    padding: 1rem;
}

.card-header {
    font-size: 1rem;
    padding: 0.5rem;
}

.card-footer {
    padding: 0.5rem;
}

.card-title {
    font-size: 1.1rem;
    font-weight: 600;
}



/* Small Adjustments for Compactness */
#lab-details-container {
    display: flex;
    flex-wrap: wrap;
}




</style>
@endsection

@section('content')
<!-- Start row -->
<div class="row">
    <!-- Start col for Total Sessions -->
    <div class="col-lg-3 col-md-6 mb-2">
        <div class="card m-b-30">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-5">
                        <span class="action-icon badge badge-primary-inverse me-0"><i class="feather icon-book-open"></i></span>
                    </div>
                    <div class="col-7 text-end mt-2 mb-2">
                        <h5 class="card-title font-14">Total Sessions</h5>
                        <h4 class="mb-0">{{ $totalSessions }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End col -->
</div>
<!-- End row -->

<!-- Action Buttons: Create Exam Period and Download -->
<div class="row mb-3">
    <div class="col-lg-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
            <!-- Create Exam Period Button -->
            <button type="button" class="btn btn-outline-success mb-2 mb-md-0" id="createExamPeriodBtn">
                <i class="feather icon-calendar"></i> Create Exam Period
            </button>
        </div>
    </div>
</div>

<!-- Sessions Table -->
<div class="row">
    <div class="col-lg-12">
        <div class="card m-b-30 table-card">
            <div class="card-body table-container">
                <div class="table-responsive">
                    <table id="default-datatable" class="display table table-bordered">
                        <thead>
                            <tr>
                                <th>Session</th>
                                <th>Date</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Occupied</th>
                                <th>Empty</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sessionData as $data)
                                <tr id="session-row-{{ $data['session']->id }}">
                                    <td>{{ $data['session']->id ?? 'N/A' }}</td>
                                    <td>{{ $data['session']->date }}</td>
                                    <td>{{ \Carbon\Carbon::parse($data['session']->start_time)->format('h:i A') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($data['session']->end_time)->format('h:i A') }}</td>
                                    <td>{{ $data['taken'] }}</td> <!-- Total Taken -->
                                    <td>{{ $data['remaining'] }}</td> <!-- Total Not Taken (Remaining) -->
                                    <td>
                                        <!-- Delete Session Button -->
                                        <button type="button" class="btn btn-round btn-danger-rgba delete-session-btn" 
                                            title="Delete Session" data-session-id="{{ $data['session']->id }}">
                                            <i class="feather icon-trash-2"></i>
                                        </button>

                                        <button type="button" class="btn btn-outline-info show-details-btn" 
                                            data-session-id="{{ $data['session']->id }}" data-bs-toggle="modal" data-bs-target="#sessionDetailsModal">
                                            More Details
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
<!-- End Sessions Table -->

<!-- Modal for More Session Details -->
<div class="modal fade" id="sessionDetailsModal" tabindex="-1" aria-labelledby="sessionDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sessionDetailsModalLabel">Session Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Lab Details will be injected here as cards -->
                <div id="lab-details-container" class="row g-2">
                    <!-- Individual Lab Cards will be inserted here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

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
    $(document).ready(function() {
        // Initialize DataTable only once
        if (!$.fn.dataTable.isDataTable('#default-datatable')) {
            $('#default-datatable').DataTable();
        }

        // Create Exam Period Button click handler
        $('#createExamPeriodBtn').click(function() {
            $(this).addClass('loading').attr('disabled', true);

            $.ajax({
                url: "{{ route('sessions.createExamPeriod') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    $('#createExamPeriodBtn').removeClass('loading').attr('disabled', false);
                    if (response.success) {
                        swal('Success!', 'Exam Period Sessions have been created.', 'success');
                    } else {
                        swal('Error!', 'Failed to create Exam Period Sessions.', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    $('#createExamPeriodBtn').removeClass('loading').attr('disabled', false);
                    swal('Error!', 'There was an error processing your request.', 'error');
                }
            });
        });

        // Handle Delete Session Button click
        $(document).on('click', '.delete-session-btn', function() {
            var sessionId = $(this).data('session-id');
            var row = $(this).closest('tr');

            swal({
                title: 'Are you sure?',
                text: "Do you want to delete this session?",
                icon: 'warning',
                buttons: ['Cancel', 'Delete'],
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: '/admin/sessions/delete/' + sessionId,
                        method: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}",
                        },
                        success: function(response) {
                            if (response.success) {
                                swal('Deleted!', 'The session has been deleted.', 'success');
                                row.remove(); // Remove row from the table
                            } else {
                                swal('Error!', 'Failed to delete the session.', 'error');
                            }
                        },
                        error: function() {
                            swal('Error!', 'An error occurred while deleting the session.', 'error');
                        }
                    });
                }
            });
        });

        // Open the "More Details" modal and load lab details
        $(document).on('click', '.show-details-btn', function() {
            var sessionId = $(this).data('session-id');

            // Clear any previous lab details
            $('#lab-details-container').empty();

            // Get the route from Laravel and inject the session ID
            var sessionLabsUrl = "{{ route('sessions.labs', ':sessionId') }}".replace(':sessionId', sessionId);

            // Fetch lab details using AJAX
            $.ajax({
                url: sessionLabsUrl,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        // Loop through all labs and create cards
                        response.labs.forEach(function(lab) {
                            // Calculate progress
                            var progressPercentage = (lab.current_capacity / lab.capacity) * 100;
                            $('#lab-details-container').append(
                                `<div class="col-md-4">
                                    <div class="card shadow-lg border-light mb-3 rounded">
                                        <div class="card-header text-white bg-primary rounded-top">
                                            <strong>${lab.location || 'Unknown Location'}</strong>
                                        </div>
                                        <div class="card-body p-2">
                                            <div class="d-flex justify-content-between">
                                                <span>Capacity:</span>
                                                <span><i class="feather icon-users"></i> ${lab.capacity || 'N/A'}</span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Current:</span>
                                                <span><i class="feather icon-user"></i> ${lab.current_capacity || '0'}</span>
                                            </div>
                                            <!-- Progress bar -->
                                            <div class="progress mt-2">
                                                <div class="progress-bar" role="progressbar" style="width: ${progressPercentage}%" aria-valuenow="${lab.current_capacity}" aria-valuemin="0" aria-valuemax="${lab.capacity}"></div>
                                            </div>
                                        </div>
                                        <div class="card-footer text-muted text-center p-1">
                                            <small>Lab Details</small>
                                        </div>
                                    </div>
                                </div>`
                            );
                        });
                    } else {
                        $('#lab-details-container').append(
                            `<div class="col-12">
                                <div class="alert alert-warning text-center" role="alert">
                                    No lab details found for this session.
                                </div>
                            </div>`
                        );
                    }
                },
                error: function() {
                    $('#lab-details-container').append(
                        `<div class="col-12">
                            <div class="alert alert-danger text-center" role="alert">
                                Error fetching lab details.
                            </div>
                        </div>`
                    );
                }
            });
        });
    });
</script>

@endsection
