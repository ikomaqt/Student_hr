<?php
require_once 'vendor/autoload.php'; // Ensure the correct path to autoload.php
include 'sqlconnection.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Check if LRN is provided in the query string
if (!isset($_GET['lrn'])) {
    die("LRN is required.");
}

$lrn = trim($_GET['lrn']);

// Validate LRN (ensure it is not empty and is a valid number)
if (empty($lrn) || !ctype_digit($lrn)) {
    die("Invalid LRN.");
}

// Fetch student information
$studentQuery = "SELECT * FROM student_info WHERE lrn = ?";
$stmt = $conn->prepare($studentQuery);
if (!$stmt) {
    die("Database error: " . $conn->error);
}
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
if (!$stmt) {
    die("Database error: " . $conn->error);
}
$stmt->bind_param("i", $student['student_id']);
$stmt->execute();
$immunizationResult = $stmt->get_result();
$immunizations = $immunizationResult->fetch_all(MYSQLI_ASSOC);

// Fetch medical history
$medicalHistoryQuery = "SELECT * FROM medical_history WHERE student_id = ?";
$stmt = $conn->prepare($medicalHistoryQuery);
if (!$stmt) {
    die("Database error: " . $conn->error);
}
$stmt->bind_param("i", $student['student_id']);
$stmt->execute();
$medicalHistoryResult = $stmt->get_result();
$medicalHistory = $medicalHistoryResult->fetch_assoc();

// If no medical history exists, initialize an empty array
if (!$medicalHistory) {
    $medicalHistory = [];
}

// Fetch menstruation history (if applicable)
if ($student['sex'] === 'Female') {
    $menstruationQuery = "SELECT * FROM menstruation_history WHERE student_id = ?";
    $stmt = $conn->prepare($menstruationQuery);
    if (!$stmt) {
        die("Database error: " . $conn->error);
    }
    $stmt->bind_param("i", $student['student_id']);
    $stmt->execute();
    $menstruationResult = $stmt->get_result();
    $menstruationHistory = $menstruationResult->fetch_assoc();
} else {
    $menstruationHistory = null;
}

// Fetch smoking history
$smokingQuery = "SELECT * FROM smoking_history WHERE student_id = ?";
$stmt = $conn->prepare($smokingQuery);
if (!$stmt) {
    die("Database error: " . $conn->error);
}
$stmt->bind_param("i", $student['student_id']);
$stmt->execute();
$smokingResult = $stmt->get_result();
$smokingHistory = $smokingResult->fetch_assoc();

// Fetch special needs
$specialNeedsQuery = "SELECT * FROM special_needs WHERE student_id = ?";
$stmt = $conn->prepare($specialNeedsQuery);
if (!$stmt) {
    die("Database error: " . $conn->error);
}
$stmt->bind_param("i", $student['student_id']);
$stmt->execute();
$specialNeedsResult = $stmt->get_result();
$specialNeeds = $specialNeedsResult->fetch_assoc();

// Fetch guardian information
$guardianQuery = "SELECT * FROM guardians WHERE student_id = ?";
$stmt = $conn->prepare($guardianQuery);
if (!$stmt) {
    die("Database error: " . $conn->error);
}
$stmt->bind_param("i", $student['student_id']);
$stmt->execute();
$guardianResult = $stmt->get_result();
$guardian = $guardianResult->fetch_assoc();

// Fetch physical examination records
$physicalExamQuery = "SELECT * FROM physical_examination WHERE student_id = ?";
$stmt = $conn->prepare($physicalExamQuery);
if (!$stmt) {
    die("Database error: " . $conn->error);
}
$stmt->bind_param("i", $student['student_id']);
$stmt->execute();
$physicalExamResult = $stmt->get_result();
$physicalExam = $physicalExamResult->fetch_assoc();

// Fetch section information
$sectionQuery = "SELECT s.section_name FROM section s 
                INNER JOIN student_info si ON s.section_id = si.section_id 
                WHERE si.student_id = ?";
$stmt = $conn->prepare($sectionQuery);
if (!$stmt) {
    die("Database error: " . $conn->error);
}
$stmt->bind_param("i", $student['student_id']);
$stmt->execute();
$sectionResult = $stmt->get_result();
$section = $sectionResult->fetch_assoc();

// Generate HTML for PDF
$html = "
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        h1 { color: #1e3a8a; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .section-title { font-size: 18px; font-weight: bold; margin-top: 20px; text-align: center; }
        .section { margin-bottom: 20px; }
        .header { width: 100%; margin-bottom: 20px; }
        .editable { cursor: pointer; }
    </style>
</head>
<body>
    <h1>Student Health Report</h1>

    <!-- Student Information -->
    <div class='section'>
        <div class='section-title'>Student Information</div>
        <table>
            <tr>
                <th>Name</th>
                <td>" . htmlspecialchars($student['name'] ?? 'N/A') . "</td>
            </tr>
            <tr>
                <th>Sex</th>
                <td>" . htmlspecialchars($student['sex'] ?? 'N/A') . "</td>
            </tr>
            <tr>
                <th>Section</th>
                <td>" . htmlspecialchars($section['section_name'] ?? 'N/A') . "</td>
            </tr>
            <tr>
                <th>LRN</th>
                <td>" . htmlspecialchars($student['lrn'] ?? 'N/A') . "</td>
            </tr>
            <tr>
                <th>Date of Birth</th>
                <td>" . htmlspecialchars($student['date_of_birth'] ?? 'N/A') . "</td>
            </tr>
            <tr>
                <th>Nationality</th>
                <td>" . htmlspecialchars($student['nationality'] ?? 'N/A') . "</td>
            </tr>
            <tr>
                <th>Address</th>
                <td>" . htmlspecialchars($student['address'] ?? 'N/A') . "</td>
            </tr>
            <tr>
                <th>Father’s Name</th>
                <td>" . htmlspecialchars($student['father_name'] ?? 'N/A') . "</td>
            </tr>
            <tr>
                <th>Father’s Phone</th>
                <td>" . htmlspecialchars($student['father_phone'] ?? 'N/A') . "</td>
            </tr>
            <tr>
                <th>Mother’s Name</th>
                <td>" . htmlspecialchars($student['mother_name'] ?? 'N/A') . "</td>
            </tr>
            <tr>
                <th>Mother’s Phone</th>
                <td>" . htmlspecialchars($student['mother_phone'] ?? 'N/A') . "</td>
            </tr>
            <tr>
                <th>Emergency Contact</th>
                <td>" . htmlspecialchars($student['emergency_contact_name'] ?? 'N/A') . "</td>
            </tr>
            <tr>
                <th>Relationship</th>
                <td>" . htmlspecialchars($student['emergency_contact_relationship'] ?? 'N/A') . "</td>
            </tr>
            <tr>
                <th>Emergency Phone</th>
                <td>" . htmlspecialchars($student['emergency_contact_phone'] ?? 'N/A') . "</td>
            </tr>
        </table>
    </div>

    <!-- Immunization Records -->
    <div class='section'>
        <div class='section-title'>Immunization Records</div>
        <table>
            <tr>
                <th>Vaccine Name</th>
                <th>Dose</th>
                <th>Date Given</th>
            </tr>";
            if (!empty($immunizations)) {
                foreach ($immunizations as $immunization) {
                    $html .= "
                    <tr>
                        <td>" . htmlspecialchars($immunization['vaccine_name'] ?? 'N/A') . "</td>
                        <td>" . htmlspecialchars($immunization['dose'] ?? 'N/A') . "</td>
                        <td>" . htmlspecialchars($immunization['date_given'] ?? 'N/A') . "</td>
                    </tr>";
                }
            } else {
                $html .= "
                <tr>
                    <td colspan='3'>No immunization records found.</td>
                </tr>";
            }
            $html .= "
        </table>
    </div>

    <!-- Past Medical History -->
    <div class='section'>
        <div class='section-title'>Past Medical History</div>
        <table>
            <tr>
                <th>Condition</th>
                <th>Status</th>
                <th>Details</th>
            </tr>";
            $medicalFields = [
                'allergies', 'asthma', 'chicken_pox', 'diabetes', 'epilepsy', 
                'heart_disorder', 'kidney_disease', 'tuberculosis', 'mumps', 'other_medical_history'
            ];
            foreach ($medicalFields as $field) {
                if (isset($medicalHistory[$field]) || isset($medicalHistory[$field . '_details'])) {
                    $html .= "
                    <tr>
                        <td>" . ucfirst(str_replace('_', ' ', $field)) . "</td>
                        <td>" . (isset($medicalHistory[$field]) && $medicalHistory[$field] ? 'Yes' : 'No') . "</td>
                        <td>" . htmlspecialchars($medicalHistory[$field . '_details'] ?? 'N/A') . "</td>
                    </tr>";
                }
            }
            // Add date_of_confinement and confinement_details
            if (!empty($medicalHistory['date_of_confinement']) || !empty($medicalHistory['confinement_details'])) {
                $html .= "
                <tr>
                    <td>Date of Confinement</td>
                    <td colspan='2'>" . htmlspecialchars($medicalHistory['date_of_confinement'] ?? 'N/A') . "</td>
                </tr>
                <tr>
                    <td>Confinement Details</td>
                    <td colspan='2'>" . htmlspecialchars($medicalHistory['confinement_details'] ?? 'N/A') . "</td>
                </tr>";
            }
            $html .= "
        </table>
    </div>

    <!-- Smoking History -->
    <div class='section'>
        <div class='section-title'>Smoking History</div>
        <table>
            <tr>
                <th>Do you smoke?</th>
                <td>" . htmlspecialchars($smokingHistory['smokes'] ?? 'N/A') . "</td>
            </tr>
            <tr>
                <th>Age when started:</th>
                <td>" . htmlspecialchars($smokingHistory['age_of_onset'] ?? 'N/A') . "</td>
            </tr>
            <tr>
                <th>Sticks per day</th>
                <td>" . htmlspecialchars($smokingHistory['sticks_per_day'] ?? 'N/A') . "</td>
            </tr>
        </table>
    </div>";

    // Menstruation History (for female students)
    if ($student['sex'] === 'Female' && !empty($menstruationHistory)) {
        $html .= "
        <div class='section'>
            <div class='section-title'>Menstruation History</div>
            <table>
                <tr>
                    <th>Age when period started</th>
                    <td>" . htmlspecialchars($menstruationHistory['menarche'] ?? 'N/A') . "</td>
                </tr>
                <tr>
                    <th>Last menstrual period</th>
                    <td>" . htmlspecialchars($menstruationHistory['last_menstrual_period'] ?? 'N/A') . "</td>
                </tr>
                <tr>
                    <th>OB Score</th>
                    <td>" . htmlspecialchars($menstruationHistory['ob_score'] ?? 'N/A') . "</td>
                </tr>
            </table>
        </div>";
    }

    // Special Needs
    $html .= "
    <!-- Special Needs -->
    <div class='section'>
        <div class='section-title'>Special Needs Assessment</div>
        <table>
            <tr>
                <th>Has Special Needs</th>
                <td>" . htmlspecialchars($specialNeeds['has_special_needs'] ?? 'No') . "</td>
            </tr>";

            if (isset($specialNeeds['has_special_needs']) && $specialNeeds['has_special_needs'] === 'Yes') {
                $specialNeedsFields = [
                    'physical_limitations' => 'Physical Limitations',
                    'emotional_disorder' => 'Emotional Disorder',
                    'natural_conditions' => 'Natural Conditions',
                    'attention_conditions' => 'Attention Conditions',
                    'medical_conditions' => 'Medical Conditions'
                ];

                foreach ($specialNeedsFields as $field => $label) {
                    if (isset($specialNeeds[$field]) || isset($specialNeeds[$field . '_specify'])) {
                        $html .= "
                        <tr>
                            <th>{$label}</th>
                            <td>" . htmlspecialchars($specialNeeds[$field . '_specify'] ?? 'N/A') . "</td>
                        </tr>";
                    }
                }

                if (isset($specialNeeds['receiving_treatment']) && $specialNeeds['receiving_treatment'] === 'Yes') {
                    $html .= "
                    <tr>
                        <th>Receiving Treatment</th>
                        <td>" . htmlspecialchars($specialNeeds['receiving_treatment']) . "</td>
                    </tr>
                    <tr>
                        <th>Health Professional Name</th>
                        <td>" . htmlspecialchars($specialNeeds['health_professional_name'] ?? 'N/A') . "</td>
                    </tr>
                    <tr>
                        <th>Office Address</th>
                        <td>" . htmlspecialchars($specialNeeds['health_professional_address'] ?? 'N/A') . "</td>
                    </tr>
                    <tr>
                        <th>Office Number</th>
                        <td>" . htmlspecialchars($specialNeeds['health_professional_office_number'] ?? 'N/A') . "</td>
                    </tr>
                    <tr>
                        <th>Mobile Number</th>
                        <td>" . htmlspecialchars($specialNeeds['health_professional_mobile_number'] ?? 'N/A') . "</td>
                    </tr>";
                }
            }

            $html .= "
        </table>
    </div>

    <!-- Guardian Information -->
    <div class='section'>
        <div class='section-title'>Guardian Information</div>
        <table>
            <tr>
                <th>Guardian Name</th>
                <td>" . htmlspecialchars($guardian['guardian_name'] ?? 'N/A') . "</td>
            </tr>
            <tr>
                <th>Date</th>
                <td>" . htmlspecialchars($guardian['guardian_date'] ?? 'N/A') . "</td>
            </tr>
        </table>
    </div>
     <!-- Physical Examination Records -->
    <div class='section'>
        <div class='section-title'>Physical Examination Records (Filled up by school personnel)</div>
        <table>
            <tr>
                <th>Height</th>
                <td>" . htmlspecialchars($physicalExam['height'] ?? 'N/A') . "</td>
            </tr>
            <tr>
                <th>Weight</th>
                <td>" . htmlspecialchars($physicalExam['weight'] ?? 'N/A') . "</td>
            </tr>
            <tr>
                <th>BMI</th>
                <td>" . htmlspecialchars($physicalExam['bmi'] ?? 'N/A') . "</td>
            </tr>
            <tr>
                <th>Blood Pressure</th>
                <td>" . htmlspecialchars($physicalExam['blood_pressure'] ?? 'N/A') . "</td>
            </tr>
            <tr>
                <th>Pulse Rate</th>
                <td>" . htmlspecialchars($physicalExam['pulse_rate'] ?? 'N/A') . "</td>
            </tr>
            <tr>
                <th>Respiratory Rate</th>
                <td>" . htmlspecialchars($physicalExam['respiratory_rate'] ?? 'N/A') . "</td>
            </tr>
            <tr>
                <th>Remarks</th>
                <td>" . htmlspecialchars($physicalExam['remarks'] ?? 'N/A') . "</td>
            </tr>
            <tr>
                <th>Examined By</th>
                <td>" . htmlspecialchars($physicalExam['examined_by'] ?? 'N/A') . "</td>
            </tr>
            <tr>
                <th>Exam Date</th>
                <td>" . htmlspecialchars($physicalExam['exam_date'] ?? 'N/A') . "</td>
            </tr>
        </table>
    </div>

</body>
</html>";

// Generate PDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("student_health_report_{$student['name']}.pdf", ["Attachment" => false]);
?>