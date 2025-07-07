<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User not logged in'
    ]);
    exit();
}

include 'sqlconnection.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Start transaction
    $conn->begin_transaction();

    try {
        // Check for duplicate LRN
        $checkLrn = $conn->prepare("SELECT COUNT(*) as count FROM student_info WHERE lrn = ?");
        $checkLrn->bind_param("s", $_POST['lrn']);
        $checkLrn->execute();
        $result = $checkLrn->get_result();
        $row = $result->fetch_assoc();
        $checkLrn->close();

        if ($row['count'] > 0) {
            throw new Exception("A record with this LRN already exists.");
        }

        // Update has_record in users table after successful submission
        $updateUserStmt = $conn->prepare("UPDATE users SET has_record = 1 WHERE id = ?");
        $updateUserStmt->bind_param("i", $_SESSION['user_id']);
        $updateUserStmt->execute();
        $updateUserStmt->close();

        // Validate and sanitize input
        $name = !empty($_POST['name']) ? trim($_POST['name']) : '';
        $sex = !empty($_POST['sex']) ? $_POST['sex'] : '';
        $lrn = !empty($_POST['lrn']) ? $_POST['lrn'] : 0;
        $date_of_birth = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : '';
        $address = !empty($_POST['address']) ? trim($_POST['address']) : '';
        $section_id = !empty($_POST['section_id']) ? $_POST['section_id'] : '';

        // Check for required fields
        if (empty($name) || empty($sex) || empty($lrn) || empty($date_of_birth) || empty($address) || empty($section_id)) {
            throw new Exception("All required fields must be filled.");
        }

        //students table
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

        //immunizations table
        if (isset($_POST['vaccine_name'])) {
            $stmt = $conn->prepare("INSERT INTO immunization_record (student_id, vaccine_name, dose, date_given) VALUES (?, ?, ?, ?)");
            foreach ($_POST['vaccine_name'] as $key => $vaccine_name) {
                $stmt->bind_param("isss", $student_id, $vaccine_name, $_POST['dose'][$key], $_POST['date_given'][$key]);
                $stmt->execute();
            }
            $stmt->close();
        }

        //medical_history table
        $stmt = $conn->prepare("INSERT INTO medical_history (student_id, allergies, allergies_details, asthma, asthma_details, chicken_pox, chicken_pox_details, diabetes, diabetes_details, epilepsy, epilepsy_details, heart_disorder, heart_disorder_details, kidney_disease, kidney_disease_details, tuberculosis, tuberculosis_details, mumps, mumps_details, other_medical_history, other_medical_history_details, date_of_confinement, confinement_details) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssssssssssssssssssss", $student_id, $_POST['allergies'], $_POST['allergies_details'], $_POST['asthma'], $_POST['asthma_details'], $_POST['chicken_pox'], $_POST['chicken_pox_details'], $_POST['diabetes'], $_POST['diabetes_details'], $_POST['epilepsy'], $_POST['epilepsy_details'], $_POST['heart_disorder'], $_POST['heart_disorder_details'], $_POST['kidney_disease'], $_POST['kidney_disease_details'], $_POST['tuberculosis'], $_POST['tuberculosis_details'], $_POST['mumps'], $_POST['mumps_details'], $_POST['other_medical_history'], $_POST['other_medical_history_details'], $_POST['doc'], $_POST['confinement_details']);
        $stmt->execute();
        $stmt->close();

        //menstruation_history table
        $stmt = $conn->prepare("INSERT INTO menstruation_history (student_id, menarche, last_menstrual_period, ob_score) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $student_id, $_POST['menarche'], $_POST['dateperiod'], $_POST['ob_score']);
        $stmt->execute();
        $stmt->close();

        //smoking_history table
        $stmt = $conn->prepare("INSERT INTO smoking_history (student_id, smokes, age_of_onset, sticks_per_day) VALUES (?, ?, ?, ?)");
        $smokes = isset($_POST['smoking']) ? $_POST['smoking'] : 'no'; // Default to 'no' if not set
        $smoking_age = !empty($_POST['smoking_age']) ? $_POST['smoking_age'] : 0;
        $sticks_per_day = !empty($_POST['sticks_per_day']) ? $_POST['sticks_per_day'] : 0;
        $stmt->bind_param("isii", $student_id, $smokes, $smoking_age, $sticks_per_day);
        $stmt->execute();
        $stmt->close();

        //special_needs table
        $stmt = $conn->prepare("INSERT INTO special_needs (student_id, has_special_needs, physical_limitations, physical_limitations_specify, emotional_disorder, emotional_disorder_specify, natural_conditions, natural_conditions_specify, attention_conditions, attention_conditions_specify, medical_conditions, medical_conditions_specify, receiving_treatment, contact_permission, health_professional_name, health_professional_address, health_professional_office_number, health_professional_mobile_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssssssssssssss", $student_id, $_POST['special_needs'], $_POST['physical_limitations'], $_POST['physical_limitations_specify'], $_POST['emotional_disorder'], $_POST['emotional_disorder_specify'], $_POST['natural_conditions'], $_POST['natural_conditions_specify'], $_POST['attention_conditions'], $_POST['attention_conditions_specify'], $_POST['medical_conditions'], $_POST['medical_conditions_specify'], $_POST['receiving_condition'], $_POST['contact_permission'], $_POST['health_professional_name'], $_POST['health_professional_address'], $_POST['health_professional_office_number'], $_POST['health_professional_mobile_number']);
        $stmt->execute();
        $stmt->close();

        //guardians table
        $stmt = $conn->prepare("INSERT INTO guardians (student_id, guardian_name, guardian_date) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $student_id, $_POST['guardian_name'], $_POST['guardian_date']);
        $stmt->execute();
        $stmt->close();

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'status' => 'success',
            'message' => 'Health record submitted successfully'
        ]);
        exit;
    } catch (Exception $e) {
        $conn->rollback();

        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
        exit;
    }

    $conn->close();
}
?>
