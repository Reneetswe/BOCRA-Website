<?php
require_once __DIR__ . '/../backend/config.php';
requireRegistrar();
$user = getCurrentUser();

$success = false;
$error = '';
$domainName = '';

// Get applicants for this registrar
$applicants = dbQuery("SELECT * FROM applicants WHERE registrar_id = ? ORDER BY created_at DESC", [$user['registrar_id']]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $domainName = strtolower(sanitize($_POST['domain_name']));
    $applicantId = (int)$_POST['applicant_id'];
    $category = sanitize($_POST['category']);
    $nameserver1 = sanitize($_POST['nameserver_1']);
    $nameserver2 = sanitize($_POST['nameserver_2']);
    $registrationTerm = (int)$_POST['registration_term'];
    $notes = sanitize($_POST['notes'] ?? '');
    
    // Validate domain name
    if (!preg_match('/^[a-z0-9-]+\.bw$/', $domainName)) {
        $error = 'Invalid domain name format. Must end with .bw';
    } else {
        // Check if domain exists
        $checkSql = "SELECT id FROM domains WHERE domain_name = ?";
        $existing = dbQuery($checkSql, [$domainName]);
        
        if (!empty($existing)) {
            $error = 'Domain already registered';
        } else {
            // Get applicant details
            $applicantSql = "SELECT * FROM applicants WHERE id = ? AND registrar_id = ?";
            $applicant = dbQuery($applicantSql, [$applicantId, $user['registrar_id']])[0] ?? null;
            
            if (!$applicant) {
                $error = 'Invalid applicant selected';
            } else {
                // Calculate dates
                $registrationDate = date('Y-m-d');
                $expiryDate = date('Y-m-d', strtotime("+$registrationTerm year"));
                
                // Insert domain
                $sql = "INSERT INTO domains (domain_name, applicant_id, registrar_id, status, category, 
                        nameserver_1, nameserver_2, registration_date, expiry_date, registration_term, notes)
                        VALUES (?, ?, ?, 'active', ?, ?, ?, ?, ?, ?, ?)";
                
                if (dbExecute($sql, [$domainName, $applicantId, $user['registrar_id'], $category, 
                                     $nameserver1, $nameserver2, $registrationDate, $expiryDate, $registrationTerm, $notes])) {
                    $domainId = dbLastInsertId();
                    
                    // Create domain application
                    $appSql = "INSERT INTO domain_applications (domain_id, applicant_id, registrar_id, submission_status, notes)
                               VALUES (?, ?, ?, 'submitted', ?)";
                    dbExecute($appSql, [$domainId, $applicantId, $user['registrar_id'], $notes]);
                    
                    // Log actions
                    $applicantName = $applicant['type'] === 'company' ? $applicant['company_name'] : $applicant['full_name'];
                    logAudit($user['name'], 'registrar', 'domain_registered', $domainName, $applicantName, 
                             "Domain registration submitted for $registrationTerm year(s)");
                    logAudit('BOCRA System', 'system', 'application_received', $domainName, $applicantName, 
                             "Application received from {$user['registrar_name']}");
                    
                    $success = true;
                } else {
                    $error = 'Failed to register domain. Please try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Domain - BOCRA Registrar</title>
    <link href="https://fonts.googleapis.com/css2?family=Forum&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/portal-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="top-bar">
                <div>
                    <h1>Register Domain</h1>
                    <p>Submit a new domain registration</p>
                </div>
            </div>
            
            <div class="content-area">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Domain <strong><?php echo htmlspecialchars($domainName); ?></strong> registered successfully! 
                        The submission has been sent to BOCRA for oversight.
                        <div style="margin-top: 1rem;">
                            <a href="domain-list.php" class="btn btn-sm btn-success">View Domain List</a>
                            <a href="register-domain.php" class="btn btn-sm btn-outline">Register Another</a>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (empty($applicants)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> No applicants found. 
                        <a href="new-applicant.php" style="color: inherit; text-decoration: underline;">Create an applicant</a> first.
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Domain Registration Form</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="form-group">
                                    <label for="applicant_id" class="form-label">Select Applicant *</label>
                                    <select id="applicant_id" name="applicant_id" class="form-select" required>
                                        <option value="">Choose an applicant...</option>
                                        <?php foreach ($applicants as $applicant): ?>
                                            <option value="<?php echo $applicant['id']; ?>">
                                                <?php echo htmlspecialchars($applicant['type'] === 'company' ? $applicant['company_name'] : $applicant['full_name']); ?>
                                                (<?php echo htmlspecialchars($applicant['email']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="domain_name" class="form-label">Domain Name *</label>
                                    <input type="text" id="domain_name" name="domain_name" class="form-control" 
                                           placeholder="example.bw" required pattern="[a-z0-9-]+\.bw">
                                    <small class="form-text">Must end with .bw (lowercase letters, numbers, and hyphens only)</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="category" class="form-label">Domain Category *</label>
                                    <select id="category" name="category" class="form-select" required>
                                        <option value="commercial">Commercial</option>
                                        <option value="government">Government</option>
                                        <option value="educational">Educational</option>
                                        <option value="non-profit">Non-Profit</option>
                                        <option value="personal">Personal</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="nameserver_1" class="form-label">Primary Nameserver *</label>
                                    <input type="text" id="nameserver_1" name="nameserver_1" class="form-control" 
                                           placeholder="ns1.example.bw" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="nameserver_2" class="form-label">Secondary Nameserver *</label>
                                    <input type="text" id="nameserver_2" name="nameserver_2" class="form-control" 
                                           placeholder="ns2.example.bw" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="registration_term" class="form-label">Registration Term *</label>
                                    <select id="registration_term" name="registration_term" class="form-select" required>
                                        <option value="1">1 Year</option>
                                        <option value="2">2 Years</option>
                                        <option value="3">3 Years</option>
                                        <option value="5">5 Years</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea id="notes" name="notes" class="form-control" rows="3" 
                                              placeholder="Additional information or special requirements"></textarea>
                                </div>
                                
                                <div class="d-flex gap-2 justify-between">
                                    <a href="dashboard.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-paper-plane"></i> Submit Registration
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
