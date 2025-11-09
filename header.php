<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$current_page = basename($_SERVER['PHP_SELF'], '.php');
$script_directory = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$base_path = ($script_directory === '' || $script_directory === '.') ? '' : $script_directory;

if (!function_exists('site_url_path')) {
    function site_url_path(string $path = ''): string
    {
        global $base_path;

        $prefix = $base_path === '' ? '' : $base_path;
        $trimmed = ltrim($path, '/');

        if ($trimmed === '') {
            return ($prefix === '' ? '' : rtrim($prefix, '/')) . '/';
        }

        if (preg_match('/^[a-z][a-z0-9+\-.]*:/i', $trimmed)) {
            return $trimmed;
        }

        // Extract fragment and query portions
        $fragment = '';
        $hashPos = strpos($trimmed, '#');
        if ($hashPos !== false) {
            $fragment = substr($trimmed, $hashPos);
            $trimmed = substr($trimmed, 0, $hashPos);
        }

        $query = '';
        $queryPos = strpos($trimmed, '?');
        if ($queryPos !== false) {
            $query = substr($trimmed, $queryPos);
            $trimmed = substr($trimmed, 0, $queryPos);
        }

        if ($trimmed !== '' && substr($trimmed, -1) !== '/' && strpos($trimmed, '.') === false) {
            $phpCandidate = __DIR__ . '/../' . $trimmed . '.php';
            $dirCandidate = __DIR__ . '/../' . $trimmed;

            if (file_exists($phpCandidate)) {
                $trimmed = rtrim($trimmed, '/');
            } elseif (!file_exists($dirCandidate)) {
                $trimmed .= '.php';
            }
        }

        $relative = $trimmed . $query . $fragment;
        $prefix = rtrim($prefix, '/');

        if ($prefix === '') {
            return '/' . ltrim($relative, '/');
        }

        return $prefix . '/' . ltrim($relative, '/');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Timiza Youth Initiative - Empowering Youth, Transforming Communities in Kilifi County, Kenya">
    <meta name="keywords" content="youth empowerment, Kilifi County, Kenya, community development, climate action, gender equality">
    <meta name="author" content="Timiza Youth Initiative">
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#007B5F">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Timiza Youth">
    <meta name="application-name" content="Timiza Youth">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo SITE_URL; ?>">
    <meta property="og:title" content="<?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?>">
    <meta property="og:description" content="Empowering Youth, Transforming Communities in Kilifi County, Kenya">
    <meta property="og:image" content="<?php echo SITE_URL; ?>/assets/images/logo-512.png">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo SITE_URL; ?>">
    <meta property="twitter:title" content="<?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?>">
    <meta property="twitter:description" content="Empowering Youth, Transforming Communities in Kilifi County, Kenya">
    <meta property="twitter:image" content="<?php echo SITE_URL; ?>/assets/images/logo-512.png">
    
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    
    <!-- PWA Icons and Manifest -->
    <link rel="icon" href="<?php echo site_url_path('assets/images/logo-192.png'); ?>" type="image/png">
    <link rel="manifest" href="<?php echo site_url_path('manifest.json'); ?>">
    <link rel="apple-touch-icon" sizes="192x192" href="<?php echo site_url_path('assets/images/logo-192.png'); ?>">
    <link rel="apple-touch-icon" sizes="512x512" href="<?php echo site_url_path('assets/images/logo-512.png'); ?>">
    
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
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo site_url_path('assets/css/styles.css'); ?>">
    <?php
    if (!empty($extra_styles)) {
        if (is_array($extra_styles)) {
            echo implode(PHP_EOL, $extra_styles) . PHP_EOL;
        } else {
            echo $extra_styles . PHP_EOL;
        }
    }
    ?>
</head>
<body class="font-poppins bg-neutral text-dark">
    <!-- PWA Install Banner -->
    <div id="pwa-install-banner" class="hidden fixed top-0 left-0 right-0 bg-primary text-white p-3 z-50 shadow-lg">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-mobile-alt mr-3 text-xl"></i>
                <span class="text-sm md:text-base">Install Timiza Youth app for better experience!</span>
            </div>
            <div class="flex items-center gap-2">
                <button id="pwa-install-btn" class="bg-accent text-dark px-4 py-2 rounded-lg text-sm font-medium hover:bg-opacity-90 transition-all">
                    Install
                </button>
                <button id="pwa-dismiss-btn" class="text-white hover:text-accent transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="bg-white shadow-lg fixed w-full top-0 z-40" id="main-nav">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-6 h-20">
                <div class="flex items-center flex-shrink-0 min-w-0">
                    <a href="<?php echo site_url_path(''); ?>" class="flex items-center space-x-3 min-w-0">
                       <img 
    src="<?php echo site_url_path('assets/images/logo.png'); ?>" 
    alt="TYI Logo" 
    class="h-14 w-14 md:h-16 md:w-16 object-contain" 
    style="max-height: 70px;"
>
<span class="text-xl md:text-2xl font-extrabold text-primary tracking-wide leading-snug whitespace-nowrap">Timiza Youth Initiative</span>

                    </a>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex flex-1 justify-end items-center gap-4 text-sm whitespace-nowrap">
                    <a href="<?php echo site_url_path(''); ?>" class="<?php echo $current_page === 'index' ? 'text-primary border-b-2 border-primary' : 'text-gray-700 hover:text-primary'; ?> px-2 py-2 font-medium transition-colors whitespace-nowrap">Home</a>
                    <a href="<?php echo site_url_path('welcome'); ?>" class="<?php echo $current_page === 'welcome' ? 'text-primary border-b-2 border-primary' : 'text-gray-700 hover:text-primary'; ?> px-2 py-2 font-medium transition-colors whitespace-nowrap">Welcome</a>
                    <a href="<?php echo site_url_path('about'); ?>" class="<?php echo $current_page === 'about' ? 'text-primary border-b-2 border-primary' : 'text-gray-700 hover:text-primary'; ?> px-2 py-2 font-medium transition-colors whitespace-nowrap">About</a>
                    <a href="<?php echo site_url_path('programs'); ?>" class="<?php echo $current_page === 'programs' ? 'text-primary border-b-2 border-primary' : 'text-gray-700 hover:text-primary'; ?> px-2 py-2 font-medium transition-colors whitespace-nowrap">Programs</a>
                    <a href="<?php echo site_url_path('get-involved'); ?>" class="<?php echo $current_page === 'get-involved' ? 'text-primary border-b-2 border-primary' : 'text-gray-700 hover:text-primary'; ?> px-2 py-2 font-medium transition-colors whitespace-nowrap">Get Involved</a>
                    <a href="<?php echo site_url_path('news'); ?>" class="<?php echo $current_page === 'news' ? 'text-primary border-b-2 border-primary' : 'text-gray-700 hover:text-primary'; ?> px-2 py-2 font-medium transition-colors whitespace-nowrap">News</a>
                    <a href="<?php echo site_url_path('gallery'); ?>" class="<?php echo $current_page === 'gallery' ? 'text-primary border-b-2 border-primary' : 'text-gray-700 hover:text-primary'; ?> px-2 py-2 font-medium transition-colors whitespace-nowrap">Gallery</a>
                    <a href="<?php echo site_url_path('contact'); ?>" class="<?php echo $current_page === 'contact' ? 'text-primary border-b-2 border-primary' : 'text-gray-700 hover:text-primary'; ?> px-2 py-2 font-medium transition-colors whitespace-nowrap">Contact</a>
                    <a href="<?php echo site_url_path('get-involved'); ?>" class="bg-primary text-white px-4 py-2 rounded-full hover:bg-opacity-90 transition-all whitespace-nowrap">Volunteer</a>
                </div>
                
                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-btn" class="text-gray-700 hover:text-primary focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="md:hidden hidden bg-white border-t">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="<?php echo site_url_path(''); ?>" class="<?php echo $current_page === 'index' ? 'text-primary bg-gray-50' : 'text-gray-700'; ?> block px-3 py-2 text-base font-medium hover:text-primary hover:bg-gray-50">
                    <i class="fas fa-home mr-3"></i>Home
                </a>
                <a href="<?php echo site_url_path('welcome'); ?>" class="<?php echo $current_page === 'welcome' ? 'text-primary bg-gray-50' : 'text-gray-700'; ?> block px-3 py-2 text-base font-medium hover:text-primary hover:bg-gray-50">
                    <i class="fas fa-user-friends mr-3"></i>Welcome
                </a>
                
                <a href="<?php echo site_url_path('about'); ?>" class="<?php echo $current_page === 'about' ? 'text-primary bg-gray-50' : 'text-gray-700'; ?> block px-3 py-2 text-base font-medium hover:text-primary hover:bg-gray-50">
                    <i class="fas fa-info-circle mr-3"></i>About
                </a>
                <a href="<?php echo site_url_path('programs'); ?>" class="<?php echo $current_page === 'programs' ? 'text-primary bg-gray-50' : 'text-gray-700'; ?> block px-3 py-2 text-base font-medium hover:text-primary hover:bg-gray-50">
                    <i class="fas fa-tasks mr-3"></i>Programs
                </a>
                <a href="<?php echo site_url_path('get-involved'); ?>" class="<?php echo $current_page === 'get-involved' ? 'text-primary bg-gray-50' : 'text-gray-700'; ?> block px-3 py-2 text-base font-medium hover:text-primary hover:bg-gray-50">
                    <i class="fas fa-hands-helping mr-3"></i>Get Involved
                </a>
                <a href="<?php echo site_url_path('news'); ?>" class="<?php echo $current_page === 'news' ? 'text-primary bg-gray-50' : 'text-gray-700'; ?> block px-3 py-2 text-base font-medium hover:text-primary hover:bg-gray-50">
                    <i class="fas fa-newspaper mr-3"></i>News
                </a>
                <a href="<?php echo site_url_path('gallery'); ?>" class="<?php echo $current_page === 'gallery' ? 'text-primary bg-gray-50' : 'text-gray-700'; ?> block px-3 py-2 text-base font-medium hover:text-primary hover:bg-gray-50">
                    <i class="fas fa-images mr-3"></i>Gallery
                </a>
                <a href="<?php echo site_url_path('contact'); ?>" class="<?php echo $current_page === 'contact' ? 'text-primary bg-gray-50' : 'text-gray-700'; ?> block px-3 py-2 text-base font-medium hover:text-primary hover:bg-gray-50">
                    <i class="fas fa-envelope mr-3"></i>Contact
                </a>
                <a href="<?php echo site_url_path('get-involved'); ?>" class="block px-3 py-2 text-base font-medium bg-primary text-white rounded-lg mx-3 mt-2 text-center">Volunteer</a>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="pt-16">