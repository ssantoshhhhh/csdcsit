<?php
// PHPMailer Configuration
// You can modify these settings according to your email provider

// Gmail SMTP Configuration (Recommended for testing)
$mail_config = [
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'username' => 'srkrcsdcsitleavemanagement@gmail.com', // Replace with your email
    'password' => 'krkuryiibmblbmlm
',    // Replace with your app password
    'encryption' => 'tls',
    'from_email' => 'srkrcsdcsitleavemanagement@gmail.com', // Replace with your email
    'from_name' => 'SRKR Engineering College - Leave Management'
];

// Alternative: Outlook/Hotmail SMTP
/*
$mail_config = [
    'host' => 'smtp-mail.outlook.com',
    'port' => 587,
    'username' => 'your-email@outlook.com',
    'password' => 'your-password',
    'encryption' => 'tls',
    'from_email' => 'your-email@outlook.com',
    'from_name' => 'SRKR Engineering College - Leave Management'
];
*/

// Alternative: Custom SMTP Server
/*
$mail_config = [
    'host' => 'your-smtp-server.com',
    'port' => 587,
    'username' => 'your-username',
    'password' => 'your-password',
    'encryption' => 'tls',
    'from_email' => 'noreply@yourdomain.com',
    'from_name' => 'SRKR Engineering College - Leave Management'
];
*/

// HOD Email Configuration
$hod_email = 'srkrcsdcsitleavemanagement@gmail.com'; // Replace with actual HOD email

// College Information
$college_info = [
    'name' => 'SRKR Engineering College ',
    'address' => 'Bhimavaram, Andhra Pradesh',
    'phone' => '+91-XXXXXXXXXX',
    'website' => 'www.srkr.ac.in'
];
?> 