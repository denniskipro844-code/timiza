<?php
// Database configuration for Timiza Youth Initiative
// For InfinityFree hosting, update these values with your actual database credentials

define('DB_HOST', 'sql112.byetcluster.com');
define('DB_NAME', 'if0_40272567_timiza');
define('DB_USER', 'if0_40272567');
define('DB_PASS', '17azBG2QRDHyQA');
define('DB_CHARSET', 'utf8mb4');

// Site configuration
define('SITE_NAME', 'Timiza Youth Initiative');
define('SITE_URL', 'https://timizayouthsinitiative.kesug.com');
define('ADMIN_EMAIL', 'admin@timizayouth.org');

// Gmail SMTP configuration (update with secure values or environment variables in production)
define('GMAIL_SMTP_HOST', getenv('GMAIL_SMTP_HOST') ?: 'smtp.gmail.com');
define('GMAIL_SMTP_PORT', (int) (getenv('GMAIL_SMTP_PORT') ?: 587));
define('GMAIL_USERNAME', getenv('GMAIL_USERNAME') ?: 'briankirui275@gmail.com');
define('GMAIL_APP_PASSWORD', getenv('GMAIL_APP_PASSWORD') ?: 'hojo mhyx bobs wprr');
define('GMAIL_FROM_EMAIL', getenv('GMAIL_FROM_EMAIL') ?: GMAIL_USERNAME);
define('GMAIL_FROM_NAME', getenv('GMAIL_FROM_NAME') ?: SITE_NAME);

// Payment gateways - replace with real credentials in production
define('MPESA_ENV', 'sandbox'); // sandbox or production
define('MPESA_CONSUMER_KEY', '');
define('MPESA_CONSUMER_SECRET', '');
define('MPESA_SHORTCODE', '');
define('MPESA_PASSKEY', '');
define('MPESA_CALLBACK_URL', SITE_URL . '/admin/donations.php?callback=mpesa');
define('MPESA_INITIATOR', '');
define('MPESA_SECURITY_CREDENTIAL', '');

define('PAYHERO_BASE_URL', 'https://backend.payhero.co.ke/api/v2');
define('PAYHERO_API_KEY', 'vph4k8gIPMu7oSeni4wi');
define('PAYHERO_SECRET_KEY', 'NNjboOeV74SmpaMyVUkuC020e8HeY2Awz1GkaXeg');
define('PAYHERO_ACCOUNT_ID', '3357');
define('PAYHERO_CHANNEL_ID', '4006');
define('PAYHERO_PROVIDER', 'm-pesa');
define('PAYHERO_CALLBACK_URL', SITE_URL . '/admin/donations.php?callback=payhero');
define('PAYHERO_CURRENCY', 'KES');

// Security settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);

// Upload settings
define('UPLOAD_PATH', 'assets/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}
?>