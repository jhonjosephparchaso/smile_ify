document.addEventListener("DOMContentLoaded", () => {
    const promoModal = document.getElementById("managePromoModal");
    const promoBody = document.getElementById("promoModalBody");

    document.body.addEventListener("click", function (e) {
        if (e.target.classList.contains("btn-promo")) {
            const id = e.target.getAttribute("data-id");

            fetch(`${BASE_URL}/Admin/processes/promos/get_promo_details.php?id=${id}`)
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
    });

    function renderPromoForm(data) {
        promoBody.innerHTML = `
            <h2>Manage Promo Status</h2>
            <form id="promoForm" action="${BASE_URL}/Admin/processes/promos/update_promo.php" method="POST" autocomplete="off">
                <input type="hidden" name="promo_id" value="${data.promo_id}">

                <div class="form-group">
                    <input type="text" class="form-control" value="${data.name}" disabled>
                    <label class="form-label">Promo Name</label>
                </div>

                <div class="form-group">
                    <textarea class="form-control" rows="3" disabled>${data.description || ""}</textarea>
                    <label class="form-label">Description</label>
                </div>

                ${data.image_path ? `
                <div class="form-group">
                    <div style="margin-top:10px;">
                        <img src="${BASE_URL}${data.image_path}" 
                            alt="Promo Image" 
                            style="max-width:300px; max-height:300px; border-radius:4px; object-fit:cover; display:block;">
                    </div>
                </div>` : ""}

                <div class="form-group">
                    <input type="text" class="form-control" 
                        value="${data.discount_type === 'percentage' ? 'Percentage (%)' : 'Fixed Amount'}" disabled>
                    <label class="form-label">Discount Type</label>
                </div>

                <div class="form-group">
                    <input type="number" class="form-control" value="${data.discount_value}" disabled>
                    <label class="form-label">Discount Value</label>
                </div>

                <div class="form-group">
                    <input type="date" class="form-control" value="${data.start_date || ''}" disabled>
                    <label class="form-label">Start Date</label>
                </div>

                <div class="form-group">
                    <input type="date" class="form-control" value="${data.end_date || ''}" disabled>
                    <label class="form-label">End Date</label>
                </div>

                <div class="form-group">
                    <input type="text" class="form-control" value="${data.date_created || ''}" disabled>
                    <label class="form-label">Date Created</label>
                </div>

                <div class="form-group">
                    <input type="text" class="form-control" value="${data.date_updated ? data.date_updated : '-'}" disabled>
                    <label class="form-label">Last Updated</label>
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
                        I understand that any updates made — including changes to promo details, or status —
                        may affect ongoing transactions or active offers. I take responsibility to ensure that 
                        the <strong>Owner</strong> is notified about these changes.
                    </label>
                    <span id="confirmError" class="error-msg" style="display:none; color:red; font-size:0.9em;">
                        Please confirm before proceeding.
                    </span>
                </div>

                <div class="button-group button-group-profile">
                    <button type="submit" class="form-button confirm-btn">Save Changes</button>
                    <button type="button" class="form-button cancel-btn" onclick="closePromoModal()">Cancel</button>
                </div>
            </form>
        `;
    }
});

function closePromoModal() {
    document.getElementById("managePromoModal").style.display = "none";
}

