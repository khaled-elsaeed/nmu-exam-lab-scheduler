@extends('layouts.admin')

@section('title', 'Sessions Management')

@section('links')
    <style>
        .card-header h4 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0;
        }

        .card-body {
            padding: 20px;
        }

        .session-card {
            background: #f7f7f7;
            border: 1px solid #dfe2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            transition: box-shadow 0.3s ease;
        }

        .session-card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .session-card .session-time {
            font-weight: 500;
            color: #263a5b;
            margin-bottom: 10px;
        }

        .session-details {
            margin-bottom: 15px;
        }

        .session-details small {
            font-size: 0.9rem;
            display: block;
            margin-bottom: 8px;
        }

        .sessions-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .quiz-card {
            background: #ffffff;
            border: 1px solid #dfe2e6;
            border-radius: 8px;
            padding: 15px;
            min-width: 280px;
            transition: box-shadow 0.3s ease;
        }

        .quiz-card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-reject {
            background-color: #e74c3c;
        }

        .btn-reject:hover {
            background-color: #c0392b;
        }

        .btn-accept {
            background-color: #27ae60;
        }

        .btn-accept:hover {
            background-color: #2ecc71;
        }

        .card-header .date-header {
            padding: 12px;
            border-radius: 5px;
        }

        @media (max-width: 768px) {
            .session-card {
                width: 100%;
            }

            .quiz-card {
                width: 100%;
            }
        }
    </style>
@endsection

@section('content')
<link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />

<div class="container">
    <div class="row">
         <!-- Export Labs Button -->
         <div class="text-right m-b-15">
                                      
            <!-- Export Quizzes Button -->
            <button type="button" class="btn btn-sm btn-primary btn-export-labs" >
                                            Export Labs
                                        </button>
                                        <!-- Export Quizzes Button -->
                                        <button type="button" class="btn btn-sm btn-secondary btn-export-quizzes">
                                            Export Quizzes
                                        </button>
        </div>
        @foreach($groupedSessions as $date => $sessions)
            <div class="col-md-12 mb-4">
                <div class="card border-primary">
                    <div class="card-header text-center text-white bg-primary">
                        <div class="date-header text-white">
                            <h4 class="text-white">{{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        @foreach($sessions as $sessionWithQuiz)
                            <div class="session-wrapper">
                                <div class="session-card">
                                    <h5 class="session-time">
                                        {{ \Carbon\Carbon::parse($sessionWithQuiz['session']->start_time)->format('h:i A') }} to 
                                        {{ \Carbon\Carbon::parse($sessionWithQuiz['session']->end_time)->format('h:i A') }}
                                    </h5>

                                    <div class="session-details">
                                        <small><strong>Total Max Occupants:</strong> {{ $sessionWithQuiz['session']->slots->sum('max_students') }}</small>
                                        <small><strong>Total Taken:</strong> {{ $sessionWithQuiz['session']->slots->sum('current_students') }}</small>
                                        <small><strong>Total Time Taken:</strong> {{ $sessionWithQuiz['session']->total_time_taken }} minutes</small>
                                    </div>

                                    <hr>

                                   

                                    <div class="sessions-wrapper">
                                        <h6><strong>Quizzes:</strong></h6>
                                        @foreach($sessionWithQuiz['quizzes'] as $quiz)
                                            <div class="quiz-card">
                                                <div>
                                                    <strong>{{ $quiz->course->faculty->name ?? 'No Faculty Assigned' }}</strong><br>
                                                    <strong>{{ $quiz->name }}</strong><br>
                                                    <small>Students Enrolled: {{ $quiz->students->count() }}</small><br>
                                                </div>
                                                <div class="w-100 text-center mt-2">
                                                    @if($quiz->status == 'pending')
                                                        <button class="btn btn-sm btn-accept text-white" onclick="acceptReservation({{ $quiz->id }})">Accept Reservation</button>
                                                        <button class="btn btn-sm btn-reject text-white" onclick="rejectReservation({{ $quiz->id }})">Reject Reservation</button>
                                                    @elseif($quiz->status == 'rejected')
                                                        <p>Status: It was rejected before</p>
                                                        <button class="btn btn-sm btn-accept text-white" onclick="acceptReservation({{ $quiz->id }})">Accept Reservation</button>
                                                        <button class="btn btn-sm btn-reject text-white" onclick="rejectReservation({{ $quiz->id }})">Reject Reservation</button>
                                                    @else
                                                        <p>Status: {{ ucfirst($quiz->status) }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>

<script>
    // Function to accept a reservation for a quiz
    function acceptReservation(quizId) {
        swal({
            title: 'Are you sure?',
            text: 'You are about to accept the reservation for this quiz.',
            icon: 'success',
            buttons: ['Cancel', 'Accept'],
            dangerMode: false
        }).then((willAccept) => {
            if (willAccept) {
                $.ajax({
                    url: '{{ route("sessions.acceptReservationForQuiz") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        quiz_id: quizId
                    },
                    success: function(response) {
                        swal({
                            title: 'Accepted!',
                            text: 'The reservation for this quiz has been accepted.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr, status, error) {
                        let errorMessage = 'There was an error accepting the reservation.';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }

                        swal({
                            title: 'Error!',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    }

    // Function to reject a reservation for a quiz
    function rejectReservation(quizId) {
        swal({
            title: 'Are you sure?',
            text: 'You are about to reject the reservation for this quiz.',
            icon: 'warning',
            buttons: ['Cancel', 'Reject'],
            dangerMode: true
        }).then((willReject) => {
            if (willReject) {
                $.ajax({
                    url: '{{ route("sessions.reverseReservationForQuiz") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        quiz_id: quizId
                    },
                    success: function(response) {
                        swal({
                            title: 'Rejected!',
                            text: 'The reservation for this quiz has been rejected.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr, status, error) {
                        let errorMessage = 'There was an error rejecting the reservation.';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }

                        swal({
                            title: 'Error!',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    }

    // Function to handle the export lab action
function exportLabs() {
    // Use Laravel's route() helper to generate the correct URL
    const exportUrl = '{{ route('sessions.exportLabs') }}';

    $.ajax({
       url: exportUrl,
       type: 'GET',
       xhrFields: {
           responseType: 'blob' 
       },
       success: function(response, status, xhr) {
           const contentDisposition = xhr.getResponseHeader('Content-Disposition');
           let filename = "downloaded_file"; // Default filename
           if (contentDisposition) {
               // Extract the filename from Content-Disposition header
               const matches = contentDisposition.match(/filename="?([^"]+)"?/);
               if (matches && matches[1]) filename = matches[1];
           }
           
           // Create a download link
           const link = document.createElement('a');
           link.href = URL.createObjectURL(response);
           link.download = filename;
           link.click();
           URL.revokeObjectURL(link.href);
       },
       error: function(xhr) {
           swal('Error!', 'Failed to export the quiz.', 'error');
       },
       
   });
}

// Attach the exportLabs function to the Export Labs button
document.querySelectorAll('.btn-export-labs').forEach(button => {
    button.addEventListener('click', function () {
        const sessionId = this.getAttribute('data-session-id');
        exportLabs(sessionId);
    });
});

// Function to handle the export quiz action
function exportQuizzes() {
        // Use Laravel's route() helper to generate the correct URL
        const exportUrl = '{{ route('sessions.exportQuizzes') }}';

        $.ajax({
       url: exportUrl,
       type: 'GET',
       xhrFields: {
           responseType: 'blob' 
       },
       success: function(response, status, xhr) {
           const contentDisposition = xhr.getResponseHeader('Content-Disposition');
           let filename = "downloaded_file"; // Default filename
           if (contentDisposition) {
               // Extract the filename from Content-Disposition header
               const matches = contentDisposition.match(/filename="?([^"]+)"?/);
               if (matches && matches[1]) filename = matches[1];
           }
           
           // Create a download link
           const link = document.createElement('a');
           link.href = URL.createObjectURL(response);
           link.download = filename;
           link.click();
           URL.revokeObjectURL(link.href);
       },
       error: function(xhr) {
           swal('Error!', 'Failed to export the quiz.', 'error');
       },
     
   });
        }

    // Attach the exportQuizzes function to the Export Quizzes button
    document.querySelectorAll('.btn-export-quizzes').forEach(button => {
        button.addEventListener('click', function () {
            const sessionId = this.getAttribute('data-session-id');
            exportQuizzes(sessionId);
        });
    });

</script>
@endsection
