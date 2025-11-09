<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!function_exists('ensurePartnersTableExists')) {
    function ensurePartnersTableExists(): void
    {
        static $initialized = false;
        if ($initialized) {
            return;
        }

        global $pdo;
        try {
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS partners (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(150) NOT NULL,
                    category VARCHAR(100) DEFAULT NULL,
                    website VARCHAR(255) DEFAULT NULL,
                    logo VARCHAR(255) DEFAULT NULL,
                    active TINYINT(1) DEFAULT 1,
                    sort_order INT DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            );
        } catch (Exception $e) {
            error_log('Failed to ensure partners table exists: ' . $e->getMessage());
        }

        $initialized = true;
    }
}

if (!function_exists('manage_partners_process_upload')) {
    function manage_partners_process_upload(array $file, string $uploadDirectory, string $relativeDirectory, bool $required = false): array
    {
        if (empty($file) || (!isset($file['name']) && !isset($file['tmp_name'])) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE || empty($file['name'])) {
            if ($required) {
                return ['success' => false, 'error' => 'Please upload a logo for the partner.'];
            }

            return ['success' => true, 'path' => null];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Logo upload failed. Please try again.'];
        }

        $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        $detectedType = @mime_content_type($file['tmp_name']);
        if (!$detectedType || !in_array($detectedType, $allowedMime, true)) {
            return ['success' => false, 'error' => 'Please upload a valid image (JPG, PNG, GIF, WebP, or SVG).'];
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }

        if (!$extension) {
            return ['success' => false, 'error' => 'Could not determine the file extension.'];
        }

        $baseName = pathinfo($file['name'], PATHINFO_FILENAME) ?: 'partner-logo';
        $sluggedName = createSlug($baseName) ?: 'partner-logo';
        $filename = $sluggedName . '-' . time() . '.' . $extension;

        $destinationPath = rtrim($uploadDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
        if (!move_uploaded_file($file['tmp_name'], $destinationPath)) {
            return ['success' => false, 'error' => 'Unable to save the uploaded logo.'];
        }

        return [
            'success' => true,
            'path' => rtrim($relativeDirectory, '/\\') . '/' . $filename
        ];
    }
}

if (!function_exists('manage_partners_delete_file')) {
    function manage_partners_delete_file(?string $storedPath, string $uploadDirectory): void
    {
        if (empty($storedPath)) {
            return;
        }

        $fullPath = rtrim($uploadDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename($storedPath);
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}

requireAuth();
ensurePartnersTableExists();

$uploadDirectory = dirname(__DIR__) . '/assets/images/partners/';
$relativeDirectory = 'assets/images/partners/';

if (!is_dir($uploadDirectory)) {
    @mkdir($uploadDirectory, 0755, true);
}

$message = null;
$partnerForm = [
    'id' => null,
    'name' => '',
    'category' => '',
    'website' => '',
    'logo' => ''
];
$isEditing = false;

$statusMessages = [
    'added' => ['success' => true, 'text' => 'Partner added successfully.'],
    'updated' => ['success' => true, 'text' => 'Partner updated successfully.'],
    'deleted' => ['success' => true, 'text' => 'Partner removed successfully.'],
    'status_changed' => ['success' => true, 'text' => 'Partner status updated.'],
    'order_updated' => ['success' => true, 'text' => 'Partner order updated.'],
    'error' => ['success' => false, 'text' => 'An error occurred. Please try again.']
];

if (isset($_GET['status']) && isset($statusMessages[$_GET['status']])) {
    $message = $statusMessages[$_GET['status']];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    try {
        switch ($action) {
            case 'add_partner':
                $name = sanitizeInput($_POST['name'] ?? '');
                $category = sanitizeInput($_POST['category'] ?? '');
                $website = sanitizeInput($_POST['website'] ?? '');

                if ($website && !filter_var($website, FILTER_VALIDATE_URL)) {
                    $message = ['success' => false, 'text' => 'Please provide a valid website URL (including https://).'];
                    $partnerForm = ['id' => null, 'name' => $name, 'category' => $category, 'website' => $website, 'logo' => ''];
                    break;
                }

                if (empty($name)) {
                    $message = ['success' => false, 'text' => 'Partner name is required.'];
                    $partnerForm = ['id' => null, 'name' => $name, 'category' => $category, 'website' => $website, 'logo' => ''];
                    break;
                }

                $uploadResult = manage_partners_process_upload($_FILES['logo'] ?? [], $uploadDirectory, $relativeDirectory, true);
                if (!$uploadResult['success']) {
                    $message = ['success' => false, 'text' => $uploadResult['error']];
                    $partnerForm = ['id' => null, 'name' => $name, 'category' => $category, 'website' => $website, 'logo' => ''];
                    break;
                }

                $sortOrder = 0;
                $sortStmt = $pdo->query("SELECT COALESCE(MAX(sort_order), 0) AS max_order FROM partners");
                $sortOrder = (int) $sortStmt->fetch()['max_order'] + 1;

                $stmt = $pdo->prepare("INSERT INTO partners (name, category, website, logo, sort_order, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$name, $category ?: null, $website ?: null, $uploadResult['path'], $sortOrder]);

                header('Location: partners.php?status=added');
                exit();

            case 'update_partner':
                $partnerId = isset($_POST['partner_id']) ? (int) $_POST['partner_id'] : 0;
                $name = sanitizeInput($_POST['name'] ?? '');
                $category = sanitizeInput($_POST['category'] ?? '');
                $website = sanitizeInput($_POST['website'] ?? '');
                $existingLogo = sanitizeInput($_POST['existing_logo'] ?? '');

                if (!$partnerId) {
                    $message = ['success' => false, 'text' => 'Invalid partner.'];
                    break;
                }

                if (empty($name)) {
                    $message = ['success' => false, 'text' => 'Partner name is required.'];
                    $partnerForm = ['id' => $partnerId, 'name' => $name, 'category' => $category, 'website' => $website, 'logo' => $existingLogo];
                    $isEditing = true;
                    break;
                }

                if ($website && !filter_var($website, FILTER_VALIDATE_URL)) {
                    $message = ['success' => false, 'text' => 'Please provide a valid website URL (including https://).'];
                    $partnerForm = ['id' => $partnerId, 'name' => $name, 'category' => $category, 'website' => $website, 'logo' => $existingLogo];
                    $isEditing = true;
                    break;
                }

                $uploadResult = manage_partners_process_upload($_FILES['logo'] ?? [], $uploadDirectory, $relativeDirectory, false);
                if (!$uploadResult['success']) {
                    $message = ['success' => false, 'text' => $uploadResult['error']];
                    $partnerForm = ['id' => $partnerId, 'name' => $name, 'category' => $category, 'website' => $website, 'logo' => $existingLogo];
                    $isEditing = true;
                    break;
                }

                $logoPath = $existingLogo;
                if (!empty($uploadResult['path'])) {
                    manage_partners_delete_file($existingLogo, $uploadDirectory);
                    $logoPath = $uploadResult['path'];
                }

                $stmt = $pdo->prepare("UPDATE partners SET name = ?, category = ?, website = ?, logo = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$name, $category ?: null, $website ?: null, $logoPath, $partnerId]);

                header('Location: partners.php?status=updated');
                exit();

            case 'delete_partner':
                $partnerId = isset($_POST['partner_id']) ? (int) $_POST['partner_id'] : 0;
                if (!$partnerId) {
                    $message = ['success' => false, 'text' => 'Invalid partner selected.'];
                    break;
                }

                $stmt = $pdo->prepare("SELECT logo FROM partners WHERE id = ? LIMIT 1");
                $stmt->execute([$partnerId]);
                $partner = $stmt->fetch();

                if ($partner) {
                    manage_partners_delete_file($partner['logo'] ?? null, $uploadDirectory);
                    $deleteStmt = $pdo->prepare("DELETE FROM partners WHERE id = ? LIMIT 1");
                    $deleteStmt->execute([$partnerId]);
                    header('Location: partners.php?status=deleted');
                    exit();
                }

                $message = ['success' => false, 'text' => 'Partner not found.'];
                break;

            case 'toggle_partner':
                $partnerId = isset($_POST['partner_id']) ? (int) $_POST['partner_id'] : 0;
                if (!$partnerId) {
                    $message = ['success' => false, 'text' => 'Invalid partner selected.'];
                    break;
                }

                $stmt = $pdo->prepare("SELECT active FROM partners WHERE id = ? LIMIT 1");
                $stmt->execute([$partnerId]);
                $partner = $stmt->fetch();

                if (!$partner) {
                    $message = ['success' => false, 'text' => 'Partner not found.'];
                    break;
                }

                $newStatus = (int) $partner['active'] === 1 ? 0 : 1;
                $updateStmt = $pdo->prepare("UPDATE partners SET active = ?, updated_at = NOW() WHERE id = ?");
                $updateStmt->execute([$newStatus, $partnerId]);

                header('Location: partners.php?status=status_changed');
                exit();

            case 'update_order':
                $partnerId = isset($_POST['partner_id']) ? (int) $_POST['partner_id'] : 0;
                $sortOrder = isset($_POST['sort_order']) ? (int) $_POST['sort_order'] : 0;

                if (!$partnerId) {
                    $message = ['success' => false, 'text' => 'Invalid partner selected.'];
                    break;
                }

                $stmt = $pdo->prepare("UPDATE partners SET sort_order = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$sortOrder, $partnerId]);

                header('Location: partners.php?status=order_updated');
                exit();
        }
    } catch (Exception $e) {
        error_log('Partners admin error: ' . $e->getMessage());
        header('Location: partners.php?status=error');
        exit();
    }
}

if (isset($_GET['edit_id'])) {
    $editId = (int) $_GET['edit_id'];
    if ($editId > 0) {
        $stmt = $pdo->prepare("SELECT * FROM partners WHERE id = ? LIMIT 1");
        $stmt->execute([$editId]);
        $currentPartner = $stmt->fetch();

        if ($currentPartner) {
            $partnerForm = [
                'id' => (int) $currentPartner['id'],
                'name' => $currentPartner['name'] ?? '',
                'category' => $currentPartner['category'] ?? '',
                'website' => $currentPartner['website'] ?? '',
                'logo' => $currentPartner['logo'] ?? ''
            ];
            $isEditing = true;
        } else {
            $message = ['success' => false, 'text' => 'Partner not found.'];
        }
    }
}

$partners = [];
try {
    $stmt = $pdo->query("SELECT * FROM partners ORDER BY sort_order ASC, created_at DESC");
    $partners = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('Fetching partners failed: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partners - Timiza Youth Initiative</title>
    <link rel="icon" href="../assets/images/favicon.ico" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body.partners-page {
            background: linear-gradient(135deg, rgba(236, 254, 255, 0.65), rgba(236, 253, 245, 0.55)), #f8fafc;
            overflow-x: hidden;
            width: 100%;
            max-width: 100vw;
        }

        .partners-main {
            width: 100%;
            overflow-x: hidden;
        }

        .partners-hero {
            background: linear-gradient(135deg, rgba(22, 101, 52, 0.14), rgba(59, 130, 246, 0.12));
            border: 1px solid rgba(15, 118, 110, 0.2);
            backdrop-filter: blur(12px);
        }

        .form-card {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.96), rgba(248, 250, 252, 0.92));
            border-radius: 1.5rem;
            border: 1px solid rgba(148, 163, 184, 0.18);
            box-shadow: 0 20px 40px -26px rgba(15, 118, 110, 0.45);
        }

        .table-wrapper {
            position: relative;
            border-radius: 1.5rem;
            border: 1px solid rgba(148, 163, 184, 0.16);
            background: white;
            overflow: hidden;
        }

        .table-wrapper::before,
        .table-wrapper::after {
            content: '';
            position: absolute;
            top: 0;
            width: 32px;
            height: 100%;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .table-wrapper::before {
            left: 0;
            background: linear-gradient(to right, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0));
        }

        .table-wrapper::after {
            right: 0;
            background: linear-gradient(to left, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0));
        }

        .table-wrapper.scroll-left::before {
            opacity: 1;
        }

        .table-wrapper.scroll-right::after {
            opacity: 1;
        }

        .table-inner {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior-x: contain;
            touch-action: pan-x;
            display: block;
            padding-bottom: 0.75rem;
        }

        .table-inner::-webkit-scrollbar {
            height: 8px;
        }

        .table-inner::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.5);
            border-radius: 999px;
        }

        .table-inner::-webkit-scrollbar-track {
            background: rgba(148, 163, 184, 0.15);
        }

        .partners-table {
            min-width: 720px;
        }

        .partners-table th {
            background: rgba(15, 118, 110, 0.08);
            padding: 1.1rem 1rem;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.08em;
            white-space: nowrap;
        }

        .partners-table td {
            padding: 1rem;
            border-top: 1px solid rgba(226, 232, 240, 0.9);
            white-space: nowrap;
        }

        .partners-table td .space-y-1,
        .partners-table td .text-slate-400,
        .partners-table td .text-slate-500,
        .partners-table td form,
        .partners-table td a,
        .partners-table td span {
            white-space: normal;
        }

        .logo-thumb {
            width: 72px;
            height: 72px;
            object-fit: contain;
            border-radius: 0.75rem;
            background: rgba(148, 163, 184, 0.12);
            padding: 0.75rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.9rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-active {
            background: rgba(22, 101, 52, 0.15);
            color: #166534;
        }

        .status-inactive {
            background: rgba(220, 38, 38, 0.12);
            color: #b91c1c;
        }

        .action-link {
            color: #0f766e;
            font-weight: 600;
        }

        .action-link:hover {
            color: #134e4a;
        }

        @media (max-width: 1024px) {
            .partners-hero {
                padding: 3rem 2.5rem;
            }
        }

        @media (max-width: 640px) {
            .partners-hero {
                padding: 2.25rem 1.75rem;
                border-radius: 1.75rem;
            }

            .partners-hero h1 {
                font-size: 1.9rem;
            }

            .partners-hero ul {
                grid-template-columns: 1fr;
            }

            .form-card {
                padding: 1.75rem;
                border-radius: 1.25rem;
            }

            .logo-thumb {
                width: 56px;
                height: 56px;
                padding: 0.5rem;
            }

            .partners-table {
                min-width: 600px;
            }

            .partners-table th,
            .partners-table td {
                padding: 0.75rem;
            }

            .table-inner {
                margin: 0;
                padding: 0 0 0.75rem;
            }

            .table-wrapper {
                border-radius: 1rem;
            }

            .partners-table {
                min-width: 560px;
            }
        }
    </style>
</head>
<body class="font-poppins partners-page min-h-screen">
    <?php include '../includes/nav-admin.php'; ?>

    <main class="partners-main w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-10">
        <?php if ($message): ?>
        <div class="rounded-2xl p-5 border <?php echo $message['success'] ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-rose-50 border-rose-200 text-rose-700'; ?>">
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

        <section class="partners-hero rounded-3xl px-6 sm:px-10 py-10 sm:py-14 flex flex-col lg:flex-row gap-10">
            <div class="space-y-5 max-w-2xl">
                <span class="inline-flex items-center gap-2 text-sm font-semibold uppercase tracking-[0.3em] text-primary/80"><span class="h-2 w-2 rounded-full bg-primary animate-pulse"></span> Strategic Partners</span>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-slate-900 leading-tight">Showcase the organizations championing Timiza Youth Initiative</h1>
                <p class="text-slate-600 text-base sm:text-lg">Upload partner logos, control their visibility, and keep your supporters section fresh. Reorder partners, link to their websites, and highlight your most impactful collaborations.</p>
                <ul class="grid sm:grid-cols-2 gap-3 text-sm text-slate-600">
                    <li class="flex items-center gap-2"><i class="fas fa-bullhorn text-primary"></i> Display partners on the public site</li>
                    <li class="flex items-center gap-2"><i class="fas fa-image text-primary"></i> Quick logo uploads & previews</li>
                    <li class="flex items-center gap-2"><i class="fas fa-globe text-primary"></i> Optional website links</li>
                    <li class="flex items-center gap-2"><i class="fas fa-arrows-up-down text-primary"></i> Drag-friendly sort order via quick updates</li>
                </ul>
            </div>
            <div class="bg-white/90 border border-white/50 shadow-xl rounded-3xl p-6 space-y-4 w-full max-w-md">
                <div class="flex items-center gap-4">
                    <div class="h-14 w-14 rounded-2xl bg-primary/10 text-primary flex items-center justify-center text-xl"><i class="fas fa-handshake"></i></div>
                    <div>
                        <p class="text-xs uppercase text-primary/70 tracking-widest font-semibold">Current Partners</p>
                        <p class="text-2xl font-bold text-slate-900"><?php echo count($partners); ?></p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 text-sm text-slate-500">
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <p class="text-xs uppercase tracking-widest text-slate-400">Active</p>
                        <p class="mt-1 text-lg font-semibold">
                            <?php echo count(array_filter($partners, fn ($partner) => (int) ($partner['active'] ?? 0) === 1)); ?>
                        </p>
                    </div>
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <p class="text-xs uppercase tracking-widest text-slate-400">Inactive</p>
                        <p class="mt-1 text-lg font-semibold">
                            <?php echo count(array_filter($partners, fn ($partner) => (int) ($partner['active'] ?? 0) === 0)); ?>
                        </p>
                    </div>
                </div>
                <p class="text-xs text-slate-400 leading-relaxed">Inactive partners remain hidden from the public but are kept here so you can reactivate them later.</p>
            </div>
        </section>

        <section class="grid lg:grid-cols-[380px,1fr] gap-8 lg:gap-12">
            <div class="form-card p-7 space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.4em] text-primary/80 font-semibold">Partner Form</p>
                        <h2 class="text-xl font-semibold text-slate-900 mt-1"><?php echo $isEditing ? 'Edit Partner' : 'Add New Partner'; ?></h2>
                    </div>
                    <?php if ($isEditing): ?>
                    <a href="partners.php" class="text-sm font-semibold text-primary hover:text-primary/80">Cancel</a>
                    <?php endif; ?>
                </div>

                <form action="partners.php" method="POST" enctype="multipart/form-data" class="space-y-5">
                    <input type="hidden" name="action" value="<?php echo $isEditing ? 'update_partner' : 'add_partner'; ?>">
                    <?php if ($isEditing): ?>
                    <input type="hidden" name="partner_id" value="<?php echo (int) $partnerForm['id']; ?>">
                    <input type="hidden" name="existing_logo" value="<?php echo htmlspecialchars($partnerForm['logo']); ?>">
                    <?php endif; ?>

                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Organization Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($partnerForm['name']); ?>" class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50" placeholder="e.g. Green Planet Trust" required>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Category / Partner Type</label>
                        <input type="text" name="category" value="<?php echo htmlspecialchars($partnerForm['category']); ?>" class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50" placeholder="e.g. Strategic Partner">
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Website URL</label>
                        <input type="url" name="website" value="<?php echo htmlspecialchars($partnerForm['website']); ?>" class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50" placeholder="https://partner.org">
                        <p class="text-xs text-slate-400">Include https:// for clickable links.</p>
                    </div>

                    <div class="space-y-3">
                        <label class="text-sm font-semibold text-slate-700"><?php echo $isEditing ? 'Update Logo' : 'Partner Logo'; ?><?php echo $isEditing ? '' : ' <span class="text-rose-500">*</span>'; ?></label>
                        <input type="file" name="logo" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:rounded-xl file:border-0 file:bg-primary file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-primary/90">
                        <?php if ($isEditing && $partnerForm['logo']): ?>
                        <div class="flex items-center gap-3">
                            <img src="../<?php echo htmlspecialchars($partnerForm['logo']); ?>" alt="Current logo" class="w-16 h-16 object-contain rounded-xl border border-slate-200 bg-white p-2">
                            <p class="text-xs text-slate-400">Leave empty to keep the existing logo.</p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-3 font-semibold text-white shadow-lg shadow-primary/30 transition hover:bg-primary/90">
                            <i class="fas <?php echo $isEditing ? 'fa-save' : 'fa-plus-circle'; ?>"></i>
                            <?php echo $isEditing ? 'Save Changes' : 'Add Partner'; ?>
                        </button>
                        <?php if ($isEditing): ?>
                        <a href="partners.php" class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-500 hover:border-slate-300">
                            <i class="fas fa-times"></i>
                            Cancel edit
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="space-y-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.4em] text-primary/80 font-semibold">Partner Roster</p>
                        <h2 class="text-xl font-semibold text-slate-900 mt-1">Manage Listed Partners</h2>
                    </div>
                    <button type="button" class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:border-slate-300" onclick="window.location.reload()">
                        <i class="fas fa-rotate"></i>
                        Refresh
                    </button>
                </div>

                <?php if (!empty($partners)): ?>
                <div class="table-wrapper">
                    <div class="table-inner">
                        <table class="w-full text-sm partners-table">
                            <thead class="text-slate-500">
                                <tr>
                                    <th class="text-left">Logo</th>
                                    <th class="text-left">Partner</th>
                                    <th class="text-left">Category</th>
                                    <th class="text-left">Website</th>
                                    <th class="text-left">Status</th>
                                    <th class="text-left">Sort</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($partners as $partner): ?>
                                <tr class="hover:bg-slate-50/60">
                                    <td>
                                        <?php if (!empty($partner['logo'])): ?>
                                        <img src="../<?php echo htmlspecialchars($partner['logo']); ?>" alt="<?php echo htmlspecialchars($partner['name']); ?> logo" class="logo-thumb">
                                        <?php else: ?>
                                        <div class="logo-thumb flex items-center justify-center text-slate-300">
                                            <i class="fas fa-image"></i>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="space-y-1">
                                            <p class="font-semibold text-slate-900 text-sm"><?php echo htmlspecialchars($partner['name']); ?></p>
                                            <p class="text-xs text-slate-400">Added on <?php echo formatDate($partner['created_at'] ?? date('Y-m-d')); ?></p>
                                        </div>
                                    </td>
                                    <td class="text-slate-600 text-sm"><?php echo htmlspecialchars($partner['category'] ?? '—'); ?></td>
                                    <td>
                                        <?php if (!empty($partner['website'])): ?>
                                        <a href="<?php echo htmlspecialchars($partner['website']); ?>" target="_blank" rel="noopener" class="text-primary text-sm font-semibold hover:text-primary/80">
                                            <?php echo htmlspecialchars(parse_url($partner['website'], PHP_URL_HOST) ?: $partner['website']); ?>
                                        </a>
                                        <?php else: ?>
                                        <span class="text-xs text-slate-400">No link</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo (int) ($partner['active'] ?? 0) === 1 ? 'status-active' : 'status-inactive'; ?>">
                                            <span class="h-2 w-2 rounded-full <?php echo (int) ($partner['active'] ?? 0) === 1 ? 'bg-emerald-500' : 'bg-rose-500'; ?>"></span>
                                            <?php echo (int) ($partner['active'] ?? 0) === 1 ? 'Active' : 'Hidden'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form action="partners.php" method="POST" class="flex items-center gap-2">
                                            <input type="hidden" name="action" value="update_order">
                                            <input type="hidden" name="partner_id" value="<?php echo (int) $partner['id']; ?>">
                                            <input type="number" name="sort_order" value="<?php echo (int) ($partner['sort_order'] ?? 0); ?>" class="w-16 rounded-lg border border-slate-200 px-2 py-1 text-sm" min="0">
                                            <button type="submit" class="text-xs font-semibold text-slate-500 hover:text-primary">Update</button>
                                        </form>
                                    </td>
                                    <td>
                                        <div class="flex items-center justify-end gap-4">
                                            <a href="partners.php?edit_id=<?php echo (int) $partner['id']; ?>" class="action-link text-xs uppercase tracking-wider">Edit</a>
                                            <form action="partners.php" method="POST" class="inline-flex" onsubmit="return confirm('Toggle visibility for this partner?');">
                                                <input type="hidden" name="action" value="toggle_partner">
                                                <input type="hidden" name="partner_id" value="<?php echo (int) $partner['id']; ?>">
                                                <button type="submit" class="text-xs font-semibold text-slate-500 hover:text-primary uppercase tracking-wider"><?php echo (int) ($partner['active'] ?? 0) === 1 ? 'Hide' : 'Show'; ?></button>
                                            </form>
                                            <form action="partners.php" method="POST" class="inline-flex" onsubmit="return confirm('Delete this partner? This cannot be undone.');">
                                                <input type="hidden" name="action" value="delete_partner">
                                                <input type="hidden" name="partner_id" value="<?php echo (int) $partner['id']; ?>">
                                                <button type="submit" class="text-xs font-semibold text-rose-500 hover:text-rose-600 uppercase tracking-wider">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php else: ?>
                <div class="rounded-3xl border border-dashed border-slate-200 bg-white p-12 text-center space-y-4 shadow-sm">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary/10 text-primary text-2xl">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3 class="text-2xl font-semibold text-slate-900">No partners listed yet</h3>
                    <p class="text-slate-500 mx-auto max-w-lg">Add the first partner using the form. Their logo and details will appear here, ready to display on the public site.</p>
                    <a href="#" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white shadow hover:bg-primary/90" onclick="window.scrollTo({ top: 0, behavior: 'smooth' }); return false;">
                        <i class="fas fa-plus-circle"></i>
                        Add Partner
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.table-wrapper').forEach(function (wrapper) {
            var scroller = wrapper.querySelector('.table-inner');
            if (!scroller) {
                return;
            }

            var updateShadows = function () {
                var atStart = scroller.scrollLeft <= 1;
                var atEnd = scroller.scrollLeft + scroller.clientWidth >= scroller.scrollWidth - 1;

                wrapper.classList.toggle('scroll-left', !atStart);
                wrapper.classList.toggle('scroll-right', !atEnd);
            };

            updateShadows();
            scroller.addEventListener('scroll', updateShadows, { passive: true });
            window.addEventListener('resize', updateShadows);
        });
    });
    </script>
</body>
</html>