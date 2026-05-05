document.addEventListener("DOMContentLoaded", function () {
    const profileCard = document.getElementById("profileCard");
    if (!profileCard) return;

    fetch(`${BASE_URL}/Patient/processes/profile/get_details.php`)
        .then(response => {
            if (!response.ok) {
                throw new Error("Forbidden or failed to load.");
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                profileCard.innerHTML = `<p>${data.error}</p>`;
                return;
            }
            const age = calculateAge(data.date_of_birth);
            profileCard.innerHTML = `
                <h3>${data.full_name}</h3>
                <p><strong>Gender:</strong><span>${data.gender}</p></span> 
                <p><strong>Date of Birth:</strong><span>${data.date_of_birth}</p></span> 
                <p><strong>Age:</strong><span>${age}</span></p>
                <p><strong>Email:</strong><span>${data.email}</p></span> 
                <p><strong>Contact Number:</strong><span>${data.contact_number}</p></span> 
                <p><strong>Address:</strong><span>${data.address}</p></span> 
                <p><strong>Registered:</strong><span>${data.joined}</p></span>
                <p><strong>Last Updated:</strong><span>${data.date_updated}</span></p>
                <div class="button-group button-group-profile">
                    <button class="confirm-btn" id="editDetails">Edit Profile</button>
                    <button class="confirm-btn" id="changePasswordBtn">Change Password</button>
                    <button class="confirm-btn" id="changeEmailBtn">Change Email</button>
                </div>
            `;
            const editDetails = document.getElementById("editDetails");
            if (editDetails) {
                editDetails.addEventListener("click", () => {
                    document.getElementById("contactNumber").value = data.contact_number || "";
                    document.getElementById("address").value = data.address || "";
                    document.getElementById("editProfileModal").style.display = "block";
                });
            }

            const changePasswordBtn = document.getElementById("changePasswordBtn");
            if (changePasswordBtn) {
                changePasswordBtn.addEventListener("click", () => {
                    document.getElementById("changePasswordModal").style.display = "block";
                });
            }

            const changeEmailBtn = document.getElementById("changeEmailBtn");
            if (changeEmailBtn) {
                changeEmailBtn.addEventListener("click", () => {
                    document.getElementById("changeEmailModal").style.display = "block";
                });
            }
        })
        .catch(error => {
            profileCard.innerHTML = "<p>Error loading profile.</p>";
            console.error("Fetch error:", error);
        });
});

function closeEditProfileModal() {
    document.getElementById("editProfileModal").style.display = "none";
}
function closeChangePasswordModal() {
    document.getElementById("changePasswordModal").style.display = "none";
}
function closeChangeEmailModal() {
    document.getElementById("changeEmailModal").style.display = "none";
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