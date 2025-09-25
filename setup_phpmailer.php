<?php
// This script will help set up PHPMailer

// Check if Composer is installed
echo "Checking for Composer...\n";
$composer_check = shell_exec('composer --version');

if (strpos($composer_check, 'Composer version') === false) {
    echo "Composer not found. Please install Composer first.\n";
    echo "Visit https://getcomposer.org/download/ for installation instructions.\n";
    exit(1);
}

echo "Composer found. Installing PHPMailer...\n";

// Create composer.json if it doesn't exist
if (!file_exists('composer.json')) {
    $composer_json = [
        'require' => [
            'phpmailer/phpmailer' => '^6.8'
        ]
    ];
    
    file_put_contents('composer.json', json_encode($composer_json, JSON_PRETTY_PRINT));
    echo "Created composer.json file.\n";
}

// Install PHPMailer
$install_result = shell_exec('composer require phpmailer/phpmailer');
echo $install_result;

echo "\nPHPMailer installation complete.\n";
echo "Now creating mail configuration file...\n";

// Create mail configuration file
$mail_config = <<<'EOT'
<?php
// Mail Configuration
// Rename this file to mail_config.php and update with your SMTP settings

// SMTP Configuration
define('SMTP_HOST', 'smtp.example.com'); // Your SMTP server
define('SMTP_PORT', 587);               // SMTP port (usually 587 for TLS, 465 for SSL)
define('SMTP_USERNAME', 'your_email@example.com'); // SMTP username
define('SMTP_PASSWORD', 'your_password');         // SMTP password
define('SMTP_SECURE', 'tls');           // Enable TLS encryption, `ssl` also accepted
define('SMTP_AUTH', true);              // Enable SMTP authentication
define('MAIL_FROM', 'your_email@example.com');    // Sender email address
define('MAIL_FROM_NAME', 'SRKR Engineering College'); // Sender name

// HOD Email for notifications
define('HOD_EMAIL', 'hod_email@example.com'); // HOD email to receive notifications

// Debug level (0 = off, 1 = client messages, 2 = client and server messages)
define('SMTP_DEBUG', 0);

// Character set
define('MAIL_CHARSET', 'UTF-8');

// Email templates directory
define('EMAIL_TEMPLATES_DIR', __DIR__ . '/email_templates/');

// Create email templates directory if it doesn't exist
if (!file_exists(EMAIL_TEMPLATES_DIR)) {
    mkdir(EMAIL_TEMPLATES_DIR, 0755, true);
}
EOT;

file_put_contents('mail_config.php.dummy', $mail_config);
echo "Created mail_config.php.dummy file. Rename to mail_config.php and update with your SMTP settings.\n";

// Create email templates directory
if (!file_exists('email_templates')) {
    mkdir('email_templates', 0755, true);
    echo "Created email_templates directory.\n";
}

// Create student notification templates
$application_submitted_template = <<<'EOT'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Leave Application Submitted</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; }
        .header { background-color: #076593; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .footer { background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; }
        .details { background-color: #f9f9f9; padding: 15px; margin: 15px 0; border-left: 4px solid #076593; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Leave Application Submitted</h2>
    </div>
    <div class="content">
        <p>Dear {{STUDENT_NAME}},</p>
        <p>Your leave application has been successfully submitted. Here are the details:</p>
        
        <div class="details">
            <p><strong>Application ID:</strong> {{APPLICATION_ID}}</p>
            <p><strong>Register Number:</strong> {{REGISTER_NO}}</p>
            <p><strong>Leave Type:</strong> {{LEAVE_TYPE}}</p>
            <p><strong>Duration:</strong> {{START_DATE}} to {{END_DATE}} ({{TOTAL_DAYS}} days)</p>
            <p><strong>Status:</strong> Pending</p>
        </div>
        
        <p>Your application will be reviewed by the HOD. You will be notified once a decision has been made.</p>
        <p>If you have any questions, please contact the department office.</p>
        
        <p>Thank you,<br>SRKR Engineering College</p>
    </div>
    <div class="footer">
        <p>This is an automated message. Please do not reply to this email.</p>
    </div>
</body>
</html>
EOT;

$application_approved_template = <<<'EOT'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Leave Application Approved</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; }
        .header { background-color: #28a745; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .footer { background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; }
        .details { background-color: #f9f9f9; padding: 15px; margin: 15px 0; border-left: 4px solid #28a745; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Leave Application Approved</h2>
    </div>
    <div class="content">
        <p>Dear {{STUDENT_NAME}},</p>
        <p>We are pleased to inform you that your leave application has been <strong>approved</strong>. Here are the details:</p>
        
        <div class="details">
            <p><strong>Application ID:</strong> {{APPLICATION_ID}}</p>
            <p><strong>Register Number:</strong> {{REGISTER_NO}}</p>
            <p><strong>Leave Type:</strong> {{LEAVE_TYPE}}</p>
            <p><strong>Duration:</strong> {{START_DATE}} to {{END_DATE}} ({{TOTAL_DAYS}} days)</p>
            <p><strong>Status:</strong> Approved</p>
            <p><strong>HOD Remarks:</strong> {{HOD_REMARKS}}</p>
        </div>
        
        <p>If you have any questions, please contact the department office.</p>
        
        <p>Thank you,<br>SRKR Engineering College</p>
    </div>
    <div class="footer">
        <p>This is an automated message. Please do not reply to this email.</p>
    </div>
</body>
</html>
EOT;

$application_rejected_template = <<<'EOT'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Leave Application Rejected</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; }
        .header { background-color: #dc3545; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .footer { background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; }
        .details { background-color: #f9f9f9; padding: 15px; margin: 15px 0; border-left: 4px solid #dc3545; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Leave Application Rejected</h2>
    </div>
    <div class="content">
        <p>Dear {{STUDENT_NAME}},</p>
        <p>We regret to inform you that your leave application has been <strong>rejected</strong>. Here are the details:</p>
        
        <div class="details">
            <p><strong>Application ID:</strong> {{APPLICATION_ID}}</p>
            <p><strong>Register Number:</strong> {{REGISTER_NO}}</p>
            <p><strong>Leave Type:</strong> {{LEAVE_TYPE}}</p>
            <p><strong>Duration:</strong> {{START_DATE}} to {{END_DATE}} ({{TOTAL_DAYS}} days)</p>
            <p><strong>Status:</strong> Rejected</p>
            <p><strong>HOD Remarks:</strong> {{HOD_REMARKS}}</p>
        </div>
        
        <p>If you have any questions or would like to discuss this further, please contact the department office.</p>
        
        <p>Thank you,<br>SRKR Engineering College</p>
    </div>
    <div class="footer">
        <p>This is an automated message. Please do not reply to this email.</p>
    </div>
</body>
</html>
EOT;

$hod_notification_template = <<<'EOT'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Leave Application</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; }
        .header { background-color: #076593; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .footer { background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; }
        .details { background-color: #f9f9f9; padding: 15px; margin: 15px 0; border-left: 4px solid #076593; }
        .button { display: inline-block; background-color: #076593; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>New Leave Application</h2>
    </div>
    <div class="content">
        <p>Dear HOD,</p>
        <p>A new leave application has been submitted and requires your review. Here are the details:</p>
        
        <div class="details">
            <p><strong>Application ID:</strong> {{APPLICATION_ID}}</p>
            <p><strong>Student Name:</strong> {{STUDENT_NAME}}</p>
            <p><strong>Register Number:</strong> {{REGISTER_NO}}</p>
            <p><strong>Section:</strong> {{SECTION}}</p>
            <p><strong>Leave Type:</strong> {{LEAVE_TYPE}}</p>
            <p><strong>Duration:</strong> {{START_DATE}} to {{END_DATE}} ({{TOTAL_DAYS}} days)</p>
            <p><strong>Reason:</strong> {{REASON}}</p>
            <p><strong>Contact Number:</strong> {{CONTACT_NUMBER}}</p>
            <p><strong>Parent Contact:</strong> {{PARENT_CONTACT}}</p>
        </div>
        
        <p>Please login to the HOD portal to review this application.</p>
        
        <p><a href="{{PORTAL_URL}}" class="button">Go to HOD Portal</a></p>
        
        <p>Thank you,<br>SRKR Engineering College</p>
    </div>
    <div class="footer">
        <p>This is an automated message. Please do not reply to this email.</p>
    </div>
</body>
</html>
EOT;

// Save email templates
if (!file_exists('email_templates')) {
    mkdir('email_templates', 0755, true);
}

file_put_contents('email_templates/application_submitted.html', $application_submitted_template);
file_put_contents('email_templates/application_approved.html', $application_approved_template);
file_put_contents('email_templates/application_rejected.html', $application_rejected_template);
file_put_contents('email_templates/hod_notification.html', $hod_notification_template);

echo "Created email templates.\n";

// Create mail helper class
$mail_helper = <<<'EOT'
<?php
// Mail Helper Class

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'vendor/autoload.php';

if (file_exists('mail_config.php')) {
    require_once 'mail_config.php';
} else {
    die('Mail configuration file not found. Please create mail_config.php from the template.');
}

class MailHelper {
    private static function getMailer() {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->SMTPDebug = SMTP_DEBUG;
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = SMTP_AUTH;
            $mail->Username   = SMTP_USERNAME;
            $mail->Password   = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port       = SMTP_PORT;
            $mail->CharSet    = MAIL_CHARSET;
            
            // Default sender
            $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
            
            return $mail;
        } catch (Exception $e) {
            error_log("Error initializing mailer: {$mail->ErrorInfo}");
            return null;
        }
    }
    
    private static function loadTemplate($template_name, $replacements = []) {
        $template_path = EMAIL_TEMPLATES_DIR . $template_name . '.html';
        
        if (!file_exists($template_path)) {
            error_log("Email template not found: {$template_path}");
            return false;
        }
        
        $template = file_get_contents($template_path);
        
        // Replace placeholders
        foreach ($replacements as $key => $value) {
            $template = str_replace('{{'.$key.'}}', $value, $template);
        }
        
        return $template;
    }
    
    public static function sendMail($to, $subject, $template_name, $replacements = [], $cc = [], $bcc = []) {
        $mail = self::getMailer();
        
        if (!$mail) {
            return ['success' => false, 'message' => 'Failed to initialize mailer'];
        }
        
        try {
            // Load and process template
            $html_content = self::loadTemplate($template_name, $replacements);
            
            if (!$html_content) {
                return ['success' => false, 'message' => 'Failed to load email template'];
            }
            
            // Recipients
            $mail->addAddress($to);
            
            // Add CC recipients
            if (!empty($cc)) {
                foreach ($cc as $cc_address) {
                    $mail->addCC($cc_address);
                }
            }
            
            // Add BCC recipients
            if (!empty($bcc)) {
                foreach ($bcc as $bcc_address) {
                    $mail->addBCC($bcc_address);
                }
            }
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $html_content;
            
            // Create plain text version
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html_content));
            
            $mail->send();
            return ['success' => true, 'message' => 'Email sent successfully'];
        } catch (Exception $e) {
            error_log("Error sending email: {$mail->ErrorInfo}");
            return ['success' => false, 'message' => "Email could not be sent. Error: {$mail->ErrorInfo}"];
        }
    }
    
    // Helper function to send leave application notification to student
    public static function sendLeaveApplicationSubmitted($application) {
        $replacements = [
            'STUDENT_NAME' => $application['student_name'],
            'APPLICATION_ID' => $application['id'],
            'REGISTER_NO' => $application['register_no'],
            'LEAVE_TYPE' => ucfirst($application['leave_type']),
            'START_DATE' => date('d M Y', strtotime($application['start_date'])),
            'END_DATE' => date('d M Y', strtotime($application['end_date'])),
            'TOTAL_DAYS' => $application['total_days']
        ];
        
        return self::sendMail(
            $application['student_email'],
            'Leave Application Submitted - SRKR Engineering College',
            'application_submitted',
            $replacements
        );
    }
    
    // Helper function to send leave application status notification to student
    public static function sendLeaveApplicationStatus($application) {
        $template = $application['status'] === 'approved' ? 'application_approved' : 'application_rejected';
        $subject = 'Leave Application ' . ucfirst($application['status']) . ' - SRKR Engineering College';
        
        $replacements = [
            'STUDENT_NAME' => $application['student_name'],
            'APPLICATION_ID' => $application['id'],
            'REGISTER_NO' => $application['register_no'],
            'LEAVE_TYPE' => ucfirst($application['leave_type']),
            'START_DATE' => date('d M Y', strtotime($application['start_date'])),
            'END_DATE' => date('d M Y', strtotime($application['end_date'])),
            'TOTAL_DAYS' => $application['total_days'],
            'HOD_REMARKS' => $application['hod_remarks'] ?: 'No remarks provided'
        ];
        
        return self::sendMail(
            $application['student_email'],
            $subject,
            $template,
            $replacements
        );
    }
    
    // Helper function to send notification to HOD about new application
    public static function sendHodNotification($application) {
        // Get server URL
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $domain = $_SERVER['HTTP_HOST'];
        $portal_url = $protocol . $domain . '/attendance/hod_leave_management.php';
        
        $replacements = [
            'APPLICATION_ID' => $application['id'],
            'STUDENT_NAME' => $application['student_name'],
            'REGISTER_NO' => $application['register_no'],
            'SECTION' => $application['section'],
            'LEAVE_TYPE' => ucfirst($application['leave_type']),
            'START_DATE' => date('d M Y', strtotime($application['start_date'])),
            'END_DATE' => date('d M Y', strtotime($application['end_date'])),
            'TOTAL_DAYS' => $application['total_days'],
            'REASON' => $application['reason'],
            'CONTACT_NUMBER' => $application['contact_number'],
            'PARENT_CONTACT' => $application['parent_contact'] ?: 'Not provided',
            'PORTAL_URL' => $portal_url
        ];
        
        return self::sendMail(
            HOD_EMAIL,
            'New Leave Application - SRKR Engineering College',
            'hod_notification',
            $replacements
        );
    }
}
EOT;

file_put_contents('mail_helper.php', $mail_helper);
echo "Created mail_helper.php file.\n";

echo "\nSetup complete! Please follow these steps:\n";
echo "1. Rename mail_config.php.dummy to mail_config.php\n";
echo "2. Edit mail_config.php with your SMTP settings\n";
echo "3. Run the update_leave_applications_table.sql script to add the email field to the database\n";
echo "4. Update your leave application forms and processing scripts to use the new email functionality\n";

echo "\nThank you for using the PHPMailer setup script!\n";
?>