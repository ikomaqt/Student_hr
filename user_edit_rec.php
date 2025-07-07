<?php
// Start session before any output
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /health_record/user_login.php");
    exit();
}

include 'sqlconnection.php';
include 'user_navbar.php';

$user_id = $_SESSION['user_id'];
// Fetch sections
$sectionsQuery = "SELECT section_id, section_name FROM section";
$sectionsResult = $conn->query($sectionsQuery);
$sections = $sectionsResult->fetch_all(MYSQLI_ASSOC);

// Fetch user's basic info
$stmt = $conn->prepare("SELECT u.lrn, u.section_id, u.first_name, u.middle_name, u.last_name, s.section_name as section_name FROM users u LEFT JOIN section s ON u.section_id = s.section_id WHERE u.id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$userLrn = $user['lrn'];
$userSectionId = $user['section_id'];
$userFirstName = $user['first_name'];
$userMiddleName = $user['middle_name'];
$userLastName = $user['last_name'];
$userSectionName = $user['section_name'];
$stmt->close();

// Fetch existing student record
$stmt = $conn->prepare("SELECT * FROM student_info WHERE lrn = ?");
$stmt->bind_param("s", $userLrn);
$stmt->execute();
$studentInfo = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$studentInfo) { die("No existing record found to edit."); }
$student_id = $studentInfo['student_id'];

// Fetch immunization records
$stmt = $conn->prepare("SELECT * FROM immunization_record WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$immunization_records = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch medical history
$stmt = $conn->prepare("SELECT * FROM medical_history WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$medicalHistory = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$medicalHistory) { $medicalHistory = []; }

// Fetch menstruation history
$stmt = $conn->prepare("SELECT * FROM menstruation_history WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$menstruationHistory = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch smoking history
$stmt = $conn->prepare("SELECT * FROM smoking_history WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$smokingHistory = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch special needs
$stmt = $conn->prepare("SELECT * FROM special_needs WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$specialNeeds = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch guardian information
$stmt = $conn->prepare("SELECT * FROM guardians WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$guardian = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch physical examination data
$stmt = $conn->prepare("SELECT * FROM physical_examination WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$physicalExam = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$physicalExam) { $physicalExam = []; }

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->begin_transaction();
    try {
        // Validate and sanitize input
        $name = !empty($_POST['name']) ? trim($_POST['name']) : '';
        $section_id = !empty($_POST['section_id']) ? intval($_POST['section_id']) : 0;
        $sex = !empty($_POST['sex']) ? $_POST['sex'] : '';
        $lrn = !empty($_POST['lrn']) ? $_POST['lrn'] : 0;
        $date_of_birth = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : '';
        $address = !empty($_POST['address']) ? trim($_POST['address']) : '';

        // Check for required fields
        if (empty($name) || empty($section_id) || empty($sex) || empty($lrn) || empty($date_of_birth) || empty($address)) {
            throw new Exception("All required fields must be filled.");
        }

        // Update student_info table
        $stmt = $conn->prepare("UPDATE student_info SET 
            name = ?, section_id = ?, sex = ?, date_of_birth = ?, nationality = ?, 
            address = ?, father_name = ?, father_phone = ?, mother_name = ?, mother_phone = ?, 
            emergency_contact_name = ?, emergency_contact_relationship = ?, emergency_contact_phone = ?
            WHERE lrn = ?");
        $stmt->bind_param("sissssssssssss", 
            $name, 
            $section_id,  
            $sex, 
            $_POST['date_of_birth'], 
            $_POST['nationality'], 
            $address, 
            $_POST['father_name'], 
            $_POST['father_phone'], 
            $_POST['mother_name'], 
            $_POST['mother_phone'], 
            $_POST['emergency_contact_name'], 
            $_POST['emergency_contact_relationship'], 
            $_POST['emergency_contact_phone'],
            $lrn
        );
        $stmt->execute();
        $stmt->close();

        // Update medical_history table
        $allergies = isset($_POST['allergies']) ? 1 : 0;
        $asthma = isset($_POST['asthma']) ? 1 : 0;
        $chicken_pox = isset($_POST['chicken_pox']) ? 1 : 0;
        $diabetes = isset($_POST['diabetes']) ? 1 : 0;
        $epilepsy = isset($_POST['epilepsy']) ? 1 : 0;
        $heart_disorder = isset($_POST['heart_disorder']) ? 1 : 0;
        $kidney_disease = isset($_POST['kidney_disease']) ? 1 : 0;
        $tuberculosis = isset($_POST['tuberculosis']) ? 1 : 0;
        $mumps = isset($_POST['mumps']) ? 1 : 0;
        $other_medical_history = isset($_POST['other_medical_history']) ? 1 : 0;
        $date_of_confinement = !empty($_POST['date_of_confinement']) ? $_POST['date_of_confinement'] : null;
        $confinement_details = !empty($_POST['confinement_details']) ? $_POST['confinement_details'] : '';

        $stmt = $conn->prepare("UPDATE medical_history SET
            allergies = ?, allergies_details = ?, asthma = ?, asthma_details = ?,
            chicken_pox = ?, chicken_pox_details = ?, diabetes = ?, diabetes_details = ?,
            epilepsy = ?, epilepsy_details = ?, heart_disorder = ?, heart_disorder_details = ?,
            kidney_disease = ?, kidney_disease_details = ?, tuberculosis = ?, tuberculosis_details = ?,
            mumps = ?, mumps_details = ?, other_medical_history = ?, other_medical_history_details = ?,
            date_of_confinement = ?, confinement_details = ?
            WHERE student_id = ?");
        $stmt->bind_param(
            "isisisisisisisisisssssi",
            $allergies, $_POST['allergies_details'],
            $asthma, $_POST['asthma_details'],
            $chicken_pox, $_POST['chicken_pox_details'],
            $diabetes, $_POST['diabetes_details'],
            $epilepsy, $_POST['epilepsy_details'],
            $heart_disorder, $_POST['heart_disorder_details'],
            $kidney_disease, $_POST['kidney_disease_details'],
            $tuberculosis, $_POST['tuberculosis_details'],
            $mumps, $_POST['mumps_details'],
            $other_medical_history, $_POST['other_medical_history_details'],
            $date_of_confinement, $confinement_details,
            $student_id
        );
        $stmt->execute();
        $stmt->close();

        // Update smoking_history
        if (isset($_POST['smoking'])) {
            if ($_POST['smoking'] === 'yes') {
                $age = !empty($_POST['smoking_age']) ? $_POST['smoking_age'] : 0;
                $sticks = !empty($_POST['sticks_per_day']) ? $_POST['sticks_per_day'] : 0;
                $stmt = $conn->prepare("SELECT * FROM smoking_history WHERE student_id = ?");
                $stmt->bind_param("i", $student_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->fetch_assoc()) {
                    $stmt2 = $conn->prepare("UPDATE smoking_history SET smokes = 'yes', age_of_onset = ?, sticks_per_day = ? WHERE student_id = ?");
                    $stmt2->bind_param("iii", $age, $sticks, $student_id);
                    $stmt2->execute();
                    $stmt2->close();
                } else {
                    $stmt2 = $conn->prepare("INSERT INTO smoking_history (student_id, smokes, age_of_onset, sticks_per_day) VALUES (?, 'yes', ?, ?)");
                    $stmt2->bind_param("iii", $student_id, $age, $sticks);
                    $stmt2->execute();
                    $stmt2->close();
                }
                $stmt->close();
            } else {
                $stmt = $conn->prepare("DELETE FROM smoking_history WHERE student_id = ?");
                $stmt->bind_param("i", $student_id);
                $stmt->execute();
                $stmt->close();
            }
        }

        // Update menstruation_history for female students
        if ($_POST['sex'] === 'Female') {
            $menarche = !empty($_POST['menarche']) ? $_POST['menarche'] : '';
            $last_menstrual_period = !empty($_POST['last_menstrual_period']) ? $_POST['last_menstrual_period'] : null;
            $ob_score = !empty($_POST['ob_score']) ? $_POST['ob_score'] : '';
            $stmt = $conn->prepare("SELECT * FROM menstruation_history WHERE student_id = ?");
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->fetch_assoc()) {
                $stmt2 = $conn->prepare("UPDATE menstruation_history SET menarche = ?, last_menstrual_period = ?, ob_score = ? WHERE student_id = ?");
                $stmt2->bind_param("sssi", $menarche, $last_menstrual_period, $ob_score, $student_id);
                $stmt2->execute();
                $stmt2->close();
            } else {
                $stmt2 = $conn->prepare("INSERT INTO menstruation_history (student_id, menarche, last_menstrual_period, ob_score) VALUES (?, ?, ?, ?)");
                $stmt2->bind_param("isss", $student_id, $menarche, $last_menstrual_period, $ob_score);
                $stmt2->execute();
                $stmt2->close();
            }
            $stmt->close();
        } else {
            $stmt = $conn->prepare("DELETE FROM menstruation_history WHERE student_id = ?");
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $stmt->close();
        }

        // Update special_needs
        $hasSpecialNeeds = isset($_POST['special_needs']) && $_POST['special_needs'] === 'yes' ? 1 : 0;
        $physical_limitations = isset($_POST['physical_limitations']) ? 1 : 0;
        $emotional_disorder = isset($_POST['emotional_disorder']) ? 1 : 0;
        $natural_conditions = isset($_POST['natural_conditions']) ? 1 : 0;
        $attention_conditions = isset($_POST['attention_conditions']) ? 1 : 0;
        $medical_conditions = isset($_POST['medical_conditions']) ? 1 : 0;
        $receiving_treatment = isset($_POST['receiving_condition']) && $_POST['receiving_condition'] === 'yes' ? 1 : 0;
        $contact_permission = isset($_POST['contact_permission']) && $_POST['contact_permission'] === 'yes' ? 1 : 0;
        $health_prof_name = !empty($_POST['health_professional_name']) ? $_POST['health_professional_name'] : '';
        $health_prof_addr = !empty($_POST['health_professional_address']) ? $_POST['health_professional_address'] : '';
        $health_prof_office = !empty($_POST['health_professional_office_number']) ? $_POST['health_professional_office_number'] : '';
        $health_prof_mobile = !empty($_POST['health_professional_mobile_number']) ? $_POST['health_professional_mobile_number'] : '';
        $stmt = $conn->prepare("UPDATE special_needs SET
            has_special_needs = ?, physical_limitations = ?, physical_limitations_specify = ?,
            emotional_disorder = ?, emotional_disorder_specify = ?, natural_conditions = ?, natural_conditions_specify = ?,
            attention_conditions = ?, attention_conditions_specify = ?, medical_conditions = ?, medical_conditions_specify = ?,
            receiving_treatment = ?, contact_permission = ?, health_professional_name = ?, health_professional_address = ?,
            health_professional_office_number = ?, health_professional_mobile_number = ?
            WHERE student_id = ?");
        $stmt->bind_param("iisisisisisisssssi",
            $hasSpecialNeeds,
            $physical_limitations, $_POST['physical_limitations_specify'],
            $emotional_disorder, $_POST['emotional_disorder_specify'],
            $natural_conditions, $_POST['natural_conditions_specify'],
            $attention_conditions, $_POST['attention_conditions_specify'],
            $medical_conditions, $_POST['medical_conditions_specify'],
            $receiving_treatment, $contact_permission,
            $health_prof_name, $health_prof_addr,
            $health_prof_office, $health_prof_mobile,
            $student_id
        );
        $stmt->execute();
        $stmt->close();

        // Update guardians
        $guardian_name = !empty($_POST['guardian_name']) ? $_POST['guardian_name'] : '';
        $guardian_date = !empty($_POST['guardian_date']) ? $_POST['guardian_date'] : null;
        $stmt = $conn->prepare("SELECT * FROM guardians WHERE student_id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_assoc()) {
            $stmt2 = $conn->prepare("UPDATE guardians SET guardian_name = ?, guardian_date = ? WHERE student_id = ?");
            $stmt2->bind_param("ssi", $guardian_name, $guardian_date, $student_id);
            $stmt2->execute();
            $stmt2->close();
        } else {
            $stmt2 = $conn->prepare("INSERT INTO guardians (student_id, guardian_name, guardian_date) VALUES (?, ?, ?)");
            $stmt2->bind_param("iss", $student_id, $guardian_name, $guardian_date);
            $stmt2->execute();
            $stmt2->close();
        }
        $stmt->close();

        // Handle immunizations - first delete existing ones, then insert new ones
        $stmt = $conn->prepare("DELETE FROM immunization_record WHERE student_id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $stmt->close();
        if (isset($_POST['vaccine_name'])) {
            $stmt = $conn->prepare("INSERT INTO immunization_record (student_id, vaccine_name, dose, date_given) VALUES (?, ?, ?, ?)");
            foreach ($_POST['vaccine_name'] as $index => $vaccineName) {
                $dose = $_POST['dose'][$index];
                $dateGiven = $_POST['date_given'][$index];
                $stmt->bind_param("isss", $student_id, $vaccineName, $dose, $dateGiven);
                $stmt->execute();
            }
            $stmt->close();
        }
        $conn->commit();
        echo "<script>alert('Record updated successfully!'); window.location.href=window.location.href;</script>";
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        die("Error updating record: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student Health Record</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/view_records.css">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        @media (max-width: 768px) {
            .container, form#student-form, fieldset, .form-group, .checkbox-group, table, th, td {
                width: 100% !important;
                max-width: 100vw !important;
                min-width: 0 !important;
                box-sizing: border-box;
            }
            input, select, button, textarea {
                width: 100% !important;
                max-width: 100vw !important;
                min-width: 0 !important;
                box-sizing: border-box;
            }
            .burger {
                display: flex !important;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                width: 40px;
                height: 40px;
                cursor: pointer;
                z-index: 1100;
            }
            /* Immunization Record mobile styles */
            #vaccine-entries .vaccine-entry {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 8px !important;
            }
            #vaccine-entries .vaccine-entry button.remove-vaccine {
                width: 100% !important;
                margin-left: 0 !important;
                margin-top: 5px !important;
            }
            #add-vaccine {
                width: 100% !important;
                margin-top: 10px !important;
            }
        }
    </style>
</head>
<body>
    <div class="container" style="margin-top:70px;">
        <form id="student-form" method="POST">
            <!-- Student Information -->
            <fieldset>
                <legend>Student Information</legend>
                <div class="form-group">
                    <label>Name: <input type="text" name="name" value="<?php echo htmlspecialchars($studentInfo['name']); ?>" required></label>
                    <label>Section: <select name="section_id" required>
                        <option value="">Select Section</option>
                        <?php foreach ($sections as $section): ?>
                            <option value="<?php echo $section['section_id']; ?>" <?php echo ($studentInfo['section_id'] == $section['section_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($section['section_name']); ?></option>
                        <?php endforeach; ?>
                    </select></label>
                    <label>Sex: <select name="sex" required>
                        <option value="">Select</option>
                        <option value="Male" <?php echo ($studentInfo['sex'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($studentInfo['sex'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                    </select></label>
                    <label>LRN: <input type="number" name="lrn" value="<?php echo htmlspecialchars($studentInfo['lrn']); ?>" readonly></label>
                </div>
                <div class="form-group">
                    <label>Date of Birth: <input type="date" name="date_of_birth" value="<?php echo htmlspecialchars($studentInfo['date_of_birth']); ?>" required></label>
                    <label>Nationality: <input type="text" name="nationality" value="<?php echo htmlspecialchars($studentInfo['nationality']); ?>" required></label>
                </div>
                <div class="form-group">
                    <label>Address: <input type="text" name="address" value="<?php echo htmlspecialchars($studentInfo['address']); ?>" required></label>
                </div>
                <div class="form-group">
                    <label>Father's Name: <input type="text" name="father_name" value="<?php echo htmlspecialchars($studentInfo['father_name']); ?>"></label>
                    <label>Phone Number: <input type="tel" name="father_phone" value="<?php echo htmlspecialchars($studentInfo['father_phone']); ?>"></label>
                </div>
                <div class="form-group">
                    <label>Mother's Name: <input type="text" name="mother_name" value="<?php echo htmlspecialchars($studentInfo['mother_name']); ?>"></label>
                    <label>Phone Number: <input type="tel" name="mother_phone" value="<?php echo htmlspecialchars($studentInfo['mother_phone']); ?>"></label>
                </div>
                <div class="form-group">
                    <label>Emergency Contact Person: <input type="text" name="emergency_contact_name" value="<?php echo htmlspecialchars($studentInfo['emergency_contact_name']); ?>"></label>
                    <label>Relationship: <input type="text" name="emergency_contact_relationship" value="<?php echo htmlspecialchars($studentInfo['emergency_contact_relationship']); ?>"></label>
                    <label>Phone Number: <input type="tel" name="emergency_contact_phone" value="<?php echo htmlspecialchars($studentInfo['emergency_contact_phone']); ?>"></label>
                </div>
            </fieldset>
            <!-- Immunization Record Section -->
            <fieldset>
                <legend>Immunization Record</legend>
                <div id="vaccine-entries">
                    <?php if (!empty($immunization_records)): ?>
                        <?php foreach ($immunization_records as $immunization): ?>
                            <div class="vaccine-entry" style="display: flex; align-items: center; gap: 10px; min-height: 40px;">
                                <input type="text" style="width: 180px; height: 40px;" name="vaccine_name[]" value="<?php echo htmlspecialchars($immunization['vaccine_name']); ?>" required>
                                <input type="text" style="width: 180px; height: 40px;" name="dose[]" value="<?php echo htmlspecialchars($immunization['dose']); ?>" required>
                                <input type="date" style="width: 180px; height: 40px;" name="date_given[]" value="<?php echo htmlspecialchars($immunization['date_given']); ?>" required>
                                <button type="button" style="width: 120px; height: 40px; margin-left: 10px; background: #ff4d4d; color: #fff; border: none; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-weight: 500; cursor: pointer;" class="remove-vaccine">Remove</button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <button type="button" id="add-vaccine" style="width: 100%; height: 40px; background: #223a7a; color: #fff; border: none; border-radius: 4px; font-weight: 500; margin-top: 10px;">Add Vaccine</button>
            </fieldset>
            <!-- Past Medical History Section -->
            <fieldset>
                <legend>Past Medical History</legend>
                <div class="checkbox-group">
                    <label><i class="small-text">(Check diseases you have had and indicate age/year of condition)</i></label>
                    <label>
                        <input type="checkbox" name="allergies" <?php echo ($medicalHistory['allergies'] ?? 0) ? 'checked' : ''; ?>> Allergies
                        <input type="text" name="allergies_details" placeholder="Please specify..." class="specify-input" value="<?php echo htmlspecialchars($medicalHistory['allergies_details'] ?? ''); ?>">
                    </label>
                    <label>
                        <input type="checkbox" name="asthma" <?php echo ($medicalHistory['asthma'] ?? 0) ? 'checked' : ''; ?>> Bronchial Asthma
                        <input type="text" name="asthma_details" placeholder="Please specify..." class="specify-input" value="<?php echo htmlspecialchars($medicalHistory['asthma_details'] ?? ''); ?>">
                    </label>
                    <label>
                        <input type="checkbox" name="chicken_pox" <?php echo ($medicalHistory['chicken_pox'] ?? 0) ? 'checked' : ''; ?>> Chicken Pox
                        <input type="text" name="chicken_pox_details" placeholder="Please specify..." class="specify-input" value="<?php echo htmlspecialchars($medicalHistory['chicken_pox_details'] ?? ''); ?>">
                    </label>
                    <label>
                        <input type="checkbox" name="diabetes" <?php echo ($medicalHistory['diabetes'] ?? 0) ? 'checked' : ''; ?>> Diabetes Mellitus
                        <input type="text" name="diabetes_details" placeholder="Please specify..." class="specify-input" value="<?php echo htmlspecialchars($medicalHistory['diabetes_details'] ?? ''); ?>">
                    </label>
                    <label>
                        <input type="checkbox" name="epilepsy" <?php echo ($medicalHistory['epilepsy'] ?? 0) ? 'checked' : ''; ?>> Epilepsy
                        <input type="text" name="epilepsy_details" placeholder="Please specify..." class="specify-input" value="<?php echo htmlspecialchars($medicalHistory['epilepsy_details'] ?? ''); ?>">
                    </label>
                    <label>
                        <input type="checkbox" name="heart_disorder" <?php echo ($medicalHistory['heart_disorder'] ?? 0) ? 'checked' : ''; ?>> Heart Disorder
                        <input type="text" name="heart_disorder_details" placeholder="Please specify..." class="specify-input" value="<?php echo htmlspecialchars($medicalHistory['heart_disorder_details'] ?? ''); ?>">
                    </label>
                    <label>
                        <input type="checkbox" name="kidney_disease" <?php echo ($medicalHistory['kidney_disease'] ?? 0) ? 'checked' : ''; ?>> Kidney Disease
                        <input type="text" name="kidney_disease_details" placeholder="Please specify..." class="specify-input" value="<?php echo htmlspecialchars($medicalHistory['kidney_disease_details'] ?? ''); ?>">
                    </label>
                    <label>
                        <input type="checkbox" name="tuberculosis" <?php echo ($medicalHistory['tuberculosis'] ?? 0) ? 'checked' : ''; ?>> Tuberculosis
                        <input type="text" name="tuberculosis_details" placeholder="Please specify..." class="specify-input" value="<?php echo htmlspecialchars($medicalHistory['tuberculosis_details'] ?? ''); ?>">
                    </label>
                    <label>
                        <input type="checkbox" name="mumps" <?php echo ($medicalHistory['mumps'] ?? 0) ? 'checked' : ''; ?>> Mumps
                        <input type="text" name="mumps_details" placeholder="Please specify..." class="specify-input" value="<?php echo htmlspecialchars($medicalHistory['mumps_details'] ?? ''); ?>">
                    </label>
                    <label>
                        <input type="checkbox" name="other_medical_history" <?php echo ($medicalHistory['other_medical_history'] ?? 0) ? 'checked' : ''; ?>> Other
                        <input type="text" name="other_medical_history_details" placeholder="Please specify..." class="specify-input" value="<?php echo htmlspecialchars($medicalHistory['other_medical_history_details'] ?? ''); ?>">
                    </label>
                    <table>
                        <tr>
                            <th>History of Confinement</th>
                        </tr>
                        <tr>
                            <td>
                                <label>Date of Confinement: <input type="date" name="date_of_confinement" value="<?php echo htmlspecialchars($medicalHistory['date_of_confinement'] ?? ''); ?>"></label>
                                <label><i class="small-text">(Please specify diagnosis and any pertinent information):</i></label>
                                <input type="text" name="confinement_details" style="width: 100%; height: 100px;" placeholder="Specify..." value="<?php echo htmlspecialchars($medicalHistory['confinement_details'] ?? ''); ?>">
                            </td>
                        </tr>
                    </table>
                </div>
            </fieldset>
            <!-- Smoking History Section -->
            <fieldset>
                <legend>Smoking History</legend>
                <div class="form-group">
                    <div class="radio-group">
                        <label class="smoking-label">Do you smoke?</label>
                        <label><input type="radio" name="smoking" value="yes" <?php echo ($smokingHistory && strtolower($smokingHistory['smokes']) === 'yes') ? 'checked' : ''; ?>> Yes</label>
                        <label><input type="radio" name="smoking" value="no" <?php echo (!$smokingHistory || strtolower($smokingHistory['smokes']) === 'no') ? 'checked' : ''; ?>> No</label>
                    </div>
                    <div class="smoking-details" style="display: <?php echo ($smokingHistory && strtolower($smokingHistory['smokes']) === 'yes') ? 'block' : 'none'; ?>;">
                        <label class="smoking-detail-label">
                            Age when started: <input type="number" name="smoking_age" class="smoking-info" value="<?php echo htmlspecialchars($smokingHistory['age_of_onset'] ?? ''); ?>">
                        </label>
                        <label class="smoking-detail-label">
                            Sticks per day: <input type="number" name="sticks_per_day" class="smoking-info" value="<?php echo htmlspecialchars($smokingHistory['sticks_per_day'] ?? ''); ?>">
                        </label>
                    </div>
                </div>
            </fieldset>
            <!-- Menstruation Section (for female students) -->
            <?php if ($studentInfo['sex'] === 'Female'): ?>
                <fieldset>
                    <legend>Menstruation</legend>
                    <table>
                        <tr>
                            <td colspan="2"><strong>Age when period started: </strong></td>
                            <td><input type="text" name="menarche" value="<?php echo htmlspecialchars($menstruationHistory['menarche'] ?? ''); ?>"></td>
                        </tr>
                        <tr>
                            <td colspan="2"><strong>Last Menstrual Period: </strong></td>
                            <td><input type="date" name="last_menstrual_period" value="<?php echo htmlspecialchars($menstruationHistory['last_menstrual_period'] ?? ''); ?>"></td>
                        </tr>
                        <tr>
                            <td colspan="2"><strong>OB score: (if with previous pregnancy) </strong></td>
                            <td><input type="text" name="ob_score" value="<?php echo htmlspecialchars($menstruationHistory['ob_score'] ?? ''); ?>"></td>
                        </tr>
                    </table>
                </fieldset>
            <?php endif; ?>
            <!-- Special Needs Section -->
            <fieldset>
                <legend>Special Needs Assessment</legend>
                <div class="form-group">
                    <label>Has Special Needs: 
                        <select name="special_needs" required>
                            <option value="yes" <?php echo ($specialNeeds['has_special_needs'] ?? 0) ? 'selected' : ''; ?>>Yes</option>
                            <option value="no" <?php echo !($specialNeeds['has_special_needs'] ?? 0) ? 'selected' : ''; ?>>No</option>
                        </select>
                    </label>
                </div>
                <div class="checkbox-group">
                    <label>
                        <input type="checkbox" name="physical_limitations" <?php echo ($specialNeeds['physical_limitations'] ?? 0) ? 'checked' : ''; ?>> Physical limitations
                        <input type="text" name="physical_limitations_specify" placeholder="Please specify..." class="specify-input" value="<?php echo htmlspecialchars($specialNeeds['physical_limitations_specify'] ?? ''); ?>">
                    </label>
                    <label>
                        <input type="checkbox" name="emotional_disorder" <?php echo ($specialNeeds['emotional_disorder'] ?? 0) ? 'checked' : ''; ?>> Emotional or behavioral disorder
                        <input type="text" name="emotional_disorder_specify" placeholder="Please specify..." class="specify-input" value="<?php echo htmlspecialchars($specialNeeds['emotional_disorder_specify'] ?? ''); ?>">
                    </label>
                    <label>
                        <input type="checkbox" name="natural_conditions" <?php echo ($specialNeeds['natural_conditions'] ?? 0) ? 'checked' : ''; ?>> Natural conditions
                        <input type="text" name="natural_conditions_specify" placeholder="Please specify..." class="specify-input" value="<?php echo htmlspecialchars($specialNeeds['natural_conditions_specify'] ?? ''); ?>">
                    </label>
                    <label>
                        <input type="checkbox" name="attention_conditions" <?php echo ($specialNeeds['attention_conditions'] ?? 0) ? 'checked' : ''; ?>> Conditions related to attention or concentration
                        <input type="text" name="attention_conditions_specify" placeholder="Please specify..." class="specify-input" value="<?php echo htmlspecialchars($specialNeeds['attention_conditions_specify'] ?? ''); ?>">
                    </label>
                    <label>
                        <input type="checkbox" name="medical_conditions" <?php echo ($specialNeeds['medical_conditions'] ?? 0) ? 'checked' : ''; ?>> Ongoing long-standing medical conditions
                        <input type="text" name="medical_conditions_specify" placeholder="Please specify..." class="specify-input" value="<?php echo htmlspecialchars($specialNeeds['medical_conditions_specify'] ?? ''); ?>">
                    </label>
                </div>
                <div class="form-group">
                    <label>Receiving Treatment: 
                        <select name="receiving_condition" required>
                            <option value="yes" <?php echo ($specialNeeds['receiving_treatment'] ?? 0) ? 'selected' : ''; ?>>Yes</option>
                            <option value="no" <?php echo !($specialNeeds['receiving_treatment'] ?? 0) ? 'selected' : ''; ?>>No</option>
                        </select>
                    </label>
                </div>
                <div class="form-group">
                    <label>Permission to Contact Health Professional: 
                        <select name="contact_permission" required>
                            <option value="yes" <?php echo ($specialNeeds['contact_permission'] ?? 0) ? 'selected' : ''; ?>>Yes</option>
                            <option value="no" <?php echo !($specialNeeds['contact_permission'] ?? 0) ? 'selected' : ''; ?>>No</option>
                        </select>
                    </label>
                </div>
                <div class="form-group">
                    <label>Name of Health Professional: <input type="text" name="health_professional_name" value="<?php echo htmlspecialchars($specialNeeds['health_professional_name'] ?? ''); ?>"></label>
                </div>
                <div class="form-group">
                    <label>Office Address: <input type="text" name="health_professional_address" value="<?php echo htmlspecialchars($specialNeeds['health_professional_address'] ?? ''); ?>"></label>
                </div>
                <div class="form-group">
                    <label>Office Number: <input type="text" name="health_professional_office_number" value="<?php echo htmlspecialchars($specialNeeds['health_professional_office_number'] ?? ''); ?>"></label>
                </div>
                <div class="form-group">
                    <label>Mobile Number: <input type="text" name="health_professional_mobile_number" value="<?php echo htmlspecialchars($specialNeeds['health_professional_mobile_number'] ?? ''); ?>"></label>
                </div>
            </fieldset>
            <!-- Guardian Section -->
            <fieldset>
                <legend>Parent/Guardian</legend>
                <div class="form-group">
                    <label>Name: <input type="text" name="guardian_name" value="<?php echo htmlspecialchars($guardian['guardian_name'] ?? ''); ?>" required></label>
                    <label>Date: <input type="date" name="guardian_date" value="<?php echo htmlspecialchars($guardian['guardian_date'] ?? ''); ?>" required></label>
                </div>
            </fieldset>
            <!-- Physical Examination Section (readonly, for display only) -->
            <fieldset>
                <legend>Physical Examination</legend>
                <label><i class="small-text">(Filled up by school personnel.)</i></label>
                <div class="form-group">
                    <label>Height (cm): <input type="text" name="height" value="<?php echo htmlspecialchars($physicalExam['height'] ?? ''); ?>" readonly></label>
                    <label>Weight (kg): <input type="text" name="weight" value="<?php echo htmlspecialchars($physicalExam['weight'] ?? ''); ?>" readonly></label>
                    <label>BMI: <input type="text" name="bmi" value="<?php echo htmlspecialchars($physicalExam['bmi'] ?? ''); ?>" readonly></label>
                </div>
                <div class="form-group">
                    <label>Blood Pressure: <input type="text" name="blood_pressure" value="<?php echo htmlspecialchars($physicalExam['blood_pressure'] ?? ''); ?>" readonly></label>
                    <label>Pulse Rate: <input type="text" name="pulse_rate" value="<?php echo htmlspecialchars($physicalExam['pulse_rate'] ?? ''); ?>" readonly></label>
                </div>
                <div class="form-group">
                    <label>Respiratory Rate: <input type="text" name="respiratory_rate" value="<?php echo htmlspecialchars($physicalExam['respiratory_rate'] ?? ''); ?>" readonly></label>
                    <label>Remarks: <input type="text" name="remarks" value="<?php echo (isset($physicalExam['remarks']) && ($physicalExam['remarks'] !== '0' && $physicalExam['remarks'] !== 0)) ? htmlspecialchars($physicalExam['remarks']) : ''; ?>" readonly></label>
                </div>
                <div class="form-group">
                    <label>Examined By: <input type="text" name="examined_by" value="<?php echo htmlspecialchars($physicalExam['examined_by'] ?? ''); ?>" readonly></label>
                    <label>Date: <input type="date" name="exam_date" value="<?php echo htmlspecialchars($physicalExam['exam_date'] ?? ''); ?>" readonly></label>
                </div>
            </fieldset>
            <button type="submit">Update Record</button>
        </form>
    </div>
    <script>
        // JavaScript for Dynamic Vaccine Entries
        document.getElementById('add-vaccine').addEventListener('click', function () {
            const vaccineEntries = document.getElementById('vaccine-entries');
            const entryDiv = document.createElement('div');
            entryDiv.className = 'vaccine-entry';
            entryDiv.style.display = 'flex';
            entryDiv.style.alignItems = 'center';
            entryDiv.style.gap = '10px';
            entryDiv.style.minHeight = '40px';
            entryDiv.innerHTML = `
                <input type="text" style="width: 180px; height: 40px;" name="vaccine_name[]" placeholder="Vaccine Name" required>
                <input type="text" style="width: 180px; height: 40px;" name="dose[]" placeholder="Dose/Booster" required>
                <input type="date" style="width: 180px; height: 40px;" name="date_given[]" placeholder="Date Given" required>
                <button type="button" style="width: 120px; height: 40px; margin-left: 10px; background: #ff4d4d; color: #fff; border: none; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-weight: 500; cursor: pointer;" class="remove-vaccine">Remove</button>
            `;
            vaccineEntries.appendChild(entryDiv);
            entryDiv.querySelector('.remove-vaccine').addEventListener('click', function () {
                vaccineEntries.removeChild(entryDiv);
            });
        });
        document.querySelectorAll('.remove-vaccine').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.vaccine-entry').remove();
            });
        });
        // Show/hide smoking details based on radio selection
        document.querySelectorAll('input[name="smoking"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const smokingDetails = document.querySelector('.smoking-details');
                if (this.value === 'yes' && this.checked) {
                    smokingDetails.style.display = 'block';
                    document.querySelector('input[name="smoking_age"]').setAttribute('required', 'required');
                    document.querySelector('input[name="sticks_per_day"]').setAttribute('required', 'required');
                } else if (this.value === 'no' && this.checked) {
                    smokingDetails.style.display = 'none';
                    document.querySelector('input[name="smoking_age"]').removeAttribute('required');
                    document.querySelector('input[name="sticks_per_day"]').removeAttribute('required');
                }
            });
        });
        if (document.querySelector('input[name="smoking"][value="yes"]').checked) {
            document.querySelector('.smoking-details').style.display = 'block';
        } else {
            document.querySelector('.smoking-details').style.display = 'none';
        }
    </script>
</body>
</html>
