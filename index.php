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
    <title>Employee Offboarding System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .section-card { margin-bottom: 2rem; }
        .ajax-loading { display: none; }
        .status-badge { font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Employee Offboarding Portal</h1>

        <!-- Initiate Offboarding Section -->
        <div class="card section-card">
            <div class="card-header bg-primary text-white">
                <h3>Initiate Offboarding</h3>
            </div>
            <div class="card-body">
                <form id="initiateOffboardingForm" onsubmit="submitForm(event)">
                    <div class="row g-3">
                        <!-- Employee Code Dropdown -->
                        <div class="col-md-4">
                            <label class="form-label">Employee Code</label>
                            <select class="form-select select2-employee" name="emp_code" required>
                                <option value="" disabled selected>Select Employee Code</option>
                                <?php foreach ($offboardingRecords as $employee): ?>
                                    <option value='<?php echo $employee['emp_code']; ?>'><?php echo $employee['emp_code'] . ' - ' . $employee['first_name'] . ' ' . $employee['last_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Exit Type Dropdown -->
                        <div class="col-md-4">
                            <label class="form-label">Exit Type</label>
                            <select class="form-select" name="exit_type" required>
                                <option value="" disabled selected>Select Exit Type</option>
                                <option value="resignation">Resignation</option>
                                <option value="retirement">Retirement</option>
                                <option value="termination">Termination</option>
                            </select>
                        </div>

                        <!-- Last Working Day Input -->
                        <div class="col-md-4">
                            <label class="form-label">Last Working Day</label>
                            <input type="date" class="form-control" name="last_working_day" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <!-- Submit Button -->
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary" id="submitButton">
                                <span id="submitText">Initiate Process</span>
                                <span id="submitSpinner" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
                            </button>
                        </div>
                    </div>
                </form>
                <div id="responseMessage" style="margin-top: 1em;"></div>
            </div>
        </div>

        <!-- Exit Interview Section -->
        <div class="card section-card">
            <div class="card-header bg-info text-white">
                <h3>Exit Interview</h3>
            </div>
            <div class="card-body">
                <form id="AddExitInterviewForm" onsubmit="submitExitInterviewForm(event)">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Employee Code</label>
                            <select class="form-select select2-employee" name="emp_code" required>
                                <?php foreach ($offboardingRecords as $employee): ?>
                                    <option value='<?php echo $employee['emp_code']; ?>'><?php echo $employee['emp_code']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Interviewer</label>
                            <input type="text" class="form-control" name="interviewer" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Reasons for Leaving</label>
                            <textarea class="form-control" name="reasons" rows="2" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Feedback</label>
                            <textarea class="form-control" name="feedback" rows="3" required></textarea>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-info" type="submit">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- View Exit Interview Section -->
        <div class="card section-card">
            <div class="card-header bg-info text-white">
                <h3>View Exit Interview</h3>
            </div>
            <div class="card-body">
                <form id="ViewExitInterviewForm" onsubmit="viewExitInterview(event)">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Employee Code</label>
                            <select class="form-select select2-employee" name="emp_code" id="emp_code" required>
                                <?php foreach ($offboardingRecords as $employee): ?>
                                    <option value='<?php echo $employee['emp_code']; ?>'><?php echo $employee['emp_code']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-info" id="searchButton">Search</button>
                        </div>
                    </div>
                    <div id="resultContainerExit" class="mt-4 h-full w-full"></div>
                </form>
            </div>
        </div>

        <!-- Asset Management Section -->
        <div class="card section-card">
            <div class="card-header bg-warning">
                <h3>Asset Management</h3>
            </div>
            <div class="card-body">
                <form id="UpdateAssetStatusForm" onsubmit="submitAsset(event)">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Employee Code</label>
                            <select class="form-select select2-employee" name="emp_code" required>
                                <?php foreach ($offboardingRecords as $employee): ?>
                                    <option value='<?php echo $employee['emp_code']; ?>'><?php echo $employee['emp_code']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Asset Type</label>
                            <select class="form-select" id="asset_type" name="asset_type" required>
                                <option value="" disabled selected>Select an asset type</option>
                                <option value="Electronics">Electronics</option>
                                <option value="Furniture">Furniture</option>
                                <option value="Stationery">Stationery</option>
                                <option value="Software License">Software License</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="0">Not Returned</option>
                                <option value="1">Returned</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Asset Description</label>
                            <textarea class="form-control" id="asset_description" name="notes" rows="2" required></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Returned Date</label>
                            <input type="date" class="form-control" name="return_date" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-warning" type="submit">Update Asset</button>
                        </div>
                    </div>
                </form>
                <div class="mt-4" id="assetStatusContainer"></div>
            </div>
        </div>

        <!-- Update Offboarding Task Section -->
        <div class="card section-card">
            <div class="card-header bg-warning">
                <h3>Update Offboarding Task</h3>
            </div>
            <div class="card-body">
                <form id="taskUpdateForm" onsubmit="submitTaskUpdateForm(event)">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Task ID</label>
                            <input type="number" class="form-control" name="task_id" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="pending">Pending</option>
                                <option value="in-progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="2" required></textarea>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-warning" type="submit">Update Task</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Complete Offboarding Section -->
        <div class="card section-card">
            <div class="card-header bg-danger text-white">
                <h3>Complete Offboarding</h3>
            </div>
            <div class="card-body">
                <form id="completeOffboardingForm" onsubmit="submitCompleteOffboardingForm(event)">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Employee Code</label>
                            <select class="form-select select2-employee" name="emp_code" required>
                                <?php foreach ($offboardingRecords as $employee): ?>
                                    <option value='<?php echo $employee['emp_code']; ?>'><?php echo $employee['emp_code']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-danger" type="submit">Finalize Offboarding</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Status Display Section -->
        <div class="card section-card">
            <div class="card-header bg-success text-white">
                <h3>Offboarding Status</h3>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Employee Code</label>
                        <select class="form-select select2-employee" name="emp_code" required>
                            <?php foreach ($offboardingRecords as $employee): ?>
                                <option value='<?php echo $employee['emp_code']; ?>'><?php echo $employee['emp_code']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-success" onclick="loadOffboardingStatus()">Load Status</button>
                    </div>
                    <div class="col-12" id="statusDetails"></div>
                </div>
            </div>
        </div>

        <!-- Loading Indicator -->
        <div class="ajax-loading" id="loading">
            <div class="d-flex justify-content-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>

        <!-- Results Container -->
        <div id="resultContainer" class="mt-4"></div>
    </div>

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
            const form = document.getElementById("initiateOffboardingForm");
            const formData = new FormData(form);
            const selectedOffboardingRecord = offboardingRecords.find(record => record.emp_code === formData.get("emp_code"));
            console.log(selectedOffboardingRecord);
            const data = {
                action: "InitiateOffboarding",
                emp_code: formData.get("emp_code"),
                exit_type: formData.get("exit_type"),
                last_working_day: formData.get("last_working_day"),
                offboarding_records: selectedOffboardingRecord
            };

            const submitButton = document.getElementById("submitButton");
            const submitText = document.getElementById("submitText");
            const submitSpinner = document.getElementById("submitSpinner");

            submitButton.disabled = true;
            submitText.textContent = "Processing...";
            submitSpinner.style.display = "inline-block";

            fetch("test.php", {
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

        function submitExitInterviewForm(event) {
            event.preventDefault();
            const form = document.getElementById("AddExitInterviewForm");
            const formData = new FormData(form);

            const data = {
                action: 'AddExitInterview',
                emp_code: formData.get("emp_code"),
                interviewer: formData.get("interviewer"),
                reasons: formData.get("reasons"),
                feedback: formData.get("feedback")
            };

            fetch("test.php", {
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
                    form.reset();
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
                errorMessage.textContent = "An error occurred while submitting the form.";
                form.appendChild(errorMessage);
            });
        }

        function viewExitInterview(event) {
            event.preventDefault();
            const form = document.getElementById("ViewExitInterviewForm");
            const formData = new FormData(form);
            const empCode = formData.get("emp_code");

            if (!empCode) {
                alert("Please select an employee code.");
                return;
            }

            const data = {
                action: 'ViewExitInterview',
                emp_code: empCode
            };

            fetch("test.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                const resultsContainer = document.getElementById("resultContainerExit");
                const responseMessage = document.createElement("div");
                responseMessage.className = "alert mt-3";

                if (result.code === 200) {
                    const interviewData = result.message;
                    responseMessage.classList.add("alert-success");
                    responseMessage.innerHTML = `
                        <strong>Exit Interview Details:</strong><br>
                        <strong>Last Working Day:</strong> ${interviewData.last_working_day}<br>
                        <strong>Offboarding Type:</strong> ${interviewData.offboarding_type}<br>
                        <strong>Reason:</strong> ${interviewData.reason || 'N/A'}<br>
                        <strong>Exit Interview:</strong> ${interviewData.exit_interview || 'N/A'}<br>
                        <strong>Asset Return Status:</strong> ${interviewData.asset_return_status ? 'Returned' : 'Not Returned'}<br>
                        <strong>Account Deactivated:</strong> ${interviewData.account_deactivated ? 'Yes' : 'No'}<br>
                        <strong>Data Export Requested:</strong> ${interviewData.data_export_requested ? 'Yes' : 'No'}<br>
                    `;
                } else {
                    responseMessage.classList.add("alert-danger");
                    responseMessage.textContent = `Error: ${result.message}`;
                }

                resultsContainer.appendChild(responseMessage);
            })
            .catch(error => {
                const resultsContainer = document.getElementById("resultContainerExit");
                console.error("Error:", error);
                const errorMessage = document.createElement("div");
                errorMessage.className = "alert alert-danger mt-3";
                errorMessage.textContent = "An error occurred while fetching the exit interview details.";
                resultsContainer.appendChild(errorMessage);
            });
        }

        function submitAsset(event) {
            event.preventDefault();
            const form = document.getElementById("UpdateAssetStatusForm");
            const formData = new FormData(form);

            const data = {
                action: 'UpdateAssetStatus',
                emp_code: formData.get("emp_code"),
                asset_type: formData.get("asset_type"),
                status: formData.get("status"),
                notes: formData.get("notes"),
                return_date: formData.get("return_date")
            };

            fetch("test.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                const assetStatusContainer = document.getElementById("assetStatusContainer");
                const responseMessage = document.createElement("div");
                responseMessage.className = "alert mt-3";

                if (result.code === 200) {
                    responseMessage.classList.add("alert-success");
                    responseMessage.textContent = result.message;
                } else {
                    responseMessage.classList.add("alert-danger");
                    responseMessage.textContent = `Error: ${result.message}`;
                }

                assetStatusContainer.appendChild(responseMessage);
            })
            .catch(error => {
                console.error("Error:", error);
                const errorMessage = document.createElement("div");
                errorMessage.className = "alert alert-danger mt-3";
                errorMessage.textContent = "An error occurred while updating the asset status.";
                assetStatusContainer.appendChild(errorMessage);
            });
        }

        function submitTaskUpdateForm(event) {
            event.preventDefault();
            const form = document.getElementById("taskUpdateForm");
            const formData = new FormData(form);

            const data = {
                action: 'UpdateTask',
                task_id: formData.get("task_id"),
                status: formData.get("status"),
                notes: formData.get("notes")
            };

            fetch("test.php", {
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
                errorMessage.textContent = "An error occurred while updating the task.";
                form.appendChild(errorMessage);
            });
        }

        function submitCompleteOffboardingForm(event) {
            event.preventDefault();
            const form = document.getElementById("completeOffboardingForm");
            const formData = new FormData(form);

            const data = {
                action: 'CompleteOffboarding',
                emp_code: formData.get("emp_code")
            };

            fetch("test.php", {
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
                errorMessage.textContent = "An error occurred while completing the offboarding process.";
                form.appendChild(errorMessage);
            });
        }

        function loadOffboardingStatus() {
            const empCode = document.querySelector("#statusDisplaySection select").value;

            if (!empCode) {
                alert("Please select an employee code.");
                return;
            }

            const data = {
                action: 'LoadOffboardingStatus',
                emp_code: empCode
            };

            fetch("test.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                const statusDetails = document.getElementById("statusDetails");
                const responseMessage = document.createElement("div");
                responseMessage.className = "alert mt-3";

                if (result.code === 200) {
                    const statusData = result.data;
                    responseMessage.classList.add("alert-success");
                    responseMessage.innerHTML = `
                        <strong>Offboarding Status:</strong><br>
                        <strong>Employee Code:</strong> ${statusData.emp_code}<br>
                        <strong>Status:</strong> ${statusData.status}<br>
                        <strong>Last Updated:</strong> ${statusData.last_updated}<br>
                    `;
                } else {
                    responseMessage.classList.add("alert-danger");
                    responseMessage.textContent = `Error: ${result.message}`;
                }

                statusDetails.appendChild(responseMessage);
            })
            .catch(error => {
                console.error("Error:", error);
                const errorMessage = document.createElement("div");
                errorMessage.className = "alert alert-danger mt-3";
                errorMessage.textContent = "An error occurred while loading the offboarding status.";
                statusDetails.appendChild(errorMessage);
            });
        }
    </script>
</body>
</html>