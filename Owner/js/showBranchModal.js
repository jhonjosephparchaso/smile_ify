document.addEventListener("DOMContentLoaded", () => {
    const branchModal = document.getElementById("manageBranchModal");
    const branchBody = document.getElementById("branchModalBody");

    document.body.addEventListener("click", function (e) {
        if (e.target.classList.contains("btn-branch")) {
            const id = e.target.getAttribute("data-id");

            fetch(`${BASE_URL}/Owner/processes/profile/branches/get_branch_details.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        branchBody.innerHTML = `<p style="color:red;">${data.error}</p>`;
                        branchModal.style.display = "block";
                        return;
                    }

                    renderBranchForm(data);
                    branchModal.style.display = "block";
                })
                .catch(() => {
                    branchBody.innerHTML = `<p style="color:red;">Error loading details</p>`;
                    branchModal.style.display = "block";
                });
        }
    });

    document.body.addEventListener("click", function (e) {
        if (e.target.id === "insertBranchBtn") {
            renderBranchForm(null);
            branchModal.style.display = "block";
        }
    });

    function renderBranchForm(data) {
        const isEdit = !!data;
        branchBody.innerHTML = `
            <h2>${isEdit ? "Manage Branch" : "Add Branch"}</h2>
            <form id="branchForm" action="${BASE_URL}/Owner/processes/profile/branches/${isEdit ? "update_branch.php" : "insert_branch.php"}" method="POST" autocomplete="off">
                ${isEdit ? `<input type="hidden" name="branch_id" value="${data.branch_id}">` : ""}

                <input type="hidden" name="confirmDeactivate" id="confirmDeactivate" value="0">

                <div class="form-group">
                    <input type="text" id="branchName" name="branchName" class="form-control"
                        value="${isEdit ? data.name : ""}" required placeholder=" ">
                    <label for="branchName" class="form-label">Branch Name <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <input type="text" id="nickname" name="nickname" class="form-control"
                        value="${isEdit ? (data.nickname || "") : ""}" required placeholder=" " autocomplete="off">
                    <label for="nickname" class="form-label">Nickname <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <input type="text" id="address" name="address" class="form-control"
                        value="${isEdit ? (data.address || "") : ""}" required placeholder=" " autocomplete="off">
                    <label for="address" class="form-label">Address <span class="required">*</span></label>
                </div>

                <div class="form-group phone-group">
                    <input type="tel" 
                        id="contactNumber" 
                        name="contactNumber" 
                        class="form-control" 
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')" 
                        pattern="[0-9]{10}" 
                        title="Mobile number must be 10 digits" 
                        required 
                        maxlength="10"
                        value="${isEdit ? (data.phone_number ? data.phone_number.replace('+63','') : '') : ''}" />
                    <label for="contactNumber" class="form-label">Mobile Number <span class="required">*</span></label>
                    <span class="phone-prefix">+63</span>
                </div>

                <div class="form-group">
                    <input 
                        type="number" 
                        id="chairCount" 
                        name="chairCount" 
                        class="form-control"
                        min="1"
                        value="${isEdit ? (data.dental_chairs ?? 1) : 1}"
                        required
                        placeholder=" "
                    >
                    <label for="chairCount" class="form-label">
                        Number of Dental Chairs <span class="required">*</span>
                    </label>
                </div>

                <div class="form-group">
                    <input type="url" id="map_url" name="map_url" class="form-control" required
                        value="${isEdit ? (data.map_url || '') : ''}" placeholder=" ">
                    <label for="map_url" class="form-label">Google Maps URL <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <select id="status" name="status" class="form-control" required>
                        <option value="" disabled ${!isEdit ? "selected" : ""}></option>
                        <option value="Active" ${isEdit && data.status === "Active" ? "selected" : ""}>Active</option>
                        <option value="Inactive" ${isEdit && data.status === "Inactive" ? "selected" : ""}>Inactive</option>
                    </select>
                    <label for="status" class="form-label">Status <span class="required">*</span></label>
                </div>

                ${isEdit ? `
                <div class="form-group">
                    <input type="text" id="dateCreated" class="form-control" value="${data.date_created}" disabled>
                    <label for="dateCreated" class="form-label">Date Created</label>
                </div>` : ""}

                ${isEdit ? `
                <div class="form-group">
                    <input type="text" id="dateUpdated" class="form-control" value="${data.date_updated ? data.date_updated : '-'}"" disabled>
                    <label for="dateUpdated" class="form-label">Last Updated</label>
                </div>` : ""}

                <div class="button-group button-group-profile">
                    <button type="submit" class="form-button confirm-btn">${isEdit ? "Save Changes" : "Add Branch"}</button>
                    <button type="button" class="form-button cancel-btn" onclick="closeBranchModal()">Cancel</button>
                </div>
            </form>
        `;

        document.addEventListener("submit", function (e) {
            const form = e.target;
            if (form.id !== "branchForm") return;

            const statusSelect = form.querySelector("#status");
            const confirmInput = form.querySelector("#confirmDeactivate");
            const branchIdInput = form.querySelector("input[name='branch_id']");

            if (!statusSelect || !confirmInput) return;

            if (!branchIdInput) return;

            if (statusSelect.value === "Inactive" && confirmInput.value !== "1") {
                e.preventDefault();

                const branchId = form.querySelector("input[name='branch_id']").value;

                openBranchDeactivateConfirm(branchId, () => {
                    confirmInput.value = "1";
                    form.submit();
                });
            }
        });
    }
});

function closeBranchModal() {
    document.getElementById("manageBranchModal").style.display = "none";
}
