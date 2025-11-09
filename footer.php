    </main>
    
    <!-- Scroll to Top Button -->
    <button id="scroll-to-top" class="hidden fixed bottom-6 right-6 bg-primary text-white p-3 rounded-full shadow-lg hover:bg-secondary transition-all duration-300 transform hover:scale-110 z-40" onclick="scrollToTop()">
        <i class="fas fa-arrow-up text-lg"></i>
    </button>
    
    <!-- Offline Indicator -->
    <div id="offline-indicator" class="offline-indicator">
        <i class="fas fa-wifi-slash mr-2"></i>
        You're offline
    </div>
    
    <!-- Footer -->
    <footer class="bg-dark text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- About Section -->
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center space-x-3 mb-4">
                        <img src="<?php echo site_url_path('assets/images/logo.png'); ?>" alt="TYI Logo" class="h-10 w-10">
                        <span class="text-xl font-bold">Timiza Youth Initiative</span>
                    </div>
                    <p class="text-gray-300 mb-4">
                        Empowering youth and transforming communities in Kilifi County through sustainable development programs, 
                        education, and community engagement.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-300 hover:text-secondary transition-colors" aria-label="Facebook">
                            <i class="fab fa-facebook-f text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-secondary transition-colors" aria-label="Instagram">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-secondary transition-colors" aria-label="Twitter">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-secondary transition-colors" aria-label="LinkedIn">
                            <i class="fab fa-linkedin-in text-xl"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="<?php echo site_url_path('about'); ?>" class="text-gray-300 hover:text-secondary transition-colors">About Us</a></li>
                        <li><a href="<?php echo site_url_path('programs'); ?>" class="text-gray-300 hover:text-secondary transition-colors">Our Programs</a></li>
                        <li><a href="<?php echo site_url_path('get-involved'); ?>" class="text-gray-300 hover:text-secondary transition-colors">Get Involved</a></li>
                        <li><a href="<?php echo site_url_path('news'); ?>" class="text-gray-300 hover:text-secondary transition-colors">News & Events</a></li>
                        <li><a href="<?php echo site_url_path('gallery'); ?>" class="text-gray-300 hover:text-secondary transition-colors">Gallery</a></li>
                        <li><a href="<?php echo site_url_path('contact'); ?>" class="text-gray-300 hover:text-secondary transition-colors">Contact</a></li>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contact Info</h3>
                    <div class="space-y-3">
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-map-marker-alt text-secondary mt-1"></i>
                            <span class="text-gray-300">Kilifi County, Kenya</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-phone text-secondary"></i>
                            <span class="text-gray-300">+254 XXX XXX XXX</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-envelope text-secondary"></i>
                            <span class="text-gray-300">info@timizayouth.org</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-8 text-center">
                <p class="text-gray-300">
                    &copy; <?php echo date('Y'); ?> Timiza Youth Initiative. All rights reserved. 
                    <a href="<?php echo site_url_path('admin/login.php'); ?>" class="text-gray-500 hover:text-gray-400 ml-4">Admin</a>
                </p>
            </div>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="<?php echo site_url_path('assets/js/main.js'); ?>"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
        
        // Handle online/offline status
        function updateOnlineStatus() {
            const offlineIndicator = document.getElementById('offline-indicator');
            if (navigator.onLine) {
                offlineIndicator.classList.remove('show');
            } else {
                offlineIndicator.classList.add('show');
            }
        }
        
        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);
        
        // Initialize contact map if function exists
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof window.initContactMap === 'function') {
                window.initContactMap();
            }
            
            // Update online status on load
            updateOnlineStatus();
        });
    </script>
    
    <?php
    if (!empty($extra_scripts)) {
        if (is_array($extra_scripts)) {
            echo implode(PHP_EOL, $extra_scripts) . PHP_EOL;
        } else {
            echo $extra_scripts . PHP_EOL;
        }
    }
    ?>
</body>
</html>