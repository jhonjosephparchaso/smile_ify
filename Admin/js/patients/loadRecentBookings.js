$(document).ready(function() {
    if (!$.fn.DataTable.isDataTable('#recentTable')) {
        $('#recentTable').DataTable({
            "ajax": `${BASE_URL}/Admin/processes/patients/load_recent_bookings.php`,
            "pageLength": 10,
            "lengthChange": false,
            "ordering": true,
            "searching": true,
            "columns": [
                { "title": "Appointment ID" },
                { "title": "Patient" },
                { "title": "Dentist" },
                { "title": "Date" },
                { "title": "Time" },
                { "title": "Created", "visible": false }, 
                { "title": "Action", "orderable": false, "searchable": false }
            ],
            "order": [[3, "asc"], [4, "asc"]],
            "language": {
                search: "",
                searchPlaceholder: "Search"
            },
            "initComplete": function() {
                const $searchInput = $('#recentTable_filter input[type=search]');
                $searchInput.attr('id', 'recentSearch').attr('name', 'recentSearch');
                $('#recentTable_filter label').attr('for', 'recentSearch');
            }
        });
    }
});
