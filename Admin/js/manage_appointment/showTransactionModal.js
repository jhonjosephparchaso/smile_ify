document.addEventListener("DOMContentLoaded", () => {
    const manageModal = document.getElementById("manageRecordModal");
    const modalBody = document.getElementById("modalRecordBody");
    if (!manageModal || !modalBody) return;

    document.body.addEventListener("click", function (e) {
        if (e.target.classList.contains("btn-action")) {
            const id = e.target.getAttribute("data-id");
            const type = e.target.getAttribute("data-type");

            let url = "";
            if (type === "dental_transaction") {
                url = `${BASE_URL}/Admin/processes/manage_appointment/transactions/get_transaction_details.php?id=${id}`;
            } else if (type === "vital") {
                url = `${BASE_URL}/Admin/processes/manage_appointment/vitals/get_vital_details.php?id=${id}`;
            } else if (type === "prescription") {
                url = `${BASE_URL}/Admin/processes/manage_appointment/prescriptions/get_prescription_details.php?id=${id}`;
            }
            if (!url) return;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        modalBody.innerHTML = `<p style="color:red;">${data.error}</p>`;
                        manageModal.style.display = "block";
                        return;
                    }

                    if (type === "dental_transaction") {
                        renderTransactionForm(data);
                    } else if (type === "vital") {
                        renderVitalForm(data);
                    } else if (type === "prescription") {
                        renderPrescriptionForm(data);
                    }
                    manageModal.style.display = "block";
                })
                .catch(() => {
                    modalBody.innerHTML = `<p style="color:red;">Error loading ${type} details</p>`;
                    manageModal.style.display = "block";
                });
        }
    });

    document.body.addEventListener("click", function (e) {
        if (e.target.id === "insertTransactionBtn") {
            window.appointmentId = e.target.getAttribute("data-appointment-id");
            renderTransactionForm(null);
            manageModal.style.display = "block";
        }
        if (e.target.id === "insertVitalBtn") {
            window.appointmentId = e.target.getAttribute("data-appointment-id");
            renderVitalForm(null);
            manageModal.style.display = "block";
        }
        if (e.target.id === "insertPrescriptionBtn") {
            window.appointmentId = e.target.getAttribute("data-appointment-id");
            renderPrescriptionForm(null);
            manageModal.style.display = "block";
        }
    });

    function renderTransactionForm(data) {
        const isEdit = !!data;
        const appointmentId = data?.appointment_transaction_id || window.appointmentId || null;

        modalBody.innerHTML = `
            <h2>${isEdit ? "Manage Transaction" : "Add Transaction"}</h2>
            <form id="transactionForm" 
                action="${BASE_URL}/Admin/processes/manage_appointment/transactions/${isEdit ? 'update_transaction.php' : 'insert_transaction.php'}" 
                method="POST" enctype="multipart/form-data" autocomplete="off">

                ${isEdit ? `<input type="hidden" name="dental_transaction_id" value="${data.dental_transaction_id}">` : ""}
                <input type="hidden" name="appointment_transaction_id" value="${appointmentId}">
                <input type="hidden" name="admin_user_id" value="${userId}">
                <input type="hidden" id="remove_xray" name="remove_xray" value="0">
                <input type="hidden" id="removed_receipt" name="removed_receipt" value="0">

                <div class="form-group">
                    <div id="servicesContainer" class="checkbox-group">
                        <p class="loading-text">Loading Services</p>
                    </div>
                </div>

                <div class="form-group">
                    <select id="transactionDentist" class="form-control" name="dentist_id" required>
                        <option value="" disabled ${isEdit ? "" : "selected"} hidden></option>
                    </select>
                    <label class="form-label">Dentist <span class="required">*</span></label>
                </div>

                <div id="xrayFields"></div>
                <div id="medicalCertFields"></div>
                <div id="serviceExtrasContainer"></div>

                <div class="form-group">
                    <select id="transactionPromo" class="form-control" name="promo_id">
                        <option value="" selected hidden>None</option>
                    </select>
                    <label class="form-label">Promo</label>
                </div>

                <div class="form-group">
                    <select id="paymentMethod" class="form-control" name="payment_method" required>
                        <option value="" disabled ${isEdit ? "" : "selected"} hidden>Select Payment Method</option>
                        <option value="Cash" ${isEdit && data.payment_method === "Cash" ? "selected" : ""}>Cash</option>
                        <option value="Cashless" ${isEdit && data.payment_method === "Cashless" ? "selected" : ""}>Cashless</option>
                    </select>
                    <label class="form-label">Payment Method <span class="required">*</span></label>
                </div>

                <div class="form-group" id="cashlessReceiptGroup" style="display:none;">
                    <input type="file" id="cashlessReceipt" class="form-control" name="receipt_upload" accept="image/*,.pdf">
                    <label class="form-label">Upload Cashless Receipt</label>

                    ${isEdit && data.cashless_receipt ? `
                        <div class="receipt-preview">
                            <p>Current file:</p>
                            <img src="${BASE_URL}/${data.cashless_receipt}" class="receipt-img" style="max-width:200px;border-radius:4px;margin-top:5px;">
                            <div style="margin:10px 0px 10px 0px;">
                                <button type="button" class="confirm-btn" onclick="removeReceiptPreview('${data.cashless_receipt}')">Remove</button>
                            </div>
                        </div>
                    ` : ""}
                </div>

                <div class="form-group">
                    <textarea id="notes" class="form-control" name="notes" rows="3" placeholder=" ">${isEdit ? data.notes || "" : ""}</textarea>
                    <label class="form-label">Notes</label>
                </div>

                ${isEdit ? `
                    <div class="form-group">
                        <input class="form-control" value="${data.recorded_by}" disabled>
                        <label class="form-label">Recorded By</label>
                    </div>

                    <div class="form-group">
                        <input class="form-control" value="${data.date_created}" disabled>
                        <label class="form-label">Date Created</label>
                    </div>

                    <div class="form-group">
                        <input class="form-control" value="${data.date_updated || '-'}" disabled>
                        <label class="form-label">Last Updated</label>
                    </div>
                ` : ""}

                <div class="checkout-summary">
                    <h3>Transaction Summary</h3>
                    <div id="servicesList" class="summary-services"></div>

                    <hr class="summary-divider">

                    <div class="summary-item">
                        <span>Subtotal:</span>
                        <span id="subtotalDisplay">₱0.00</span>
                    </div>

                    <div class="summary-item">
                        <span>Discount:</span>
                        <span id="discountDisplay">₱0.00</span>
                    </div>

                    <hr class="summary-divider total-divider">

                    <div class="summary-item total">
                        <span>Total Amount:</span>
                        <span id="totalDisplay">₱0.00</span>
                    </div>
                </div>

                <input type="hidden" id="total_payment" name="total_payment" value="0">

                <div class="button-group">
                    <button type="submit" class="form-button confirm-btn">${isEdit ? "Update Transaction" : "Save Transaction"}</button>
                    <button type="button" class="form-button cancel-btn" onclick="closeManageModal()">Cancel</button>
                </div>

            </form>
        `;

        // <div class="summary-item">
        //     <span>Total Additional Payment:</span>
        //     <span id="additionalPaymentDisplay">₱0.00</span>
        // </div>

        window.editTransactionData = isEdit ? data : null;

        const dentistSelect = modalBody.querySelector("#transactionDentist");
        const servicesContainer = modalBody.querySelector("#servicesContainer");
        const appointmentServiceIds = data?.appointment_service_ids || [];
        const appointmentDentistId = data?.dentist_id || window.appointmentDentistId || null;
        const effectiveBranchId = data?.branch_id || branchId;
        const mcFields = modalBody.querySelector("#medicalCertFields");
        const paymentMethodSelect = modalBody.querySelector("#paymentMethod");
        const cashlessGroup = modalBody.querySelector("#cashlessReceiptGroup");
        const cashlessInput = modalBody.querySelector("#cashlessReceipt");
        const selectedServices = data?.services?.map(s => s.service_id) || [];

        loadDentists(effectiveBranchId, [], dentistSelect, appointmentDentistId);

        function toggleCashlessField() {
            if (paymentMethodSelect.value === "Cashless") {
                cashlessGroup.style.display = "block";
                cashlessInput.required = false;
            } else {
                cashlessGroup.style.display = "none";
                cashlessInput.value = "";
            }
        }
        toggleCashlessField();
        paymentMethodSelect.addEventListener("change", toggleCashlessField);

        function getMedicalCertCheckbox() {
            return [...servicesContainer.querySelectorAll('input[name="appointmentServices[]"]')].find(cb => {
                return cb.closest("label")?.textContent?.toLowerCase()?.includes("certificate");
            });
        }

        function toggleMedicalCertFields() {
            const mc = getMedicalCertCheckbox();

            if (mc && mc.checked) {
                mcFields.innerHTML = `
                    <div class="form-group">
                        <input class="form-control"
                            name="fitness_status"
                            required
                            value="${data?.fitness_status || ""}">
                        <label class="form-label">
                            Period of Rest <span class="required">*</span>
                        </label>
                    </div>

                    <div class="form-group">
                        <input class="form-control"
                            name="diagnosis"
                            value="${data?.diagnosis || ""}">
                        <label class="form-label">Diagnosis</label>
                    </div>

                    <div class="form-group">
                        <textarea class="form-control"
                                name="remarks"
                                rows="2">${data?.remarks || ""}</textarea>
                        <label class="form-label">Remarks</label>
                    </div>
                `;
            } else {
                mcFields.innerHTML = "";
            }
        }

        function renderXrayField(existingFile = null) {
            const xrayContainer = modalBody.querySelector("#xrayFields");

            let preview = "";
            const isRequired = !existingFile;

            if (existingFile) {
                preview = `
                    <div class="xray-preview">
                        <p>Current file:</p>
                        <img src="${BASE_URL}/${existingFile}" class="xray-img"
                            style="max-width:200px;margin-top:10px;">
                        <div style="margin:10px 0;">
                            <button type="button" class="confirm-btn"
                                onclick="removeXrayFile('${existingFile}')">Remove</button>
                        </div>
                    </div>
                `;
            }

            xrayContainer.innerHTML = `
                <div class="form-group">
                    <input type="file"
                        name="xray_file"
                        class="form-control"
                        accept="image/*,.pdf"
                        ${isRequired ? "required" : ""}>
                    <label class="form-label">
                        Upload X-Ray ${isRequired ? '<span class="required">*</span>' : ''}
                    </label>
                </div>
                ${preview}
            `;
        }

        function updateXrayVisibility() {
            const selected = [...document.querySelectorAll('input[name="appointmentServices[]"]:checked')];
            const requiresXray = selected.some(cb => cb.dataset.requiresXray === "1");

            const xrayContainer = document.getElementById("xrayFields");

            if (requiresXray) {
                const existing = isEdit ? data.xray_file : null;
                renderXrayField(existing);
                xrayContainer.style.display = "block";
            } else {
                xrayContainer.innerHTML = "";
                xrayContainer.style.display = "none";
            }
        }
        window.updateXrayVisibility = updateXrayVisibility;

        if (!isEdit) {
            loadServices(effectiveBranchId, servicesContainer, null, appointmentServiceIds, appointmentId, () => {
                const selected = [...servicesContainer.querySelectorAll('input[name="appointmentServices[]"]:checked')].map(cb => cb.value);
                loadDentists(effectiveBranchId, selected, dentistSelect, appointmentDentistId);
                updateServicesSummary();
                toggleMedicalCertFields();
                updateXrayVisibility();
            });
        } else {
            loadServices(effectiveBranchId, servicesContainer, data.dental_transaction_id, selectedServices, null, () => {
                loadDentists(effectiveBranchId, [], dentistSelect, data.dentist_id);

                selectedServices.forEach(id => {
                    const checkbox = servicesContainer.querySelector(`input[value="${id}"]`);
                    if (checkbox) {

                        checkbox.checked = true;

                        const s = data.services.find(s => s.service_id == id);

                        const qtyInput = servicesContainer.querySelector(`input[name="serviceQuantity[${id}]"]`);
                        if (qtyInput) {
                            qtyInput.disabled = false;
                            qtyInput.style.display = "inline-block";
                            qtyInput.value = s?.quantity || 1;
                        }

                        const extraField = document.querySelector(`input[name="additional_payment[${id}]"]`);
                        if (extraField) {
                            extraField.value = s?.add_payment_per_services || 0;
                        }
                    }
                });

                updateServicesSummary();
                toggleMedicalCertFields();
                updateXrayVisibility();
            });
        }

        const promoSelect = modalBody.querySelector("#transactionPromo");
        if (promoSelect) loadPromos(promoSelect, isEdit ? data.promo_id : null, effectiveBranchId);

        document.body.addEventListener("input", e => {
            if (e.target.matches('input[name^="serviceQuantity"]') ||
                e.target.matches('input[name="appointmentServices[]"]')) {
                toggleMedicalCertFields();
                updateServicesSummary();
            }
        });

        document.body.addEventListener("change", e => {
            if (e.target.matches('input[name="appointmentServices[]"]')) {
                updateXrayVisibility();
            }
        });
    }

    function updateServicesSummary() {
        const serviceCheckboxes = document.querySelectorAll('#servicesContainer input[name="appointmentServices[]"]');
        const services = [];

        serviceCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const serviceId = checkbox.value;
                const name = checkbox.parentElement.textContent.trim();

                const price = parseFloat(
                    checkbox.closest(".checkbox-item")
                    .querySelector(".price").textContent.replace(/[₱,]/g, "")
                ) || 0;

                const quantityInput = document.querySelector(`input[name="serviceQuantity[${serviceId}]"]`);
                const quantity = parseInt(quantityInput?.value || 1);

                const extraInput = document.querySelector(`input[name="additional_payment[${serviceId}]"]`);
                const extra = parseFloat(extraInput?.value || 0);

                services.push({ name, price, quantity, extra });
            }
        });

        const promoSelect = document.getElementById("transactionPromo");
        const selectedPromo = promoSelect ? promoSelect.selectedOptions[0] : null;

        const discountType = selectedPromo?.dataset.discountType || null;
        const discountValue = parseFloat(selectedPromo?.dataset.discountValue || 0);

        updateCheckoutSummary({
            services,
            discountType,
            discountValue
        });
    }

    function updateCheckoutSummary({ services = [], discountType = null, discountValue = 0 }) {
        const servicesList = document.getElementById("servicesList");
        const subtotalEl = document.getElementById("subtotalDisplay");
        // const extraEl = document.getElementById("additionalPaymentDisplay");
        const discountEl = document.getElementById("discountDisplay");
        const totalEl = document.getElementById("totalDisplay");
        const totalPaymentInput = document.getElementById("total_payment");

        servicesList.innerHTML = "";

        let subtotal = 0;
        let extrasSum = 0;

        services.forEach(service => {
            const lineTotal = service.price * service.quantity;

            subtotal += lineTotal;
            extrasSum += service.extra;

            const item = document.createElement("div");
            item.classList.add("summary-item");

            item.innerHTML = `
                <span>${service.name} × ${service.quantity}</span>
                <span class="service-price-group">
                    <span class="service-price">
                        ₱${lineTotal % 1 === 0 ? lineTotal : lineTotal.toFixed(2)}
                    </span>
                    ${service.extra > 0 ? `
                        <span class="service-extra">
                            +₱${service.extra % 1 === 0 ? service.extra : service.extra.toFixed(2)}
                        </span>
                    ` : ""}
                </span>
            `;

            servicesList.appendChild(item);
        });

        let discount = 0;

        if (discountType === "fixed") {
            discount = discountValue;
        }

        if (discountType === "percent" || discountType === "percentage") {
            const discountBase = subtotal + extrasSum;
            discount = discountBase * (discountValue / 100);
        }

        const total = Math.max(subtotal - discount + extrasSum, 0);

        function formatMoney(val) {
            return val % 1 === 0 ? val : val.toFixed(2);
        }

        subtotalEl.textContent = `₱${formatMoney(subtotal + extrasSum)}`;
        // extraEl.textContent = `₱${formatMoney(extrasSum)}`;
        discountEl.textContent = `₱-${discount.toFixed(2)}`;
        totalEl.textContent = `₱${total.toFixed(2)}`;
        totalPaymentInput.value = total.toFixed(2);
    }

    function loadServices(branchId, container, transactionId = null, appointmentServiceIds = [], appointmentId = null, callback = null, editServiceIds = []) {
        container.innerHTML = '<p class="loading-text">Loading services</p>';

        $.ajax({
            type: "POST",
            url: `${BASE_URL}/processes/load_services.php`,
            data: {
                appointmentBranch: branchId,
                appointment_transaction_id: transactionId,
                appointment_id: appointmentId,
                hide_duration: true 
            },
            success: function (response) {
                container.innerHTML = response;

                const checkboxes = container.querySelectorAll('input[name="appointmentServices[]"]');
                const dentistSelect = document.getElementById("transactionDentist");

                if (!transactionId && !editServiceIds.length && appointmentServiceIds.length > 0) {
                    checkboxes.forEach(cb => {
                        if (appointmentServiceIds.includes(cb.value)) {
                            cb.checked = true;

                            const qtyInput = container.querySelector(`input[name="serviceQuantity[${cb.value}]"]`);
                            if (qtyInput) {
                                qtyInput.style.display = "inline-block";

                                const service = window.appointmentServices?.find(s => s.service_id == cb.value);
                                qtyInput.value = service?.quantity || 1;
                            }
                        }
                    });
                }

                if (transactionId !== null && editServiceIds.length > 0) {
                    checkboxes.forEach(cb => {
                        cb.checked = editServiceIds.includes(parseInt(cb.value));
                    });
                }

                checkboxes.forEach(cb => {
                    cb.addEventListener("change", () => {

                        const qtyInput = container.querySelector(`input[name="serviceQuantity[${cb.value}]"]`);
                        if (qtyInput) {
                            qtyInput.style.display = cb.checked ? "inline-block" : "none";
                        }

                        rebuildExtrasSection();
                        updateServicesSummary();
                        updateXrayVisibility();
                    });
                });
                const extrasContainer = document.getElementById("serviceExtrasContainer");

                function rebuildExtrasSection() {
                    const extrasContainer = document.getElementById("serviceExtrasContainer");

                    const existingValues = {};
                    extrasContainer.querySelectorAll("input[name^='additional_payment']").forEach(input => {
                        const id = input.name.match(/\[(\d+)\]/)[1];
                        existingValues[id] = input.value;
                    });

                    extrasContainer.innerHTML = "";

                    const checked = [...document.querySelectorAll('input[name="appointmentServices[]"]:checked')];

                    checked.forEach(cb => {
                        const serviceId = cb.value;
                        const serviceName = cb.dataset.serviceName;

                        let savedExtra = 0;

                        if (existingValues[serviceId] !== undefined) {
                            savedExtra = existingValues[serviceId];
                        } else if (window.editTransactionData?.services) {
                            const srv = window.editTransactionData.services.find(s => s.service_id == serviceId);
                            savedExtra = srv ? (srv.extra ?? srv.additional_payment ?? 0) : 0;
                        }

                        extrasContainer.innerHTML += `
                            <div class="service-extra-block">
                                <h4>${serviceName}</h4>

                                <div class="form-group">
                                    <input type="number"
                                        class="form-control service-extra-input"
                                        name="additional_payment[${serviceId}]"
                                        min="0" step="0.01"
                                        value="${savedExtra}">
                                    <label class="form-label">Additional Payment</label>
                                </div>
                            </div>
                        `;
                    });

                    extrasContainer.querySelectorAll("input")
                        .forEach(el => el.addEventListener("input", updateServicesSummary));
                }

                rebuildExtrasSection();

                checkboxes.forEach(cb => {
                    cb.addEventListener("change", () => {
                        rebuildExtrasSection();
                        updateServicesSummary();
                    });
                });

                if (callback) callback();
            }
        });
    }

    function loadDentists(branchId, serviceIds = [], dentistSelect, selectedId = null) {
        dentistSelect.innerHTML = '<option disabled>Loading dentists...</option>';

        $.ajax({
            type: "POST",
            url: `${BASE_URL}/Admin/processes/manage_appointment/transactions/load_dentists_transaction.php`,
            data: {
                appointmentBranch: branchId,
                appointmentServices: serviceIds
            },
            success: function (response) {
                dentistSelect.innerHTML = response.trim();

                if (selectedId) {
                    dentistSelect.value = selectedId;
                }
            },
            error: function () {
                dentistSelect.innerHTML = '<option disabled>Error loading dentists</option>';
            }
        });
    }

    function loadPromos(promoSelect, selectedId = null, branchId = null) {
        $.ajax({
            type: "GET",
            url: `${BASE_URL}/processes/load_promos.php`,
            data: { branch_id: branchId || window.branchId || null },
            dataType: "json",
            success: function (promos) {
                promoSelect.innerHTML = '<option value="">None</option>';

                promos.forEach(p => {
                    const opt = document.createElement("option");
                    opt.value = p.id;
                    opt.dataset.discountType = p.discount_type;
                    opt.dataset.discountValue = p.discount_value;

                    let discountLabel = "";
                    if (p.discount_type === "percent" || p.discount_type === "percentage") {
                        discountLabel = ` (${parseFloat(p.discount_value).toFixed(2)}% OFF)`;
                    } else if (p.discount_type === "fixed") {
                        discountLabel = ` (₱${parseFloat(p.discount_value).toFixed(2)} OFF)`;
                    }

                    opt.textContent = `${p.name}${discountLabel}`;
                    if (selectedId && selectedId == p.id) opt.selected = true;
                    promoSelect.appendChild(opt);
                });

                promoSelect.addEventListener("change", updateServicesSummary);
                updateServicesSummary();
            },
            error: function (xhr, status, error) {
                console.error("Promo load failed:", status, error);
                promoSelect.innerHTML = '<option disabled>Error loading promos</option>';
            }
        });
    }

    function renderVitalForm(data) {
        const isEdit = !!data;

        modalBody.innerHTML = `
            <h2>${isEdit ? "Manage Vitals" : "Add Vitals"}</h2>
            <form id="vitalForm" 
                action="${BASE_URL}/Admin/processes/manage_appointment/vitals/${isEdit ? 'update_vital.php' : 'insert_vital.php'}" 
                method="POST" autocomplete="off" />
                
                ${isEdit ? `<input type="hidden" name="vitals_id" value="${data.vitals_id}">` : ""}
                <input type="hidden" name="appointment_transaction_id" value="${appointmentId}">
                <input type="hidden" name="admin_user_id" value="${userId}" readonly required>

                <div class="form-group">
                    <input type="number" step="0.1" id="bodyTemp" class="form-control" name="body_temp"
                        value="${isEdit ? data.body_temp : ""}"  placeholder=" "required />
                    <label for="bodyTemp" class="form-label">Body Temperature (°C) <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <input type="number" id="pulseRate" class="form-control" name="pulse_rate"
                        value="${isEdit ? data.pulse_rate : ""}" placeholder=" " required />
                    <label for="pulseRate" class="form-label">Pulse Rate (bpm) <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <input type="number" id="respiratoryRate" class="form-control" name="respiratory_rate"
                        value="${isEdit ? data.respiratory_rate : ""}" placeholder=" " required />
                    <label for="respiratoryRate" class="form-label">Respiratory Rate (rpm) <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <input type="text" id="bloodPressure" class="form-control" name="blood_pressure"
                        value="${isEdit ? data.blood_pressure : ""}" placeholder=" "  required autocomplete="off" />
                    <label for="bloodPressure" class="form-label">Blood Pressure (e.g., 120/80)<span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <input type="number" step="0.1" id="height" class="form-control" name="height"
                        value="${isEdit ? data.height : ""}" placeholder=" " required />
                    <label for="height" class="form-label">Height (cm) <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <input type="number" step="0.1" id="weight" class="form-control" name="weight"
                        value="${isEdit ? data.weight : ""}" placeholder=" " required />
                    <label for="weight" class="form-label">Weight (kg) <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <select id="isSwelling" class="form-control" name="is_swelling" required>
                        <option value="No" ${isEdit && data.is_swelling === "No" ? "selected" : ""}>No</option>
                        <option value="Yes" ${isEdit && data.is_swelling === "Yes" ? "selected" : ""}>Yes</option>
                    </select>
                    <label for="isSwelling" class="form-label">Swelling <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <select id="isSensitive" class="form-control" name="is_sensitive" required>
                        <option value="No" ${isEdit && data.is_sensitive === "No" ? "selected" : ""}>No</option>
                        <option value="Yes" ${isEdit && data.is_sensitive === "Yes" ? "selected" : ""}>Yes</option>
                    </select>
                    <label for="isSensitive" class="form-label">Sensitive <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <select id="isBleeding" class="form-control" name="is_bleeding" required>
                        <option value="No" ${isEdit && data.is_bleeding === "No" ? "selected" : ""}>No</option>
                        <option value="Yes" ${isEdit && data.is_bleeding === "Yes" ? "selected" : ""}>Yes</option>
                    </select>
                    <label for="isBleeding" class="form-label">Bleeding <span class="required">*</span></label>
                </div>

                ${isEdit ? `
                <div class="form-group">
                    <input type="text" id="recordedBy" class="form-control" value="${data.recorded_by}" disabled>
                    <label for="recordedBy" class="form-label">Recorded by:</label>
                </div>` : ""}

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

                <div class="button-group">
                    <button type="submit" class="form-button confirm-btn">${isEdit ? "Update Vitals" : "Save Vitals"}</button>
                    <button type="button" class="form-button cancel-btn" onclick="closeManageModal()">Cancel</button>
                </div>
            </form>
        `;
    }

    function renderPrescriptionForm(data) {
        const isEdit = !!data;

        modalBody.innerHTML = `
            <h2>${isEdit ? "Manage Prescription" : "Add Prescription"}</h2>
            <form id="prescriptionForm" 
                action="${BASE_URL}/Admin/processes/manage_appointment/prescriptions/${isEdit ? 'update_prescription.php' : 'insert_prescription.php'}" 
                method="POST" autocomplete="off" />
                
                ${isEdit ? `<input type="hidden" name="prescription_id" value="${data.prescription_id}">` : ""}
                <input type="hidden" name="appointment_transaction_id" value="${appointmentId}">
                <input type="hidden" name="admin_user_id" value="${userId}" readonly required>

                <div class="form-group">
                    <input type="text" id="drug" class="form-control" name="drug"
                        value="${isEdit ? data.drug : ""}" placeholder=" " required />
                    <label for="drug" class="form-label">Drug <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <input type="text" id="frequency" class="form-control" name="frequency"
                        value="${isEdit ? data.frequency : ""}" placeholder=" " required />
                    <label for="frequency" class="form-label">Frequency <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <input type="text" id="dosage" class="form-control" name="dosage"
                        value="${isEdit ? data.dosage : ""}" placeholder=" " required />
                    <label for="dosage" class="form-label">Dosage <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <input type="text" id="duration" class="form-control" name="duration"
                        value="${isEdit ? data.duration : ""}" placeholder=" " required />
                    <label for="duration" class="form-label">Duration <span class="required">*</span></label>
                </div>

                
                <div class="form-group">
                    <input type="text" id="quantity" class="form-control" name="quantity"
                        value="${isEdit ? data.quantity : ""}" placeholder=" " required />
                    <label for="quantity" class="form-label">Quantity <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <textarea id="instructions" class="form-control" name="instructions" rows="3" placeholder=" ">${isEdit ? data.instructions : ""}</textarea>
                    <label for="instructions" class="form-label">Instructions</label>
                </div>

                ${isEdit ? `
                <div class="form-group">
                    <input type="text" id="recordedBy" class="form-control" value="${data.recorded_by}" disabled>
                    <label for="recordedBy" class="form-label">Recorded by:</label>
                </div>` : ""}

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

                <div class="button-group">
                    <button type="submit" class="form-button confirm-btn">${isEdit ? "Update Prescription" : "Save Prescription"}</button>
                    <button type="button" class="form-button cancel-btn" onclick="closeManageModal()">Cancel</button>
                </div>
            </form>
        `;
    }
});

function closeManageModal() {
    document.getElementById("manageRecordModal").style.display = "none";
}

function removeXrayFile(path) {
    document.getElementById("remove_xray").value = "1";

    const preview = document.querySelector(".xray-preview");
    if (preview) preview.remove();
}

function removeReceiptPreview(filePath) {

    const hidden = document.getElementById("removed_receipt");
    hidden.value = filePath;

    const preview = document.querySelector(".receipt-preview");
    if (preview) preview.remove();

    const input = document.getElementById("cashlessReceipt");
    if (input) input.value = "";
}