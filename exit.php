<?php
header("Content-Type: application/json");
require_once 'db_connection.php'; 

$inputData = json_decode(file_get_contents('php://input'), true);
$action = $inputData['action'] ?? $_POST['action'] ?? $_GET['action'];

// Log the action to the error log
error_log("Action: " . $action);

// Log the entire body of the request to a file for debugging purposes
file_put_contents('debug_log.txt', json_encode($inputData) . "\n", FILE_APPEND);

$response = [
    'code' => 200,
    'message' => 'Request received',
    'received_data' => $inputData // Resending the received body
];
function insertExitInterviewData($data) {
    global $db;

    // Validate input
    $empCode = $data['emp_code'] ?? '';
    $reasons = $data['reasons'] ?? '';
    $feedback = $data['feedback'] ?? '';
    $interviewer = $data['interviewer'] ?? '';

    if (empty($empCode) || empty($reasons) || empty($feedback) || empty($interviewer)) {
        return ['code' => 400, 'message' => 'Missing required fields'];
    }

    try {
        // Fetch emp_id based on emp_code
        $stmt = $db->prepare("SELECT offboarding_id FROM wy_employees WHERE emp_code = ?");
        $stmt->bind_param("s", $empCode);  // Bind emp_code as a string
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // If no employee found
            return ['code' => 404, 'message' => 'Employee not found'];
        }

        $empId = $result->fetch_assoc()['emp_id'];

        // Create offboarding record
        $stmt = $db->prepare("INSERT INTO Offboarding (emp_id, feedback, reasons, interviewer) 
                              VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $empId, $feedback, $reasons, $interviewer);  // Bind parameters
        $stmt->execute();

        $offboardingId = $db->lastInsertId();  // Get the last inserted ID

        return ['code' => 200, 'message' => 'Exit interview data inserted successfully', 'offboarding_id' => $offboardingId];

    } catch (Exception $e) {
        return ['code' => 500, 'message' => 'Error inserting exit interview data: ' . $e->getMessage()];
    }
}


// Call the initiateOffboarding function with the data from the request
if ($action === 'AddExitInterview') {
    $offboardingResult = insertExitInterviewData($inputData); // Pass the inputData as parameter
    $response = $offboardingResult; // Use the result from initiateOffboarding
}

header('Content-Type: application/json');
echo json_encode($response);
?>
