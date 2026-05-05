document.addEventListener("DOMContentLoaded", () => {
    const appointmentModal = document.getElementById("manageModal");
    const appointmentBody = document.getElementById("modalBody");

    const transactionModal = document.getElementById("transactionModal");
    const transactionBody = document.getElementById("transactionModalBody");

    if (!appointmentModal && !transactionModal) return;

    document.body.addEventListener("click", function (e) {
        if (e.target.classList.contains("btn-action")) {
            const id = e.target.getAttribute("data-id");
            const type = e.target.getAttribute("data-type");

            let url = "";
            if (type === "appointment") {
                url = `${BASE_URL}/Admin/processes/manage_patient/get_appointment_details.php?id=${id}`;
            } else if (type === "transaction") {
                url = `${BASE_URL}/Admin/processes/manage_patient/get_dental_transaction_details.php?id=${id}`;
            }

            fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    if (type === "appointment") {
                        appointmentBody.innerHTML = `<p style="color:red;">${data.error}</p>`;
                        appointmentModal.style.display = "block";
                    } else {
                        transactionBody.innerHTML = `<p style="color:red;">${data.error}</p>`;
                        transactionModal.style.display = "block";
                    }
                    return;
                }

                if (type === "appointment") {
                    appointmentBody.innerHTML = `
                        <h2>Appointment Details</h2>
                        <p><strong>Dentist:</strong><span>${data.dentist ?? 'Available Dentist'}</span></p>
                        <p><strong>Branch:</strong><span>${data.branch}<span></p>
                        <p><strong>Service:</strong><span>${data.services || '-'}</p>
                        <p><strong>Date:</strong><span>${
                            data.appointment_date
                                ? new Date(data.appointment_date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
                                : ''
                        }</span></p>
                        <p><strong>Time:</strong><span>${
                            data.appointment_time
                                ? new Date(`1970-01-01T${data.appointment_time}`).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true })
                                : ''
                        }</span></p>
                        <p><strong>Notes:</strong><span>${data.notes || '-'}</span></p>
                        <p><strong>Status:</strong><span>${data.status}<span></p>
                        <p><strong>Date Booked:</strong><span>${
                            data.date_created
                                ? new Date(data.date_created).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
                                : '-'
                        }</span></p>
                    `;
                    appointmentModal.style.display = "block";
                }

                else if (type === "transaction") {

                    // ===== PRESCRIPTIONS VALIDITY =====
                    let prescriptionButtonHtml = "";

                    const hasPrescriptions = data.prescriptions && data.prescriptions.length > 0;

                    if (!hasPrescriptions) {
                        prescriptionButtonHtml = `
                            <div class="button-group button-group-profile">
                                <button class="confirm-btn" id="downloadPrescription" disabled>No Prescription Available</button>
                            </div>
                        `;
                    } else {
                        prescriptionButtonHtml = `
                            <div class="button-group button-group-profile">
                                <button class="confirm-btn" id="downloadPrescription">Download Prescription</button>
                            </div>
                        `;
                    }

                    transactionBody.innerHTML = `
                        <div class="transaction-columns">
                            <div class="transaction-section">
                                <h3>Dental Transaction</h3>
                                <p><strong>Dentist:</strong><span>${data.dentist_name}</span></p>
                                <p><strong>Branch:</strong><span>${data.branch}</span></p>
                                <p><strong>Service:</strong><span>${data.services_text || '-'}</span></p>
                                <p><strong>Date:</strong><span>${
                                    data.appointment_date
                                        ? new Date(data.appointment_date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
                                        : ''
                                }</span></p>
                                <p><strong>Time:</strong><span>${
                                    data.appointment_time
                                        ? new Date(`1970-01-01T${data.appointment_time}`).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true })
                                        : ''
                                }</span></p>
                                <p><strong>Amount Paid:</strong><span>${data.total}</span></p>
                                <p><strong>Additional:</strong><span>${data.additional_payment || '-'}</span></p>
                                <p><strong>Method:</strong><span>${data.payment_method}</span></p>
                                <p><strong>Notes:</strong><span>${data.notes || '-'}</span></p>
                                <p><strong>Prepared by:</strong><span>${data.admin_name || '-'}</span></p>
                                <p><strong>Date Recorded:</strong><span>${
                                    data.date_created
                                        ? new Date(data.date_created).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
                                        : '-'
                                }</span></p>
                                <p><strong>Certificate Request Date:</strong>
                                    <span>${
                                        data.medcert_requested_date
                                            ? new Date(data.medcert_requested_date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
                                            : '-'
                                    }</span>
                                </p>
                                <p><strong>Certificate Notes:</strong><span>${data.medcert_notes || '-'}</span></p>

                                <div class="button-group button-group-profile">
                                    <button class="confirm-btn" id="downloadReceipt">Download Receipt</button>

                                    ${
                                        data.payment_method === "Cashless" && data.cashless_receipt
                                            ? `<button class="confirm-btn" id="viewCashlessReceipt" data-id="${data.dental_transaction_id}">View Cashless Receipt</button>`
                                            : ""
                                    }

                                    ${
                                        data.medcert_status === 'None'
                                            ? `<button class="confirm-btn issue-medcert-btn" id="issueMedicalCertificate" data-id="${data.dental_transaction_id}">Issue Dental Certificate</button>`
                                            : data.medcert_status === 'Requested'
                                                ? `<button class="confirm-btn issue-medcert-btn" id="issueMedicalCertificate" data-id="${data.dental_transaction_id}">Issue Dental Certificate</button>`
                                                : data.medcert_status === 'Eligible'
                                                    ? `<button class="confirm-btn" id="downloadMedicalCertificate">Download Dental Certificate</button>`
                                                    : data.medcert_status === 'Issued'
                                                        ? `<button class="confirm-btn issued-btn" id="viewMedCertReceipt" data-id="${data.dental_transaction_id}">Issued</button>`
                                                        : data.medcert_status === 'Expired'
                                                            ? `<button class="confirm-btn expired-btn" id="viewMedCertReceipt" data-id="${data.dental_transaction_id}">Expired</button>`
                                                            : ''
                                    }
                                </div>
                            </div>

                            <div class="transaction-section">
                                <h3>Vitals</h3>
                                <p><strong>Body Temp:</strong><span>${data.body_temp}</span></p>
                                <p><strong>Pulse Rate:</strong><span>${data.pulse_rate}</span></p>
                                <p><strong>Respiratory Rate:</strong><span>${data.respiratory_rate}</span></p>
                                <p><strong>Blood Pressure:</strong><span>${data.blood_pressure}</span></p>
                                <p><strong>Height:</strong><span>${data.height}</span></p>
                                <p><strong>Weight:</strong><span>${data.weight}</span></p>
                                <p><strong>Swelling:</strong><span>${data.is_swelling}</span></p>
                                <p><strong>Sensitivity:</strong><span>${data.is_sensitive}</span></p>
                                <p><strong>Bleeding:</strong><span>${data.is_bleeding}</span></p>

                                <div class="button-group button-group-profile" style="margin-top:10px;">
                                    ${
                                        data.xray_results && data.xray_results.length > 0
                                            ? `<button class="confirm-btn" id="viewXrayResult" data-id="${data.dental_transaction_id}">View Xray Result</button>`
                                            : ``
                                    }
                                </div>
                            </div>

                            <div class="transaction-section">
                                <h3>Prescription</h3>
                                <div id="prescriptionList"></div>

                                <div class="button-group button-group-profile">
                                    ${prescriptionButtonHtml}
                                </div>
                            </div>
                        </div>
                    `;

                    // ===== PRESCRIPTIONS LIST =====
                    let prescriptionHtml = '';
                    if (data.prescriptions && data.prescriptions.length > 0) {
                        data.prescriptions.forEach(p => {
                            prescriptionHtml += `
                                <div class="prescription-item">
                                    <p><strong>Drug:</strong><span>${p.drug}</span></p>
                                    <p><strong>Frequency:</strong><span>${p.frequency}</span></p>
                                    <p><strong>Dosage:</strong><span>${p.dosage}</span></p>
                                    <p><strong>Duration:</strong><span>${p.duration}</span></p>
                                    <p><strong>Quantity:</strong><span>${p.quantity}</span></p>
                                    <p><strong>Instructions:</strong><span>${p.instructions}</span></p>
                                    <hr>
                                </div>
                            `;
                        });
                    } else {
                        prescriptionHtml = `<p>No prescriptions recorded.</p>`;
                    }

                    document.getElementById("prescriptionList").innerHTML = prescriptionHtml;
                    transactionModal.style.display = "block";

                    async function addPdfHeader(doc, BASE_URL, getBase64ImageFromUrl, data) {

                        // ===== LOGO =====
                        const logoUrl = `${BASE_URL}/images/logo/logo_default.png`;
                        const logoBase64 = await getBase64ImageFromUrl(logoUrl);
                        doc.addImage(logoBase64, "PNG", 10, 10, 50, 30);

                        // ===== CLINIC NAME =====
                        doc.setFontSize(14);
                        doc.setFont("helvetica", "bold");
                        doc.text("Arriesgado Dental Clinic", 105, 15, { align: "center" });

                        // ===== BRANCHES =====
                        const response = await fetch(`${BASE_URL}/Admin/processes/manage_patient/get_branches.php`);
                        const branchesData = await response.json();
                        const branchesText = branchesData.branches ? branchesData.branches.join(" • ") : "-";

                        doc.setFontSize(12);
                        doc.setFont("helvetica", "normal");
                        doc.text(branchesText, 105, 25, { align: "center" });

                        // ===== CLINIC HOURS =====
                        doc.setFontSize(11);
                        doc.text("Clinic Hours: 9AM - 3PM | Mon–Sun (All Branches)", 105, 35, { align: "center" });

                        // Top divider
                        doc.line(10, 45, 200, 45);

                        // ===== PATIENT INFO =====
                        const fullName = `${data.patient_last_name}, ${data.patient_first_name} ${data.patient_middle_name ? data.patient_middle_name[0] + "." : ""}`;

                        let age = "-";
                        if (data.patient_dob) {
                            const dob = new Date(data.patient_dob);
                            age = Math.floor((Date.now() - dob.getTime()) / (1000 * 60 * 60 * 24 * 365.25)) + " yrs";
                        }

                        const formattedDate = data.date_created
                            ? new Date(data.date_created).toLocaleDateString("en-US", {
                                year: "numeric",
                                month: "long",
                                day: "numeric"
                            })
                            : "-";

                        doc.setFontSize(12);

                        const leftX = 10;
                        const rightX = 120;

                        let y = 55;

                        doc.setFont("helvetica", "bold");
                        doc.text("Patient:", leftX, y);
                        doc.setFont("helvetica", "normal");
                        doc.text(fullName, leftX + 30, y);

                        doc.setFont("helvetica", "bold");
                        doc.text("Age:", rightX, y);
                        doc.setFont("helvetica", "normal");
                        doc.text(age, rightX + 20, y);

                        y += 7;

                        doc.setFont("helvetica", "bold");
                        doc.text("Branch:", leftX, y);
                        doc.setFont("helvetica", "normal");
                        doc.text(data.branch || "-", leftX + 30, y);

                        doc.setFont("helvetica", "bold");
                        doc.text("Date Issued:", rightX, y);
                        doc.setFont("helvetica", "normal");
                        doc.text(formattedDate, rightX + 30, y);

                        y += 6;
                        doc.line(10, y, 200, y);

                        return { y, formattedDate };
                    }

                    // ========= DOWNLOAD RECEIPT =========
                    const receiptBtn = document.getElementById("downloadReceipt");
                    if (receiptBtn) {
                        receiptBtn.addEventListener("click", async () => {
                            const { jsPDF } = window.jspdf;
                            const doc = new jsPDF();

                            async function getBase64ImageFromUrl(url) {
                                const res = await fetch(url);
                                const blob = await res.blob();
                                return new Promise((resolve, reject) => {
                                    const reader = new FileReader();
                                    reader.onloadend = () => resolve(reader.result);
                                    reader.onerror = reject;
                                    reader.readAsDataURL(blob);
                                });
                            }

                            const header = await addPdfHeader(doc, BASE_URL, getBase64ImageFromUrl, data);
                            let y = header.y;

                            // ===== SERVICE TABLE =====
                            let services = [];

                            try {
                                if (Array.isArray(data.services)) {
                                    services = data.services;
                                } else if (typeof data.services === "string" && data.services.trim() !== "") {
                                    try {
                                        services = JSON.parse(data.services);
                                    } catch {
                                        services = data.services.split(/[,|\n]/).map(s => ({
                                            service_name: s.trim(),
                                            quantity: 1,
                                            price: 0,
                                            subtotal: 0
                                        }));
                                    }
                                }
                            } catch (err) {
                                console.warn("Failed to parse services:", err);
                            }

                            const colService = 20;
                            const colQty     = 80;
                            const colUnit    = 110;
                            const colAdd     = 145;
                            const colSub     = 190;

                            y += 6;
                            doc.setFont("helvetica", "bold");
                            doc.text("Service", colService, y);
                            doc.text("Qty", colQty, y, { align: "center" });
                            doc.text("Unit Price", colUnit, y, { align: "right" });
                            doc.text("Add. Pay", colAdd, y, { align: "right" });
                            doc.text("Subtotal", colSub, y, { align: "right" });

                            y += 4;
                            doc.line(20, y, 190, y);
                            y += 5;

                            doc.setFont("helvetica", "normal");
                            let tableSubtotal  = 0;

                            if (services.length > 0) {
                                const serviceMaxWidth = 55;

                                services.forEach((s) => {
                                    const name = s.service_name || "-";
                                    const qty = s.quantity ?? 1;
                                    const price = s.service_price ?? 0;
                                    const addPay = Number(s.additional_payment ?? 0);
                                    const lineTotal = (qty * price) + addPay;
                                    tableSubtotal  += lineTotal;

                                    const wrappedName = doc.splitTextToSize(name, serviceMaxWidth);
                                    doc.text(wrappedName, colService, y);

                                    doc.text(String(qty), colQty, y, { align: "center" });
                                    doc.text(Number(price).toLocaleString(), colUnit, y, { align: "right" });
                                    doc.text(addPay.toLocaleString(), colAdd, y, { align: "right" });
                                    doc.text(lineTotal.toLocaleString(), colSub, y, { align: "right" });

                                    y += (wrappedName.length * 6);
                                });
                            } else {
                                doc.text("No services recorded.", 20, y);
                                y += 6;
                            }

                            y += 3;
                            doc.line(20, y, 190, y);
                            y += 8;

                            // ===== PAYMENT SUMMARY =====
                            const labelX = 130;
                            const valueX = 190;

                            // Use DB service subtotals
                            const subtotal = data.services_raw.reduce((sum, s) => sum + Number(s.subtotal), 0);

                            doc.setFont("helvetica", "bold");
                            doc.text("Subtotal:", labelX, y);
                            doc.setFont("helvetica", "normal");
                            doc.text(subtotal.toLocaleString(), valueX, y, { align: "right" });
                            y += 6;

                            // === DISCOUNT ===
                            let discountValue = 0;

                            if (data.promo) {
                                const type = data.promo.discount_type;
                                const value = Number(data.promo.discount_value);

                                let discountLabel = "Discount";

                                if (type === "percentage") {
                                    discountValue = subtotal * (value / 100);
                                    discountLabel = `Discount (${value}%)`;
                                } 
                                else if (type === "fixed") {
                                    discountValue = value;
                                    discountLabel = `Discount`;
                                }

                                doc.setFont("helvetica", "bold");
                                doc.text(`${discountLabel}:`, labelX, y);

                                doc.setFont("helvetica", "normal");
                                doc.text(
                                    `- ${discountValue.toLocaleString(undefined, { 
                                        minimumFractionDigits: 2, 
                                        maximumFractionDigits: 2 
                                    })}`,
                                    valueX,
                                    y,
                                    { align: "right" }
                                );
                                y += 6;
                            }

                            // ===== GRAND TOTAL =====
                            const grandTotal = Number(data.total);

                            doc.setFont("helvetica", "bold");
                            doc.text("Total Payment:", labelX, y);

                            doc.setFont("helvetica", "normal");
                            doc.text(
                                grandTotal.toLocaleString(undefined, { 
                                    minimumFractionDigits: 2, 
                                    maximumFractionDigits: 2 
                                }),
                                valueX,
                                y,
                                { align: "right" }
                            );
                            y += 12;

                            // ===== PREPARED INFO =====
                            doc.text(`Prepared By: ${data.admin_name || "-"}`, 20, y);
                            y += 6;

                            const transDate = data.date_created
                                ? new Date(data.date_created).toLocaleDateString("en-US", {
                                    year: "numeric",
                                    month: "long",
                                    day: "numeric"
                                })
                                : "-";

                            // ===== SAVE FILE =====
                            const safeName = (data.patient_last_name || "patient").replace(/\s+/g, "_");
                            const safeDate = (data.date_created ? data.date_created.split(" ")[0] : "unknown");
                            const fileName = `${safeName}_${safeDate}_receipt.pdf`;
                            doc.save(fileName);
                        });
                    }

                    // ========= DOWNLOAD Dental Certificate =========
                    const medCertBtn = document.getElementById("downloadMedicalCertificate");
                    if (medCertBtn) {
                        medCertBtn.addEventListener("click", async () => {
                            const { jsPDF } = window.jspdf;
                            const doc = new jsPDF();
                            const pageHeight = doc.internal.pageSize.getHeight();

                            async function getBase64ImageFromUrl(url) {
                                const res = await fetch(url);
                                const blob = await res.blob();
                                return new Promise((resolve, reject) => {
                                    const reader = new FileReader();
                                    reader.onloadend = () => resolve(reader.result);
                                    reader.onerror = reject;
                                    reader.readAsDataURL(blob);
                                });
                            }

                            const header = await addPdfHeader(doc, BASE_URL, getBase64ImageFromUrl, data);
                            let y = header.y + 20;
                            let formattedDate = header.formattedDate;

                            const diagnosis = data.diagnosis?.trim() || "(not specified)";

                            const services = Array.isArray(data.services)
                                ? data.services.map(s => s.service_name)
                                : [];

                            const restText = data.fitness_status?.trim() || "No rest period necessary.";

                            const remarks = data.remarks?.trim() || "None";

                            // ===== MAIN CERTIFICATE BODY =====
                            doc.setFont("helvetica", "normal");
                            doc.setFontSize(12);

                            doc.text(
                                `This is to certify that this patient was examined and treated at Arriesgado Dental Clinic on ${formattedDate} and was diagnosed with ${diagnosis}.`,
                                20, y,
                                { maxWidth: 170, align: "justify" }
                            );
                            y += 20;

                            // ===== SERVICES RENDERED =====
                            let filtered = [];

                            if (Array.isArray(services)) {
                                filtered = services
                                    .map(s => s.trim())
                                    .filter(s => s !== "" && !/Dental Certificate/i.test(s));
                            }

                            filtered = filtered.map(name =>
                                name.replace(/\s*[×x]\s*\d+$/i, "").trim()
                            );

                            if (filtered.length > 0) {
                                doc.setFont("helvetica", "bold");
                                doc.text(
                                    "The patient has undergone the following dental procedure(s):",
                                    20, y,
                                    { maxWidth: 170 }
                                );

                                y += 8;
                                doc.setFont("helvetica", "normal");

                                filtered.forEach(name => {
                                    const wrapped = doc.splitTextToSize(name, 150);

                                    doc.text(`• ${wrapped[0]}`, 30, y);
                                    y += 7;

                                    for (let i = 1; i < wrapped.length; i++) {
                                        doc.text(wrapped[i], 35, y);
                                        y += 7;
                                    }
                                });

                                y += 12;
                            }

                            // ===== PERIOD OF REST =====
                            doc.setFont("helvetica", "bold");
                            doc.text("Period of Rest:", 20, y);
                            y += 7;

                            doc.setFont("helvetica", "normal");
                            const restLines = doc.splitTextToSize(restText, 160);
                            doc.text(restLines, 25, y);
                            y += restLines.length * 7 + 12;

                            // ===== REMARKS =====
                            doc.setFont("helvetica", "bold");
                            doc.text("Remarks:", 20, y);
                            y += 7;

                            doc.setFont("helvetica", "normal");
                            const remarkLines = doc.splitTextToSize(remarks, 160);
                            doc.text(remarkLines, 25, y);
                            y += remarkLines.length * 7 + 12;

                            // ===== FOOTER =====
                            doc.setFont("helvetica", "normal");
                            doc.text(
                                "This certificate is issued upon the patient’s request for whatever legal or personal purpose it may serve.",
                                20, y,
                                { maxWidth: 170, align: "justify" }
                            );

                            y += 20;

                            doc.text(`Issued this ${formattedDate}.`, 20, y);

                            // ===== SIGNATURE =====
                            if (data.dentist_last_name || data.dentist_first_name) {

                                let sigY = 250;

                                if (data.signature_image) {
                                    try {
                                        const sigUrl = `${BASE_URL}/images/dentists/signature/${data.signature_image}`;
                                        const sigBase64 = await getBase64ImageFromUrl(sigUrl);
                                        doc.addImage(sigBase64, "PNG", 125, sigY - 25, 50, 25);
                                    } catch (err) {}
                                }

                                doc.line(120, sigY, 200, sigY);

                                const dentistFullName = `${data.dentist_first_name} ${
                                    data.dentist_middle_name ? data.dentist_middle_name[0] + '. ' : ''
                                }${data.dentist_last_name}`;

                                doc.text("Dr. " + dentistFullName, 160, sigY + 10, { align: "center" });
                                doc.text("License No: " + (data.license_number ?? "-"), 160, sigY + 20, { align: "center" });
                            }

                            // ===== SAVE FILE =====
                            const safeName = (data.patient_last_name || "patient").replace(/\s+/g, "_");
                            const safeDate = (data.date_created ? data.date_created.split(" ")[0] : "unknown");
                            const fileName = `${safeName}_${safeDate}_dental_certificate.pdf`;
                            doc.save(fileName);

                            // ===== UPDATE STATUS TO ISSUED =====
                            try {
                                const updateResponse = await fetch(`${BASE_URL}/Admin/processes/manage_patient/update_medcert_status.php`, {
                                    method: "POST",
                                headers: { "Content-Type": "application/json" },
                                    body: JSON.stringify({
                                        dental_transaction_id: data.dental_transaction_id,
                                        new_status: "Issued"
                                    })
                                });

                                const updateResult = await updateResponse.json();

                                if (updateResult.success) {
                                    medCertBtn.textContent = "Issued";
                                    medCertBtn.disabled = true;
                                    medCertBtn.classList.add("issued-btn");
                                } else {
                                    console.warn("Failed to update Certificate status:", updateResult.error);
                                }
                            } catch (error) {
                                console.error("Error updating Certificate status:", error);
                            }
                        });
                    }

                    // ========= DOWNLOAD PRESCRIPTION =========
                    const btn = document.getElementById("downloadPrescription");
                    if (btn) {
                        btn.addEventListener("click", async () => {
                            const { jsPDF } = window.jspdf;
                            const doc = new jsPDF();

                            async function getBase64ImageFromUrl(url) {
                                const res = await fetch(url);
                                const blob = await res.blob();
                                return new Promise((resolve, reject) => {
                                    const reader = new FileReader();
                                    reader.onloadend = () => resolve(reader.result);
                                    reader.onerror = reject;
                                    reader.readAsDataURL(blob);
                                });
                            }

                            const header = await addPdfHeader(doc, BASE_URL, getBase64ImageFromUrl, data);
                            let y = header.y;

                            // ===== PRESCRIPTIONS =====
                            const pageHeight = doc.internal.pageSize.getHeight();

                            doc.setFont("helvetica", "bold");
                            doc.text("Prescription:", 10, y + 5);

                            doc.setFontSize(26);
                            doc.setFont("helvetica", "bolditalic");
                            doc.text("Rx", 10, y + 22);

                            doc.setFontSize(12);
                            doc.setFont("helvetica", "normal");

                            y += 35;

                            if (data.prescriptions && data.prescriptions.length > 0) {

                            data.prescriptions.forEach((p, index) => {

                                if (y > pageHeight - 50) {
                                    doc.addPage();
                                    y = 20;
                                }

                                const dosageText = p.dosage ? ` ${p.dosage}` : "";

                                const qtyText = p.quantity ? ` – Qty: ${p.quantity}` : "";

                                const mainLine = `${index + 1}. ${p.drug}${dosageText}${qtyText}`;
                                doc.text(mainLine, 10, y);
                                y += 9;

                                let freqText = "";
                                if (p.frequency) {
                                    if (isNaN(p.frequency)) {
                                        freqText = p.frequency;
                                    } else {
                                        freqText = `${p.frequency} times a day`;
                                    }
                                }

                                let durationText = "";
                                if (p.duration) {
                                    if (isNaN(p.duration)) {
                                        durationText = p.duration;
                                    } else {
                                        durationText = `${p.duration} day/s`;
                                    }
                                }

                                if (freqText || durationText) {
                                    doc.text(`Take ${freqText}${durationText ? " for " + durationText : ""}`, 15, y);
                                    y += 9;
                                }

                                if (p.instructions) {
                                    const wrapped = doc.splitTextToSize(`Notes: ${p.instructions}`, 170);
                                    doc.text(wrapped, 15, y);
                                    y += wrapped.length * 7;
                                }

                                y += 7;
                            });

                            } else {
                                doc.text("No prescriptions recorded.", 10, y);
                                y += 10;
                            }

                            // ===== SIGNATURE =====
                            if (data.dentist_last_name || data.dentist_first_name) {
                                let sigY = y + 5;
                                if (sigY < 60) sigY = 60;

                                if (sigY > pageHeight - 80) {
                                    doc.addPage();
                                    sigY = 50;
                                }

                                const sigUrl = `${BASE_URL}/images/dentists/signature/${data.signature_image}`;
                                let hasSignature = false;

                                if (data.signature_image) {
                                    try {
                                        const sigBase64 = await getBase64ImageFromUrl(sigUrl);
                                        doc.addImage(sigBase64, "PNG", 125, sigY, 50, 30);
                                        hasSignature = true;
                                    } catch (err) {}
                                }

                                const lineY = hasSignature ? sigY + 35 : sigY + 25;
                                doc.line(120, lineY, 200, lineY);

                                const nameY = lineY + 10;
                                const licenseY = lineY + 20;

                                const dentistFullName = `${data.dentist_first_name} ${
                                    data.dentist_middle_name ? data.dentist_middle_name[0] + '. ' : ''
                                }${data.dentist_last_name}`;

                                doc.text("Dr. " + dentistFullName, 160, nameY, { align: "center" });
                                doc.text("License No: " + (data.license_number ?? "-"), 160, licenseY, { align: "center" });
                            }

                            // ===== PAGE NUMBERS =====
                            const pageCount = doc.internal.getNumberOfPages();
                            for (let i = 1; i <= pageCount; i++) {
                                doc.setPage(i);
                                doc.setFontSize(10);
                                doc.setFont("helvetica", "normal");
                                doc.text(`Page ${i} of ${pageCount}`, 105, pageHeight - 10, { align: "center" });
                            }

                            // Save PDF
                            const safeName = (data.patient_last_name || "patient").replace(/\s+/g, "_");
                            const safeDate = (data.date_created ? data.date_created.split(" ")[0] : "unknown");
                            const fileName = `${safeName}_${safeDate}_prescription.pdf`;
                            doc.save(fileName);
                        });
                    }
                }
            })
            .catch(err => {
                if (type === "appointment") {
                    appointmentBody.innerHTML = `<p style="color:red;">Error loading details</p>`;
                    appointmentModal.style.display = "block";
                } else {
                    transactionBody.innerHTML = `<p style="color:red;">Error loading details</p>`;
                    transactionModal.style.display = "block";
                }
            });
        }
    });

    window.onclick = (e) => {
        if (e.target == appointmentModal) {
            appointmentModal.style.display = "none";
        }
        if (e.target == transactionModal) {
            transactionModal.style.display = "none";
        }
    };

    document.body.addEventListener("click", async function (e) {
        if (e.target && e.target.id === "issueMedicalCertificate") {
            const medCertModal = document.getElementById("medCertModal");
            const transactionId = e.target.getAttribute("data-id");
            const transactionInput = document.getElementById("transactionIdInput");
            const receiptImage = document.getElementById("receiptImage");
            const receiptPreview = document.getElementById("receiptPreview");
            const paymentSection = document.getElementById("paymentSection");
            const paymentMethod = document.getElementById("paymentMethod");
            const receiptUpload = document.getElementById("receiptUpload");

            if (transactionInput) {
                transactionInput.value = transactionId;
            }

            fetch(`${BASE_URL}/Admin/processes/manage_patient/get_medcert_details.php?id=${transactionId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById("fitnessStatus").value = data.fitness_status || "";
                        document.getElementById("diagnosis").value = data.diagnosis || "";
                        document.getElementById("remarks").value = data.remarks || "";

                        if (data.medcert_receipt) {
                            receiptImage.src = `${BASE_URL}${data.medcert_receipt}`;
                            receiptPreview.style.display = "flex";
                            paymentSection.style.display = "none";
                            paymentMethod.required = false;
                            paymentMethod.disabled = true;
                            receiptUpload.required = false;
                            receiptUpload.disabled = true;
                        } else {
                            receiptPreview.style.display = "none";
                            paymentSection.style.display = "block";
                            paymentMethod.disabled = false;
                            paymentMethod.required = true;
                            receiptUpload.disabled = false;
                            receiptUpload.required = (paymentMethod.value === "cashless");
                        }
                    } else {
                        console.warn("No Certificate details found:", data.error);
                        receiptPreview.style.display = "none";
                        paymentSection.style.display = "block";
                        paymentMethod.disabled = false;
                        paymentMethod.required = true;
                        receiptUpload.disabled = false;
                        receiptUpload.required = false;
                    }
                })
                .catch(err => console.error("Error loading Certificate details:", err));

            if (medCertModal) {
                medCertModal.style.display = "block";
            }
        }
    });

    document.addEventListener("change", (e) => {
        if (e.target.id === "paymentMethod") {
            const uploadGroup = document.getElementById("receiptUploadGroup");
            const receiptUpload = document.getElementById("receiptUpload");

            if (e.target.value === "cashless") {
                uploadGroup.style.display = "block";
                receiptUpload.required = false;
            } else {
                uploadGroup.style.display = "none";
                receiptUpload.required = false;
            }
        }
    });

    window.addEventListener("click", (e) => {
        const medCertModal = document.getElementById("medCertModal");
        if (e.target === medCertModal) {
            medCertModal.style.display = "none";
        }
    });

    document.body.addEventListener("click", async function (e) {
        if (e.target && e.target.id === "viewCashlessReceipt") {
            const transactionId = e.target.getAttribute("data-id");

            try {
                const response = await fetch(`${BASE_URL}/Admin/processes/manage_patient/get_cashless_receipt.php?id=${transactionId}`);
                const data = await response.json();

                if (data.success && data.file_path) {
                    const imgUrl = `${BASE_URL}${data.file_path}`;
                    const modal = document.getElementById("medCertReceiptModal");
                    const modalBody = document.getElementById("medCertReceiptBody");

                    modalBody.innerHTML = `
                        <h2>Cashless Payment Receipt</h2>
                        ${
                            imgUrl.toLowerCase().endsWith('.pdf')
                                ? `<iframe src="${imgUrl}" style="width:80%;height:600px;margin:auto;display:block;border:none;"></iframe>`
                                : `<img src="${imgUrl}" alt="Cashless Receipt" style="width:50%;display:block;margin:auto;border-radius:4px;">`
                        }
                    `;
                    modal.style.display = "flex";
                } else {
                    alert("No cashless receipt available.");
                }
            } catch (error) {
                console.error("Error loading cashless receipt:", error);
            }
        }
    });

    document.body.addEventListener("click", async function (e) {
        if (e.target && e.target.id === "viewMedCertReceipt") {
            const transactionId = e.target.getAttribute("data-id");

            try {
                const response = await fetch(`${BASE_URL}/Admin/processes/manage_patient/get_medcert_receipt.php?id=${transactionId}`);
                const data = await response.json();

                if (data.success && data.file_path) {
                    const imgUrl = `${BASE_URL}${data.file_path}`;
                    const modal = document.getElementById("medCertReceiptModal");
                    const modalBody = document.getElementById("medCertReceiptBody");

                    modalBody.innerHTML = `
                        <h2>Dental Certificate Payment Receipt</h2>
                        <img src="${imgUrl}" alt="Dental Certificate Receipt" style="width:50%;display:block;margin:auto;border-radius:4px;">
                    `;
                    modal.style.display = "flex";
                }
            } catch (error) {
                console.error("Error loading receipt:", error);
            }
        }
    });

    document.body.addEventListener("click", async function (e) {
        if (e.target && e.target.id === "viewXrayResult") {
            const transactionId = e.target.getAttribute("data-id");

            try {
                const response = await fetch(`${BASE_URL}/Admin/processes/manage_patient/get_xray_results.php?id=${transactionId}`);
                const data = await response.json();

                if (data.success && data.files.length > 0) {
                    const modal = document.getElementById("medCertReceiptModal");
                    const modalBody = document.getElementById("medCertReceiptBody");

                    let html = `<h2>X-ray Result</h2>`;

                    data.files.forEach((item) => {
                        const file = item.file_path;

                        const createdDate = item.date_created
                            ? new Date(item.date_created).toLocaleDateString('en-US', { 
                                month: 'long', 
                                day: 'numeric', 
                                year: 'numeric' 
                            })
                            : "Unknown Date";

                        let fixedPath = file.startsWith("/") ? file : "/" + file;
                        const fileUrl = `${BASE_URL}${fixedPath}`;
                        const isPdf = file.toLowerCase().endsWith(".pdf");

                        html += `
                            <div style="margin:20px 0; text-align:center;">
                                <p style="margin-top:-10px; color:#777; font-size:14px;">${createdDate}</p>
                                ${
                                    isPdf
                                    ? `<iframe src="${fileUrl}" style="width:80%; height:500px; border:none;"></iframe>`
                                    : `<img src="${fileUrl}" style="width:100%; border-radius:5px; display:block; margin:auto;">`
                                }
                            </div>
                        `;
                    });

                    modalBody.innerHTML = html;
                    modal.style.display = "flex";

                } else {
                    alert("No X-ray results available.");
                }

            } catch (error) {
                console.error("Error loading x-ray results:", error);
            }
        }
    });
});