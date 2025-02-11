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
    <title>Enterprise Offboarding Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css" rel="stylesheet">
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
        }

        .portal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .portal-title {
            font-weight: 300;
            letter-spacing: 0.5px;
        }

        .portal-subtitle {
            opacity: 0.8;
            font-size: 1rem;
        }

        .section-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
            border: none;
            transition: transform 0.2s;
        }

        .section-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .card-header {
            border-radius: 8px 8px 0 0 !important;
            padding: 1rem 1.5rem;
        }

        .card-header h3 {
            font-size: 1.25rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .form-label {
            font-weight: 500;
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select, .select2-container--default .select2-selection--single {
            border-radius: 6px;
            border: 1px solid #dee2e6;
            padding: 0.625rem;
            height: auto;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .btn {
            padding: 0.625rem 1.25rem;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }

        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }

        .btn-warning {
            background-color: var(--warning-color);
            border-color: var(--warning-color);
        }

        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }

        .status-badge {
            padding: 0.375rem 0.75rem;
            border-radius: 50rem;
            font-size: 0.875rem;
            font-weight: 500;
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

        .select2-container {
            width: 100% !important;
        }

        .alert {
            border-radius: 6px;
            padding: 1rem;
            margin-top: 1rem;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .progress {
            height: 8px;
            border-radius: 4px;
            margin: 1rem 0;
        }

        /* Custom animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }
    </style>
</head>
<body>
    <!-- Portal Header -->
    <header class="portal-header">
        <div class="container">
            <h1 class="portal-title">Enterprise Offboarding Portal</h1>
            <p class="portal-subtitle">Streamline your employee offboarding process</p>
        </div>
    </header>

    <div class="container">
        <!-- Initiate Offboarding Section -->
        <div class="section-card">
            <div class="card-header bg-primary text-white">
                <h3><i class="fas fa-play-circle"></i> Initiate Offboarding</h3>
            </div>
            <div class="card-body">
                <form id="initiateOffboardingForm" onsubmit="submitForm(event)">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label">Employee Code</label>
                            <select class="form-select select2-employee" name="emp_code" required>
                                <option value="" disabled selected>Select Employee Code</option>
                                <?php foreach ($offboardingRecords as $employee): ?>
                                    <option value='<?php echo $employee['emp_code']; ?>'>
                                        <?php echo $employee['emp_code'] . ' - ' . $employee['first_name'] . ' ' . $employee['last_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Exit Type</label>
                            <select class="form-select" name="exit_type" required>
                                <option value="" disabled selected>Select Exit Type</option>
                                <option value="resignation">Resignation</option>
                                <option value="retirement">Retirement</option>
                                <option value="termination">Termination</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Working Day</label>
                            <input type="date" class="form-control" name="last_working_day" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary" id="submitButton">
                                <i class="fas fa-check-circle me-2"></i>
                                <span id="submitText">Initiate Process</span>
                                <span id="submitSpinner" class="spinner-border spinner-border-sm ms-2" role="status" style="display: none;"></span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Exit Interview Section -->
        <div class="section-card">
            <div class="card-header bg-info text-white">
                <h3><i class="fas fa-comments"></i> Exit Interview</h3>
            </div>
            <div class="card-body">
                <form id="AddExitInterviewForm" onsubmit="submitExitInterviewForm(event)">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">Employee Code</label>
                            <select class="form-select select2-employee" name="emp_code" required>
                                <?php foreach ($offboardingRecords as $employee): ?>
                                    <option value='<?php echo $employee['emp_code']; ?>'>
                                        <?php echo $employee['emp_code']; ?>
                                    </option>
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
                        <div class="col-12">
                            <label class="form-label">Feedback</label>
                            <textarea class="form-control" name="feedback" rows="3" required></textarea>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-info" type="submit">
                                <i class="fas fa-paper-plane me-2"></i>Submit Interview
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Asset Management Section -->
        <div class="section-card">
            <div class="card-header bg-warning">
                <h3><i class="fas fa-laptop"></i> Asset Management</h3>
            </div>
            <div class="card-body">
                <form id="UpdateAssetStatusForm" onsubmit="submitAsset(event)">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label">Employee Code</label>
                            <select class="form-select select2-employee" name="emp_code" required>
                                <?php foreach ($offboardingRecords as $employee): ?>
                                    <option value='<?php echo $employee['emp_code']; ?>'>
                                        <?php echo $employee['emp_code']; ?>
                                    </option>
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
                            <button class="btn btn-warning" type="submit">
                                <i class="fas fa-sync-alt me-2"></i>Update Asset
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Complete Offboarding Section -->
        <div class="section-card">
            <div class="card-header bg-danger text-white">
                <h3><i class="fas fa-check-double"></i> Complete Offboarding</h3>
            </div>
            <div class="card-body">
                <form id="completeOffboardingForm" onsubmit="submitCompleteOffboardingForm(event)">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">Employee Code</label>
                            <select class="form-select select2-employee" name="emp_code" required>
                                <?php foreach ($offboardingRecords as $employee): ?>
                                    <option value='<?php echo $employee['emp_code']; ?>'>
                                        <?php echo $employee['emp_code']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-danger" type="submit">
                                <i class="fas fa-flag-checkered me-2"></i>Finalize Offboarding
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loading">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
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
            
            submitButton.disabled = true;
            submitText.textContent = 'Processing...';
            submitSpinner.style.display = 'inline-block';

            const data = {
                action: 'InitiateOffboarding',
                emp_code: formData.get('emp_code'),
                exit_type: formData.get('exit_type'),
                last_working_day: formData.get('last_working_day')
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

        // Error Handler
        window.onerror = function(msg, url, lineNo, columnNo, error) {
            console.error('Error: ' + msg + '\nURL: ' + url + '\nLine: ' + lineNo + '\nColumn: ' + columnNo + '\nError object: ' + JSON.stringify(error));
            showToast('An unexpected error occurred. Please try again.', 'danger');
            return false;
        };
    </script>
</body>
</html>