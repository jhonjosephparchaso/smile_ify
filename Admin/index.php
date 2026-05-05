<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    session_unset();
    session_destroy();
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

$currentPage = 'index';

$updateSuccess = $_SESSION['updateSuccess'] ?? "";
$updateError = $_SESSION['updateError'] ?? "";

require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/Admin/includes/navbar.php';
?>

<title>Home</title>

<?php if (!empty($updateSuccess) || !empty($updateError)): ?>
    <div id="toastContainer">
        <?php if (!empty($updateSuccess)): ?>
            <div class="toast success"><?= htmlspecialchars($updateSuccess) ?></div>
            <?php unset($_SESSION['updateSuccess']); ?>
        <?php endif; ?>

        <?php if (!empty($updateError)): ?>
            <div class="toast error"><?= htmlspecialchars($updateError) ?></div>
            <?php unset($_SESSION['updateError']); ?>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="dashboard">
    <div class="cards">
        <div class="card">
            <h2><span class="material-symbols-outlined">calendar_month</span> Upcoming Appointments</h2>
            <div class="appointment">Today: <span id="todayCount">0</span></div>
            <div class="appointment">This Week: <span id="weekCount">0</span></div>
            <div class="appointment">This Month: <span id="monthCount">0</span></div>
            <div class="button-group card-actions">
                <a href="<?= BASE_URL ?>/Admin/pages/calendar.php" class="confirm-btn card-link">View Calendar</a>
                <a href="#" class="confirm-btn card-link" onclick="openBookingModal()"><span class="material-symbols-outlined">calendar_add_on</span> Book Appointment</a>
            </div>
        </div>  
        
        <div id="bookingModal" class="booking-modal">
            <div class="booking-modal-content">
                
                <form action="<?= BASE_URL ?>/Admin/processes/index/insert_appointment.php" method="POST" autocomplete="off">
                
                    <div class="booking-type-selector">
                        <label class="selection-label">Booking For:</label>

                        <div class="radio-row">
                            <label class="radio-option">
                                <input type="radio" name="bookingType" id="bookForSelf" value="self" checked>
                                Adult
                            </label>

                            <label class="radio-option">
                                <input type="radio" name="bookingType" id="bookForChild" value="child">
                                Dependent (Child, Person with Disability, or Senior Citizen)
                            </label>
                        </div>
                    </div>
                
                    <div class="form-group">
                        <input type="text" id="lastName" name="lastName" class="form-control" placeholder=" " required />
                        <label for="lastName" class="form-label">Last Name <span class="required">*</span></label>
                    </div>

                    <div class="form-group">
                        <input type="text" id="firstName" name="firstName" class="form-control" placeholder=" " required />
                        <label for="firstName" class="form-label">First Name <span class="required">*</span></label>
                    </div>

                    <div class="form-group">
                        <input type="text" id="middleName" name="middleName" class="form-control" placeholder=" " />
                        <label for="middleName" class="form-label">Middle Name</label>
                    </div>

                    <div class="form-group">
                        <input type="email" id="email" name="email" class="form-control" placeholder=" " required autocomplete="off"/>
                        <label for="email" class="form-label">Email Address <span class="required">*</span></label>
                        <span id="emailError" class="error-msg-calendar error" style="display:none"></span>
                    </div>
                    
                    <div class="form-group">
                        <select id="gender" name="gender" class="form-control" required>
                            <option value="" disabled selected hidden></option>
                            <option value="female">Female</option>
                            <option value="male">Male</option>
                        </select>
                        <label for="gender" class="form-label">Gender <span class="required">*</span></label>
                    </div>

                    <div class="form-group">
                        <input type="date" id="dateofBirth" name="dateofBirth" class="form-control" required />
                        <label for="dateofBirth" class="form-label">Date of Birth <span class="required">*</span></label>
                        <span id="dobError" class="error-msg-calendar error" style="display: none;"></span>
                    </div>

                    <div class="form-group phone-group">
                        <input type="tel" id="contactNumber" name="contactNumber" class="form-control" 
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')" 
                            pattern="[0-9]{10}" title="Mobile number must be 10 digits" 
                            required maxlength="10" />
                        <label for="contactNumber" class="form-label">Mobile Number <span class="required">*</span></label>
                        <span class="phone-prefix">+63</span>
                    </div>

                    <div id="childInfo" style="display:none; margin-top:20px;">
                        <h3 class="section-title">Dependent Information</h3>

                        <div class="form-group">
                            <input type="text" id="childLastName" name="childLastName" class="form-control" placeholder=" ">
                            <label for="childLastName" class="form-label">Dependent Last Name <span class="required">*</span></label>
                        </div>

                        <div class="form-group">
                            <input type="text" id="childFirstName" name="childFirstName" class="form-control" placeholder=" ">
                            <label for="childFirstName" class="form-label">Dependent First Name <span class="required">*</span></label>
                        </div>

                        <div class="form-group">
                            <select id="relationship" name="relationship" class="form-control">
                                <option value="" disabled selected hidden></option>
                                <option value="Parent">Parent</option>
                                <option value="Sibling">Sibling</option>
                                <option value="Child">Child</option>
                            </select>
                            <label for="relationship" class="form-label">
                                Relationship to Guardian <span class="required">*</span>
                            </label>
                        </div>

                        <div class="form-group">
                            <select id="childGender" name="childGender" class="form-control">
                                <option value="" disabled selected hidden></option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                            <label for="childGender" class="form-label">Dependent Gender <span class="required">*</span></label>
                        </div>

                        <div class="form-group">
                            <input type="date" id="childDob" name="childDob" class="form-control">
                            <label for="childDob" class="form-label">Dependent Date of Birth <span class="required">*</span></label>
                        </div>
                    </div>

                    <h3 class="section-title">Appointment Information</h3>

                    <div class="form-group">
                        <select id="appointmentBranch" name="appointmentBranch" class="form-control" required>
                            <option value="" disabled selected hidden></option>
                            <?php
                            $sql = "SELECT branch_id, name, status FROM branch WHERE status = 'Active'";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<option value='" . $row["branch_id"] . "'>" . htmlspecialchars($row["name"]) . "</option>";
                                }
                            } else {
                                echo "<option disabled>No branches available</option>";
                            }
                            ?>
                        </select>
                        <label for="appointmentBranch" class="form-label">Branch <span class="required">*</span></label>
                    </div>

                    <div class="form-group">
                        <input type="date" id="appointmentDate" name="appointmentDate" class="form-control" required />
                        <label for="appointmentDate" class="form-label">Date <span class="required">*</span></label>
                        <span id="dateError" class="error-msg-calendar error" style="display:none;">
                            Sundays are not available for appointments. Please select another date.
                        </span>
                    </div>

                    <div class="form-group">
                        <select id="appointmentTime" name="appointmentTime" class="form-control" required></select>
                        <label for="appointmentTime" class="form-label">Time <span class="required">*</span></label>
                        <div id="estimatedEnd" class="text-gray-600 mt-2"></div>
                        <span id="timeError" class="error-msg-calendar error" style="display:none"></span>
                    </div>

                    <div class="form-group">
                        <div id="servicesContainer" class="checkbox-group">
                            <p class="loading-text">Select a branch to load available services</p>
                        </div>
                        <span id="servicesError" class="error-msg-calendar error" style="display:none"></span>
                    </div>

                    <div class="form-group">
                        <select id="appointmentDentist" name="appointmentDentist" class="form-control" required>
                            <option value="" disabled selected hidden></option>
                        </select>
                        <label for="appointmentDentist" class="form-label">Dentist <span class="required">*</span></label>
                        <span id="dentistError" class="error-msg-calendar error" style="display:none"></span>
                    </div>

                    <div class="form-group">
                        <textarea id="notes" name="notes" class="form-control" rows="3" placeholder=" " autocomplete="off"></textarea>
                        <label for="notes" class="form-label">Add a note</label>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="form-button confirm-btn">Confirm</button>
                        <button type="button" class="form-button cancel-btn" onclick="closeBookingModal()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <h2><span class="material-symbols-outlined">groups</span> Patients</h2>
            <div class="appointment">New This Month: <span id="newPatientsCount">0</span></div>
            <div class="appointment">Total: <span id="totalPatientsCount">0</span></div>
            <div class="button-group card-actions">
                <a href="<?= BASE_URL ?>/Admin/pages/patients.php" class="card-link">Manage Patients</a>
            </div>
        </div>

        <div class="card">
            <h2><span class="material-symbols-outlined">inventory_2</span> Supplies</h2>
            <div id="lowSuppliesContainer">
                <div class="announcement">Loading</div>
            </div>
            <div class="button-group card-actions">
                <a href="<?= BASE_URL ?>/Admin/pages/supplies.php" class="card-link">Manage Supplies</a>
            </div>
        </div>

        <div class="card">
            <h2><span class="material-symbols-outlined">bar_chart</span> Promo</h2>
            <div class="appointment">Total Availed: <span id="promoAvailedCount">0</span></div>
            <div id="promoAvailedList" class="announcement"></div>
            <div class="button-group card-actions">
                <a href="<?= BASE_URL ?>/Admin/pages/reports.php" class="card-link">View Detailed Reports</a>
            </div>
        </div>

        <div class="card">
            <h2><span class="material-symbols-outlined">notifications</span> Recent Notifications</h2>

            <?php if (empty($notifications)): ?>
                <div class="announcement">No notifications</div>
            <?php else: ?>
                <?php foreach (array_slice($notifications, 0, 3) as $n): ?>
                    <div class="announcement <?= $n['is_read'] ? '' : 'unread' ?>">
                        <div class="notif-message"><?= htmlspecialchars($n['message']) ?></div>
                        <div class="notif-date"><?= date('M d, Y H:i', strtotime($n['date_created'])) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2><span class="material-symbols-outlined">bolt</span> Quick Links</h2>
            <div class="quick-links">
                <a href="<?= BASE_URL ?>/Admin/pages/services.php"><span class="material-symbols-outlined">medical_services</span> Manage Services</a>
                <a href="<?= BASE_URL ?>/Admin/pages/promos.php"><span class="material-symbols-outlined">local_offer</span> Manage Promos</a>
                <a href="<?= BASE_URL ?>/Admin/pages/profile.php"><span class="material-symbols-outlined">manage_accounts</span> Profile Settings</a>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const adminBranchId = "<?= $_SESSION['branch_id'] ?? '' ?>";
        const branchSelect = document.getElementById("appointmentBranch");

        if (adminBranchId && branchSelect) {
            const observer = new MutationObserver(() => {
                const modal = document.getElementById("bookingModal");
                if (modal && modal.style.display === "block") {
                    const option = branchSelect.querySelector(`option[value='${adminBranchId}']`);
                    if (option && !branchSelect.value) {
                        option.selected = true;
                        branchSelect.dispatchEvent(new Event("change", { bubbles: true }));
                    }
                }
            });

            observer.observe(document.body, {
                attributes: true,
                subtree: true,
                attributeFilter: ["style", "class"]
            });
        }
    });
    
    document.addEventListener("DOMContentLoaded", function () {

        function attachSafeSubmit(form) {
            form.addEventListener("submit", function () {
                const btn = form.querySelector("button[type='submit']");
                if (!btn) return;
                setTimeout(() => { btn.disabled = true; }, 50);
            });
        }

        const loginForm = document.querySelector("form[action*='request_otp_login.php']");
        const bookingForm = document.querySelector("#bookingModal form");
        const forgotForm = document.querySelector("#forgotpasswordModal form");

        if (loginForm) attachSafeSubmit(loginForm);
        if (forgotForm) attachSafeSubmit(forgotForm);

        let emailIsValid = false;
        const emailInput = document.getElementById("email");
        const emailError = document.getElementById("emailError");

        function showError(msg) {
            emailError.textContent = msg;
            emailError.style.display = "block";
        }

        function hideError() {
            emailError.style.display = "none";
        }

        function validateMX(email) {
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!regex.test(email)) {
                showError("Invalid email format.");
                emailIsValid = false;
                return;
            }
            fetch(`/Smile-ify/processes/validate_email.php?email=${encodeURIComponent(email)}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.valid) {
                        showError("Email domain is not valid or unreachable.");
                        emailIsValid = false;
                    } else {
                        hideError();
                        emailIsValid = true;
                    }
                })
                .catch(() => {
                    showError("Unable to validate email right now.");
                    emailIsValid = false;
                });
        }

        let timer;
        const delay = 600;

        emailInput.addEventListener("input", () => {
            clearTimeout(timer);
            const email = emailInput.value.trim();
            enableSubmitButton(); 
            if (email === "") {
                hideError();
                emailIsValid = false;
                return;
            }
            timer = setTimeout(() => validateMX(email), delay);
        });

        const servicesError = document.getElementById("servicesError");
        const dentistError = document.getElementById("dentistError");
        const servicesContainer = document.getElementById("servicesContainer");

        servicesContainer.addEventListener("change", function(e) {
            enableSubmitButton();
            if (e.target.name === "appointmentServices[]") {
                const selected = document.querySelectorAll("input[name='appointmentServices[]']:checked");
                if (selected.length > 0) {
                    servicesError.style.display = "none";
                }
            }
        });

        document.getElementById("appointmentDentist").addEventListener("change", () => {
            dentistError.style.display = "none";
            enableSubmitButton();
        });

        function enableSubmitButton() {
            const btn = bookingForm.querySelector("button[type='submit']");
            btn.disabled = false;
            btn.innerText = btn.dataset.originalText || "Confirm";
        }

        bookingForm.addEventListener("submit", function(e) {

            if (!emailIsValid) {
                e.preventDefault();
                showError("Please enter a valid email address.");
                emailInput.focus();
                enableSubmitButton();
                return;
            }

            const selectedServices = document.querySelectorAll("input[name='appointmentServices[]']:checked");
            if (selectedServices.length === 0) {
                e.preventDefault();
                servicesError.textContent = "Please select at least one service.";
                servicesError.style.display = "block";
                enableSubmitButton();
                return;
            }

            const dentist = document.getElementById("appointmentDentist").value;
            if (dentist === "" || dentist === null) {
                e.preventDefault();
                dentistError.textContent = "Please select a dentist.";
                dentistError.style.display = "block";
                enableSubmitButton();
                return;
            }

            const btn = bookingForm.querySelector("button[type='submit']");
            setTimeout(() => { btn.disabled = true; }, 50);
        });
    });

    document.getElementById("bookForSelf").addEventListener("change", () => {
        document.getElementById("childInfo").style.display = "none";

        document.getElementById("childFirstName").required = false;
        document.getElementById("childLastName").required = false;
        document.getElementById("childDob").required = false;
        document.getElementById("childGender").required = false;
    });

    document.getElementById("bookForChild").addEventListener("change", () => {
        document.getElementById("childInfo").style.display = "block";

        document.getElementById("childFirstName").required = true;
        document.getElementById("childLastName").required = true;
        document.getElementById("childDob").required = true;
        document.getElementById("childGender").required = true;
        document.getElementById("relationship").required = true;
    });
</script>
<style>
    #servicesModal .booking-modal-content {
        width: 500px;
    }

    .booking-type-selector {
        margin-bottom: 20px;
    }

    .selection-label {
        font-weight: 600;
        display: block;
        margin-bottom: 6px;
    }

    .radio-row {
        display: flex;
        gap: 40px;
        align-items: center;
    }

    .radio-option {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        font-size: 15px;
    }

    .radio-option input[type="radio"] {
        transform: scale(1.2);
        cursor: pointer;
    }
</style>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
