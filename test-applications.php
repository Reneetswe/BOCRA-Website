<?php
/**
 * TEST PAGE - View All Applications in Database
 * This will show you exactly what's stored
 */

require_once __DIR__ . '/backend/config.php';

// Get ALL applications from database
$sql = "SELECT * FROM license_applications ORDER BY submission_date DESC";
$all_applications = dbQuery($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test - View All Applications</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 2rem;
            background: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            color: #006B5E;
            margin-bottom: 1rem;
        }
        .count {
            background: #E8F4F2;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 2rem;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #006B5E;
            color: white;
            font-weight: bold;
        }
        tr:hover {
            background: #f9f9f9;
        }
        .status {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .status-submitted {
            background: #FDF6E3;
            color: #C9A227;
        }
        .status-under_review {
            background: #E3F2FD;
            color: #1976D2;
        }
        .status-approved {
            background: #E8F5E9;
            color: #4CAF50;
        }
        .status-rejected {
            background: #FFEBEE;
            color: #D4415E;
        }
        .test-form {
            background: #FDF6E3;
            padding: 1.5rem;
            border-radius: 4px;
            margin-bottom: 2rem;
        }
        .test-form h3 {
            margin-top: 0;
            color: #C9A227;
        }
        .test-form input {
            padding: 0.5rem;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-right: 0.5rem;
        }
        .test-form button {
            padding: 0.5rem 1rem;
            background: #006B5E;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
        }
        .test-form button:hover {
            background: #004D43;
        }
        .result {
            margin-top: 1rem;
            padding: 1rem;
            background: white;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        pre {
            background: #f5f5f5;
            padding: 1rem;
            border-radius: 4px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Database Test - All License Applications</h1>
        
        <div class="count">
            Total Applications in Database: <strong><?php echo count($all_applications); ?></strong>
        </div>

        <!-- Test API Call -->
        <div class="test-form">
            <h3>Test API Call by Email</h3>
            <p>Enter your email to test if the API returns your applications:</p>
            <input type="email" id="testEmail" placeholder="your.email@example.com">
            <button onclick="testAPI()">Test API</button>
            <div id="apiResult" class="result" style="display: none;">
                <h4>API Response:</h4>
                <pre id="apiData"></pre>
            </div>
        </div>

        <?php if (count($all_applications) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Application #</th>
                        <th>Applicant Name</th>
                        <th>Email</th>
                        <th>License Type</th>
                        <th>Status</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_applications as $app): ?>
                        <tr>
                            <td><?php echo $app['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($app['application_number']); ?></strong></td>
                            <td><?php echo htmlspecialchars($app['applicant_name']); ?></td>
                            <td><?php echo htmlspecialchars($app['applicant_email']); ?></td>
                            <td><?php echo htmlspecialchars($app['license_type']); ?></td>
                            <td>
                                <span class="status status-<?php echo $app['status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $app['status'])); ?>
                                </span>
                            </td>
                            <td><?php echo date('d M Y', strtotime($app['submission_date'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="text-align: center; padding: 3rem; color: #888;">
                <h3>No Applications Found</h3>
                <p>The database is empty. Submit a license application first.</p>
                <a href="application.html?type=Broadcasting%20Licence" style="display: inline-block; margin-top: 1rem; padding: 0.75rem 1.5rem; background: #006B5E; color: white; text-decoration: none; border-radius: 4px; font-weight: 600;">
                    Submit Test Application
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        async function testAPI() {
            const email = document.getElementById('testEmail').value;
            if (!email) {
                alert('Please enter an email address');
                return;
            }

            const resultDiv = document.getElementById('apiResult');
            const dataDiv = document.getElementById('apiData');
            
            try {
                const response = await fetch(`api/get-my-applications.php?email=${encodeURIComponent(email)}`);
                const data = await response.json();
                
                resultDiv.style.display = 'block';
                dataDiv.textContent = JSON.stringify(data, null, 2);
                
                if (data.success) {
                    alert(`Found ${data.applications.length} applications for ${email}`);
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                resultDiv.style.display = 'block';
                dataDiv.textContent = 'Error: ' + error.message;
                alert('API Error: ' + error.message);
            }
        }
    </script>
</body>
</html>
