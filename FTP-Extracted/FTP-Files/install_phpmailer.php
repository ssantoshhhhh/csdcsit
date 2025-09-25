<?php
/**
 * PHPMailer Installation Script
 * Run this script to install PHPMailer if Composer is not available
 */

echo "PHPMailer Installation Script\n";
echo "=============================\n\n";

// Check if PHPMailer is already installed
if (file_exists('vendor/phpmailer/phpmailer/src/PHPMailer.php')) {
    echo "✓ PHPMailer is already installed!\n";
    exit;
}

// Create vendor directory structure
$vendor_dir = 'vendor/phpmailer/phpmailer/src';
if (!is_dir($vendor_dir)) {
    mkdir($vendor_dir, 0755, true);
    echo "✓ Created vendor directory structure\n";
}

// Download PHPMailer files
$files = [
    'PHPMailer.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/PHPMailer.php',
    'SMTP.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/SMTP.php',
    'Exception.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/Exception.php'
];

echo "Downloading PHPMailer files...\n";

foreach ($files as $filename => $url) {
    $filepath = $vendor_dir . '/' . $filename;
    
    if (file_put_contents($filepath, file_get_contents($url))) {
        echo "✓ Downloaded: $filename\n";
    } else {
        echo "✗ Failed to download: $filename\n";
    }
}

// Create autoload file
$autoload_content = '<?php
// Simple autoloader for PHPMailer
spl_autoload_register(function ($class) {
    $prefix = "PHPMailer\\PHPMailer\\";
    $base_dir = __DIR__ . "/vendor/phpmailer/phpmailer/src/";
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace("\\", "/", $relative_class) . ".php";
    
    if (file_exists($file)) {
        require $file;
    }
});
?>';

if (file_put_contents('vendor/autoload.php', $autoload_content)) {
    echo "✓ Created autoload file\n";
} else {
    echo "✗ Failed to create autoload file\n";
}

echo "\nInstallation completed!\n";
echo "You can now use PHPMailer in your leave application system.\n";
echo "\nNext steps:\n";
echo "1. Configure your email settings in mail_config.php\n";
echo "2. Test the email functionality\n";
echo "3. Make sure your server allows outgoing SMTP connections\n";
?> 