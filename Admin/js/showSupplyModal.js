document.addEventListener("DOMContentLoaded", () => {
    const supplyModal = document.getElementById("manageSupplyModal");
    const supplyBody = document.getElementById("supplyModalBody");
    
    const today = new Date();
    const todayISO = today.toISOString().split("T")[0];

    document.body.addEventListener("click", function (e) {
        if (e.target.classList.contains("btn-supply")) {
            const id = e.target.getAttribute("data-id");

            fetch(`${BASE_URL}/Admin/processes/supplies/get_supply_details.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        supplyBody.innerHTML = `<p style="color:red;">${data.error}</p>`;
                        supplyModal.style.display = "block";
                        return;
                    }

                    renderSupplyForm(data);
                    supplyModal.style.display = "block";
                })
                .catch(() => {
                    supplyBody.innerHTML = `<p style="color:red;">Error loading details</p>`;
                    supplyModal.style.display = "block";
                });
        }
    });

    document.body.addEventListener("click", function (e) {
        if (e.target.id === "insertSupplyBtn") {
            renderSupplyForm(null);
            supplyModal.style.display = "block";
        }
    });

    function renderSupplyForm(data) {
        const isEdit = !!data;

        supplyBody.innerHTML = `
            <h2>${isEdit ? "Manage Supply" : "Add Supply"}</h2>
            <form id="supplyForm" action="${BASE_URL}/Admin/processes/supplies/${isEdit ? "update_supply.php" : "insert_supply.php"}" method="POST" autocomplete="off">
                ${isEdit ? `<input type="hidden" name="supply_id" value="${data.supply_id}">` : ""}
                <input type="hidden" name="branch_id" value="${branchId}">

                <div class="form-group">
                    <input type="text" id="supplyName" name="supplyName" class="form-control"
                        value="${isEdit ? data.name : ""}" required placeholder=" ">
                    <label for="supplyName" class="form-label">Supply Name <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <input type="text" id="description" name="description" class="form-control"
                        value="${isEdit ? (data.description || "") : ""}" placeholder=" ">
                    <label for="description" class="form-label">Description</label>
                </div>

                <div class="form-group">
                    <input type="text" id="category" name="category" class="form-control"
                        value="${isEdit ? (data.category || "") : ""}" placeholder=" ">
                    <label for="category" class="form-label">Category</label>
                </div>

                <div class="form-group">
                    <input type="text" id="unit" name="unit" class="form-control"
                        value="${isEdit ? (data.unit || "") : ""}" placeholder=" ">
                    <label for="unit" class="form-label">Unit</label>
                </div>

                <div class="form-group">
                    <input type="number" id="quantity" name="quantity" class="form-control"
                        value="${isEdit ? data.quantity : ""}" required placeholder=" " min="0">
                    <label for="quantity" class="form-label">Quantity <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <input type="number" id="reorderLevel" name="reorderLevel" class="form-control"
                        value="${isEdit ? data.reorder_level : ""}" required placeholder=" " min="0">
                    <label for="reorderLevel" class="form-label">Reorder Level <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <input type="date" id="expiration_date" name="expiration_date" class="form-control"
                        value="${isEdit ? (data.expiration_date || "") : ""}" placeholder=" " min="${todayISO}">
                    <label for="expiration_date" class="form-label">Expiration Date</label>
                    <span id="expErrorMsg" class="error-msg-calendar error" style="display:none;"></span>
                </div>

                <div class="form-group">
                    <div id="servicesContainer" class="checkbox-group">
                        <p class="loading-text">Loading Services</p>
                    </div>
                </div>

                <div class="form-group">
                    <select id="status" name="status" class="form-control" required>
                        <option value="" disabled ${!isEdit ? "selected" : ""}></option>
                        <option value="Available" ${isEdit && data.status === "Available" ? "selected" : ""}>Available</option>
                        <option value="Out of Stock" ${isEdit && data.status === "Out of Stock" ? "selected" : ""}>Out of Stock</option>
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
                    <input type="text" id="dateUpdated" class="form-control" value="${data.latest_update}" disabled>
                    <label for="dateUpdated" class="form-label">Last Updated</label>
                </div>` : ""}

                <div class="button-group button-group-profile">
                    <button type="submit" class="form-button confirm-btn">${isEdit ? "Save Changes" : "Add Supply"}</button>
                    <button type="button" class="form-button cancel-btn" onclick="closeSupplyModal()">Cancel</button>
                </div>
            </form>
        `;
        const expInput = document.getElementById("expiration_date");

        const today2 = new Date();
        const year = today2.getFullYear();
        const month = String(today2.getMonth() + 1).padStart(2, "0");
        const day = String(today2.getDate()).padStart(2, "0");
        expInput.min = `${year}-${month}-${day}`;

        validateExpirationDate();
        loadBranchServices(data);
    }

    function validateExpirationDate() {
        const expInput = document.getElementById("expiration_date");
        const expError = document.getElementById("expErrorMsg");

        expInput.addEventListener("change", function () {
            expError.style.display = "none";
        });
    }

    function loadBranchServices(data = null) {
        const container = document.getElementById("servicesContainer");

        fetch(`${BASE_URL}/Admin/processes/supplies/load_services_for_supply.php`, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
                branch_id: branchId,
                supply_id: data?.supply_id || ""
            })
        })
        .then(res => res.json())
        .then(services => {
            if (!Array.isArray(services) || services.length === 0) {
                container.innerHTML = `<p>No services available for this branch.</p>`;
                return;
            }

            container.innerHTML = services.map(service => {
                const checked = service.assigned ? "checked" : "";
                const qtyDisplay = service.assigned ? "inline-block" : "none";
                return `
                    <div class="checkbox-item">
                        <label>
                            <input type="checkbox" name="services[]" value="${service.id}" ${checked}>
                            ${service.name}
                        </label>
                        <input type="number" name="quantities[${service.id}]" class="service-quantity" 
                            value="${service.quantity || ""}" 
                            min="1" placeholder="Qty" 
                            style="display:${qtyDisplay}; width:80px; margin-left:10px;">
                    </div>
                `;
            }).join("");

            container.querySelectorAll("input[type='checkbox']").forEach(chk => {
                chk.addEventListener("change", () => {
                    const qtyInput = chk.closest(".checkbox-item").querySelector(".service-quantity");
                    if (!qtyInput) return;
                    qtyInput.style.display = chk.checked ? "inline-block" : "none";
                    if (!chk.checked) qtyInput.value = "";
                });
            });
        })
        .catch(err => {
            console.error(err);
            container.innerHTML = `<p style="color:red;">Error loading services.</p>`;
        });
    }
});

function closeSupplyModal() {
    document.getElementById("manageSupplyModal").style.display = "none";
}
