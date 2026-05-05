document.addEventListener("DOMContentLoaded", () => {
    const employeeModal = document.getElementById("manageModal");
    const employeeBody = document.getElementById("modalBody");

    document.body.addEventListener("click", function (e) {
        if (e.target.classList.contains("btn-action")) {
            const id = e.target.getAttribute("data-id");
            const type = e.target.getAttribute("data-type");

            let url = "";
            if (type === "admin") {
                url = `${BASE_URL}/Owner/processes/employees/get_admin_details.php?id=${id}`;
            } else if (type === "dentist") {
                url = `${BASE_URL}/Owner/processes/employees/get_dentist_details.php?id=${id}`;
            }

            fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    employeeBody.innerHTML = `<p style="color:red;">${data.error}</p>`;
                    employeeModal.style.display = "block";
                    return;
                }

                if (type === "admin") {
                    renderAdminForm(data);
                } else if (type === "dentist") {
                    renderDentistForm(data);
                }

                employeeModal.style.display = "block";
            })
            .catch(() => {
                employeeBody.innerHTML = `<p style="color:red;">Error loading details</p>`;
                employeeModal.style.display = "block";
            });
        }
    });

    document.body.addEventListener("click", function (e) {
        if (e.target.id === "addAdminBtn") {
            renderAdminForm(null);
            employeeModal.style.display = "block";
        }
        if (e.target.id === "addDentistBtn") {
            renderDentistForm(null);
            employeeModal.style.display = "block";
        }
    });

    function renderAdminForm(data) {
        const isEdit = !!data;
        employeeBody.innerHTML = `
            <h2>${isEdit ? "Manage Secretary" : "Add Secretary"}</h2>
            <form id="adminForm" action="${BASE_URL}/Owner/processes/employees/${isEdit ? "update_admin.php" : "insert_admin.php"}" method="POST" autocomplete="off">
                ${isEdit ? `<input type="hidden" name="user_id" value="${data.user_id}">` : ""}

                ${isEdit ? `
                <div class="form-group">
                    <input type="text" id="userName" class="form-control" value="${data.username}" disabled  autocomplete="off">
                    <label for="userName" class="form-label">Username</label>
                </div>` : ""}

                <div class="form-group">
                    <input type="text" id="lastName" name="lastName" class="form-control"
                        value="${isEdit ? data.last_name : ""}" required placeholder=" " autocomplete="off">
                    <label for="lastName" class="form-label">Last Name <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <input type="text" id="firstName" name="firstName" class="form-control"
                        value="${isEdit ? data.first_name : ""}" required placeholder=" " autocomplete="off">
                    <label for="firstName" class="form-label">First Name <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <input type="text" id="middleName" name="middleName" class="form-control"
                        value="${isEdit ? (data.middle_name || "") : ""}" placeholder=" " autocomplete="off">
                    <label for="middleName" class="form-label">Middle Name</label>
                </div>

                <div class="form-group">
                    <select id="gender" name="gender"  class="form-control" required>
                        <option value="" disabled selected hidden></option>
                        <option value="Male" ${isEdit && data.gender === "Male" ? "selected" : ""}>Male</option>
                        <option value="Female" ${isEdit && data.gender === "Female" ? "selected" : ""}>Female</option>
                    </select>
                    <label for="gender" class="form-label">Gender <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <input type="date" id="dateofBirth" name="dateofBirth" class="form-control"
                        value="${isEdit ? data.date_of_birth : ""}" required autocomplete="off">
                    <label for="dateofBirth" class="form-label">Date of Birth <span class="required">*</span></label>
                    <span id="dobError" class="error-msg-calendar error" style="display: none;"></span>
                </div>

                <div class="form-group">
                    <input type="email" id="email" name="email" class="form-control"
                        value="${isEdit ? data.email : ""}" required placeholder=" " autocomplete="off">
                    <label for="email" class="form-label">Email <span class="required">*</span></label>
                </div>

                <div class="form-group phone-group">
                    <input type="tel" id="contactNumber" name="contactNumber"  class="form-control"
                        value="${isEdit ? data.contact_number : ""}" oninput="this.value = this.value.replace(/[^0-9]/g, '')" pattern="[0-9]{10}" title="Mobile number must be 10 digits" required maxlength="10" autocomplete="off">
                    <label for="contactNumber" class="form-label">Mobile Number <span class="required">*</span></label>
                    <span class="phone-prefix">+63</span>
                </div>

                <div class="form-group">
                    <input type="text" id="address" name="address" class="form-control"
                        value="${isEdit ? data.address : ""}" required placeholder=" " autocomplete="off">
                    <label for="address" class="form-label">Address <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <select id="branchAssignment" name="branchAssignment" class="form-control" required>
                        <option value="" disabled selected hidden></option>
                    </select>
                    <label for="branchAssignment" class="form-label">Branch <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <select id="status" name="status" class="form-control" required>
                        <option value="" disabled selected hidden></option>
                        <option value="Active" ${isEdit && data.status === "Active" ? "selected" : ""}>Active</option>
                        <option value="Inactive" ${isEdit && data.status === "Inactive" ? "selected" : ""}>Inactive</option>
                    </select>
                    <label for="status" class="form-label">Status <span class="required">*</span></label>
                </div>
                            
                <div class="form-group">
                    <input  
                        type="date" 
                        id="dateStarted" 
                        name="dateStarted"
                        class="form-control"
                        value="${isEdit ? data.date_started : ''}"
                        ${isEdit && hasStarted(data.date_started) ? "disabled" : "required"}
                        autocomplete="off"
                    >
                    <label for="dateStarted" class="form-label">Start Date <span class="required">*</span></label>

                    ${isEdit && hasStarted(data.date_started)
                        ? `
                            <input type="hidden" name="dateStarted" value="${data.date_started}">
                            <small style="color:#999; font-size:0.85em;">
                                The staff has already started. Start date can no longer be edited.
                            </small>
                        `
                        : `
                            <span id="dateError" class="error-msg-calendar error" style="display:none;">
                                Sundays are not available for work. Please select another date.
                            </span>
                        `
                    }
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
                    <button type="submit" class="form-button confirm-btn">${isEdit ? "Save Changes" : "Add Secretary"}</button>
                    <button type="button" class="form-button cancel-btn" onclick="closeEmployeeModal()">Cancel</button>
                </div>
            </form>
        `;

        fetch(`${BASE_URL}/Owner/processes/employees/get_branches.php`)
        .then(res => res.json())
        .then(branches => {
            const branchSelect = document.getElementById("branchAssignment");
            branches.forEach(branch => {
                const option = document.createElement("option");
                option.value = branch.branch_id;
                option.textContent = branch.name;
                if (isEdit && branch.branch_id == data.branch_id) option.selected = true;
                branchSelect.appendChild(option);
            });
        });

        setTimeout(() => {
            const form = document.getElementById("adminForm");
            if (form) attachEmailValidator(form);
        }, 50);

        setTimeout(() => {
            const form = document.getElementById("adminForm");
            if (form) attachDateValidators(form);
        }, 60);

        setTimeout(() => {
            const form = document.getElementById("adminForm");
            if (form) attachSafeSubmit(form);
        }, 70);
    }

    const CHAIR_OCCUPANCY = {};
    let SCHEDULE_LOCKED = false;
    function renderDentistForm(data) {
        const isEdit = !!data;
        SCHEDULE_LOCKED = false;
        const selectedBranches = isEdit && data.branches ? data.branches.map(b => parseInt(b)) : [];
        const selectedServices = isEdit && data.services ? data.services.map(s => parseInt(s)) : [];
        
        employeeBody.innerHTML = `
            <h2>${isEdit ? "Manage Dentist" : "Add Dentist"}</h2>
            <form id="dentistForm" action="${BASE_URL}/Owner/processes/employees/${isEdit ? "update_dentist.php" : "insert_dentist.php"}" method="POST" enctype="multipart/form-data" autocomplete="off">
                ${isEdit ? `<input type="hidden" name="dentist_id" value="${data.dentist_id}">` : ""}

                <input type="hidden" name="confirmDentistUpdate" id="confirmDentistUpdate" value="0">
                <input type="hidden" id="originalStatus" value="${data.status}">

                <div class="form-group" style="position: relative; margin-bottom: 18px;">
                    <input type="file" id="profileImage" name="profileImage" class="form-control" accept="image/*" ${isEdit ? "" : ""}>
                    <label for="profileImage" class="form-label" style="display: block; margin-top: 6px; margin-bottom: 4px;">Profile Picture </label>
                    
                    ${isEdit && data.profile_image 
                        ? `<div class="mt-2" style="margin-top: 6px;">
                                <p style="margin-bottom: 4px;">Current Profile Picture:</p>
                                <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 6px;">
                                    <img src="${BASE_URL}/images/dentists/profile/${data.profile_image}" alt="Profile Image"
                                        style="max-width:150px; border:1px solid #ccc; padding:4px; border-radius:4px;; margin-bottom: 6px;">
                                    <button type="button" class="confirm-btn"
                                        style="width:150px; margin-top:4px;"
                                        onclick="clearImage('profileImage', 'profileCleared')">Remove</button>
                                </div>
                                <input type="hidden" name="profileCleared" id="profileCleared" value="0">
                        </div>`
                        : ""
                    }
                </div>

                <div class="form-group">
                    <input type="text" id="lastName" name="lastName" class="form-control"
                        value="${isEdit ? data.last_name : ""}" required placeholder=" " autocomplete="off">
                    <label for="lastName" class="form-label">Last Name <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <input type="text" id="firstName" name="firstName" class="form-control"
                        value="${isEdit ? data.first_name : ""}" required placeholder=" " autocomplete="off">
                    <label for="firstName" class="form-label">First Name <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <input type="text" id="middleName" name="middleName" class="form-control"
                        value="${isEdit ? (data.middle_name || "") : ""}" placeholder=" " autocomplete="off">
                    <label for="middleName" class="form-label">Middle Name</label>
                </div>

                <div class="form-group">
                    <select id="gender" name="gender" class="form-control" required>
                        <option value="" disabled selected hidden></option>
                        <option value="Male" ${isEdit && data.gender === "Male" ? "selected" : ""}>Male</option>
                        <option value="Female" ${isEdit && data.gender === "Female" ? "selected" : ""}>Female</option>
                    </select>
                    <label for="gender" class="form-label">Gender <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <input type="date" id="dateofBirth" name="dateofBirth" class="form-control"
                        value="${isEdit ? data.date_of_birth : ""}" required autocomplete="off">
                    <label for="dateofBirth" class="form-label">Date of Birth <span class="required">*</span></label>
                    <span id="dobError" class="error-msg-calendar error" style="display: none;">
                        Date of birth cannot be in the future.
                    </span>
                </div>

                <div class="form-group">
                    <input type="email" id="email" name="email" class="form-control"
                        value="${isEdit ? data.email : ""}" required placeholder=" " autocomplete="off">
                    <label for="email" class="form-label">Email <span class="required">*</span></label>
                </div>

                <div class="form-group phone-group">
                    <input type="tel" id="contactNumber" name="contactNumber" class="form-control" 
                        value="${isEdit ? data.contact_number : ""}" oninput="this.value = this.value.replace(/[^0-9]/g, '')" pattern="[0-9]{10}" title="Mobile number must be 10 digits" required maxlength="10" autocomplete="off">
                    <label for="contactNumber" class="form-label">Mobile Number <span class="required">*</span></label>
                    <span class="phone-prefix">+63</span>
                </div>

                <div class="form-group" style="position: relative; margin-bottom: 18px;">
                    <input type="file" id="signatureImage" name="signatureImage" class="form-control" accept="image/*">
                    <label for="signatureImage" class="form-label" style="display: block; margin-top: 6px; margin-bottom: 4px;">Signature Image </label>

                    ${isEdit && data.signature_image 
                        ? `<div class="mt-2" style="margin-top: 6px;">
                                <p style="margin-bottom: 4px;">Current Signature:</p>
                                <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 6px;">
                                    <img src="${BASE_URL}/images/dentists/signature/${data.signature_image}" alt="Signature"
                                        style="max-width:150px; border:1px solid #ccc; padding:4px; border-radius:4px;; margin-bottom: 6px;">
                                    <button type="button" class="confirm-btn"
                                        style="width:150px; margin:4px 0px 10px 0px;"
                                        onclick="clearImage('signatureImage', 'signatureCleared')">Remove</button>
                                </div>
                                <input type="hidden" name="signatureCleared" id="signatureCleared" value="0">
                        </div>`
                        : ""
                    }
                </div>

                <div class="form-group">
                    <input type="text" id="licenseNumber" name="licenseNumber" class="form-control"
                        value="${isEdit ? data.license_number : ""}" required placeholder=" " autocomplete="off">
                    <label for="licenseNumber" class="form-label">License Number <span class="required">*</span></label>
                </div>
                
                <div class="form-group">
                    <input  
                        type="date" 
                        id="dateStarted" 
                        name="dateStarted"
                        class="form-control"
                        value="${isEdit ? data.date_started : ''}"
                        ${isEdit && hasStarted(data.date_started) ? "disabled" : "required"}
                        autocomplete="off"
                    >
                    <label for="dateStarted" class="form-label">Start Date <span class="required">*</span></label>

                    ${isEdit && hasStarted(data.date_started)
                        ? `
                            <input type="hidden" name="dateStarted" value="${data.date_started}">
                            <small style="color:#999; font-size:0.85em;">
                                The staff has already started. Start date can no longer be edited.
                            </small>
                        `
                        : `
                            <span id="dateError" class="error-msg-calendar error" style="display:none;">
                                Sundays are not available for work. Please select another date.
                            </span>
                        `
                    }
                </div>

                <div class="form-group">
                    <select id="status" name="status" class="form-control" required>
                        <option value="" disabled selected hidden></option>
                        <option value="Active" ${isEdit && data.status === "Active" ? "selected" : ""}>Active</option>
                        <option value="Inactive" ${isEdit && data.status === "Inactive" ? "selected" : ""}>Inactive</option>
                    </select>
                    <label for="status" class="form-label">Status <span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <div id="branchAssignment" class="checkbox-group"></div>
                </div>

                <div class="form-group">
                    <div id="branchScheduleContainer" class="schedule-days-container"></div>
                </div>

                <div class="form-group">
                    <div id="servicesCheckboxes" class="checkbox-group"></div>
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
                    <button type="submit" class="form-button confirm-btn">${isEdit ? "Save Changes" : "Add Dentist"}</button>
                    <button type="button" class="form-button cancel-btn" onclick="closeEmployeeModal()">Cancel</button>
                </div>
            </form>
        `;

        fetch(`${BASE_URL}/Owner/processes/employees/get_branches.php`)
        .then(res => res.json())
        .then(branches => {

            const container = document.getElementById("branchAssignment");
            container.innerHTML = "";

            branches.forEach(branch => {
                const wrapper = document.createElement("div");
                wrapper.innerHTML = `
                    <div class="checkbox-item">
                        <input type="checkbox" id="branch_${branch.branch_id}" name="branches[]" value="${branch.branch_id}"
                            ${isEdit ? (selectedBranches.includes(parseInt(branch.branch_id)) ? "checked" : "") : "checked"}>
                        <label for="branch_${branch.branch_id}">${branch.name}</label>  
                    </div>
                `;
                container.appendChild(wrapper);
            });

            Promise.all(
                branches.map(branch =>
                    fetch(`${BASE_URL}/Owner/processes/employees/get_branch_chair_occupancy.php?branch_id=${branch.branch_id}`)
                        .then(res => res.json())
                        .then(data => {
                            CHAIR_OCCUPANCY[branch.branch_id] = data;
                        })
                )
            ).then(() => {
                rebuildAllTimeDropdowns();

                if (isEdit && data.status === "Active") {
                    setScheduleReadonly(true);
                }
            });

            const scheduleContainer = document.getElementById("branchScheduleContainer");
            scheduleContainer.innerHTML = "";

            const days = ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
            const savedSchedule =
                isEdit && data.branch_schedule && Object.keys(data.branch_schedule).length
                    ? data.branch_schedule
                    : {};

            days.forEach(day => {
                const dayWrapper = document.createElement("div");
                dayWrapper.classList.add("day-schedule-wrapper");
                dayWrapper.dataset.day = day;

                dayWrapper.innerHTML = `
                    <h4>${day}</h4>
                    <div class="schedule-rows" id="rows_${day}"></div>
                    <button type="button" class="add-schedule-btn" data-day="${day}">+ Add schedule</button>
                `;

                scheduleContainer.appendChild(dayWrapper);

                const rowsContainer = dayWrapper.querySelector(`#rows_${day}`);

                if (savedSchedule[day]) {
                    savedSchedule[day].forEach(entry => {
                        addScheduleRow(day, rowsContainer, branches, entry);
                        updateAddScheduleButton(day);
                    });
                }
                updateAddScheduleButton(day);
            });

            scheduleContainer.querySelectorAll(".add-schedule-btn").forEach(btn => {
                btn.addEventListener("click", () => {
                    if (SCHEDULE_LOCKED) return;
                    const day = btn.dataset.day;
                    const rowsContainer = document.getElementById(`rows_${day}`);
                    addScheduleRow(day, rowsContainer, branches);
                });
            });

            function addScheduleRow(day, rowsContainer, branches, saved = null) {

                const checkedBranches = Array.from(
                    document.querySelectorAll("#branchAssignment input[type=checkbox]:checked")
                ).map(cb => ({
                    branch_id: cb.value,
                    name: cb.nextElementSibling.textContent
                }));

                const startVal = saved ? (saved.start_time ?? "") : "";
                const endVal   = saved ? (saved.end_time ?? "") : "";

                const row = document.createElement("div");
                row.classList.add("schedule-row");

                row.innerHTML = `
                    <select name="schedule[${day}][branch][]" required>
                        <option value="" disabled ${!saved ? "selected" : ""}>Select Branch</option>
                        ${checkedBranches.map(b => `
                            <option value="${b.branch_id}" ${saved && saved.branch_id == b.branch_id ? "selected" : ""}>
                                ${b.name}
                            </option>
                        `).join("")}
                    </select>

                    <select class="start-time" name="schedule[${day}][start][]" required></select>
                    <select class="end-time" name="schedule[${day}][end][]" required></select>

                    <button type="button" class="remove-row-btn">×</button>
                `;

                const startInput = row.querySelector(".start-time");
                const endInput   = row.querySelector(".end-time");

                row.querySelector(".remove-row-btn").addEventListener("click", () => {
                    row.remove();
                    updateAddScheduleButton(day);
                    updateTimeDropdowns(day);
                });

                rowsContainer.appendChild(row);
                updateAddScheduleButton(day);

                buildTimeOptions(startInput, startVal);
                buildTimeOptions(endInput, endVal);

                startInput.addEventListener("change", () => updateTimeDropdowns(day));
                endInput.addEventListener("change", () => updateTimeDropdowns(day));

                const branchSelect = row.querySelector("select[name$='[branch][]']");
                branchSelect.addEventListener("change", () => {
                    updateBranchDropdowns();
                    buildTimeOptions(startInput);
                    buildTimeOptions(endInput);
                });

                setTimeout(updateBranchDropdowns, 10);
            }

            container.querySelectorAll("input[type=checkbox]").forEach(chk => {
                chk.addEventListener("change", () => {
                    updateBranchDropdowns();
                });
            });
        });

        fetch(`${BASE_URL}/Owner/processes/employees/get_services.php`)
        .then(res => res.json())
        .then(services => {
            const container = document.getElementById("servicesCheckboxes");
            container.innerHTML = "";

            const selectAllServices = document.createElement("div");
            selectAllServices.innerHTML = `
                <div class="checkbox-item">
                    <input type="checkbox" id="selectAllServices">
                    <label for="selectAllServices"><strong>Select All Services</strong></label>
                </div>
            `;
            container.appendChild(selectAllServices);

            services.forEach(service => {
                const wrapper = document.createElement("div");
                wrapper.innerHTML = `
                    <div class="checkbox-item">
                        <input type="checkbox" id="service_${service.service_id}" name="services[]" value="${service.service_id}"
                            ${isEdit ? (selectedServices.includes(parseInt(service.service_id)) ? "checked" : "") : "checked"}>
                        <label for="service_${service.service_id}">${service.name}</label>
                    </div>
                `;
                container.appendChild(wrapper);
            });
            
            function updateSelectAllServicesState() {
                const all = document.querySelectorAll("#servicesCheckboxes input[name='services[]']");
                const allChecked = [...all].every(cb => cb.checked);
                document.getElementById("selectAllServices").checked = allChecked;
            }

            document.querySelectorAll("#servicesCheckboxes input[name='services[]']").forEach(cb => {
                cb.addEventListener("change", () => {
                    updateSelectAllServicesState();
                });
            });

            updateSelectAllServicesState();

            document.getElementById("selectAllServices").addEventListener("change", function () {
                const checked = this.checked;

                document.querySelectorAll("#servicesCheckboxes input[name='services[]']")
                    .forEach(cb => cb.checked = checked);
            });
        });

        setTimeout(() => {
            const form = document.getElementById("dentistForm");
            if (form) attachEmailValidator(form);
        }, 50);

        setTimeout(() => {
            const form = document.getElementById("dentistForm");
            if (form) attachDateValidators(form);
        }, 60);
    }

    function rebuildAllTimeDropdowns() {
        document.querySelectorAll(".schedule-row").forEach(row => {
            const start = row.querySelector(".start-time");
            const end   = row.querySelector(".end-time");

            const startVal = start.value;
            const endVal   = end.value;

            buildTimeOptions(start, startVal);
            buildTimeOptions(end, endVal);
        });

        ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"]
            .forEach(updateTimeDropdowns);
    }

    function formatTime(time) {
        let [h, m] = time.split(":");
        h = parseInt(h);

        const ampm = h >= 12 ? "pm" : "am";
        const hour12 = (h % 12) || 12;

        return `${hour12}:${m} ${ampm}`;
    }
    
    function buildTimeOptions(select, selected = "") {
        const row = select.closest(".schedule-row");
        const branchSelect = row.querySelector("select[name$='[branch][]']");
        const day = row.closest(".day-schedule-wrapper").dataset.day;
        const branchId = branchSelect?.value;

        const start = 9 * 60;
        const end = 16 * 60 + 30;

        select.innerHTML = `<option value="">--</option>`;

        for (let t = start; t < end; t += 30) {
            const hh = String(Math.floor(t / 60)).padStart(2, "0");
            const mm = String(t % 60).padStart(2, "0");
            const val = `${hh}:${mm}`;
            const label = formatTime(val);

            const opt = document.createElement("option");
            opt.value = val;

            const isCurrent = selected === val;

            const dentistBusy = dentistBusyAt(day, val, row);

            if (dentistBusy && !isCurrent) {
                opt.textContent = `${label}`;
                opt.disabled = true;
                select.appendChild(opt);
                continue;
            }

            if (branchId && CHAIR_OCCUPANCY[branchId]) {
                const total = CHAIR_OCCUPANCY[branchId].dental_chairs;
                const usedFromDB = countOccupiedChairs(branchId, day, val);
                const usedLocal  = countLocalChairUsage(branchId, day, val, row);
                const used = usedFromDB + usedLocal;

                if (used >= total && !isCurrent) {
                    opt.textContent = `${label} (occupied)`;
                    opt.disabled = true;
                } else {
                    const remaining = Math.max(total - used, 0);
                    opt.textContent = `${label} (${remaining} chair${remaining !== 1 ? "s" : ""} left)`;
                }
            } else {
                opt.textContent = label;
            }

            if (isCurrent) opt.selected = true;
            select.appendChild(opt);
        }
    }

    function dentistBusyAt(day, time, excludeRow = null) {
        let busy = false;

        document.querySelectorAll(`#rows_${day} .schedule-row`).forEach(row => {
            if (row === excludeRow) return;

            const startSel = row.querySelector(".start-time");
            const endSel   = row.querySelector(".end-time");

            if (!startSel.value || !endSel.value) return;

            if (time >= startSel.value && time < endSel.value) {
                busy = true;
            }
        });

        return busy;
    }

    function countOccupiedChairs(branchId, day, time) {
        const data = CHAIR_OCCUPANCY[branchId];
        if (!data) return 0;

        return data.occupied?.[day]?.[time]?.used || 0;
    }

    function countLocalChairUsage(branchId, day, time, excludeRow = null) {
        let count = 0;

        document.querySelectorAll(`#rows_${day} .schedule-row`).forEach(row => {
            if (row === excludeRow) return;

            const branchSel = row.querySelector("select[name$='[branch][]']");
            if (!branchSel || branchSel.value != branchId) return;

            const startSel = row.querySelector(".start-time");
            const endSel   = row.querySelector(".end-time");

            if (!startSel.value || !endSel.value) return;

            if (time >= startSel.value && time < endSel.value) {
                count++;
            }
        });

        return count;
    }

    function updateAddScheduleButton(day) {
        const rowsContainer = document.getElementById(`rows_${day}`);
        if (!rowsContainer) return;

        const dayWrapper = rowsContainer.closest(".day-schedule-wrapper");
        if (!dayWrapper) return;

        const addBtn = dayWrapper.querySelector(".add-schedule-btn");
        if (!addBtn) return;

        addBtn.style.display = "inline-block";
    }
    
    function updateTimeDropdowns(day) {
        const rows = document.querySelectorAll(`#rows_${day} .schedule-row`);

        rows.forEach(row => {
            const startSelect = row.querySelector(".start-time");
            const endSelect   = row.querySelector(".end-time");

            const startVal = startSelect.value;
            const endVal   = endSelect.value;

            if (endVal && startVal && endVal <= startVal) {
                endSelect.value = "";
            }
        });
    }

    function updateBranchDropdowns() {
        const allRows = document.querySelectorAll(".schedule-row");

        const chosen = [];
        allRows.forEach(row => {
            const sel = row.querySelector("select[name$='[branch][]']");
            if (sel && sel.value) chosen.push(sel.value);
        });

        allRows.forEach(row => {
            const select = row.querySelector("select[name$='[branch][]']");
            const currentValue = select.value;

            const checkedBranches = Array.from(
                document.querySelectorAll("#branchAssignment input[type=checkbox]:checked")
            ).map(cb => ({
                branch_id: cb.value,
                name: cb.nextElementSibling.textContent
            }));

            select.innerHTML = `
                <option value="" disabled ${currentValue === "" ? "selected" : ""}>Select Branch</option>
                ${checkedBranches.map(b => `
                    <option value="${b.branch_id}" 
                        ${currentValue == b.branch_id ? "selected" : ""}
                    >
                        ${b.name}
                    </option>
                `).join("")}
            `;
        });
    }

    function hasStarted(dateStarted) {
        if (!dateStarted) return false;
        const today = new Date();
        today.setHours(0,0,0,0);

        const started = new Date(dateStarted);
        started.setHours(0,0,0,0);

        return started <= today;
    }

    function attachEmailValidator(form) {
        const emailInput = form.querySelector("#email");
        if (!emailInput) return;

        let timer = null;
        let emailIsValid = true;
        let validating = false;

        let errorSpan = form.querySelector("#emailErrorDynamic");
        if (!errorSpan) {
            errorSpan = document.createElement("span");
            errorSpan.id = "emailErrorDynamic";
            errorSpan.className = "error-msg-calendar error";
            errorSpan.style.display = "none";
            emailInput.parentNode.appendChild(errorSpan);
        }

        const submitBtn = form.querySelector("button[type='submit']");

        function show(msg) {
            errorSpan.textContent = msg;
            errorSpan.style.display = "block";
        }

        function hide() {
            errorSpan.style.display = "none";
        }

        function disableSubmit() {
            if (submitBtn) submitBtn.disabled = true;
        }

        function enableSubmit() {
            if (submitBtn) submitBtn.disabled = false;
        }

        function validate(email) {
            const basicPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!basicPattern.test(email)) {
                emailIsValid = false;
                show("Invalid email format.");
                disableSubmit();
                return;
            }

            hide();
            validating = true;

            fetch(`/processes/validate_email.php?email=${encodeURIComponent(email)}`)
                .then(res => res.json())
                .then(d => {
                    emailIsValid = d.valid;

                    if (!d.valid) {
                        show("Email domain is invalid or unreachable.");
                        disableSubmit();
                    } else {
                        hide();
                        enableSubmit();
                    }
                })
                .catch(() => {
                    emailIsValid = false;
                    show("Unable to validate email right now.");
                    disableSubmit();
                })
                .finally(() => {
                    validating = false;
                });
        }

        emailInput.addEventListener("input", () => {
            clearTimeout(timer);

            hide();
            validating = false;

            disableSubmit();

            timer = setTimeout(() => {
                validate(emailInput.value.trim());
            }, 500);
        });

        form.addEventListener("submit", (e) => {
            if (!emailInput.checkValidity()) return;

            if (validating) {
                e.preventDefault();
                show("Validating email… please wait.");
                disableSubmit();
                return;
            }

            if (!emailIsValid) {
                e.preventDefault();
                show("Please enter a valid email address.");
                disableSubmit();
                return;
            }

            enableSubmit();
        });
    }

    function attachDateValidators(form) {
        if (!form) return;

        const dobInput = form.querySelector("#dateofBirth");
        const startInput = form.querySelector("#dateStarted");

        function getOrCreateErrorSpan(input, id) {
            let span = form.querySelector("#" + id);
            if (!span) {
                span = document.createElement("span");
                span.id = id;
                span.className = "error-msg-calendar error";
                span.style.display = "none";
                input.parentNode.appendChild(span);
            }
            return span;
        }

        const dobError = dobInput ? getOrCreateErrorSpan(dobInput, "dobError") : null;
        const startError = startInput ? getOrCreateErrorSpan(startInput, "dateError") : null;

        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const tomorrow = new Date(today);
        tomorrow.setDate(today.getDate() + 1);
        const tomorrowISO = tomorrow.toISOString().split("T")[0];

        if (dobInput) {
            dobInput.addEventListener("change", () => {
                const value = dobInput.value;
                if (!value) {
                    dobError.style.display = "none";
                    return;
                }
                const selected = new Date(value);
                selected.setHours(0, 0, 0, 0);

                if (isNaN(selected)) {
                    dobError.textContent = "Please enter a valid date.";
                    dobError.style.display = "block";
                    dobInput.value = "";
                } else if (selected > today) {
                    dobError.textContent = "Please enter a valid date.";
                    dobError.style.display = "block";
                    dobInput.value = "";
                } else {
                    dobError.style.display = "none";
                }
            });

            dobInput.setAttribute("min", "1900-01-01");
            dobInput.setAttribute("max", today.toISOString().split("T")[0]);
        }

        if (startInput) {
            startInput.addEventListener("change", () => {
                const value = startInput.value;
                if (!value) {
                    startError.style.display = "none";
                    return;
                }

                const selected = new Date(value);
                selected.setHours(0, 0, 0, 0);
                const day = selected.getDay();

                const now = new Date();
                now.setHours(0, 0, 0, 0);

                const tomorrowCheck = new Date(now);
                tomorrowCheck.setDate(now.getDate() + 1);

                if (isNaN(selected)) {
                    startError.textContent = "Please enter a valid date.";
                    startError.style.display = "block";
                    startInput.value = "";
                } else if (selected < tomorrowCheck) {
                    startError.textContent = "Start date must be at least tomorrow.";
                    startError.style.display = "block";
                    startInput.value = "";
                } else if (day === 0) {
                    startError.textContent = "Sundays are not available. Please select another date.";
                    startError.style.display = "block";
                    startInput.value = "";
                } else {
                    startError.style.display = "none";
                }
            });

            startInput.setAttribute("min", tomorrowISO);
        }
    }

    function attachSafeSubmit(form) {
        form.addEventListener("submit", function () {
            const btn = form.querySelector("button[type='submit']");
            if (!btn) return;

            if (form.checkValidity()) {
                btn.disabled = true;
            }
        });
    }

    function setScheduleReadonly(isReadonly) {
        SCHEDULE_LOCKED = isReadonly;

        const container = document.getElementById("branchScheduleContainer");
        if (!container) return;

        container.querySelectorAll("select").forEach(el => {
            el.disabled = isReadonly;
        });

        container.querySelectorAll(".add-schedule-btn, .remove-row-btn").forEach(btn => {
            btn.style.display = isReadonly ? "none" : "inline-block";
        });
    }
});

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

function closeEmployeeModal() {
    document.getElementById("manageModal").style.display = "none";
}

document.addEventListener("submit", function (e) {
    const form = e.target;
    if (form.id !== "dentistForm") return;

    const confirmInput = form.querySelector("#confirmDentistUpdate");
    const dentistIdInput = form.querySelector("input[name='dentist_id']");
    const statusSelect = form.querySelector("#status");
    const originalStatusInput = form.querySelector("#originalStatus");

    if (!confirmInput || !dentistIdInput || !statusSelect || !originalStatusInput) return;

    const dentistId = dentistIdInput.value;
    const currentStatus = originalStatusInput.value;
    const newStatus = statusSelect.value;

    if (currentStatus === "Active" && newStatus === "Inactive" && confirmInput.value !== "1") {
        e.preventDefault();

        openDentistUpdateConfirm(
            dentistId,
            currentStatus,
            newStatus,
            () => {
                confirmInput.value = "1";
                form.submit();
            }
        );
    }
});
