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
    exit;
}

if ($action === 'TaskAssign') {
    try {
        global $db;
    
        $emp_code = $inputData['emp_code'] ?? null;
        $task_title = $inputData['task_title'] ?? null;
        $task_description = $inputData['task_description'] ?? null;
        $due_date = $inputData['due_date'] ?? null;
        $assigned_by = $inputData['assigned_by'] ?? 'Admin'; // Default assigned_by value
    
        if (!$emp_code || !$task_title || !$assigned_by) {
            sendResponse(400, "Missing required fields.");
        }
    
        $sql = "INSERT INTO Tasks (emp_code, task_title, task_description, due_date) 
                VALUES (:emp_code, :task_title, :task_description, :due_date)";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':emp_code', $emp_code);
        $stmt->bindValue(':task_title', $task_title);
        $stmt->bindValue(':task_description', $task_description);
        $stmt->bindValue(':due_date', $due_date);
    
        // Single execution of the statement
        if ($stmt->execute()) {
            sendResponse(200, "Task added successfully.");
        } else {
            sendResponse(500, "Error: " . json_encode($stmt->errorInfo()));
        }
    
        $stmt->closeCursor(); // Close the statement properly
    } catch (Exception $e) {
        sendResponse(500, "Exception: " . $e->getMessage());
    }
    
} elseif ($action === 'UpdateTask') {
    try {
        global $db;
    
        $task_id = $inputData['task_id'] ?? null;
        $task_status = $inputData['task_status'] ?? null;
    
        if (!$task_id || !$task_status) {
            sendResponse(400, "Missing required fields.");
        }
    
        $sql = "UPDATE Tasks SET task_status = :task_status WHERE task_id = :task_id";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':task_status', $task_status);
        $stmt->bindValue(':task_id', $task_id, PDO::PARAM_INT);
    
        if ($stmt->execute()) {
            sendResponse(200, "Task updated successfully.");
        } else {
            sendResponse(500, "Error: " . json_encode($stmt->errorInfo()));
        }
    } catch (Exception $e) {
        sendResponse(500, "Exception: " . $e->getMessage());
    }
    
} else if ($action === "GetTask") {
    global $db;

    try {
        $emp_code = $inputData['emp_code'] ?? null;

        if (!$emp_code) {
            sendResponse(400, "Missing required fields.");
            exit;
        }

        $sql = "SELECT * FROM tasks WHERE emp_code = :emp_code";
        $stmt = $db->prepare($sql);

        // Bind parameter for PDO
        $stmt->bindParam(':emp_code', $emp_code, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($tasks)) {
                sendResponse(200, $tasks);
            } else {
                sendResponse(404, "No tasks found for this employee.");
            }
        } else {
            sendResponse(500, "Database error.");
        }

    } catch (Exception $e) {
        sendResponse(500, "Server error: " . $e->getMessage());
    }
}
else if($action == "FinaliseOffboarding"){
    try {
        // Check if the offboarding record exists
         global $db;
         $emp_code = $inputData['emp_code'];
        $sqlCheck = "SELECT emp_code FROM Offboarding WHERE emp_code = :emp_code";
        $stmt = $db->prepare($sqlCheck);
        $stmt->bindValue(':emp_code', $emp_code, PDO::PARAM_INT);
        $stmt->execute();
        $offboardingRecord = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$offboardingRecord) {
            sendResponse(404, "Offboarding record not found.");
            return;
        }

        // Step 1: Update Offboarding Table
        $sqlUpdateOffboarding = "
            UPDATE Offboarding 
            SET asset_return_status = 1, account_deactivated = 1, data_export_requested = 1
            WHERE emp_code = :emp_code
        ";
        $stmt = $db->prepare($sqlUpdateOffboarding);
        $stmt->bindValue(':emp_code', $emp_code, PDO::PARAM_INT);
        $stmt->execute();

        // Step 2: Update Asset Status
        $sqlUpdateAssets = "
            UPDATE Assets 
            SET returned = 1, return_date = CURRENT_DATE
            WHERE emp_code = :emp_code
        ";
        $stmt = $db->prepare($sqlUpdateAssets);
        $stmt->bindValue(':emp_code', $emp_code);
        $stmt->execute();

        // Step 3: Insert a notification for the employee
        $notificationMessage = "Your offboarding process has been finalized. Thank you for your service.";
        $sqlInsertNotification = "
            INSERT INTO Notifications (emp_code, message, notification_type)
            VALUES (:emp_code, :message, 'Offboarding Finalized')
        ";
        $stmt = $db->prepare($sqlInsertNotification);
        $stmt->bindValue(':emp_code', $emp_code);
        $stmt->bindValue(':message', $notificationMessage);
        $stmt->execute();

        // Step 4: (Optional) Record Exit Feedback if provided
        $feedbackType = 'Exit Survey'; // Example type
        $feedbackText = 'Thank you for your valuable service. We wish you the best!'; // Example text
        $sqlInsertFeedback = "
            INSERT INTO Feedback (emp_code, feedback_type, feedback_text)
            VALUES (:emp_code, :feedback_type, :feedback_text)
        ";
        $stmt = $db->prepare($sqlInsertFeedback);
        $stmt->bindValue(':emp_code', $emp_code);
        $stmt->bindValue(':feedback_type', $feedbackType);
        $stmt->bindValue(':feedback_text', $feedbackText);
        $stmt->execute();

        sendResponse(200, "Offboarding finalized successfully.");
    } catch (Exception $e) {
        sendResponse(500, "Exception: " . $e->getMessage());
    }
}else if($action == 'LoadStatus'){
    try {
        // Database connection
        global $db;
    
        // Get input data
        $emp_code = $inputData['emp_code'] ?? null;
    
        if (!$emp_code) {
            echo json_encode(['success' => false, 'message' => 'Employee code is required.']);
            exit;
        }
    
        // Query to fetch offboarding details
        $sql = "SELECT last_working_day, offboarding_type, reason, asset_return_status, account_deactivated, data_export_requested
                FROM Offboarding
                WHERE emp_code = :emp_code";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':emp_code', $emp_code);
        $stmt->execute();
    
        $details = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($details) {
            echo json_encode(['success' => true, 'details' => $details]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No offboarding record found for the selected employee.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}else{
    sendResponse(400, "Invalid action.");
}
?>
