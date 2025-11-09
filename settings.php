<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!function_exists('ensureSiteSettingsTableExists')) {
    function ensureSiteSettingsTableExists(): void
    {
        static $initialized = false;
        if ($initialized) {
            return;
        }

        global $pdo;
        try {
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS settings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    setting_key VARCHAR(120) NOT NULL UNIQUE,
                    setting_value MEDIUMTEXT NULL,
                    group_key VARCHAR(60) DEFAULT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            );
        } catch (Exception $e) {
            error_log('Failed to ensure settings table exists: ' . $e->getMessage());
        }

        $initialized = true;
    }
}

requireAuth();
ensureSiteSettingsTableExists();

$adminId = $_SESSION['admin_id'] ?? null;

$settingDefinitions = [
    'general' => [
        'title' => 'General Site Information',
        'description' => 'Edit the core details that appear across the Timiza Youth Initiative site.',
        'icon' => 'fa-earth-africa',
        'fields' => [
            'site_name' => ['label' => 'Site Name', 'type' => 'text', 'required' => true, 'placeholder' => 'Timiza Youth Initiative'],
            'site_tagline' => ['label' => 'Tagline', 'type' => 'text', 'placeholder' => 'Empowering youth across Kilifi County'],
            'support_email' => ['label' => 'Primary Contact Email', 'type' => 'email', 'required' => true, 'placeholder' => 'info@timizayouth.org'],
            'support_phone' => ['label' => 'Primary Contact Phone', 'type' => 'text', 'placeholder' => '+254 700 000 000'],
            'physical_address' => ['label' => 'Physical Address', 'type' => 'textarea', 'placeholder' => "P.O. Box 1234-80100\nKilifi, Kenya"],
            'mission_statement' => ['label' => 'Short Mission Statement', 'type' => 'textarea', 'placeholder' => 'Share a short sentence about your mission for quick references.']
        ]
    ],
    'contact' => [
        'title' => 'Contact & Footer Details',
        'description' => 'Control the information that appears on the contact page and footer.',
        'icon' => 'fa-address-book',
        'fields' => [
            'contact_email' => ['label' => 'Contact Page Email', 'type' => 'email', 'placeholder' => 'reachout@timizayouth.org'],
            'contact_phone' => ['label' => 'Contact Page Phone', 'type' => 'text', 'placeholder' => '+254 711 222 333'],
            'office_hours' => ['label' => 'Office Hours', 'type' => 'text', 'placeholder' => 'Mon – Fri, 8:30 AM – 5:00 PM'],
            'map_embed_url' => ['label' => 'Google Maps Embed URL', 'type' => 'url', 'placeholder' => 'https://www.google.com/maps/embed?...']
        ]
    ],
    'social' => [
        'title' => 'Social & Outreach Links',
        'description' => 'Keep your community connected by sharing your official profiles.',
        'icon' => 'fa-hashtag',
        'fields' => [
            'facebook_url' => ['label' => 'Facebook Page', 'type' => 'url', 'placeholder' => 'https://facebook.com/timizayouth'],
            'instagram_url' => ['label' => 'Instagram Profile', 'type' => 'url', 'placeholder' => 'https://instagram.com/timizayouth'],
            'twitter_url' => ['label' => 'Twitter / X Profile', 'type' => 'url', 'placeholder' => 'https://twitter.com/timizayouth'],
            'youtube_url' => ['label' => 'YouTube Channel', 'type' => 'url', 'placeholder' => 'https://youtube.com/@timizayouth'],
            'linkedin_url' => ['label' => 'LinkedIn Page', 'type' => 'url', 'placeholder' => 'https://linkedin.com/company/timizayouth']
        ]
    ],
    'engagement' => [
        'title' => 'Engagement Links',
        'description' => 'Configure quick links used throughout the site for donations or volunteer sign-ups.',
        'icon' => 'fa-heart-circle-plus',
        'fields' => [
            'donation_link' => ['label' => 'Primary Donation Link', 'type' => 'url', 'placeholder' => 'https://timizayouthsinitiative.kesug.com/donate'],
            'volunteer_link' => ['label' => 'Volunteer Sign-up Link', 'type' => 'url', 'placeholder' => 'https://timizayouthsinitiative.kesug.com/get-involved#volunteer-form'],
            'press_kit_link' => ['label' => 'Press Kit / Media Pack Link', 'type' => 'url', 'placeholder' => 'https://drive.google.com/...']
        ]
    ]
];

$defaultValues = [
    'site_name' => defined('SITE_NAME') ? SITE_NAME : 'Timiza Youth Initiative',
    'support_email' => defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'info@timizayouth.org',
    'support_phone' => '+254 700 000 000',
    'site_tagline' => 'Empowering youth across Kilifi County',
    'mission_statement' => 'We empower young people to lead change through education, health, climate action, and peace-building.',
    'physical_address' => "P.O. Box 1234-80100\nKilifi, Kenya",
    'contact_email' => 'reachout@timizayouth.org',
    'contact_phone' => '+254 711 222 333',
    'office_hours' => 'Mon – Fri, 8:30 AM – 5:00 PM',
    'map_embed_url' => '',
    'facebook_url' => '',
    'instagram_url' => '',
    'twitter_url' => '',
    'youtube_url' => '',
    'linkedin_url' => '',
    'donation_link' => defined('SITE_URL') ? SITE_URL . '/donate' : '',
    'volunteer_link' => defined('SITE_URL') ? SITE_URL . '/get-involved#volunteer-form' : '',
    'press_kit_link' => ''
];

$currentSettings = [];
$lastUpdated = null;
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    foreach ($stmt->fetchAll() as $row) {
        $currentSettings[$row['setting_key']] = $row['setting_value'];
    }

    $timestampStmt = $pdo->query("SELECT MAX(updated_at) AS last_updated FROM settings");
    $lastRow = $timestampStmt->fetch();
    if (!empty($lastRow['last_updated'])) {
        $lastUpdated = $lastRow['last_updated'];
    }
} catch (Exception $e) {
    error_log('Unable to fetch settings: ' . $e->getMessage());
}

$formData = [];
foreach ($settingDefinitions as $groupKey => $group) {
    foreach ($group['fields'] as $key => $field) {
        $formData[$key] = $currentSettings[$key] ?? $defaultValues[$key] ?? '';
    }
}

$message = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_settings') {
    $postedValues = [];

    foreach ($settingDefinitions as $groupKey => $group) {
        foreach ($group['fields'] as $key => $field) {
            $rawValue = $_POST[$key] ?? '';
            $cleanValue = trim(is_array($rawValue) ? '' : (string) $rawValue);

            if (!empty($field['required']) && $cleanValue === '') {
                $errors[$key] = $field['label'] . ' is required.';
            }

            if ($cleanValue !== '') {
                if (($field['type'] ?? '') === 'email' && !filter_var($cleanValue, FILTER_VALIDATE_EMAIL)) {
                    $errors[$key] = 'Please provide a valid email address.';
                }

                if (($field['type'] ?? '') === 'url' && !filter_var($cleanValue, FILTER_VALIDATE_URL)) {
                    $errors[$key] = 'Please provide a valid URL (including https://).';
                }
            }

            $postedValues[$key] = $cleanValue;
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            $insertStmt = $pdo->prepare(
                "INSERT INTO settings (setting_key, setting_value, group_key, created_at, updated_at)
                 VALUES (?, ?, ?, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), group_key = VALUES(group_key), updated_at = NOW()"
            );

            foreach ($settingDefinitions as $groupKey => $group) {
                foreach ($group['fields'] as $key => $field) {
                    $insertStmt->execute([
                        $key,
                        $postedValues[$key],
                        $groupKey
                    ]);
                }
            }

            $pdo->commit();
            header('Location: settings.php?status=saved');
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log('Failed to save settings: ' . $e->getMessage());
            $message = ['success' => false, 'text' => 'We were unable to save your settings. Please try again.'];
        }
    } else {
        $message = ['success' => false, 'text' => 'Please review the highlighted fields and try again.'];
    }

    $formData = array_merge($formData, $postedValues);
}

$statusMessages = [
    'saved' => ['success' => true, 'text' => 'Settings updated successfully.']
];

if (!$message && isset($_GET['status']) && isset($statusMessages[$_GET['status']])) {
    $message = $statusMessages[$_GET['status']];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Timiza Youth Initiative</title>
    <link rel="icon" href="../assets/images/favicon.ico" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --color-general: #047857;
            --color-general-light: #dcfce7;
            --color-contact: #0284c7;
            --color-contact-light: #bae6fd;
            --color-social: #c026d3;
            --color-social-light: #f5d0fe;
            --color-engagement: #f97316;
            --color-engagement-light: #fed7aa;
            --color-action-primary: #2563eb;
            --color-action-secondary: #0ea5e9;
            --color-action-tertiary: #22c55e;
        }

        body.settings-page {
            background: linear-gradient(155deg, #ecfccb 0%, #cffafe 45%, #ede9fe 100%);
            min-height: 100vh;
        }

        .settings-hero-highlight {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.5rem 1.1rem;
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.25), rgba(14, 165, 233, 0.25));
            color: #0f172a;
            font-weight: 600;
            font-size: 0.85rem;
            box-shadow: 0 18px 36px -28px rgba(14, 116, 144, 0.55);
        }

        .settings-hero-highlight i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.75rem;
            height: 1.75rem;
            border-radius: 50%;
            background: rgba(15, 118, 110, 0.18);
            color: #047857;
        }

        .quick-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            background: #ffffff;
            padding: 0.75rem 1rem;
            border-radius: 999px;
            border: 1px solid rgba(148, 163, 184, 0.24);
            box-shadow: 0 20px 40px -28px rgba(15, 118, 110, 0.5);
        }

        .quick-nav a {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.55rem 1.2rem;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            color: #0f172a;
            background: #f8fafc;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .quick-nav a .icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.75rem;
            height: 1.75rem;
            border-radius: 999px;
            background: rgba(15, 118, 110, 0.12);
            color: #0f766e;
        }

        .quick-nav a:hover,
        .quick-nav a:focus {
            transform: translateY(-2px);
            box-shadow: 0 18px 32px -24px rgba(14, 116, 144, 0.45);
        }

        .quick-nav a:focus {
            outline: 2px solid rgba(14, 165, 233, 0.45);
            outline-offset: 2px;
        }

        .quick-nav a[data-section="general"] {
            background: var(--color-general-light);
            color: var(--color-general);
        }

        .quick-nav a[data-section="general"] .icon {
            background: rgba(34, 197, 94, 0.18);
            color: var(--color-general);
        }

        .quick-nav a[data-section="contact"] {
            background: var(--color-contact-light);
            color: var(--color-contact);
        }

        .quick-nav a[data-section="contact"] .icon {
            background: rgba(14, 165, 233, 0.2);
            color: var(--color-contact);
        }

        .quick-nav a[data-section="social"] {
            background: var(--color-social-light);
            color: var(--color-social);
        }

        .quick-nav a[data-section="social"] .icon {
            background: rgba(216, 180, 254, 0.35);
            color: var(--color-social);
        }

        .quick-nav a[data-section="engagement"] {
            background: var(--color-engagement-light);
            color: var(--color-engagement);
        }

        .quick-nav a[data-section="engagement"] .icon {
            background: rgba(251, 191, 36, 0.3);
            color: var(--color-engagement);
        }

        .settings-card {
            position: relative;
            background: #ffffff;
            border-radius: 1.5rem;
            border: 1px solid rgba(148, 163, 184, 0.24);
            box-shadow: 0 28px 45px -30px rgba(15, 23, 42, 0.28);
            padding: 2rem;
            overflow: hidden;
            isolation: isolate;
        }

        .settings-card > * {
            position: relative;
            z-index: 1;
        }

        .settings-card::after {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            border-radius: inherit;
            background: radial-gradient(circle at top left, rgba(15, 118, 110, 0.12), transparent 60%);
            z-index: 0;
        }

        .settings-card[data-section="general"] {
            border-left: 6px solid var(--color-general);
            box-shadow: 0 24px 50px -30px rgba(34, 197, 94, 0.4);
        }

        .settings-card[data-section="general"]::after {
            background: radial-gradient(circle at top left, rgba(74, 222, 128, 0.22), transparent 65%);
        }

        .settings-card[data-section="general"] .section-header .icon {
            background: rgba(34, 197, 94, 0.18);
            color: var(--color-general);
        }

        .settings-card[data-section="contact"] {
            border-left: 6px solid var(--color-contact);
            box-shadow: 0 24px 50px -30px rgba(14, 165, 233, 0.4);
        }

        .settings-card[data-section="contact"]::after {
            background: radial-gradient(circle at top left, rgba(56, 189, 248, 0.2), transparent 65%);
        }

        .settings-card[data-section="contact"] .section-header .icon {
            background: rgba(56, 189, 248, 0.2);
            color: var(--color-contact);
        }

        .settings-card[data-section="social"] {
            border-left: 6px solid var(--color-social);
            box-shadow: 0 24px 50px -30px rgba(217, 70, 239, 0.35);
        }

        .settings-card[data-section="social"]::after {
            background: radial-gradient(circle at top left, rgba(244, 114, 182, 0.22), transparent 65%);
        }

        .settings-card[data-section="social"] .section-header .icon {
            background: rgba(217, 70, 239, 0.2);
            color: var(--color-social);
        }

        .settings-card[data-section="engagement"] {
            border-left: 6px solid var(--color-engagement);
            box-shadow: 0 24px 50px -30px rgba(248, 153, 0, 0.35);
        }

        .settings-card[data-section="engagement"]::after {
            background: radial-gradient(circle at top left, rgba(251, 191, 36, 0.24), transparent 65%);
        }

        .settings-card[data-section="engagement"] .section-header .icon {
            background: rgba(251, 191, 36, 0.22);
            color: var(--color-engagement);
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .section-header .icon {
            height: 2.75rem;
            width: 2.75rem;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.4);
        }

        .section-header h2 {
            font-size: 1.35rem;
            font-weight: 600;
            color: #0f172a;
        }

        .section-description {
            color: #475569;
            font-size: 0.96rem;
            margin-top: 0.35rem;
        }

        .field-group {
            display: grid;
            gap: 1.2rem;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .field label {
            font-weight: 600;
            color: #0f172a;
            font-size: 0.92rem;
        }

        .field input,
        .field textarea {
            border-radius: 0.9rem;
            border: 1px solid rgba(148, 163, 184, 0.28);
            padding: 0.9rem 1.1rem;
            font-size: 0.95rem;
            color: #0f172a;
            background: #ffffff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        .field input::placeholder,
        .field textarea::placeholder {
            color: rgba(100, 116, 139, 0.6);
        }

        .field input:focus,
        .field textarea:focus {
            outline: none;
            border-color: rgba(14, 165, 233, 0.5);
            box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.15);
            transform: translateY(-1px);
        }

        .field textarea {
            min-height: 130px;
            resize: vertical;
        }

        .field-error input,
        .field-error textarea {
            border-color: rgba(239, 68, 68, 0.6);
            box-shadow: 0 0 0 4px rgba(248, 113, 113, 0.15);
        }

        .field-error .error-text {
            color: #b91c1c;
            font-size: 0.82rem;
            font-weight: 500;
        }

            flex-wrap: wrap;
            gap: 0.75rem;
            background: #ffffff;
            padding: 0.75rem 1rem;
            border-radius: 999px;
            border: 1px solid rgba(148, 163, 184, 0.24);
            box-shadow: 0 20px 40px -28px rgba(15, 118, 110, 0.5);
        }

        .quick-nav a {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.55rem 1.2rem;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            color: #0f172a;
            background: #f8fafc;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .quick-nav a .icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.75rem;
            height: 1.75rem;
            border-radius: 999px;
            background: rgba(15, 118, 110, 0.12);
            color: #0f766e;
        }

        .quick-nav a:hover,
        .quick-nav a:focus {
            transform: translateY(-2px);
            box-shadow: 0 18px 32px -24px rgba(14, 116, 144, 0.45);
        }

        .quick-nav a:focus {
            outline: 2px solid rgba(14, 165, 233, 0.45);
            outline-offset: 2px;
        }

        .quick-nav a[data-section="general"] {
            background: var(--color-general-light);
            color: var(--color-general);
        }

        .quick-nav a[data-section="general"] .icon {
            background: rgba(34, 197, 94, 0.18);
            color: var(--color-general);
        }

        .quick-nav a[data-section="contact"] {
            background: var(--color-contact-light);
            color: var(--color-contact);
        }

        .quick-nav a[data-section="contact"] .icon {
            background: rgba(14, 165, 233, 0.2);
            color: var(--color-contact);
        }

        .quick-nav a[data-section="social"] {
            background: var(--color-social-light);
            color: var(--color-social);
        }

        .quick-nav a[data-section="social"] .icon {
            background: rgba(216, 180, 254, 0.35);
            color: var(--color-social);
        }

        .quick-nav a[data-section="engagement"] {
            background: var(--color-engagement-light);
            color: var(--color-engagement);
        }

        .quick-nav a[data-section="engagement"] .icon {
            background: rgba(251, 191, 36, 0.3);
            color: var(--color-engagement);
        }

        .settings-card {
            position: relative;
            background: #ffffff;
            border-radius: 1.5rem;
            border: 1px solid rgba(148, 163, 184, 0.24);
            box-shadow: 0 28px 45px -30px rgba(15, 23, 42, 0.28);
            padding: 2rem;
            overflow: hidden;
            isolation: isolate;
        }

        .settings-card > * {
            position: relative;
            z-index: 1;
        }

        .settings-card::after {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            border-radius: inherit;
            background: radial-gradient(circle at top left, rgba(15, 118, 110, 0.12), transparent 60%);
            z-index: 0;
        }

        .settings-card[data-section="general"] {
            border-left: 6px solid var(--color-general);
            box-shadow: 0 24px 50px -30px rgba(34, 197, 94, 0.4);
        }

        .settings-card[data-section="general"]::after {
            background: radial-gradient(circle at top left, rgba(74, 222, 128, 0.22), transparent 65%);
        }

        .settings-card[data-section="general"] .section-header .icon {
            background: rgba(34, 197, 94, 0.18);
            color: var(--color-general);
        }

        .settings-card[data-section="contact"] {
            border-left: 6px solid var(--color-contact);
            box-shadow: 0 24px 50px -30px rgba(14, 165, 233, 0.4);
        }

        .settings-card[data-section="contact"]::after {
            background: radial-gradient(circle at top left, rgba(56, 189, 248, 0.2), transparent 65%);
        }

        .settings-card[data-section="contact"] .section-header .icon {
            background: rgba(56, 189, 248, 0.2);
            color: var(--color-contact);
        }

        .settings-card[data-section="social"] {
            border-left: 6px solid var(--color-social);
            box-shadow: 0 24px 50px -30px rgba(217, 70, 239, 0.35);
        }

        .settings-card[data-section="social"]::after {
            background: radial-gradient(circle at top left, rgba(244, 114, 182, 0.22), transparent 65%);
        }

        .settings-card[data-section="social"] .section-header .icon {
            background: rgba(217, 70, 239, 0.2);
            color: var(--color-social);
        }

        .settings-card[data-section="engagement"] {
            border-left: 6px solid var(--color-engagement);
            box-shadow: 0 24px 50px -30px rgba(248, 153, 0, 0.35);
        }

        .settings-card[data-section="engagement"]::after {
            background: radial-gradient(circle at top left, rgba(251, 191, 36, 0.24), transparent 65%);
        }

        .settings-card[data-section="engagement"] .section-header .icon {
            background: rgba(251, 191, 36, 0.22);
            color: var(--color-engagement);
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .section-header .icon {
            height: 2.75rem;
            width: 2.75rem;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.4);
        }

        .section-header h2 {
            font-size: 1.35rem;
            font-weight: 600;
            color: #0f172a;
        }

        .section-description {
            color: #475569;
            font-size: 0.96rem;
            margin-top: 0.35rem;
        }

        .field-group {
            display: grid;
            gap: 1.2rem;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .field label {
            font-weight: 600;
            color: #0f172a;
            font-size: 0.92rem;
        }

        .field input,
        .field textarea {
            border-radius: 0.9rem;
            border: 1px solid rgba(148, 163, 184, 0.28);
            padding: 0.9rem 1.1rem;
            font-size: 0.95rem;
            color: #0f172a;
            background: #ffffff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        .field input::placeholder,
        .field textarea::placeholder {
            color: rgba(100, 116, 139, 0.6);
        }

        .field input:focus,
        .field textarea:focus {
            outline: none;
            border-color: rgba(14, 165, 233, 0.5);
            box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.15);
            transform: translateY(-1px);
        }

        .field textarea {
            min-height: 130px;
            resize: vertical;
        }

        .field-error input,
        .field-error textarea {
            border-color: rgba(239, 68, 68, 0.6);
            box-shadow: 0 0 0 4px rgba(248, 113, 113, 0.15);
        }

        .field-error .error-text {
            color: #b91c1c;
            font-size: 0.82rem;
            font-weight: 500;
        }

        .save-bar {
            background: linear-gradient(120deg, #0ea5e9 0%, #22c55e 100%);
            border-radius: 1.5rem;
            box-shadow: 0 28px 48px -28px rgba(14, 116, 144, 0.55);
            border: none;
            color: #f0fdfa;
        }

        .save-bar__info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .save-bar__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.2rem;
            height: 2.2rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.22);
            color: #0f172a;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.35);
            font-size: 1rem;
        }

        .btn-primary,
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            border-radius: 0.9rem;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 0.85rem 1.6rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, color 0.2s ease;
            text-decoration: none;
            border: none;
            cursor: pointer;
            white-space: nowrap;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--color-action-primary) 0%, var(--color-action-secondary) 45%, var(--color-action-tertiary) 100%);
            color: #ffffff;
            box-shadow: 0 24px 40px -22px rgba(14, 165, 233, 0.65);
            border: none;
        }

        .btn-primary i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.9rem;
            height: 1.9rem;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.12);
            color: #ffffff;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 26px 44px -22px rgba(14, 165, 233, 0.72);
            filter: brightness(1.05);
        }

        .btn-secondary {
            background: #f8fafc;
            color: #0f172a;
            border: 1px solid rgba(148, 163, 184, 0.4);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 28px -20px rgba(30, 64, 175, 0.35);
            background: #e0f2fe;
            color: #0f172a;
        }

        .btn-secondary:focus,
        .btn-primary:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.35);
        }

        @media (min-width: 768px) {
            .field-group {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .quick-nav {
                border-radius: 1.25rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .quick-nav a,
            .field input,
            .field textarea,
            .btn-primary,
            .btn-secondary {
                transition: none;
            }
        }
    </style>
</head>
<body class="font-poppins settings-page min-h-screen">
    <?php include '../includes/nav-admin.php'; ?>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-10">
        <div class="flex flex-col gap-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="space-y-2">
                    <div class="settings-hero-highlight">
                        <i class="fas fa-sliders"></i>
                        <span>Central hub for Timiza’s configuration</span>
                    </div>
                    <div>
                        <p class="text-sm uppercase tracking-[0.4em] text-primary/70 font-semibold">Control Centre</p>
                        <h1 class="text-3xl font-bold text-slate-900">Site & Communication Settings</h1>
                    </div>
                    <p class="text-slate-600 max-w-2xl text-sm sm:text-base">Update the key information that powers your public site, contact pages, and donor outreach. Changes are applied immediately after saving.</p>
                </div>
                <div class="rounded-2xl border border-primary/15 bg-primary/10 px-4 py-3 text-primary text-sm font-semibold flex items-center gap-2">
                    <i class="fas fa-floppy-disk"></i>
                    <span>Remember to hit “Save settings” after making edits</span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <nav class="quick-nav" aria-label="Settings quick navigation">
                    <?php foreach ($settingDefinitions as $groupKey => $group): ?>
                    <a href="#section-<?php echo $groupKey; ?>" data-section="<?php echo $groupKey; ?>">
                        <span class="icon"><i class="fas <?php echo $group['icon']; ?>"></i></span>
                        <span><?php echo htmlspecialchars($group['title']); ?></span>
                    </a>
                    <?php endforeach; ?>
                </nav>
                <?php if ($lastUpdated): ?>
                <div class="text-xs sm:text-sm text-slate-500 bg-white/60 border border-slate-200 rounded-2xl px-4 py-2 shadow-sm">
                    <i class="fas fa-clock text-primary"></i>
                    <span class="ml-2">Last updated: <?php echo formatDate($lastUpdated, 'M j, Y g:i a'); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($message): ?>
            <div class="rounded-2xl border <?php echo $message['success'] ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-rose-50 border-rose-200 text-rose-700'; ?> p-5">
                <div class="flex items-start gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full <?php echo $message['success'] ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600'; ?>">
                        <i class="fas <?php echo $message['success'] ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i>
                    </span>
                    <div>
                        <p class="font-semibold text-sm sm:text-base"><?php echo htmlspecialchars($message['text']); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <form action="settings.php" method="POST" class="space-y-10">
            <input type="hidden" name="action" value="save_settings">

            <?php foreach ($settingDefinitions as $groupKey => $group): ?>
            <section class="settings-card p-7 sm:p-8 space-y-6" id="section-<?php echo $groupKey; ?>" data-section="<?php echo $groupKey; ?>">
                <div class="section-header">
                    <span class="icon"><i class="fas <?php echo $group['icon']; ?>"></i></span>
                    <div>
                        <h2><?php echo htmlspecialchars($group['title']); ?></h2>
                        <p class="section-description"><?php echo htmlspecialchars($group['description']); ?></p>
                    </div>
                </div>

                <div class="field-group">
                    <?php foreach ($group['fields'] as $key => $field): ?>
                    <?php $hasError = isset($errors[$key]); ?>
                    <div class="field <?php echo $hasError ? 'field-error' : ''; ?>">
                        <label for="<?php echo $key; ?>">
                            <?php echo htmlspecialchars($field['label']); ?>
                            <?php if (!empty($field['required'])): ?>
                            <span class="text-rose-500">*</span>
                            <?php endif; ?>
                        </label>
                        <?php if (($field['type'] ?? '') === 'textarea'): ?>
                        <textarea id="<?php echo $key; ?>" name="<?php echo $key; ?>" placeholder="<?php echo htmlspecialchars($field['placeholder'] ?? ''); ?>"><?php echo htmlspecialchars($formData[$key] ?? ''); ?></textarea>
                        <?php else: ?>
                        <input id="<?php echo $key; ?>" type="<?php echo htmlspecialchars($field['type'] ?? 'text'); ?>" name="<?php echo $key; ?>" value="<?php echo htmlspecialchars($formData[$key] ?? ''); ?>" placeholder="<?php echo htmlspecialchars($field['placeholder'] ?? ''); ?>">
                        <?php endif; ?>
                        <?php if ($hasError): ?>
                        <p class="error-text"><?php echo htmlspecialchars($errors[$key]); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endforeach; ?>

            <div class="sticky bottom-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white/80 backdrop-blur-md border border-slate-200 rounded-2xl px-6 py-5 shadow-lg">
                <div class="text-sm text-slate-500 flex items-center gap-2">
                    <i class="fas fa-clock text-primary"></i>
                    <span>All updates are saved instantly and reflected on the public site.</span>
                </div>
                <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                    <a href="settings.php" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-500 hover:border-slate-300">
                        <i class="fas fa-rotate"></i>
                        Reset changes
                    </a>
                   <button 
  type="submit" 
  class="inline-flex items-center justify-center gap-3 rounded-full 
         bg-gradient-to-r from-teal-500 via-green-500 to-blue-500 
         px-8 py-3 text-base font-semibold text-white 
         shadow-lg shadow-teal-400/30 
         transition-all duration-300 
         hover:scale-105 hover:shadow-xl hover:shadow-green-400/40 
         focus:ring-4 focus:ring-teal-300 focus:outline-none">
    <i class="fas fa-floppy-disk text-lg"></i>
    Save Settings
</button>

                </div>
            </div>
        </form>
    </main>
</body>

                </div>
            </div>
        </form>
    </main>
</body>
</html>