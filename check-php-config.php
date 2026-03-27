<?php
/**
 * Check PHP Configuration and Error Logging
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>PHP Configuration Check</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 2rem; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 2rem; border-radius: 8px; }
        h1 { color: #006B5E; }
        .info-box { background: #E8F4F2; padding: 1rem; border-radius: 4px; margin: 1rem 0; }
        .error-box { background: #FFEBEE; padding: 1rem; border-radius: 4px; margin: 1rem 0; border-left: 4px solid #D4415E; }
        .success-box { background: #E8F5E9; padding: 1rem; border-radius: 4px; margin: 1rem 0; border-left: 4px solid #4CAF50; }
        table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
        th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #006B5E; color: white; }
        code { background: #f5f5f5; padding: 0.25rem 0.5rem; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 PHP Configuration Check</h1>

        <div class="info-box">
            <strong>PHP Version:</strong> <?php echo PHP_VERSION; ?>
        </div>

        <h2>Error Logging Configuration</h2>
        <table>
            <tr>
                <th>Setting</th>
                <th>Value</th>
            </tr>
            <tr>
                <td>display_errors</td>
                <td><code><?php echo ini_get('display_errors') ? 'On' : 'Off'; ?></code></td>
            </tr>
            <tr>
                <td>log_errors</td>
                <td><code><?php echo ini_get('log_errors') ? 'On' : 'Off'; ?></code></td>
            </tr>
            <tr>
                <td>error_log</td>
                <td><code><?php echo ini_get('error_log') ?: 'Not set (using default)'; ?></code></td>
            </tr>
            <tr>
                <td>error_reporting</td>
                <td><code><?php echo error_reporting(); ?></code></td>
            </tr>
        </table>

        <h2>Database Connection Test</h2>
        <?php
        try {
            require_once __DIR__ . '/backend/config.php';
            $db = getDB();
            echo '<div class="success-box">✅ <strong>Database connection successful!</strong></div>';
            
            // Test if license_applications table exists
            $stmt = $db->query("SHOW TABLES LIKE 'license_applications'");
            $table_exists = $stmt->fetch();
            
            if ($table_exists) {
                echo '<div class="success-box">✅ <strong>Table "license_applications" exists</strong></div>';
                
                // Count applications
                $stmt = $db->query("SELECT COUNT(*) as count FROM license_applications");
                $count = $stmt->fetch()['count'];
                echo '<div class="info-box"><strong>Total applications in database:</strong> ' . $count . '</div>';
            } else {
                echo '<div class="error-box">❌ <strong>Table "license_applications" does NOT exist!</strong><br>You need to import the database schema.</div>';
            }
        } catch (Exception $e) {
            echo '<div class="error-box">❌ <strong>Database connection failed:</strong><br>' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>

        <h2>Test Direct Insert</h2>
        <?php
        if (isset($_POST['test_insert'])) {
            try {
                require_once __DIR__ . '/backend/config.php';
                
                $app_number = 'TEST-' . date('YmdHis');
                $sql = "INSERT INTO license_applications (
                    application_number, applicant_name, applicant_email, applicant_phone,
                    license_type, business_type, physical_address, business_description, 
                    proposed_services, status, submission_date
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'submitted', NOW())";
                
                dbQuery($sql, [
                    $app_number,
                    'Test User',
                    'test@example.com',
                    '+267 71234567',
                    'Test License',
                    'individual',
                    'Test Address',
                    'Test Description',
                    'Test Services'
                ]);
                
                $id = getDB()->lastInsertId();
                
                echo '<div class="success-box">✅ <strong>Test insert successful!</strong><br>Application Number: ' . $app_number . '<br>ID: ' . $id . '</div>';
            } catch (Exception $e) {
                echo '<div class="error-box">❌ <strong>Test insert failed:</strong><br>' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        }
        ?>

        <form method="POST">
            <button type="submit" name="test_insert" style="padding: 1rem 2rem; background: #006B5E; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">
                Run Test Insert
            </button>
        </form>

        <h2>File Permissions Check</h2>
        <?php
        $api_file = __DIR__ . '/api/submit-license-application.php';
        if (file_exists($api_file)) {
            echo '<div class="success-box">✅ API file exists: <code>' . $api_file . '</code></div>';
            if (is_readable($api_file)) {
                echo '<div class="success-box">✅ API file is readable</div>';
            } else {
                echo '<div class="error-box">❌ API file is NOT readable</div>';
            }
        } else {
            echo '<div class="error-box">❌ API file does NOT exist: <code>' . $api_file . '</code></div>';
        }
        ?>

        <h2>Next Steps</h2>
        <div class="info-box">
            <ol>
                <li>If database connection failed → Import <code>database/schema_extended.sql</code></li>
                <li>If test insert works → Try submitting from <a href="test-submit.html">test-submit.html</a></li>
                <li>Check browser console (F12) for JavaScript errors</li>
                <li>Check Network tab in browser to see API request/response</li>
            </ol>
        </div>
    </div>
</body>
</html>
