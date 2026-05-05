document.addEventListener("DOMContentLoaded", function () {
    const profileCard = document.getElementById("profileCard");
    if (!profileCard) return;

    fetch(`${BASE_URL}/Owner/processes/profile/get_details.php`)
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

            profileCard.innerHTML = `
                <div class="profile-info">
                    <h3>${data.full_name}</h3>

                    <p><strong>Email:</strong> <span>${data.email}</span></p>
                    <p><strong>Contact Number:</strong> <span>${data.contact_number}</span></p>
                    <p><strong>Registered:</strong> <span>${data.joined}</span></p>
                    <p><strong>Last Updated:</strong> <span>${data.date_updated ? data.date_updated : '—'}</span></p>
                </div>

                <div class="profile-qr" style="text-align:center; margin-top:15px;">
                    <h4>Payment QR Code</h4>
                    ${
                        QR_IMAGE_URL
                            ? `<img src="${QR_IMAGE_URL}" alt="QR Code" style="width:140px;">`
                            : `<p style="color:#888;">No QR uploaded</p>`
                    }

                    <form method="POST" action="${BASE_URL}/Owner/processes/profile/upload_qr.php" enctype="multipart/form-data">
                        <input type="file" name="qrImage" accept="image/*" required>
                        <button type="submit" class="confirm-btn">Replace QR</button>
                    </form>
                </div>

                <div class="button-group button-group-profile">
                    <button class="confirm-btn" id="changePasswordBtn">Change Password</button>
                    <button class="confirm-btn" id="changeEmailBtn">Change Email</button>
                </div>
            `;

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
