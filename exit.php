<?php
require_once 'original_db.php'; // Include the database connection

// Prepare and execute the query
$stmt = $origin_db->prepare("SELECT * FROM wy_employees ORDER BY emp_code");
$stmt->execute();
$offboardingRecords = $stmt->fetchAll(PDO::FETCH_ASSOC); 
// Fetch as associative array
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    font-size: 1em;
    font-family: Arial, sans-serif;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.table th, .table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.table th {
    background-color: #4CAF50; /* Green background */
    color: white; /* White text */
}

.table tr:hover {
    background-color: #f1f1f1; /* Light grey background on hover */
}

.table-row:nth-child(even) {
    background-color: #f9f9f9; /* Light grey for even rows */
}

.table-cell {
    vertical-align: middle; /* Center align cells vertically */
}

/* Optional: Add responsive design */
@media (max-width: 600px) {
    .table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
}
    </style>
</head>
<body class="bg-gray-100 p-10">
    <div class="max-w-3xl mx-auto bg-white p-5 rounded shadow">
        <h1 class="text-2xl font-bold mb-5">Assign Task</h1>

        <form id="TaskCreateForm" onsubmit={submitForm(event)}>

            <select class="form-select select2-employee" name="emp_code" required>
                                <option value="" disabled selected>Select Employee Code</option>
                                <?php foreach ($offboardingRecords as $employee): ?>
                                    <option value='<?php echo $employee['emp_code']; ?>'><?php echo $employee['emp_code'] . ' - ' . $employee['first_name'] . ' ' . $employee['last_name']; ?></option>
                                <?php endforeach; ?>
                            </select>

            <label for="task_title" class="block text-gray-700">Task Title:</label>
            <input type="text" name="task_title" id="task_title" class="border w-full p-2 mb-4" required>

            <label for="task_description" class="block text-gray-700">Task Description:</label>
            <textarea name="task_description" id="task_description" class="border w-full p-2 mb-4"></textarea>

            <label for="due_date" class="block text-gray-700">Due Date:</label>
            <input type="date" name="due_date" id="due_date" class="border w-full p-2 mb-4" required>

            <label for="task_status" class="block text-gray-700">Task Status:</label>
            <select name="task_status" id="task_status" class="border w-full p-2 mb-4">
                <option value="Pending">Pending</option>
                <option value="In Progress">In Progress</option>
                <option value="Completed">Completed</option>
            </select>

            <label for="priority" class="block text-gray-700">Priority:</label>
            <select name="priority" id="priority" class="border w-full p-2 mb-4">
                <option value="Low">Low</option>
                <option value="Medium">Medium</option>
                <option value="High">High</option>
            </select>
            <button type="submit" class="btn btn-primary" id="submitButton">
            <span id="submitText" class="bg-blue-400 p-3">Assign Task</span>
                <span id="submitSpinner" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
            </button>
        </form>
    </div>
    <form id="GetTaskForm" onsubmit={getTaskInfo(event)} class="mb-5">
        <label for="emp_code" class="block text-lg font-semibold mb-2">Select Employee Code:</label>
        <select class="form-select select2-employee" name="emp_code" required>
                                <option value="" disabled selected>Select Employee Code</option>
                                <?php foreach ($offboardingRecords as $employee): ?>
                                    <option value='<?php echo $employee['emp_code']; ?>'><?php echo $employee['emp_code'] . ' - ' . $employee['first_name'] . ' ' . $employee['last_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
        <button type="submit" class="mt-3 px-4 py-2 bg-blue-500 text-white rounded">Show Tasks</button>
    </form>
    <div id="taskContainer" class="mt-4"></div>
</body>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
        const offboardingRecords = <?php echo json_encode($offboardingRecords); ?>;

        $(document).ready(function () {
            $('.select2-employee').select2({
                placeholder: 'Select Employee Code',
                allowClear: true
            });
        });

        function submitForm(event) {
            event.preventDefault();
            const form = document.getElementById("TaskCreateForm");
            const formData = new FormData(form);
            const data = {
                action: "TaskAssign",
                emp_code: formData.get("emp_code"),
                task_title: formData.get("task_title"),
                task_description: formData.get("task_description"),
                due_date: formData.get("due_date"),
            };

            const submitButton = document.getElementById("submitButton");
            const submitText = document.getElementById("submitText");
            const submitSpinner = document.getElementById("submitSpinner");

            submitButton.disabled = true;
            submitText.textContent = "Processing...";
            submitSpinner.style.display = "inline-block";

            fetch("task_handler.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                const responseMessage = document.createElement("div");
                responseMessage.className = "alert mt-3";

                if (result.code === 200) {
                    responseMessage.classList.add("alert-success");
                    responseMessage.textContent = result.message;
                } else {
                    responseMessage.classList.add("alert-danger");
                    responseMessage.textContent = `Error: ${result.message}`;
                }

                form.appendChild(responseMessage);
            })
            .catch(error => {
                console.error("Error:", error);
                const errorMessage = document.createElement("div");
                errorMessage.className = "alert alert-danger mt-3";
                errorMessage.textContent = "An error occurred.";
                form.appendChild(errorMessage);
            })
            .finally(() => {
                submitButton.disabled = false;
                submitText.textContent = "Initiate Process";
                submitSpinner.style.display = "none";
            });
        }
    </script>
    <script>

        function getTaskInfo(event) {
            event.preventDefault();
            const form = document.getElementById("GetTaskForm");
            const formData = new FormData(form);
            const data = {
                action: "GetTask",
                emp_code: formData.get("emp_code")
            };

            const submitButton = document.getElementById("submitButton");
            const submitText = document.getElementById("submitText");
            const submitSpinner = document.getElementById("submitSpinner");

            submitButton.disabled = true;
            submitText.textContent = "Processing...";
            submitSpinner.style.display = "inline-block";

            fetch("task_handler.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
        taskContainer.innerHTML = ""; // Clear previous task content

        if (result.code === 200) {
            if (Array.isArray(result.message) && result.message.length > 0) {
    // Create a table to display task data
    const table = document.createElement("table");
    table.className = "table table-striped";

    // Table headers
    table.innerHTML = `
        <thead>
            <tr>
                <th>Task ID</th>
                <th>Title</th>
                <th>Description</th>
                <th>Assigned Date</th>
                <th>Due Date</th>
                <th>Status</th>
                <th>Completion Date</th>
            </tr>
        </thead>
    `;

    const tbody = document.createElement("tbody");

    // Populate the table with task data
    result.message.forEach(task => {
        const row = document.createElement("tr");
        row.className = "table-row"; // Add a class for styling
        row.innerHTML = `
            <td class="table-cell">${task.task_id}</td>
            <td class="table-cell">${task.task_title}</td>
            <td class="table-cell">${task.task_description}</td>
            <td class="table-cell">${task.assigned_date}</td>
            <td class="table-cell">${task.due_date}</td>
            <td class="table-cell">${task.task_status}</td>
            <td class="table-cell">${task.completion_date ?? "N/A"}</td>
        `;
        tbody.appendChild(row);
    });

    table.appendChild(tbody);
    taskContainer.appendChild(table);
} else {
                taskContainer.textContent = "No tasks found.";
            }
        } else {
            taskContainer.textContent = `Error: ${result.message}`;
        }
    })
    .catch(error => {
        console.error("Error:", error);
        taskContainer.textContent = "An error occurred.";
    })
    .finally(() => {
        submitButton.disabled = false;
        submitText.textContent = "Initiate Process";
        submitSpinner.style.display = "none";
    });
}
    </script>
</html> 