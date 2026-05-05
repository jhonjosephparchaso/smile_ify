$(document).ready(function() {
    if (!$.fn.DataTable.isDataTable('#inactiveTable')) {
        $('#inactiveTable').DataTable({
            "ajax": `${BASE_URL}/Admin/processes/patients/load_inactive_patients.php`,
            "pageLength": 10,
            "lengthChange": false,
            "ordering": true,
            "searching": true,
            "columns": [
                { "title": "Patient ID" },
                { "title": "Name" },
                { "title": "Recent Transaction" },
                { "title": "Branch Registered" },
                { "title": "Action", "orderable": false, "searchable": false }
            ],
            "order": [[2, "desc"]],
            "language": {
                search: "",
                searchPlaceholder: "Search"
            },
            "initComplete": function() {
                const $searchInput = $('#inactiveTable_filter input[type=search]');
                $searchInput.attr('id', 'inactiveSearch').attr('name', 'inactiveSearch');
                $('#inactiveTable_filter label').attr('for', 'inactiveSearch');
            }
        });
    }
});
