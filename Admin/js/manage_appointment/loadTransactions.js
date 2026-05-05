document.addEventListener("DOMContentLoaded", () => {
    if (typeof appointmentId === "undefined" || !appointmentId) {
        return;
    }

    waitForAppointmentId((appointmentId) => {
        initTables(appointmentId);
    });
});

function waitForAppointmentId(callback, retries = 10) {
    if (typeof appointmentId !== "undefined" && appointmentId !== null && appointmentId !== "") {
        callback(appointmentId);
    } else if (retries > 0) {
        setTimeout(() => waitForAppointmentId(callback, retries - 1), 200);
    } else {
    }
}

function initTables(appointmentId) {
    $('#dentaltransactionTable').DataTable({
        destroy: true,
        ajax: `${BASE_URL}/Admin/processes/manage_appointment/transactions/load_transactions.php?appointment_id=${appointmentId}`,
        pageLength: 20,
        lengthChange: false,
        ordering: true,
        searching: false,
        columns: [
            { title: "ID" },
            { title: "Dentist" },
            { title: "Service" },
            { title: "Method" },
            { title: "Total" },
            { title: "Action", orderable: false },
        ],
        language: {
            emptyTable: "No transactions found for this appointment.",
            loadingRecords: "Loading transactions..."
        },
        initComplete: function () {
            $('#dentaltransactionTable_filter').remove();
            $('#dentaltransactionTable_wrapper .dataTables_length').remove();
            $('#dentaltransactionTable_wrapper').prepend(`
                <div class="table-action-once">
                    <button id="insertTransactionBtn" data-appointment-id="${appointmentId}">
                        + Add
                    </button>
                </div>
            `);
            checkTransactionStatus();
        }
    });

    $('#vitalTable').DataTable({
        destroy: true,
        ajax: `${BASE_URL}/Admin/processes/manage_appointment/vitals/load_vitals.php?appointment_id=${appointmentId}`,
        pageLength: 20,
        lengthChange: false,
        ordering: true,
        searching: false,
        columns: [
            { title: "ID" },
            { title: "Body Temp" },
            { title: "Pulse Rate" },
            { title: "Blood Pressure" },
            { title: "Action", orderable: false },
        ],
        language: {
            emptyTable: "No vital records found.",
            loadingRecords: "Loading vital records..."
        },
        initComplete: function () {
            $('#vitalTable_filter').remove();
            $('#vitalTable_wrapper .dataTables_length').remove();
            $('#vitalTable_wrapper').prepend(`
                <div class="table-action-once">
                    <button id="insertVitalBtn">+ Add</button>
                </div>
            `);
            checkVitalStatus();
        }
    });

    $('#prescriptionTable').DataTable({
        destroy: true,
        ajax: `${BASE_URL}/Admin/processes/manage_appointment/prescriptions/load_prescriptions.php?appointment_id=${appointmentId}`,
        pageLength: 20,
        lengthChange: false,
        ordering: true,
        searching: false,
        columns: [
            { title: "ID" },
            { title: "Drug" },
            { title: "Dosage" },
            { title: "Frequency" },
            { title: "Duration" },
            { title: "Action", orderable: false },
        ],
        language: {
            emptyTable: "No prescriptions found.",
            loadingRecords: "Loading prescriptions..."
        },
        initComplete: function () {
            $('#prescriptionTable_filter').remove();
            $('#prescriptionTable_wrapper .dataTables_length').remove();
            $('#prescriptionTable_wrapper').prepend(`
                <div class="table-action">
                    <button id="insertPrescriptionBtn">+ Add</button>
                </div>
            `);
        }
    });
}

function checkTransactionStatus() {
    if (typeof appointmentId === "undefined" || !appointmentId) return;

    fetch(`${BASE_URL}/Admin/processes/manage_appointment/transactions/check_transaction_exists.php?appointment_id=${appointmentId}`)
        .then(res => res.json())
        .then(data => {
            const insertTransactionBtn = document.getElementById("insertTransactionBtn");
            if (!insertTransactionBtn) return;
            insertTransactionBtn.style.display = data.exists ? "none" : "inline-block";
        })
        .catch(err => console.error("Error checking transaction status:", err));
}

function checkVitalStatus() {
    if (typeof appointmentId === "undefined" || !appointmentId) return;

    fetch(`${BASE_URL}/Admin/processes/manage_appointment/vitals/check_vital_exists.php?appointment_id=${appointmentId}`)
        .then(res => res.json())
        .then(data => {
            const insertVitalBtn = document.getElementById("insertVitalBtn");
            if (!insertVitalBtn) return;
            insertVitalBtn.style.display = data.exists ? "none" : "inline-block";
        })
        .catch(err => console.error("Error checking vital status:", err));
}
