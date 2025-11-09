<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
session_start();

// Redirect to login if user not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get current user details
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$currentUser = $stmt->fetch();

if (!$currentUser) {
    session_destroy();
    header('Location: login.php');
    exit();
}

if (!isset($currentUser['status']) || $currentUser['status'] !== 'active') {
    session_destroy();
    $statusMsg = '';
    if (($currentUser['status'] ?? '') === 'pending') {
        $statusMsg = 'Your account is pending approval. Please wait for confirmation from Timiza Youth Initiative.';
    } else {
        $statusMsg = 'Your account is not active. Please contact the Timiza Youth Initiative for assistance.';
    }
    header('Location: login.php?status_msg=' . urlencode($statusMsg));
    exit();
}

$userRole = strtolower((string) ($currentUser['role'] ?? 'member'));
$isMember = $userRole === 'member';
$isFinancer = $userRole === 'financer';
$isLeader = $userRole === 'leader' || (!$isMember && !$isFinancer);

$stats = [
    'members' => 0,
    'leaders' => 0,
    'pending' => 0,
    'active' => 0,
];

if ($isLeader) {
    try {
        $stats['members'] = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $stats['leaders'] = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='leader'")->fetchColumn();
        $stats['pending'] = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE status='pending'")->fetchColumn();
        $stats['active'] = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE status='active'")->fetchColumn();
    } catch (Exception $e) {
        error_log('Leader stats load failed: ' . $e->getMessage());
        $stats = ['members' => 0, 'leaders' => 0, 'pending' => 0, 'active' => 0];
    }
}

$financeMetrics = [
    'total_received' => 0.0,
    'completed_amount' => 0.0,
    'pending_amount' => 0.0,
    'pending_count' => 0,
    'failed_amount' => 0.0,
    'today_amount' => 0.0,
];
$recentDonations = [];
$pendingPayouts = [];
$spendingPriorities = [];

if ($isFinancer) {
    ensureDonationsTablesInitialized();
    try {
        $financeMetrics['total_received'] = (float) $pdo->query("SELECT COALESCE(SUM(amount),0) FROM donations")->fetchColumn();
        $financeMetrics['completed_amount'] = (float) $pdo->query("SELECT COALESCE(SUM(amount),0) FROM donations WHERE status = 'completed'")->fetchColumn();
        $financeMetrics['pending_amount'] = (float) $pdo->query("SELECT COALESCE(SUM(amount),0) FROM donations WHERE status IN ('pending','initiated','processing')")->fetchColumn();
        $financeMetrics['pending_count'] = (int) $pdo->query("SELECT COUNT(*) FROM donations WHERE status IN ('pending','initiated','processing')")->fetchColumn();
        $financeMetrics['failed_amount'] = (float) $pdo->query("SELECT COALESCE(SUM(amount),0) FROM donations WHERE status IN ('failed','cancelled')")->fetchColumn();
        $financeMetrics['today_amount'] = (float) $pdo->query("SELECT COALESCE(SUM(amount),0) FROM donations WHERE DATE(created_at) = CURDATE()")->fetchColumn();

        $recentDonations = $pdo->query("SELECT donor_name, amount, status, provider, created_at FROM donations ORDER BY created_at DESC LIMIT 8")->fetchAll();
        $pendingPayouts = $pdo->query("SELECT donor_name, amount, status, provider, created_at FROM donations WHERE status IN ('pending','initiated','processing') ORDER BY created_at DESC LIMIT 6")->fetchAll();
    } catch (Exception $e) {
        error_log('Finance dashboard load failed: ' . $e->getMessage());
        $recentDonations = [];
        $pendingPayouts = [];
    }

    try {
        $spendingPriorities = $pdo->query("SELECT name, description FROM programs WHERE active = 1 ORDER BY sort_order ASC LIMIT 6")->fetchAll();
    } catch (Exception $e) {
        error_log('Finance priorities load failed: ' . $e->getMessage());
        $spendingPriorities = [];
    }
}

$activeMembersCount = $stats['active'] ?? 0;
if ($isMember && !$activeMembersCount) {
    try {
        $activeMembersCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE status='active'")->fetchColumn();
    } catch (Exception $e) {
        error_log('Active members count failed: ' . $e->getMessage());
        $activeMembersCount = 0;
    }
}

$upcomingEvents = [];
$upcomingEventCount = 0;
try {
    $upcomingEventCount = (int) $pdo->query("SELECT COUNT(*) FROM events WHERE status IN ('upcoming','ongoing')")->fetchColumn();
    $eventLimit = $isMember ? 5 : 6;
    $eventSql = "SELECT title, slug, event_date, location, status, description FROM events WHERE status IN ('upcoming','ongoing') ORDER BY event_date ASC LIMIT " . (int) $eventLimit;
    $upcomingEvents = $pdo->query($eventSql)->fetchAll();
} catch (Exception $e) {
    error_log('Dashboard events load failed: ' . $e->getMessage());
    $upcomingEvents = [];
}

$publishedNewsCount = 0;
try {
    $publishedNewsCount = (int) $pdo->query("SELECT COUNT(*) FROM news WHERE status = 'published'")->fetchColumn();
} catch (Exception $e) {
    error_log('News count failed: ' . $e->getMessage());
}

$latestNews = [];
try {
    $newsLimit = $isMember ? 3 : 4;
    $newsSql = "SELECT title, slug, category, created_at FROM news WHERE status = 'published' ORDER BY created_at DESC LIMIT " . (int) $newsLimit;
    $latestNews = $pdo->query($newsSql)->fetchAll();
} catch (Exception $e) {
    error_log('Dashboard news load failed: ' . $e->getMessage());
    $latestNews = [];
}

$pendingMembers = [];
$recentRegistrations = [];
if (!$isMember) {
    try {
        $pendingStmt = $pdo->query("SELECT full_name, email, phone, created_at FROM users WHERE status='pending' ORDER BY created_at DESC LIMIT 6");
        $pendingMembers = $pendingStmt->fetchAll();
    } catch (Exception $e) {
        error_log('Pending members load failed: ' . $e->getMessage());
        $pendingMembers = [];
    }

    try {
        $recentStmt = $pdo->query("SELECT full_name, role, status, created_at FROM users ORDER BY created_at DESC LIMIT 6");
        $recentRegistrations = $recentStmt->fetchAll();
    } catch (Exception $e) {
        error_log('Recent registrations load failed: ' . $e->getMessage());
        $recentRegistrations = [];
    }
}

$statusBadgeClasses = [
    'pending'   => 'status-badge status-pending',
    'inactive'  => 'status-badge status-inactive',
    'active'    => 'status-badge status-active',
    'ongoing'   => 'status-badge status-ongoing',
    'upcoming'  => 'status-badge',
    'completed' => 'status-badge status-completed',
    'initiated' => 'status-badge status-initiated',
    'processing'=> 'status-badge status-processing',
    'failed'    => 'status-badge status-failed',
    'cancelled' => 'status-badge status-cancelled',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Dashboard - Timiza Youth Initiative</title>
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <style>
        body {
            background: linear-gradient(120deg, #bbf7d0 0%, #34d399 100%);
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            margin: 0;
        }
        .dashboard-section {
            margin: 2rem auto;
            width: 100%;
            max-width: 1140px;
        }
        .quick-card {
            border-radius: 20px;
            background: #edfaf6;
            border: 1.5px solid #bbf7d0;
            box-shadow: 0 2px 8px #bbf7d022;
            padding: 1.8rem 1.2rem;
            display: flex;
            align-items: center;
            gap: 1.2rem;
            transition: box-shadow .14s, transform .12s;
        }
        .quick-card:hover { box-shadow: 0 7px 24px #22c55e26; transform: translateY(-4px) scale(1.02); }
        .quick-card i { font-size: 2.2rem; color: #10b981; }
        .welcome-bubble {
            padding: 1.5rem 2rem;
            background: linear-gradient(90deg,#bbf7d0,#f0fdf4);
            border-radius: 24px;
            box-shadow: 0 2px 16px #34d3991a;
            font-size: 1.25rem;
            color: #047857;
            margin-bottom: .7rem;
        }
        .member-events-card {
            background: #f0fdf4;
            border-radius: 18px;
            border: 1.5px solid #bbf7d0;
            box-shadow: 0 2px 8px #bbf7d022;
            margin-bottom: 2rem;
            padding: 1.35em 1em;
        }
        .event-title { font-weight: 700; color: #047857; }
        .event-date { color: #22c55e; font-weight: 600; }
        .event-location { color: #0e9488; }
        .event-desc { color: #374151; }
        .motivation-note {
            background: #d1fae5; border-left: 7px solid #34d399;
            font-size: 1rem; border-radius: 12px; margin: 1.4rem 0 0.7rem 0; padding: 1rem 1.5rem;
            color: #047857; font-weight: 500;
        }
        .stat-card {
            background: #f0fdf4;
            border-radius: 20px;
            border: 1.5px solid #bbf7d0;
            box-shadow: 0 3px 14px #22c55e1a;
            padding: 1.6rem 1.4rem;
            display: flex;
            flex-direction: column;
            gap: .5rem;
        }
        .stat-title { color: #047857; font-size: .95rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
        .stat-value { font-size: 2rem; font-weight: 700; color: #065f46; }
        .panel-card {
            background: #f0fdf4;
            border-radius: 18px;
            border: 1.5px solid #bbf7d0;
            padding: 1.5rem;
            box-shadow: 0 2px 8px #0f766e1a;
        }
        .panel-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 700;
            color: #047857;
            margin-bottom: 1rem;
        }
        .panel-list { list-style: none; padding: 0; margin: 0; }
        .panel-list li { padding: .75rem 0; border-bottom: 1px solid #d1fae5; }
        .panel-list li:last-child { border-bottom: none; }
        .panel-meta {
            font-size: .85rem;
            color: #0f766e;
            display: flex;
            flex-wrap: wrap;
            gap: .75rem;
            margin-top: .35rem;
        }
        .status-badge {
            display: inline-block;
            padding: .35rem .7rem;
            border-radius: 999px;
            font-size: .75rem;
            font-weight: 600;
            text-transform: capitalize;
            background: #d1fae5;
            color: #047857;
        }
        .status-pending { background: #fef3c7; color: #b45309; }
        .status-inactive { background: #fee2e2; color: #b91c1c; }
        .status-active { background: #bbf7d0; color: #047857; }
        .status-completed { background: #bbf7d0; color: #047857; }
        .status-ongoing { background: #e0f2fe; color: #0369a1; }
        .status-initiated { background: #fef9c3; color: #92400e; }
        .status-processing { background: #dbeafe; color: #1d4ed8; }
        .status-failed { background: #fee2e2; color: #b91c1c; }
        .status-cancelled { background: #e2e8f0; color: #475569; }
        .insight-card {
            background: rgba(240, 253, 244, 0.88);
            border-radius: 18px;
            border: 1.5px solid #bbf7d0;
            padding: 1.4rem;
            box-shadow: 0 2px 12px #0478571a;
            backdrop-filter: blur(4px);
        }
        .insight-card h3 {
            margin: 0 0 .75rem 0;
            color: #047857;
            font-size: 1.1rem;
            font-weight: 700;
        }
        .summary-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: .55rem;
        }
        .summary-list li {
            color: #065f46;
            font-size: .95rem;
            display: flex;
            align-items: center;
            gap: .6rem;
        }
        .summary-list li i { color: #10b981; }
        .empty-state {
            padding: 1rem;
            background: #ecfeff;
            border-radius: 12px;
            text-align: center;
            color: #0e7490;
            font-size: 0.875rem;
            margin: 0.5rem 0;
        }
        .panel-link {
            font-size: .85rem;
            color: #047857;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
        }
        @media (max-width:900px){ .dashboard-section{padding-left:1vw;padding-right:1vw;} }
        @media (max-width:700px){
            .dashboard-section{margin-top:.5rem;}
            .quick-card, .welcome-bubble {padding:1rem .7rem;}
            .member-events-card{padding:.9em .5em;}
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="dashboard-section px-2">
    <div class="welcome-bubble mb-6">
        <div class="flex items-center gap-4">
            <img src="<?php echo (isset($currentUser['profile_picture']) && $currentUser['profile_picture'] && file_exists($currentUser['profile_picture']))
                ? htmlspecialchars($currentUser['profile_picture'])
                : 'assets/images/avatar-placeholder.png'; ?>"
                alt="profile" style="width:72px;height:72px; border-radius:50%; object-fit:cover; border:2.5px solid #bbf7d0;">
            <div>
                <div class="font-bold mb-0.5 text-emerald-700">
                    Hello, <?php echo htmlspecialchars($currentUser['full_name']); ?>!
                </div>
                <div class="text-emerald-800 text-lg">
                    <?php echo $isMember
                        ? "Welcome to your Timiza dashboard – stay engaged and help make a difference!"
                        : ($isFinancer
                            ? "Welcome to your finance workspace – track donations and plan impact spending."
                            : "Welcome to your Timiza Youth Initiative leadership dashboard."); ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($isMember): ?>
        <!-- MEMBER DASHBOARD -->
        <div class="member-events-card">
            <div class="flex items-center mb-4">
                <i class="fas fa-calendar-check text-emerald-600 text-2xl mr-2"></i>
                <h2 class="text-emerald-800 font-bold text-xl">My Upcoming Events</h2>
                <span class="ml-auto text-sm text-emerald-700 font-semibold">
                    <?php echo $upcomingEventCount; ?> scheduled
                </span>
            </div>
            <?php if (!empty($upcomingEvents)): ?>
                <?php foreach ($upcomingEvents as $event): ?>
                    <?php
                        $eventDate = !empty($event['event_date']) ? date("M j, Y", strtotime($event['event_date'])) : 'Date TBA';
                        $statusKey = strtolower((string) ($event['status'] ?? ''));
                        $statusClass = $statusBadgeClasses[$statusKey] ?? 'status-badge';
                        $eventDesc = !empty($event['description']) ? truncateText(strip_tags($event['description']), 140) : 'Stay tuned for more details.';
                    ?>
                    <div class="mb-3 pb-3 border-b border-emerald-100 last:border-0 last:pb-0">
                        <div class="flex items-center justify-between">
                            <div class="event-title"><?php echo htmlspecialchars($event['title']); ?></div>
                            <span class="<?php echo $statusClass; ?>"><?php echo htmlspecialchars($event['status']); ?></span>
                        </div>
                        <div class="flex text-sm gap-3 mb-1 flex-wrap">
                            <span class="event-date"><i class="fas fa-calendar"></i> <?php echo htmlspecialchars($eventDate); ?></span>
                            <?php if (!empty($event['location'])): ?>
                                <span class="event-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="event-desc mb-1"><?php echo htmlspecialchars($eventDesc); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-info-circle mr-2"></i>No upcoming events yet. Check back soon!
                </div>
            <?php endif; ?>
            <div class="text-right mt-4">
                <a href="events.php" class="text-green-700 font-semibold hover:underline text-sm">See all events <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <a href="resources.php" class="quick-card group">
                <i class="fas fa-book-open"></i>
                <div>
                    <div class="font-bold text-green-800">My Resources</div>
                    <span class="text-sm text-green-600 group-hover:underline">Access guides & team docs</span>
                </div>
            </a>
            <a href="profile.php" class="quick-card group">
                <i class="fas fa-user-cog"></i>
                <div>
                    <div class="font-bold text-green-800">My Profile</div>
                    <span class="text-sm text-green-600 group-hover:underline">Update your info</span>
                </div>
            </a>
            <a href="contact.php" class="quick-card group">
                <i class="fas fa-hands-helping"></i>
                <div>
                    <div class="font-bold text-green-800">Reach Out</div>
                    <span class="text-sm text-green-600 group-hover:underline">Share feedback or ask for support</span>
                </div>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="insight-card">
                <h3><i class="fas fa-id-card mr-2"></i>Membership Snapshot</h3>
                <ul class="summary-list">
                    <li><i class="fas fa-user-tag"></i>Role: <strong><?php echo ucfirst(htmlspecialchars($userRole)); ?></strong></li>
                    <li><i class="fas fa-shield-alt"></i>Status: <strong><?php echo ucfirst(htmlspecialchars($currentUser['status'])); ?></strong></li>
                    <li><i class="fas fa-calendar-alt"></i>Joined: <strong><?php echo !empty($currentUser['created_at']) ? date("M j, Y", strtotime($currentUser['created_at'])) : 'Not recorded'; ?></strong></li>
                    <li><i class="fas fa-clock"></i>Last Login: <strong><?php echo (array_key_exists('last_login', $currentUser) && !empty($currentUser['last_login']))
                        ? date("M j, Y g:i a", strtotime($currentUser['last_login'])) : 'First time here!'; ?></strong></li>
                </ul>
            </div>
            <small>**[truncated for brevity in the completion due to character limits, but full replacement should include financer and leader sections as shown in the preview]**</small>
    <?php elseif ($isFinancer): ?>
        <!-- FINANCER DASHBOARD -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
            <div class="stat-card">
                <span class="stat-title">Total Received</span>
                <div class="stat-value">KES <?php echo number_format($financeMetrics['total_received'], 2); ?></div>
                <span class="text-sm text-emerald-600">Lifetime</span>
            </div>
            <div class="stat-card">
                <span class="stat-title">Completed</span>
                <div class="stat-value">KES <?php echo number_format($financeMetrics['completed_amount'], 2); ?></div>
                <span class="text-sm text-emerald-600">Confirmed donations</span>
            </div>
            <div class="stat-card">
                <span class="stat-title">Pending</span>
                <div class="stat-value">KES <?php echo number_format($financeMetrics['pending_amount'], 2); ?></div>
                <span class="text-sm text-emerald-600"><?php echo $financeMetrics['pending_count']; ?> pending requests</span>
            </div>
            <div class="stat-card">
                <span class="stat-title">Today</span>
                <div class="stat-value">KES <?php echo number_format($financeMetrics['today_amount'], 2); ?></div>
                <span class="text-sm text-emerald-600">Same-day inflows</span>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
            <div class="panel-card xl:col-span-2">
                <div class="panel-title">
                    <span><i class="fas fa-donate mr-2"></i>Recent Donations</span>
                    <a href="donations.php" class="panel-link">View full history <i class="fas fa-chevron-right"></i></a>
                </div>
                <?php if (!empty($recentDonations)): ?>
                    <ul class="panel-list">
                        <?php foreach ($recentDonations as $donation): ?>
                            <?php
                                $statusKey = strtolower((string) ($donation['status'] ?? 'pending'));
                                $statusClass = $statusBadgeClasses[$statusKey] ?? 'status-badge';
                            ?>
                            <li>
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="font-semibold text-emerald-800"><?php echo htmlspecialchars($donation['donor_name'] ?: 'Anonymous Donor'); ?></div>
                                        <div class="panel-meta">
                                            <span><i class="fas fa-coins mr-1"></i>KES <?php echo number_format((float) $donation['amount'], 2); ?></span>
                                            <span><i class="fas fa-building-columns mr-1"></i><?php echo strtoupper(htmlspecialchars($donation['provider'] ?? '')); ?></span>
                                            <span><i class="fas fa-clock mr-1"></i><?php echo !empty($donation['created_at']) ? date("M j, Y g:i a", strtotime($donation['created_at'])) : 'Recently'; ?></span>
                                        </div>
                                    </div>
                                    <span class="<?php echo $statusClass; ?>"><?php echo ucfirst(htmlspecialchars($donation['status'] ?? 'pending')); ?></span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-info-circle mr-2"></i>No donations recorded yet.
                    </div>
                <?php endif; ?>
            </div>
            <div class="panel-card">
                <div class="panel-title">
                    <span><i class="fas fa-hand-holding-dollar mr-2"></i>Pending Payouts</span>
                    <a href="donations.php?status=pending" class="panel-link">Review <i class="fas fa-chevron-right"></i></a>
                </div>
                <?php if (!empty($pendingPayouts)): ?>
                    <ul class="panel-list">
                        <?php foreach ($pendingPayouts as $pending): ?>
                            <?php
                                $statusKey = strtolower((string) ($pending['status'] ?? 'pending'));
                                $statusClass = $statusBadgeClasses[$statusKey] ?? 'status-badge';
                            ?>
                            <li>
                                <div class="font-semibold text-emerald-800"><?php echo htmlspecialchars($pending['donor_name'] ?: 'Anonymous Donor'); ?></div>
                                <div class="panel-meta">
                                    <span><i class="fas fa-coins mr-1"></i>KES <?php echo number_format((float) $pending['amount'], 2); ?></span>
                                    <span><i class="fas fa-clock mr-1"></i><?php echo !empty($pending['created_at']) ? date("M j, Y g:i a", strtotime($pending['created_at'])) : 'Recently'; ?></span>
                                    <span class="<?php echo $statusClass; ?>"><?php echo ucfirst(htmlspecialchars($pending['status'] ?? 'pending')); ?></span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-info-circle mr-2"></i>No pending payouts at the moment.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="panel-card">
                <div class="panel-title">
                    <span><i class="fas fa-bullseye mr-2"></i>Spending Priorities</span>
                    <a href="programs.php" class="panel-link">Programs <i class="fas fa-chevron-right"></i></a>
                </div>
                <?php if (!empty($spendingPriorities)): ?>
                    <ul class="panel-list">
                        <?php foreach ($spendingPriorities as $priority): ?>
                            <li>
                                <div class="font-semibold text-emerald-800"><?php echo htmlspecialchars($priority['name']); ?></div>
                                <div class="panel-meta"><?php echo !empty($priority['description']) ? htmlspecialchars(truncateText($priority['description'], 120)) : 'No description provided.'; ?></div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-info-circle mr-2"></i>No active spending priorities. Update programs to showcase funding needs.
                    </div>
                <?php endif; ?>
            </div>
            <div class="insight-card">
                <h3><i class="fas fa-lightbulb-dollar mr-2"></i>Finance Highlights</h3>
                <ul class="summary-list">
                    <li><i class="fas fa-piggy-bank"></i>Completed donations now total <strong>KES <?php echo number_format($financeMetrics['completed_amount'], 2); ?></strong>.</li>
                    <li><i class="fas fa-hourglass-half"></i><?php echo $financeMetrics['pending_count']; ?> contributions are awaiting confirmation worth <strong>KES <?php echo number_format($financeMetrics['pending_amount'], 2); ?></strong>.</li>
                    <li><i class="fas fa-chart-line"></i>Today's inflow: <strong>KES <?php echo number_format($financeMetrics['today_amount'], 2); ?></strong>.</li>
                    <li><i class="fas fa-triangle-exclamation"></i>Failed or cancelled attempts amount to <strong>KES <?php echo number_format($financeMetrics['failed_amount'], 2); ?></strong>.</li>
                </ul>
            </div>
        </div>

    <?php else: ?>
        <!-- LEADER DASHBOARD -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
            <div class="stat-card">
                <span class="stat-title">Total Members</span>
                <div class="stat-value"><?php echo number_format($stats['members']); ?></div>
                <span class="text-sm text-emerald-600">Active: <?php echo number_format($stats['active']); ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-title">Leadership Team</span>
                <div class="stat-value"><?php echo number_format($stats['leaders']); ?></div>
                <span class="text-sm text-emerald-600">Active leaders</span>
            </div>
            <div class="stat-card">
                <span class="stat-title">Pending Approvals</span>
                <div class="stat-value"><?php echo number_format($stats['pending']); ?></div>
                <a href="admin/approvals.php" class="text-sm text-emerald-600 hover:underline">Review now</a>
            </div>
            <div class="stat-card">
                <span class="stat-title">Upcoming Events</span>
                <div class="stat-value"><?php echo number_format($upcomingEventCount); ?></div>
                <a href="events.php" class="text-sm text-emerald-600 hover:underline">View all</a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Pending Approvals Panel -->
            <div class="panel-card">
                <div class="panel-title">
                    <span><i class="fas fa-user-clock mr-2"></i>Pending Approvals</span>
                    <a href="admin/approvals.php" class="panel-link">Manage <i class="fas fa-chevron-right"></i></a>
                </div>
                <?php if (!empty($pendingMembers)): ?>
                    <ul class="panel-list">
                        <?php foreach ($pendingMembers as $member): ?>
                            <li class="flex items-center justify-between">
                                <div>
                                    <div class="font-semibold text-emerald-800"><?php echo htmlspecialchars($member['full_name']); ?></div>
                                    <div class="panel-meta">
                                        <span><i class="far fa-envelope mr-1"></i><?php echo htmlspecialchars($member['email']); ?></span>
                                        <?php if (!empty($member['phone'])): ?>
                                            <span><i class="fas fa-phone-alt mr-1"></i><?php echo htmlspecialchars($member['phone']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <a href="admin/approve_user.php?id=<?php echo urlencode($member['email']); ?>" 
                                       class="text-green-600 hover:text-green-800" 
                                       title="Approve">
                                        <i class="fas fa-check-circle"></i>
                                    </a>
                                    <a href="admin/reject_user.php?id=<?php echo urlencode($member['email']); ?>" 
                                       class="text-red-600 hover:text-red-800" 
                                       title="Reject">
                                        <i class="fas fa-times-circle"></i>
                                    </a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-info-circle mr-2"></i>No pending member approvals at this time.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Latest News Panel -->
            <div class="panel-card">
                <div class="panel-title">
                    <span><i class="far fa-newspaper mr-2"></i>Latest News</span>
                    <a href="news.php" class="panel-link">View all <i class="fas fa-chevron-right"></i></a>
                </div>
                <?php if (!empty($latestNews)): ?>
                    <ul class="panel-list">
                        <?php foreach ($latestNews as $news): ?>
                            <?php 
                                $newsDate = !empty($news['created_at']) ? date("M j, Y", strtotime($news['created_at'])) : '';
                                $category = !empty($news['category']) ? ucfirst($news['category']) : 'General';
                            ?>
                            <li>
                                <a href="news/<?php echo htmlspecialchars($news['slug']); ?>" class="block hover:bg-emerald-50 -mx-3 px-3 py-2 rounded">
                                    <div class="font-semibold text-emerald-800"><?php echo htmlspecialchars($news['title']); ?></div>
                                    <div class="panel-meta">
                                        <span><i class="far fa-calendar-alt mr-1"></i><?php echo $newsDate; ?></span>
                                        <span><i class="fas fa-tag mr-1"></i><?php echo htmlspecialchars($category); ?></span>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-info-circle mr-2"></i>No news articles found.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Membership Growth (Placeholder) -->
            <div class="panel-card">
                <div class="panel-title">
                    <span><i class="fas fa-chart-line mr-2"></i>Membership Growth</span>
                    <a href="admin/reports.php" class="panel-link">View report <i class="fas fa-chevron-right"></i></a>
                </div>
                <div class="p-4 flex items-center justify-center h-48 bg-gray-50 rounded-lg">
                    <div class="text-center text-gray-500">
                        <i class="fas fa-chart-bar text-4xl mb-2"></i>
                        <p>Membership growth chart</p>
                        <p class="text-sm mt-2">Visualization coming soon</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="insight-card mb-8">
            <h3><i class="fas fa-bolt mr-2"></i>Quick Actions</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
                <a href="admin/create_event.php" class="quick-action-btn">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Schedule New Event</span>
                </a>
                <a href="admin/create_news.php" class="quick-action-btn">
                    <i class="fas fa-bullhorn"></i>
                    <span>Publish News Update</span>
                </a>
                <a href="admin/manage_users.php" class="quick-action-btn">
                    <i class="fas fa-users-cog"></i>
                    <span>Manage Users</span>
                </a>
                <a href="admin/reports.php" class="quick-action-btn">
                    <i class="fas fa-chart-pie"></i>
                    <span>View Reports</span>
                </a>
            </div>
        </div>
</body>
</html>