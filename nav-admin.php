<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAuth();
?>
<nav class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <a href="dashboard.php" class="flex items-center space-x-3">
                <img src="../assets/images/logo.png" alt="TYI Logo" class="h-8 w-8">
                <span class="text-lg font-semibold text-primary">TYI Admin</span>
            </a>
            <div class="flex items-center space-x-4 text-sm">
                <a href="dashboard.php" class="text-gray-600 hover:text-primary">Dashboard</a>
                <a href="manage-news.php" class="text-gray-600 hover:text-primary">News</a>
                <a href="manage-gallery.php" class="text-gray-600 hover:text-primary">Gallery</a>
                <a href="volunteers.php" class="text-gray-600 hover:text-primary">Volunteers</a>
                <a href="donations.php" class="text-gray-600 hover:text-primary">Donations</a>
                <a href="partners.php" class="text-gray-600 hover:text-primary">Partners</a>
                <a href="messages.php" class="text-gray-600 hover:text-primary">Messages</a>
                <a href="settings.php" class="text-gray-600 hover:text-primary">Settings</a>
                <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">Logout</a>
            </div>
        </div>
    </div>
</nav>