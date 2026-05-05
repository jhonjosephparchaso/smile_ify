document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const branchInput = document.getElementById('branchIdInput');
    const legend = document.getElementById('calendarLegend');
    let currentBranchId = branchInput?.value || null;
    let calendar;

    function initCalendar(branchId) {
        if (!calendarEl) return;

        if (calendar) {
            calendar.destroy();
        }

        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'customPrev,customNext today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            customButtons: {
                customPrev: { text: '<', click: () => calendar.prev() },
                customNext: { text: '>', click: () => calendar.next() }
            },
            height: 650,
            events: `${BASE_URL}/Admin/processes/calendar/load_calendar.php?branch_id=${branchId}`,
            eventOrder: "branch",

            eventDidMount: function(info) {
                if (info.event.extendedProps.branchColor) {
                    let color = info.event.extendedProps.branchColor;
                    info.el.style.backgroundColor = color + "20";
                    info.el.style.borderLeft = "6px solid " + color;
                    info.el.style.borderRadius = "4px";
                    info.el.style.padding = "2px 4px";
                    let timeEl = info.el.querySelector(".fc-event-time");
                    if (timeEl) timeEl.style.color = "#000";
                }
            },

            eventClick: function(info) {
                const appointment = info.event.extendedProps;
                const modalBody = document.getElementById('modalBody');
                modalBody.innerHTML = `
                    <h2>Appointment Details</h2>
                    <p><strong>Patient:</strong> <span>${appointment.patient}</span></p>
                    <p><strong>Dentist:</strong> <span>${appointment.dentist ? "Dr. " + appointment.dentist : "Available Dentist"}</span></p>
                    <p><strong>Branch:</strong> <span>${appointment.branch}</span></p>
                    <p><strong>Service:</strong> <span>${appointment.services}</span></p>
                    <p><strong>Date:</strong> <span>${
                        info.event.start
                            ? info.event.start.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
                            : '-'
                    }</span></p>
                    <p><strong>Time:</strong> <span>${
                        info.event.start
                            ? info.event.start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true })
                            : '-'
                    }</span></p>
                    <p><strong>Notes:</strong> <span>${appointment.notes || '-'}</span></p>
                    <p><strong>Status:</strong> <span>${appointment.status}</span></p>
                    <p><strong>Date Booked:</strong> <span>${
                        appointment.date_created
                            ? new Date(appointment.date_created).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
                            : '-'
                    }</span></p>
                `;
                document.getElementById('appointmentModalDetails').style.display = "block";
            }
        });

        calendar.render();

        calendar.on('eventsSet', function(events) {
            legend.innerHTML = '';
            const branches = {};
            events.forEach(e => {
                if (e.extendedProps.branch && e.extendedProps.branchColor) {
                    branches[e.extendedProps.branch] = e.extendedProps.branchColor;
                }
            });
            Object.keys(branches).forEach(branch => {
                const item = document.createElement("div");
                item.classList.add("legend-item");
                item.innerHTML = `
                    <span class="legend-color" style="background:${branches[branch]}"></span>
                    ${branch}
                `;
                legend.appendChild(item);
            });

            const statusColors = {
                "Booked": "#fe9705",
                "Pending Reschedule": "#0066ff",
                "Completed": "#3ac430",
                "Cancelled": "#d11313"
            };
            Object.keys(statusColors).forEach(status => {
                const item = document.createElement("div");
                item.classList.add("legend-item");
                item.innerHTML = `
                    <span class="legend-color" style="background:${statusColors[status]}"></span>
                    ${status}
                `;
                legend.appendChild(item);
            });
        });
    }

    if (currentBranchId) {
        initCalendar(currentBranchId);
    }

    window.switchTabBranch = function(branchId) {
        const tabs = document.querySelectorAll('.tab');
        tabs.forEach(tab => tab.classList.remove('active'));
        document.querySelector(`.tab[data-branch-id="${branchId}"]`)?.classList.add('active');
        currentBranchId = branchId;
        branchInput.value = branchId;
        initCalendar(branchId);
    };

    window.addEventListener('click', function(event) {
        const modal = document.getElementById('appointmentModalDetails');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});
