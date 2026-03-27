<?php
// To install dependencies open XAMPP Shell and run:
// cd C:\xampp\htdocs\bocra-website\backend
// composer install

// BOCRA-Website — Email Helper

use PHPMailer\PHPMailer\PHPMailer;

function sendMail(
  string $toEmail,
  string $toName,
  string $subject,
  string $htmlBody,
  string $plainBody = ''
): bool {
  require_once __DIR__ .
    '/../vendor/autoload.php';
  require_once __DIR__ .
    '/../config/config.php';

  $mail = new PHPMailer(true);
  try {
    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USERNAME;
    $mail->Password   = MAIL_PASSWORD;
    $mail->SMTPSecure =
      PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = MAIL_PORT;
    $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
    $mail->addAddress($toEmail, $toName);
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $htmlBody;
    $mail->AltBody = $plainBody ?:
      strip_tags($htmlBody);
    $mail->send();
    return true;
  } catch (Throwable $e) {
    error_log(
      'BOCRA-Website mail error: ' .
      $e->getMessage()
    );
    return false;
  }
}

function emailApplicationSubmitted(
  array $user,
  array $application
): bool {
  $name = htmlspecialchars(
    $user['first_name'] . ' ' . $user['last_name']
  );
  $ref  = htmlspecialchars($application['reference']);
  $type = htmlspecialchars($application['license_type']);
  $date = date('d F Y');

  $html = <<<HTML
  <!DOCTYPE html>
  <html>
  <head>
  <meta charset="UTF-8">
  <style>
    body{font-family:Arial,sans-serif;
         color:#2C2C2C;margin:0;padding:0}
    .wrap{max-width:600px;margin:0 auto;
          padding:32px 16px}
    .header{background:#004D43;
            padding:24px 32px;
            border-radius:10px 10px 0 0}
    .header h1{color:#fff;font-size:18px;
               margin:0;font-weight:400}
    .header p{color:rgba(255,255,255,.6);
              margin:4px 0 0;font-size:12px}
    .body{background:#fff;padding:28px 32px;
          border:1px solid #DDD;border-top:none}
    .ref-box{background:#E8F4F2;
             border:1px solid rgba(0,107,94,.2);
             border-radius:8px;padding:16px;
             text-align:center;margin:18px 0}
    .ref-label{font-size:10px;color:#555;
               text-transform:uppercase;
               letter-spacing:1px}
    .ref-num{font-size:20px;font-weight:900;
             color:#004D43;letter-spacing:2px;
             margin-top:4px}
    .next{background:#F7F7F5;border-radius:8px;
          padding:16px;margin-top:18px}
    .next h3{font-size:14px;margin-bottom:8px;
             color:#2C2C2C}
    .next li{font-size:12px;color:#555;
             line-height:1.7;margin-bottom:4px}
    .footer{padding:16px 32px;
            background:#F7F7F5;
            border:1px solid #DDD;
            border-top:none;
            border-radius:0 0 10px 10px;
            font-size:11px;color:#888;
            text-align:center}
    p{font-size:13px;line-height:1.7}
  </style>
  </head>
  <body>
  <div class="wrap">
    <div class="header">
      <h1>BOCRA Website</h1>
      <p>Botswana Communications Regulatory Authority
         — Licensing Portal</p>
    </div>
    <div class="body">
      <p>Dear {$name},</p>
      <p>Your license application has been successfully
         submitted and is now under review by BOCRA's
         licensing team.</p>
      <div class="ref-box">
        <div class="ref-label">Application Reference</div>
        <div class="ref-num">{$ref}</div>
      </div>
      <p><strong>License Type:</strong> {$type}<br>
         <strong>Date Submitted:</strong> {$date}</p>
      <div class="next">
        <h3>What happens next?</h3>
        <ul>
          <li>BOCRA will acknowledge your application
              within 2 business days</li>
          <li>A technical officer will be assigned
              to review your submission</li>
          <li>You will be notified if additional
              documents are required</li>
          <li>A final decision will be communicated
              within 30 business days</li>
        </ul>
      </div>
      <p style="margin-top:18px">Track your application
         by logging in to BOCRA Website Licensing
         Portal at any time.</p>
      <p>For queries quote your reference number and
         contact us at
         <a href="mailto:licensing@bocra.org.bw"
            style="color:#006B5E">
         licensing@bocra.org.bw</a>.</p>
    </div>
    <div class="footer">
      &copy; 2026 Botswana Communications Regulatory
      Authority &middot; Automated message —
      do not reply directly.
    </div>
  </div>
  </body>
  </html>
HTML;

  return sendMail(
    $user['email'],
    $name,
    'Application Submitted — ' . $ref .
    ' — BOCRA Website',
    $html
  );
}
