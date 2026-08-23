<?php
session_start();

// OFFLINE_MODE: Set to true for offline use, false for CDN
define('OFFLINE_MODE', true);

if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? 'teacher';
    if ($role === 'admin') {
        header("Location: dashboard.php");
    } else {
        header("Location: teacher_dashboard.php");
    }
    exit;
}

// Generate CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Setup asset URLs
$assetBase = './assets/';
$sweetAlertJS = './assets/js/sweetalert2.all.min.js';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduSync - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' }
    </script>
    <script src="<?php echo $sweetAlertJS; ?>"></script>
    
</head>
<body class="bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 h-screen flex items-center justify-center transition-colors duration-300 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">

    <!-- Dark Mode Toggle -->
    <button id="theme-toggle" class="absolute top-4 right-4 sm:top-5 sm:right-5 p-2 rounded-full bg-white/20 text-white hover:bg-white/30 transition-all">
        <svg id="sun-icon" class="w-5 h-5 sm:w-6 sm:h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        <svg id="moon-icon" class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
    </button>

    <div class="bg-white/20 backdrop-blur-sm border border-white/30 dark:bg-black/40 dark:border-white/10 p-6 sm:p-8 rounded-2xl shadow-2xl w-full max-w-sm sm:max-w-md mx-4 transform transition-all hover:scale-105 duration-300">
        <div class="text-center mb-6 sm:mb-8">
            <div class="flex justify-center mb-4">
                <img src="./assets/images/logo.svg" alt="EduSync Logo" class="h-20 w-20 sm:h-24 sm:w-24 md:h-28 md:w-28 rounded-full shadow-lg border-4 border-white/30">
            </div>
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white mb-2 tracking-wide">EduSync</h1>
            <p class="text-blue-100 font-light text-xs sm:text-sm md:text-base">Welcome back, please login</p>
        </div>

        <form id="loginForm" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div>
                <label for="role" class="block text-xs sm:text-sm font-medium text-white mb-2">I am a</label>
                <div class="grid grid-cols-2 gap-2 sm:gap-4">
                    <label class="cursor-pointer">
                        <input type="radio" name="role" value="teacher" class="peer sr-only" checked>
                        <div class="text-center py-2 sm:py-3 rounded-lg bg-white/20 text-white text-xs sm:text-sm font-medium border border-transparent peer-checked:bg-white peer-checked:text-blue-600 peer-checked:font-bold transition-all hover:bg-white/30">
                            Teacher
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="role" value="admin" class="peer sr-only">
                        <div class="text-center py-2 sm:py-3 rounded-lg bg-white/20 text-white text-xs sm:text-sm font-medium border border-transparent peer-checked:bg-white peer-checked:text-blue-600 peer-checked:font-bold transition-all hover:bg-white/30">
                            Admin
                        </div>
                    </label>
                </div>
            </div>

            <div>
                <label for="email" class="block text-xs sm:text-sm font-medium text-white mb-2">Email Address</label>
                <input type="email" id="email" name="email" required 
                    class="block w-full px-3 sm:px-4 py-2 sm:py-3 text-sm sm:text-base bg-white/10 border border-gray-300/30 rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all backdrop-blur-sm"
                    placeholder="Enter your email">
            </div>

            <div>
                <label for="password" class="block text-xs sm:text-sm font-medium text-white mb-2">Password</label>
                <input type="password" id="password" name="password" required 
                    class="block w-full px-3 sm:px-4 py-2 sm:py-3 text-sm sm:text-base bg-white/10 border border-gray-300/30 rounded-lg text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all backdrop-blur-sm"
                    placeholder="••••••••">
            </div>

            <button type="submit" 
                class="w-full flex justify-center py-2 sm:py-3 px-4 border border-transparent rounded-lg shadow-lg text-xs sm:text-sm font-bold text-blue-600 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transform transition-transform active:scale-95">
                Sign In
            </button>
        </form>
    </div>

    <script>
        // Dark Mode Logic
        const themeToggle = document.getElementById('theme-toggle');
        const sunIcon = document.getElementById('sun-icon');
        const moonIcon = document.getElementById('moon-icon');
        const html = document.documentElement;

        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            html.classList.add('dark');
            moonIcon.classList.add('hidden');
            sunIcon.classList.remove('hidden');
        } else {
            html.classList.remove('dark');
            sunIcon.classList.add('hidden');
            moonIcon.classList.remove('hidden');
        }

        themeToggle.addEventListener('click', () => {
            html.classList.toggle('dark');
            if (html.classList.contains('dark')) {
                localStorage.theme = 'dark';
                moonIcon.classList.add('hidden');
                sunIcon.classList.remove('hidden');
            } else {
                localStorage.theme = 'light';
                sunIcon.classList.add('hidden');
                moonIcon.classList.remove('hidden');
            }
        });

        // Login Logic
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const btn = this.querySelector('button[type="submit"]');
            
            // Loading state
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Signing in...';

            try {
                const response = await fetch('../api/auth/login.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true
                    });
                    
                    await Toast.fire({
                        icon: 'success',
                        title: 'Signed in successfully'
                    });
                    
                    window.location.href = data.redirect;
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Login Failed',
                        text: data.message || 'Invalid credentials',
                        confirmButtonColor: '#3b82f6'
                    });
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An unexpected error occurred. Please try again.',
                    confirmButtonColor: '#3b82f6'
                });
                btn.disabled = false;
                btn.textContent = originalText;
            }
        });
    </script>
</body>
</html>
