<?php
require_once 'vendor/autoload.php'; // Simple autoloader for PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class MailHelper {
    private $mailer;
    private $config;
    
    public function __construct($config) {
        $this->config = $config;
        $this->initializeMailer();
    }
    
    private function initializeMailer() {
        $this->mailer = new PHPMailer(true);
        
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->config['username'];
            $this->mailer->Password = $this->config['password'];
            $this->mailer->SMTPSecure = $this->config['encryption'];
            $this->mailer->Port = $this->config['port'];
            
            // Default settings
            $this->mailer->setFrom($this->config['from_email'], $this->config['from_name']);
            $this->mailer->isHTML(true);
            
        } catch (Exception $e) {
            error_log("Mailer initialization failed: " . $e->getMessage());
        }
    }
    
    public function sendLeaveApplicationNotification($student_data, $hod_email) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($hod_email);
            $this->mailer->Subject = 'New Leave Application - ' . $student_data['student_name'];
            
            $body = $this->getLeaveApplicationEmailBody($student_data);
            $this->mailer->Body = $body;
            $this->mailer->AltBody = $this->getPlainTextBody($student_data);
            
            $result = $this->mailer->send();
            if (!$result) {
                error_log("Mailer send() returned false for HOD notification to: " . $hod_email);
            }
            return $result;
        } catch (Exception $e) {
            error_log("Failed to send leave application notification: " . $e->getMessage());
            error_log("HOD email: " . $hod_email);
            return false;
        }
    }
    
    public function sendLeaveStatusNotification($student_data, $status, $remarks = '') {
        try {
            $this->mailer->clearAddresses();
            
            // Use student_email instead of email (database field name)
            $student_email = $student_data['student_email'] ?? '';
            if (!empty($student_email)) {
                $this->mailer->addAddress($student_email);
            } else {
                error_log("No student email found for notification. Student data: " . json_encode($student_data));
                return false;
            }
            
            $this->mailer->Subject = 'Leave Application ' . ucfirst($status) . ' - ' . $student_data['student_name'];
            
            $body = $this->getLeaveStatusEmailBody($student_data, $status, $remarks);
            $this->mailer->Body = $body;
            $this->mailer->AltBody = $this->getStatusPlainTextBody($student_data, $status, $remarks);
            
            $result = $this->mailer->send();
            if (!$result) {
                error_log("Mailer send() returned false for student: " . $student_email);
            }
            return $result;
        } catch (Exception $e) {
            error_log("Failed to send leave status notification: " . $e->getMessage());
            error_log("Student data: " . json_encode($student_data));
            return false;
        }
    }
    
    private function getLeaveApplicationEmailBody($student_data) {
        $sections = [
            '28csit_a_attendance' => '2/4 CSIT-A',
            '28csit_b_attendance' => '2/4 CSIT-B',
            '28csd_attendance'    => '2/4 CSD',
            '27csit_attendance'   => '3/4 CSIT',
            '27csd_attendance'    => '3/4 CSD',
            '26csd_attendance'    => '4/4 CSD',
        ];
        
        $section_name = $sections[$student_data['section']] ?? ($student_data['section'] ?? '');
        $student_email_safe = $student_data['student_email'] ?? ($student_data['email'] ?? '');
        $contact_number_safe = $student_data['contact_number'] ?? '';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { background: #076593; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .info-box { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px; margin: 15px 0; }
                .footer { background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #666; }
                .btn { display: inline-block; padding: 10px 20px; background: #076593; color: white; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h2>SRKR Engineering College</h2>
                <p>Leave Management System</p>
            </div>
            
            <div class='content'>
                <h3>New Leave Application Received</h3>
                <p>A new leave application has been submitted and requires your review.</p>
                
                <div class='info-box'>
                    <h4>Student Information:</h4>
                    <p><strong>Name:</strong> {$student_data['student_name']}</p>
                    <p><strong>Register Number:</strong> {$student_data['register_no']}</p>
                    <p><strong>Section:</strong> {$section_name}</p>
                    <p><strong>Email:</strong> {$student_email_safe}</p>
                    <p><strong>Contact:</strong> {$contact_number_safe}</p>
                </div>
                
                <div class='info-box'>
                    <h4>Leave Details:</h4>
                    <p><strong>Type:</strong> " . ucfirst($student_data['leave_type']) . " Leave</p>
                    <p><strong>From:</strong> " . date('d M Y', strtotime($student_data['start_date'])) . "</p>
                    <p><strong>To:</strong> " . date('d M Y', strtotime($student_data['end_date'])) . "</p>
                    <p><strong>Total Days:</strong> {$student_data['total_days']} day(s)</p>
                    <p><strong>Reason:</strong> {$student_data['reason']}</p>
                </div>
                
                <p><strong>Application ID:</strong> {$student_data['id']}</p>
                <p><strong>Submitted On:</strong> " . date('d M Y H:i', strtotime($student_data['applied_at'])) . "</p>
                
                <p style='margin-top: 20px;'>
                    <a href='http://your-domain.com/hod_leave_management.php' class='btn'>Review Application</a>
                </p>
            </div>
            
            <div class='footer'>
                <p>This is an automated notification from the SRKR Engineering College Leave Management System.</p>
                <p>Please do not reply to this email.</p>
            </div>
        </body>
        </html>";
    }
    
    private function getLeaveStatusEmailBody($student_data, $status, $remarks) {
        $sections = [
            '28csit_a_attendance' => '2/4 CSIT-A',
            '28csit_b_attendance' => '2/4 CSIT-B',
            '28csd_attendance'    => '2/4 CSD',
            '27csit_attendance'   => '3/4 CSIT',
            '27csd_attendance'    => '3/4 CSD',
            '26csd_attendance'    => '4/4 CSD',
        ];
        $section_name = $sections[$student_data['section']] ?? ($student_data['section'] ?? '');
        $status_color = ($status == 'approved') ? '#28a745' : '#dc3545';
        $status_text = ucfirst($status);
        $remarks_html = !empty($remarks) ? htmlspecialchars($remarks) : '<span style="color:#888;">No remarks</span>';
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f8fb; color: #222; margin: 0; }
                .header { background: #076593; color: white; padding: 24px 0; text-align: center; border-radius: 0 0 12px 12px; }
                .content { background: #fff; margin: 32px auto; max-width: 600px; border-radius: 12px; box-shadow: 0 4px 24px rgba(7,101,147,0.08); padding: 32px; }
                .status-box { background: {$status_color}; color: white; padding: 18px; border-radius: 8px; text-align: center; margin: 18px 0; font-size: 1.2em; }
                .info-box { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 18px; margin: 18px 0; }
                .footer { background: #f8f9fa; padding: 18px; text-align: center; font-size: 13px; color: #666; border-radius: 0 0 12px 12px; }
                h2, h3, h4 { margin: 0 0 12px 0; }
                p { margin: 0 0 8px 0; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h2>SRKR Engineering College</h2>
                <p>Leave Management System</p>
            </div>
            <div class='content'>
                <div class='status-box'>
                    <h3>Leave Application {$status_text}</h3>
                </div>
                <p>Dear <strong>{$student_data['student_name']}</strong>,</p>
                <p>Your leave application has been <strong>{$status_text}</strong> by the HOD.</p>
                <div class='info-box'>
                    <h4>Application Details:</h4>
                    <p><strong>Application ID:</strong> {$student_data['id']}</p>
                    <p><strong>Register Number:</strong> {$student_data['register_no']}</p>
                    <p><strong>Section:</strong> {$section_name}</p>
                    <p><strong>Leave Type:</strong> " . ucfirst($student_data['leave_type']) . " Leave</p>
                    <p><strong>From:</strong> " . date('d M Y', strtotime($student_data['start_date'])) . "</p>
                    <p><strong>To:</strong> " . date('d M Y', strtotime($student_data['end_date'])) . "</p>
                    <p><strong>Total Days:</strong> {$student_data['total_days']} day(s)</p>
                </div>
                <div class='info-box'>
                    <h4>HOD Remarks:</h4>
                    <p>{$remarks_html}</p>
                </div>
                <p><strong>Processed On:</strong> " . date('d M Y H:i') . "</p>
                <p style='margin-top: 20px;'>If you have any questions, please contact your HOD or the administration office.</p>
            </div>
            <div class='footer'>
                <p>This is an automated notification from the SRKR Engineering College Leave Management System.</p>
                <p>Please do not reply to this email.</p>
            </div>
        </body>
        </html>";
    }
    
    private function getPlainTextBody($student_data) {
        return "New Leave Application\n\n" .
               "Student: {$student_data['student_name']}\n" .
               "Register Number: {$student_data['register_no']}\n" .
               "Section: {$student_data['section']}\n" .
               "Leave Type: " . ucfirst($student_data['leave_type']) . "\n" .
               "From: " . date('d M Y', strtotime($student_data['start_date'])) . "\n" .
               "To: " . date('d M Y', strtotime($student_data['end_date'])) . "\n" .
               "Total Days: {$student_data['total_days']}\n" .
               "Reason: {$student_data['reason']}\n" .
               "Application ID: {$student_data['id']}";
    }
    
    private function getStatusPlainTextBody($student_data, $status, $remarks) {
        $body = "Leave Application {$status}\n\n" .
                "Dear {$student_data['student_name']},\n\n" .
                "Your leave application has been {$status}.\n\n" .
                "Application ID: {$student_data['id']}\n" .
                "Register Number: {$student_data['register_no']}\n" .
                "Leave Type: " . ucfirst($student_data['leave_type']) . "\n" .
                "From: " . date('d M Y', strtotime($student_data['start_date'])) . "\n" .
                "To: " . date('d M Y', strtotime($student_data['end_date'])) . "\n" .
                "Total Days: {$student_data['total_days']}\n";
        
        if (!empty($remarks)) {
            $body .= "HOD Remarks: {$remarks}\n";
        }
        
        $body .= "\nProcessed On: " . date('d M Y H:i');
        
        return $body;
    }

    // New: Notify all faculty assigned to a section
    public function notifySectionFaculty($faculty_emails, $student_data, $status, $remarks = '') {
        $results = [];
        foreach ($faculty_emails as $email) {
            try {
                $this->mailer->clearAddresses();
                $this->mailer->addAddress($email);
                $this->mailer->Subject = 'Leave Application ' . ucfirst($status) . ' - ' . $student_data['student_name'];
                $body = $this->getLeaveStatusEmailBody($student_data, $status, $remarks);
                $this->mailer->Body = $body;
                $this->mailer->AltBody = $this->getStatusPlainTextBody($student_data, $status, $remarks);
                $results[$email] = $this->mailer->send();
            } catch (Exception $e) {
                error_log("Failed to notify faculty $email: " . $e->getMessage());
                $results[$email] = false;
            }
        }
        return $results;
    }
}
?>