function openLogoutModal() {
    const modal = document.getElementById("logoutModal");
    if (modal) {
        modal.style.display = "flex";
        document.body.classList.add("modal-open");
    }
}

function closeLogoutModal() {
    const modal = document.getElementById("logoutModal");
    if (modal) {
        modal.style.display = "none";
        document.body.classList.remove("modal-open");
    }
}

function confirmLogout() {
    window.location.href = `${BASE_URL}/processes/logout.php`;
}

document.addEventListener("DOMContentLoaded", function () {
    const logoutLink = document.getElementById("logoutLink");
    const confirmBtn = document.getElementById("confirmLogout");
    const cancelBtn = document.getElementById("cancelLogout");

    if (logoutLink) {
        logoutLink.addEventListener("click", function (e) {
            e.preventDefault();
            openLogoutModal();
        });
    }

    if (confirmBtn) {
        confirmBtn.addEventListener("click", confirmLogout);
    }

    if (cancelBtn) {
        cancelBtn.addEventListener("click", closeLogoutModal);
    }
});
