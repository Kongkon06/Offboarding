<?php
header("Content-Type: application/json");
require_once 'db_connection.php'; 

$inputData = json_decode(file_get_contents('php://input'), true);
$action = $inputData['action'] ?? $_POST['action'] ?? $_GET['action'];

// Log the action to the error log
error_log("Action: " . $action);

// Log the entire body of the request to a file for debugging purposes
file_put_contents('debug_log.txt', json_encode($inputData) . "\n", FILE_APPEND);

function sendResponse($code, $message) {
    echo json_encode(['code' => $code, 'message' => $message]);
    exit; // Ensure no further output is sent
}

function syncEmployeeData($inputData) {
    global $db;

    // Get the specific employee data from the input
    $data = $inputData['offboarding_records'];

    // Convert dob to YYYY-MM-DD format if it exists and is in DD/MM/YYYY format
    if (isset($data['dob'])) {
        $dob = $data['dob'];
        // Check if the date is in DD/MM/YYYY format
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dob)) {
            // Convert DD/MM/YYYY to YYYY-MM-DD
            $dateParts = explode('/', $dob);
            $data['dob'] = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
        } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
            // If it's already in YYYY-MM-DD format, keep it as is
            $data['dob'] = $dob;
        } else {
            // If the format is invalid, set it to NULL or handle the error
            $data['dob'] = null; // or throw an exception
        }
    }

    try {
        // Prepare the SQL statement for inserting the employee data
        $stmt = $db->prepare("
            INSERT INTO wy_employees (
                emp_code, emp_password, first_name, last_name, dob, gender, marital_status, 
                nationality, address, city, state, country, email, mobile, telephone, 
                identity_doc, identity_no, emp_type, joining_date, blood_group, photo, 
                designation, department, pan_no, bank_name, account_no, ifsc_code, pf_account, created
            ) VALUES (
                :emp_code, :emp_password, :first_name, :last_name, :dob, :gender, :marital_status, 
                :nationality, :address, :city, :state, :country, :email, :mobile, :telephone, 
                :identity_doc, :identity_no, :emp_type, :joining_date, :blood_group, :photo, 
                :designation, :department, :pan_no, :bank_name, :account_no, :ifsc_code, :pf_account, :created
            )
        ");
        
        // Execute the prepared statement with the employee data
        $stmt->execute([
            ':emp_code' => $data['emp_code'],
            ':emp_password' => $data['emp_password'],
            ':first_name' => $data['first_name'],
            ':last_name' => $data['last_name'],
            ':dob' => $data['dob'],
            ':gender' => $data['gender'],
            ':marital_status' => 'aa', // Assuming marital status is fixed as 'aa'
            ':nationality' => $data['nationality'],
            ':address' => $data['address'],
            ':city' => $data['city'],
            ':state' => $data['state'],
            ':country' => $data['country'],
            ':email' => $data['email'],
            ':mobile' => $data['mobile'],
            ':telephone' => $data['telephone'],
            ':identity_doc' => $data['identity_doc'],
            ':identity_no' => $data['identity_no'],
            ':emp_type' => $data['emp_type'],
            ':joining_date' => $data['joining_date'],
            ':blood_group' => $data['blood_group'],
            ':photo' => $data['photo'],
            ':designation' => $data['designation'],
            ':department' => $data['department'],
            ':pan_no' => $data['pan_no'],
            ':bank_name' => $data['bank_name'],
            ':account_no' => $data['account_no'],
            ':ifsc_code' => $data['ifsc_code'],
            ':pf_account' => $data['pf_account'],
            ':created' => $data['created'],
        ]);
    } catch (Exception $e) {
        // Convert the input data array to a JSON string for better readability in the error message
        $inputDataString = json_encode($inputData);
        sendResponse(500, 'Failed to insert employee data: ' . $e->getMessage() . ' Input Data: ' . $inputDataString);
    }
    return ['code' => 200, 'message' => 'Employee data synced successfully'];
}

function initiateOffboarding($inputData) {
    global $db;
    $empCode = $inputData['emp_code'] ?? '';
    $exitType = $inputData['exit_type'] ?? '';
    $lastWorkingDay = $inputData['last_working_day'] ?? '';
    $reason = $inputData['reasons'] ?? '';
    $exitInterview = $inputData['exit_interview'] ?? '';

    if (empty($empCode) || empty($exitType) || empty($lastWorkingDay)) {
        sendResponse(400, 'Missing required fields');
    }

    try {
        $stmt = $db->prepare("INSERT INTO Offboarding 
                            (emp_code, last_working_day, offboarding_type, reason, exit_interview) 
                            VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$empCode, $lastWorkingDay, $exitType, $reason, $exitInterview]);

        sendResponse(200, 'Offboarding initiated successfully');
    } catch (Exception $e) {
        sendResponse(500, 'Failed to initiate offboarding: ' . $e->getMessage());
    }
}

function updateAssetStatus($inputData) {
    global $db;
    $empCode = $inputData['emp_code'] ?? '';
    $assetType = $inputData['asset_type'] ?? '';
    $returnDay = $inputData['return_date'] ?? '';
    $status = $inputData['status'] ?? '';
    $assetDescription = $inputData['asset_description'] ?? '';

    try {
        $stmt = $db->prepare("INSERT INTO Assets 
                            (emp_code, return_date, asset_type, asset_description, returned) 
                            VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$empCode, $returnDay, $assetType, $assetDescription, $status]);

        sendResponse(200, 'Assets updated successfully');
    } catch (Exception $e) {
        sendResponse(500, 'Failed to update asset status: ' . $e->getMessage());
    }
}

function addExitInterview($data) {
    global $db;
    try {
        // Start transaction
        $db->beginTransaction();

        // Update the `Offboarding` table
        $updateOffboardingSql = "UPDATE Offboarding 
                                  SET reason = :reason, exit_interview = :interviewer
                                  WHERE emp_code = :emp_code";
        $updateOffboardingStmt = $db->prepare($updateOffboardingSql);
        
        $reason = $data['reasons'] ?? "";
        $interviewer = $data['interviewer'];
        $empCode = $data['emp_code'];

        $updateOffboardingStmt->bindParam(':reason', $reason);
        $updateOffboardingStmt->bindParam(':interviewer', $interviewer);
        $updateOffboardingStmt->bindParam(':emp_code', $empCode);
        $updateOffboardingStmt->execute();

        if ($updateOffboardingStmt->rowCount() === 0) {
            throw new Exception("No records found for emp_code.");
        }

        // Insert a new record into the `Feedback` table
        $insertFeedbackSql = "INSERT INTO Feedback (emp_code, feedback_type, feedback_text) 
                               VALUES (:emp_code, :feedback_type, :feedback_text)";
        $insertFeedbackStmt = $db->prepare($insertFeedbackSql);

        $feedbackType = 'critic';
        $feedbackText = $data['feedback'];

        $insertFeedbackStmt->bindParam(':emp_code', $empCode);
        $insertFeedbackStmt->bindParam(':feedback_type', $feedbackType);
        $insertFeedbackStmt->bindParam(':feedback_text', $feedbackText);
        $insertFeedbackStmt->execute();

        // Commit the transaction
        $db->commit();
        sendResponse(200, "Records updated and feedback inserted successfully.");
    } catch (Exception $e) {
        $db->rollBack();
        // Roll back the transaction in case of error
        $inputDataString = json_encode($data);
        sendResponse(500, 'Failed to insert employee data: ' . $e->getMessage() . ' Input Data: ' . $inputDataString);
    }
}
function viewExitInterview($data) {
    global $db; // Assuming $db is your database connection
    $emp_code = $data['emp_code'];
    try {
        $stmt = $db->prepare("
            SELECT 
                o.offboarding_id,
                o.emp_code,
                o.last_working_day,
                o.offboarding_type,
                o.reason,
                o.exit_interview,
                o.asset_return_status,
                o.account_deactivated,
                o.data_export_requested
            FROM 
                Offboarding o
            WHERE 
                o.emp_code = :emp_code
        ");

        $stmt->execute([':emp_code' => $emp_code]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch the result as an associative array

        if ($result) {
            sendResponse(200,$result); // Return success response with data
        } else {
            return ['code' => 404, 'message' => 'No exit interview found for this employee.'];
        }
    } catch (Exception $e) {
        return ['code' => 500, 'message' => 'Error retrieving exit interview: ' . $e->getMessage()];
    }
}

if ($action === 'InitiateOffboarding') {
    $syncResult = syncEmployeeData($inputData);
    if ($syncResult['code'] === 200) {
        $offboardingResult = initiateOffboarding($inputData); // Call the offboarding function
        $response = $offboardingResult; // This should also return a response
    } else {
        sendResponse(500, 'Failed to insert employee data: ' . $e->getMessage()); // Return the sync error response
    }
} else if ($action === 'AddExitInterview') {
    addExitInterview($inputData);
} else if ($action === 'UpdateAssetStatus') {
    updateAssetStatus($inputData);
} else if($action === 'ViewExitInterview'){
    viewExitInterview($inputData);
}

header('Content-Type: application/json');
?>