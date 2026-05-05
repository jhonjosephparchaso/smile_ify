$(document).ready(function() { 
    if (!$.fn.DataTable.isDataTable('#dentistsTable')) {
        $('#dentistsTable').DataTable({
            "ajax": `${BASE_URL}/Owner/processes/employees/load_dentists.php`,
            "pageLength": 10,
            "lengthChange": false,
            "ordering": true,
            "searching": true,
            "columns": [
                { "title": "ID" },
                { "title": "Name" },
                { "title": "Branch" },
                { "title": "Status" },
                { "title": "Action", "orderable": false, "searchable": false }
            ],
            "order": [[3, "desc"],[0, "asc"]],
            "language": {
                search: "",
                searchPlaceholder: "Search"
            },
            "initComplete": function() {
                const $searchInput = $('#dentistsTable_filter input[type=search]');
                $searchInput.attr('id', 'dentistSearch').attr('name', 'dentistSearch');
                $('#dentistsTable_filter label').attr('for', 'dentistSearch');

                $('#dentistsTable_filter').append(
                    '<button id="addDentistBtn">+ Add</button>'
                );
            }
        });
    }
});