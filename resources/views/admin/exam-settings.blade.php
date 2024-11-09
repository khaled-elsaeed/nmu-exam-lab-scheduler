@extends('layouts.admin')

@section('title', 'Exam Settings')

@section('content')
<link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />

<div class="container">
    <h2 class="page-title text-primary mb-3">Exam Settings</h2>
    <!-- Exam Settings Overview -->
    <div class="row justify-content-center">
        <!-- Widen the card by increasing column width to 8 -->
        <div class="col-lg-8 col-md-10 mb-3">
            <div class="card">
                <div class="card-body">
                    <form id="exam-settings-form">
                        @csrf
                        @method('POST')

                        <!-- Start of Flex Container for Inputs -->
                        <div class="d-flex flex-wrap mb-3"> 
                            <!-- Start Date Input -->
                            <div class="form-group mr-3 flex-fill mb-3">
                                <label for="start_date" class="col-form-label">Start Date</label>
                                <input type="date" id="start_date" name="start_date" class="form-control" value="{{ old('start_date', $examSettings->start_date ?? '') }}" required>
                            </div>
                            
                            <!-- End Date Input -->
                            <div class="form-group mr-3 flex-fill mb-3">
                                <label for="end_date" class="col-form-label">End Date</label>
                                <input type="date" id="end_date" name="end_date" class="form-control" value="{{ old('end_date', $examSettings->end_date ?? '') }}" required>
                            </div>
                        </div>

                        <!-- Start of Flex Container for Time Inputs -->
                        <div class="d-flex flex-wrap mb-3">
                            <!-- Daily Start Time Input -->
                            <div class="form-group mr-3 flex-fill mb-3">
                                <label for="daily_start_time" class="col-form-label">Daily Start Time</label>
                                <input type="time" id="daily_start_time" name="daily_start_time" class="form-control" value="{{ old('daily_start_time', $examSettings->daily_start_time ?? '') }}" required>
                            </div>
                            
                            <!-- Daily End Time Input -->
                            <div class="form-group mr-3 flex-fill mb-3">
                                <label for="daily_end_time" class="col-form-label">Daily End Time</label>
                                <input type="time" id="daily_end_time" name="daily_end_time" class="form-control" value="{{ old('daily_end_time', $examSettings->daily_end_time ?? '') }}" required>
                            </div>
                        </div>

                        <!-- Time Slot Duration and Rest Period (Beside each other) -->
                        <div class="d-flex flex-wrap mb-3">
                            <!-- Time Slot Duration Input -->
                            <div class="form-group mr-3 flex-fill mb-3">
                                <label for="time_slot_duration" class="col-form-label">Time Slot Duration (minutes)</label>
                                <input type="number" id="time_slot_duration" name="time_slot_duration" class="form-control" value="{{ old('time_slot_duration', $examSettings->time_slot_duration ?? '') }}" required>
                            </div>

                            <!-- Rest Period Input -->
                            <div class="form-group mr-3 flex-fill mb-3">
                                <label for="rest_period" class="col-form-label">Rest Period (minutes)</label>
                                <input type="number" id="rest_period" name="rest_period" class="form-control" value="{{ old('rest_period', $examSettings->rest_period ?? '') }}" required>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="form-group mb-3">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </div>
                    </form>

                    <hr>

                    <h5 class="text-secondary">Last Updated: <span id="last-updated">{{ $examSettings ? $examSettings->updated_at->format('H:i d/m/Y') : 'N/A' }}</span></h5>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>

<script>
   $(document).ready(function() {
    // Handle form submission with AJAX
    $('#exam-settings-form').submit(function(e) {
        e.preventDefault(); // Prevent the default form submission

        // Ensure that the time values are formatted correctly (HH:mm)
        var startTime = $('#daily_start_time').val();
        var endTime = $('#daily_end_time').val();

        // Format to H:i (24-hour format)
        if (startTime && !/^\d{2}:\d{2}$/.test(startTime)) {
            // If it's not in the correct format, change it to 'HH:mm'
            var timeParts = startTime.split(":");
            startTime = (timeParts[0].padStart(2, '0')) + ':' + (timeParts[1].padStart(2, '0'));
            $('#daily_start_time').val(startTime);
        }
        if (endTime && !/^\d{2}:\d{2}$/.test(endTime)) {
            // If it's not in the correct format, change it to 'HH:mm'
            var timeParts = endTime.split(":");
            endTime = (timeParts[0].padStart(2, '0')) + ':' + (timeParts[1].padStart(2, '0'));
            $('#daily_end_time').val(endTime);
        }

        // Serialize the form data
        var formData = $(this).serialize(); 

        // Make AJAX POST request
        $.ajax({
            url: '{{ route("exam-settings.update", $examSettings->id ?? 0) }}', // Update the route
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.status === 'success') {
                    // Show success SweetAlert message
                    swal({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        confirmButtonText: 'OK'
                    });

                    // Update the last updated time
                    $('#last-updated').text(new Date().toLocaleString());
                    
                    // Update form values if necessary
                    $('#start-date').text(response.examSettings.start_date);
                    $('#end-date').text(response.examSettings.end_date);
                    $('#daily-start-time').text(response.examSettings.daily_start_time);
                    $('#daily-end-time').text(response.examSettings.daily_end_time);
                    $('#time-slot-duration').text(response.examSettings.time_slot_duration);
                    $('#rest-period').text(response.examSettings.rest_period);
                }
            },
            error: function(xhr, status, error) {
                // Show failure SweetAlert message
                swal({
                    icon: 'error',
                    title: 'Error!',
                    text: 'There was an error updating the exam settings. Please try again.',
                    confirmButtonText: 'OK'
                });
            }
        });
    });
});
</script>
@endsection
