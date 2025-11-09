<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAuth();

$uploadDirectory = dirname(__DIR__) . '/assets/images/gallery/';
$relativeDirectory = 'assets/images/gallery/';

if (!is_dir($uploadDirectory)) {
    @mkdir($uploadDirectory, 0755, true);
}

if (!function_exists('manage_gallery_process_upload')) {
    function manage_gallery_process_upload(array $file, string $uploadDirectory): array
    {
        if (empty($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE || empty($file['name'])) {
            return ['success' => false, 'error' => 'Please choose an image to upload.'];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Image upload failed. Please try again.'];
        }

        $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $detectedType = @mime_content_type($file['tmp_name']);
        if (!$detectedType || !in_array($detectedType, $allowedMime, true)) {
            return ['success' => false, 'error' => 'Please upload a valid image (JPG, PNG, GIF, or WebP).'];
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION) ?: '');
        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }

        if ($extension === '') {
            return ['success' => false, 'error' => 'Could not determine the file extension.'];
        }

        $baseName = pathinfo($file['name'], PATHINFO_FILENAME) ?: 'gallery-image';
        $sluggedName = createSlug($baseName) ?: 'gallery-image';
        $filename = $sluggedName . '-' . time() . '.' . $extension;

        $destinationPath = rtrim($uploadDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destinationPath)) {
            return ['success' => false, 'error' => 'Unable to save the uploaded image.'];
        }

        return [
            'success' => true,
            'stored_filename' => $filename,
            'original_name' => $file['name']
        ];
    }
}

if (!function_exists('manage_gallery_delete_file')) {
    function manage_gallery_delete_file(?string $filename, string $uploadDirectory): void
    {
        if (empty($filename)) {
            return;
        }

        $fullPath = rtrim($uploadDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename($filename);
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}

$message = null;

if (isset($_GET['status'])) {
    $statusMessages = [
        'added' => ['success' => true, 'text' => 'Image uploaded successfully.'],
        'deleted' => ['success' => true, 'text' => 'Image removed successfully.'],
        'error' => ['success' => false, 'text' => 'An error occurred. Please try again.']
    ];

    if (isset($statusMessages[$_GET['status']])) {
        $message = $statusMessages[$_GET['status']];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        switch ($_POST['action']) {
            case 'add_image':
                $caption = sanitizeInput($_POST['caption'] ?? '');
                $category = sanitizeInput($_POST['category'] ?? 'General');

                $uploadResult = manage_gallery_process_upload($_FILES['image'] ?? [], $uploadDirectory);
                if (!$uploadResult['success']) {
                    $message = ['success' => false, 'text' => $uploadResult['error']];
                    break;
                }

                $stmt = $pdo->prepare("INSERT INTO gallery (filename, original_name, caption, category, active, created_at) VALUES (?, ?, ?, ?, 1, NOW())");
                $stmt->execute([
                    $uploadResult['stored_filename'],
                    $uploadResult['original_name'],
                    $caption,
                    $category
                ]);

                header('Location: manage-gallery.php?status=added');
                exit();

            case 'delete_image':
                $imageId = isset($_POST['image_id']) ? (int) $_POST['image_id'] : 0;
                if (!$imageId) {
                    $message = ['success' => false, 'text' => 'Invalid image selected.'];
                    break;
                }

                $stmt = $pdo->prepare("SELECT filename FROM gallery WHERE id = ? LIMIT 1");
                $stmt->execute([$imageId]);
                $image = $stmt->fetch();

                if (!$image) {
                    $message = ['success' => false, 'text' => 'Image not found.'];
                    break;
                }

                manage_gallery_delete_file($image['filename'] ?? null, $uploadDirectory);

                $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ? LIMIT 1");
                $stmt->execute([$imageId]);

                header('Location: manage-gallery.php?status=deleted');
                exit();
        }
    } catch (Exception $e) {
        error_log('Manage gallery error: ' . $e->getMessage());
        header('Location: manage-gallery.php?status=error');
        exit();
    }
}

$galleryImages = [];
try {
    $stmt = $pdo->query("SELECT * FROM gallery ORDER BY created_at DESC");
    $galleryImages = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('Fetch gallery error: ' . $e->getMessage());
}

$totalGalleryImages = count($galleryImages);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Gallery - Timiza Youth Initiative</title>
    <link rel="icon" href="../assets/images/favicon.ico" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .upload-dropzone {
            position: relative;
            background: linear-gradient(135deg, rgba(0, 123, 95, 0.12), rgba(0, 191, 166, 0.08));
            border: 2px dashed rgba(0, 123, 95, 0.35);
            border-radius: 1.25rem;
            transition: border-color 0.25s ease, box-shadow 0.25s ease, transform 0.25s ease, background 0.25s ease;
        }

        .upload-dropzone:hover,
        .upload-dropzone:focus-within {
            border-color: rgba(0, 123, 95, 0.65);
            background: linear-gradient(135deg, rgba(0, 123, 95, 0.18), rgba(0, 191, 166, 0.12));
            box-shadow: 0 18px 35px -20px rgba(0, 123, 95, 0.55);
            transform: translateY(-2px);
        }

        .upload-dropzone .upload-cta {
            background: linear-gradient(135deg, #007B5F, #009678);
            color: #fff;
            padding: 0.75rem 1.5rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 12px 30px -15px rgba(0, 123, 95, 0.9);
            transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
        }

        .upload-dropzone .upload-cta:hover,
        .upload-dropzone .upload-cta:focus {
            transform: translateY(-2px);
            box-shadow: 0 18px 35px -15px rgba(0, 123, 95, 0.85);
            background: linear-gradient(135deg, #00684f, #00886d);
        }

        .upload-dropzone .upload-cta i {
            font-size: 1.05rem;
        }

        .upload-filename {
            margin-top: 0.75rem;
            font-size: 0.85rem;
            color: #475569;
        }

        .upload-submit {
            background: linear-gradient(135deg, #007B5F, #00BFA6);
            color: #fff;
            padding: 0.9rem 2.75rem;
            border-radius: 1rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            cursor: pointer;
            box-shadow: 0 20px 40px -18px rgba(0, 123, 95, 0.65);
            transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
            position: relative;
            overflow: hidden;
        }

        .upload-submit::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.32), transparent 60%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .upload-submit:hover,
        .upload-submit:focus {
            transform: translateY(-3px);
            box-shadow: 0 26px 48px -20px rgba(0, 123, 95, 0.75);
        }

        .upload-submit:hover::before,
        .upload-submit:focus::before {
            opacity: 1;
        }

        .upload-submit:focus-visible {
            outline: 2px solid rgba(0, 191, 166, 0.65);
            outline-offset: 4px;
        }

        .upload-submit i {
            font-size: 1rem;
        }

        @media (max-width: 640px) {
            .upload-dropzone {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .upload-submit {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body class="font-poppins bg-neutral min-h-screen">
    <?php include '../includes/nav-admin.php'; ?>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-10">
        <div class="bg-white/70 backdrop-blur rounded-2xl border border-white/40 shadow-lg p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
            <div class="space-y-2">
                <p class="text-sm uppercase tracking-wider text-primary font-semibold flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-secondary animate-pulse"></span> Gallery Management</p>
                <h1 class="text-3xl font-bold text-dark">Manage Gallery</h1>
                <p class="text-gray-600">Upload new photos and curate the public gallery experience.</p>
            </div>
            <div class="bg-white rounded-xl shadow-inner px-5 py-4 text-center border border-primary/10">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Images</span>
                <p class="text-3xl font-bold text-primary mt-1">
                    <?php echo number_format($totalGalleryImages); ?>
                </p>
                <span class="text-xs text-gray-500">Active in gallery</span>
            </div>
        </div>

        <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-xl border <?php echo $message['success'] ? 'bg-green-50 text-green-700 border-green-200 shadow-sm' : 'bg-red-50 text-red-700 border-red-200 shadow-sm'; ?>">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center <?php echo $message['success'] ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'; ?>">
                    <i class="fas <?php echo $message['success'] ? 'fa-check' : 'fa-triangle-exclamation'; ?>"></i>
                </div>
                <p class="text-sm font-medium flex-1"><?php echo htmlspecialchars($message['text']); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <section class="admin-section bg-white rounded-2xl shadow-lg p-6 sm:p-8 mb-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <h2 class="text-xl sm:text-2xl font-semibold text-dark">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h8.25M8.25 12h8.25m-8.25 5.25h6.75" />
                    </svg>
                    Upload Image
                </h2>
                <div class="upload-helper hidden sm:flex items-center gap-3 bg-neutral/80 px-4 py-3 rounded-lg text-sm text-gray-600">
                    <i class="fas fa-lightbulb text-primary"></i>
                    <span>Tip: Use high-quality landscape images for best presentation.</span>
                </div>
            </div>
            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="action" value="add_image">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Select Image *</label>
                            <label class="upload-dropzone flex items-center justify-between gap-4 px-6 py-7 cursor-pointer">
                                <div class="space-y-2">
                                    <p class="text-sm font-semibold text-primary tracking-wide uppercase">Drop or click to upload</p>
                                    <p class="text-xs text-gray-600">PNG, JPG, GIF or WebP (max 5MB)</p>
                                    <p class="text-xs text-gray-400">Drag files from your computer or tap the button</p>
                                </div>
                                <span class="upload-cta">
                                    <i class="fas fa-cloud-arrow-up"></i>
                                    Choose image
                                </span>
                                <input type="file" name="image" accept="image/*" required class="hidden" onchange="handleUploadPreview(this)">
                            </label>
                            <p class="upload-filename" data-upload-filename>Waiting for file...</p>
                        </div>

                        <div>
                            <label for="caption" class="block text-sm font-semibold text-gray-700 mb-2">Caption</label>
                            <textarea id="caption" name="caption" rows="3" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/50 focus:border-primary/40 transition" placeholder="Tell us about this photo..."></textarea>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label for="category" class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
                            <input type="text" id="category" name="category" placeholder="e.g. Events, Programs" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/50 focus:border-primary/40 transition">
                        </div>
                        <div class="bg-neutral/70 border border-primary/10 rounded-xl px-4 py-4 space-y-3 text-sm text-gray-600">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-check-circle text-primary"></i>
                                <span>Accepted formats: JPG, PNG, GIF, WebP.</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <i class="fas fa-shield-alt text-primary"></i>
                                <span>Files are scanned automatically for safety.</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <i class="fas fa-cloud-upload-alt text-primary"></i>
                                <span>Images appear instantly in the list below.</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <p class="text-xs text-gray-500">By uploading, you confirm you have rights to share these photos.</p>
                    <button type="submit" class="upload-submit inline-flex items-center gap-2">
                        <i class="fas fa-upload"></i>
                        Upload Image
                    </button>
                </div>
            </form>
        </section>

        <section class="admin-section bg-white rounded-2xl shadow-lg p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <h2 class="text-xl sm:text-2xl font-semibold text-dark">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5l7.5 7.5L3 19.5m18-15L13.5 12l7.5 7.5" />
                    </svg>
                    Gallery Images
                </h2>
                <div class="flex items-center gap-4 text-sm text-gray-500">
                    <div class="flex items-center gap-2 bg-neutral/80 px-4 py-2 rounded-lg">
                        <i class="fas fa-layer-group text-primary"></i>
                        <span><?php echo number_format($totalGalleryImages); ?> items</span>
                    </div>
                    <div class="hidden sm:flex items-center gap-2 text-xs text-gray-400 uppercase tracking-wide">
                        <i class="fas fa-info-circle"></i>
                        <span>Latest uploads appear first</span>
                    </div>
                </div>
            </div>

            <?php if (!empty($galleryImages)): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                <?php foreach ($galleryImages as $index => $image): ?>
                <div class="gallery-card bg-neutral rounded-2xl overflow-hidden shadow-sm">
                    <div class="relative group overflow-hidden">
                        <div class="h-60 bg-gray-100 overflow-hidden">
                            <img src="<?php echo '../' . $relativeDirectory . htmlspecialchars($image['filename']); ?>" alt="<?php echo htmlspecialchars($image['caption'] ?? $image['original_name']); ?>" class="w-full h-full object-cover">
                        </div>
                        <span class="absolute top-4 left-4 bg-white/90 text-xs font-semibold text-gray-700 px-3 py-1 rounded-full shadow-sm">
                            <i class="fas fa-tag mr-1 text-primary"></i>
                            <?php echo htmlspecialchars($image['category'] ?? 'General'); ?>
                        </span>
                        <span class="absolute top-4 right-4 bg-dark/70 text-white text-xs font-semibold px-3 py-1 rounded-full shadow">
                            #<?php echo str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT); ?>
                        </span>
                    </div>
                    <div class="p-5 space-y-4 text-sm text-gray-600">
                        <?php if (!empty($image['caption'])): ?>
                        <p class="caption text-gray-700 font-medium italic leading-relaxed">
                            “<?php echo htmlspecialchars($image['caption']); ?>”
                        </p>
                        <?php endif; ?>
                        <div class="meta space-y-2">
                            <p><strong>Uploaded:</strong> <?php echo formatDate($image['created_at'] ?? ''); ?></p>
                            <p><strong>File:</strong> <?php echo htmlspecialchars($image['original_name']); ?></p>
                        </div>
                    </div>
                    <div class="px-5 pb-5">
                        <form method="POST" onsubmit="return confirm('Remove this image? This action cannot be undone.');">
                            <input type="hidden" name="action" value="delete_image">
                            <input type="hidden" name="image_id" value="<?php echo (int) $image['id']; ?>">
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-red-500 text-white py-3 rounded-xl hover:bg-red-600 transition">
                                <i class="fas fa-trash-alt"></i>
                                Remove Image
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state text-center py-20 px-6 space-y-4">
                <div class="bg-white/80 w-20 h-20 rounded-full flex items-center justify-center mx-auto text-primary text-3xl shadow">
                    <i class="fas fa-images"></i>
                </div>
                <h3 class="text-2xl font-semibold text-dark">No images yet</h3>
                <p class="text-gray-500 max-w-xl mx-auto">Start building the gallery by uploading your first photo above. Highlight impactful moments from your programs, events, and community outreaches.</p>
            </div>
            <?php endif; ?>
        </section>
    </main>
    <script>
        function handleUploadPreview(input) {
            var filenameDisplay = document.querySelector('[data-upload-filename]');
            if (!filenameDisplay) {
                return;
            }

            if (input.files && input.files.length > 0) {
                var file = input.files[0];
                filenameDisplay.textContent = file.name;
            } else {
                filenameDisplay.textContent = 'Waiting for file...';
            }
        }
    </script>
</body>
</html>