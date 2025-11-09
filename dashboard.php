<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAuth();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Timiza Youth Initiative</title>
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
<body class="font-poppins bg-neutral min-h-screen">
    <?php include '../includes/nav-admin.php'; ?>
    
    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Published News</p>
                        <p class="text-3xl font-semibold text-primary"><?php echo count(getNews()); ?></p>
                    </div>
                    <div class="h-12 w-12 bg-primary/10 text-primary rounded-full flex items-center justify-center text-xl">
                        <i class="fas fa-newspaper"></i>
                    </div>
                </div>
                <a href="manage-news.php" class="mt-4 inline-flex items-center text-sm text-primary hover:underline">
                    Manage News <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Messages</p>
                        <p class="text-3xl font-semibold text-secondary"><?php echo count(getMessages() ?? []); ?></p>
                    </div>
                    <div class="h-12 w-12 bg-secondary/10 text-secondary rounded-full flex items-center justify-center text-xl">
                        <i class="fas fa-envelope"></i>
                    </div>
                </div>
                <a href="messages.php" class="mt-4 inline-flex items-center text-sm text-secondary hover:underline">
                    View Messages <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Programs</p>
                        <p class="text-3xl font-semibold text-accent"><?php echo count(getPrograms()); ?></p>
                    </div>
                    <div class="h-12 w-12 bg-accent/10 text-accent rounded-full flex items-center justify-center text-xl">
                        <i class="fas fa-layer-group"></i>
                    </div>
                </div>
                <a href="../programs.php" class="mt-4 inline-flex items-center text-sm text-accent hover:underline">
                    View Site Programs <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Volunteers</p>
                        <p class="text-3xl font-semibold text-teal"><?php echo getStats()['volunteers']; ?></p>
                    </div>
                    <div class="h-12 w-12 bg-teal/10 text-teal rounded-full flex items-center justify-center text-xl">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <a href="../get-involved.php#volunteer-form" class="mt-4 inline-flex items-center text-sm text-teal hover:underline">
                    Volunteer Leads <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
        
        <div class="mt-10 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-dark mb-4">Quick Links</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <a href="manage-news.php" class="flex items-center gap-3 bg-primary/10 text-primary px-4 py-3 rounded-lg hover:bg-primary/20 transition">
                    <i class="fas fa-pen-to-square"></i> Manage News
                </a>
                <a href="messages.php" class="flex items-center gap-3 bg-secondary/10 text-secondary px-4 py-3 rounded-lg hover:bg-secondary/20 transition">
                    <i class="fas fa-comments"></i> View Messages
                </a>
                <a href="../index.php" class="flex items-center gap-3 bg-accent/10 text-accent px-4 py-3 rounded-lg hover:bg-accent/20 transition">
                    <i class="fas fa-globe"></i> View Site
                </a>
                <a href="logout.php" class="flex items-center gap-3 bg-red-100 text-red-600 px-4 py-3 rounded-lg hover:bg-red-200 transition">
                    <i class="fas fa-power-off"></i> Logout
                </a>
            </div>
        </div>
    </div>
</body>
</html>