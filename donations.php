<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

ensureDonationsTablesInitialized();

if (isset($_GET['callback'])) {
    $provider = $_GET['callback'];
    if (in_array($provider, ['mpesa', 'payhero'], true)) {
        $result = handleDonationCallback($provider);
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid callback provider']);
    exit;
}

requireAuth();

$notice = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $donationId = isset($_POST['donation_id']) ? (int) $_POST['donation_id'] : 0;

    if ($action === 'resend_mpesa') {
        $notice = resendDonationPayment($donationId);
    } elseif ($action === 'sync_payhero') {
        $notice = syncPayheroDonationStatus($donationId);
    }
}

$statusFilter = $_GET['status'] ?? 'all';
$allowedStatuses = ['all', 'pending', 'initiated', 'processing', 'completed', 'failed', 'cancelled'];
if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = 'all';
}

try {
    if ($statusFilter === 'all') {
        $stmt = $pdo->query("SELECT * FROM donations ORDER BY created_at DESC");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM donations WHERE status = ? ORDER BY created_at DESC");
        $stmt->execute([$statusFilter]);
    }
    $donations = $stmt->fetchAll();
} catch (Exception $e) {
    $donations = [];
    error_log('Fetch donations failed: ' . $e->getMessage());
}

$metrics = [
    'total_amount' => 0,
    'completed_amount' => 0,
    'pending_count' => 0,
    'failed_count' => 0,
    'today_amount' => 0,
];

$today = date('Y-m-d');
foreach ($donations as $donationRow) {
    $amount = (float) $donationRow['amount'];
    $metrics['total_amount'] += $amount;
    if ($donationRow['status'] === 'completed') {
        $metrics['completed_amount'] += $amount;
    }
    if (in_array($donationRow['status'], ['pending', 'initiated', 'processing'], true)) {
        $metrics['pending_count']++;
    }
    if (in_array($donationRow['status'], ['failed', 'cancelled'], true)) {
        $metrics['failed_count']++;
    }
    if (strpos($donationRow['created_at'], $today) === 0) {
        $metrics['today_amount'] += $amount;
    }
}

$selectedId = isset($_GET['id']) ? (int) $_GET['id'] : null;
if (!$selectedId && !empty($donations)) {
    $selectedId = (int) $donations[0]['id'];
}

$selectedDonation = null;
$donationEvents = [];

if ($selectedId) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM donations WHERE id = ? LIMIT 1");
        $stmt->execute([$selectedId]);
        $selectedDonation = $stmt->fetch();
        if ($selectedDonation) {
            $stmt = $pdo->prepare("SELECT * FROM donation_events WHERE donation_id = ? ORDER BY created_at DESC");
            $stmt->execute([$selectedId]);
            $donationEvents = $stmt->fetchAll();
        }
    } catch (Exception $e) {
        error_log('Fetch donation detail failed: ' . $e->getMessage());
    }
}

$statusStyles = [
    'pending'    => 'bg-yellow-100 text-yellow-700',
    'initiated'  => 'bg-blue-100 text-blue-700',
    'processing' => 'bg-purple-100 text-purple-700',
    'completed'  => 'bg-green-100 text-green-700',
    'failed'     => 'bg-red-100 text-red-700',
    'cancelled'  => 'bg-gray-100 text-gray-600',
];

function buildStatusFilterUrl(string $status): string {
    $query = $_GET;
    $query['status'] = $status;
    unset($query['id']);
    return 'donations.php' . (!empty($query) ? '?' . http_build_query($query) : '');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Donations - Timiza Youth Initiative</title>
    <link rel="icon" href="../assets/images/favicon.ico" type="image/x-icon"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        emerald: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b'
                        },
                        primary: '#10b981',
                        secondary: '#22c55e',
                        accent: '#FFD166',
                        neutral: '#f8fafc',
                        dark: '#1E293B',
                        teal: '#009688'
                    },
                    fontFamily: {
                        'poppins': ['Poppins', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        body {
            background: linear-gradient(120deg, #d1fae5 0%, #22c55e 100%);
            font-family: 'Poppins', sans-serif;
        }
        .button-primary {
            @apply bg-primary text-white font-semibold rounded-xl py-2 px-6 shadow-md hover:bg-secondary transition;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(12px);
            border-radius: 20px;
            border: 1.5px solid #bbf7d0;
            box-shadow: 0 4px 12px #22c55e60;
            transition: box-shadow 0.3s ease, transform 0.3s ease;
        }
        .glass-card:hover {
            box-shadow: 0 8px 24px #22c55eaa;
            transform: translateY(-3px);
        }
        .donation-status-filters {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 1.25rem;
        }
        .donation-status-filter-btn {
            background: #f1f5f9;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 1rem;
            padding: 0.6em 1.8em;
            box-shadow: 0 4px 12px -7px #10b98112;
            color: #028256;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5em;
            border: none;
            transition: all 0.2s ease;
            outline: none;
        }
        .donation-status-filter-btn:hover,
        .donation-status-filter-btn:focus {
            background: #d1fae5;
            color: #0f766e;
            box-shadow: 0 8px 32px -10px #10b98125;
            transform: scale(1.05);
        }
        .donation-status-filter-btn.active,
        .donation-status-filter-btn[aria-current="true"] {
            background: linear-gradient(95deg,#22c55e 40%,#10b981 99%);
            color: #fff;
            box-shadow: 0 8px 28px -12px #10b98147;
        }
        .donation-status-filter-btn.status-pending.active { background: linear-gradient(95deg,#fde68a 58%,#fbbf24 99%); color: #b45309;}
        .donation-status-filter-btn.status-initiated.active { background: linear-gradient(95deg,#a5b4fc 46%,#60a5fa 99%); color: #1d4ed8;}
        .donation-status-filter-btn.status-processing.active { background: linear-gradient(95deg,#e0e7ff 70%,#a78bfa 100%); color: #7c3aed;}
        .donation-status-filter-btn.status-completed.active { background: linear-gradient(95deg,#bbf7d0 45%,#10b981 99%); color: #047857;}
        .donation-status-filter-btn.status-failed.active { background: linear-gradient(95deg,#fecaca 40%,#f87171 99%); color: #b91c1c;}
        .donation-status-filter-btn.status-cancelled.active { background: linear-gradient(95deg,#e7e5e4 40%,#a3a3a3 99%); color: #52525b;}

        /* Donation entries */
        a.donation-entry {
            border-radius: 20px;
            padding: 1rem 1.25rem;
            transition: background-color 0.2s, transform 0.2s;
            display: block;
            color: inherit;
        }
        a.donation-entry:hover {
            background-color: #d1fae5;
            box-shadow: inset 0 0 10px #10b98122;
            transform: translateX(3px);
            text-decoration: none;
        }
        .status-dot {
            width: 0.75rem; 
            height: 0.75rem; 
            border-radius: 9999px; 
            display: inline-block;
        }
    </style>
</head>
<body class="font-poppins min-h-screen">
<?php include '../includes/nav-admin.php'; ?>

<main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-10">
    <div class="bg-white/70 backdrop-blur rounded-2xl border border-white/40 shadow-lg p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
        <div class="space-y-2">
            <p class="text-sm uppercase tracking-wider text-primary font-semibold flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-secondary animate-pulse"></span> Donations Dashboard</p>
            <h1 class="text-3xl font-bold text-dark">Donations Dashboard</h1>
            <p class="text-gray-600">Monitor incoming contributions, resend M-Pesa prompts, and track payment events.</p>
        </div>
        <div class="bg-white rounded-xl shadow-inner px-5 py-4 text-center border border-primary/10">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Collected</span>
            <p class="text-3xl font-bold text-primary mt-1">
                KES <?php echo number_format($metrics['completed_amount'], 2); ?>
            </p>
            <span class="text-xs text-gray-500">Confirmed funds received</span>
        </div>
    </div>

    <!-- Status Filters -->
    <div class="donation-status-filters">
        <a href="<?php echo htmlspecialchars(buildStatusFilterUrl('all')); ?>"
           class="donation-status-filter-btn <?php if ($statusFilter === 'all') echo 'active'; ?>" <?php if ($statusFilter === 'all') echo 'aria-current="true"'; ?>>
            <i class="fas fa-layer-group"></i> All
        </a>
        <a href="<?php echo htmlspecialchars(buildStatusFilterUrl('pending')); ?>"
           class="donation-status-filter-btn status-pending <?php if ($statusFilter==='pending') echo 'active'; ?>" <?php if ($statusFilter==='pending') echo 'aria-current="true"'; ?>>
            <i class="fas fa-clock"></i> Pending
        </a>
        <a href="<?php echo htmlspecialchars(buildStatusFilterUrl('initiated')); ?>"
           class="donation-status-filter-btn status-initiated <?php if ($statusFilter==='initiated') echo 'active'; ?>" <?php if ($statusFilter==='initiated') echo 'aria-current="true"'; ?>>
            <i class="fas fa-play-circle"></i> Initiated
        </a>
        <a href="<?php echo htmlspecialchars(buildStatusFilterUrl('processing')); ?>"
           class="donation-status-filter-btn status-processing <?php if ($statusFilter==='processing') echo 'active'; ?>" <?php if ($statusFilter==='processing') echo 'aria-current="true"'; ?>>
            <i class="fas fa-gears"></i> Processing
        </a>
        <a href="<?php echo htmlspecialchars(buildStatusFilterUrl('completed')); ?>"
           class="donation-status-filter-btn status-completed <?php if ($statusFilter==='completed') echo 'active'; ?>" <?php if ($statusFilter==='completed') echo 'aria-current="true"'; ?>>
            <i class="fas fa-check-circle"></i> Completed
        </a>
        <a href="<?php echo htmlspecialchars(buildStatusFilterUrl('failed')); ?>"
           class="donation-status-filter-btn status-failed <?php if ($statusFilter==='failed') echo 'active'; ?>" <?php if ($statusFilter==='failed') echo 'aria-current="true"'; ?>>
            <i class="fas fa-times-circle"></i> Failed
        </a>
        <a href="<?php echo htmlspecialchars(buildStatusFilterUrl('cancelled')); ?>"
           class="donation-status-filter-btn status-cancelled <?php if ($statusFilter==='cancelled') echo 'active'; ?>" <?php if ($statusFilter==='cancelled') echo 'aria-current="true"'; ?>>
            <i class="fas fa-ban"></i> Cancelled
        </a>
    </div>

    <?php if ($notice): ?>
        <div class="mb-6 p-4 rounded-xl border <?php echo $notice['success'] ? 'bg-green-50 text-green-700 border-green-200 shadow-sm' : 'bg-red-50 text-red-700 border-red-200 shadow-sm'; ?>">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center <?php echo $notice['success'] ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'; ?>">
                    <i class="fas <?php echo $notice['success'] ? 'fa-check' : 'fa-triangle-exclamation'; ?>"></i>
                </div>
                <p class="text-sm font-medium flex-1"><?php echo htmlspecialchars($notice['message'] ?? ''); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <aside class="glass-card p-5 overflow-y-auto max-h-[650px]">
            <div class="flex items-center justify-between mb-4">
                <span class="font-semibold text-dark">Recent Donations</span>
                <span class="text-sm text-gray-500"><?php echo count($donations); ?> total</span>
            </div>
            <?php if (!empty($donations)): ?>
                <div class="divide-y divide-gray-200">
                    <?php foreach ($donations as $donation):
                        $isSelected = (int) $donation['id'] === $selectedId;
                        $status = $donation['status'];
                        $badgeClass = $statusStyles[$status] ?? 'bg-gray-100 text-gray-600';
                    ?>
                    <a href="donations.php?id=<?php echo (int) $donation['id']; ?><?php echo $statusFilter !== 'all' ? '&status=' . urlencode($statusFilter) : ''; ?>"
                       class="donation-entry <?php echo $isSelected ? 'bg-primary/20 border-l-4 border-primary' : 'hover:bg-primary/10'; ?>">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-semibold text-dark truncate"><?php echo htmlspecialchars($donation['donor_name']); ?></h3>
                                    <span class="text-xs px-2 py-0.5 rounded-full <?php echo $badgeClass; ?>"><?php echo ucfirst($status); ?></span>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">
                                    <?php echo htmlspecialchars(strtoupper($donation['currency'])); ?> <?php echo number_format((float) $donation['amount'], 2); ?>
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    <?php echo strtoupper($donation['provider']); ?> • <?php echo formatDate($donation['created_at'], 'M j, Y g:i A'); ?>
                                </p>
                            </div>
                            <i class="fas fa-chevron-right text-gray-300 self-center"></i>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="p-10 text-center text-gray-500">
                    No donations found for this filter.
                </div>
            <?php endif; ?>
        </aside>

        <section class="glass-card p-6 space-y-6 lg:col-span-2 max-h-[650px] overflow-auto">
            <?php if ($selectedDonation): ?>
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-semibold text-dark mb-2">Donation #<?php echo htmlspecialchars($selectedDonation['reference']); ?></h2>
                        <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600">
                            <span class="flex items-center gap-2"><i class="fas fa-user-circle text-primary"></i> <?php echo htmlspecialchars($selectedDonation['donor_name']); ?></span>
                            <?php if ($selectedDonation['donor_email']): ?>
                                <span class="flex items-center gap-2"><i class="fas fa-envelope text-secondary"></i> <?php echo htmlspecialchars($selectedDonation['donor_email']); ?></span>
                            <?php endif; ?>
                            <span class="flex items-center gap-2"><i class="fas fa-phone text-primary"></i> <?php echo htmlspecialchars($selectedDonation['donor_phone'] ?: 'N/A'); ?></span>
                        </div>
                    </div>
                    <div class="text-right space-y-2">
                        <div class="text-lg font-semibold text-dark"><?php echo htmlspecialchars(strtoupper($selectedDonation['currency'])); ?> <?php echo number_format((float) $selectedDonation['amount'], 2); ?></div>
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium <?php echo $statusStyles[$selectedDonation['status']] ?? 'bg-gray-100 text-gray-600'; ?>">
                            <span class="status-dot" style="background-color: currentColor;"></span>
                            <?php echo ucfirst($selectedDonation['status']); ?>
                        </span>
                        <div class="text-xs text-gray-500">Updated <?php echo formatDate($selectedDonation['updated_at'] ?? $selectedDonation['created_at'], 'M j, Y g:i A'); ?></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white rounded-xl p-5 border border-primary/20 shadow-sm">
                        <h3 class="text-sm font-semibold text-dark uppercase tracking-wide mb-4 flex items-center gap-2"><i class="fas fa-receipt text-primary"></i> Payment Details</h3>
                        <dl class="space-y-3 text-sm text-gray-600">
                            <div class="flex justify-between border-b border-gray-100 pb-2">
                                <dt>Provider</dt>
                                <dd class="font-medium"><?php echo strtoupper($selectedDonation['provider']); ?></dd>
                            </div>
                            <div class="flex justify-between border-b border-gray-100 pb-2">
                                <dt>Channel</dt>
                                <dd><?php echo htmlspecialchars($selectedDonation['provider_channel'] ?: '-'); ?></dd>
                            </div>
                            <div class="flex justify-between border-b border-gray-100 pb-2">
                                <dt>Provider Reference</dt>
                                <dd><?php echo htmlspecialchars($selectedDonation['provider_reference'] ?: '-'); ?></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt>Created</dt>
                                <dd><?php echo formatDate($selectedDonation['created_at'], 'M j, Y g:i A'); ?></dd>
                            </div>
                        </dl>
                    </div>

                    <div class="bg-white rounded-xl p-5 border border-primary/20 shadow-sm">
                        <h3 class="text-sm font-semibold text-dark uppercase tracking-wide mb-4 flex items-center gap-2"><i class="fas fa-toolbox text-secondary"></i> Actions</h3>
                        <div class="space-y-3 text-sm text-gray-600">
                            <p>Use these controls to follow up on pending payments.</p>
                            <?php if ($selectedDonation['provider'] === 'mpesa' && in_array($selectedDonation['status'], ['pending', 'initiated', 'processing'], true)): ?>
                                <form method="POST" class="space-y-3">
                                    <input type="hidden" name="action" value="resend_mpesa">
                                    <input type="hidden" name="donation_id" value="<?php echo (int) $selectedDonation['id']; ?>">
                                    <button type="submit" class="button-primary w-full flex items-center justify-center gap-2">
                                        <i class="fas fa-sync-alt"></i> Resend M-Pesa Push
                                    </button>
                                    <p class="text-xs text-gray-500 bg-primary/10 border border-primary/30 rounded-lg p-3">We'll send a fresh STK prompt to <?php echo htmlspecialchars($selectedDonation['donor_phone'] ?: 'the donor'); ?>. Funds are confirmed after Safaricom approval.</p>
                                </form>
                            <?php elseif ($selectedDonation['provider'] === 'payhero'): ?>
                                <form method="POST" class="space-y-3">
                                    <input type="hidden" name="action" value="sync_payhero">
                                    <input type="hidden" name="donation_id" value="<?php echo (int) $selectedDonation['id']; ?>">
                                    <button type="submit" class="button-primary w-full flex items-center justify-center gap-2">
                                        <i class="fas fa-arrows-rotate"></i> Sync PayHero Status
                                    </button>
                                    <p class="text-xs text-gray-500 bg-primary/10 border border-primary/30 rounded-lg p-3">Fetch the latest status straight from PayHero using the transaction reference.</p>
                                </form>
                            <?php else: ?>
                                <p class="text-xs text-gray-500 bg-gray-100 border border-gray-200 rounded-lg p-3">No follow-up actions available for this donation.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if (!empty($donationEvents)): ?>
                    <div class="bg-white rounded-xl p-5 border border-primary/20 shadow-sm overflow-auto max-h-64">
                        <h3 class="text-lg font-semibold text-dark mb-3 flex items-center gap-2"><i class="fas fa-stream text-primary"></i> Event History</h3>
                        <div class="divide-y divide-gray-200">
                            <?php foreach ($donationEvents as $event): ?>
                                <div class="py-4 text-sm">
                                    <div class="flex items-center justify-between">
                                        <span class="font-medium text-dark flex items-center gap-2"><i class="fas fa-circle text-primary text-xs"></i> <?php echo ucfirst($event['event_type']); ?></span>
                                        <span class="text-xs text-gray-500"><?php echo formatDate($event['created_at'], 'M j, Y g:i A'); ?></span>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">Status: <?php echo htmlspecialchars($event['status'] ?? '-'); ?></div>
                                    <?php 
                                        $providerStatus = null;
                                        if (!empty($event['payload'])) {
                                            $payloadArr = json_decode($event['payload'], true);
                                            if (is_array($payloadArr)) {
                                                $providerStatus = $payloadArr['status'] 
                                                    ?? ($payloadArr['response']['status'] ?? null);
                                            }
                                        }
                                    ?>
                                    <?php if (!empty($providerStatus)): ?>
                                        <div class="text-xs text-gray-500 mt-1">Provider status: <?php echo htmlspecialchars($providerStatus); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($event['payload'])): ?>
                                        <details class="mt-2">
                                            <summary class="cursor-pointer text-primary text-xs font-medium flex items-center gap-2"><i class="fas fa-code"></i> View payload</summary>
                                            <pre class="mt-2 bg-slate-900 text-slate-100 rounded-lg p-3 overflow-x-auto text-xs"><?php echo htmlspecialchars(json_encode(json_decode($event['payload'], true), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
                                        </details>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-sm text-gray-500">No events recorded yet for this donation.</p>
                <?php endif; ?>

                <?php if (!empty($selectedDonation['meta'])): ?>
                    <div class="bg-white rounded-xl p-5 border border-primary/20 shadow-sm">
                        <h3 class="text-lg font-semibold text-dark mb-3 flex items-center gap-2"><i class="fas fa-database text-secondary"></i> Latest Callback Payload</h3>
                        <pre class="bg-slate-900 text-slate-100 rounded-lg p-4 overflow-x-auto text-xs"><?php echo htmlspecialchars(json_encode(json_decode($selectedDonation['meta'], true), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="glass-card p-10 text-center text-gray-500">
                    Select a donation to view its details.
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js" defer></script>
</body>
</html>
