<?php
include 'admin_navbar.php';
include 'sqlconnection.php';

// Check if LRN is provided in the query string
if (!isset($_GET['lrn'])) {
    die("LRN is required.");
}

$lrn = $_GET['lrn'];

// Validate LRN (ensure it is not empty and is a valid number)
if (empty($lrn) || !is_numeric($lrn)) {
    die("Invalid LRN.");
}

// Fetch student information
$studentQuery = "SELECT si.*, s.section_name as section_name 
                FROM student_info si 
                LEFT JOIN section s ON si.section_id = s.section_id 
                WHERE si.lrn = ?";
$stmt = $conn->prepare($studentQuery);
$stmt->bind_param("s", $lrn);
$stmt->execute();
$studentResult = $stmt->get_result();
$student = $studentResult->fetch_assoc();

if (!$student) {
    die("Student not found.");
}

// Fetch immunization records
$immunizationQuery = "SELECT * FROM immunization_record WHERE student_id = ?";
$stmt = $conn->prepare($immunizationQuery);
$stmt->bind_param("i", $student['student_id']);
$stmt->execute();
$immunizationResult = $stmt->get_result();
$immunizations = $immunizationResult->fetch_all(MYSQLI_ASSOC);

// Fetch medical history
$medicalHistoryQuery = "SELECT * FROM medical_history WHERE student_id = ?";
$stmt = $conn->prepare($medicalHistoryQuery);
$stmt->bind_param("i", $student['student_id']);
$stmt->execute();
$medicalHistoryResult = $stmt->get_result();
$medicalHistory = $medicalHistoryResult->fetch_assoc();

// If no medical history exists, initialize an empty array
if (!$medicalHistory) {
    $medicalHistory = [];
}

// Fetch menstruation history (if applicable)
$menstruationQuery = "SELECT * FROM menstruation_history WHERE student_id = ?";
$stmt = $conn->prepare($menstruationQuery);
$stmt->bind_param("i", $student['student_id']);
$stmt->execute();
$menstruationResult = $stmt->get_result();
$menstruationHistory = $menstruationResult->fetch_assoc();

// Fetch smoking history
$smokingQuery = "SELECT * FROM smoking_history WHERE student_id = ?";
$stmt = $conn->prepare($smokingQuery);
$stmt->bind_param("i", $student['student_id']);
$stmt->execute();
$smokingResult = $stmt->get_result();
$smokingHistory = $smokingResult->fetch_assoc();

// Fetch special needs
$specialNeedsQuery = "SELECT * FROM special_needs WHERE student_id = ?";
$stmt = $conn->prepare($specialNeedsQuery);
$stmt->bind_param("i", $student['student_id']);
$stmt->execute();
$specialNeedsResult = $stmt->get_result();
$specialNeeds = $specialNeedsResult->fetch_assoc();

// Fetch guardian information
$guardianQuery = "SELECT * FROM guardians WHERE student_id = ?";
$stmt = $conn->prepare($guardianQuery);
$stmt->bind_param("i", $student['student_id']);
$stmt->execute();
$guardianResult = $stmt->get_result();
$guardian = $guardianResult->fetch_assoc();

// Fetch physical examination data
$physicalExamQuery = "SELECT * FROM physical_examination WHERE student_id = ?";
$stmt = $conn->prepare($physicalExamQuery);
$stmt->bind_param("i", $student['student_id']);
$stmt->execute();
$physicalExamResult = $stmt->get_result();
$physicalExam = $physicalExamResult->fetch_assoc();

// If no physical examination data exists, initialize an empty array
if (!$physicalExam) {
    $physicalExam = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student Health Record</title>
    <link rel="stylesheet" href="css/view_records.css"> <!-- Use the same CSS as the form -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container">
    <form id="student-form" method="POST" action="physical_exam.php">
            <!-- Student Information -->
            <fieldset>
                <legend>Student Information</legend>
                <div class="form-group">
                    <label>Name: <input type="text" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" readonly></label>
                    <label>Section: <input type="text" name="section" value="<?php echo htmlspecialchars($student['section_name'] ?? 'Not Assigned'); ?>" readonly></label>
                </div>
                <div class="form-group">
                    <label>Sex: 
                        <select name="sex" readonly>
                            <option value="<?php echo htmlspecialchars($student['sex']); ?>" selected><?php echo htmlspecialchars($student['sex']); ?></option>
                        </select>
                    </label>
                    <label>LRN: <input type="number" name="lrn" value="<?php echo htmlspecialchars($student['lrn']); ?>" readonly></label>
                </div>
                <div class="form-group">
                    <label>Date of Birth: <input type="date" name="date_of_birth" value="<?php echo htmlspecialchars($student['date_of_birth']); ?>" readonly></label>
                    <label>Nationality: <input type="text" name="nationality" value="<?php echo htmlspecialchars($student['nationality']); ?>" readonly></label>
                </div>
                <div class="form-group">
                    <label>Address: <input type="text" name="address" value="<?php echo htmlspecialchars($student['address']); ?>" readonly></label>
                </div>
                <div class="form-group">
                    <label>Father's Name: <input type="text" name="father_name" value="<?php echo htmlspecialchars($student['father_name']); ?>" readonly></label>
                    <label>Phone Number: <input type="tel" name="father_phone" value="<?php echo htmlspecialchars($student['father_phone']); ?>" readonly></label>
                </div>
                <div class="form-group">
                    <label>Mother's Name: <input type="text" name="mother_name" value="<?php echo htmlspecialchars($student['mother_name']); ?>" readonly></label>
                    <label>Phone Number: <input type="tel" name="mother_phone" value="<?php echo htmlspecialchars($student['mother_phone']); ?>" readonly></label>
                </div>
                <div class="form-group">
                    <label>Emergency Contact Person: <input type="text" name="emergency_contact_name" value="<?php echo htmlspecialchars($student['emergency_contact_name']); ?>" readonly></label>
                    <label>Relationship: <input type="text" name="emergency_contact_relationship" value="<?php echo htmlspecialchars($student['emergency_contact_relationship']); ?>" readonly></label>
                    <label>Phone Number: <input type="tel" name="emergency_contact_phone" value="<?php echo htmlspecialchars($student['emergency_contact_phone']); ?>" readonly></label>
                </div>
            </fieldset>

            <!-- Immunization Record Section -->
            <fieldset>
                <legend>Immunization Record</legend>
                <div id="vaccine-entries">
                    <?php if (!empty($immunizations)): ?>
                        <?php foreach ($immunizations as $immunization): ?>
                            <div class="vaccine-entry">
                                <input type="text" name="vaccine_name[]" value="<?php echo htmlspecialchars($immunization['vaccine_name']); ?>" readonly>
                                <input type="text" name="dose[]" value="<?php echo htmlspecialchars($immunization['dose']); ?>" readonly>
                                <input type="date" name="date_given[]" value="<?php echo htmlspecialchars($immunization['date_given']); ?>" readonly>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No immunization records found.</p>
                    <?php endif; ?>
                </div>
            </fieldset>

            <!-- Past Medical History Section -->
            <fieldset>
                <legend>Past Medical History</legend>
                <?php
                // Check if all medical history fields are empty
                $allFieldsEmpty = true;
                $medicalFields = [
                    'allergies', 'asthma', 'chicken_pox', 'diabetes', 'epilepsy', 
                    'heart_disorder', 'kidney_disease', 'tuberculosis', 'mumps', 
                    'other_medical_history', 'date_of_confinement', 'confinement_details'
                ];
                foreach ($medicalFields as $field) {
                    if (!empty($medicalHistory[$field]) || !empty($medicalHistory[$field . '_details'])) {
                        $allFieldsEmpty = false;
                        break;
                    }
                }

                if ($allFieldsEmpty): ?>
                    <p>No past medical history found.</p>
                <?php else: ?>
                    <div class="checkbox-group">
                        <?php foreach ($medicalFields as $field): ?>
                            <?php if (!empty($medicalHistory[$field]) || !empty($medicalHistory[$field . '_details'])): ?>
                                <?php if ($field === 'date_of_confinement'): ?>
                                    <label>
                                        Date of Confinement: 
                                        <input type="date" name="date_of_confinement" 
                                            value="<?php echo isset($medicalHistory['date_of_confinement']) ? htmlspecialchars($medicalHistory['date_of_confinement']) : ''; ?>" 
                                            readonly>
                                    </label>
                                <?php elseif ($field === 'confinement_details'): ?>
                                    <label>
                                        Confinement Details: 
                                        <input type="text" name="confinement_details" 
                                            value="<?php echo isset($medicalHistory['confinement_details']) ? htmlspecialchars($medicalHistory['confinement_details']) : ''; ?>" 
                                            readonly>
                                    </label>
                                <?php else: ?>
                                    <label>
                                        <input type="checkbox" name="<?php echo $field; ?>" 
                                            <?php echo isset($medicalHistory[$field]) && $medicalHistory[$field] ? 'checked' : ''; ?> 
                                            disabled>
                                        <?php echo ucfirst(str_replace('_', ' ', $field)); ?>
                                        <input type="text" name="<?php echo $field; ?>_details" 
                                            value="<?php echo isset($medicalHistory[$field . '_details']) ? htmlspecialchars($medicalHistory[$field . '_details']) : ''; ?>" 
                                            readonly>
                                    </label>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </fieldset>

            <!-- Smoking History Section -->
            <fieldset>
                <legend>Smoking History</legend>
                <?php if (!empty($smokingHistory)): ?>
                    <div class="form-group">
                        <div class="radio-group">
                            <label class="smoking-label">Do you smoke?</label>
                            <label>
                                <input type="radio" name="smoking" value="Yes" 
                                    <?php echo (isset($smokingHistory['smokes']) && $smokingHistory['smokes'] == 'Yes') ? 'checked' : ''; ?> 
                                    disabled> Yes
                            </label>
                            <label>
                                <input type="radio" name="smoking" value="No" 
                                    <?php echo (isset($smokingHistory['smokes']) && $smokingHistory['smokes'] == 'No') ? 'checked' : ''; ?> 
                                    disabled> No
                            </label>
                        </div>
                        <?php if (isset($smokingHistory['smokes']) && $smokingHistory['smokes'] == 'Yes'): ?>
                            <div class="smoking-details">
                                <label class="smoking-detail-label">
                                Age when started: <input type="number" name="smoking_age" 
                                        value="<?php echo isset($smokingHistory['age_of_onset']) ? htmlspecialchars($smokingHistory['age_of_onset']) : ''; ?>" 
                                        readonly>
                                </label>
                                <label class="smoking-detail-label">
                                    Sticks per day: <input type="number" name="sticks_per_day" 
                                        value="<?php echo isset($smokingHistory['sticks_per_day']) ? htmlspecialchars($smokingHistory['sticks_per_day']) : ''; ?>" 
                                        readonly>
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p>No smoking history recorded.</p>
                <?php endif; ?>
            </fieldset>

            <!-- Menstruation Section (for female students) -->
            <?php if ($student['sex'] === 'Female' && !empty($menstruationHistory)): ?>
                <fieldset>
                    <legend>Menstruation</legend>
                    <table>
                        <tr>
                            <td colspan="2"><strong>Age when period started: </strong></td>
                            <td><input type="text" name="menarche" value="<?php echo isset($menstruationHistory['menarche']) ? htmlspecialchars($menstruationHistory['menarche']) : ''; ?>" readonly></td>
                        </tr>
                        <tr>
                            <td colspan="2"><strong>Last Menstrual Period: </strong></td>
                            <td><input type="date" name="dateperiod" value="<?php echo isset($menstruationHistory['last_menstrual_period']) ? htmlspecialchars($menstruationHistory['last_menstrual_period']) : ''; ?>" readonly></td>
                        </tr>
                        <tr>
                            <td colspan="2"><strong>OB score: (if with previous pregnancy) </strong></td>
                            <td><input type="text" name="ob_score" value="<?php echo isset($menstruationHistory['ob_score']) ? htmlspecialchars($menstruationHistory['ob_score']) : ''; ?>" readonly></td>
                        </tr>
                    </table>
                </fieldset>
            <?php endif; ?>

            <!-- Special Needs Section -->
            <fieldset>
                <legend>Special Needs Assessment</legend>
                <div class="form-group">
                    <label>Has Special Needs: 
                        <input type="text" name="special_needs" value="<?php echo isset($specialNeeds['has_special_needs']) ? htmlspecialchars($specialNeeds['has_special_needs']) : 'No'; ?>" readonly>
                    </label>
                </div>

                <?php if (isset($specialNeeds['has_special_needs']) && $specialNeeds['has_special_needs'] === 'Yes'): ?>
                    <div class="checkbox-group">
                        <!-- Physical Limitations -->
                        <?php if (!empty($specialNeeds['physical_limitations']) || !empty($specialNeeds['physical_limitations_specify'])): ?>
                            <label>
                                <input type="checkbox" name="physical_limitations" <?php echo !empty($specialNeeds['physical_limitations']) ? 'checked' : ''; ?> disabled>
                                Physical limitations (e.g., Cerebral palsy, paraplegia, problems with ambulation, heart problems, others)
                                <input type="text" name="physical_limitations_specify" 
                                    value="<?php echo isset($specialNeeds['physical_limitations_specify']) ? htmlspecialchars($specialNeeds['physical_limitations_specify']) : ''; ?>" 
                                    readonly>
                            </label>
                        <?php endif; ?>

                        <!-- Emotional Disorder -->
                        <?php if (!empty($specialNeeds['emotional_disorder']) || !empty($specialNeeds['emotional_disorder_specify'])): ?>
                            <label>
                                <input type="checkbox" name="emotional_disorder" <?php echo !empty($specialNeeds['emotional_disorder']) ? 'checked' : ''; ?> disabled>
                                Emotional or behavioral disorder (e.g., anxiety, depression)
                                <input type="text" name="emotional_disorder_specify" 
                                    value="<?php echo isset($specialNeeds['emotional_disorder_specify']) ? htmlspecialchars($specialNeeds['emotional_disorder_specify']) : ''; ?>" 
                                    readonly>
                            </label>
                        <?php endif; ?>

                        <!-- Natural Conditions -->
                        <?php if (!empty($specialNeeds['natural_conditions']) || !empty($specialNeeds['natural_conditions_specify'])): ?>
                            <label>
                                <input type="checkbox" name="natural_conditions" <?php echo !empty($specialNeeds['natural_conditions']) ? 'checked' : ''; ?> disabled>
                                Natural conditions (e.g., obsession-compulsion, personality disorder, Asperger's syndrome, others)
                                <input type="text" name="natural_conditions_specify" 
                                    value="<?php echo isset($specialNeeds['natural_conditions_specify']) ? htmlspecialchars($specialNeeds['natural_conditions_specify']) : ''; ?>" 
                                    readonly>
                            </label>
                        <?php endif; ?>

                        <!-- Attention Conditions -->
                        <?php if (!empty($specialNeeds['attention_conditions']) || !empty($specialNeeds['attention_conditions_specify'])): ?>
                            <label>
                                <input type="checkbox" name="attention_conditions" <?php echo !empty($specialNeeds['attention_conditions']) ? 'checked' : ''; ?> disabled>
                                Conditions related to attention or concentration (e.g., ADHD, others)
                                <input type="text" name="attention_conditions_specify" 
                                    value="<?php echo isset($specialNeeds['attention_conditions_specify']) ? htmlspecialchars($specialNeeds['attention_conditions_specify']) : ''; ?>" 
                                    readonly>
                            </label>
                        <?php endif; ?>

                        <!-- Medical Conditions -->
                        <?php if (!empty($specialNeeds['medical_conditions']) || !empty($specialNeeds['medical_conditions_specify'])): ?>
                            <label>
                                <input type="checkbox" name="medical_conditions" <?php echo !empty($specialNeeds['medical_conditions']) ? 'checked' : ''; ?> disabled>
                                Ongoing long-standing medical conditions (e.g., seizures or epilepsy, diabetes, others)
                                <input type="text" name="medical_conditions_specify" 
                                    value="<?php echo isset($specialNeeds['medical_conditions_specify']) ? htmlspecialchars($specialNeeds['medical_conditions_specify']) : ''; ?>" 
                                    readonly>
                            </label>
                        <?php endif; ?>
                    </div>

                    <!-- Receiving Treatment -->
                    <div class="form-group">
                        <label>Receiving Treatment: 
                            <input type="text" name="receiving_treatment" 
                                value="<?php echo isset($specialNeeds['receiving_treatment']) ? htmlspecialchars($specialNeeds['receiving_treatment']) : 'No'; ?>" 
                                readonly>
                        </label>
                    </div>

                    <!-- Permission to Contact Health Professional -->
                    <?php if (isset($specialNeeds['receiving_treatment']) && $specialNeeds['receiving_treatment'] === 'Yes'): ?>
                        <div class="form-group">
                            <label>Permission to Contact Health Professional: 
                                <input type="text" name="contact_permission" 
                                    value="<?php echo isset($specialNeeds['contact_permission']) ? htmlspecialchars($specialNeeds['contact_permission']) : 'No'; ?>" 
                                    readonly>
                            </label>
                        </div>

                        <!-- Health Professional Details -->
                        <div class="form-group">
                            <label>Name of Health Professional: 
                                <input type="text" name="health_professional_name" 
                                    value="<?php echo isset($specialNeeds['health_professional_name']) ? htmlspecialchars($specialNeeds['health_professional_name']) : ''; ?>" 
                                    readonly>
                            </label>
                        </div>
                        <div class="form-group">
                            <label>Office Address: 
                                <input type="text" name="health_professional_address" 
                                    value="<?php echo isset($specialNeeds['health_professional_address']) ? htmlspecialchars($specialNeeds['health_professional_address']) : ''; ?>" 
                                    readonly>
                            </label>
                        </div>
                        <div class="form-group">
                            <label>Office Number: 
                                <input type="text" name="health_professional_office_number" 
                                    value="<?php echo isset($specialNeeds['health_professional_office_number']) ? htmlspecialchars($specialNeeds['health_professional_office_number']) : ''; ?>" 
                                    readonly>
                            </label>
                        </div>
                        <div class="form-group">
                            <label>Mobile Number: 
                                <input type="text" name="health_professional_mobile_number" 
                                    value="<?php echo isset($specialNeeds['health_professional_mobile_number']) ? htmlspecialchars($specialNeeds['health_professional_mobile_number']) : ''; ?>" 
                                    readonly>
                            </label>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </fieldset>

            <!-- Guardian Section -->
            <fieldset>
                <legend>Parent/Guardian</legend>
                <?php if (!empty($guardian)): ?>
                    <div class="form-group">
                        <label>Name: 
                            <input type="text" name="guardian_name" 
                                value="<?php echo isset($guardian['guardian_name']) ? htmlspecialchars($guardian['guardian_name']) : ''; ?>" 
                                readonly>
                        </label>
                        <label>Date: 
                            <input type="date" name="guardian_date" 
                                value="<?php echo isset($guardian['guardian_date']) ? htmlspecialchars($guardian['guardian_date']) : ''; ?>" 
                                readonly>
                        </label>
                    </div>
                <?php else: ?>
                    <p>No guardian information recorded.</p>
                <?php endif; ?>
            </fieldset>
           <!-- Physical Examination Section -->
<fieldset>
    <legend>Physical Examination</legend>
    <label><i class="small-text">(To be filled up by school personnel only.)</i></label>
    <div class="form-group">
        <label>Height (cm): 
            <input type="text" name="height" class="editable" 
                value="<?php echo isset($physicalExam['height']) ? htmlspecialchars($physicalExam['height']) : ''; ?>">
        </label>
        <label>Weight (kg): 
            <input type="text" name="weight" class="editable" 
                value="<?php echo isset($physicalExam['weight']) ? htmlspecialchars($physicalExam['weight']) : ''; ?>">
        </label>
        <label>BMI: 
            <input type="text" name="bmi" class="editable" 
                value="<?php echo isset($physicalExam['bmi']) ? htmlspecialchars($physicalExam['bmi']) : ''; ?>">
        </label>
    </div>
    <div class="form-group">
        <label>Blood Pressure: 
            <input type="text" name="blood_pressure" class="editable" 
                value="<?php echo isset($physicalExam['blood_pressure']) ? htmlspecialchars($physicalExam['blood_pressure']) : ''; ?>">
        </label>
        <label>Pulse Rate: 
            <input type="text" name="pulse_rate" class="editable" 
                value="<?php echo isset($physicalExam['pulse_rate']) ? htmlspecialchars($physicalExam['pulse_rate']) : ''; ?>">
        </label>
    </div>
    <div class="form-group">
        <label>Respiratory Rate: 
            <input type="text" name="respiratory_rate" class="editable" 
                value="<?php echo isset($physicalExam['respiratory_rate']) ? htmlspecialchars($physicalExam['respiratory_rate']) : ''; ?>">
        </label>
        <label>Remarks: 
            <input type="text" name="remarks" class="editable" 
                value="<?php echo (isset($physicalExam['remarks']) && $physicalExam['remarks'] !== '0') ? htmlspecialchars($physicalExam['remarks']) : ''; ?>">
        </label>
    </div>
    <div class="form-group">
        <label>Examined By: 
            <input type="text" name="examined_by" class="editable" 
                value="<?php echo isset($physicalExam['examined_by']) ? htmlspecialchars($physicalExam['examined_by']) : ''; ?>">
        </label>
        <label>Date: 
            <input type="date" name="exam_date" class="editable" 
                value="<?php echo isset($physicalExam['exam_date']) ? htmlspecialchars($physicalExam['exam_date']) : ''; ?>">
        </label>
    </div>
    <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
    <input type="hidden" name="lrn" value="<?php echo $lrn; ?>"> <!-- Added hidden LRN field -->
    <button type="submit" name="physical_exam">Save</button>
</fieldset>
        </form>
        <a href="generate_pdf.php?lrn=<?php echo $lrn; ?>" class="pdf-button">Generate PDF</a>
    </div>
</body>
</html>