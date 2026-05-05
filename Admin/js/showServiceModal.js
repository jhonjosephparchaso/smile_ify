document.addEventListener("DOMContentLoaded", () => {
    const serviceModal = document.getElementById("manageServiceModal");
    const serviceBody = document.getElementById("serviceModalBody");

    document.body.addEventListener("click", function (e) {
        if (e.target.classList.contains("btn-service")) {
            const id = e.target.getAttribute("data-id");

            fetch(`${BASE_URL}/Admin/processes/services/get_service_details.php?id=${id}`)
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
    });

    function renderServiceForm(data) {
        serviceBody.innerHTML = `
            <h2>Update Service Status</h2>
            <form id="serviceForm" 
                    action="${BASE_URL}/Admin/processes/services/update_service.php" 
                    method="POST" autocomplete="off">

                <input type="hidden" name="service_id" value="${data.service_id}">

                <div class="form-group">
                    <input type="text" id="serviceName" class="form-control"
                        value="${data.name}" disabled placeholder=" ">
                    <label for="serviceName" class="form-label">Service Name</label>
                </div>

                <div class="form-group">
                    <input type="number" id="price" class="form-control"
                        value="${data.price}" disabled placeholder=" ">
                    <label for="price" class="form-label">Price</label>
                </div>

                <div class="form-group">
                    <input type="number" id="duration_minutes" class="form-control"
                        value="${data.duration_minutes}" disabled placeholder=" ">
                    <label for="duration_minutes" class="form-label">Duration (minutes)</label>
                </div>

                <div class="form-group">
                    <select id="requires_xray" class="form-control" disabled>
                        <option value="0" ${data.requires_xray == 0 ? "selected" : ""}>No</option>
                        <option value="1" ${data.requires_xray == 1 ? "selected" : ""}>Yes</option>
                    </select>
                    <label for="requires_xray" class="form-label">Requires X-ray?</label>
                </div>

                <div class="form-group">
                    <input type="text" id="dateCreated" class="form-control" value="${data.date_created}" disabled>
                    <label for="dateCreated" class="form-label">Date Created</label>
                </div>

                <div class="form-group">
                    <input type="text" id="dateUpdated" class="form-control" value="${data.date_updated ? data.date_updated : '-'}" disabled>
                    <label for="dateUpdated" class="form-label">Last Updated</label>
                </div>

                <div class="form-group">
                    <select id="status" name="status" class="form-control" required>
                        <option value="Active" ${data.status === "Active" ? "selected" : ""}>Active</option>
                        <option value="Inactive" ${data.status === "Inactive" ? "selected" : ""}>Inactive</option>
                    </select>
                    <label for="status" class="form-label">Status <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <label class="confirmation-label">
                        <input type="checkbox" id="confirmationCheck" required>
                        I hereby confirm that all information provided above is true and accurate. <br>
                        I understand that any updates made — including changes to service details, or status —
                        may affect ongoing appointments or scheduling. I take responsibility to ensure that 
                        the <strong>Owner</strong> is notified about these changes.
                    </label>
                    <span id="confirmError" class="error-msg" style="display:none; color:red; font-size:0.9em;">
                        Please confirm before proceeding.
                    </span>
                </div>

                <div class="button-group button-group-profile">
                    <button type="submit" class="form-button confirm-btn">Save Status</button>
                    <button type="button" class="form-button cancel-btn" onclick="closeServiceModal()">Cancel</button>
                </div>
            </form>
        `;
    }
});

function closeServiceModal() {
    document.getElementById("manageServiceModal").style.display = "none";
}
