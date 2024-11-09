$(document).ready(function() {
    // Initialize DataTable
    const table = $('#default-datatable').DataTable({
        "order": [[0, "asc"]],
        responsive: true
    });

    // Reference to the toggle button and icon
    const toggleButton = document.getElementById("toggleButton");
    const icon = toggleButton.querySelector("i");

    // Toggle icon on collapse show
    document.getElementById("collapseExample").addEventListener("shown.bs.collapse", function() {
        icon.classList.remove("fa-search-plus");
        icon.classList.add("fa-search-minus");
    });

    // Toggle icon on collapse hide
    document.getElementById("collapseExample").addEventListener("hidden.bs.collapse", function() {
        icon.classList.remove("fa-search-minus");
        icon.classList.add("fa-search-plus");
    });

    // Search functionality for DataTable using custom search box
    $('#searchBox').on('keyup', function() {
        table.search(this.value).draw(); // Use the DataTable API to search
    });

    
});
