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
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $sql = "INSERT INTO Tasks (emp_code, task_title, task_description, due_date) 
        VALUES (:emp_code, :task_title, :task_description, :due_date)";

$stmt = $db->prepare($sql);
$stmt->bindValue(':emp_code', $emp_code);
$stmt->bindValue(':task_title', $task_title);
$stmt->bindValue(':task_description', $task_description);
$stmt->bindValue(':due_date', $due_date);

if ($stmt->execute()) {
    echo "Task added successfully.";
} else {
    echo "Error: " . json_encode($stmt->errorInfo());
}


        if ($stmt->execute()) {
            sendResponse(200, "Task added successfully.");
        } else {
            sendResponse(500, "Error: " . $stmt->error);
        }
        $stmt->close();
    } catch (Exception $e) {
        sendResponse(500, "Exception: " . $e->getMessage());
    }
} elseif ($action === 'UpdateTask') {
    try {
        global $db;

        $task_id = $inputData['task_id'] ?? null;
        $task_status = $inputData['task_status'] ?? null;
        $completion_date = $inputData['completion_date'] ?? null;

        if (!$task_id || !$task_status) {
            sendResponse(400, "Missing required fields.");
        }

        $sql = "UPDATE Tasks SET task_status = ?, completion_date = ? WHERE task_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("ssi", $task_status, $completion_date, $task_id);

        if ($stmt->execute()) {
            sendResponse(200, "Task updated successfully.");
        } else {
            sendResponse(500, "Error: " . $stmt->error);
        }
        $stmt->close();
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
else {
    sendResponse(400, "Invalid action.");
}
?>
