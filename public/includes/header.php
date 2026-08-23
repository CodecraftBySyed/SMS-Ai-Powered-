<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// OFFLINE_MODE: Set to true to use local libraries, false for CDN
// When true: Application works completely offline
// When false: Uses CDN (requires internet connection)
define('OFFLINE_MODE', true);

// Basic path resolution
$is_public_root = file_exists('includes/sidebar.php');
$include_path = $is_public_root ? 'includes/' : '../includes/';
$api_path = $is_public_root ? '../api/' : '../../api/';

// Ensure Auth
require_once $api_path . 'common/auth.php';

// Define asset URLs
$assetBase = '/edusync/public/assets/';
$fontAwesomeCSS = $assetBase . 'css/font-awesome.min.css';
$tailwindCDN = 'https://cdn.tailwindcss.com';
$chartJS = $assetBase . 'js/chart.js';
$sweetAlertJS = $assetBase . 'js/sweetalert2.all.min.js';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduSync</title>
    <link rel="stylesheet" href="<?php echo $fontAwesomeCSS; ?>">
    <script src="<?php echo $tailwindCDN; ?>"></script>
    <script>
        tailwind.config = { 
            darkMode: 'class',
            safelist: [
                'opacity-0','opacity-30','opacity-100',
                'scale-95','scale-100','transform',
                'transition-opacity','transition-transform','transition-all',
                'duration-200','duration-300','ease-out','ease-in-out',
                'flex-row-reverse','space-x-reverse',
                'rounded-tr-none',
                'ring-1','ring-gray-100','dark:ring-gray-700'
            ]
        }
    </script>
    <script src="<?php echo $chartJS; ?>"></script>
    <script src="<?php echo $sweetAlertJS; ?>"></script>
    <style>
        @keyframes blink{0%,100%{opacity:.3;transform:translateY(0)}50%{opacity:1;transform:translateY(-3px)}}
        .dot{animation:blink 1.4s infinite both}
        .dot:nth-child(2){animation-delay:.2s}
        .dot:nth-child(3){animation-delay:.4s}
        .opacity-0{opacity:0}
        .opacity-100{opacity:1}
        .scale-95{transform:scale(.95)}
        .scale-100{transform:scale(1)}
        .transform{transform:translateZ(0)}
        .transition-all{transition:all .3s ease-out}
        .duration-300{transition-duration:.3s}
        .ease-out{transition-timing-function:cubic-bezier(.0,.0,.2,1)}
    </style>
    
    <?php include $include_path . 'csrf_script.php'; ?>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 transition-colors duration-200 font-sans antialiased overflow-x-hidden">

    <?php include $include_path . 'sidebar.php'; ?>

    <!-- Main Content Wrapper -->
    <div class="md:ml-64 min-h-screen flex flex-col pt-16 md:pt-0 transition-all duration-300">
        <!-- Content Container -->
        <main class="flex-1 p-4 sm:p-6 space-y-4 sm:space-y-6">
