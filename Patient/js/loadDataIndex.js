document.addEventListener("DOMContentLoaded", function() {
    function loadPatientAppointments() {
        fetch(`${BASE_URL}/Patient/processes/index/get_upcoming_appointments.php`)
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById("patientUpcomingAppointments");
                if (!container) return;

                container.innerHTML = "";

                if (data.error) {
                    container.innerHTML = `<div class="appointment">${data.error}</div>`;
                    return;
                }

                const appts = data.appointments || [];
                if (appts.length === 0) {
                    container.innerHTML = `<div class="appointment">No upcoming appointments</div>`;
                    return;
                }

                container.innerHTML = appts.map(a => `
                    <div class="appointment">
                        <strong>${a.date}</strong> at ${a.time} for ${a.for}
                        with ${a.dentist} in ${a.branch}
                    </div>
                `).join("");
            })
            .catch(err => console.error("Error loading patient appointments:", err));
    }
    
    function loadAnnouncements() {
        fetch(`${BASE_URL}/Patient/processes/index/get_announcements.php`)
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById("patientAnnouncements");
                if (!container) return;

                container.innerHTML = "";

                if (data.error) {
                    container.innerHTML = `<div class="announcement">${data.error}</div>`;
                    return;
                }

                const announcements = data.announcements || [];
                if (announcements.length === 0) {
                    container.innerHTML = `<div class="announcement">No active announcements at the moment.</div>`;
                    return;
                }

                announcements.forEach(a => {
                    const dateRange =
                        a.start_date && a.end_date
                            ? `<br><small>${a.start_date} - ${a.end_date}</small>`
                            : a.start_date
                            ? `<br><small>From ${a.start_date}</small>`
                            : a.end_date
                            ? `<br><small>Until ${a.end_date}</small>`
                            : "";

                    const branchDisplay = a.branch_name
                        ? `<br><small>📍 Branch: ${a.branch_name}</small>`
                        : "";

                    container.insertAdjacentHTML(
                        "beforeend",
                        `
                        <div class="announcement">
                            <strong>${a.title}</strong><br>
                            ${a.description}
                            ${branchDisplay}
                            ${dateRange}
                        </div>
                    `
                    );
                });
            })
            .catch(err => console.error("Error loading announcements:", err));
    }

    function loadDentalTips() {
        fetch(`${BASE_URL}/Patient/processes/index/get_tips.php`)
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById("patientTips");
                if (!container) return;

                container.innerHTML = "";

                if (data.error) {
                    container.innerHTML = `<div class="tip">${data.error}</div>`;
                    return;
                }

                const tips = data.tips || [];
                if (tips.length === 0) {
                    container.innerHTML = `<div class="tip">No dental tips available right now.</div>`;
                    return;
                }

                tips.forEach(t => {
                    container.insertAdjacentHTML("beforeend", `<div class="tip">${t}</div>`);
                });
            })
            .catch(err => console.error("Error loading dental tips:", err));
    }

loadPatientAppointments();
loadAnnouncements();
loadDentalTips();
});
