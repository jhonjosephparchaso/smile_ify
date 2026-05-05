$(document).ready(function() {
    if (window.IS_DEPENDENT_PAGE) return;
    $('#transactionTable').DataTable({
        "ajax": `${BASE_URL}/Patient/processes/profile/load_dental_transactions.php`,
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
            { "title": "Amount" , "searchable": false },
            { "title": "Action", "orderable": false },
            { "title": "Created", "visible": false, "searchable": false }
        ],
        "language": {
            search: "",
            searchPlaceholder: "Search"
        },
        "initComplete": function() {
            const $searchInput = $('#transactionTable_filter input[type=search]');
            $searchInput
                .attr('id', 'dentalTransactionSearch')
                .attr('name', 'dentalTransactionSearch');
            $('#transactionTable_filter label').attr('for', 'dentalTransactionSearch');
        }
    });
});
