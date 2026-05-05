function attachBookingDateValidators() {
    const form = document.querySelector("#bookingModal form");
    if (!form) return;

    const dobInput = form.querySelector("#dateofBirth");
    const childDobInput = form.querySelector("#childDob");
    const appointmentInput = form.querySelector("#appointmentDate");
    const dobError = form.querySelector("#dobError");
    const dateError = form.querySelector("#dateError");
    const branchSelect = form.querySelector("#appointmentBranch");

    let closedDates = [];

    if (branchSelect) {
        branchSelect.addEventListener("change", async function () {
            const branchId = this.value;
            closedDates = [];
            if (!branchId) return;
            try {
                const res = await fetch(`${BASE_URL}/processes/get_closed_dates.php?branch_id=${branchId}`);
                const data = await res.json();
                closedDates = data.closedDates || [];
            } catch (e) {
                closedDates = [];
            }
            appointmentInput.value = "";
            dateError.style.display = "none";
        });
    }

    if (dobInput && dobError) {
        const today = new Date();
        dobInput.max = today.toISOString().split("T")[0];

        dobInput.addEventListener("input", function () {
            if (!this.value) {
                dobError.style.display = "none";
                return;
            }

            if (this.value === "0001-01-01" || this.value === "01/01/0001") {
                dobError.textContent = "Please enter a valid date of birth.";
                dobError.style.display = "block";
                this.value = "";
                return;
            }

            const d = new Date(this.value);
            if (isNaN(d.getTime()) || d > today) { // || d.getFullYear() < 1900
                dobError.textContent = "Please enter a valid date of birth.";
                dobError.style.display = "block";
                this.value = "";
            } else {
                dobError.style.display = "none";
            }
        });
    }

    if (childDobInput) {
        const today = new Date();
        childDobInput.max = today.toISOString().split("T")[0];

        childDobInput.addEventListener("input", function () {

            let errorSpan = document.getElementById("childDobError");

            if (!errorSpan) {
                errorSpan = document.createElement("span");
                errorSpan.id = "childDobError";
                errorSpan.className = "error-msg-calendar error";
                errorSpan.style.display = "none";
                childDobInput.parentNode.appendChild(errorSpan);
            }

            if (!this.value) {
                errorSpan.style.display = "none";
                return;
            }

            if (this.value === "0001-01-01" || this.value === "01/01/0001") {
                errorSpan.textContent = "Please enter a valid date of birth.";
                errorSpan.style.display = "block";
                this.value = "";
                return;
            }

            const d = new Date(this.value);
            if (isNaN(d.getTime()) || d > today) {
                errorSpan.textContent = "Please enter a valid date of birth.";
                errorSpan.style.display = "block";
                this.value = "";
            } else {
                errorSpan.style.display = "none";
            }
        });
    }

    if (appointmentInput && dateError) {
        appointmentInput.addEventListener("input", function () {
            if (!this.value) {
                dateError.style.display = "none";
                return;
            }

            if (this.value === "0001-01-01" || this.value === "01/01/0001") {
                dateError.textContent = "Please enter a valid date.";
                dateError.style.display = "block";
                this.value = "";
                return;
            }

            const d = new Date(this.value);
            if (isNaN(d.getTime()) || d.getFullYear() < 1900) {
                dateError.textContent = "Please enter a valid date.";
                dateError.style.display = "block";
                this.value = "";
                return;
            }

            if (d.getDay() === 0) {
                dateError.textContent = "Sundays are not available for appointments.";
                dateError.style.display = "block";
                this.value = "";
                return;
            }

            if (closedDates.includes(this.value)) {
                dateError.textContent = "This date is unavailable due to branch closure.";
                dateError.style.display = "block";
                this.value = "";
                return;
            }

            dateError.style.display = "none";
        });
    }
}

document.addEventListener("DOMContentLoaded", () => {
    attachBookingDateValidators();
});
