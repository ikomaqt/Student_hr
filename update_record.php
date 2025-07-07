<?php
session_start();
include 'sqlconnection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

try {
    $conn->begin_transaction();

    // Get student ID from LRN
    $stmt = $conn->prepare("SELECT id FROM student_info WHERE lrn = ?");
    $stmt->bind_param("s", $_POST['lrn']);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    $student_id = $student['id'];

    // Update student_info
    $stmt = $conn->prepare("UPDATE student_info SET 
        name = ?, section = ?, sex = ?, date_of_birth = ?, nationality = ?, 
        address = ?, father_name = ?, father_phone = ?, mother_name = ?, 
        mother_phone = ?, emergency_contact_name = ?, 
        emergency_contact_relationship = ?, emergency_contact_phone = ? 
        WHERE id = ?");
    $stmt->bind_param("sssssssssssssi", 
        $_POST['name'], $_POST['section'], $_POST['sex'], 
        $_POST['date_of_birth'], $_POST['nationality'], $_POST['address'], 
        $_POST['father_name'], $_POST['father_phone'], $_POST['mother_name'], 
        $_POST['mother_phone'], $_POST['emergency_contact_name'], 
        $_POST['emergency_contact_relationship'], $_POST['emergency_contact_phone'],
        $student_id
    );
    $stmt->execute();

    // Update immunization records
    $stmt = $conn->prepare("DELETE FROM immunization_record WHERE student_id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();

    if (isset($_POST['vaccine_name']) && is_array($_POST['vaccine_name'])) {
        $stmt = $conn->prepare("INSERT INTO immunization_record (student_id, vaccine_name, dose, date_given) VALUES (?, ?, ?, ?)");
        foreach ($_POST['vaccine_name'] as $key => $vaccine_name) {
            $stmt->bind_param("isss", $student_id, $vaccine_name, $_POST['dose'][$key], $_POST['date_given'][$key]);
            $stmt->execute();
        }
    }

    // Update medical history
    $stmt = $conn->prepare("INSERT INTO medical_history (student_id, allergies, allergies_details, 
        asthma, asthma_details, chicken_pox, chicken_pox_details, diabetes, diabetes_details, 
        epilepsy, epilepsy_details, heart_disorder, heart_disorder_details, kidney_disease, 
        kidney_disease_details, tuberculosis, tuberculosis_details, mumps, mumps_details, 
        other_medical_history, other_medical_history_details, doc, confinement_details) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE 
        allergies = VALUES(allergies), allergies_details = VALUES(allergies_details),
        asthma = VALUES(asthma), asthma_details = VALUES(asthma_details),
        chicken_pox = VALUES(chicken_pox), chicken_pox_details = VALUES(chicken_pox_details),
        diabetes = VALUES(diabetes), diabetes_details = VALUES(diabetes_details),
        epilepsy = VALUES(epilepsy), epilepsy_details = VALUES(epilepsy_details),
        heart_disorder = VALUES(heart_disorder), heart_disorder_details = VALUES(heart_disorder_details),
        kidney_disease = VALUES(kidney_disease), kidney_disease_details = VALUES(kidney_disease_details),
        tuberculosis = VALUES(tuberculosis), tuberculosis_details = VALUES(tuberculosis_details),
        mumps = VALUES(mumps), mumps_details = VALUES(mumps_details),
        other_medical_history = VALUES(other_medical_history), 
        other_medical_history_details = VALUES(other_medical_history_details),
        doc = VALUES(doc), confinement_details = VALUES(confinement_details)");

    $stmt->bind_param("isisisisisisisisisisisss", 
        $student_id,
        isset($_POST['allergies']), $_POST['allergies_details'],
        isset($_POST['asthma']), $_POST['asthma_details'],
        isset($_POST['chicken_pox']), $_POST['chicken_pox_details'],
        isset($_POST['diabetes']), $_POST['diabetes_details'],
        isset($_POST['epilepsy']), $_POST['epilepsy_details'],
        isset($_POST['heart_disorder']), $_POST['heart_disorder_details'],
        isset($_POST['kidney_disease']), $_POST['kidney_disease_details'],
        isset($_POST['tuberculosis']), $_POST['tuberculosis_details'],
        isset($_POST['mumps']), $_POST['mumps_details'],
        isset($_POST['other_medical_history']), $_POST['other_medical_history_details'],
        $_POST['doc'], $_POST['confinement_details']
    );
    $stmt->execute();

    // Update smoking history
    $smoking = isset($_POST['smoking']) ? $_POST['smoking'] : 'no';
    $smoking_age = isset($_POST['smoking_age']) ? $_POST['smoking_age'] : null;
    $sticks_per_day = isset($_POST['sticks_per_day']) ? $_POST['sticks_per_day'] : null;

    $stmt = $conn->prepare("INSERT INTO smoking_history (student_id, smoking, smoking_age, sticks_per_day) 
        VALUES (?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE 
        smoking = VALUES(smoking), 
        smoking_age = VALUES(smoking_age), 
        sticks_per_day = VALUES(sticks_per_day)");
    $stmt->bind_param("isii", $student_id, $smoking, $smoking_age, $sticks_per_day);
    $stmt->execute();

    // Update menstruation history for female students
    if ($_POST['sex'] === 'Female') {
        $menarche = isset($_POST['menarche']) ? $_POST['menarche'] : null;
        $dateperiod = isset($_POST['dateperiod']) ? $_POST['dateperiod'] : null;
        $ob_score = isset($_POST['ob_score']) ? $_POST['ob_score'] : null;

        $stmt = $conn->prepare("INSERT INTO menstruation_history (student_id, menarche, dateperiod, ob_score) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
            menarche = VALUES(menarche), 
            dateperiod = VALUES(dateperiod), 
            ob_score = VALUES(ob_score)");
        $stmt->bind_param("isss", $student_id, $menarche, $dateperiod, $ob_score);
        $stmt->execute();
    }

    // Update special needs
    $special_needs = isset($_POST['special_needs']) ? $_POST['special_needs'] : 'no';
    $physical_limitations = isset($_POST['physical_limitations']) ? 1 : 0;
    $emotional_disorder = isset($_POST['emotional_disorder']) ? 1 : 0;
    $natural_conditions = isset($_POST['natural_conditions']) ? 1 : 0;
    $attention_conditions = isset($_POST['attention_conditions']) ? 1 : 0;
    $medical_conditions = isset($_POST['medical_conditions']) ? 1 : 0;
    $receiving_condition = isset($_POST['receiving_condition']) ? $_POST['receiving_condition'] : 'no';
    $contact_permission = isset($_POST['contact_permission']) ? $_POST['contact_permission'] : 'no';

    $stmt = $conn->prepare("INSERT INTO special_needs (
            student_id, special_needs, physical_limitations, physical_limitations_specify,
            emotional_disorder, emotional_disorder_specify, natural_conditions,
            natural_conditions_specify, attention_conditions, attention_conditions_specify,
            medical_conditions, medical_conditions_specify, receiving_condition,
            contact_permission, health_professional_name, health_professional_address,
            health_professional_office_number, health_professional_mobile_number
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            special_needs = VALUES(special_needs),
            physical_limitations = VALUES(physical_limitations),
            physical_limitations_specify = VALUES(physical_limitations_specify),
            emotional_disorder = VALUES(emotional_disorder),
            emotional_disorder_specify = VALUES(emotional_disorder_specify),
            natural_conditions = VALUES(natural_conditions),
            natural_conditions_specify = VALUES(natural_conditions_specify),
            attention_conditions = VALUES(attention_conditions),
            attention_conditions_specify = VALUES(attention_conditions_specify),
            medical_conditions = VALUES(medical_conditions),
            medical_conditions_specify = VALUES(medical_conditions_specify),
            receiving_condition = VALUES(receiving_condition),
            contact_permission = VALUES(contact_permission),
            health_professional_name = VALUES(health_professional_name),
            health_professional_address = VALUES(health_professional_address),
            health_professional_office_number = VALUES(health_professional_office_number),
            health_professional_mobile_number = VALUES(health_professional_mobile_number)");

    $stmt->bind_param("isisissisisssssssss",
        $student_id,
        $special_needs,
        $physical_limitations,
        $_POST['physical_limitations_specify'],
        $emotional_disorder,
        $_POST['emotional_disorder_specify'],
        $natural_conditions,
        $_POST['natural_conditions_specify'],
        $attention_conditions,
        $_POST['attention_conditions_specify'],
        $medical_conditions,
        $_POST['medical_conditions_specify'],
        $receiving_condition,
        $contact_permission,
        $_POST['health_professional_name'],
        $_POST['health_professional_address'],
        $_POST['health_professional_office_number'],
        $_POST['health_professional_mobile_number']
    );
    $stmt->execute();

    // Update guardian information
    $guardian_name = isset($_POST['guardian_name']) ? $_POST['guardian_name'] : '';
    $guardian_date = isset($_POST['guardian_date']) ? $_POST['guardian_date'] : null;

    $stmt = $conn->prepare("INSERT INTO guardians (student_id, guardian_name, guardian_date) 
        VALUES (?, ?, ?) 
        ON DUPLICATE KEY UPDATE 
        guardian_name = VALUES(guardian_name), 
        guardian_date = VALUES(guardian_date)");
    $stmt->bind_param("iss", $student_id, $guardian_name, $guardian_date);
    $stmt->execute();

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Record updated successfully']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>
