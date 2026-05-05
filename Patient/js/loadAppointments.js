$(document).ready(function() {
    if (window.IS_DEPENDENT_PAGE) return;
    $('#appointmentTable').DataTable({
        "ajax": `${BASE_URL}/Patient/processes/profile/load_appointments.php`,
        "pageLength": 20,
        "lengthChange": false,
        "ordering": true,
        "searching": true,
        "order": [[3, "desc"], [4, "asc"]],
        "columns": [
            { "title": "Dentist" , "searchable": false },
            { "title": "Branch" },
            { "title": "Service" },
            { "title": "Date" , "searchable": false },
            { "title": "Time" , "searchable": false },
            { "title": "Status" , "searchable": false },
            { "title": "Action", "orderable": false },
            { "title": "Created", "visible": false, "searchable": false }
        ],
        "language": {
            search: "",
            searchPlaceholder: "Search"
        },
        "initComplete": function() {
            const $searchInput = $('#appointmentTable_filter input[type=search]');
            $searchInput
                .attr('id', 'appointmentSearch')
                .attr('name', 'appointmentSearch');
            $('#appointmentTable_filter label').attr('for', 'appointmentSearch');
        }
    });
});
