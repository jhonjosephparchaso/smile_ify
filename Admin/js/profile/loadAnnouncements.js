$(document).ready(function() {
    if (!$.fn.DataTable.isDataTable('#announcementsTable')) {
        $('#announcementsTable').DataTable({
            ajax: `${BASE_URL}/Admin/processes/profile/announcements/load_announcements.php`,
            pageLength: 5,
            lengthChange: false,
            ordering: true,
            searching: true,
            columns: [
                { title: "ID" },
                { title: "Title" },
                { title: "Type" },
                { title: "Start Date" },
                { title: "End Date" },
                { title: "Status" },
                { title: "Action", orderable: false }
            ],
            order: [[0, "asc"],[5, "asc"]],
            language: {
                search: "",
                searchPlaceholder: "Search"
            },
            initComplete: function() {
                const $searchInput = $('#announcementsTable_filter input[type=search]');
                $searchInput.attr('id', 'announcementSearch').attr('name', 'announcementSearch');
                $('#announcementsTable_filter label').attr('for', 'announcementSearch');

                $('#announcementsTable_filter').append(
                    '<button id="insertAnnouncementBtn">+ Add</button>'
                );
            }
        });
    }
});
