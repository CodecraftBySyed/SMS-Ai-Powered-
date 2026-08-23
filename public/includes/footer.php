        </main>
        
        <!-- Footer -->
        <?php if (!isset($current_page) || $current_page !== 'ai-chat'): ?>
            <footer class="p-6 text-center text-gray-500 text-sm">
                &copy; <?php echo date('Y'); ?> 
                <a href="/edusync/public/about.edusync.php" class="font-semibold text-blue-600 hover:text-purple-600 transition-colors duration-200 underline decoration-dotted">
                    Edusync
                </a>. All rights reserved.
            </footer>
        <?php endif; ?>
    </div>

    <script>
        // Dark Mode Logic
        function toggleDarkMode() {
            document.documentElement.classList.toggle('dark');
            const isDark = document.documentElement.classList.contains('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            
            // Dispatch event for charts to update
            window.dispatchEvent(new Event('themeChanged'));
        }

        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        // Global Error Handler for Fetch
        window.addEventListener('unhandledrejection', function(event) {});
    </script>
</body>
</html>
