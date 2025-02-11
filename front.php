<?php
require_once 'original_db.php'; // Include the database connection

// Prepare and execute the query
$stmt = $origin_db->prepare("SELECT * FROM wy_employees ORDER BY emp_code");
$stmt->execute();
$offboardingRecords = $stmt->fetchAll(PDO::FETCH_ASSOC); 
// Fetch as associative array
?>
<!DOCTYPE html>
<html>
<head>
<style>
    :root {
        --primary-color: #2c3e50;
        --secondary-color: #34495e;
        --accent-color: #3498db;
        --success-color: #27ae60;
        --warning-color: #f39c12;
        --danger-color: #c0392b;
        --light-bg: #f8f9fa;
    }

    body {
        background-color: #f5f6fa;
        color: var(--primary-color);
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        margin: 0;
        padding: 0;
    }

    .portal-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 2rem 0;
        margin-bottom: 0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1rem;
    }

    .portal-title {
        font-weight: 300;
        letter-spacing: 0.5px;
        margin: 0;
    }

    .portal-subtitle {
        opacity: 0.8;
        font-size: 1rem;
        margin: 0.5rem 0 0 0;
    }

    /* Tab Styling */
    .tabs {
        background: white;
        padding: 1rem 1rem 0;
        border-bottom: 1px solid #dee2e6;
    }

    .tab-buttons {
        display: flex;
        gap: 0.5rem;
        margin-bottom: -1px;
        flex-wrap: wrap;
    }

    .tab-button {
        padding: 0.75rem 1.5rem;
        background: none;
        border: 1px solid transparent;
        border-radius: 6px 6px 0 0;
        cursor: pointer;
        font-weight: 500;
        color: var(--secondary-color);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .tab-button.active {
        background: white;
        border-color: #dee2e6;
        border-bottom-color: white;
        color: var(--accent-color);
    }

    .tab-button:hover:not(.active) {
        background: var(--light-bg);
    }

    /* Tab Content */
    .tab-content {
        display: none;
        padding: 2rem;
        background: white;
        animation: fadeIn 0.3s ease-in-out;
    }

    .tab-content.active {
        display: block;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .form-label {
        font-weight: 500;
        color: var(--secondary-color);
        margin-bottom: 0.5rem;
        display: block;
    }

    .form-control {
        width: 100%;
        padding: 0.625rem;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        box-sizing: border-box;
    }

    .form-control:focus {
        border-color: var(--accent-color);
        outline: none;
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }

    .btn {
        padding: 0.625rem 1.25rem;
        border-radius: 6px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary {
        background-color: var(--accent-color);
        color: white;
    }

    .btn-warning {
        background-color: var(--warning-color);
        color: white;
    }

    .btn-danger {
        background-color: var(--danger-color);
        color: white;
    }

    .btn:hover {
        opacity: 0.9;
    }

    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255,255,255,0.8);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
</style>
</head>
<body>
    <header class="portal-header">
        <div class="container">
            <h1 class="portal-title">Enterprise Offboarding Portal</h1>
            <p class="portal-subtitle">Streamline your employee offboarding process</p>
        </div>
    </header>

    <div class="tabs">
        <div style="display:flex;justify-content:center">
            <div class="tab-buttons">
                <button class="tab-button active" onclick="openTab(event, 'initiateTab')">
                    <i class="fas fa-user-minus"></i>
                    Initiate Offboarding
                </button>
                <button class="tab-button" onclick="openTab(event, 'interviewTab')">
                    <i class="fas fa-comments"></i>
                    Exit Interview
                </button>
                <button class="tab-button" onclick="openTab(event, 'viewInterviewTab')">
                    <i class="fas fa-check-double"></i>
                    View Exit Interview
                </button>
                <button class="tab-button" onclick="openTab(event, 'assetTab')">
                    <i class="fas fa-laptop"></i>
                    Asset Management
                </button>
                <button class="tab-button" onclick="openTab(event, 'completeTab')">
                    <i class="fas fa-check-double"></i>
                    Complete Offboarding
                </button>
                <button class="tab-button" onclick="openTab(event, 'statusTab')">
                    <i class="fas fa-check-double"></i>
                   Load offboarding Status
                </button>
                <button class="tab-button" onclick="openTab(event, 'taskTab')">
                    <i class="fas fa-check-double"></i>
                    Task Update
                </button>
            </div>
        </div>
    </div>

    <div class="container">
        <div id="initiateTab" class="tab-content active">
            <form id="initiateOffboardingForm" onsubmit={submitForm(event)}>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Employee Code</label>
                        <select class="form-control" name="emp_code" required>
                            <option value="" disabled selected>Select Employee Code</option>
                            <?php foreach ($offboardingRecords as $employee): ?>
                                    <option value='<?php echo $employee['emp_code']; ?>'>
                                        <?php echo $employee['emp_code'] . ' - ' . $employee['first_name'] . ' ' . $employee['last_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Exit Type</label>
                        <select class="form-control" name="exit_type" required>
                            <option value="" disabled selected>Select Exit Type</option>
                            <option value="resignation">Resignation</option>
                            <option value="retirement">Retirement</option>
                            <option value="termination">Termination</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Working Day</label>
                        <input type="date" class="form-control" name="last_working_day" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Initiate Process</button>
            </form>
        </div>

        <div id="interviewTab" class="tab-content">
            <form id="exitInterviewForm" onsubmit={submitExitInterviewForm(event)}>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Employee Code</label>
                        <select class="form-control" name="emp_code" required>
                            <option value="" disabled selected>Select Employee Code</option>
                            <?php foreach ($offboardingRecords as $employee): ?>
                                    <option value='<?php echo $employee['emp_code']; ?>'>
                                        <?php echo $employee['emp_code'] . ' - ' . $employee['first_name'] . ' ' . $employee['last_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Interviewer</label>
                        <input type="text" class="form-control" name="interviewer" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Reasons for Leaving</label>
                    <textarea class="form-control" name="reasons" rows="2" required></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Feedback</label>
                    <textarea class="form-control" name="feedback" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit Interview</button>
            </form>
        </div>

        <div id="assetTab" class="tab-content">
            <form id="UpdateAssetStatusForm" onsubmit={submitAsset(event)}>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Employee Code</label>
                        <select class="form-control" name="emp_code" required>
                            <option value="" disabled selected>Select Employee Code</option>
                            <?php foreach ($offboardingRecords as $employee): ?>
                                    <option value='<?php echo $employee['emp_code']; ?>'>
                                        <?php echo $employee['emp_code'] . ' - ' . $employee['first_name'] . ' ' . $employee['last_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Asset Type</label>
                        <select class="form-control" name="asset_type" required>
                            <option value="" disabled selected>Select an asset type</option>
                            <option value="Electronics">Electronics</option>
                            <option value="Furniture">Furniture</option>
                            <option value="Stationery">Stationery</option>
                            <option value="Software License">Software License</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select class="form-control" name="status" required>
                            <option value="0">Not Returned</option>
                            <option value="1">Returned</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Asset Description</label>
                    <textarea class="form-control" name="asset_description" rows="2" required></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Returned Date</label>
                    <input type="date" class="form-control" name="return_date" required>
                </div>
                <button type="submit" class="btn btn-warning">Update Asset</button>
            </form>
        </div>

        <div id="completeTab" class="tab-content">
            <form id="completeOffboardingForm" onsubmit={submitCompleteOffboardingForm(event)}>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Employee Code</label>
                        <select class="form-control" name="emp_code" required>
                            <option value="" disabled selected>Select Employee Code</option>
                            <?php foreach ($offboardingRecords as $employee): ?>
                                    <option value='<?php echo $employee['emp_code']; ?>'>
                                        <?php echo $employee['emp_code'] . ' - ' . $employee['first_name'] . ' ' . $employee['last_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-danger">Finalize Offboarding</button>
            </form>
        </div>
        <div id="statusTab" class="tab-content">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Employee Code</label>
                    <select class="form-control" name="emp_code" required>
                        <option value="" disabled selected>Select Employee Code</option>
                        <?php foreach ($offboardingRecords as $employee): ?>
                                    <option value='<?php echo $employee['emp_code']; ?>'>
                                        <?php echo $employee['emp_code'] . ' - ' . $employee['first_name'] . ' ' . $employee['last_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <button class="btn btn-success" onclick="loadOffboardingStatus()">Load Status</button>
                </div>
            </div>
            <div id="statusDetails" class="status-grid">
                <!-- Status details will be loaded here -->
            </div>
        </div>

        <div id="taskTab" class="tab-content">
            <form id="taskUpdateForm" onsubmit="return false;">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Task ID</label>
                        <input type="number" class="form-control" name="task_id" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select class="form-control" name="status" required>
                            <option value="pending">Pending</option>
                            <?php foreach ($offboardingRecords as $employee): ?>
                                    <option value='<?php echo $employee['emp_code']; ?>'>
                                        <?php echo $employee['emp_code'] . ' - ' . $employee['first_name'] . ' ' . $employee['last_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea class="form-control" name="notes" rows="2" required></textarea>
                </div>
                <button type="submit" class="btn btn-warning">Update Task</button>
            </form>
        </div>

        <div id="viewInterviewTab" class="tab-content">
            <form id="ViewExitInterviewForm" onsubmit="return false;">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Employee Code</label>
                        <select class="form-control" name="emp_code" required>
                            <option value="" disabled selected>Select Employee Code</option>
                            <?php foreach ($offboardingRecords as $employee): ?>
                                    <option value='<?php echo $employee['emp_code']; ?>'>
                                        <?php echo $employee['emp_code'] . ' - ' . $employee['first_name'] . ' ' . $employee['last_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-info" id="searchButton">Search</button>
                    </div>
                </div>
                <div id="resultContainerExit"></div>
            </form>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loading">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <script>
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            
            // Hide all tab content
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].classList.remove("active");
            }
            
            // Remove active class from all tab buttons
            tablinks = document.getElementsByClassName("tab-button");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].classList.remove("active");
            }
            
            // Show the selected tab and activate the button
            document.getElementById(tabName).classList.add("active");
            evt.currentTarget.classList.add("active");
        }
    </script>
    <script>
        const offboardingRecords = <?php echo json_encode($offboardingRecords); ?>;
        // Initialize Select2
        $(document).ready(function() {
            $('.select2-employee').select2({
                theme: 'classic',
                width: '100%',
                placeholder: 'Select Employee Code',
                allowClear: true
            });

            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
        });

        // Show/hide loading overlay
        function toggleLoading(show) {
            const loading = document.getElementById('loading');
            loading.style.display = show ? 'flex' : 'none';
        }

        // Generic form submission handler
        async function handleFormSubmission(formData, endpoint) {
            toggleLoading(true);
            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                const result = await response.json();
                return result;
            } catch (error) {
                console.error('Error:', error);
                return {
                    code: 500,
                    message: 'An unexpected error occurred. Please try again.'
                };
            } finally {
                toggleLoading(false);
            }
        }

        // Create toast notification
        function showToast(message, type = 'success') {
            const toastContainer = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast show fade-in bg-${type} text-white`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            
            toast.innerHTML = `
                <div class="toast-header bg-${type} text-white">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                    <strong class="me-auto">Notification</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            `;
            
            toastContainer.appendChild(toast);
            setTimeout(() => {
                toast.remove();
            }, 5000);
        }

        // Initiate Offboarding Form Submission
        async function submitForm(event) {
            event.preventDefault();
            const form = document.getElementById('initiateOffboardingForm');
            const formData = new FormData(form);
            
            const submitButton = document.getElementById('submitButton');
            const submitText = document.getElementById('submitText');
            const submitSpinner = document.getElementById('submitSpinner');
            const selectedOffboardingRecord = offboardingRecords.find(record => record.emp_code === formData.get("emp_code"));
            console.log(selectedOffboardingRecord);
            
            submitButton.disabled = true;
            submitText.textContent = 'Processing...';
            submitSpinner.style.display = 'inline-block';

            const data = {
                action: 'InitiateOffboarding',
                emp_code: formData.get('emp_code'),
                exit_type: formData.get('exit_type'),
                last_working_day: formData.get('last_working_day'),
                offboarding_records: selectedOffboardingRecord
            };

            try {
                const result = await handleFormSubmission(data, 'test.php');
                if (result.code === 200) {
                    showToast(result.message, 'success');
                    form.reset();
                    $('.select2-employee').val(null).trigger('change');
                } else {
                    showToast(result.message, 'danger');
                }
            } finally {
                submitButton.disabled = false;
                submitText.textContent = 'Initiate Process';
                submitSpinner.style.display = 'none';
            }
        }

        // Exit Interview Form Submission
        async function submitExitInterviewForm(event) {
            event.preventDefault();
            const form = document.getElementById('AddExitInterviewForm');
            const formData = new FormData(form);

            const data = {
                action: 'AddExitInterview',
                emp_code: formData.get('emp_code'),
                interviewer: formData.get('interviewer'),
                reasons: formData.get('reasons'),
                feedback: formData.get('feedback')
            };

            const result = await handleFormSubmission(data, 'test.php');
            if (result.code === 200) {
                showToast(result.message, 'success');
                form.reset();
                $('.select2-employee').val(null).trigger('change');
            } else {
                showToast(result.message, 'danger');
            }
        }

        // Asset Management Form Submission
        async function submitAsset(event) {
            event.preventDefault();
            const form = document.getElementById('UpdateAssetStatusForm');
            const formData = new FormData(form);

            const data = {
                action: 'UpdateAssetStatus',
                emp_code: formData.get('emp_code'),
                asset_type: formData.get('asset_type'),
                status: formData.get('status'),
                notes: formData.get('notes'),
                return_date: formData.get('return_date')
            };

            const result = await handleFormSubmission(data, 'test.php');
            if (result.code === 200) {
                showToast(result.message, 'success');
                form.reset();
                $('.select2-employee').val(null).trigger('change');
            } else {
                showToast(result.message, 'danger');
            }
        }

        // Complete Offboarding Form Submission
        async function submitCompleteOffboardingForm(event) {
            event.preventDefault();
            const form = document.getElementById('completeOffboardingForm');
            const formData = new FormData(form);

            if (!confirm('Are you sure you want to complete the offboarding process? This action cannot be undone.')) {
                return;
            }

            const data = {
                action: 'CompleteOffboarding',
                emp_code: formData.get('emp_code')
            };

            const result = await handleFormSubmission(data, 'test.php');
            if (result.code === 200) {
                showToast(result.message, 'success');
                form.reset();
                $('.select2-employee').val(null).trigger('change');
            } else {
                showToast(result.message, 'danger');
            }
        }

        function loadOffboardingStatus() {
    event.preventDefault(); // Prevent page reload

    // Create a FormData object to gather form input
    const form = document.getElementById("completeOffboardingForm");
    const formData = new FormData(form);

    // Make an AJAX request to fetch offboarding status
    const data = {
        action:'LoadStatus',
        emp_code:formData.get('emp_code'),
    }
    fetch('task_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
    })
        .then(response => response.json())
        .then(data => {
            const statusDetails = document.getElementById('statusDetails');
            statusDetails.innerHTML = ''; // Clear previous content

            if (data.success) {
                displayOffboardingDetails(formData.get('emp_code'), data.details);
            } else {
                statusDetails.innerHTML = `<p class="text-danger">${data.message}</p>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while fetching offboarding status.');
        });
        }
        
        // Error Handler
        window.onerror = function(msg, url, lineNo, columnNo, error) {
            console.error('Error: ' + msg + '\nURL: ' + url + '\nLine: ' + lineNo + '\nColumn: ' + columnNo + '\nError object: ' + JSON.stringify(error));
            showToast('An unexpected error occurred. Please try again.', 'danger');
            return false;
        };
    </script>
</body>
</html>