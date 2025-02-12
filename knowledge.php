<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Knowledge Transfer - Offboarding</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
</head>
<style>
    body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 20px;
}

.container {
    max-width: 600px;
    margin: auto;
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

h1 {
    text-align: center;
    color: #333;
}

.form-group {
    margin-bottom: 15px;
}

label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

input[type="text"], textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
}

button {
    width: 100%;
    padding: 10px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}

button:hover {
    background-color: #45a049;
}

.hidden {
    display: none;
}

#confirmationMessage {
    margin-top: 20px;
    text-align: center;
    color: green;
}
</style>
<body>
    <div class="container">
        <h1>Knowledge Transfer</h1>
        <form id="knowledgeTransferForm">
            <div class="form-group">
                <label for="employeeName">Employee Name:</label>
                <input type="text" id="employeeName" required>
            </div>
            <div class="form-group">
                <label for="role">Employee code:</label>
                <input type="text" id="emp_code" required>
            </div>
            <div class="form-group">
                <label for="role">Role:</label>
                <input type="text" id="role" required>
            </div>
            <div class="form-group">
                <label for="knowledgeAreas">Knowledge Areas:</label>
                <textarea id="knowledgeAreas" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="importantContacts">Important Contacts:</label>
                <textarea id="importantContacts" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="handoverNotes">Handover Notes:</label>
                <textarea id="handoverNotes" rows="4" required></textarea>
            </div>
            <button type="submit">Submit Knowledge Transfer</button>
        </form>
        <div id="confirmationMessage" class="hidden"></div>
    </div>
    <script src="script.js"></script>
</body>
<script>
    document.getElementById('knowledgeTransferForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent the default form submission

    // Gather form data
    const employeeName = document.getElementById('employeeName').value;
    const role = document.getElementById('role').value;
    const knowledgeAreas = document.getElementById('knowledgeAreas').value;
    const importantContacts = document.getElementById('importantContacts').value;
    const handoverNotes = document.getElementById('handoverNotes').value;

    // Here you can send the data to your server or process it as needed
    console.log({
        employeeName,
        role,
        knowledgeAreas,
        importantContacts,
        handoverNotes
    });

    // Show confirmation message
    const confirmationMessage = document.getElementById('confirmationMessage');
    confirmationMessage.textContent = 'Knowledge transfer submitted successfully!';
    confirmationMessage.classList.remove('hidden');

    // Clear the form
    document.getElementById('knowledgeTransferForm').reset();
});
</script>
</html>