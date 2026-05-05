document.addEventListener("DOMContentLoaded", () => {
    const promoModal = document.getElementById("managePromoModal");
    const promoBody = document.getElementById("promoModalBody");

    function toLocalDateStringPH(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, "0");
        const d = String(date.getDate()).padStart(2, "0");
        return `${y}-${m}-${d}`;
    }

    const today = new Date();
    const formattedToday = toLocalDateStringPH(today);

    document.body.addEventListener("click", function (e) {
        if (e.target.classList.contains("btn-promo")) {
            const id = e.target.getAttribute("data-id");

            fetch(`${BASE_URL}/Owner/processes/promos/get_promo_details.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        promoBody.innerHTML = `<p style="color:red;">${data.error}</p>`;
                        promoModal.style.display = "block";
                        return;
                    }
                    renderPromoForm(data);
                    promoModal.style.display = "block";
                })
                .catch(() => {
                    promoBody.innerHTML = `<p style="color:red;">Error loading details</p>`;
                    promoModal.style.display = "block";
                });
        }

        if (e.target.id === "insertPromoBtn") {
            renderPromoForm(null);
            promoModal.style.display = "block";
        }
    });

    document.body.addEventListener("focusin", function (e) {
        if (e.target && (e.target.id === "startDate" || e.target.id === "endDate")) {
            e.target.setAttribute("min", formattedToday);
        }
    });

    document.body.addEventListener("change", function (e) {
        const target = e.target;

        function parseLocalDate(dateStr) {
            const [year, month, day] = dateStr.split('-').map(Number);
            return new Date(year, month - 1, day);
        }

        if (target && target.id === "startDate") {
            const startInput = target;
            const endInput = document.getElementById("endDate");
            const errorEl = document.getElementById("startDateError");

            if (startInput.value) {
                const startDate = parseLocalDate(startInput.value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                if (startDate < today) {
                    errorEl.textContent = "Please enter a valid date.";
                    errorEl.style.display = "block";
                    startInput.value = "";

                    endInput.value = "";
                    endInput.removeAttribute("min");

                } else {
                    errorEl.style.display = "none";

                    endInput.setAttribute("min", startInput.value);

                    if (startInput.value) {
                        const plusOneMonth = new Date(startDate);
                        plusOneMonth.setMonth(plusOneMonth.getMonth() + 1);

                        const y = plusOneMonth.getFullYear();
                        const m = String(plusOneMonth.getMonth() + 1).padStart(2, "0");
                        const d = String(plusOneMonth.getDate()).padStart(2, "0");

                        const autoEnd = `${y}-${m}-${d}`;

                        endInput.value = autoEnd;
                    }
                }
            } else {
                errorEl.style.display = "none";
                endInput.value = "";
                endInput.removeAttribute("min");
            }
        }

        if (target && target.id === "endDate") {
            const startInput = document.getElementById("startDate");
            const endInput = target;
            const errorEl = document.getElementById("endDateError");

            if (endInput.value) {
                const endDate = parseLocalDate(endInput.value);
                const startDate = startInput.value ? parseLocalDate(startInput.value) : null;
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                if (endDate < today) {
                    errorEl.textContent = "Please enter a valid date.";
                    errorEl.style.display = "block";
                    endInput.value = "";
                } else if (startDate && endDate.getTime() === startDate.getTime()) {
                    errorEl.textContent = "Start date and end date cannot be the same.";
                    errorEl.style.display = "block";
                    endInput.value = "";
                } else if (startDate && endDate < startDate) {
                    errorEl.textContent = "End date cannot be before start date.";
                    errorEl.style.display = "block";
                    endInput.value = "";
                } else {
                    errorEl.style.display = "none";
                }
            }
        }
    });

    function renderPromoForm(data) {
        const isEdit = !!data;
        const selectedBranches = isEdit && data.branches ? data.branches.map(Number) : [];

        const startDate = isEdit && data.start_date ? data.start_date : "";
        const endDate = isEdit && data.end_date ? data.end_date : "";

        promoBody.innerHTML = `
            <h2>${isEdit ? "Manage Promo" : "Add Promo"}</h2>
            <form id="promoForm" action="${BASE_URL}/Owner/processes/promos/${isEdit ? "update_promo.php" : "insert_promo.php"}" method="POST" enctype="multipart/form-data" autocomplete="off">
                ${isEdit ? `<input type="hidden" name="promo_id" value="${data.promo_id}">` : ""}

                <div class="form-group">
                    <input type="text" id="promoName" name="promoName" class="form-control"
                        value="${isEdit ? data.name : ""}" required placeholder=" ">
                    <label for="promoName" class="form-label">Promo Name <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <textarea id="description" name="description" class="form-control" rows="3" placeholder=" ">${isEdit ? (data.description || "") : ""}</textarea>
                    <label for="description" class="form-label">Description</label>
                </div>

                <div class="form-group" style="position: relative; margin-bottom: 18px;">
                    <input 
                        type="file" 
                        id="promoImage" 
                        name="promoImage" 
                        class="form-control" 
                        accept="image/*"
                        ${!isEdit ? "required" : ""}
                    >
                    <label for="promoImage" class="form-label" style="display: block; margin-top: 6px; margin-bottom: 4px;">Promo Image <span class="required">*</span></label>

                    ${isEdit && data.image_path 
                        ? `<div class="mt-2" style="margin-top: 6px;">
                                <p style="margin-bottom: 4px;">Current Promo Image:</p>
                                <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 6px;">
                                    <img src="${BASE_URL}${data.image_path}" 
                                        alt="Promo Image"
                                        style="max-width:300px; max-height:300px; border:1px solid #ccc; padding:4px; border-radius:4px; margin-bottom: 6px;">
                                    <button type="button" class="confirm-btn"
                                        style="width:150px; margin:4px 0px 10px 0px;"
                                        onclick="clearImage('promoImage', 'promoImageCleared')">Remove</button>
                                </div>
                                <input type="hidden" name="promoImageCleared" id="promoImageCleared" value="0">
                        </div>`
                        : ""
                    }
                </div>

                <div class="form-group">
                    <select id="discountType" name="discountType" class="form-control" required>
                        <option value="" disabled ${!isEdit ? "selected" : ""}></option>
                        <option value="percentage" ${isEdit && data.discount_type === "percentage" ? "selected" : ""}>Percentage (%)</option>
                        <option value="fixed" ${isEdit && data.discount_type === "fixed" ? "selected" : ""}>Fixed Amount</option>
                    </select>
                    <label for="discountType" class="form-label">Discount Type <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <input type="number" id="discountValue" name="discountValue" class="form-control"
                        value="${isEdit ? data.discount_value : ""}" required placeholder=" " min="0">
                    <label for="discountValue" class="form-label">Discount Value <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <input type="date" id="startDate" name="startDate" class="form-control" value="${startDate}">
                    <label for="startDate" class="form-label">Start Date</label>
                    <span id="startDateError" class="error-msg-calendar error" style="display: none;"></span>
                </div>

                <div class="form-group">
                    <input type="date" id="endDate" name="endDate" class="form-control" value="${endDate}">
                    <label for="endDate" class="form-label">End Date</label>
                    <span id="endDateError" class="error-msg-calendar error" style="display: none;"></span>
                </div>

                <div class="form-group">
                    <div id="branchAssignment" class="checkbox-group"></div>
                </div>

                ${isEdit ? `
                <div class="form-group">
                    <input type="text" id="dateCreated" class="form-control" value="${data.date_created}" disabled>
                    <label for="dateCreated" class="form-label">Date Created</label>
                </div>` : ""}

                ${isEdit ? `
                <div class="form-group">
                    <input type="text" id="dateUpdated" class="form-control" value="${data.date_updated ? data.date_updated : '-'}" disabled>
                    <label for="dateUpdated" class="form-label">Last Updated</label>
                </div>` : ""}

                <div class="button-group button-group-profile">
                    <button type="submit" class="form-button confirm-btn">${isEdit ? "Save Changes" : "Add Promo"}</button>
                    <button type="button" class="form-button cancel-btn" onclick="closePromoModal()">Cancel</button>
                </div>
            </form>
        `;

        fetch(`${BASE_URL}/Owner/processes/employees/get_branches.php`)
            .then(res => res.json())
            .then(branches => {
                const container = document.getElementById("branchAssignment");
                branches.forEach(branch => {
                    const checked = !isEdit || selectedBranches.includes(parseInt(branch.branch_id)) ? "checked" : "";
                    const wrapper = document.createElement("div");
                    wrapper.innerHTML = `
                        <div class="checkbox-item">
                            <input type="checkbox" id="branch_${branch.branch_id}" name="branches[]" value="${branch.branch_id}" ${checked}>
                            <label for="branch_${branch.branch_id}">${branch.name}</label>
                        </div>
                    `;
                    container.appendChild(wrapper);
                });
            });

            setTimeout(() => {
                const promoForm = document.getElementById("promoForm");
                const promoImageInput = document.getElementById("promoImage");
                const clearedField = document.getElementById("promoImageCleared");

                if (!promoForm) return;

                promoForm.addEventListener("submit", function (e) {
                    const removedImage = clearedField && clearedField.value === "1";
                    const noImageSelected = promoImageInput.files.length === 0;

                    const isAdding = !clearedField;

                    if ((isAdding || removedImage) && noImageSelected) {
                        promoImageInput.required = true;

                        promoImageInput.reportValidity();

                        e.preventDefault();
                        return false;
                    }
                });
            }, 80);
    }
});

function closePromoModal() {
    document.getElementById("managePromoModal").style.display = "none";
}

function clearImage(inputId, hiddenId) {
    const input = document.getElementById(inputId);
    const hidden = document.getElementById(hiddenId);

    if (input) input.value = "";
    if (hidden) hidden.value = "1";

    const imgPreview = input.closest(".form-group").querySelector("img");
    if (imgPreview) imgPreview.remove();

    const btn = input.closest(".form-group").querySelector("button");
    if (btn) btn.remove();
}
