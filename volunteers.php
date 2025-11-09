<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAuth();

$message = null;
$allowedStatuses = ['approved', 'pending', 'rejected'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'update_status') {
        $volunteerId = isset($_POST['volunteer_id']) ? (int) $_POST['volunteer_id'] : 0;
        $newStatus = strtolower(trim($_POST['new_status'] ?? ''));

        if ($volunteerId > 0 && in_array($newStatus, $allowedStatuses, true)) {
            try {
                $stmt = $pdo->prepare("UPDATE volunteers SET status = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$newStatus, $volunteerId]);

                $message = [
                    'success' => true,
                    'text' => 'Volunteer status updated successfully.'
                ];
            } catch (Exception $e) {
                error_log('Update volunteer status error: ' . $e->getMessage());
                $message = [
                    'success' => false,
                    'text' => 'Failed to update volunteer status. Please try again.'
                ];
            }
        } else {
            $message = [
                'success' => false,
                'text' => 'Invalid volunteer selection or status.'
            ];
        }
    } elseif ($action === 'export_csv') {
        try {
            $stmt = $pdo->query("SELECT id, name, email, phone, interest_area, experience, status, created_at FROM volunteers WHERE active = 1 ORDER BY created_at DESC");
            $exportRows = $stmt->fetchAll();

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="volunteers-' . date('Ymd-His') . '.csv"');

            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'Name', 'Email', 'Phone', 'Interest Area', 'Experience', 'Status', 'Applied At']);

            foreach ($exportRows as $row) {
                fputcsv($output, [
                    $row['id'],
                    $row['name'],
                    $row['email'],
                    $row['phone'],
                    $row['interest_area'],
                    $row['experience'],
                    $row['status'],
                    $row['created_at']
                ]);
            }

            fclose($output);
            exit;
        } catch (Exception $e) {
            error_log('Export volunteers CSV error: ' . $e->getMessage());
            $message = [
                'success' => false,
                'text' => 'Unable to export volunteers right now. Please try again later.'
            ];
        }
    }
}

$volunteers = [];
$totalVolunteers = 0;
$statusSummary = [
    'approved' => 0,
    'pending' => 0,
    'rejected' => 0
];
$interestAreas = [];
$statusLabels = [
    'approved' => ['label' => 'Approved', 'icon' => 'fa-circle-check', 'icon_bg' => 'bg-emerald-100 text-emerald-600', 'text' => 'text-emerald-600'],
    'pending' => ['label' => 'Pending', 'icon' => 'fa-hourglass-half', 'icon_bg' => 'bg-amber-100 text-amber-600', 'text' => 'text-amber-600'],
    'rejected' => ['label' => 'Rejected', 'icon' => 'fa-circle-xmark', 'icon_bg' => 'bg-rose-100 text-rose-600', 'text' => 'text-rose-600']
];
$statusActionClasses = [
    'approved' => 'status-action-approve',
    'pending' => 'status-action-pending',
    'rejected' => 'status-action-reject'
];

try {
    $stmt = $pdo->query("SELECT id, name, email, phone, interest_area, experience, status, created_at FROM volunteers WHERE active = 1 ORDER BY created_at DESC");
    $volunteers = $stmt->fetchAll();
    $totalVolunteers = count($volunteers);

    foreach ($volunteers as $volunteer) {
        $status = strtolower($volunteer['status'] ?? 'pending');
        if (isset($statusSummary[$status])) {
            $statusSummary[$status]++;
        }

        $area = $volunteer['interest_area'] ?? 'General';
        if (!isset($interestAreas[$area])) {
            $interestAreas[$area] = 0;
        }
        $interestAreas[$area]++;
    }

    if (!empty($interestAreas)) {
        arsort($interestAreas);
    }
} catch (Exception $e) {
    error_log('Fetch volunteers error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteers - Timiza Youth Initiative</title>
    <link rel="icon" href="../assets/images/favicon.ico" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body.volunteers-page {
            background: radial-gradient(circle at top right, rgba(0, 191, 166, 0.12), transparent 45%),
                        radial-gradient(circle at bottom left, rgba(0, 123, 95, 0.12), transparent 50%),
                        #F8FAFC;
        }

        .volunteers-hero {
            background: linear-gradient(135deg, rgba(0, 123, 95, 0.12), rgba(0, 191, 166, 0.08));
            border: 1px solid rgba(14, 116, 144, 0.12);
            backdrop-filter: blur(10px);
        }

        .stats-card {
            border-radius: 1.25rem;
            border: 1px solid rgba(30, 41, 59, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 38px -20px rgba(13, 103, 86, 0.45);
            border-color: rgba(14, 116, 144, 0.25);
        }

        .status-chip {
            padding: 0.35rem 0.85rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .status-chip i {
            font-size: 0.7rem;
        }

        .status-approved {
            background: rgba(16, 185, 129, 0.12);
            color: #047857;
        }

        .status-pending {
            background: rgba(234, 179, 8, 0.18);
            color: #b45309;
        }

        .status-rejected {
            background: rgba(239, 68, 68, 0.15);
            color: #b91c1c;
        }

        .volunteer-card {
            border-radius: 1.25rem;
            border: 1px solid rgba(15, 118, 110, 0.08);
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.96), rgba(248, 250, 252, 0.92));
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .volunteer-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 25px 40px -22px rgba(15, 118, 110, 0.45);
        }

        .volunteer-card .meta-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.925rem;
        }

        .volunteer-card .meta-icon {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(14, 116, 144, 0.1);
            color: #0f766e;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.3rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
            background: rgba(14, 116, 144, 0.1);
            color: #0f766e;
        }

        .volunteer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
            gap: 1.5rem;
        }

        .table-wrapper {
            position: relative;
            overflow-x: auto;
            border-radius: 1.25rem;
            border: 1px solid rgba(30, 41, 59, 0.08);
        }

        .volunteers-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
        }

        .volunteers-table thead th {
            background: linear-gradient(135deg, rgba(15, 118, 110, 0.08), rgba(13, 148, 136, 0.1));
            color: #0f172a;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 600;
            padding: 1rem 1.25rem;
        }

        .volunteers-table tbody td {
            padding: 1.15rem 1.25rem;
            font-size: 0.925rem;
            color: #475569;
            border-top: 1px solid rgba(148, 163, 184, 0.18);
        }

        .volunteers-table tbody tr:hover td {
            background: rgba(14, 116, 144, 0.08);
        }

        .empty-state {
            background: linear-gradient(145deg, rgba(14, 116, 144, 0.05), rgba(0, 191, 166, 0.09));
            border-radius: 1.5rem;
            border: 1px dashed rgba(15, 118, 110, 0.25);
        }

        .filter-tabs {
            display: inline-flex;
            align-items: center;
            background: rgba(15, 118, 110, 0.08);
            padding: 0.35rem;
            border-radius: 999px;
        }

        .filter-tab {
            padding: 0.45rem 0.95rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #0f766e;
            opacity: 0.7;
            transition: all 0.2s ease;
        }

        .filter-tab.active,
        .filter-tab:hover {
            background: white;
            opacity: 1;
            box-shadow: 0 10px 15px -12px rgba(15, 118, 110, 0.65);
        }

        .primary-btn {
            background: linear-gradient(135deg, #007B5F, #00BFA6);
            color: #fff;
            padding: 0.75rem 1.75rem;
            border-radius: 1rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            box-shadow: 0 20px 40px -18px rgba(0, 123, 95, 0.65);
            transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
        }

        .primary-btn:hover,
        .primary-btn:focus {
            transform: translateY(-2px);
            box-shadow: 0 26px 48px -20px rgba(0, 123, 95, 0.75);
            background: linear-gradient(135deg, #00684f, #009d83);
        }

        .secondary-btn {
            background: white;
            color: #0f766e;
            padding: 0.7rem 1.5rem;
            border-radius: 1rem;
            font-weight: 600;
            border: 1px solid rgba(15, 118, 110, 0.25);
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .secondary-btn:hover,
        .secondary-btn:focus {
            transform: translateY(-1px);
            border-color: rgba(15, 118, 110, 0.45);
            box-shadow: 0 16px 30px -22px rgba(15, 118, 110, 0.55);
        }

        .status-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .status-action-btn {
            padding: 0.4rem 0.85rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .status-action-btn i {
            font-size: 0.75rem;
        }

        .status-action-btn:hover,
        .status-action-btn:focus {
            transform: translateY(-1px);
            box-shadow: 0 12px 22px -18px rgba(15, 118, 110, 0.65);
        }

        .status-action-approve {
            background: rgba(16, 185, 129, 0.15);
            color: #047857;
        }

        .status-action-pending {
            background: rgba(234, 179, 8, 0.2);
            color: #b45309;
        }

        .status-action-reject {
            background: rgba(239, 68, 68, 0.18);
            color: #b91c1c;
        }

        .table-wrapper::-webkit-scrollbar {
            height: 8px;
        }

        .table-wrapper::-webkit-scrollbar-thumb {
            background: rgba(15, 118, 110, 0.25);
            border-radius: 999px;
        }

        @media (max-width: 768px) {
            .volunteers-hero {
                flex-direction: column;
                align-items: flex-start;
                gap: 1.5rem;
            }

            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }

            .status-actions {
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body class="font-poppins bg-neutral min-h-screen volunteers-page">
    <?php include '../includes/nav-admin.php'; ?>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-10">
        <section class="volunteers-hero rounded-3xl px-6 sm:px-10 py-8 sm:py-12 flex items-center justify-between gap-8">
            <div class="space-y-4 max-w-2xl">
                <p class="text-sm uppercase tracking-wider text-primary font-semibold flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-secondary animate-pulse"></span> Volunteer Management</p>
                <h1 class="text-3xl sm:text-4xl font-bold text-dark leading-tight">Manage Volunteer Applications & Engagement</h1>
                <p class="text-gray-600 text-base sm:text-lg">Review the latest volunteer submissions, track statuses, and keep your team organized. Use the filters below to focus on specific statuses or interest areas.</p>
                <div class="filter-tabs">
                    <button class="filter-tab active" type="button">All Volunteers</button>
                    <button class="filter-tab" type="button">Approved</button>
                    <button class="filter-tab" type="button">Pending</button>
                    <button class="filter-tab" type="button">Rejected</button>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-xl px-6 py-6 w-full max-w-xs border border-white/60">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase tracking-wider font-semibold">Total Volunteers</p>
                        <p class="text-4xl font-bold text-primary mt-2"><?php echo number_format($totalVolunteers); ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-primary/10 text-primary flex items-center justify-center text-lg">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="mt-6 space-y-3">
                    <?php foreach ($statusSummary as $key => $count): ?>
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium text-gray-500 uppercase tracking-wide"><?php echo htmlspecialchars($statusLabels[$key]['label']); ?></span>
                        <span class="font-semibold text-dark"><?php echo number_format($count); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (!empty($interestAreas)): ?>
                <div class="mt-6">
                    <p class="text-xs uppercase tracking-wide text-gray-400 mb-3">Top Interest Areas</p>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach (array_slice($interestAreas, 0, 4) as $area => $count): ?>
                        <span class="pill">
                            <i class="fas fa-seedling"></i>
                            <?php echo htmlspecialchars($area); ?> (<?php echo $count; ?>)
                        </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="space-y-1">
                    <h2 class="text-xl sm:text-2xl font-semibold text-dark">Volunteer Applications</h2>
                    <p class="text-sm text-gray-500">Stay updated with new submissions and manage approvals effortlessly.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <button class="inline-flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-xl shadow hover:bg-primary/90 transition">
                        <i class="fas fa-download"></i>
                        Export CSV
                    </button>
                    <button class="inline-flex items-center gap-2 bg-white text-primary px-4 py-2 rounded-xl border border-primary/20 shadow-sm hover:border-primary/40 transition">
                        <i class="fas fa-envelope"></i>
                        Send Email Blast
                    </button>
                </div>
            </div>

            <?php if (!empty($volunteers)): ?>
            <div class="table-wrapper bg-white shadow-lg relative" data-table-wrapper>
                <div class="table-shadow-indicator" data-table-shadow></div>
                <table class="volunteers-table" data-volunteers-table>
                    <thead>
                        <tr>
                            <th class="rounded-tl-2xl">Volunteer</th>
                            <th>Contact</th>
                            <th>Interest Area</th>
                            <th>Experience</th>
                            <th>Status</th>
                            <th class="rounded-tr-2xl">Applied</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($volunteers as $volunteer): ?>
                        <tr>
                            <td>
                                <div class="flex flex-col">
                                    <span class="font-semibold text-dark"><?php echo htmlspecialchars($volunteer['name']); ?></span>
                                    <span class="text-xs text-gray-400 uppercase tracking-wider">#<?php echo str_pad((string) $volunteer['id'], 4, '0', STR_PAD_LEFT); ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="space-y-1 text-sm">
                                    <a href="mailto:<?php echo htmlspecialchars($volunteer['email']); ?>" class="text-primary hover:underline flex items-center gap-2">
                                        <i class="fas fa-envelope"></i>
                                        <?php echo htmlspecialchars($volunteer['email']); ?>
                                    </a>
                                    <a href="tel:<?php echo htmlspecialchars($volunteer['phone']); ?>" class="text-gray-500 hover:text-primary flex items-center gap-2">
                                        <i class="fas fa-phone"></i>
                                        <?php echo htmlspecialchars($volunteer['phone']); ?>
                                    </a>
                                </div>
                            </td>
                            <td>
                                <span class="pill">
                                    <i class="fas fa-hand-holding-heart"></i>
                                    <?php echo htmlspecialchars($volunteer['interest_area'] ?? 'General'); ?>
                                </span>
                            </td>
                            <td>
                                <p class="text-sm text-gray-600 leading-relaxed line-clamp-2">
                                    <?php echo htmlspecialchars($volunteer['experience'] ?: 'No experience details provided.'); ?>
                                </p>
                            </td>
                            <td>
                                <?php $statusKey = strtolower($volunteer['status'] ?? 'pending'); ?>
                                <span class="status-chip status-<?php echo $statusKey; ?>">
                                    <i class="fas <?php echo $statusLabels[$statusKey]['icon']; ?>"></i>
                                    <?php echo htmlspecialchars($statusLabels[$statusKey]['label']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="text-sm text-gray-500"><?php echo formatDate($volunteer['created_at'] ?? ''); ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state text-center py-16 px-6 space-y-4">
                <div class="bg-white/80 w-20 h-20 rounded-full flex items-center justify-center mx-auto text-primary text-3xl shadow">
                    <i class="fas fa-hands-helping"></i>
                </div>
                <h3 class="text-2xl font-semibold text-dark">No Volunteers Yet</h3>
                <p class="text-gray-500 max-w-xl mx-auto">You haven’t received any volunteer applications yet. Share your volunteer form to start receiving new applications.</p>
                <a href="../get-involved.php#volunteer-form" class="inline-flex items-center gap-2 bg-primary text-white px-6 py-3 rounded-xl shadow hover:bg-primary/90 transition">
                    <i class="fas fa-share-alt"></i>
                    Share Volunteer Form
                </a>
            </div>
            <?php endif; ?>
        </section>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var filterTabs = document.querySelectorAll('.filter-tab');
            var table = document.querySelector('[data-volunteers-table]');
            var tableWrapper = document.querySelector('[data-table-wrapper]');
            var emailBlast = document.querySelector('[data-email-blast]');

            function updateTableShadow() {
                if (!tableWrapper) {
                    return;
                }
                var isScrollable = tableWrapper.scrollWidth > tableWrapper.clientWidth;
                tableWrapper.setAttribute('data-scrollable', isScrollable ? 'true' : 'false');
            }

            function applyFilter(filterKey) {
                if (!table) {
                    return;
                }
                var rows = table.querySelectorAll('tbody tr');
                rows.forEach(function (row) {
                    var rowStatus = row.getAttribute('data-status');
                    if (filterKey === 'all' || rowStatus === filterKey) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            filterTabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    var filterKey = tab.getAttribute('data-filter') || 'all';

                    filterTabs.forEach(function (btn) {
                        btn.classList.remove('active');
                        btn.setAttribute('aria-pressed', 'false');
                    });

                    tab.classList.add('active');
                    tab.setAttribute('aria-pressed', 'true');

                    applyFilter(filterKey);
                });
            });

            if (emailBlast) {
                emailBlast.addEventListener('click', function () {
                    alert('Email broadcast tooling is coming soon. In the meantime, export the CSV and reach out manually.');
                });
            }

            window.addEventListener('resize', updateTableShadow);
            updateTableShadow();
            applyFilter('all');
        });
    </script>
</body>
</html>