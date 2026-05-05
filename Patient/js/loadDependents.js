$(document).ready(function() {
    $('#dependentTable').DataTable({
        "ajax": `${BASE_URL}/Patient/processes/profile/dependent_accounts/load_dependent_accounts.php`,
        "pageLength": 20,
        "lengthChange": false,
        "ordering": true,
        "searching": true,
        "order": [2, "desc"],
        "columns": [
            { "title": "Name"},
            { "title": "Relationship"},
            { "title": "Recent Transaction" },
            { "title": "Status"},
            { "title": "Action", "orderable": false },
            { "title": "Created", "visible": false, "searchable": false }
        ],
        "language": {
            search: "",
            searchPlaceholder: "Search"
        },
        "initComplete": function() {
            const $searchInput = $('#dependentTable_filter input[type=search]');
            $searchInput
                .attr('id', 'dependentTableSearch')
                .attr('name', 'dependentTableSearch');
            $('#dependentTable_filter label').attr('for', 'dependentTableSearch');
        }
    });
});
