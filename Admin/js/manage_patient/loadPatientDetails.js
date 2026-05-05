document.addEventListener("DOMContentLoaded", function () {
    const patientCard = document.getElementById("patientCard");
    if (!patientCard) return;

    fetch(`${BASE_URL}/Admin/processes/manage_patient/get_patient_details.php?id=${userId}`)
        .then(response => {
            if (!response.ok) throw new Error();
            return response.json();
        })
        .then(data => {
            if (data.error) {
                patientCard.innerHTML = `<p>${data.error}</p>`;
                return;
            }

            const isDependent = data.is_dependent === true;
            window.IS_DEPENDENT_ACCOUNT = isDependent;
            const age = calculateAge(data.date_of_birth);

            const relationshipHtml = data.relationship
                ? `<p><strong>Relationship:</strong><span>${data.relationship}</span></p>`
                : "";

            let contactHtml = "";
            let guardianHtml = "";
            let actionButtons = "";

            if (isDependent) {
                const g = data.guardian_info;
                const guardianAge = calculateAge(g.dob);

                guardianHtml = `
                    <hr style="margin:15px 0;">
                    <h4 style="margin-bottom:10px;">Guardian Information</h4>

                    <p><strong>Name:</strong> <span>${g.full_name}</span></p>
                    <p><strong>Gender:</strong> <span>${g.gender}</span></p>
                    <p><strong>Date of Birth:</strong> <span>${g.dob}</span></p>
                    <p><strong>Age:</strong> <span>${guardianAge}</span></p>
                    <p><strong>Email:</strong> <span>${g.email}</span></p>
                    <p><strong>Contact Number:</strong> <span>${g.contact_number}</span></p>
                    <p><strong>Address:</strong> <span>${g.address}</span></p>
                `;
            } else {
                contactHtml = `
                    <p><strong>Email:</strong><span>${data.email}</span></p>
                    <p><strong>Contact Number:</strong><span>${data.contact_number}</span></p>
                    <p><strong>Address:</strong><span>${data.address}</span></p>
                `;

                if (data.status.toLowerCase() === "active") {
                    actionButtons = `<button type="button" class="confirm-btn" id="setInactiveBtn">Set Inactive</button>`;
                } else if (data.status.toLowerCase() === "inactive") {
                    actionButtons = `<button type="button" class="confirm-btn" id="setActiveBtn">Set Active</button>`;
                }
            }

            patientCard.innerHTML = `
                <h3>${data.full_name}</h3>

                ${relationshipHtml}

                <p><strong>Gender:</strong><span>${data.gender}</span></p>
                <p><strong>Date of Birth:</strong><span>${data.date_of_birth}</span></p>
                <p><strong>Age:</strong><span>${age}</span></p>

                ${contactHtml}

                <p><strong>Registered:</strong><span>${data.joined}</span></p>
                <p><strong>Last Updated:</strong><span>${data.date_updated}</span></p>
                <p><strong>Status:</strong><span>${data.status}</span></p>

                ${guardianHtml}

                <div class="button-group button-group-profile">
                    ${actionButtons}
                </div>
            `;

            if (!isDependent) {
                const openStatusModal = (status) => {
                    document.getElementById("statusUserId").value = userId;
                    document.getElementById("statusValue").value = status;
                    document.getElementById("statusMessage").textContent =
                        `Are you sure you want to set this patient as ${status}?`;
                    document.getElementById("setStatusModal").style.display = "block";
                };

                const setActiveBtn = document.getElementById("setActiveBtn");
                const setInactiveBtn = document.getElementById("setInactiveBtn");

                if (setActiveBtn) setActiveBtn.addEventListener("click", () => openStatusModal("active"));
                if (setInactiveBtn) setInactiveBtn.addEventListener("click", () => openStatusModal("inactive"));
            }
        })
        .catch(() => {
            patientCard.innerHTML = "<p>Error loading profile.</p>";
        });
});

function closeStatusModal() {
    document.getElementById("setStatusModal").style.display = "none";
}

function calculateAge(dobString) {
    if (!dobString) return "-";
    const dob = new Date(dobString);
    if (isNaN(dob)) return "-";

    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const monthDiff = today.getMonth() - dob.getMonth();

    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
        age--;
    }
    return age;
}