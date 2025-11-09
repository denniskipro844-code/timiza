<?php
$page_title = "Contact Messages";
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAuth();

try {
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS contact_message_replies (
            id INT AUTO_INCREMENT PRIMARY KEY,
            message_id INT NOT NULL,
            subject VARCHAR(255) DEFAULT NULL,
            body TEXT NOT NULL,
            sent_success TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_message_reply_message FOREIGN KEY (message_id)
                REFERENCES contact_messages(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
} catch (Exception $e) {
    error_log('Ensure contact_message_replies table failed: ' . $e->getMessage());
}

$statusNotice = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'reply_message') {
        $messageId = isset($_POST['message_id']) ? (int) $_POST['message_id'] : 0;
        $replySubject = trim($_POST['reply_subject'] ?? '');
        $replyBody = trim($_POST['reply_body'] ?? '');

        if (!$messageId || $replyBody === '') {
            $statusNotice = ['success' => false, 'text' => 'Reply message cannot be empty.'];
        } else {
            $stmt = $pdo->prepare('SELECT * FROM contact_messages WHERE id = ? LIMIT 1');
            $stmt->execute([$messageId]);
            $message = $stmt->fetch();

            if (!$message) {
                $statusNotice = ['success' => false, 'text' => 'Selected message was not found.'];
            } else {
                if ($replySubject === '') {
                    $replySubject = 'Re: ' . ($message['subject'] ?? 'Your message');
                }

                $recipientEmail = $message['email'];
                $mailSent = false;

                if (filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                    $mailSent = sendEmail(
                        $recipientEmail,
                        $replySubject,
                        $replyBody,
                        GMAIL_FROM_EMAIL,
                        GMAIL_FROM_NAME,
                        true
                    );
                }

                try {
                    $insert = $pdo->prepare('INSERT INTO contact_message_replies (message_id, subject, body, sent_success) VALUES (?, ?, ?, ?)');
                    $insert->execute([
                        $messageId,
                        $replySubject,
                        $replyBody,
                        $mailSent ? 1 : 0
                    ]);

                    if ($mailSent) {
                        $update = $pdo->prepare('UPDATE contact_messages SET status = "replied" WHERE id = ? LIMIT 1');
                        $update->execute([$messageId]);
                        header('Location: messages.php?id=' . $messageId . '&status=reply_success');
                        exit();
                    }

                    header('Location: messages.php?id=' . $messageId . '&status=reply_saved');
                    exit();
                } catch (Exception $e) {
                    error_log('Failed to log reply: ' . $e->getMessage());
                    header('Location: messages.php?id=' . $messageId . '&status=reply_error');
                    exit();
                }
            }
        }
    }

    if ($action === 'update_status') {
        $messageId = isset($_POST['message_id']) ? (int) $_POST['message_id'] : 0;
        $newStatus = $_POST['status'] ?? 'read';
        $allowedStatuses = ['unread', 'read', 'replied'];

        if ($messageId && in_array($newStatus, $allowedStatuses, true)) {
            $stmt = $pdo->prepare('UPDATE contact_messages SET status = ? WHERE id = ? LIMIT 1');
            $stmt->execute([$newStatus, $messageId]);
            header('Location: messages.php?id=' . $messageId . '&status=status_updated');
            exit();
        }
    }
}

if (!$statusNotice && isset($_GET['status'])) {
    $statusMessages = [
        'reply_success' => ['success' => true, 'text' => 'Reply sent and logged successfully.'],
        'reply_saved' => ['success' => true, 'text' => 'Reply saved locally, but email delivery could not be confirmed.'],
        'reply_error' => ['success' => false, 'text' => 'Unable to send reply. Please try again later.'],
        'status_updated' => ['success' => true, 'text' => 'Message status updated.']
    ];
    $statusNotice = $statusMessages[$_GET['status']] ?? null;
}

$messages = [];
try {
    $stmt = $pdo->query('SELECT * FROM contact_messages ORDER BY created_at DESC');
    $messages = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('Fetch messages failed: ' . $e->getMessage());
}

$selectedId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!$selectedId && !empty($messages)) {
    $selectedId = (int) $messages[0]['id'];
}

$selectedMessage = null;
$messageReplies = [];

if ($selectedId) {
    $stmt = $pdo->prepare('SELECT * FROM contact_messages WHERE id = ? LIMIT 1');
    $stmt->execute([$selectedId]);
    $selectedMessage = $stmt->fetch();

    if ($selectedMessage) {
        if ($selectedMessage['status'] === 'unread') {
            $update = $pdo->prepare('UPDATE contact_messages SET status = "read" WHERE id = ? LIMIT 1');
            $update->execute([$selectedId]);
            $selectedMessage['status'] = 'read';
        }

        $stmt = $pdo->prepare('SELECT * FROM contact_message_replies WHERE message_id = ? ORDER BY created_at DESC');
        $stmt->execute([$selectedId]);
        $messageReplies = $stmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Timiza Youth Initiative</title>
    <link rel="icon" href="../assets/images/favicon.ico" type="image/x-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#007B5F',
                        secondary: '#00BFA6',
                        accent: '#FFD166',
                        neutral: '#F8FAFC',
                        dark: '#1E293B',
                        teal: '#009688'
                    },
                    fontFamily: {
                        'poppins': ['Poppins', 'sans-serif']
                    }
                }
            }
        };
    </script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="font-poppins bg-neutral min-h-screen">
    <?php include '../includes/nav-admin.php'; ?>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-dark">Contact Messages</h1>
                <p class="text-gray-600">Review, manage, and reply to incoming messages.</p>
            </div>
        </div>

        <?php if ($statusNotice): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $statusNotice['success'] ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-red-100 text-red-700 border border-red-300'; ?>">
                <?php echo htmlspecialchars($statusNotice['text']); ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <aside class="bg-white rounded-lg shadow lg:col-span-1">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <span class="font-semibold text-dark">Inbox</span>
                    <span class="text-sm text-gray-500"><?php echo count($messages); ?> messages</span>
                </div>

                <?php if (!empty($messages)): ?>
                    <div class="max-h-[600px] overflow-y-auto">
                        <?php foreach ($messages as $message): ?>
                            <?php
                                $isSelected = ((int) $message['id']) === $selectedId;
                                $statusBadgeClasses = [
                                    'unread' => 'bg-red-100 text-red-700',
                                    'read' => 'bg-gray-100 text-gray-600',
                                    'replied' => 'bg-green-100 text-green-700'
                                ];
                                $badgeClass = $statusBadgeClasses[$message['status']] ?? 'bg-gray-100 text-gray-600';
                            ?>
                            <a href="messages.php?id=<?php echo (int) $message['id']; ?>" class="block px-5 py-4 border-b border-gray-100 <?php echo $isSelected ? 'bg-primary/10 border-l-4 border-primary' : 'hover:bg-gray-50'; ?> transition-colors">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="text-sm font-semibold text-dark truncate w-3/4">
                                        <?php echo htmlspecialchars($message['name']); ?>
                                    </div>
                                    <span class="text-xs px-2 py-1 rounded-full <?php echo $badgeClass; ?>">
                                        <?php echo ucfirst($message['status']); ?>
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 mb-1 truncate">
                                    <?php echo htmlspecialchars($message['subject'] ?: 'No subject'); ?>
                                </div>
                                <p class="text-sm text-gray-600 truncate">
                                    <?php echo htmlspecialchars(truncateText($message['message'], 80)); ?>
                                </p>
                                <div class="text-xs text-gray-400 mt-2">
                                    <?php echo formatDate($message['created_at'], 'M j, Y g:i A'); ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="p-6 text-center text-gray-500">No messages yet.</div>
                <?php endif; ?>
            </aside>

            <section class="lg:col-span-2">
                <?php if ($selectedMessage): ?>
                    <div class="bg-white rounded-lg shadow p-6 mb-6">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-4">
                            <div>
                                <h2 class="text-2xl font-semibold text-dark mb-1">
                                    <?php echo htmlspecialchars($selectedMessage['subject'] ?: 'No subject provided'); ?>
                                </h2>
                                <div class="text-sm text-gray-500">
                                    <span class="font-medium text-dark"><?php echo htmlspecialchars($selectedMessage['name']); ?></span>
                                    <span class="mx-1">•</span>
                                    <a href="mailto:<?php echo htmlspecialchars($selectedMessage['email']); ?>" class="text-primary hover:underline">
                                        <?php echo htmlspecialchars($selectedMessage['email']); ?>
                                    </a>
                                </div>
                                <div class="text-xs text-gray-400 mt-2">
                                    Received <?php echo formatDate($selectedMessage['created_at'], 'l, M j Y g:i A'); ?>
                                </div>
                            </div>
                            <form method="POST" class="flex items-center space-x-2">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="message_id" value="<?php echo (int) $selectedMessage['id']; ?>">
                                <label for="status" class="text-sm font-medium text-gray-600">Status:</label>
                                <select id="status" name="status" class="text-sm border border-gray-300 rounded-lg px-3 py-1 focus:ring-2 focus:ring-primary focus:border-transparent" onchange="this.form.submit()">
                                    <?php foreach (['unread', 'read', 'replied'] as $statusOption): ?>
                                        <option value="<?php echo $statusOption; ?>" <?php echo $selectedMessage['status'] === $statusOption ? 'selected' : ''; ?>>
                                            <?php echo ucfirst($statusOption); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </div>

                        <div class="prose prose-sm max-w-none text-gray-700 border-t border-gray-100 pt-4">
                            <?php echo nl2br(htmlspecialchars($selectedMessage['message'])); ?>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6 mb-6">
                        <h3 class="text-lg font-semibold text-dark mb-4">Reply to <?php echo htmlspecialchars($selectedMessage['name']); ?></h3>
                        <form method="POST" class="space-y-4">
                            <input type="hidden" name="action" value="reply_message">
                            <input type="hidden" name="message_id" value="<?php echo (int) $selectedMessage['id']; ?>">
                            <div>
                                <label for="reply_subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                                <input type="text" id="reply_subject" name="reply_subject" value="<?php echo htmlspecialchars($_POST['reply_subject'] ?? ('Re: ' . ($selectedMessage['subject'] ?: 'Your message'))); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label for="reply_body" class="block text-sm font-medium text-gray-700 mb-1">Message *</label>
                                <textarea id="reply_body" name="reply_body" rows="6" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="Type your response..." required></textarea>
                                <p class="text-xs text-gray-500 mt-2">Replies are sent via Gmail SMTP. Ensure valid Gmail app credentials are configured.</p>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-opacity-90 transition">
                                    <i class="fas م"></i>Send Reply
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-dark">Reply History</h3>
                            <span class="text-sm text-gray-500"><?php echo count($messageReplies); ?> replies</span>
                        </div>

                        <?php if (!empty($messageReplies)): ?>
                            <div class="space-y-4">
                                <?php foreach ($messageReplies as $reply): ?>
                                    <div class="border border-gray-200 rounded-lg p-4 bg-neutral">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($reply['subject'] ?: 'No subject'); ?></span>
                                            <span class="text-xs <?php echo ((int) $reply['sent_success']) === 1 ? 'text-green-600' : 'text-yellow-600'; ?>">
                                                <?php echo ((int) $reply['sent_success']) === 1 ? 'Sent' : 'Saved (delivery failed)'; ?>
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-500 mb-2">
                                            <?php echo formatDate($reply['created_at'], 'M j, Y g:i A'); ?>
                                        </div>
                                        <div class="text-sm text-gray-700 whitespace-pre-wrap">
                                            <?php echo htmlspecialchars($reply['body']); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-gray-500 text-center py-6">No replies recorded yet.</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-white rounded-lg shadow p-10 text-center text-gray-500">
                        Select a message from the inbox to view its details and send a reply.
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>
</body>
</html>
