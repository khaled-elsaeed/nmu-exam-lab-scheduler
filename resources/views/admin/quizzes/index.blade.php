@extends('layouts.admin')
@section('title', 'Quiz Management')
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
</style>
@endsection
@section('content')
<!-- Loading Overlay -->
<div id="loadingOverlay" class="loading-overlay">
   <div class="spinner-border text-light" role="status">
      <span class="visually-hidden">Loading...</span>
   </div>
</div>
<div class="d-flex flex-column mb-3">
   <!-- Title in its own row -->
   <h2 class="page-title text-primary mb-2">Buildings</h2>
</div>
<div class="d-flex justify-content-between align-items-center mb-3">
   <!-- Left Section with Add Building Button -->
   <div class="d-flex">
      
   </div>
   <!-- Right Section with Toggle and Download Dropdown -->
   <div class="d-flex align-items-center">
      <!-- Toggle Button -->
      <button class="btn btn-outline-primary btn-sm ms-2 toggle-btn" id="toggleButton" type="button" data-bs-toggle="collapse" 
         data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
      <i class="fa fa-search-plus"></i>
      </button>
      <button type="button" class="btn btn-primary mx-2" data-bs-toggle="modal" data-bs-target="#addCourseModal">
         <i class="feather icon-plus"></i> Add New Course
         </button>
         <!-- Add Quiz Button -->
         <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addQuizModal">
         <i class="feather icon-plus"></i> Add New Quiz
         </button>
   </div>
</div>
<div class="collapse" id="collapseExample">
   <div class="search-filter-container card card-body">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
         <!-- Search Box with Icon on the Left -->
         <div class="search-container d-flex align-items-center mb-3 mb-md-0">
            <div class="search-icon-container">
               <i class="fa fa-search search-icon"></i>
            </div>
            <input type="search" class="form-control search-input" id="searchBox" placeholder="Search..." />
         </div>
        
      </div>
   </div>
</div>


<!-- Quizzes Table -->
<div class="row">
   <div class="col-lg-12">
      <div class="card m-b-30 table-card">
         <div class="card-body table-container">
            <div class="table-responsive">
               <table id="default-datatable" class="display table table-bordered">
                  <thead>
                     <tr>
                        <th>Quiz Title</th>
                        <th>Course Name</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Session</th>
                        <th>Students</th>
                        <th>Created At</th>
                        <th>Actions</th>
                     </tr>
                  </thead>
                  <tbody>
                     @forelse($quizzes as $quiz)
                     <tr id="quiz-row-{{ $quiz->id }}">
                        <td>{{ $quiz->name }}</td>
                        <td>{{ $quiz->course->name }} ({{$quiz->course->code}})</td>
                        <td>{{ $examDuration }} minutes</td>
                        <td>
                           @if($quiz->status == 'pending')
                              <span class="badge bg-warning text-dark">Pending</span>
                           @elseif($quiz->status == 'rejected')
                              <span class="badge bg-danger">Rejected</span>
                           @elseif($quiz->status == 'accepted')
                              <span class="badge bg-success">Accepted</span>
                           @endif
                        </td>
                        <td>
                           @if($quiz->slots->isNotEmpty() && $quiz->slots->first()->session)
                              <div>
                                 <strong>Date:</strong> {{ \Carbon\Carbon::parse($quiz->slots->first()->session->date)->format('l, F j, Y') }}<br>
                                 <strong>Time:</strong> {{ \Carbon\Carbon::parse($quiz->slots->first()->session->start_time)->format('g:i A') }} 
                                 to {{ \Carbon\Carbon::parse($quiz->slots->first()->session->end_time)->format('g:i A') }}
                              </div>
                           @else
                              <span class="text-muted">Not Reserved Yet</span>
                           @endif
                        </td>
                        <td>{{ $quiz->students->count() }}</td>
                        <td>{{ $quiz->created_at->format('d-m-Y H:i') }}</td>
                        <td>
                           <!-- Delete Quiz Button -->
                           <button type="button" class="btn btn-round btn-danger delete-quiz-btn" 
                              title="Delete Quiz" data-quiz-id="{{ $quiz->id }}">
                              <i class="feather icon-trash-2"></i>
                           </button>
                           <!-- Export Quiz Button - Show only if status is accepted -->
                           @if($quiz->status == 'accepted')
                           <button type="button" class="btn btn-round btn-info export-quiz-btn" 
                              title="Export Quiz" data-quiz-id="{{ $quiz->id }}">
                              <i class="feather icon-download"></i>
                           </button>
                           @endif
                        </td>
                     </tr>
                     @empty
                     <tr>
                        <td colspan="8" class="text-center">No quizzes available</td>
                     </tr>
                     @endforelse
                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </div>
</div>

  <!-- Add Quiz Modal -->
<div class="modal fade" id="addQuizModal" tabindex="-1" role="dialog" aria-labelledby="addQuizModalLabel" aria-hidden="true">
   <div class="modal-dialog" role="document">
      <form id="addQuizForm" method="POST" enctype="multipart/form-data">
         @csrf
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="addQuizModalLabel">Add New Quiz</h5>
               <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
               <!-- Faculty Selection Section -->
               <div class="form-group">
                  <label for="faculty">Select Faculty</label>
                  <select class="form-control" id="faculty" name="faculty" required
                      @if(auth()->user() && auth()->user()->hasRole('faculty')) disabled @endif>
                     @if(auth()->user() && auth()->user()->hasRole('faculty'))
                         <!-- If the user is a faculty, preselect their faculty and make it disabled -->
                         <option value="{{ auth()->user()->faculty_id }}" selected>
                             {{ auth()->user()->faculty->name }}
                         </option>
                     @else
                         <option value="" disabled selected>Select Faculty</option>
                         @foreach($faculties as $faculty)
                             <option value="{{ $faculty->id }}">{{ $faculty->name }}</option>
                         @endforeach
                     @endif
                  </select>
               </div>
               
               <!-- Course Selection (dynamically populated) -->
               <div class="form-group">
                  <label for="course_id">Select Course</label>
                  <select class="form-control" id="course_id" name="course_id" required disabled>
                     <option value="">Select Course</option>
                  </select>
               </div>
               
               <!-- Quiz Details Section -->
               <div class="form-group">
                  <label for="title">Quiz Title</label>
                  <input type="text" class="form-control" name="title" required>
               </div>
               <div class="form-group">
                  <label for="students_file">Upload Students (Excel)</label>
                  <input type="file" class="form-control" name="students_file" accept=".xlsx, .xls" required>
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


<!-- Add Course Modal -->
<div class="modal fade" id="addCourseModal" tabindex="-1" role="dialog" aria-labelledby="addCourseModalLabel" aria-hidden="true">
   <div class="modal-dialog" role="document">
      <form id="addCourseForm" method="POST" enctype="multipart/form-data">
         @csrf
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="addCourseModalLabel">Add New Course</h5>
               <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
               <!-- Faculty Selection Section -->
               <div class="form-group">
                  <label for="faculty">Select Faculty</label>
                  <select class="form-control" id="faculty" name="faculty" required 
                      @if(auth()->user() && auth()->user()->hasRole('faculty')) disabled @endif>
                     @if(auth()->user() && auth()->user()->hasRole('faculty'))
                         <!-- If the user is a faculty, preselect their faculty and make it disabled -->
                         <option value="{{ auth()->user()->faculty_id }}" selected>
                             {{ auth()->user()->faculty->name }}
                         </option>
                     @else
                         <option value="" disabled selected>Select Faculty</option>
                         @foreach($faculties as $faculty)
                             <option value="{{ $faculty->id }}">{{ $faculty->name }}</option>
                         @endforeach
                     @endif
                  </select>
               </div>
               
               <!-- Course Details Section -->
               <div class="form-group">
                  <label for="course_code">Course Code</label>
                  <input type="text" class="form-control" name="course_code" id="course_code" required>
               </div>
               <div class="form-group">
                  <label for="course_name">Course Name</label>
                  <input type="text" class="form-control" name="course_name" id="course_name" required>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
               <button type="submit" class="btn btn-primary">Save Course</button>
            </div>
         </div>
      </form>
   </div>
</div>

@endsection
@section('scripts')
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
   $(document).ready(function () {
    let table; // Declare table in the broader scope

    // Initialize DataTables if the table exists
    if ($('#default-datatable').length) {
        table = $('#default-datatable').DataTable();
    }

    // Optional: Trigger search when the user types in the search box
    $('#searchBox').on('keyup', function () {
        if (table) { // Ensure table is initialized before calling draw
            table.search(this.value).draw();
        }
    });
});

   
   // Function to show the loading overlay
   function showLoading() {
       document.getElementById('loadingOverlay').style.display = 'flex'; // Show the loading overlay
       // Disable the modal and background content
       $('body').css('pointer-events', 'none');
   }
   
   
   // Function to hide the loading overlay
   function hideLoading() {
       document.getElementById('loadingOverlay').style.display = 'none'; 
       $('body').css('pointer-events', 'auto'); 
   }
   
   $(document).on('click', '.export-quiz-btn', function() {
   const quizId = $(this).data('quiz-id');
   const quizBtn = $(this);
   
   // Construct the URL without extra slashes
   const exportUrl = "{{ route('admin.quizzes.export', ':id') }}".replace(':id', quizId);
   
   // Disable the button
   quizBtn.prop('disabled', true);
   
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
       complete: function() {
           // Re-enable the button after the request completes
           quizBtn.prop('disabled', false);
       }
   });
});

   
   // Add Quiz Form Submission via AJAX
   $('#addQuizForm').on('submit', function(e) {
       e.preventDefault();
       const formData = new FormData(this);
       const submitButton = $('#addQuizForm button[type="submit"]');
       
       submitButton.addClass('loading').prop('disabled', true);
       showLoading();
   
       $.ajax({
           url: "{{ route('admin.quizzes.store') }}",
           type: 'POST',
           headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
           data: formData,
           processData: false,
           contentType: false,
           success: function(response) {
             


                swal('Success!', 'The quiz has been added.', 'success')
                .then(() => {
                    $('#addQuizModal').modal('hide');
                    location.reload(); // Reload page to see new slots

                });
           },
           error: function(xhr) {
               submitButton.removeClass('loading').prop('disabled', false);
               let errorMessage = 'Failed to add the quiz.';
               if (xhr.responseJSON && xhr.responseJSON.message) {
                   errorMessage = xhr.responseJSON.message;
               }
               swal('Error!', errorMessage, 'error');
           },complete: function() {
               hideLoading();
           }
       });
   });
   
   // Add Course Form Submission via AJAX
   $('#addCourseForm').on('submit', function(e) {
       e.preventDefault();
       const formData = new FormData(this);
       const submitButton = $('#addCourseForm button[type="submit"]');
       
       submitButton.addClass('loading').prop('disabled', true);
       showLoading();
   
       $.ajax({
           url: "{{ route('courses.store') }}",  // Ensure this route is defined in your routes file
           type: 'POST',
           headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
           data: formData,
           processData: false,
           contentType: false,
           success: function(response) {

                swal('Success!', 'The course has been added.', 'success')
                .then(() => {
                    $('#addCourseModal').modal('hide');
                    location.reload(); // Reload page to see new slots

                });


              
           },
           error: function(xhr) {
               submitButton.removeClass('loading').prop('disabled', false);
               let errorMessage = 'Failed to add the course.';
               if (xhr.responseJSON && xhr.responseJSON.message) {
                   errorMessage = xhr.responseJSON.message;
               }
               swal('Error!', errorMessage, 'error');
           },complete: function() {
               hideLoading();
           }
       });
   });


 

   var facultyId = $('#faculty').val();  // Get the currently selected faculty (if any)

// If the faculty is already selected (e.g., the user is a faculty member and the faculty dropdown is pre-selected)
if (facultyId) {
    loadCourses(facultyId);  // Load courses for the pre-selected faculty
}

// Listen for changes in faculty dropdown (if enabled)
$('#faculty').on('change', function() {
    var facultyId = $(this).val();
    var courseDropdown = $('#course_id');

    // Clear previous options
    courseDropdown.html('<option value="">Select Course</option>');

    if (facultyId) {
        // Enable course dropdown if faculty is selected
        courseDropdown.prop('disabled', false);

        // Fetch courses for the selected faculty
        loadCourses(facultyId);
    } else {
        // Disable course dropdown if no faculty is selected
        courseDropdown.prop('disabled', true);
    }
});

// Function to load courses based on faculty ID
function loadCourses(facultyId) {
    var courseDropdown = $('#course_id');

    // Make AJAX call to get courses for the selected faculty
    var url = @json(route('get.courses.by.faculty', ['facultyId' => 'FAKE_ID'])).replace('FAKE_ID', facultyId);

    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        success: function(courses) {
            if (courses.length > 0) {
                $.each(courses, function(index, course) {
                    courseDropdown.append('<option value="' + course.id + '">' + course.name + ' (' + course.code + ')' + '</option>');
                });
                courseDropdown.prop('disabled', false);

            } else {
                courseDropdown.html('<option value="" disabled>No courses available for this faculty</option>');
            }
        },
        error: function() {
            console.error('Error fetching courses');
            courseDropdown.html('<option value="" disabled>Error loading courses</option>');
        }
    });
}

   
   // Delete Quiz with Confirmation
   $(document).on('click', '.delete-quiz-btn', function() {
       const quizId = $(this).data('quiz-id');
   
       swal({
           title: 'Are you sure?',
           text: "This will delete the quiz permanently!",
           icon: 'warning',
           buttons: true,
           dangerMode: true
       }).then((willDelete) => {
               showLoading();
   
               $.ajax({
                   url: "{{ route('admin.quizzes.destroy', '') }}/" + quizId,
                   type: 'DELETE',
                   data: { _token: "{{ csrf_token() }}" },
                   success: function(response) {
                       $('#quiz-row-' + quizId).remove();
                       swal('Deleted!', 'The quiz has been deleted.', 'success');
                   },
                   error: function(xhr) {
                       let errorMessage = 'Failed to delete the quiz.';
                       if (xhr.responseJSON && xhr.responseJSON.message) {
                           errorMessage = xhr.responseJSON.message;
                       }
                       swal('Error!', errorMessage, 'error');
                   },complete: function() {
               hideLoading();
           }
               });
           
       });
   });
</script>
@endsection