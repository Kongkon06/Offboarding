<?php
header("Content-Type: application/json");
require_once 'db_connection.php'; // Database connection file

function handleRequest() {
    global $db;
    
    // Get JSON input
    $inputData = json_decode(file_get_contents('php://input'), true);
    $action = $inputData['action'] ?? '';

    $result = ['code' => 400, 'message' => 'Invalid action'];

    try {
        switch ($action) {
            case 'InitiateOffboarding':
                $result = initiateOffboarding($inputData);
                break;
            case 'AddExitInterview':
                $result = addExitInterview($inputData);
                break;
            case 'UpdateAssetStatus':
                $result = updateAssetStatus($inputData);
                break;
            case 'CompleteOffboarding':
                $result = completeOffboarding($inputData);
                break;
            case 'GetOffboardingStatus':
                $result = getOffboardingStatus($inputData);
                break;
            default:
                $result = ['code' => 404, 'message' => 'Action not found'];
        }
    } catch (Exception $e) {
        $result = ['code' => 500, 'message' => 'Server error: ' . $e->getMessage()];
    }

    echo json_encode($result);
}
function addExitInterview($data) {
    global $db;

    // Extract data from the provided array
    $empCode = $data['emp_code'] ?? '';
    $interviewer = $data['interviewer'] ?? '';
    $reason = $data['reason'] ?? '';
    $feedback = $data['feedback'] ?? '';

    // Validate the input
    if (empty($empCode) || empty($interviewer) || empty($reason) || empty($feedback)) {
        return ['code' => 400, 'message' => 'All fields are required'];
    }

    // Begin transaction for safety (in case something goes wrong)
    $db->begin_transaction();
    try {
        // Fetch emp_id based on emp_code
        $stmt = $db->prepare("SELECT emp_id FROM wy_employees WHERE emp_code = ?");
        $stmt->bind_param("s", $empCode);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // If no employee is found
            return ['code' => 404, 'message' => 'Employee not found'];
        }

        $empId = $result->fetch_assoc()['emp_id'];

        // Insert data into Feedback table (or any other table you're using for storing interview data)
        $stmt = $db->prepare("INSERT INTO Feedback (emp_id, interviewer, reason, feedback_text, feedback_type, submitted_at) 
                              VALUES (?, ?, ?, ?, 'Exit Survey', NOW())");
        $stmt->bind_param("isss", $empId, $interviewer, $reason, $feedback);
        $stmt->execute();

        // Commit the transaction
        $db->commit();

        return ['code' => 200, 'message' => 'Exit interview data inserted successfully'];

    } catch (Exception $e) {
        // Rollback the transaction in case of an error
        $db->rollback();
        return ['code' => 500, 'message' => 'Error inserting exit interview data: ' . $e->getMessage()];
    }
}

function initiateOffboarding($data) {
    global $db;
    
    // Validate input
    $empCode = $data['emp_code'] ?? '';
    $exitType = $data['exit_type'] ?? '';
    $lastWorkingDay = $data['last_working_day'] ?? '';

    if (empty($empCode) || empty($exitType) || empty($lastWorkingDay)) {
        return ['code' => 400, 'message' => 'Missing required fields'];
    }

    // Get employee details
    $stmt = $db->prepare("SELECT emp_id FROM wy_employees WHERE emp_code = ? AND offboarded_at IS NULL");
    $stmt->bind_param("s", $empCode);
    $stmt->execute();
    $employee = $stmt->get_result()->fetch_assoc();
    
    if (!$employee) {
        return ['code' => 404, 'message' => 'Employee not found or already offboarded'];
    }

    $db->begin_transaction();
    try {
        // Create offboarding record
        $stmt = $db->prepare("INSERT INTO Offboarding 
                            (emp_id, last_working_day, offboarding_type) 
                            VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $employee['emp_id'], $lastWorkingDay, $exitType);
        $stmt->execute();
        $offboardingId = $db->insert_id;

        // Create default assets
        $defaultAssets = [
            ['Laptop', 'Company-issued laptop'],
            ['Access Card', 'Office access card'],
            ['Company Phone', 'Mobile device']
        ];

        $assetStmt = $db->prepare("INSERT INTO Assets 
                                 (emp_id, asset_type, asset_description)
                                 VALUES (?, ?, ?)");
        foreach ($defaultAssets as $asset) {
            $assetStmt->bind_param("iss", $employee['emp_id'], $asset[0], $asset[1]);
            $assetStmt->execute();
        }

        // Create notification
        $message = "Offboarding process initiated for employee $empCode";
        $db->query("INSERT INTO Notifications (emp_id, message, notification_type)
                  VALUES ({$employee['emp_id']}, '$message', 'Offboarding Initiated')");

        $db->commit();
        return ['code' => 200, 'message' => 'Offboarding initiated successfully', 'offboarding_id' => $offboardingId];

    } catch (Exception $e) {
        $db->rollback();
        return ['code' => 500, 'message' => 'Failed to initiate offboarding: ' . $e->getMessage()];
    }
}
function updateAssetStatus($data) {
    global $db;
    
    // Extracting data from the request
    $assetId = $data['asset_id'] ?? 0;
    $status = $data['status'] ?? 0;
    $notes = $data['notes'] ?? '';
    
    // Check for invalid asset ID
    if ($assetId <= 0) {
        return ['code' => 400, 'message' => 'Invalid asset ID'];
    }

    // Get the emp_code of the asset to ensure the asset belongs to a valid employee
    $stmt = $db->prepare("SELECT emp_code FROM Assets WHERE asset_id = ?");
    $stmt->execute([$assetId]);
    $asset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$asset) {
        return ['code' => 404, 'message' => 'Asset not found'];
    }

    $empCode = $asset['emp_code'];

    try {
        // Update the asset status
        $stmt = $db->prepare("UPDATE Assets SET
                            returned = ?,
                            asset_condition = ?,
                            return_date = CURRENT_DATE
                            WHERE asset_id = ?");
        $stmt->execute([$status, $notes, $assetId]);

        // Check if all assets for the employee have been returned
        $assetCheckStmt = $db->prepare("SELECT a.asset_id 
                                        FROM Assets a
                                        JOIN Offboarding o ON a.emp_code = o.emp_code
                                        WHERE a.emp_code = ? AND o.asset_return_status = 0");
        $assetCheckStmt->execute([$empCode]);
        $assetsNotReturned = $assetCheckStmt->fetchAll(PDO::FETCH_ASSOC);

        // If no assets are left to be returned, update the Offboarding record
        if (empty($assetsNotReturned)) {
            $updateOffboardingStmt = $db->prepare("UPDATE Offboarding SET asset_return_status = 1 WHERE emp_code = ?");
            $updateOffboardingStmt->execute([$empCode]);
        }

        return ['code' => 200, 'message' => 'Asset status updated successfully'];

    } catch (Exception $e) {
        return ['code' => 500, 'message' => 'Failed to update asset status: ' . $e->getMessage()];
    }
}
function completeOffboarding($data) {
    global $db;
    
    $empCode = $data['emp_code'] ?? '';
    if (empty($empCode)) {
        return ['code' => 400, 'message' => 'Employee code required'];
    }

    $db->beginTransaction();
    try {
        // Update employee offboarding status
        $stmt = $db->prepare("UPDATE wy_employees SET offboarded_at = NOW() WHERE emp_code = ?");
        $stmt->execute([$empCode]);

        // Finalize offboarding
        $stmt = $db->prepare("UPDATE Offboarding 
                              SET account_deactivated = 1, 
                                  data_export_requested = 1 
                              WHERE emp_code = ?");
        $stmt->execute([$empCode]);

        // Create notification
        $message = "Offboarding process completed for employee $empCode";
        $stmt = $db->prepare("INSERT INTO Notifications (emp_code, message, notification_type)
                              VALUES (?, ?, ?)");
        $stmt->execute([$empCode, $message, 'Offboarding Complete']);

        $db->commit();
        return ['code' => 200, 'message' => 'Offboarding completed successfully'];

    } catch (Exception $e) {
        $db->rollback();
        return ['code' => 500, 'message' => 'Failed to complete offboarding: ' . $e->getMessage()];
    }
}

function getOffboardingStatus($data) {
    global $db;
    
    $empCode = $data['emp_code'] ?? '';
    if (empty($empCode)) {
        return ['code' => 400, 'message' => 'Employee code required'];
    }

    $stmt = $db->prepare("SELECT o.*, a.asset_type, a.returned
                        FROM Offboarding o
                        LEFT JOIN Assets a ON o.emp_code = a.emp_code
                        WHERE o.emp_code = ?");
    $stmt->execute([$empCode]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return ['code' => 200, 'data' => $result];
}


handleRequest();
?>