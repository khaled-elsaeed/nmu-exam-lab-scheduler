
$(document).ready(function () {

    let specificLabs = [];

    $('#addSpecificLabBtn').on('click', function() {
        // Get the array of selected lab IDs
        let selectedLabs = $('#specificLabSelect').val();
        
        // Ensure selectedLabs is an array
        if (!Array.isArray(selectedLabs)) {
            selectedLabs = [selectedLabs];
        }
        
        // Add unique labs
        selectedLabs.forEach(function(labId) {
            if (!specificLabs.includes(labId)) {
                specificLabs.push(labId);
            }
        });
        
        // Update the display of selected labs
        updateSelectedLabsDisplay();
        
        console.log(specificLabs); // Log the array for debugging
    });
    
    // Function to update the display list of selected labs
    function updateSelectedLabsDisplay() {
        const labsList = $('#labsDisplayList');
        labsList.empty(); // Clear the existing list
    
        // Loop through the specificLabs array and display each selected lab
        specificLabs.forEach(function(labId) {
            const labOption = $('#specificLabSelect option[value="' + labId + '"]');
            const labText = labOption.text(); // Get the text of the selected lab
            labsList.append(
                '<li class="lab-item d-inline-block mx-1" data-lab-id="' + labId + '">' + 
                labText + 
                ' <button type="button" class="btn btn-round btn-outline-danger removeLabBtn"><i class="feather icon-trash"></i></button></li>'
            );
        });
    }
    
    
    $('#labsDisplayList').on('click', '.removeLabBtn', function() {
        const labId = $(this).closest('.lab-item').data('lab-id');
        
        // Remove the labId from the specificLabs array
        specificLabs = specificLabs.filter(function(id) {
            return id != labId; 
        });
        
        updateSelectedLabsDisplay();
        
        console.log(specificLabs); 
    });
    


    // Utility Functions
    function showLoading() {
        $('#loadingOverlay').fadeIn('fast');
        $('body').css('pointer-events', 'none');
    }

    function hideLoading() {
        $('#loadingOverlay').fadeOut('fast');
        $('body').css('pointer-events', 'auto');
    }


    // Sortable for Building Order
    new Sortable(document.getElementById('buildingOrder'), {
        animation: 150,
        ghostClass: 'bg-light'
    });

// Reservation Type Toggle
    $('input[name="reserve_type"]').on('change', function() {
        const isAutomatic = $(this).val() === 'automatic';
        
        if (isAutomatic) {
            $('#automaticAssignmentSection').removeClass('d-none');
            $('#specificLabsSection').addClass('d-none');
        } else {
            $('#specificLabsSection').removeClass('d-none');
            $('#automaticAssignmentSection').addClass('d-none');
        }
    });


    // Open Reservation Modal
    $('.btn-reserve').on('click', function() {
        const sessionId = $(this).data('session-id');
        $('#sessionId').val(sessionId);
        $('#reserveQuizSessionModal').modal('show');
    });

    // Faculty Dropdown Handling
    $('#faculty').on('change', function() {
        const facultyId = $(this).val();
        const $courseDropdown = $('#course');
        
        $courseDropdown.prop('disabled', true).html('<option>Loading...</option>');

        $.ajax({
            url: window.routes.getCoursesByFaculty.replace(':facultyId', facultyId),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: 'GET',
            beforeSend: showLoading,
            success: function(courses) {
                $courseDropdown.html('<option value="">Select Course</option>');
                courses.forEach(course => {
                    $courseDropdown.append(`
                        <option value="${course.id}">
                            ${course.name} (${course.code})
                        </option>
                    `);
                    $courseDropdown.prop('disabled', false);
                });
            },
            error: function() {
                $courseDropdown.html('<option>Error loading courses</option>');
            },
            complete: hideLoading
        });
    });

    // Course Dropdown Handling
    $('#course').on('change', function() {
        const courseId = $(this).val();
        const $quizDropdown = $('#quiz');
        
        $quizDropdown.prop('disabled', true).html('<option>Loading...</option>');

        $.ajax({
            url: window.routes.getQuizzesByCourse.replace(':courseId', courseId),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: 'GET',
            beforeSend: showLoading,
            success: function(response) {
                $quizDropdown.html('<option value="">Select Quiz</option>');
                response.quizzes.forEach(quiz => {
                    $quizDropdown.append(`
                        <option value="${quiz.id}">
                            ${quiz.name}
                        </option>
                    `);
                    $quizDropdown.prop('disabled', false);
                });
            },
            error: function() {
                $quizDropdown.html('<option>Error loading quizzes</option>');
            },
            complete: hideLoading
        });
    });

    $('#quiz').on('change', function() {
        const quizId = $(this).val();
        const $studentCountInput = $('#student_counts'); // Ensure this is the correct input for the student count
    
        $.ajax({
            url: window.routes.getQuizStudentCounts.replace(':quizId', quizId), // Use your correct route
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: 'GET',
            beforeSend: showLoading,
            success: function(response) {
                // Assuming the response contains a 'quiz_student_count' key with the count
                if (response.quiz_student_count !== undefined) {
                    // Set the student count in the input
                    $studentCountInput.val(response.quiz_student_count);
                } else {
                    // If the count is not available for some reason
                    $studentCountInput.val('0');
                }
            },
            error: function() {
                // Handle error gracefully
                $studentCountInput.val('Error loading student count');
            },
            complete: hideLoading
        });
    });
    

    // Form Submission
    $('#reserveQuizSessionForm').on('submit', function(e) {
        e.preventDefault();

        if (!$('#specificLabsSection').hasClass('d-none')) {
            if (specificLabs.length === 0) {
                swal({
                    type: 'error',
                    title: 'No Labs Selected',
                    text: 'You must select at least one lab to proceed. Please make a selection and try again.',
                    confirmButtonText: 'Got It'
                });
                
                return; // Ensure to stop further execution if this condition fails
            }
        }
        


        const formData = new FormData(this);
        const buildingOrder = Array.from($('#buildingOrder li')).map(li => li.dataset.id);
        buildingOrder.forEach(id => formData.append('building_order[]', id));
        specificLabs.forEach(id => formData.append('specific_labs[]',id));

        $.ajax({
            url: window.routes.reserveForQuiz,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: showLoading,
            success: function(response) {
                swal({
                    type: 'success',
                    title: 'Reservation Successful',
                    text: 'Your quiz session has been reserved.',
                    confirmButtonText: 'OK'
                }).then(() => location.reload());
            },
            error: function(xhr) {
                let errorMessage = 'An unexpected error occurred.';
                
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }

                swal({
                    type: 'error',
                    title: 'Reservation Failed',
                    text: errorMessage,
                    confirmButtonText: 'Try Again'
                });
            },
            complete: hideLoading
        });
    });

    // Reset modal on close
    $('#reserveQuizSessionModal').on('hidden.bs.modal', function () {
        // Reset form
        $(this).find('form')[0].reset();
        
        // Disable dependent dropdowns
        $('#course, #quiz').prop('disabled', true).html('<option>Select Course</option>');
        
        // Reset reservation type
        $('#automaticReserve').prop('checked', true);
        $('#automaticAssignmentSection').show();
        $('#specificLabsSection').hide();
    });
});
