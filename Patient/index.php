<?php 
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

$currentPage = 'index';
$error_msg = '';

if (isset($_SESSION['error_msg'])) {
    $error_msg = $_SESSION['error_msg'];
    unset($_SESSION['error_msg']);
}

require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/Patient/includes/navbar.php';

?>
<script>
    window.LOGGED_IN_USER_ID = <?= json_encode($_SESSION['user_id']) ?>;
</script>
<body>
    <title>Home</title>

    <div class="dashboard">
        <div class="top-section">
            <div class="welcome">
                <h1>
                    👋 Welcome, 
                    <?php
                        $prefix = '';
                        if (isset($_SESSION['gender'])) {
                            $gender = strtolower($_SESSION['gender']);
                            $prefix = ($gender === 'male') ? 'Mr.' : (($gender === 'female') ? 'Ms.' : '');
                        }

                        $fullName = htmlspecialchars($_SESSION['username'] ?? 'User');

                        if (!empty($_SESSION['first_name']) && !empty($_SESSION['last_name'])) {
                            $fullName = htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
                        }

                        echo trim("$prefix $fullName!") ;
                    ?>
                </h1>
            </div>
        </div>

        <div class="cards">
            <div class="card">
                <h2><span class="material-symbols-outlined">calendar_month</span> Upcoming Appointments</h2>
                <div id="patientUpcomingAppointments">
                    <div class="appointment">Loading</div>
                </div>
            </div>

            <div class="card">
                <h2><span class="material-symbols-outlined">campaign</span> Announcements</h2>
                <div id="patientAnnouncements">
                    <div class="announcement">Loading</div>
                </div>
            </div>

            <div class="card">
                <h2><span class="material-symbols-outlined">dentistry</span> Dental Care Tips</h2>
                <div id="patientTips">
                    <div class="tip">Loading</div>
                </div>
            </div>

            <div class="card">
                <h2><span class="material-symbols-outlined">bolt</span> Quick Links</h2>
                <div class="quick-links">
                    <a href="#" onclick="openBookingModal()"><span class="material-symbols-outlined">calendar_add_on</span> Book Appointment</a>
                    <a href="#" onclick="openDentistsModal()"><span class="material-symbols-outlined">medical_information</span> View Dentists</a>
                    <a href="#" onclick="openServicesModal()"><span class="material-symbols-outlined">medical_services</span> View Service</a>
                    <a href="<?= BASE_URL ?>/Patient/pages/profile.php"><span class="material-symbols-outlined">manage_accounts</span> Profile Settings</a><br>
                    <a href="#" onclick="openEducationalModal()"><span class="material-symbols-outlined">info</span> About</a>
                
                </div>
            </div>
            <?php if (!empty($error_msg)): ?>
                <div class="error"><?php echo htmlspecialchars($error_msg); ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="tagline-container">
        <div class="column1" style="background-color: #e7c6ff">
            <img src="<?= BASE_URL ?>/images/icons/experienced_dentist.png" alt="Experienced Dentist">
            <h2>Experienced Dentist</h2>
            <p>With the team's expertise, your smile is in the best hands possible.</p>
        </div>
        <div class="column2" style="background-color: #c8b6ff">
            <img src="<?= BASE_URL ?>/images/icons/advance_treament.png" alt="Advance Treatment">
            <h2>Advance Treatment</h2>
            <p>Backed by expertise and advanced technology, our team ensures your satisfaction.</p>
        </div>
        <div class="column3" style="background-color: #b8c0ff">
            <img src="<?= BASE_URL ?>/images/icons/guaranteed_results.png" alt="Guaranteed Results">
            <h2>Guaranteed Results</h2>
            <p>Skilled team and techniques ensure your smile transformation is delivered.</p>
        </div>
        <div class="column4" style="background-color: #bbd0ff">
            <img src="<?= BASE_URL ?>/images/icons/affordable_rates.png" alt="Affordable Rates">
            <h2>Affordable Rates</h2>
            <p>Offers affordable rates and top-notch care, so you get the best of both worlds.</p>
        </div>
    </div>                    
    <div class="welcome-container">
        <div class="welcome-text">We are open and welcoming, Patients!</div>
    </div>

    <p class="description">
        Make the best choice for your dental health – choose us.
    </p>

    <div class="service-container">
        <div class="grid">
            <div class="image-column">
                <img src="<?= BASE_URL ?>/images/services/checkup.jpg" alt="Check Up and Cleaning">
                <p>Check Up and Cleaning</p>
            </div>
            <div class="image-column">
                <img src="<?= BASE_URL ?>/images/services/root_canal.jpg" alt="Root Canal">
                <p>Root Canal</p>
            </div>
            <div class="image-column">
                <img src="<?= BASE_URL ?>/images/services/crown.jpg" alt="Crown">
                <p>Crown</p>
            </div>
            <div class="image-column">
                <img src="<?= BASE_URL ?>/images/services/veneers.jpg" alt="Veneers">
                <p>Veneers</p>
            </div>
            <div class="image-column">
                <img src="<?= BASE_URL ?>/images/services/index_brace.jpg" alt="Braces">
                <p>Braces</p>
            </div>
            <div class="image-column">
                <img src="<?= BASE_URL ?>/images/services/denture.jpg" alt="Dentures and Porcelain">
                <p>Dentures and Porcelain</p>
            </div>
        </div>
    </div>

    <div class="promo-container">
        <div class="welcome-text">Promos</div>
        <div class="promos swiper promo-slider">
            <div class="swiper-wrapper" id="promoWrapper">
                <!-- promos will be loaded here via JS -->
            </div>

            <div class="swiper-pagination"></div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </div>

    <div class="aboutus-wrapper">
        <div class="aboutus-info">
            <p class="aboutus-heading">About Us</p>
            <p class="aboutus-paragraph">
                Arriesgado Dental Clinic is a busy dental practice that serves a large patient population in Cebu City, Philippines. We have three (3) branches located in Babag Lapu-Lapu City, Pusok Lapu-Lapu City and Mandaue City. We offer a wide range of dental services, including general dentistry and orthodontics. <br><br>
                Our commitment to quality means that we use only the best materials and techniques to ensure that our services meet your expectations and exceed them. <br><br>
                Our convenient location means that you won't have to travel far to take advantage of our services, making it easy for you to fit us into your busy schedule. <br><br>
                So whether you're looking for a regular dental check up or a complete dental rehabilitation, we've got you covered.
            </p>
        </div>
    </div>

    <div id="bookingModal" class="booking-modal">
        <div class="booking-modal-content">
            
            <form action="<?= BASE_URL ?>/Patient/processes/insert_appointment.php" method="POST" autocomplete="off">
            
                <div class="booking-type-selector">
                    <label class="selection-label">Booking For:</label>

                    <div class="radio-row">
                        <label class="radio-option">
                            <input type="radio" name="bookingType" id="bookForSelf" value="self" checked>
                            Myself (Adult)
                        </label>

                        <label class="radio-option">
                            <input type="radio" name="bookingType" id="bookForChild" value="child">
                            New Dependent (Child, Person with Disability, or Senior Citizen)
                        </label>

                        <label class="radio-option">
                            <input type="radio" name="bookingType" id="bookForExisting" value="existing">
                            My Registered Dependent
                        </label>
                    </div>
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

                <div id="existingDependentInfo" style="display:none; margin-top:20px;">
                    <h3 class="section-title">Select Dependent</h3>

                    <div class="form-group">
                        <select id="existingDependentSelect" name="existingDependentId" class="form-control">
                            <option value="" disabled selected hidden></option>
                            <?php
                                $guardianId = $_SESSION['user_id'];

                                $sqlDep = "SELECT user_id, first_name, last_name, gender, date_of_birth
                                        FROM users
                                        WHERE guardian_id = ? 
                                        AND username IS NULL 
                                        AND password IS NULL
                                        AND role = 'patient'
                                        AND status = 'Active'";

                                $stmtDep = $conn->prepare($sqlDep);
                                $stmtDep->bind_param("i", $guardianId);
                                $stmtDep->execute();
                                $depResult = $stmtDep->get_result();

                                if ($depResult->num_rows > 0) {
                                    while ($d = $depResult->fetch_assoc()) {
                                        echo "<option value='{$d['user_id']}'>
                                                {$d['first_name']} {$d['last_name']} ({$d['gender']})
                                            </option>";
                                    }
                                } else {
                                    echo "<option disabled>No registered dependents</option>";
                                }
                            ?>
                        </select>
                        <label for="existingDependentSelect" class="form-label">Dependent <span class="required">*</span></label>
                    </div>
                </div>

                <h3 class="section-title">Appointment Information</h3>

                <div class="form-group">
                    <select id="appointmentBranch" class="form-control" name="appointmentBranch" required>
                        <option value="" disabled selected hidden></option>
                        <?php

                        $sql = "SELECT branch_id, name, status FROM branch WHERE status = 'Active' ";
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
                    <span id="dateError" class="error-msg-calendar error">
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
                    <span id="servicesError" class="error-msg-calendar error" style="display:none"></span>
                    <div id="servicesContainer" class="checkbox-group">
                        <p class="loading-text">Select a branch to load available services</p>
                    </div>
                </div>

                <div class="form-group">
                    <select id="appointmentDentist" class="form-control" name="appointmentDentist" required>
                        <option value="" disabled selected hidden></option>
                    </select>
                    <label for="appointmentDentist" class="form-label">Dentist <span class="required">*</span></label>
                    <span id="dentistError" class="error-msg-calendar error" style="display:none"></span>
                </div>

                <div class="form-group">
                    <textarea id="notes" class="form-control" name="notes" rows="3" placeholder=" "autocomplete="off"></textarea>
                    <label for="notes" class="form-label">Add a note</label>
                </div>

                <div class="button-group">
                    <button type="submit" class="form-button confirm-btn">Confirm</button>
                    <button type="button" class="form-button cancel-btn" onclick="closeBookingModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <div id="promoModal" class="promo-modal">
        <div class="promo-modal-content">
            <img id="promoModalImg" src="" alt="Promo Image" class="modal-img">
            <div class="modal-details">
                <h3 id="promoTitle"></h3>
                <p id="promoDesc"></p>
                <p id="promoDate"></p>
                <p id="promoBranch"></p>
            </div>
        </div>
    </div>

    <div id="educationalModal" class="educational-modal">
        <div class="educational-modal-content" id="educationalModalContent">
        </div>
    </div>

    <div id="dentistsModal" class="booking-modal">
        <div class="booking-modal-content">
            <h2>Available Dentists</h2>
            <p>Below is the list of dentists, their assigned branches, and services.</p>

            <div id="dentistsContainer" style="max-height: 400px; overflow-y: auto;">
                <p>Loading dentists...</p>
            </div>

            <div class="button-group">
                <button type="button" class="cancel-btn" onclick="closeDentistsModal()">Close</button>
            </div>
        </div>

    </div>

    <div id="servicesModal" class="booking-modal services-modal">
        <div class="booking-modal-content">
            <h2>Available Services</h2>
            <p>Below is the list of services.</p>

            <div id="serviceContainer" style="max-height: 400px; overflow-y: auto;">
                <p>Loading service...</p>
            </div>

            <div class="button-group">
                <button type="button" class="cancel-btn" onclick="closeServicesModal()">Close</button>
            </div>
        </div>
    </div>

    <?php require_once BASE_PATH . '/includes/footer.php'; ?>
</body>

<script>
    const selfRadio = document.getElementById("bookForSelf");
    const childRadio = document.getElementById("bookForChild");
    const existingRadio = document.getElementById("bookForExisting");

    const childInfo = document.getElementById("childInfo");
    const existingInfo = document.getElementById("existingDependentInfo");

    const childFirst = document.getElementById("childFirstName");
    const childLast = document.getElementById("childLastName");
    const childDob = document.getElementById("childDob");
    const childGender = document.getElementById("childGender");
    const relationship = document.getElementById("relationship");
    const existingSelect = document.getElementById("existingDependentSelect");

    function resetChildRequirements() {
        childFirst.required = false;
        childLast.required = false;
        childDob.required = false;
        childGender.required = false;
        relationship.required = false;
        relationship.value = "";
    }

    function hideAllBookingForms() {
        childInfo.style.display = "none";
        existingInfo.style.display = "none";

        resetChildRequirements();
        existingSelect.required = false;
    }

    selfRadio.addEventListener("change", () => {
        hideAllBookingForms();
        loadAvailableTimes();
    });

    childRadio.addEventListener("change", () => {
        hideAllBookingForms();
        childInfo.style.display = "block";

        childFirst.required = true;
        childLast.required = true;
        childDob.required = true;
        childGender.required = true;
        relationship.required = true;

        loadAvailableTimes();
    });

    existingRadio.addEventListener("change", () => {
        hideAllBookingForms();
        existingInfo.style.display = "block";
        existingSelect.required = true;

        loadAvailableTimes();
    });

    existingSelect.addEventListener("change", () => {
        loadAvailableTimes();
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
