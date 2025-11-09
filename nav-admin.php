<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAuth();
?>

<nav class="bg-white shadow sticky top-0 inset-x-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <a href="dashboard.php" class="flex items-center space-x-3">
                <img 
    src="../assets/images/logo.png" 
    alt="TYI Logo" 
    class="h-12 w-12 md:h-14 md:w-14 object-contain" 
    style="max-height: 65px;"
>
<span class="text-xl font-bold text-primary tracking-wide">Timiza Youths Initiative Admin Panel</span>

            </a>

            <div class="hidden sm:flex items-center space-x-4 text-sm">
                <a href="dashboard.php" class="text-gray-600 hover:text-primary">Dashboard</a>
                <a href="manage-news.php" class="text-gray-600 hover:text-primary">News</a>
                <a href="manage-gallery.php" class="text-gray-600 hover:text-primary">Gallery</a>
                <a href="volunteers.php" class="text-gray-600 hover:text-primary">Volunteers</a>
                <a href="donations.php" class="text-gray-600 hover:text-primary">Donations</a>
                <a href="partners.php" class="text-gray-600 hover:text-primary">Partners</a>
                <a href="messages.php" class="text-gray-600 hover:text-primary">Messages</a>
                <a href="settings.php" class="text-gray-600 hover:text-primary">Settings</a>
                <a href="icon.php" class="text-gray-600 hover:text-primary">PWA Icons</a>
                <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">Logout</a>
            </div>

            <div class="sm:hidden">
                <button type="button"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-600 hover:text-primary hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                        aria-controls="admin-mobile-menu"
                        aria-expanded="false"
                        data-menu-toggle>
                    <span class="sr-only">Toggle navigation</span>
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" data-icon="menu">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                    </svg>
                    <svg class="h-6 w-6 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" data-icon="close">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div id="admin-mobile-menu" class="sm:hidden hidden border-t border-gray-200 bg-white">
        <div class="px-4 py-4 space-y-2 text-sm">
            <a href="dashboard.php" class="block text-gray-700 hover:text-primary">Dashboard</a>
            <a href="manage-news.php" class="block text-gray-700 hover:text-primary">News</a>
            <a href="manage-gallery.php" class="block text-gray-700 hover:text-primary">Gallery</a>
            <a href="volunteers.php" class="block text-gray-700 hover:text-primary">Volunteers</a>
            <a href="donations.php" class="block text-gray-700 hover:text-primary">Donations</a>
            <a href="partners.php" class="block text-gray-700 hover:text-primary">Partners</a>
            <a href="messages.php" class="block text-gray-700 hover:text-primary">Messages</a>
            <a href="settings.php" class="block text-gray-700 hover:text-primary">Settings</a>
            <a href="logout.php" class="block text-center bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">Logout</a>
        </div>
    </div>
</nav>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var toggleButton = document.querySelector('[data-menu-toggle]');
    var mobileMenu = document.getElementById('admin-mobile-menu');

    if (!toggleButton || !mobileMenu) {
        return;
    }

    var menuIcon = toggleButton.querySelector('[data-icon="menu"]');
    var closeIcon = toggleButton.querySelector('[data-icon="close"]');

    toggleButton.addEventListener('click', function () {
        var expanded = toggleButton.getAttribute('aria-expanded') === 'true';
        toggleButton.setAttribute('aria-expanded', (!expanded).toString());

        mobileMenu.classList.toggle('hidden');

        if (menuIcon && closeIcon) {
            menuIcon.classList.toggle('hidden');
            closeIcon.classList.toggle('hidden');
        }
    });
});
</script>