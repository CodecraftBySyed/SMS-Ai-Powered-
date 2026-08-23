<script>
    // Global CSRF Token for Fetch requests
    const CSRF_TOKEN = "<?php echo $_SESSION['csrf_token'] ?? ''; ?>";
    
    // Intercept all Fetch requests to add CSRF header
    const originalFetch = window.fetch;
    window.fetch = function(url, options = {}) {
        if (options.method && ['POST', 'PUT', 'DELETE'].includes(options.method.toUpperCase())) {
            options.headers = options.headers || {};
            // If headers is not an instance of Headers, treat as object
            if (!(options.headers instanceof Headers)) {
                options.headers['X-CSRF-Token'] = CSRF_TOKEN;
            } else {
                options.headers.append('X-CSRF-Token', CSRF_TOKEN);
            }
        }
        return originalFetch(url, options);
    };
</script>