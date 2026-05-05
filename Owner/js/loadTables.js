$(document).ready(function() {
    if (!$.fn.DataTable.isDataTable('#servicesTable')) {
        $('#servicesTable').DataTable({
            "ajax": `${BASE_URL}/Owner/processes/services/load_services.php`,
            "pageLength": 10,
            "lengthChange": false,
            "ordering": true,
            "searching": true,
            "columns": [
                { "title": "Service Name" },
                { "title": "Branch" },
                { "title": "Price (₱)" },
                { "title": "Duration (mins)" },
                { "title": "Action", "orderable": false }
            ],
            "order": [[1, "asc"]],
            "language": {
                search: "",
                searchPlaceholder: "Search"
            },
            "initComplete": function() {
                const $searchInput = $('#servicesTable_filter input[type=search]');
                $searchInput.attr('id', 'servicesSearch').attr('name', 'servicesSearch');
                $('#servicesTable_filter label').attr('for', 'servicesSearch');

                $('#servicesTable_filter').append(
                    '<button id="insertServiceBtn">+ Add</button>'
                );
            }
        });
    }

    if (!$.fn.DataTable.isDataTable('#promosTable')) {
        $('#promosTable').DataTable({
            "ajax": `${BASE_URL}/Owner/processes/promos/load_promos.php`,
            "pageLength": 10,
            "lengthChange": false,
            "ordering": true,
            "searching": true,
            "columns": [
                { "title": "Promo Name" },
                { "title": "Branch" },
                { "title": "Discount" },
                { "title": "Validity" },
                { "title": "Action", "orderable": false }
            ],
            "order": [[1, "asc"]],
            "language": {
                search: "",
                searchPlaceholder: "Search"
            },
            "initComplete": function() {
                const $searchInput = $('#promosTable_filter input[type=search]');
                $searchInput.attr('id', 'promosSearch').attr('name', 'promosSearch');
                $('#promosTable_filter label').attr('for', 'promosSearch');

                $('#promosTable_filter').append(
                    '<button id="insertPromoBtn">+ Add</button>'
                );
            }
        });
    }
});
