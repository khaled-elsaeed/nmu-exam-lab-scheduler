


@extends('layouts.admin')

@section('title', 'Sessions Management')

@section('links')
    <style>
        /* Add a loading overlay with spinner */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            display: none; /* Initially hidden */
            align-items: center;
            justify-content: center;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
            border-width: 4px;
        }

        /* Modal & Content Styling */
        /* General Styling */
        .progress-bar {
            position: relative;
            height: 20px;  /* Reduced height for more compact progress bars */
        }

        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 0.8rem;  /* Smaller text for compact display */
            font-weight: bold;
            color: white;
        }

        .card-header h5 {
            font-size: 1.1rem;  /* Slightly smaller header font */
            font-weight: 600;
            margin-bottom: 10px; /* Reduced margin */
        }

        .card-body {
            padding: 15px;  /* Reduced padding for a more compact card */
        }

        .btn-reserve {
            width: 100%;  /* Full width for the button */
            font-size: 0.75rem;  /* Smaller font size */
            padding: 6px 10px;  /* Smaller padding for a compact button */
            margin-top: 5px;  /* Reduced top margin */
            text-align: center;
        }

        /* Flexbox styling for displaying sessions side by side */
        .sessions-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;  /* Reduced gap between session cards */
            justify-content: flex-start;  /* Evenly distribute the sessions */
        }

        .session-card {
            width: 32%;  /* Adjusted width for smaller session cards */
            min-width: 280px;  /* Minimum width to ensure readability */
            margin-bottom: 15px; /* Space between rows */
        }

        .session-time {
            font-weight: bold;
            font-size: 1rem;  /* Smaller font size for session time */
            margin-bottom: 10px;  /* Reduced margin between session time and progress bar */
        }

        .progress-container {
            width: 100%;
            margin-bottom: 10px;  /* Reduced bottom margin */
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .col-md-12 {
                width: 100%;
            }

            .session-card {
                width: 100%;  /* Make session cards full width on smaller screens */
            }

            .btn-reserve {
                width: auto;
                margin-top: 8px;  /* Keep consistent margin on small screens */
            }
        }
    </style>
@endsection

@section('content')
<link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />

<!-- Loading Overlay -->
<div id="loadingOverlay" class="loading-overlay">
    <div class="spinner-border text-light" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<div class="container">
    <div class="row">
        <!-- Loop through each date in the results -->
        @foreach($results as $date => $data)
            <div class="col-md-12 mb-4">
                <div class="card border-primary" style="min-height: 380px;"> <!-- Reduced height of card -->
                    <div class="card-header text-center">
                        <h5>{{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}</h5> <!-- Show formatted date -->
                    </div>
                    <div class="card-body">
                        <!-- Display aggregated data for the day -->
                        <div>
                            <small style="font-size: 0.9rem;"><strong>Total Max Occupants:</strong> {{ $data['total_max_occupants'] }}</small><br>
                            <small style="font-size: 0.9rem;"><strong>Total Taken:</strong> {{ $data['total_taken'] }}</small><br>
                            <small style="font-size: 0.9rem;"><strong>Total Time Taken:</strong> 
                            {{ $data['total_time_taken'] ? $data['total_time_taken'] . ' minutes' : 'N/A' }}</small>
                        </div>
                        <hr>
                        <!-- Wrapper for session cards, displayed side by side -->
                        <div class="sessions-wrapper flex-start">
                            @foreach($data['sessions'] as $session)
                                <div class="session-card">
                                    <div class="progress-container">
                                        <div class="progress-wrapper">
                                            <!-- Session time -->
                                             <!-- Progress Bar with dynamic width for taken percentage -->
                                             @php
                                                    // Calculate the taken percentage
                                                    $takenPercentage = ($session['total_taken'] / $session['total_max_occupants']) * 100;
                                                @endphp
                                            <div class="session-time mb-2">
                                                <label for="bar{{ $session['session']->id }}">
                                                    Session ({{ $session['session']->start_time }} to {{ $session['session']->end_time }})
                                                </label>
                    
                                                <div><small style="font-size: 0.9rem;"><strong>Total Student Occupied:</strong> 
                                                    {{ $session['total_taken'] ? $session['total_taken'] . ' students' : 'N/A' }}</small>
                                                </div>
                                                <div><small style="font-size: 0.9rem;"><strong>Total Student not:</strong> 
                                                    {{ $session['total_max_occupants'] ? $session['total_max_occupants'] . ' students' : 'N/A' }}</small>
                                                </div>
                                            </div>

                                            <div class="w-100">
                                                <div class="progress">                        
                                                    <div class="progress-bar" id="bar{{ $session['session']->id }}" role="progressbar" style="width: {{ $takenPercentage }}%" aria-valuenow="{{ $session['total_taken'] }}" aria-valuemin="0" aria-valuemax="{{ $session['total_max_occupants'] }}">
                                                        <span class="progress-text">{{ $session['total_taken'] }} / {{ $session['total_max_occupants'] }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Reserve Button beside each progress bar -->
                                            <div class="w-100 text-center">
                                                <button class="btn btn-sm btn-outline-primary rounded-pill btn-reserve" onclick="openReserveModal({{ $session['session']->id }})">Reserve</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Modal for quiz selection -->
<div class="modal fade" id="reserveModal" tabindex="-1" aria-labelledby="reserveModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="reserveModalLabel">Select Quiz</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="reserveForm">
            <div class="form-group">
                <label for="quizSelect">Choose a Quiz</label>
                <select class="form-control" id="quizSelect" required>
                    @foreach($quizzes as $quiz)
                        <option value="{{ $quiz->id }}">{{ $quiz->name }}</option>
                    @endforeach
                </select>
            </div>
            <input type="hidden" id="sessionId" name="session_id">
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="submitReservation()">Reserve</button>
      </div>
    </div>
  </div>
</div>

<script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>

<script>
    // Function to show the loading overlay
    function showLoading() {
        document.getElementById('loadingOverlay').style.display = 'flex'; // Show the loading overlay
        // Disable the modal and background content
        $('body').css('pointer-events', 'none');
    }

    // Function to hide the loading overlay
    function hideLoading() {
        document.getElementById('loadingOverlay').style.display = 'none'; // Hide the loading overlay
        $('body').css('pointer-events', 'auto'); // Enable user interaction again
    }

    // JavaScript function for opening the modal and passing the session ID
    function openReserveModal(sessionId) {
        document.getElementById('sessionId').value = sessionId;
        $('#reserveModal').modal('show');
    }

    // Function to handle form submission for reservation
    function submitReservation() {
        const sessionId = document.getElementById('sessionId').value;
        const quizId = document.getElementById('quizSelect').value;

        // Show loading overlay
        showLoading();

        // Send the data via jQuery AJAX
        $.ajax({
            url: '{{ route('sessions.reserveForQuiz') }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',  // CSRF token for security
                session_id: sessionId,
                quiz_id: quizId
            },
            success: function(response) {
              

                swal('Success!', 'Session reserved successfully!', 'success')
                .then(() => {
                    $('#reserveModal').modal('hide');
                    location.reload(); // Reload page to see new slots

                });
            },
            error: function(xhr, status, error) {
                let errorMessage = 'There was an error with the reservation. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }

                swal({
                    title: 'Error!',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            },
            complete: function() {
                hideLoading();
            }
        });
    }
</script>


@endsection
