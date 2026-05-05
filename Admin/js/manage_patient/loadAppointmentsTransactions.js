$(document).ready(function () {
    if (typeof userId !== "undefined" && userId !== "") {
        $('#appointmentTable').DataTable({
            "ajax": `${BASE_URL}/Admin/processes/manage_patient/load_appointments.php?id=${userId}`,
            "pageLength": 10,
            "lengthChange": false,
            "ordering": true,
            "searching": true,
            "columns": [
                { "title": "ID" },
                { "title": "Dentist" },
                { "title": "Service" },
                { "title": "Date" },
                { "title": "Time" },
                { "title": "Status" },
                { "title": "Action", "orderable": false },
                { "title": "Created", "visible": false, "searchable": false }
            ],
            "order": [[3, "desc"], [4, "asc"]],
            "language": {
                search: "",
                searchPlaceholder: "Search"
            },
            "initComplete": function () {
                const $searchInput = $('#appointmentTable_filter input[type=search]');
                $searchInput
                    .attr('id', 'appointmentSearch')
                    .attr('name', 'appointmentSearch');
                $('#appointmentTable_filter label').attr('for', 'appointmentSearch');

                $('#appointmentTable_filter').append(
                    '<button id="insertAppointmentBtn">+ Add</button>'
                );
            }
        });

        $('#transactionTable').DataTable({
            "ajax": `${BASE_URL}/Admin/processes/manage_patient/load_dental_transactions.php?id=${userId}`,
            "pageLength": 10,
            "lengthChange": false,
            "ordering": true,
            "searching": true,
            "columns": [
                { "title": "ID" },
                { "title": "Dentist" },
                { "title": "Service" },
                { "title": "Date" },
                { "title": "Time" },
                { "title": "Total" },
                { "title": "Action", "orderable": false },
                { "title": "Created", "visible": false, "searchable": false }
            ],
            "order": [[3, "desc"], [4, "asc"]],
            "language": {
                search: "",
                searchPlaceholder: "Search"
            },
            "initComplete": function () {
                const $searchInput = $('#transactionTable_filter input[type=search]');
                $searchInput
                    .attr('id', 'dentalTransactionSearch')
                    .attr('name', 'dentalTransactionSearch');
                $('#transactionTable_filter label').attr('for', 'dentalTransactionSearch');
            }
        });
    }
});
