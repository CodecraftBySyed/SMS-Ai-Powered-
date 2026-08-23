<?php
// Determine active page for highlighting
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? 'guest';
$base_url = '/edusync/public';

function isActive($pages) {
    global $current_page;
    if (!is_array($pages)) $pages = [$pages];
    return in_array($current_page, $pages) ? 'bg-blue-600 text-white shadow-lg' : 'text-gray-300 hover:bg-gray-700 hover:text-white';
}
?>

<!-- Mobile Header -->
<div class="md:hidden bg-gray-900 text-white p-3 sm:p-4 flex justify-between items-center fixed w-full top-0 z-50 shadow-md">
    <div class="flex items-center space-x-2 sm:space-x-3 min-w-0">
        <img src="<?php echo $base_url; ?>/assets/images/logo.svg" alt="EduSync Logo" class="h-7 w-7 sm:h-8 sm:w-8 rounded-full flex-shrink-0">
        <span class="font-bold text-sm sm:text-base bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-purple-500 truncate">EduSync</span>
    </div>
    <button onclick="toggleSidebar()" class="p-2 rounded hover:bg-gray-800 transition flex-shrink-0 ml-2">
        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
    </button>
</div>

<!-- Sidebar -->
<aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-gray-900 text-white transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out z-40 shadow-2xl flex flex-col">
    <!-- Logo -->
    <div class="p-4 sm:p-6 border-b border-gray-800 flex items-center justify-center sm:justify-start space-x-3">
        <img src="<?php echo $base_url; ?>/assets/images/logo.svg" alt="EduSync Logo" class="h-10 w-10 sm:h-12 sm:w-12 rounded-full shadow-lg flex-shrink-0">
        <h1 class="hidden sm:block text-lg sm:text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-purple-500 tracking-wider">EduSync</h1>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
        
        <?php if ($role === 'admin'): ?>
            <a href="<?php echo $base_url; ?>/dashboard.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo isActive('dashboard.php'); ?>">
                <span>📊</span>
                <span class="font-medium">Dashboard</span>
            </a>
            <?php $isStudentAnalysis = (strpos($_SERVER['REQUEST_URI'], '/student-analysis/') !== false); ?>
            <a href="<?php echo $base_url; ?>/student-analysis/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo $isStudentAnalysis ? 'bg-blue-600 text-white shadow-lg' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                <span>📊</span>
                <span class="font-medium">Student Analysis</span>
            </a>
            <a href="<?php echo $base_url; ?>/students/list.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo isActive(['list.php', 'add.php', 'edit.php', 'view.php']); ?>">
                <span>👨‍🎓</span>
                <span class="font-medium">Students</span>
            </a>
            <a href="<?php echo $base_url; ?>/teachers/list.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo isActive(['list.php', 'add.php']); // Note: this might conflict if filenames are same. Better logic needed if deep folder structure used. ?>">
                <span>👨‍🏫</span>
                <span class="font-medium">Teachers</span>
            </a>
            <?php $isAddSubject = ($current_page === 'manage.php' && (isset($_GET['action']) && $_GET['action'] === 'add-subject')); ?>
            <a href="<?php echo $base_url; ?>/subjects/manage.php?action=add-subject" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo $isAddSubject ? 'bg-blue-600 text-white shadow-lg' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                <span>➕</span>
                <span class="font-medium">Add Subject</span>
            </a>
        <?php elseif ($role === 'teacher'): ?>
            <a href="<?php echo $base_url; ?>/teacher_dashboard.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo isActive('teacher_dashboard.php'); ?>">
                <span>📊</span>
                <span class="font-medium">Dashboard</span>
            </a>
            <?php $isStudentAnalysis = (strpos($_SERVER['REQUEST_URI'], '/student-analysis/') !== false); ?>
            <a href="<?php echo $base_url; ?>/student-analysis/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo $isStudentAnalysis ? 'bg-blue-600 text-white shadow-lg' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                <span>📊</span>
                <span class="font-medium">Student Analysis</span>
            </a>
            <a href="<?php echo $base_url; ?>/attendance/mark.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo isActive('mark.php'); ?>">
                <span>📅</span>
                <span class="font-medium">Attendance</span>
            </a>
            <a href="<?php echo $base_url; ?>/fees/manage.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo isActive('manage.php'); ?>">
                <span>💰</span>
                <span class="font-medium">Fees</span>
            </a>
            <a href="<?php echo $base_url; ?>/marks/entry.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo isActive('entry.php'); ?>">
                <span>📝</span>
                <span class="font-medium">Marks</span>
            </a>
            <a href="<?php echo $base_url; ?>/ai-chat/chatbot.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo isActive('chatbot.php'); ?>">
                <span>🤖</span>
                <span class="font-medium">AI Assistant</span>
            </a>
        <?php endif; ?>

    </nav>

    <!-- User Profile & Logout -->
    <div class="p-4 border-t border-gray-800 bg-gray-900/50">
        <div class="flex items-center space-x-3 mb-4 px-2">
            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-blue-500 to-purple-500 flex items-center justify-center font-bold text-lg">
                <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?>
            </div>
            <div>
                <p class="text-sm font-semibold text-white"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></p>
                <p class="text-xs text-gray-400 capitalize"><?php echo htmlspecialchars($role); ?></p>
            </div>
        </div>
        
        <div class="flex justify-between items-center space-x-2">
            <button onclick="toggleDarkMode()" class="p-2 rounded-lg bg-gray-800 hover:bg-gray-700 text-yellow-400 transition flex-1 text-center">
                🌓 Theme
            </button>
            <a href="<?php echo $base_url; ?>/../api/auth/logout.php" class="p-2 rounded-lg bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white transition flex-1 text-center font-medium">
                Logout
            </a>
        </div>
    </div>
</aside>

<!-- Overlay for mobile -->
<div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/50 z-30 hidden md:hidden glass-effect"></div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        if (sidebar.classList.contains('-translate-x-full')) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        }
    }
</script>
