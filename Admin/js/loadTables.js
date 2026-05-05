$(document).ready(function() {
    if (!$.fn.DataTable.isDataTable('#suppliesTable')) {
        $('#suppliesTable').DataTable({
            "ajax": `${BASE_URL}/Admin/processes/supplies/load_supplies.php`,
            "pageLength": 10,
            "lengthChange": false,
            "ordering": true,
            "searching": true,
            "columns": [
                { "title": "ID" },
                { "title": "Supply Name" },
                { "title": "Quantity" },
                { "title": "Reorder Level" },
                { "title": "Status" },
                { "title": "Action", "orderable": false }
            ],
            "order": [[4, "desc"]],
            "language": {
                search: "",
                searchPlaceholder: "Search"
            },
            "initComplete": function() {
                const $searchInput = $('#suppliesTable_filter input[type=search]');
                $searchInput.attr('id', 'suppliesSearch').attr('name', 'suppliesSearch');
                $('#suppliesTable_filter label').attr('for', 'suppliesSearch');

                $('#suppliesTable_filter').append(
                    '<button id="insertSupplyBtn">+ Add</button>'
                );
            }
        });
    }
    
    if (!$.fn.DataTable.isDataTable('#servicesTable')) {
        $('#servicesTable').DataTable({
            "ajax": `${BASE_URL}/Admin/processes/services/load_services.php`,
            "pageLength": 10,
            "lengthChange": false,
            "ordering": true,
            "searching": true,
            "columns": [
                { "title": "ID" },
                { "title": "Service Name" },
                { "title": "Price (₱)" },
                { "title": "Duration (mins)" },
                { "title": "Status" },
                { "title": "Action", "orderable": false }
            ],
            "order": [[4, "desc"]],
            "language": {
                search: "",
                searchPlaceholder: "Search"
            },
            "initComplete": function() {
                const $searchInput = $('#servicesTable_filter input[type=search]');
                $searchInput.attr('id', 'servicesSearch').attr('name', 'servicesSearch');
                $('#servicesTable_filter label').attr('for', 'servicesSearch');
            }
        });
    }

    if (!$.fn.DataTable.isDataTable('#promosTable')) {
        $('#promosTable').DataTable({
            "ajax": `${BASE_URL}/Admin/processes/promos/load_promos.php`,
            "pageLength": 10,
            "lengthChange": false,
            "ordering": true,
            "searching": true,
            "columns": [
                { "title": "ID" },
                { "title": "Promo Name" },
                { "title": "Discount" },
                { "title": "Validity" },
                { "title": "Status" },
                { "title": "Action", "orderable": false }
            ],
            "order": [[4, "desc"]],
            "language": {
                search: "",
                searchPlaceholder: "Search"
            },
            "initComplete": function() {
                const $searchInput = $('#promosTable_filter input[type=search]');
                $searchInput.attr('id', 'promosSearch').attr('name', 'promosSearch');
                $('#promosTable_filter label').attr('for', 'promosSearch');
            }
        });
    }
});
