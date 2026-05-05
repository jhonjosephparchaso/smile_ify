document.addEventListener("DOMContentLoaded", () => {
    const serviceModal = document.getElementById("manageServiceModal");
    const serviceBody = document.getElementById("serviceModalBody");

    document.body.addEventListener("click", function (e) {
        if (e.target.classList.contains("btn-service")) {
            const id = e.target.getAttribute("data-id");

            fetch(`${BASE_URL}/Owner/processes/services/get_service_details.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        serviceBody.innerHTML = `<p style="color:red;">${data.error}</p>`;
                        serviceModal.style.display = "block";
                        return;
                    }
                    renderServiceForm(data);
                    serviceModal.style.display = "block";
                })
                .catch(() => {
                    serviceBody.innerHTML = `<p style="color:red;">Error loading details</p>`;
                    serviceModal.style.display = "block";
                });
        }

        if (e.target.id === "insertServiceBtn") {
            renderServiceForm(null);
            serviceModal.style.display = "block";
        }
    });

    function renderServiceForm(data) {
        const isEdit = !!data;
        const selectedBranches = isEdit && data.branches ? data.branches.map(Number) : [];

        serviceBody.innerHTML = `
            <h2>${isEdit ? "Manage Service" : "Add Service"}</h2>
            <form id="serviceForm" action="${BASE_URL}/Owner/processes/services/${isEdit ? "update_service.php" : "insert_service.php"}" method="POST" autocomplete="off">
                ${isEdit ? `<input type="hidden" name="service_id" value="${data.service_id}">` : ""}

                <div class="form-group">
                    <input type="text" id="serviceName" name="serviceName" class="form-control"
                        value="${isEdit ? data.name : ""}" required placeholder=" ">
                    <label for="serviceName" class="form-label">Service Name <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <input type="number" id="price" name="price" class="form-control"
                        value="${isEdit ? data.price : ""}" required placeholder=" " min="0">
                    <label for="price" class="form-label">Price <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <input type="number" id="duration_minutes" name="duration_minutes" class="form-control"
                        value="${isEdit ? data.duration_minutes : ""}" required placeholder=" " min="0">
                    <label for="duration_minutes" class="form-label">Duration (minutes)<span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <div id="branchAssignment" class="checkbox-group"></div>
                </div>

                <div class="form-group">
                    <div id="xrayAssignment" class="checkbox-group">
                        <div class="checkbox-item">
                            <input 
                                type="checkbox" 
                                id="requires_xray" 
                                name="requires_xray" 
                                ${isEdit && data.requires_xray == 1 ? "checked" : ""}
                            >
                            <label for="requires_xray">Requires X-ray for this service</label>
                        </div>
                    </div>
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
                    <button type="submit" class="form-button confirm-btn">${isEdit ? "Save Changes" : "Add Service"}</button>
                    <button type="button" class="form-button cancel-btn" onclick="closeServiceModal()">Cancel</button>
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
                        <input type="checkbox" id="branch_${branch.branch_id}" 
                            name="branches[]" 
                            value="${branch.branch_id}" ${checked}>
                        <label for="branch_${branch.branch_id}">${branch.name}</label>
                    </div>
                `;
                container.appendChild(wrapper);
            });
        });
    }
});

function closeServiceModal() {
    document.getElementById("manageServiceModal").style.display = "none";
}
