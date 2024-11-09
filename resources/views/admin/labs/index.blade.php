@extends('layouts.admin')

@section('title', 'Labs Management')

@section('links')
<link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('plugins/datatables/buttons.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('css/custom-datatable.css') }}" rel="stylesheet" type="text/css" />
<style>
    .loading { pointer-events: none; /* Disable button interactions */ }
</style>
@endsection

@section('content')
<!-- Overview Cards -->
<div class="row">
    <div class="col-lg-3 col-md-6 mb-2">
        <div class="card m-b-30">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-5">
                        <span class="action-icon badge badge-primary-inverse"><i class="feather icon-box"></i></span>
                    </div>
                    <div class="col-7 text-end">
                        <h5 class="card-title">Total Labs</h5>
                        <h4>{{ $totalLabs }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-2">
        <div class="card m-b-30">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-5">
                        <span class="action-icon badge badge-success-inverse"><i class="feather icon-users"></i></span>
                    </div>
                    <div class="col-7 text-end">
                        <h5 class="card-title">Total Capacity</h5>
                        <h4>{{ $totalCapacity }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Button to open Create Lab Modal -->
<button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createLabModal">
    <i class="feather icon-plus"></i> Add New Lab
</button>

<!-- Labs Table -->
<div class="card m-b-30 table-card">
    <div class="card-body table-container">
        <div class="table-responsive">
            <table id="default-datatable" class="display table table-bordered">
                <thead>
                    <tr>
                        <th>Building</th>
                        <th>Floor</th>
                        <th>Lab Number</th>
                        <th>Capacity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($labs as $lab)
                        <tr id="lab-row-{{ $lab->id }}">
                            <td>Building {{ $lab->building ?? 'N/A' }}</td>
                            <td>Floor {{ $lab->floor ?? 'N/A' }}</td>
                            <td>Lab {{ $lab->number ?? 'N/A' }}</td>
                            <td>{{ $lab->capacity ?? 'N/A' }}</td>
                            <td>
                                <button type="button" class="btn btn-round btn-primary edit-lab-btn" 
                                    data-lab-id="{{ $lab->id }}" data-building="{{ $lab->building }}" data-floor="{{ $lab->floor }} "
                                    data-number="{{ $lab->number }}" data-capacity="{{ $lab->capacity }}">
                                    <i class="feather icon-edit-2"></i>
                                </button>
                                <button type="button" class="btn btn-round btn-danger delete-lab-btn" 
                                    data-lab-id="{{ $lab->id }}">
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

<!-- Create Lab Modal -->
<div class="modal fade" id="createLabModal" tabindex="-1" role="dialog" aria-labelledby="createLabModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createLabModalLabel">Add New Lab</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createLabForm">
                    @csrf
                    <div class="mb-3">
                        <label for="building" class="form-label">Building</label>
                        <input type="text" class="form-control" id="building" name="building" required>
                    </div>
                    <div class="mb-3">
                        <label for="floor" class="form-label">Floor</label>
                        <input type="text" class="form-control" id="floor" name="floor" required>
                    </div>
                    <div class="mb-3">
                        <label for="number" class="form-label">Lab Number</label>
                        <input type="text" class="form-control" id="number" name="number" required>
                    </div>
                    <div class="mb-3">
                        <label for="capacity" class="form-label">Capacity</label>
                        <input type="number" class="form-control" id="capacity" name="capacity" required min="1">
                    </div>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Lab Modal -->
<div class="modal fade" id="editLabModal" tabindex="-1" role="dialog" aria-labelledby="editLabModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editLabModalLabel">Edit Lab</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editLabForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editLabId" name="id">
                    <div class="mb-3">
                        <label for="editBuilding" class="form-label">Building</label>
                        <input type="text" class="form-control" id="editBuilding" name="building" required>
                    </div>
                    <div class="mb-3">
                        <label for="editFloor" class="form-label">Floor</label>
                        <input type="text" class="form-control" id="editFloor" name="floor" required>
                    </div>
                    <div class="mb-3">
                        <label for="editNumber" class="form-label">Lab Number</label>
                        <input type="text" class="form-control" id="editNumber" name="number" required>
                    </div>
                    <div class="mb-3">
                        <label for="editCapacity" class="form-label">Capacity</label>
                        <input type="number" class="form-control" id="editCapacity" name="capacity" required min="1">
                    </div>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>

<script>
    $(document).ready(function() {
        $('#default-datatable').DataTable();

        const storeLabRoute = "{{ route('labs.store') }}";
        const updateLabRoute = "{{ route('labs.update', ':id') }}";
        const deleteLabRoute = "{{ route('labs.destroy', ':id') }}";
        const redirectRoute = "{{ route('labs.index') }}";  // Redirect after success (use the appropriate route)

        // Handle create lab form submission
        $('#createLabForm').submit(function(event) {
            event.preventDefault();

            const formData = $(this).serialize();

            $.ajax({
                url: storeLabRoute,
                type: 'POST',
                data: formData,
                success: function(lab) {
                    $('#createLabModal').modal('hide');
                    $('#createLabForm')[0].reset();
                    swal('Created!', 'New lab added.', 'success');

                    // Refresh the page after showing the success message
                    setTimeout(function() {
                        window.location.href = redirectRoute;
                    }, 1000); // Delay for 2 seconds to show the success message before refreshing
                },
                error: function() {
                    swal('Error!', 'Failed to add new lab.', 'error');
                }
            });
        });

        // Handle edit lab button click
        $(document).on('click', '.edit-lab-btn', function() {
            const labId = $(this).data('lab-id');
            const building = $(this).data('building');
            const floor = $(this).data('floor');
            const number = $(this).data('number');
            const capacity = $(this).data('capacity');

            $('#editLabId').val(labId);
            $('#editBuilding').val(building);
            $('#editFloor').val(floor);
            $('#editNumber').val(number);
            $('#editCapacity').val(capacity);

            $('#editLabModal').modal('show');
        });

        // Handle edit lab form submission
        $('#editLabForm').submit(function(event) {
            event.preventDefault();

            const formData = $(this).serialize();
            const labId = $('#editLabId').val();

            $.ajax({
                url: updateLabRoute.replace(':id', labId),
                type: 'PUT',
                data: formData,
                success: function(updatedLab) {
                    

                    $('#editLabModal').modal('hide');
                    swal('Updated!', 'Lab details have been updated.', 'success');

                    // Refresh the page after showing the success message
                    setTimeout(function() {
                        window.location.href = redirectRoute;
                    }, 1000); // Delay for 2 seconds to show the success message before refreshing
                },
                error: function() {
                    swal('Error!', 'Failed to update lab.', 'error');
                }
            });
        });

        // Handle delete button click with SweetAlert2 confirmation
        $(document).on('click', '.delete-lab-btn', function() {
            const labId = $(this).data('lab-id');
            const deleteUrl = deleteLabRoute.replace(':id', labId);

            // SweetAlert2 with two buttons for confirmation
            swal({
                title: 'Are you sure?',
                text: "This action cannot be undone.",
                icon: 'warning',
                buttons: {
                    cancel: {
                        text: "Cancel",
                        value: null,
                        visible: true,
                        className: "btn btn-secondary",
                        closeModal: true
                    },
                    confirm: {
                        text: "Delete",
                        value: true,
                        visible: true,
                        className: "btn btn-danger",
                        closeModal: false
                    }
                },
                dangerMode: true,
            }).then((willDelete) => {
                    $.ajax({
                        url: deleteUrl,
                        type: 'DELETE',
                        data: { _token: "{{ csrf_token() }}" },
                        success: function() {
                            // Remove the row from the table
                            $('#lab-row-' + labId).remove();
                            // Show success message
                            swal('Deleted!', 'Lab has been deleted.', 'success');
                        },
                        error: function() {
                            // Show error message if the AJAX request fails
                            swal('Error!', 'Failed to delete lab.', 'error');
                        }
                    });
                
            });
        });

    });
</script>

@endsection
