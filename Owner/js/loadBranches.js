$(document).ready(function() {
    if (!$.fn.DataTable.isDataTable('#branchesTable')) {
        $('#branchesTable').DataTable({
            "ajax": `${BASE_URL}/Owner/processes/profile/branches/load_branches.php`,
            "pageLength": 5,
            "lengthChange": false,
            "ordering": true,
            "searching": true,
            "columns": [
                { "title": "ID" },
                { "title": "Branch Name" },
                { "title": "Dental Chairs" },
                { "title": "Phone Number" },
                { "title": "Status" },
                { "title": "Action", "orderable": false }
            ],
            "order": [[4, "desc"]],
            "language": {
                search: "",
                searchPlaceholder: "Search"
            },
            "initComplete": function() {
                const $searchInput = $('#branchesTable_filter input[type=search]');
                $searchInput.attr('id', 'branchesSearch').attr('name', 'branchesSearch');
                $('#branchesTable_filter label').attr('for', 'branchesSearch');

                $('#branchesTable_filter').append(
                    '<button id="insertBranchBtn">+ Add</button>'
                );
            }
        });
    }
});
