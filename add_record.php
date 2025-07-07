<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /health_record/user_login.php");
    exit();
}

include 'sqlconnection.php';
include 'user_navbar.php';

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Fetch user's data from the database
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT u.lrn, u.first_name, u.middle_name, u.last_name, 
                       u.section_id, s.section_name
                       FROM users u 
                       LEFT JOIN section s ON u.section_id = s.section_id 
                       WHERE u.id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$userLrn = $user['lrn'];
$userSectionId = $user['section_id'];
$userSectionName = $user['section_name'];
$userFirstName = $user['first_name'];
$userMiddleName = $user['middle_name'];
$userLastName = $user['last_name'];
$stmt->close();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($isAjax) {
        header('Content-Type: application/json');
    }

    // Add LRN validation with debug logging
    error_log('Posted LRN: [' . $_POST['lrn'] . ']');
    error_log('User LRN: [' . $userLrn . ']');

    // Convert to strings and ensure proper comparison
    $postedLrn = strval(trim($_POST['lrn']));
    $storedLrn = strval(trim($userLrn));

    if ($postedLrn !== $storedLrn) {
        if ($isAjax) {
            echo json_encode([
                'status' => 'error',
                'message' => "LRN mismatch. Please check your LRN."
            ]);
        }
        exit;
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Validate and sanitize input
        $name = !empty($_POST['name']) ? trim($_POST['name']) : '';
        $section_id = !empty($_POST['section_id']) ? trim($_POST['section_id']) : '';
        $sex = !empty($_POST['sex']) ? $_POST['sex'] : '';
        $lrn = !empty($_POST['lrn']) ? $_POST['lrn'] : 0;
        $date_of_birth = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : '';
        $address = !empty($_POST['address']) ? trim($_POST['address']) : '';

        // Check for required fields
        if (empty($name) || empty($section_id) || empty($sex) || empty($lrn) || empty($date_of_birth) || empty($address)) {
            throw new Exception("All required fields must be filled.");
        }

        // Insert into student_info table (assuming this exists based on your form)
        $stmt = $conn->prepare("INSERT INTO student_info (name, section_id, sex, lrn, date_of_birth, nationality, address, father_name, father_phone, mother_name, mother_phone, emergency_contact_name, emergency_contact_relationship, emergency_contact_phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssissssssssss", 
            $name, 
            $section_id,  
            $sex, 
            $lrn, 
            $date_of_birth, 
            $_POST['nationality'], 
            $address, 
            $_POST['father_name'], 
            $_POST['father_phone'], 
            $_POST['mother_name'], 
            $_POST['mother_phone'], 
            $_POST['emergency_contact_name'], 
            $_POST['emergency_contact_relationship'], 
            $_POST['emergency_contact_phone']
        );
        $stmt->execute();
        $student_id = $stmt->insert_id;
        $stmt->close();

        // Insert into medical_history table
        $stmt = $conn->prepare("INSERT INTO medical_history (
            student_id, allergies, allergies_details, asthma, asthma_details,
            chicken_pox, chicken_pox_details, diabetes, diabetes_details,
            epilepsy, epilepsy_details, heart_disorder, heart_disorder_details,
            kidney_disease, kidney_disease_details, tuberculosis, tuberculosis_details,
            mumps, mumps_details, other_medical_history, other_medical_history_details,
            date_of_confinement, confinement_details
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        // Get POSTed values or set defaults
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
        $doc = !empty($_POST['doc']) ? $_POST['doc'] : null;
        $confinement_details = !empty($_POST['confinement_details']) ? $_POST['confinement_details'] : '';

        $stmt->bind_param("iisisisisisisisisisisiss", 
            $student_id,
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
            $doc, $confinement_details
        );
        $stmt->execute();
        $stmt->close();

        // Insert into smoking_history table
        if (isset($_POST['smoking']) && $_POST['smoking'] === 'yes') {
            $stmt = $conn->prepare("INSERT INTO smoking_history (
                student_id, smokes, age_of_onset, sticks_per_day
            ) VALUES (?, 'yes', ?, ?)");
            $age = !empty($_POST['smoking_age']) ? $_POST['smoking_age'] : 0;
            $sticks = !empty($_POST['sticks_per_day']) ? $_POST['sticks_per_day'] : 0;
            $stmt->bind_param("iii", $student_id, $age, $sticks);
            $stmt->execute();
            $stmt->close();
        }

        // Insert into menstruation_history table for female students
        if ($_POST['sex'] === 'Female') {
            $stmt = $conn->prepare("INSERT INTO menstruation_history (
                student_id, menarche, last_menstrual_period, ob_score
            ) VALUES (?, ?, ?, ?)");
            $menarche = !empty($_POST['menarche']) ? $_POST['menarche'] : '';
            $last_period = !empty($_POST['dateperiod']) ? $_POST['dateperiod'] : null;
            $ob_score = !empty($_POST['ob_score']) ? $_POST['ob_score'] : '';
            $stmt->bind_param("isss", $student_id, $menarche, $last_period, $ob_score);
            $stmt->execute();
            $stmt->close();
        }

        // Insert into special_needs table
        $stmt = $conn->prepare("INSERT INTO special_needs (
            student_id, has_special_needs, physical_limitations, physical_limitations_details,
            emotional_disorder, emotional_disorder_details, natural_conditions, natural_conditions_details,
            attention_conditions, attention_conditions_details, medical_conditions, medical_conditions_details,
            receiving_treatment, contact_permission, health_professional_name, health_professional_address,
            health_professional_office, health_professional_mobile
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        // Prepare special needs data
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
        
        $stmt->bind_param("iisisisisisisssss",
            $student_id, $hasSpecialNeeds,
            $physical_limitations, $_POST['physical_limitations_specify'],
            $emotional_disorder, $_POST['emotional_disorder_specify'],
            $natural_conditions, $_POST['natural_conditions_specify'],
            $attention_conditions, $_POST['attention_conditions_specify'],
            $medical_conditions, $_POST['medical_conditions_specify'],
            $receiving_treatment, $contact_permission,
            $health_prof_name, $health_prof_addr,
            $health_prof_office, $health_prof_mobile
        );
        $stmt->execute();
        $stmt->close();

        // Insert into guardians table
        $stmt = $conn->prepare("INSERT INTO guardians (
            student_id, guardian_name, guardian_date
        ) VALUES (?, ?, ?)");
        $guardian_name = !empty($_POST['guardian_name']) ? $_POST['guardian_name'] : '';
        $guardian_date = !empty($_POST['guardian_date']) ? $_POST['guardian_date'] : null;
        $stmt->bind_param("iss", $student_id, $guardian_name, $guardian_date);
        $stmt->execute();
        $stmt->close();

        // Update has_record in users table
        $updateUserStmt = $conn->prepare("UPDATE users SET has_record = 1 WHERE id = ?");
        $updateUserStmt->bind_param("i", $_SESSION['user_id']);
        $updateUserStmt->execute();
        $updateUserStmt->close();

        // Commit transaction
        $conn->commit();

        // Return success response
        if ($isAjax) {
            echo json_encode([
                'status' => 'success',
                'message' => 'New records created successfully.'
            ]);
        } else {
            header('Location: landing.php');
        }
        exit;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();

        // Return error response
        if ($isAjax) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        } else {
            die($e->getMessage());
        }
        exit;
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Health Record Form</title>
    <link rel="stylesheet" href="css/add_new_record.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <form id="student-form" method="POST" action="">
            <fieldset>
                <legend>Student Information</legend>
                <div class="form-group">
                    <label>Name: <input type="text" name="name" required value="<?php echo htmlspecialchars($userFirstName . ' ' . $userMiddleName . ' ' . $userLastName ?? ''); ?>" readonly></label>
                    <label>Class Section: <input type="text" name="section_name" value="<?php echo htmlspecialchars($userSectionName ?? ''); ?>" readonly>
                    <input type="hidden" name="section_id" value="<?php echo htmlspecialchars($userSectionId ?? ''); ?>"></label>
                    <label>Sex: 
                        <select name="sex" required>
                            <option value="">Select</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </label>
                    <label>LRN: <input type="number" name="lrn" value="<?php echo htmlspecialchars($userLrn ?? ''); ?>" readonly></label>
                </div>
                <div class="form-group">
                    <label>Date of Birth: <input type="date" name="date_of_birth" required></label>
                    <label>Nationality: <input type="text" name="nationality" required></label>
                </div>
                <div class="form-group">
                    <label>Address: <input type="text" name="address" required></label>
                </div>
                <div class="form-group">
                    <label>Father’s Name: <input type="text" name="father_name"></label>
                    <label>Phone Number: <input type="tel" name="father_phone"></label>
                </div>
                <div class="form-group">
                    <label>Mother’s Name: <input type="text" name="mother_name"></label>
                    <label>Phone Number: <input type="tel" name="mother_phone"></label>
                </div>
                <div class="form-group">
                    <label>Emergency Contact Person: <input type="text" name="emergency_contact_name"></label>
                    <label>Relationship: <input type="text" name="emergency_contact_relationship"></label>
                    <label>Phone Number: <input type="tel" name="emergency_contact_phone"></label>
                </div>
            </fieldset>

            <!-- Immunization Record Section -->
            <fieldset>
                <legend>Immunization Record</legend>
                <div id="vaccine-entries">
                    <label><i class="small-text">(Click "Add Vaccine" and fill up the information.)</i></label>
                    <!-- Vaccine entries will be added here dynamically -->
                </div>
                <button type="button" id="add-vaccine" class="styled-btn">Add Vaccine</button>
            </fieldset>

            <!-- Past Medical History Section -->
            <fieldset>
                <legend>Past Medical History</legend>
                <div class="checkbox-group">
                    <label><i class="small-text">(Check diseases you have had and indicate age/year of condition)</i></label>
                    <label>
                        <input type="checkbox" name="allergies"> Allergies
                        <input type="text" name="allergies_details" placeholder="Please specify..." class="specify-input">
                    </label>
                    <label>
                        <input type="checkbox" name="asthma"> Bronchial Asthma
                        <input type="text" name="asthma_details" placeholder="Please specify..." class="specify-input">
                    </label>
                    <label>
                        <input type="checkbox" name="chicken_pox"> Chicken Pox
                        <input type="text" name="chicken_pox_details" placeholder="Please specify..." class="specify-input">
                    </label>
                    <label>
                        <input type="checkbox" name="diabetes"> Diabetes Mellitus
                        <input type="text" name="diabetes_details" placeholder="Please specify..." class="specify-input">
                    </label>
                    <label>
                        <input type="checkbox" name="epilepsy"> Epilepsy
                        <input type="text" name="epilepsy_details" placeholder="Please specify..." class="specify-input">
                    </label>
                    <label>
                        <input type="checkbox" name="heart_disorder"> Heart Disorder
                        <input type="text" name="heart_disorder_details" placeholder="Please specify..." class="specify-input">
                    </label>
                    <label>
                        <input type="checkbox" name="kidney_disease"> Kidney Disease
                        <input type="text" name="kidney_disease_details" placeholder="Please specify..." class="specify-input">
                    </label>
                    <label>
                        <input type="checkbox" name="tuberculosis"> Tuberculosis
                        <input type="text" name="tuberculosis_details" placeholder="Please specify..." class="specify-input">
                    </label>
                    <label>
                        <input type="checkbox" name="mumps"> Mumps
                        <input type="text" name="mumps_details" placeholder="Please specify..." class="specify-input">
                    </label>
                    <label>
                        <input type="checkbox" name="other_medical_history"> Other
                        <input type="text" name="other_medical_history_details" placeholder="Please specify..." class="specify-input">
                    </label>
                    <table>
                        <tr>
                            <th>History of Confinement</th>
                        </tr>
                        <tr>
                            <td>
                                <label>Date of Confinement: <input type="date" name="doc"></label>
                                <label><i class="small-text">(Please specify diagnosis and any pertinent information):</i></label>
                                <input type="text" name="confinement_details" style="width: 100%; height: 100px;" placeholder="Specify...">
                            </td>
                        </tr>
                    </table>
                </div>
            </fieldset>

            <!-- Smoking History Section -->
            <fieldset> 
                <legend>Smoking History</legend>
                <div class="smoking-question">
                    <div class="radio-group">
                        <label class="smoking-label">Do you smoke?</label>
                        <div class="radio-options">
                            <label><input type="radio" name="smoking" value="yes"> Yes</label>
                            <label><input type="radio" name="smoking" value="no"> No</label>
                        </div>
                    </div>
                    <div class="smoking-details">
                        <label class="smoking-detail-label">
                            Age when started: <input type="number" name="smoking_age" class="smoking-info">
                        </label>
                        <label class="smoking-detail-label">
                            Sticks per day: <input type="number" name="sticks_per_day" class="smoking-info">
                        </label>
                    </div>
                </div>
            </fieldset>

            <!-- Menstruation Section -->
            <fieldset>
                <legend>Menstruation</legend>
                <label><i class="small-text">(For female students):</i></label>
                <table>
                    <tr>
                        <td colspan="2"><strong>Age when period started: </strong></td>
                        <td><input type="text" name="menarche" id="menarche"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><strong>Last menstrual period: </strong></td>
                        <td><input type="date" name="dateperiod" id="dateperiod"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><strong>OB score: (if with previous pregnancy) </strong></td>
                        <td><input type="text" name="ob_score" id="ob_score"></td>
                    </tr>
                </table>
            </fieldset>

            <!-- Special Needs Section -->
            <fieldset>
                <legend>Special Needs Assessment</legend>
                <table>
                    <tr>
                        <td colspan="2"><strong>Do you currently have special needs that can affect your academic performance or social adjustment in the school?</strong></td>
                        <td>
                            <div class="radio-group">
                                <label><input type="radio" name="special_needs" value="yes" required> Yes</label>
                                <label><input type="radio" name="special_needs" value="no" required> No</label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3"><strong>If Yes, what are these? (Please specify)</strong></td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <div class="checkbox-group">
                                <label>
                                    <input type="checkbox" name="physical_limitations"> Physical limitations (e.g., Cerebral palsy, paraplegia, problems with ambulation, heart problems, others)
                                    <input type="text" name="physical_limitations_specify" placeholder="Please specify..." class="specify-input">
                                </label>
                                <label>
                                    <input type="checkbox" name="emotional_disorder"> Emotional or behavioral disorder (e.g., anxiety, depression)
                                    <input type="text" name="emotional_disorder_specify" placeholder="Please specify..." class="specify-input">
                                </label>
                                <label>
                                    <input type="checkbox" name="natural_conditions"> Natural conditions (e.g., obsession-compulsion, personality disorder, Asperger’s syndrome, others)
                                    <input type="text" name="natural_conditions_specify" placeholder="Please specify..." class="specify-input">
                                </label>
                                <label>
                                    <input type="checkbox" name="attention_conditions"> Conditions related to attention or concentration (e.g., ADHD, others)
                                    <input type="text" name="attention_conditions_specify" placeholder="Please specify..." class="specify-input">
                                </label>
                                <label>
                                    <input type="checkbox" name="medical_conditions"> Ongoing long-standing medical conditions (e.g., seizures or epilepsy, diabetes, others)
                                    <input type="text" name="medical_conditions_specify" placeholder="Please specify..." class="specify-input">
                                </label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><strong>Are you receiving treatment for the above condition?</strong></td>
                        <td>
                            <div class="radio-group">
                                <label><input type="radio" name="receiving_condition" value="yes" required> Yes</label>
                                <label><input type="radio" name="receiving_condition" value="no" required> No</label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><strong>If yes, may we ask for your permission to contact the health professional so we can coordinate with him/her the health care and services for you if necessary?</strong></td>
                        <td>
                            <div class="radio-group">
                                <label><input type="radio" name="contact_permission" value="yes" required> Yes</label>
                                <label><input type="radio" name="contact_permission" value="no" required> No</label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Name of Health Professional:</strong></td>
                        <td colspan="2"><input type="text" name="health_professional_name" class="small-input"></td>
                    </tr>
                    <tr>
                        <td><strong>Office Address:</strong></td>
                        <td colspan="2"><input type="text" name="health_professional_address" class="small-input"></td>
                    </tr>
                    <tr>
                        <td><strong>Office Number:</strong></td>
                        <td colspan="2"><input type="text" name="health_professional_office_number" class="small-input"></td>
                    </tr>
                    <tr>
                        <td><strong>Mobile Number:</strong></td>
                        <td colspan="2"><input type="text" name="health_professional_mobile_number" class="small-input"></td>
                    </tr>
                </table>
            </fieldset>

            <!-- Guardian Section -->
            <fieldset>
                <legend>Parent/Guardian</legend>
                <div class="form-group">
                    <label>Name: <input type="text" name="guardian_name" required></label>
                    <label>Date: <input type="date" name="guardian_date" required></label>
                </div>
            </fieldset>

            <button type="submit" class="styled-btn">Submit</button>
        </form>
    </div>
    <script>
        // JavaScript for Dynamic Vaccine Entries
        document.getElementById('add-vaccine').addEventListener('click', function () {
            const vaccineEntries = document.getElementById('vaccine-entries');

            const entryDiv = document.createElement('div');
            entryDiv.className = 'vaccine-entry';

            entryDiv.innerHTML = `
                <div style="align-items: center;" class="add_vaccine">
                    <input type="text" style="width: 180px;" name="vaccine_name[]" placeholder="Vaccine Name" required>
                    <input type="text" style="width: 180px;" name="dose[]" placeholder="Dose/Booster" required>
                    <input type="date" style="width: 180px;" name="date_given[]" placeholder="Date Given" required>
                    <button type="button" style="width: 165px;" class="remove-vaccine">Remove</button>
                </div>
            `;

            vaccineEntries.appendChild(entryDiv);

            // Add event listener to the remove button
            entryDiv.querySelector('.remove-vaccine').addEventListener('click', function () {
                vaccineEntries.removeChild(entryDiv);
            });
        });

        // JavaScript to make menstruation section required for female students
        document.querySelector('select[name="sex"]').addEventListener('change', function () {
            const lastMenstrualPeriodField = document.getElementById('dateperiod');
            if (this.value === 'Female') {
                lastMenstrualPeriodField.setAttribute('required', 'required');
            } else {
                lastMenstrualPeriodField.removeAttribute('required');
            }
        });

        // JavaScript to require details if checkbox is checked
        document.querySelectorAll('.checkbox-group input[type="checkbox"]').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                const detailsInput = this.nextElementSibling;
                if (this.checked) {
                    detailsInput.setAttribute('required', 'required');
                } else {
                    detailsInput.removeAttribute('required');
                }
            });
        });

        // Plain JavaScript for form submission
        document.getElementById('student-form').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            // Make sure section_id is included in form data
            formData.set('section_id', document.querySelector('input[name="section_id"]').value);

            // Convert checkbox values to 1 or 0
            document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                formData.set(checkbox.name, checkbox.checked ? 1 : 0);
            });

            // Show loading message
            alert('Submitting form...');

            fetch('submit_record.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Success! Your health record has been submitted.');
                    window.location.href = 'landing.php';
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('An error occurred: ' + error.message);
            });
        });
    </script>  
</body>
</html>