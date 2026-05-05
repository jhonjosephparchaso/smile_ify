document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('notifDropdownToggle');
    const dropdown = document.getElementById('notifDropdown');

    if (toggle && dropdown) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });

        document.addEventListener('click', function (e) {
            if (!toggle.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
    }

    const notifItems = document.querySelectorAll('.notif-item');
    if (notifItems.length > 0) {
        notifItems.forEach(function (item) {
            item.addEventListener('click', function () {

                const notifId = this.getAttribute('data-id');
                const message = this.querySelector('.notif-message')?.textContent || "";

                if (this.classList.contains('unread')) {
                    fetch(`${BASE_URL}/processes/read_notification.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'notification_id=' + encodeURIComponent(notifId)
                    })
                    .then(res => res.text())
                    .then(data => {
                        if (data.trim() === "success") {
                            this.classList.remove('unread');

                            const notifBadge = document.querySelector('#notifDropdownToggle .notif-badge');
                            if (notifBadge) {
                                const count = parseInt(notifBadge.textContent || '0', 10) || 0;
                                if (count > 1) notifBadge.textContent = count - 1;
                                else notifBadge.remove();
                            }
                        }
                    });
                }

                if (message.startsWith("Your dental appointment requires action")) {

                    const appointmentId = this.getAttribute('data-appointment-id');

                    if (!appointmentId || appointmentId === "null") {
                        alert("This notification is not linked to an appointment.");
                        return;
                    }

                    fetch(
                        `${BASE_URL}/Patient/processes/appointments/get_reschedule_proposal.php?appointment_id=${appointmentId}`
                    )
                    .then(res => res.json())
                    .then(data => openRescheduleModal(data))
                    .catch(() => alert("Failed to load reschedule proposal."));
                }
            });
        });
    }

    const markAllReadBtn = document.getElementById('markAllRead');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function (e) {
            e.preventDefault();

            fetch(`${BASE_URL}/processes/read_all_notifications.php`, {
                method: 'POST'
            })
                .then(response => response.text())
                .then(data => {
                    if (data.trim() === "success") {
                        document.querySelectorAll('.notif-item.unread').forEach(item => {
                            item.classList.remove('unread');
                        });

                        const notifBadge = document.querySelector('#notifDropdownToggle .notif-badge');
                        if (notifBadge) notifBadge.remove();
                    }
                })
                .catch(err => console.error('Failed to mark all as read:', err));
        });
    }

    function updatePatientsBadge() {
        fetch(`${BASE_URL}/Admin/processes/index/get_booking_notification.php`)
            .then(res => res.json())
            .then(data => {
                const badge = document.getElementById('patientsBadge');
                if (!badge) return;

                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = "inline-block";
                } else {
                    badge.style.display = "none";
                }
            })
            .catch(err => console.error("Error updating patients badge:", err));
    }

    updatePatientsBadge();

    if (!window.patientsBadgeUpdater) {
        window.patientsBadgeUpdater = setInterval(updatePatientsBadge, 300000);
    }
});

function openRescheduleModal(data) {
    window.currentRescheduleProposal = data;
    const modal = document.getElementById('rescheduleModal');
    const body = document.getElementById('rescheduleModalBody');

    if (!data.success) {
        body.innerHTML = `<p>${data.message}</p>`;
        modal.style.display = "block";
        return;
    }

    body.innerHTML = `
        <h2>Appointment Reschedule</h2>

        <div class="resched-section">
            <h3>Original Appointment</h3>

            <div class="row">
                <span class="label">Patient:</span>
                <span class="value">${data.patient}</span>
            </div>
            <div class="row">
                <span class="label">Branch:</span>
                <span class="value">${data.original_branch}</span>
            </div>
            <div class="row">
                <span class="label">Dentist:</span>
                <span class="value">Dr. ${data.original_dentist}</span>
            </div>
            <div class="row">
                <span class="label">Date:</span>
                <span class="value">${data.original_date}</span>
            </div>
            <div class="row">
                <span class="label">Time:</span>
                <span class="value">${data.original_time}</span>
            </div>
        </div>

        <div class="resched-section">
            <h3>Proposed New Schedule</h3>

            <div class="row">
                <span class="label">Branch:</span>
                <span class="value">${data.branch}</span>
            </div>
            <div class="row">
                <span class="label">Dentist:</span>
                <span class="value">Dr. ${data.dentist}</span>
            </div>
            <div class="row">
                <span class="label">Date:</span>
                <span class="value">${data.date}</span>
            </div>
            <div class="row">
                <span class="label">Time:</span>
                <span class="value">${data.time}</span>
            </div>
        </div>

        <div class="button-group">
            <button class="form-button confirm-btn" onclick="confirmReschedule(${data.appointment_id})">
                Confirm Schedule
            </button>
            <button class="form-button cancel-btn" onclick="cancelReschedule(${data.appointment_id})">
                Cancel Appointment
            </button>
        </div>
    `;

    modal.style.display = "block";
}

window.addEventListener('click', function (e) {
    const modal = document.getElementById('rescheduleModal');
    if (e.target === modal) {
        modal.style.display = "none";
    }
});

let pendingRescheduleAppointmentId = null;
let pendingCancelAppointmentId = null;

function confirmReschedule(appointmentId) {
    if (!appointmentId) return;

    pendingRescheduleAppointmentId = appointmentId;

    const modal = document.getElementById("reschedConfirmModal");
    if (modal) modal.style.display = "block";
}

function cancelReschedule(appointmentId) {
    if (!appointmentId) return;

    pendingCancelAppointmentId = appointmentId;

    const modal = document.getElementById("cancelConfirmModal");
    if (modal) modal.style.display = "block";
}

document.addEventListener("DOMContentLoaded", function () {

    const confirmReschedBtn = document.getElementById("confirmReschedBtn");
    const cancelReschedBtn  = document.getElementById("cancelReschedBtn");
    const reschedModal      = document.getElementById("reschedConfirmModal");

    if (confirmReschedBtn) {
        confirmReschedBtn.addEventListener("click", function () {
            if (!pendingRescheduleAppointmentId) return;

            const p = window.currentRescheduleProposal;

            fetch(`${BASE_URL}/Patient/processes/appointments/confirm_reschedule.php`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: new URLSearchParams({
                    appointment_id: pendingRescheduleAppointmentId,
                    branch_id: p.branch_id,
                    dentist_id: p.dentist_id,
                    appointment_date: p.date_raw,
                    appointment_time: p.time_raw
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    reschedModal.style.display = "none";

                    const mainModal = document.getElementById("rescheduleModal");
                    if (mainModal) mainModal.style.display = "none";

                    location.reload();
                } else {
                    alert(data.message || "Failed to confirm reschedule.");
                }
            })
            .catch(err => {
                console.error(err);
                alert("Server error while confirming reschedule.");
            });
        });
    }

    if (cancelReschedBtn) {
        cancelReschedBtn.addEventListener("click", function () {
            pendingRescheduleAppointmentId = null;
            reschedModal.style.display = "none";
        });
    }

    window.addEventListener("click", function (e) {
        if (e.target === reschedModal) {
            pendingRescheduleAppointmentId = null;
            reschedModal.style.display = "none";
        }
    });

    const confirmCancelBtn = document.getElementById("confirmCancelBtn");
    const cancelCancelBtn  = document.getElementById("cancelCancelBtn");
    const cancelModal      = document.getElementById("cancelConfirmModal");

    if (confirmCancelBtn) {
        confirmCancelBtn.addEventListener("click", function () {
            if (!pendingCancelAppointmentId) return;

            fetch(`${BASE_URL}/Patient/processes/appointments/cancel_appointment.php`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `appointment_id=${encodeURIComponent(pendingCancelAppointmentId)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    cancelModal.style.display = "none";

                    const reschedModal = document.getElementById("rescheduleModal");
                    if (reschedModal) reschedModal.style.display = "none";

                    location.reload();
                } else {
                    alert(data.message || "Failed to cancel appointment.");
                }
            })
            .catch(() => alert("Server error while cancelling appointment."));
        });
    }

    if (cancelCancelBtn) {
        cancelCancelBtn.addEventListener("click", function () {
            pendingCancelAppointmentId = null;
            cancelModal.style.display = "none";
        });
    }

    window.addEventListener("click", function (e) {
        if (e.target === cancelModal) {
            pendingCancelAppointmentId = null;
            cancelModal.style.display = "none";
        }
    });
});
