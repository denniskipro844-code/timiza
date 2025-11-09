<?php
$page_title = "Manage News";
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAuth();

$uploadDirectory = dirname(__DIR__) . '/assets/images/news/';
$relativeImageDirectory = 'assets/images/news/';

if (!is_dir($uploadDirectory)) {
    @mkdir($uploadDirectory, 0755, true);
}

if (!function_exists('manage_news_process_upload')) {
    function manage_news_process_upload(array $file, string $uploadDirectory, string $relativeDirectory): array
    {
        if (empty($file) || (!isset($file['name']) && !isset($file['tmp_name'])) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE || empty($file['name'])) {
            return ['success' => true, 'path' => null];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Image upload failed. Please try again.'];
        }

        $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $detectedType = @mime_content_type($file['tmp_name']);
        if (!$detectedType || !in_array($detectedType, $allowedMime, true)) {
            return ['success' => false, 'error' => 'Please upload a valid image (JPG, PNG, GIF, or WebP).'];
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }

        $baseName = pathinfo($file['name'], PATHINFO_FILENAME);
        $sluggedName = createSlug($baseName) ?: 'news-image';
        $filename = $sluggedName . '-' . time() . '.' . $extension;

        $destinationPath = rtrim($uploadDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destinationPath)) {
            return ['success' => false, 'error' => 'Unable to save the uploaded image.'];
        }

        return [
            'success' => true,
            'path' => rtrim($relativeDirectory, '/\\') . '/' . $filename
        ];
    }
}

if (!function_exists('manage_news_delete_file')) {
    function manage_news_delete_file(?string $storedPath, string $uploadDirectory): void
    {
        if (empty($storedPath)) {
            return;
        }

        $filename = basename($storedPath);
        $fullPath = rtrim($uploadDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}

$message = '';
$currentArticle = null;

// Flash messages
if (isset($_GET['status'])) {
    $statusMessages = [
        'added' => ['success' => true, 'text' => 'News article added successfully!'],
        'updated' => ['success' => true, 'text' => 'News article updated successfully!'],
        'deleted' => ['success' => true, 'text' => 'News article deleted successfully!'],
        'error' => ['success' => false, 'text' => 'An error occurred. Please try again.']
    ];

    if (isset($statusMessages[$_GET['status']])) {
        $message = $statusMessages[$_GET['status']];
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    try {
        switch ($action) {
            case 'add_news':
                $title = sanitizeInput($_POST['title']);
                $content = sanitizeInput($_POST['content']);
                $excerpt = sanitizeInput($_POST['excerpt']);
                $category = sanitizeInput($_POST['category']);

                if (empty($title) || empty($content)) {
                    $message = ['success' => false, 'text' => 'Title and content are required.'];
                    break;
                }

                $uploadResult = manage_news_process_upload($_FILES['image'] ?? [], $uploadDirectory, $relativeImageDirectory);
                if (!$uploadResult['success']) {
                    $message = ['success' => false, 'text' => $uploadResult['error']];
                    break;
                }

                $slug = createSlug($title);
                $stmt = $pdo->prepare("INSERT INTO news (title, slug, excerpt, content, image, category, author, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'published', NOW())");
                $stmt->execute([
                    $title,
                    $slug,
                    $excerpt,
                    $content,
                    $uploadResult['path'],
                    $category,
                    $_SESSION['admin_username']
                ]);

                header('Location: manage-news.php?status=added');
                exit();

            case 'update_news':
                $newsId = isset($_POST['news_id']) ? (int) $_POST['news_id'] : 0;
                $title = sanitizeInput($_POST['title']);
                $content = sanitizeInput($_POST['content']);
                $excerpt = sanitizeInput($_POST['excerpt']);
                $category = sanitizeInput($_POST['category']);
                $existingImage = sanitizeInput($_POST['existing_image'] ?? '');

                if (!$newsId || empty($title) || empty($content)) {
                    $message = ['success' => false, 'text' => 'Unable to update article. Missing required information.'];
                    break;
                }

                $uploadResult = manage_news_process_upload($_FILES['image'] ?? [], $uploadDirectory, $relativeImageDirectory);
                if (!$uploadResult['success']) {
                    $message = ['success' => false, 'text' => $uploadResult['error']];
                    break;
                }

                $imagePath = $existingImage;
                if (!empty($uploadResult['path'])) {
                    manage_news_delete_file($existingImage, $uploadDirectory);
                    $imagePath = $uploadResult['path'];
                }

                $slug = createSlug($title);
                $stmt = $pdo->prepare("UPDATE news SET title = ?, slug = ?, excerpt = ?, content = ?, image = ?, category = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$title, $slug, $excerpt, $content, $imagePath, $category, $newsId]);

                header('Location: manage-news.php?status=updated');
                exit();

            case 'delete_news':
                $newsId = isset($_POST['news_id']) ? (int) $_POST['news_id'] : 0;
                $existingImage = sanitizeInput($_POST['existing_image'] ?? '');

                if (!$newsId) {
                    $message = ['success' => false, 'text' => 'Unable to delete article.'];
                    break;
                }

                manage_news_delete_file($existingImage, $uploadDirectory);

                $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
                $stmt->execute([$newsId]);

                header('Location: manage-news.php?status=deleted');
                exit();
        }
    } catch (Exception $e) {
        error_log('Manage news error: ' . $e->getMessage());
        header('Location: manage-news.php?status=error');
        exit();
    }
}

// Load article for editing
if (isset($_GET['edit_id'])) {
    $editId = (int) $_GET['edit_id'];
    if ($editId) {
        $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
        $stmt->execute([$editId]);
        $currentArticle = $stmt->fetch();

        if (!$currentArticle) {
            $message = ['success' => false, 'text' => 'Requested article was not found.'];
        }
    }
}

$isEditing = $currentArticle !== null;
$formAction = $isEditing ? 'update_news' : 'add_news';
$formHeading = $isEditing ? 'Edit Article' : 'Add New Article';
$submitLabel = $isEditing ? 'Update Article' : 'Add Article';
$categories = ['News', 'Events', 'Programs', 'Community'];

// Get all news articles
$news = [];
try {
    $stmt = $pdo->query("SELECT * FROM news ORDER BY created_at DESC");
    $news = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('Fetch news error: ' . $e->getMessage());
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
        }
    </script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="font-poppins bg-neutral">
    <?php include '../includes/nav-admin.php'; ?>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-dark">Manage News</h1>
            <p class="text-gray-600">Add and manage news articles and events</p>
        </div>
        
        <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $message['success'] ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-red-100 text-red-700 border border-red-300'; ?>">
            <?php echo htmlspecialchars($message['text']); ?>
        </div>
        <?php endif; ?>
        
        <!-- Add/Edit News Form -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <div class="flex items-start justify-between mb-4">
                <h2 class="text-xl font-bold text-dark"><?php echo $formHeading; ?></h2>
                <?php if ($isEditing): ?>
                <a href="manage-news.php" class="text-sm text-primary hover:underline">Cancel editing</a>
                <?php endif; ?>
            </div>
            <form method="POST" class="space-y-4" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $formAction; ?>">
                <?php if ($isEditing): ?>
                    <input type="hidden" name="news_id" value="<?php echo (int) $currentArticle['id']; ?>">
                    <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($currentArticle['image'] ?? ''); ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                        <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($currentArticle['title'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select id="category" name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category; ?>" <?php echo (($currentArticle['category'] ?? 'News') === $category) ? 'selected' : ''; ?>><?php echo $category; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-2">Excerpt</label>
                    <textarea id="excerpt" name="excerpt" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="Brief summary of the article..."><?php echo htmlspecialchars($currentArticle['excerpt'] ?? ''); ?></textarea>
                </div>
                
                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Content *</label>
                    <textarea id="content" name="content" rows="8" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="Full article content..."><?php echo htmlspecialchars($currentArticle['content'] ?? ''); ?></textarea>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Feature Image</label>
                        <input type="file" name="image" accept="image/*" class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-opacity-90">
                        <p class="text-xs text-gray-500 mt-2">Upload JPG, PNG, GIF, or WebP up to 2MB.</p>
                    </div>
                    <?php if (!empty($currentArticle['image'])): ?>
                    <div>
                        <p class="text-sm font-medium text-gray-700 mb-2">Current Image</p>
                        <img src="../<?php echo htmlspecialchars($currentArticle['image']); ?>" alt="Current image" class="h-32 w-full object-cover rounded border">
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="flex items-center justify-end space-x-3">
                    <?php if ($isEditing): ?>
                        <a href="manage-news.php" class="text-sm text-gray-500 hover:underline">Reset form</a>
                    <?php endif; ?>
                    <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-opacity-90 transition-all">
                        <i class="fas fa-save mr-2"></i><?php echo $submitLabel; ?>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- News List -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-dark mb-4">All Articles</h2>
            
            <?php if (!empty($news)): ?>
            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-3 px-4">Image</th>
                            <th class="text-left py-3 px-4">Title</th>
                            <th class="text-left py-3 px-4">Category</th>
                            <th class="text-left py-3 px-4">Author</th>
                            <th class="text-left py-3 px-4">Date</th>
                            <th class="text-left py-3 px-4">Status</th>
                            <th class="text-left py-3 px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($news as $article): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <?php if (!empty($article['image'])): ?>
                                    <img src="../<?php echo htmlspecialchars($article['image']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="h-16 w-16 object-cover rounded border">
                                <?php else: ?>
                                    <div class="h-16 w-16 bg-gray-100 border flex items-center justify-center text-xs text-gray-400 rounded">No image</div>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4">
                                <div>
                                    <h3 class="font-medium text-dark"><?php echo htmlspecialchars($article['title']); ?></h3>
                                    <p class="text-sm text-gray-600"><?php echo truncateText($article['excerpt'], 80); ?></p>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="bg-primary text-white text-xs px-2 py-1 rounded-full"><?php echo htmlspecialchars($article['category']); ?></span>
                            </td>
                            <td class="py-3 px-4 text-gray-600"><?php echo htmlspecialchars($article['author']); ?></td>
                            <td class="py-3 px-4 text-gray-600"><?php echo formatDate($article['created_at']); ?></td>
                            <td class="py-3 px-4">
                                <span class="<?php echo $article['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?> text-xs px-2 py-1 rounded-full">
                                    <?php echo ucfirst($article['status']); ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap space-x-3">
                                <a href="manage-news.php?edit_id=<?php echo (int) $article['id']; ?>" class="text-sm text-primary hover:underline">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </a>
                                <form method="POST" class="inline" onsubmit="return confirm('Delete this article? This action cannot be undone.');">
                                    <input type="hidden" name="action" value="delete_news">
                                    <input type="hidden" name="news_id" value="<?php echo (int) $article['id']; ?>">
                                    <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($article['image'] ?? ''); ?>">
                                    <button type="submit" class="text-sm text-red-600 hover:underline">
                                        <i class="fas fa-trash-alt mr-1"></i>Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-gray-500 text-center py-8">No articles found. Add your first article above.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>