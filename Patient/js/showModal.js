document.addEventListener("DOMContentLoaded", () => {
    const appointmentModal = document.getElementById("manageModal");
    const appointmentBody = document.getElementById("modalBody");

    const transactionModal = document.getElementById("transactionModal");
    const transactionBody = document.getElementById("transactionModalBody");

    document.body.addEventListener("click", function(e) {
        if (e.target.classList.contains("btn-action")) {
            const id = e.target.getAttribute("data-id");
            const type = e.target.getAttribute("data-type");

            let url = "";
            if (type === "appointment") {
                url = `${BASE_URL}/Patient/processes/profile/get_appointment_details.php?id=${id}`;
            } else if (type === "transaction") {
                url = `${BASE_URL}/Patient/processes/profile/get_dental_transaction_details.php?id=${id}`;
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

                        // ===== MEDCERT VALIDITY =====
                        let medCertButtonHtml = "";
                        let medCertExpired = false;

                        if (data.date_created) {
                            const createdDate = new Date(data.date_created);
                            const now = new Date();
                            const diffDays = (now - createdDate) / (1000 * 60 * 60 * 24);
                            medCertExpired = diffDays >= 7;
                        }

                        if (medCertExpired && data.medcert_status !== 'Expired') {
                            medCertButtonHtml = `<button class="confirm-btn expired-btn" disabled>Expired</button>`;
                        } else {
                            medCertButtonHtml =
                                data.medcert_status === 'None'
                                    ? `<button class="confirm-btn" id="requestMedicalCertificate" data-id="${data.dental_transaction_id}">Request Dental Certificate</button>`
                                    : data.medcert_status === 'Requested'
                                        ? `<button class="confirm-btn pending-btn" disabled>Pending</button>`
                                        : data.medcert_status === 'Eligible'
                                            ? `<button class="confirm-btn" id="downloadMedicalCertificate">Download Dental Certificate</button>`
                                            : data.medcert_status === 'Issued'
                                                ? `<button class="confirm-btn issued-btn" id="viewMedCertReceipt" data-id="${data.dental_transaction_id}">Issued</button>`
                                                : data.medcert_status === 'Expired'
                                                    ? `<button class="confirm-btn expired-btn" id="viewMedCertReceipt" data-id="${data.dental_transaction_id}">Expired</button>`
                                                    : '';
                        }

                        // ===== PRESCRIPTIONS VALIDITY =====
                        let prescriptionButtonHtml = "";

                        const hasPrescriptions = data.prescriptions && data.prescriptions.length > 0;

                        let isExpired = false;
                        if (data.date_created) {
                            const createdDate = new Date(data.date_created);
                            const now = new Date();
                            const diffYears = (now - createdDate) / (1000 * 60 * 60 * 24 * 365);
                            isExpired = diffYears >= 1;
                        }

                        if (!hasPrescriptions) {
                            prescriptionButtonHtml = `<button class="confirm-btn" disabled>No Prescription Available</button>`;
                        } else if (isExpired) {
                            prescriptionButtonHtml = `<button class="confirm-btn expired-btn" disabled>Expired</button>`;
                        } else if (data.prescription_downloaded == 0) {
                            prescriptionButtonHtml = `<button class="confirm-btn" id="downloadPrescription">Download Prescription</button>`;
                        } else {
                            prescriptionButtonHtml = `<button class="confirm-btn" disabled>Issued</button>`;
                        }

                        transactionBody.innerHTML = `
                            <div class="transaction-columns">
                                <div class="transaction-section">
                                    <h3>Dental Transaction</h3>
                                    <p><strong>Dentist:</strong><span>${data.dentist ?? 'Available Dentist'}</span></p>
                                    <p><strong>Branch:</strong><span>${data.branch}</span></p>
                                    <p><strong>Service:</strong><span>${data.services}</span></p>
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
                                        ${medCertButtonHtml}
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
                                                ? `<button class="confirm-btn" id="viewXrayResult" data-id="${data.dental_transaction_id}">View Xray Results</button>`
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

                                // ===== SERVICES / PROCEDURES =====
                                let filtered = [];

                                if (data.services) {
                                    filtered = data.services
                                        .split("\n")
                                        .map(name => name.trim())
                                        .filter(name => name !== "" && !/Dental Certificate/i.test(name));
                                }

                                filtered = filtered.map(name =>
                                    name.replace(/\s*[×x]\s*\d+$/i, "").trim()
                                );

                                if (filtered.length > 0) {
                                    doc.setFont("helvetica", "bold");
                                    doc.text(
                                        "The patient underwent the following dental procedure(s):",
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

                                    y += 10;
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
                                    const updateResponse = await fetch(`${BASE_URL}/Patient/processes/profile/update_medcert_status.php`, {
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

                                // === UNIFIED HEADER ===
                                const header = await addPdfHeader(doc, BASE_URL, getBase64ImageFromUrl, data);
                                let y = header.y + 10;

                                // ===== LABEL =====
                                doc.setFont("helvetica", "bold");
                                doc.text("Prescription:", 10, y);

                                doc.setFont("helvetica", "bolditalic");
                                doc.setFontSize(26);
                                doc.text("Rx", 10, y + 18);

                                doc.setFontSize(12);
                                doc.setFont("helvetica", "normal");

                                y += 32;

                                // ===== PRESCRIPTION LIST =====
                                if (data.prescriptions?.length > 0) {
                                    data.prescriptions.forEach((p, index) => {
                                        if (y > pageHeight - 60) {
                                            doc.addPage();
                                            y = 20;
                                        }

                                        const qtyText = p.quantity ? ` – Qty: ${p.quantity}` : "";

                                        doc.text(`${index + 1}. ${p.drug} ${p.dosage || ""}${qtyText}`, 10, y);
                                        y += 8;

                                        let freqText = "";
                                        if (p.frequency) {
                                            freqText = isNaN(p.frequency)
                                                ? p.frequency
                                                : `${p.frequency} times a day`;
                                        }

                                        let durationText = "";
                                        if (p.duration) {
                                            durationText = isNaN(p.duration)
                                                ? p.duration
                                                : `${p.duration} day(s)`;
                                        }

                                        if (freqText || durationText) {
                                            doc.text(`Take ${freqText}${durationText ? " for " + durationText : ""}`, 15, y);
                                            y += 8;
                                        }

                                        if (p.instructions) {
                                            const wrapped = doc.splitTextToSize(`Notes: ${p.instructions}`, 170);
                                            doc.text(wrapped, 15, y);
                                            y += wrapped.length * 7;
                                        }

                                        y += 6;
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

                                // ===== SAVE PDF =====
                                const safeName = (data.patient_last_name || "patient").replace(/\s+/g, "_");
                                const safeDate = data.date_created?.split(" ")[0] || "unknown";
                                doc.save(`${safeName}_${safeDate}_prescription.pdf`);

                                // ===== UPDATE STATUS (ISSUED) =====
                                try {
                                    const res = await fetch(
                                        `${BASE_URL}/Patient/processes/profile/update_prescription_status.php`,
                                        {
                                            method: "POST",
                                            headers: { "Content-Type": "application/json" },
                                            body: JSON.stringify({
                                                transaction_id: data.dental_transaction_id
                                            })
                                        }
                                    );

                                    const json = await res.json();

                                    if (json.success) {
                                        btn.textContent = "Issued";
                                        btn.disabled = true;
                                    } else {
                                        console.error("Update failed:", json.error);
                                        window.location.href = `${BASE_URL}/Patient/processes/profile/update_prescription_status.php?error=1`;
                                    }
                                } catch (err) {
                                    console.error("Update fetch error:", err);
                                    window.location.href = `${BASE_URL}/Patient/processes/profile/update_prescription_status.php?error=1`;
                                }
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

    document.body.addEventListener("click", function(e) {
        if (e.target && e.target.id === "requestMedicalCertificate") {
            const medCertModal = document.getElementById("medCertModal");
            const transactionId = e.target.getAttribute("data-id");
            document.getElementById("transactionIdInput").value = transactionId;
            medCertModal.style.display = "block";
        }
    });

    window.addEventListener("click", (e) => {
        const medCertModal = document.getElementById("medCertModal");
        if (e.target === medCertModal) {
            medCertModal.style.display = "none";
        }
    });

    document.body.addEventListener("click", async function (e) {
        if (e.target && e.target.id === "viewMedCertReceipt") {
            const transactionId = e.target.getAttribute("data-id");

            try {
                const response = await fetch(`${BASE_URL}/Patient/processes/profile/get_medcert_receipt.php?id=${transactionId}`);
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
                const response = await fetch(`${BASE_URL}/Patient/processes/profile/get_xray_results.php?id=${transactionId}`);
                const data = await response.json();

                if (data.success && data.files.length > 0) {
                    const modal = document.getElementById("medCertReceiptModal");
                    const modalBody = document.getElementById("medCertReceiptBody");

                    let html = `<h2>X-ray Result</h2>`;

                    data.files.forEach((item) => {
                        const file = item.file_path;

                        const createdDate = item.date_created
                            ? new Date(item.date_created).toLocaleDateString("en-US", {
                                month: "long",
                                day: "numeric",
                                year: "numeric",
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
