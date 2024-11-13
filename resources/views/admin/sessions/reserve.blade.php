@extends('layouts.admin')

@section('title', 'Sessions Management')

@section('links')
    <style>
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
            border-width: 4px;
        }

        .progress-bar {
            position: relative;
            height: 20px;
        }

        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 0.8rem;
            font-weight: bold;
            color: white;
        }

        .card-header h5 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .card-body {
            padding: 15px;
        }

        .btn-reserve {
            width: 100%;
            font-size: 0.75rem;
            padding: 6px 10px;
            margin-top: 5px;
            text-align: center;
        }

        .sessions-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: flex-start;
        }

        .session-card {
            width: 32%;
            min-width: 280px;
            margin-bottom: 15px;
        }

        .session-time {
            font-weight: bold;
            font-size: 1rem;
            margin-bottom: 10px;
        }

        .progress-container {
            width: 100%;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .col-md-12 {
                width: 100%;
            }

            .session-card {
                width: 100%;
            }

            .btn-reserve {
                width: auto;
                margin-top: 8px;
            }
        }
    </style>
@endsection

@section('content')
<link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />

<div id="loadingOverlay" class="loading-overlay">
    <div class="spinner-border text-light" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<div class="container">
    <div class="row">
        @foreach($results as $date => $data)
            <div class="col-md-12 mb-4">
                <div class="card border-primary" style="min-height: 380px;">
                    <div class="card-header text-center">
                        <h5>{{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}</h5>
                    </div>
                    <div class="card-body">
                        <div>
                            <small style="font-size: 0.9rem;"><strong>Total Max Occupants:</strong> {{ $data['total_max_occupants'] }}</small><br>
                            <small style="font-size: 0.9rem;"><strong>Total Taken:</strong> {{ $data['total_taken'] }}</small><br>
                            <small style="font-size: 0.9rem;"><strong>Total Time Taken:</strong> 
                            {{ $data['total_time_taken'] ? $data['total_time_taken'] . ' minutes' : 'N/A' }}</small>
                        </div>
                        <hr>
                        <div class="sessions-wrapper flex-start">
                            @foreach($data['sessions'] as $session)
                                <div class="session-card">
                                    <div class="progress-container">
                                        <div class="progress-wrapper">
                                            @php
                                                $takenPercentage = ($session['total_taken'] / $session['total_max_occupants']) * 100;
                                            @endphp
                                            <div class="session-time mb-2">
                                                <label for="bar{{ $session['session']->id }}">
                                                Session ({{ \Carbon\Carbon::parse($session['session']->start_time)->format('h:i A') }} to {{ \Carbon\Carbon::parse($session['session']->end_time)->format('h:i A') }})
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

<div class="modal fade" id="reserveQuizSessionModal" tabindex="-1" role="dialog" aria-labelledby="reserveQuizSessionModalLabel" aria-hidden="true">
   <div class="modal-dialog" role="document">
      <form id="reserveQuizSessionForm" method="POST" enctype="multipart/form-data">
         @csrf
         <input type="hidden" id="sessionId" name="session_id">
         <input type="hidden" id="quizSelect" name="quiz_id">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="reserveQuizSessionModalLabel">Add New Quiz</h5>
               <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
               <div class="form-group">
                  <label for="faculty">Select Faculty</label>
                  <select class="form-control" id="faculty" name="faculty" required>
                     <option value="" disabled selected>Select Faculty</option>
                     @foreach($faculties as $faculty)
                         <option value="{{ $faculty->id }}">{{ $faculty->name }}</option>
                     @endforeach
                  </select>
               </div>
               
               <div class="form-group">
                  <label for="course">Select Course</label>
                  <select class="form-control" id="course" name="course" required disabled>
                     <option value="">Select Course</option>
                  </select>
               </div>

               <div class="form-group">
                  <label for="quiz">Select Quiz</label>
                  <select class="form-control" id="quiz" name="quiz_id" required disabled>
                     <option value="">Select Quiz</option>
                  </select>
               </div>
               
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
               <button type="submit" class="btn btn-primary">Save Quiz</button>
            </div>
         </div>
      </form>
   </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>

<script>
    function showLoading() {
        document.getElementById('loadingOverlay').style.display = 'flex';
        document.body.style.pointerEvents = 'none';
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').style.display = 'none';
        document.body.style.pointerEvents = 'auto';
    }

    function openReserveModal(sessionId) {
        $('#sessionId').val(sessionId);
        $('#reserveQuizSessionModal').modal('show');
    }

    $('#faculty').on('change', function() {
        const facultyId = $(this).val();
        const courseDropdown = $('#course');

        courseDropdown.html('<option value="">Select Course</option>');

        if (facultyId) {
            courseDropdown.prop('disabled', false);

            const url = "{{ route('get.courses.by.faculty', ['facultyId' => ':facultyId']) }}".replace(':facultyId', facultyId);

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                beforeSend: showLoading,
                success: function(courses) {
                    hideLoading();
                    if (courses.length > 0) {
                        courses.forEach(course => {
                            courseDropdown.append(`<option value="${course.id}">${course.name} (${course.code})</option>`);
                        });
                    } else {
                        courseDropdown.html('<option value="" disabled>No courses available for this faculty</option>');
                    }
                },
                error: function() {
                    hideLoading();
                    courseDropdown.html('<option value="" disabled>Error loading courses</option>');
                }
            });
        } else {
            courseDropdown.prop('disabled', true);
        }
    });

    $('#course').on('change', function() {
        const courseId = $(this).val();
        const quizDropdown = $('#quiz');
        
        quizDropdown.html('<option value="">Select Quiz</option>');

        if (courseId) {
            quizDropdown.prop('disabled', false);

            const url = "{{ route('admin.courses.quizzes', ['course' => ':courseId']) }}".replace(':courseId', courseId);

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                beforeSend: showLoading,
                success: function(response) {
                    hideLoading();
                    if (response.success && response.quizzes.length > 0) {
                        response.quizzes.forEach(quiz => {
                            quizDropdown.append(`<option value="${quiz.id}">${quiz.name}</option>`);
                        });
                    } else {
                        quizDropdown.html('<option value="" disabled>No quizzes available for this course</option>');
                    }
                },
                error: function() {
                    hideLoading();
                    quizDropdown.html('<option value="" disabled>Error loading quizzes</option>');
                }
            });
        } else {
            quizDropdown.prop('disabled', true);
        }
    });

    $('#reserveQuizSessionForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: "{{ route('sessions.reserveForQuiz') }}",
            type: "POST",
            data: $(this).serialize(),
            beforeSend: showLoading,
            success: function(response) {
                $('#reserveQuizSessionModal').modal('hide');
                swal('success!', 'The reservation has been successfully.', 'success')
                .then(() => {
                            location.reload();  // Refresh the page to reflect the change
                        });
            },
            error: function(xhr) {
                let errorMessage = 'Failed to reserve the quiz.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                swal('Error!', errorMessage, 'error');
            },
            complete: function() {
                hideLoading();
            }
        });
    });
</script>
@endsection
