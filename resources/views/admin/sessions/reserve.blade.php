@extends('layouts.admin')

@section('title', 'Sessions Management')

@section('links')
    {{-- CSS Dependencies --}}
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

@endsection

@section('content')


    {{-- Main Container --}}
    <div class="container-fluid px-4">
        {{-- Page Header --}}
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h3 mb-3 text-gray-800">Quiz Session Management</h1>
            </div>
        </div>

        {{-- Sessions Grid --}}
        <div class="row">
            @forelse($results as $date => $data)
                <div class="col-12 mb-4">
                    <div class="card border-primary shadow-sm">
                        <div class="card-header bg-primary text-white text-center">
                            <h5 class="m-0">
                                {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            {{-- Date Summary --}}
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <small class="d-block"><strong>Total Max Occupants:</strong> {{ $data['total_max_occupants'] }}</small>
                                </div>
                                <div class="col-md-4">
                                    <small class="d-block"><strong>Total Taken:</strong> {{ $data['total_taken'] }}</small>
                                </div>
                                <div class="col-md-4">
                                    <small class="d-block">
                                        <strong>Total Time Taken:</strong> 
                                        {{ $data['total_time_taken'] ? $data['total_time_taken'] . ' minutes' : 'N/A' }}
                                    </small>
                                </div>
                            </div>

                            <hr>

                            {{-- Sessions Grid --}}
                            <div class="row sessions-wrapper g-3">
                                @foreach($data['sessions'] as $session)
                                    <div class="col-lg-4 col-md-6">
                                        <div class="session-card card h-100 border border-secondary">
                                            <div class="card-body">
                                                <div class="session-time mb-3">
                                                    <h6 class="card-subtitle mb-2 text-muted">
                                                        {{ \Carbon\Carbon::parse($session['session']->start_time)->format('h:i A') }} 
                                                        - 
                                                        {{ \Carbon\Carbon::parse($session['session']->end_time)->format('h:i A') }}
                                                    </h6>
                                                </div>

                                                {{-- Occupancy Progress --}}
                                                @php
                                                    $takenPercentage = ($session['total_taken'] / $session['total_max_occupants']) * 100;
                                                @endphp
                                                <div class="progress mb-3">
                                                    <div 
                                                        class="progress-bar" 
                                                        role="progressbar" 
                                                        style="width: {{ $takenPercentage }}%" 
                                                        aria-valuenow="{{ $session['total_taken'] }}" 
                                                        aria-valuemin="0" 
                                                        aria-valuemax="{{ $session['total_max_occupants'] }}"
                                                    >
                                                        {{ $session['total_taken'] }} / {{ $session['total_max_occupants'] }}
                                                    </div>
                                                </div>

                                                {{-- Session Details --}}
                                                <div class="session-details mb-3">
                                                    <small class="d-block">
                                                        <strong>Occupied:</strong> 
                                                        {{ $session['total_taken'] ? $session['total_taken'] . ' students' : 'N/A' }}
                                                    </small>
                                                    <small class="d-block">
                                                        <strong>Capacity:</strong> 
                                                        {{ $session['total_max_occupants'] ? $session['total_max_occupants'] . ' students' : 'N/A' }}
                                                    </small>
                                                </div>

                                                {{-- Reserve Button --}}
                                                <div class="text-center">
                                                    <button 
                                                        class="btn btn-primary btn-sm btn-reserve" 
                                                        data-session-id="{{ $session['session']->id }}"
                                                    >
                                                        Reserve Session
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        No sessions available for the selected period.
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    {{-- admin/sessions/partials/reservation-modal.blade.php --}}
<div class="modal fade" id="reserveQuizSessionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <form id="reserveQuizSessionForm" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="sessionId" name="session_id">
            
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Reserve Quiz Session</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="row g-3">
                        {{-- Faculty Selection --}}
                        <div class="col-md-4">
                            <label for="faculty" class="form-label">Select Faculty</label>
                            <select class="form-select" id="faculty" name="faculty" required>
                                <option value="">Choose Faculty</option>
                                @foreach($faculties as $faculty)
                                    <option value="{{ $faculty->id }}">{{ $faculty->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Course Selection --}}
                        <div class="col-md-4">
                            <label for="course" class="form-label">Select Course</label>
                            <select class="form-select" id="course" name="course" disabled required>
                                <option value="">Select Course</option>
                            </select>
                        </div>

                        {{-- Quiz Selection --}}
                        <div class="col-md-4">
                            <label for="quiz" class="form-label">Select Quiz</label>
                            <select class="form-select" id="quiz" name="quiz_id" disabled required>
                                <option value="">Select Quiz</option>
                            </select>
                        </div>

                     

                        <div class="col-md-4">
                            <label for="student_counts" class="form-label">Students</label>
                            <input type="text" name="student_counts" id="student_counts" value="" disabled>
                        </div>

                        {{-- Reservation Type --}}
<div class="col-12">
    <label class="form-label">Reservation Type</label>
    <div class="d-flex gap-3">
        <div class="form-check">
            <input class="form-check-input" type="radio" name="reserve_type" id="automaticReserve" value="automatic" checked>
            <label class="form-check-label" for="automaticReserve">
                Automatic Assignment
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="reserve_type" id="specificReserve" value="specific">
            <label class="form-check-label" for="specificReserve">
                Specific Labs
            </label>
        </div>
    </div>
</div>

{{-- Building Order for Automatic Assignment --}}
<div class="col-12" id="automaticAssignmentSection">
    <label class="form-label">Building Preference Order</label>
    <ul id="buildingOrder" class="list-group list-group-sortable">
        <li class="list-group-item" data-id="5">Building 5</li>
        <li class="list-group-item" data-id="7">Building 7</li>
        <li class="list-group-item" data-id="2">Building 2</li>
    </ul>
</div>

{{-- Specific Labs Selection --}}
<div class="col-12 d-none" id="specificLabsSection">
    <label class="form-label">Select Specific Labs</label>

    <!-- Using a simple multi-select element -->
    <select class="form-control" id="specificLabSelect" name="labs">
        @foreach($labs as $lab)
            <option value="{{ $lab->id }}">
                {{ $lab->building }}-{{ $lab->floor }}-{{ $lab->number }}
            </option>
        @endforeach
    </select>

    <!-- Button to add selected labs -->
    <button type="button" id="addSpecificLabBtn" class="btn btn-primary mt-3">Add Selected Labs</button>

    <!-- Display the selected labs -->
    <div id="selectedLabsList" class="mt-3">
        <h5>Selected Labs:</h5>
        <ul id="labsDisplayList"></ul>
    </div>
</div>


                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Reserve Session</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
    {{-- JavaScript Dependencies --}}
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>

    {{-- Page-specific Scripts --}}
    <script src="{{ asset('js/pages/sessions-management.js') }}"></script>
    <script>

    window.routes = {
        getCoursesByFaculty: "{{ route('get.courses.by.faculty', ['facultyId' => ':facultyId']) }}",
        getQuizzesByCourse: "{{ route('admin.courses.quizzes', ['course' => ':courseId']) }}",
        reserveForQuiz: "{{ route('sessions.reserveForQuiz') }}",
        getQuizStudentCounts: "{{ route('admin.quizzes.student-counts', ['quizId' => ':quizId']) }}",
    };
</script>

@endsection