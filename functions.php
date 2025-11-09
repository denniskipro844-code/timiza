<?php
session_start();

require_once __DIR__ . '/../vendor/autoload.php';

// Lightweight PayHero client used by donation flows
if (false && !class_exists('PayheroService')) {
    class PayheroService {
        private static function baseHeaders(): array {
            $headers = [
                'Content-Type: application/json',
            ];
            if (defined('PAYHERO_API_KEY') && PAYHERO_API_KEY) {
                $headers[] = 'Authorization: Bearer ' . PAYHERO_API_KEY;
            }
            if (defined('PAYHERO_SECRET_KEY') && PAYHERO_SECRET_KEY) {
                $headers[] = 'X-Secret-Key: ' . PAYHERO_SECRET_KEY;
            }
            return $headers;
        }

        private static function baseUrl(string $path): string {
            $base = defined('PAYHERO_BASE_URL') && PAYHERO_BASE_URL ? rtrim(PAYHERO_BASE_URL, '/') : '';
            return $base . '/' . ltrim($path, '/');
        }

        private static function httpRequest(string $method, string $url, ?array $body = null): array {
            $ch = curl_init();
            $opts = [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_CUSTOMREQUEST => strtoupper($method),
                CURLOPT_HTTPHEADER => self::baseHeaders(),
            ];
            if ($body !== null) {
                $opts[CURLOPT_POSTFIELDS] = json_encode($body, JSON_UNESCAPED_UNICODE);
            }
            curl_setopt_array($ch, $opts);
            $raw = curl_exec($ch);
            $errno = curl_errno($ch);
            $err = curl_error($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $decoded = null;
            if ($raw !== false) {
                $decoded = json_decode($raw, true);
            }

            return [
                'http_code' => $code,
                'curl_errno' => $errno,
                'curl_error' => $err,
                'raw' => $raw,
                'json' => is_array($decoded) ? $decoded : null,
            ];
        }

        public static function initiateBankCollection(array $params): array {
            $endpoint = self::baseUrl('/collections/initiate');

            $payload = [
                'amount' => (float) ($params['amount'] ?? 0),
                'reference' => (string) ($params['reference'] ?? ''),
                'phone_number' => (string) ($params['phone'] ?? ''),
                'name' => (string) ($params['name'] ?? ''),
                'email' => (string) ($params['email'] ?? ''),
                'provider' => defined('PAYHERO_PROVIDER') ? PAYHERO_PROVIDER : 'm-pesa',
            ];

            if (defined('PAYHERO_ACCOUNT_ID') && PAYHERO_ACCOUNT_ID) {
                $payload['account_id'] = PAYHERO_ACCOUNT_ID;
            }
            if (defined('PAYHERO_CHANNEL_ID') && PAYHERO_CHANNEL_ID) {
                $payload['channel_id'] = PAYHERO_CHANNEL_ID;
            }
            if (defined('PAYHERO_CALLBACK_URL') && PAYHERO_CALLBACK_URL) {
                $payload['callback_url'] = PAYHERO_CALLBACK_URL;
            }
            if (defined('PAYHERO_CURRENCY') && PAYHERO_CURRENCY) {
                $payload['currency'] = PAYHERO_CURRENCY;
            }

            $resp = self::httpRequest('POST', $endpoint, $payload);
            $json = $resp['json'] ?? [];

            $success = false;
            $status = null;
            $trxRef = null;
            $extRef = $payload['reference'] ?? null;

            if (is_array($json)) {
                $success = (bool) ($json['success'] ?? ($resp['http_code'] >= 200 && $resp['http_code'] < 300));
                $status = $json['status'] ?? ($json['response']['status'] ?? null);
                $trxRef = $json['transaction_reference']
                    ?? ($json['response']['transaction_reference'] ?? ($json['response']['CheckoutRequestID'] ?? null));
            }

            return [
                'success' => $success,
                'status' => $status,
                'transaction_reference' => $trxRef,
                'external_reference' => $extRef,
                'response' => is_array($json) ? $json : ['raw' => $resp['raw']],
                'http_code' => $resp['http_code'],
                'payload' => is_array($json) ? $json : null,
            ];
        }

        public static function fetchTransactionStatus(string $transactionReference): array {
            $query = [
                // Canonical lookup key
                'transaction_reference' => trim($transactionReference),
            ];
            if (defined('PAYHERO_ACCOUNT_ID') && PAYHERO_ACCOUNT_ID) {
                $query['account_id'] = PAYHERO_ACCOUNT_ID;
            }
            if (defined('PAYHERO_CHANNEL_ID') && PAYHERO_CHANNEL_ID) {
                $query['channel_id'] = PAYHERO_CHANNEL_ID;
            }

            $endpoint = self::baseUrl('/transactions/status') . '?' . http_build_query($query);
            $resp = self::httpRequest('GET', $endpoint);
            $json = $resp['json'] ?? [];

            // Normalize common shapes
            $status = $json['status'] ?? ($json['data']['status'] ?? ($json['response']['status'] ?? null));
            $trxRef = $json['transaction_reference']
                ?? ($json['data']['transaction_reference'] ?? ($json['response']['transaction_reference'] ?? null));
            $extRef = $json['external_reference']
                ?? ($json['data']['external_reference'] ?? ($json['response']['external_reference'] ?? null));
            $checkoutId = $json['CheckoutRequestID']
                ?? ($json['data']['CheckoutRequestID'] ?? ($json['response']['CheckoutRequestID'] ?? null));

            $success = (bool) ($json['success'] ?? ($resp['http_code'] >= 200 && $resp['http_code'] < 300));

            return [
                'success' => $success,
                'status' => $status,
                'transaction_reference' => $trxRef,
                'external_reference' => $extRef,
                'CheckoutRequestID' => $checkoutId,
                'used_query_param' => 'transaction_reference',
                'http_code' => $resp['http_code'],
                'payload' => is_array($json) ? $json : ['raw' => $resp['raw']],
            ];
        }
    }
}

// Authentication functions
function isLoggedIn(): bool {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function loadCurrentAdmin(): ?array {
    if (!isLoggedIn()) {
        return null;
    }

    if (isset($_SESSION['admin_cached']) && is_array($_SESSION['admin_cached'])) {
        return $_SESSION['admin_cached'];
    }

    global $pdo;
    $stmt = $pdo->prepare("SELECT id, username, email, full_name, role, active FROM admin_users WHERE id = ? LIMIT 1");
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch();

    if ($admin) {
        $_SESSION['admin_cached'] = $admin;
        $_SESSION['admin_role'] = $admin['role'] ?? null;
    } else {
        logout();
        return null;
    }

    return $admin;
}

function currentAdminRole(): ?string {
    if (!isset($_SESSION['admin_role']) || empty($_SESSION['admin_role'])) {
        $admin = loadCurrentAdmin();
        return $admin['role'] ?? null;
    }

    return $_SESSION['admin_role'];
}

function requireAuth(): void {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }

    // Refresh cached admin info if needed (including role)
    loadCurrentAdmin();
}

function requireRole(array $allowedRoles): void {
    requireAuth();
    $role = currentAdminRole();

    if (!$role || !in_array($role, $allowedRoles, true)) {
        header('Location: dashboard.php?error=unauthorized');
        exit();
    }
}

function login($username, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id, username, password, role FROM admin_users WHERE username = ? AND active = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_role'] = $user['role'] ?? null;
        unset($_SESSION['admin_cached']);
        $_SESSION['last_activity'] = time();
        return true;
    }
    return false;
}

function logout() {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
    header('Location: login.php');
    exit();
}

// Utility functions
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function sendEmail(string $to, string $subject, string $body, ?string $fromEmail = null, ?string $fromName = null, bool $formatAsHtml = true): bool
{
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    if (empty(GMAIL_USERNAME) || empty(GMAIL_APP_PASSWORD)) {
        error_log('Mailer configuration missing: ensure GMAIL_USERNAME and GMAIL_APP_PASSWORD are set.');
        return false;
    }

    $fromEmail = $fromEmail ?: GMAIL_FROM_EMAIL;
    $fromName = $fromName ?: GMAIL_FROM_NAME;
    $plainTextBody = normalizeEmailPlainText($body);

    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = GMAIL_SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = GMAIL_USERNAME;
        $mail->Password = GMAIL_APP_PASSWORD;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = GMAIL_SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($to);
        $mail->Subject = $subject;

        if ($formatAsHtml) {
            $mail->isHTML(true);
            $mail->Body = buildEmailTemplate($subject, $body, $fromName ?: SITE_NAME);
            $mail->AltBody = $plainTextBody;
        } else {
            $mail->isHTML(false);
            $mail->Body = $plainTextBody;
        }

        $mail->send();
        return true;
    } catch (\Throwable $e) {
        error_log('Mailer error: ' . $e->getMessage());
        return false;
    }
}

function buildEmailTemplate(string $subject, string $content, string $brandName): string
{
    $escapedSubject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
    $contentHtml = nl2br(htmlspecialchars(trim($content), ENT_QUOTES, 'UTF-8'));
    $currentYear = date('Y');
    $siteUrl = defined('SITE_URL') ? SITE_URL : '#';

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$escapedSubject}</title>
</head>
<body style="margin:0; padding:0; background-color:#f8fafc; font-family:'Poppins', Arial, sans-serif; color:#1f2937;">
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background-color:#f8fafc; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" width="600" style="max-width:600px; background:#ffffff; border-radius:24px; overflow:hidden; box-shadow:0 20px 40px -25px rgba(15,118,110,0.35);">
                    <tr>
                        <td style="background:linear-gradient(135deg, rgba(22,101,52,0.16), rgba(59,130,246,0.12)); padding:32px; text-align:center;">
                            <h1 style="margin:0; font-size:24px; color:#0f172a; letter-spacing:0.08em; text-transform:uppercase;">{$brandName}</h1>
                            <p style="margin:12px 0 0; font-size:16px; color:#0f766e; font-weight:600;">{$escapedSubject}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px 36px 24px; font-size:15px; line-height:1.7; color:#1f2937;">
                            {$contentHtml}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 36px 32px; background-color:#f1f5f9; font-size:12px; line-height:1.6; color:#64748b; text-align:center;">
                            <p style="margin:0 0 8px;">You are receiving this email from {$brandName}.</p>
                            <p style="margin:0;">© {$currentYear} {$brandName}. All rights reserved. <a href="{$siteUrl}" style="color:#0f766e; text-decoration:none;">Visit our website</a></p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
}

function normalizeEmailPlainText(string $content): string
{
    $content = trim($content);
    if ($content === '') {
        return '';
    }

    $content = preg_replace("/\r\n|\r/", "\n", $content);
    return html_entity_decode(strip_tags($content), ENT_QUOTES, 'UTF-8');
}

function createSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

function formatDate($date, $format = 'M j, Y') {
    return date($format, strtotime($date));
}

function truncateText($text, $length = 150) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

// Database helper functions
function getNews($limit = null, $featured = false) {
    global $pdo;
    
    $sql = "SELECT * FROM news WHERE status = 'published'";
    if ($featured) {
        $sql .= " AND featured = 1";
    }
    $sql .= " ORDER BY created_at DESC";
    if ($limit) {
        $sql .= " LIMIT " . intval($limit);
    }
    
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function getNewsBySlug(string $slug) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM news WHERE slug = ? AND status = 'published' LIMIT 1");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function getPrograms() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT * FROM programs WHERE active = 1 ORDER BY sort_order ASC");
    return $stmt->fetchAll();
}

function getGalleryImages($limit = null) {
    global $pdo;
    
    $sql = "SELECT * FROM gallery WHERE active = 1 ORDER BY created_at DESC";
    if ($limit) {
        $sql .= " LIMIT " . intval($limit);
    }
    
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function getMessages($limit = null) {
    global $pdo;
    
    $sql = "SELECT * FROM contact_messages ORDER BY created_at DESC";
    if ($limit) {
        $sql .= " LIMIT " . intval($limit);
    }
    
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function getStats() {
    global $pdo;
    
    $stats = [];
    
    // Get volunteer count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM volunteers WHERE active = 1");
    $stats['volunteers'] = $stmt->fetch()['count'];
    
    // Get programs count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM programs WHERE active = 1");
    $stats['programs'] = $stmt->fetch()['count'];
    
    // Get news count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM news WHERE status = 'published'");
    $stats['news'] = $stmt->fetch()['count'];
    
    // Static stats (update as needed)
    $stats['youth_reached'] = 1200;
    $stats['communities'] = 15;
    
    return $stats;
}

// Form handling
function handleContactForm() {
    if ($_POST['action'] === 'contact') {
        global $pdo;
        
        $name = sanitizeInput($_POST['name']);
        $email = sanitizeInput($_POST['email']);
        $subject = sanitizeInput($_POST['subject']);
        $message = sanitizeInput($_POST['message']);
        
        if (empty($name) || empty($email) || empty($message)) {
            return ['success' => false, 'message' => 'Please fill in all required fields.'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Please enter a valid email address.'];
        }
        
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$name, $email, $subject, $message]);
            
            return ['success' => true, 'message' => 'Thank you for your message. We will get back to you soon!'];
        } catch (Exception $e) {
            error_log("Contact form error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Sorry, there was an error sending your message. Please try again.'];
        }
    }
}

function handleVolunteerForm() {
    if ($_POST['action'] === 'volunteer') {
        global $pdo;
        
        $name = sanitizeInput($_POST['name']);
        $email = sanitizeInput($_POST['email']);
        $phone = sanitizeInput($_POST['phone']);
        $interest = sanitizeInput($_POST['interest']);
        $experience = sanitizeInput($_POST['experience'] ?? '');
        
        if (empty($name) || empty($email) || empty($phone) || empty($interest)) {
            return ['success' => false, 'message' => 'Please fill in all required fields.'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Please enter a valid email address.'];
        }
        
        try {
            $stmt = $pdo->prepare("INSERT INTO volunteers (name, email, phone, interest_area, experience, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
            $stmt->execute([$name, $email, $phone, $interest, $experience]);
            
            return ['success' => true, 'message' => 'Thank you for your interest in volunteering! We will contact you soon.'];
        } catch (Exception $e) {
            error_log("Volunteer form error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Sorry, there was an error processing your application. Please try again.'];
        }
    }
}

function ensureDonationsTablesInitialized(): void {
    static $initialized = false;
    if ($initialized) {
        return;
    }

    global $pdo;

    try {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS donations (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                donor_name VARCHAR(150) NOT NULL,
                donor_email VARCHAR(150) DEFAULT NULL,
                donor_phone VARCHAR(50) DEFAULT NULL,
                amount DECIMAL(12,2) NOT NULL,
                currency VARCHAR(10) DEFAULT 'KES',
                provider ENUM('mpesa','payhero') NOT NULL,
                provider_channel VARCHAR(50) DEFAULT NULL,
                status ENUM('pending','initiated','processing','completed','failed','cancelled') DEFAULT 'pending',
                reference VARCHAR(40) NOT NULL UNIQUE,
                provider_reference VARCHAR(80) DEFAULT NULL,
                meta JSON DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS donation_events (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                donation_id INT UNSIGNED NOT NULL,
                provider ENUM('mpesa','payhero') NOT NULL,
                event_type VARCHAR(50) NOT NULL,
                status VARCHAR(30) DEFAULT NULL,
                payload JSON DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_donation_events_donation FOREIGN KEY (donation_id)
                    REFERENCES donations(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    } catch (Exception $e) {
        error_log('Failed creating donation tables: ' . $e->getMessage());
    }

    $initialized = true;
}

function recordDonationEvent(int $donationId, string $provider, string $eventType, array $payload = [], ?string $status = null): void {
    global $pdo;

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO donation_events (donation_id, provider, event_type, status, payload) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $donationId,
            $provider,
            $eventType,
            $status,
            json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);
    } catch (Exception $e) {
        error_log('Failed to record donation event: ' . $e->getMessage());
    }
}

function resendDonationPayment(int $donationId): array {
    ensureDonationsTablesInitialized();
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT * FROM donations WHERE id = ? LIMIT 1");
        $stmt->execute([$donationId]);
        $donation = $stmt->fetch();
    } catch (Exception $e) {
        error_log('Fetch donation failed: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Unable to locate the donation record.'];
    }

    if (!$donation) {
        return ['success' => false, 'message' => 'Donation not found.'];
    }

    if ($donation['provider'] !== 'mpesa') {
        return ['success' => false, 'message' => 'Resending is only supported for M-Pesa donations.'];
    }

    if ($donation['status'] === 'completed') {
        return ['success' => false, 'message' => 'This donation is already marked as completed.'];
    }

    if (empty($donation['donor_phone'])) {
        return ['success' => false, 'message' => 'No phone number available for this donor.'];
    }

    $response = MpesaService::initiateStkPush([
        'Amount' => (float) $donation['amount'],
        'PhoneNumber' => $donation['donor_phone'],
        'AccountReference' => $donation['reference'],
        'TransactionDesc' => 'Donation Resend'
    ]);

    recordDonationEvent((int) $donation['id'], 'mpesa', 'stk_resend', $response, $response['success'] ? 'initiated' : 'failed');

    if ($response['success']) {
        try {
            $checkoutRequestId = $response['response']['CheckoutRequestID'] ?? null;
            $pdo->prepare("UPDATE donations SET status = 'initiated', provider_reference = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")
                ->execute([$checkoutRequestId, $donation['id']]);
        } catch (Exception $e) {
            error_log('Failed to update donation after resend: ' . $e->getMessage());
        }

        return ['success' => true, 'message' => 'M-Pesa push resent successfully. Ask the donor to approve the prompt.'];
    }

    return ['success' => false, 'message' => $response['message'] ?? 'Failed to resend M-Pesa push.'];
}

function notifyPayheroManualSync(array $donation, bool $success, string $message, array $details = []): void {
    if (!defined('ADMIN_EMAIL') || !filter_var(ADMIN_EMAIL, FILTER_VALIDATE_EMAIL)) {
        return;
    }

    $reference = $donation['reference'] ?? ('Donation #' . ($donation['id'] ?? '?'));
    $subject = ($success ? 'PayHero Manual Sync Successful - ' : 'PayHero Manual Sync Failed - ') . $reference;

    $lines = [
        'Donation ID: ' . ($donation['id'] ?? 'unknown'),
        'Reference: ' . $reference,
        'Provider Reference: ' . ($donation['provider_reference'] ?? 'N/A'),
        'Previous Status: ' . ($donation['status'] ?? 'unknown'),
        'Message: ' . $message,
    ];

    if (!empty($details)) {
        $lines[] = '';
        $lines[] = 'Details:';
        $lines[] = json_encode($details, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    $body = implode("\n", $lines);
    sendEmail(ADMIN_EMAIL, $subject, $body, null, SITE_NAME, false);
}

function syncPayheroDonationStatus(int $donationId): array {
    ensureDonationsTablesInitialized();
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT * FROM donations WHERE id = ? LIMIT 1");
        $stmt->execute([$donationId]);
        $donation = $stmt->fetch();
    } catch (Exception $e) {
        error_log('Fetch donation failed: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Unable to locate the donation record.'];
    }

    if (!$donation) {
        return ['success' => false, 'message' => 'Donation not found.'];
    }

    if ($donation['provider'] !== 'payhero') {
        $message = 'This action applies only to PayHero donations.';
        notifyPayheroManualSync($donation, false, $message);
        return ['success' => false, 'message' => $message];
    }

    // Enhanced reference collection with better fallback strategy
    $referencesToTry = [];
    if (!empty($donation['provider_reference']) && trim((string) $donation['provider_reference']) !== '') {
        $referencesToTry[] = trim((string) $donation['provider_reference']);
    }

    // Parse metadata for additional references
    $meta = json_decode($donation['meta'] ?? '[]', true);
    if (is_array($meta)) {
        $metaCandidates = [];

        // Check previous sync attempts
        if (isset($meta['payhero_last_manual_sync'])) {
            $lastSync = $meta['payhero_last_manual_sync'];
            if (!empty($lastSync['reference_used'])) {
                $metaCandidates[] = $lastSync['reference_used'];
            }
            if (!empty($lastSync['provider_reference'])) {
                $metaCandidates[] = $lastSync['provider_reference'];
            }
        }

        // Check callback metadata
        $callbackMeta = $meta['callback'] ?? ($meta['payhero']['callback'] ?? null);
        if (is_array($callbackMeta)) {
            $identifierKeys = [
                'transaction_reference', 'TransactionReference', 'CheckoutRequestID', 
                'checkout_request_id', 'MerchantRequestID', 'merchant_request_id',
                'ReferenceNumber', 'reference_number', 'TransactionReceipt'
            ];
            
            foreach ($identifierKeys as $key) {
                if (!empty($callbackMeta[$key])) {
                    $metaCandidates[] = $callbackMeta[$key];
                }
                if (!empty($callbackMeta['payload'][$key])) {
                    $metaCandidates[] = $callbackMeta['payload'][$key];
                }
                if (!empty($callbackMeta['response'][$key])) {
                    $metaCandidates[] = $callbackMeta['response'][$key];
                }
            }
        }

        // Add unique candidates to references to try
        foreach ($metaCandidates as $candidate) {
            $candidate = trim((string) $candidate);
            if ($candidate !== '' && !in_array($candidate, $referencesToTry, true)) {
                $referencesToTry[] = $candidate;
            }
        }
    }

    if (empty($referencesToTry)) {
        $message = 'No PayHero reference is available for this donation.';
        notifyPayheroManualSync($donation, false, $message);
        return ['success' => false, 'message' => $message];
    }

    // Try each reference until we find a match
    $attemptErrors = [];
    $response = null;
    $referenceUsed = null;

    foreach ($referencesToTry as $candidateReference) {
        $candidateResponse = PayheroService::fetchTransactionStatus($candidateReference);
        
        if (!empty($candidateResponse['success'])) {
            // Enhanced validation with more flexible matching
            $isValidMatch = validatePayheroTransactionMatch($donation, $candidateResponse, $candidateReference);
            
            if ($isValidMatch) {
                $referenceUsed = $candidateReference;
                $response = $candidateResponse;
                break;
            } else {
                // Log mismatch but continue trying other references
                $attemptErrors[] = [
                    'reference' => $candidateReference,
                    'message' => 'Transaction details do not match donation record',
                    'payload' => $candidateResponse['payload'] ?? null,
                    'http_code' => $candidateResponse['http_code'] ?? null,
                ];
                error_log("PayHero reference mismatch for donation {$donation['id']}: {$candidateReference}");
            }
        } else {
            $attemptErrors[] = [
                'reference' => $candidateReference,
                'message' => $candidateResponse['message'] ?? 'Failed to fetch status',
                'payload' => $candidateResponse['payload'] ?? null,
                'http_code' => $candidateResponse['http_code'] ?? null,
            ];
        }
    }

    if (!$response || empty($response['success'])) {
        $message = 'Unable to retrieve matching PayHero transaction status.';
        notifyPayheroManualSync($donation, false, $message, [
            'references_tried' => $referencesToTry,
            'attempt_errors' => $attemptErrors,
        ]);
        return ['success' => false, 'message' => $message];
    }

    // Process successful response
    $incomingStatus = strtolower((string) ($response['status'] ?? ''));
    $statusMap = [
        'success' => 'completed',
        'completed' => 'completed',
        'successful' => 'completed',
        'paid' => 'completed',
        'queued' => 'processing',
        'pending' => 'processing',
        'processing' => 'processing',
        'failed' => 'failed',
        'failed_payment' => 'failed',
        'cancelled' => 'cancelled',
        'canceled' => 'cancelled',
    ];

    if (!isset($statusMap[$incomingStatus])) {
        $message = 'Unrecognized PayHero status: ' . ($response['status'] ?? 'unknown');
        notifyPayheroManualSync($donation, false, $message, [
            'reference_used' => $referenceUsed,
            'payload' => $response['payload'] ?? null,
        ]);
        return ['success' => false, 'message' => $message];
    }

    $mappedStatus = $statusMap[$incomingStatus];

    // Update donation record
    $providerReference = extractProviderReference($response) ?? $donation['provider_reference'];
    
    $metaData = json_decode($donation['meta'] ?? '[]', true) ?: [];
    $metaData['payhero_last_manual_sync'] = [
        'synced_at' => date('c'),
        'raw_status' => $response['status'] ?? null,
        'reference_used' => $referenceUsed,
        'references_tried' => $referencesToTry,
        'lookup_query_param' => $response['used_query_param'] ?? null,
        'provider_reference' => $providerReference,
        'payload' => $response['payload'] ?? null,
    ];

    $statusChanged = $mappedStatus !== $donation['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE donations SET status = ?, provider_reference = ?, meta = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$mappedStatus, $providerReference, json_encode($metaData, JSON_UNESCAPED_UNICODE), $donation['id']]);

        recordDonationEvent(
            (int) $donation['id'],
            'payhero',
            'manual_sync',
            $response['payload'] ?? $response,
            $mappedStatus
        );
    } catch (Exception $e) {
        error_log('PayHero manual sync update failed: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update donation record after syncing.'];
    }

    $statusLabel = ucfirst($mappedStatus);
    $message = $statusChanged ? "Donation status updated to {$statusLabel}." : 'Donation status is already up to date.';
    
    notifyPayheroManualSync($donation, true, $message, [
        'reference_used' => $referenceUsed,
        'references_tried' => $referencesToTry,
        'lookup_query_param' => $response['used_query_param'] ?? null,
        'raw_status' => $response['status'] ?? null,
    ]);

    return ['success' => true, 'message' => $message];
}

/**
 * Validate if PayHero transaction response matches the donation record
 */
function validatePayheroTransactionMatch(array $donation, array $response, string $referenceUsed): bool {
    $payload = $response['payload'] ?? [];
    if (!is_array($payload)) {
        return false;
    }

    // Extract transaction details from response
    $respAmount = $payload['amount'] ?? ($payload['response']['amount'] ?? null);
    $respPhone = $payload['phone_number'] ?? ($payload['response']['phone_number'] ?? ($payload['Phone'] ?? null));
    
    // Normalize amounts for comparison
    $donationAmount = (float) ($donation['amount'] ?? 0);
    $responseAmount = (float) $respAmount;
    
    // Normalize phone numbers for comparison
    $donationPhone = normalizePhoneNumber($donation['donor_phone'] ?? '');
    $responsePhone = normalizePhoneNumber($respPhone ?? '');
    
    // Check if amounts match (allow small floating point differences)
    $amountMatch = abs($donationAmount - $responseAmount) < 0.01;
    
    // Check if phone numbers match
    $phoneMatch = $donationPhone !== '' && $responsePhone !== '' && $donationPhone === $responsePhone;
    
    // For strict validation, require both amount and phone to match
    // For lenient validation, allow if either matches and the other is not contradictory
    if ($amountMatch && $phoneMatch) {
        return true;
    }
    
    // Lenient validation: if amounts match but phones don't, still accept if response phone is empty
    if ($amountMatch && ($responsePhone === '' || $donationPhone === '')) {
        return true;
    }
    
    // Log the mismatch details for debugging
    error_log("PayHero validation failed for donation {$donation['id']}: " . 
              "Amount: {$donationAmount} vs {$responseAmount} (match: " . ($amountMatch ? 'yes' : 'no') . "), " .
              "Phone: {$donationPhone} vs {$responsePhone} (match: " . ($phoneMatch ? 'yes' : 'no') . ")");
    
    return false;
}

/**
 * Normalize phone number for comparison
 */
function normalizePhoneNumber(string $phone): string {
    $digits = preg_replace('/\D+/', '', $phone);
    if (strpos($digits, '254') === 0) {
        $digits = '0' . substr($digits, 3);
    }
    return $digits;
}

/**
 * Extract provider reference from PayHero response
 */
function extractProviderReference(array $response): ?string {
    $candidates = [
        $response['provider_reference'] ?? null,
        $response['transaction_reference'] ?? null,
        $response['CheckoutRequestID'] ?? null,
        $response['external_reference'] ?? null,
    ];

    if (isset($response['payload']) && is_array($response['payload'])) {
        $payload = $response['payload'];
        $candidates = array_merge($candidates, [
            $payload['provider_reference'] ?? null,
            $payload['transaction_reference'] ?? null,
            $payload['CheckoutRequestID'] ?? null,
            $payload['external_reference'] ?? null,
        ]);
    }

    foreach ($candidates as $candidate) {
        $candidate = trim((string) $candidate);
        if ($candidate !== '') {
            return $candidate;
        }
    }

    return null;
}

function manualOverrideDonationStatus(int $donationId, string $newStatus, ?string $providerRef = null, ?string $note = null): array {
    ensureDonationsTablesInitialized();
    global $pdo;

    $allowed = ['pending','initiated','processing','completed','failed','cancelled'];
    $newStatus = strtolower(trim($newStatus));
    if (!in_array($newStatus, $allowed, true)) {
        return ['success' => false, 'message' => 'Invalid status value.'];
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM donations WHERE id = ? LIMIT 1");
        $stmt->execute([$donationId]);
        $donation = $stmt->fetch();
        if (!$donation) {
            return ['success' => false, 'message' => 'Donation not found.'];
        }

        $pdo->prepare("UPDATE donations SET status = ?, provider_reference = COALESCE(?, provider_reference), updated_at = CURRENT_TIMESTAMP WHERE id = ?")
            ->execute([$newStatus, ($providerRef && trim($providerRef) !== '' ? trim($providerRef) : null), $donationId]);

        $payload = ['note' => $note, 'previous_status' => $donation['status'], 'provider_reference' => $providerRef];
        recordDonationEvent((int) $donationId, $donation['provider'], 'manual_override', $payload, $newStatus);

        return ['success' => true, 'message' => 'Donation updated by manual override.'];
    } catch (Exception $e) {
        error_log('Manual override failed: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to override donation status.'];
    }
}

function handleDonationForm(): ?array {
    if (!isset($_POST['action']) || $_POST['action'] !== 'donate') {
        return null;
    }

    global $pdo;
    ensureDonationsTablesInitialized();

    $name = sanitizeInput($_POST['donor_name'] ?? '');
    $email = sanitizeInput($_POST['donor_email'] ?? '');
    $phone = sanitizeInput($_POST['donor_phone'] ?? '');
    $amount = sanitizeInput($_POST['amount'] ?? '');
    $method = sanitizeInput($_POST['payment_method'] ?? '');
    $channel = sanitizeInput($_POST['mpesa_channel'] ?? '');

    if (empty($name) || empty($phone) || empty($amount) || empty($method)) {
        return ['success' => false, 'message' => 'Please complete all required donation fields.'];
    }

    if (!is_numeric($amount) || (float) $amount < 1) {
        return ['success' => false, 'message' => 'Donation amount must be a valid number greater than zero.'];
    }

    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Please enter a valid email address.'];
    }

    if (!in_array($method, ['mpesa', 'payhero'], true)) {
        return ['success' => false, 'message' => 'Select a valid payment method.'];
    }

    $reference = 'TYI' . date('YmdHis') . rand(100, 999);
    $currency = PAYHERO_CURRENCY ?: 'KES';

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO donations (donor_name, donor_email, donor_phone, amount, currency, provider, provider_channel, status, reference)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?)"
        );
        $stmt->execute([
            $name,
            $email ?: null,
            $phone,
            number_format((float) $amount, 2, '.', ''),
            $currency,
            $method,
            $method === 'mpesa' ? ($channel ?: 'till') : null,
            $reference,
        ]);

        $donationId = (int) $pdo->lastInsertId();
    } catch (Exception $e) {
        error_log('Insert donation failed: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Unable to create donation record. Please try again later.'];
    }

    if ($method === 'mpesa') {
        $response = MpesaService::initiateStkPush([
            'Amount' => (float) $amount,
            'PhoneNumber' => $phone,
            'AccountReference' => $reference,
            'TransactionDesc' => 'Donation',
        ]);

        recordDonationEvent($donationId, 'mpesa', 'stk_request', $response, $response['success'] ? 'initiated' : 'failed');

        if ($response['success']) {
            try {
                $update = $pdo->prepare("UPDATE donations SET status = 'initiated', provider_reference = ? WHERE id = ?");
                $checkoutRequestId = $response['response']['CheckoutRequestID'] ?? null;
                $update->execute([$checkoutRequestId, $donationId]);
            } catch (Exception $e) {
                error_log('Failed updating donation reference: ' . $e->getMessage());
            }

            return [
                'success' => true,
                'message' => 'M-Pesa STK push sent. Please check your phone to complete the donation.',
                'reference' => $reference,
            ];
        }

        return [
            'success' => false,
            'message' => $response['message'] ?? 'Failed to initiate M-Pesa payment.',
            'reference' => $reference,
        ];
    }

    // PayHero flow
    $response = PayheroService::initiateBankCollection([
        'amount' => (float) $amount,
        'reference' => $reference,
        'phone' => $phone,
        'name' => $name,
        'email' => $email,
    ]);

    recordDonationEvent(
        $donationId,
        'payhero',
        'init_request',
        $response,
        $response['response']['status'] ?? ($response['status'] ?? ($response['success'] ? 'initiated' : 'failed'))
    );

    if ($response['success']) {
        try {
            $pdo->prepare("UPDATE donations SET status = 'processing', provider_reference = ? WHERE id = ?")
                ->execute([$response['transaction_reference'] ?? null, $donationId]);
        } catch (Exception $e) {
            error_log('Failed updating PayHero donation reference: ' . $e->getMessage());
        }

        return [
            'success' => true,
            'message' => 'PayHero request received. We will confirm your donation shortly.',
            'reference' => $reference,
        ];
    }

    return [
        'success' => false,
        'message' => $response['message'] ?? 'Failed to initiate PayHero collection.',
        'reference' => $reference,
    ];
}

function handleDonationCallback(string $provider): array {
    ensureDonationsTablesInitialized();
    global $pdo;

    $payloadRaw = file_get_contents('php://input');
    $payload = json_decode($payloadRaw, true);
    if (!is_array($payload)) {
        $payload = ['raw' => $payloadRaw];
    }

    $status = 'failed';
    $donationId = null;

    try {
        if ($provider === 'mpesa' && isset($payload['Body']['stkCallback'])) {
            $callback = $payload['Body']['stkCallback'];
            $resultCode = (int) ($callback['ResultCode'] ?? -1);
            $status = $resultCode === 0 ? 'completed' : 'failed';
            $checkoutRequestId = $callback['CheckoutRequestID'] ?? null;
            $merchantRequestId = $callback['MerchantRequestID'] ?? null;

            $receipt = null;
            if (!empty($callback['CallbackMetadata']['Item']) && is_array($callback['CallbackMetadata']['Item'])) {
                foreach ($callback['CallbackMetadata']['Item'] as $item) {
                    if (($item['Name'] ?? '') === 'MpesaReceiptNumber') {
                        $receipt = $item['Value'] ?? null;
                        break;
                    }
                }
            }

            $lookupValues = array_filter([$checkoutRequestId, $merchantRequestId]);
            foreach ($lookupValues as $value) {
                $stmt = $pdo->prepare("SELECT id FROM donations WHERE provider = 'mpesa' AND (provider_reference = ? OR reference = ?) LIMIT 1");
                $stmt->execute([$value, $value]);
                $foundId = $stmt->fetchColumn();
                if ($foundId) {
                    $donationId = (int) $foundId;
                    break;
                }
            }

            if ($donationId) {
                $meta = ['callback' => $callback];
                if ($receipt) {
                    $meta['receipt'] = $receipt;
                }
                if ($checkoutRequestId) {
                    $meta['checkout_request_id'] = $checkoutRequestId;
                }

                $metaJson = json_encode($meta, JSON_UNESCAPED_UNICODE);

                if ($receipt) {
                    $stmt = $pdo->prepare("UPDATE donations SET status = ?, provider_reference = ?, meta = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                    $stmt->execute([$status, $receipt, $metaJson, $donationId]);
                } else {
                    $stmt = $pdo->prepare("UPDATE donations SET status = ?, meta = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                    $stmt->execute([$status, $metaJson, $donationId]);
                }
            }
        } elseif ($provider === 'payhero' && (isset($payload['transaction_reference']) || isset($payload['reference']) || isset($payload['CheckoutRequestID']) || isset($payload['MerchantRequestID']) || isset($payload['TransactionReceipt']))) {
            error_log('PayHero Callback Payload: ' . print_r($payload, true));

            $statusMap = [
                'success' => 'completed',
                'completed' => 'completed',
                'successful' => 'completed',
                'paid' => 'completed',
                'processing' => 'processing',
                'queued' => 'processing',
                'pending' => 'processing',
                'failed' => 'failed',
                'rejected' => 'failed',
                'declined' => 'failed',
                'error' => 'failed',
                'cancelled' => 'cancelled',
                'canceled' => 'cancelled',
                'timeout' => 'cancelled',
            ];

            $rawStatus = null;
            if (isset($payload['status'])) {
                $rawStatus = $payload['status'];
            } elseif (isset($payload['Status'])) {
                $rawStatus = $payload['Status'];
            } elseif (isset($payload['response']['Status'])) {
                $rawStatus = $payload['response']['Status'];
            } elseif (isset($payload['ResponseCode'])) {
                $rawStatus = $payload['ResponseCode'];
            } elseif (isset($payload['ResultCode'])) {
                $rawStatus = $payload['ResultCode'];
            } elseif (array_key_exists('success', $payload)) {
                $rawStatus = ($payload['success'] === true) ? 'success' : 'failed';
            }

            $incomingStatus = strtolower(trim((string) $rawStatus));

            if ($incomingStatus !== '' && is_numeric($incomingStatus)) {
                $code = (int) $incomingStatus;
                $incomingStatus = ($code === 0) ? 'success' : 'failed';
            }

            $status = $statusMap[$incomingStatus] ?? 'processing';
            error_log("PayHero Status: $incomingStatus -> $status");

            $lookupValues = array_values(array_filter([
    // Strongest identifiers first
    $payload['TransactionReceipt'] ?? null,
    $payload['transaction_reference'] ?? null,
    $payload['CheckoutRequestID'] ?? null,
    $payload['MerchantRequestID'] ?? null,
    $payload['ReferenceNumber'] ?? null,
    // External or generic references
    $payload['reference'] ?? null,
    $payload['external_reference'] ?? null,
    $payload['ExternalReference'] ?? null,
    // Response nested variants
    $payload['response']['TransactionReceipt'] ?? null,
    $payload['response']['transaction_reference'] ?? null,
    $payload['response']['CheckoutRequestID'] ?? null,
    $payload['response']['MerchantRequestID'] ?? null,
    $payload['response']['ReferenceNumber'] ?? null,
    $payload['response']['reference'] ?? null,
    $payload['response']['external_reference'] ?? null,
    $payload['response']['ExternalReference'] ?? null,
], function ($v) { return trim((string) $v) !== ''; }));

error_log('Looking up donation with values: ' . print_r($lookupValues, true));

foreach ($lookupValues as $value) {
    $value = trim((string) $value);
    if ($value === '') { continue; }
    $stmt = $pdo->prepare("SELECT id FROM donations WHERE provider = 'payhero' AND (provider_reference = ? OR reference = ?) LIMIT 1");
    $stmt->execute([$value, $value]);
    $foundId = $stmt->fetchColumn();
    if ($foundId) {
        $donationId = (int) $foundId;
        break;
    }
}
            if ($donationId) {
                $meta = ['callback' => $payload];

                $providerReference = $payload['TransactionReceipt'] ??
                    $payload['transaction_reference'] ??
                    $payload['CheckoutRequestID'] ??
                    $payload['reference'] ??
                    ($payload['response']['TransactionReceipt'] ?? null) ??
                    ($payload['response']['CheckoutRequestID'] ?? null) ??
                    ($payload['response']['transaction_reference'] ?? null);

                $metaJson = json_encode($meta, JSON_UNESCAPED_UNICODE);

                error_log("Updating donation $donationId to status: $status, provider_ref: $providerReference");

                if ($providerReference) {
                    $stmt = $pdo->prepare("UPDATE donations SET status = ?, provider_reference = ?, meta = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                    $result = $stmt->execute([$status, $providerReference, $metaJson, $donationId]);
                } else {
                    $stmt = $pdo->prepare("UPDATE donations SET status = ?, meta = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                    $result = $stmt->execute([$status, $metaJson, $donationId]);
                }

                $affectedRows = $stmt->rowCount();
                error_log("Update result - Rows affected: $affectedRows, Success: " . ($result ? 'yes' : 'no'));
            }
        }

        if ($donationId) {
            recordDonationEvent($donationId, $provider, 'callback', $payload, $status);
            return ['success' => true, 'status' => $status];
        }
    } catch (Exception $e) {
        error_log('Donation callback handling failed: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Error processing callback'];
    }

    return ['success' => false, 'message' => 'Donation not found', 'status' => $status];
}

class MpesaService
{
    private const BASE_URLS = [
        'sandbox' => 'https://sandbox.safaricom.co.ke',
        'production' => 'https://api.safaricom.co.ke',
    ];

    private static function isConfigured(): bool
    {
        return !empty(MPESA_CONSUMER_KEY) && !empty(MPESA_CONSUMER_SECRET) && !empty(MPESA_SHORTCODE) && !empty(MPESA_PASSKEY);
    }

    public static function getAccessToken(): array
    {
        if (!self::isConfigured()) {
            return ['success' => false, 'message' => 'M-Pesa credentials are not configured.'];
        }

        $env = MPESA_ENV === 'production' ? 'production' : 'sandbox';
        $url = self::BASE_URLS[$env] . '/oauth/v1/generate?grant_type=client_credentials';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json; charset=utf-8'],
            CURLOPT_USERPWD => MPESA_CONSUMER_KEY . ':' . MPESA_CONSUMER_SECRET,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlError) {
            return ['success' => false, 'message' => 'cURL error: ' . $curlError];
        }

        if ($httpCode !== 200) {
            return ['success' => false, 'message' => 'Access token request failed with status ' . $httpCode];
        }

        $data = json_decode($response, true);
        if (!isset($data['access_token'])) {
            return ['success' => false, 'message' => 'Access token missing in response.'];
        }

        return ['success' => true, 'token' => $data['access_token']];
    }

    public static function initiateStkPush(array $payload): array
    {
        $required = ['Amount', 'PhoneNumber', 'AccountReference', 'TransactionDesc'];
        foreach ($required as $field) {
            if (empty($payload[$field])) {
                return ['success' => false, 'message' => 'Missing parameter: ' . $field];
            }
        }

        $tokenResponse = self::getAccessToken();
        if (!$tokenResponse['success']) {
            return $tokenResponse;
        }

        $env = MPESA_ENV === 'production' ? 'production' : 'sandbox';
        $url = self::BASE_URLS[$env] . '/mpesa/stkpush/v1/processrequest';

        $timestamp = date('YmdHis');
        $password = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);

        $body = [
            'BusinessShortCode' => MPESA_SHORTCODE,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => (int) $payload['Amount'],
            'PartyA' => $payload['PhoneNumber'],
            'PartyB' => MPESA_SHORTCODE,
            'PhoneNumber' => $payload['PhoneNumber'],
            'CallBackURL' => MPESA_CALLBACK_URL,
            'AccountReference' => substr($payload['AccountReference'], 0, 12),
            'TransactionDesc' => substr($payload['TransactionDesc'], 0, 13),
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $tokenResponse['token'],
            ],
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlError) {
            return ['success' => false, 'message' => 'cURL error: ' . $curlError];
        }

        $data = json_decode($response, true) ?: [];
        if ($httpCode !== 200) {
            return ['success' => false, 'message' => 'STK push request failed with status ' . $httpCode, 'response' => $data];
        }

        if (($data['ResponseCode'] ?? '') !== '0') {
            return ['success' => false, 'message' => $data['ResponseDescription'] ?? 'Unexpected response', 'response' => $data];
        }

        return [
            'success' => true,
            'response' => $data,
        ];
    }
}

class PayheroService
{
    public static function isConfigured(): bool
    {
        return !empty(PAYHERO_BASE_URL) && !empty(PAYHERO_API_KEY) && !empty(PAYHERO_SECRET_KEY) && !empty(PAYHERO_ACCOUNT_ID) && !empty(PAYHERO_CHANNEL_ID);
    }

    private static function buildBasicAuthHeader(): string
    {
        return 'Basic ' . base64_encode(PAYHERO_API_KEY . ':' . PAYHERO_SECRET_KEY);
    }

    private static function sanitizePhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($digits, '254')) {
            $digits = '0' . substr($digits, 3);
        } elseif (!str_starts_with($digits, '0') && strlen($digits) >= 9) {
            $digits = '0' . $digits;
        }

        return $digits;
    }

    public static function fetchTransactionStatus(string $reference): array
    {
        if (!self::isConfigured()) {
            return ['success' => false, 'message' => 'PayHero credentials are not configured.'];
        }

        $reference = trim($reference);
        if ($reference === '') {
            return ['success' => false, 'message' => 'Missing reference for status check.'];
        }

        $baseUrl = rtrim(PAYHERO_BASE_URL, '/') . '/transaction-status';
        $attempts = [
            // Use only collision-resistant keys
            ['key' => 'transaction_reference', 'value' => $reference],
            ['key' => 'CheckoutRequestID', 'value' => $reference],
            ['key' => 'checkout_request_id', 'value' => $reference],
        ];

        $errors = [];
        $lastMessage = null;
        $seenKeys = [];

        foreach ($attempts as $attempt) {
            $key = $attempt['key'];
            if (isset($seenKeys[$key])) {
                continue;
            }
            $seenKeys[$key] = true;

            $url = $baseUrl . '?' . http_build_query([
                $key => $attempt['value'],
                'channel_id' => PAYHERO_CHANNEL_ID,
                'account_id' => PAYHERO_ACCOUNT_ID,
            ]);

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json',
                    'Authorization: ' . self::buildBasicAuthHeader(),
                    'X-Channel-Id: ' . PAYHERO_CHANNEL_ID,
                    'X-Account-Id: ' . PAYHERO_ACCOUNT_ID,
                ],
                CURLOPT_TIMEOUT => 20,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);

            $response = curl_exec($ch);
            $curlError = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($curlError) {
                error_log('PayHero status cURL error: ' . $curlError);
                return ['success' => false, 'message' => 'Unable to reach PayHero: ' . $curlError];
            }

            $data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('PayHero status invalid JSON: ' . $response);
                return ['success' => false, 'message' => 'Invalid response from PayHero.'];
            }

            if ($httpCode >= 400) {
                $message = $data['message'] ?? ($data['error'] ?? 'PayHero status request failed');
                $errorDetails = [
                    'query_param' => $key,
                    'message' => $message,
                    'payload' => $data,
                    'http_code' => $httpCode,
                ];
                $errors[] = $errorDetails;
                $lastMessage = $message;

                if ($httpCode !== 404) {
                    return [
                        'success' => false,
                        'message' => $message,
                        'payload' => $data,
                        'http_code' => $httpCode,
                        'attempts' => $errors,
                    ];
                }

                continue;
            }

            $status = strtolower((string) ($data['status'] ?? ''));
            $providerReference = $data['provider_reference'] ?? ($data['CheckoutRequestID'] ?? null);

            return [
                'success' => true,
                'status' => $status,
                'raw_status' => $data['status'] ?? null,
                'provider_reference' => $providerReference,
                'reference' => $data['reference'] ?? null,
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'CheckoutRequestID' => $data['CheckoutRequestID'] ?? ($data['checkout_request_id'] ?? null),
                'external_reference' => $data['external_reference'] ?? null,
                'used_query_param' => $key,
                'payload' => $data,
                'http_code' => $httpCode,
            ];
        }

        return [
            'success' => false,
            'message' => $lastMessage ?? 'PayHero status request failed',
            'payload' => ['attempts' => $errors],
            'http_code' => $errors ? ($errors[count($errors) - 1]['http_code'] ?? null) : null,
            'attempts' => $errors,
        ];
    }

    public static function initiateBankCollection(array $payload): array
    {
        if (!self::isConfigured()) {
            return ['success' => false, 'message' => 'PayHero credentials are not configured.'];
        }

        $required = ['amount', 'reference'];
        foreach ($required as $field) {
            if (empty($payload[$field])) {
                return ['success' => false, 'message' => 'Missing parameter: ' . $field];
            }
        }

        $phone = self::sanitizePhone($payload['phone'] ?? null);
        $reference = substr((string) $payload['reference'], 0, 64);

        $url = rtrim(PAYHERO_BASE_URL, '/') . '/payments';
        $body = [
            'amount' => (float) $payload['amount'],
            'phone_number' => $phone,
            'channel_id' => (int) PAYHERO_CHANNEL_ID,
            'provider' => PAYHERO_PROVIDER,
            'external_reference' => $reference,
            'customer_name' => $payload['name'] ?? null,
            'customer_email' => $payload['email'] ?? null,
            'callback_url' => PAYHERO_CALLBACK_URL,
        ];

        $headers = [
            'Content-Type: application/json',
            'Authorization: ' . self::buildBasicAuthHeader(),
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlError) {
            error_log('PayHero request cURL error: ' . $curlError);
            return ['success' => false, 'message' => 'Unable to reach PayHero: ' . $curlError];
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('PayHero invalid JSON response: ' . $response);
            return ['success' => false, 'message' => 'Invalid response from PayHero.'];
        }

        if ($httpCode >= 400) {
            $errorMessage = $data['message'] ?? ($data['error'] ?? ($data['errors'][0] ?? 'PayHero request failed'));
            return ['success' => false, 'message' => $errorMessage, 'response' => $data];
        }

        $transactionReference = $data['transaction_reference']
            ?? $data['CheckoutRequestID']
            ?? $data['checkout_request_id']
            ?? null;

        if (!$transactionReference) {
            return ['success' => false, 'message' => 'Missing transaction reference from PayHero.', 'response' => $data];
        }

        return [
            'success' => true,
            'transaction_reference' => $transactionReference,
            'response' => $data,
        ];
    }
}
?>