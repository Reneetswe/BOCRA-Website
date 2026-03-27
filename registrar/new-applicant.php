<?php
require_once __DIR__ . '/../backend/config.php';
requireRegistrar();
$user = getCurrentUser();

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = sanitize($_POST['type']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    
    // Type-specific fields
    $fullName = $type === 'individual' ? sanitize($_POST['full_name']) : null;
    $companyName = $type === 'company' ? sanitize($_POST['company_name']) : null;
    $nationalId = sanitize($_POST['national_id'] ?? '');
    $registrationNumber = sanitize($_POST['registration_number'] ?? '');
    $taxNumber = sanitize($_POST['tax_number'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $contactPerson = $type === 'company' ? sanitize($_POST['contact_person'] ?? '') : null;
    
    // Insert applicant
    $sql = "INSERT INTO applicants (registrar_id, type, full_name, company_name, national_id, 
            registration_number, tax_number, email, phone, address, contact_person)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    if (dbExecute($sql, [$user['registrar_id'], $type, $fullName, $companyName, $nationalId, 
                         $registrationNumber, $taxNumber, $email, $phone, $address, $contactPerson])) {
        $applicantId = dbLastInsertId();
        
        // Log action
        $applicantName = $type === 'company' ? $companyName : $fullName;
        logAudit($user['name'], 'registrar', 'applicant_created', null, $applicantName, 
                 "New $type applicant registered");
        
        // Check for compliance flags
        if (empty($taxNumber)) {
            $flagSql = "INSERT INTO compliance_flags (applicant_id, flag_type, severity, status, note)
                       VALUES (?, 'missing_tax_number', 'medium', 'open', 'Tax number not provided during registration')";
            dbExecute($flagSql, [$applicantId]);
        }
        
        $success = true;
    } else {
        $error = 'Failed to create applicant. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Applicant - BOCRA Registrar</title>
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
                    <h1>New Applicant</h1>
                    <p>Register a new domain applicant</p>
                </div>
            </div>
            
            <div class="content-area">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Applicant created successfully! 
                        <a href="register-domain.php" style="color: inherit; text-decoration: underline;">Register a domain</a> or 
                        <a href="new-applicant.php" style="color: inherit; text-decoration: underline;">add another applicant</a>.
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Applicant Information</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="form-group">
                                <label class="form-label">Applicant Type *</label>
                                <div style="display: flex; gap: 2rem;">
                                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                        <input type="radio" name="type" value="individual" required onchange="toggleFields()">
                                        <span>Individual</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                        <input type="radio" name="type" value="company" required onchange="toggleFields()">
                                        <span>Company</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div id="individual-fields" style="display: none;">
                                <div class="form-group">
                                    <label for="full_name" class="form-label">Full Name *</label>
                                    <input type="text" id="full_name" name="full_name" class="form-control">
                                </div>
                                
                                <div class="form-group">
                                    <label for="national_id" class="form-label">National ID / Passport Number</label>
                                    <input type="text" id="national_id" name="national_id" class="form-control" placeholder="e.g., 123456789">
                                </div>
                            </div>
                            
                            <div id="company-fields" style="display: none;">
                                <div class="form-group">
                                    <label for="company_name" class="form-label">Company Name *</label>
                                    <input type="text" id="company_name" name="company_name" class="form-control">
                                </div>
                                
                                <div class="form-group">
                                    <label for="registration_number" class="form-label">Company Registration Number</label>
                                    <input type="text" id="registration_number" name="registration_number" class="form-control" placeholder="e.g., BW00123456789">
                                </div>
                                
                                <div class="form-group">
                                    <label for="contact_person" class="form-label">Contact Person</label>
                                    <input type="text" id="contact_person" name="contact_person" class="form-control">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="tax_number" class="form-label">Tax Number</label>
                                <input type="text" id="tax_number" name="tax_number" class="form-control" placeholder="e.g., C12345678">
                                <small class="form-text">Required for compliance. Missing tax number will be flagged.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" id="email" name="email" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="text" id="phone" name="phone" class="form-control" placeholder="+267 71234567" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="address" class="form-label">Physical Address</label>
                                <textarea id="address" name="address" class="form-control" rows="3"></textarea>
                            </div>
                            
                            <div class="d-flex gap-2 justify-between">
                                <a href="dashboard.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-save"></i> Create Applicant
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        function toggleFields() {
            const type = document.querySelector('input[name="type"]:checked').value;
            const individualFields = document.getElementById('individual-fields');
            const companyFields = document.getElementById('company-fields');
            
            if (type === 'individual') {
                individualFields.style.display = 'block';
                companyFields.style.display = 'none';
                document.getElementById('full_name').required = true;
                document.getElementById('company_name').required = false;
            } else {
                individualFields.style.display = 'none';
                companyFields.style.display = 'block';
                document.getElementById('full_name').required = false;
                document.getElementById('company_name').required = true;
            }
        }
    </script>
</body>
</html>
