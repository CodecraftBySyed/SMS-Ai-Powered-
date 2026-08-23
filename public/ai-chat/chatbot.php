<?php
require_once '../../api/common/auth.php';
$current_page = 'ai-chat';
require_once '../includes/header.php';
?>

<!-- Chat Interface -->
<div class="flex flex-col min-h-[calc(100vh-120px)] px-2 sm:px-4 lg:px-8 py-4">
    <div class="rounded-2xl shadow-xl flex-grow flex flex-col overflow-hidden border border-gray-200/50 dark:border-gray-700/50 backdrop-blur-md bg-white/90 dark:bg-gray-900/70 max-w-3xl mx-auto w-full">
        
        <!-- Chat Header -->
        <div class="p-4 border-b dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-700">
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-blue-500 to-purple-500 flex items-center justify-center text-white font-bold text-xl shadow-lg">
                        <i class="fas fa-robot"></i>
                    </div>
                    <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full"></span>
                </div>
                <div>
                    <h2 class="font-bold text-lg leading-tight text-gray-800 dark:text-gray-200">EduSync AI</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Always online</p>
                </div>
            </div>
            <button onclick="clearChat()" class="text-xs font-medium text-gray-500 hover:text-red-500 transition uppercase tracking-wide flex items-center gap-1">
                <i class="fas fa-trash-alt"></i> Clear History
            </button>
        </div>

        <!-- Tools -->
        <div class="px-4 pt-3 bg-gray-50 dark:bg-gray-700/60 border-b dark:border-gray-700">
            <input id="chatSearch" type="text" placeholder="Search messages (Ctrl+K)" aria-label="Search messages"
                class="w-full bg-white/70 dark:bg-gray-800/70 border-0 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-500">
        </div>

        <!-- Chat Messages -->
        <div id="chatContainer" role="log" aria-live="polite" aria-relevant="additions" class="flex-grow p-4 md:p-6 overflow-y-auto space-y-4 bg-gray-50/60 dark:bg-gray-950/60">
            <!-- Welcome Message -->
            <div class="flex items-start space-x-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-green-500 to-blue-600 flex-shrink-0 flex items-center justify-center text-white shadow-md">
                    <i class="fas fa-robot text-lg"></i>
                </div>
                <div class="bg-white/80 dark:bg-gray-800/70 backdrop-blur-md p-4 rounded-2xl rounded-tl-none shadow-sm max-w-[85%] md:max-w-[70%] border border-gray-100/60 dark:border-gray-700/60 text-sm md:text-base text-gray-800 dark:text-gray-200">
                    <p><strong>👋 Welcome to EduSync AI!</strong></p>
                    <p class="mt-2">I can help you with:</p>
                    <ul class="list-disc ml-4 mt-2 space-y-1 text-gray-600 dark:text-gray-300 text-sm">
                        <li>📊 Student Performance & Progress</li>
                        <li>📅 Attendance Tracking</li>
                        <li>💰 Fee Management</li>
                        <li>✏️ Marks & Grades</li>
                        <li>📈 Class Statistics</li>
                    </ul>
                    <p class="mt-3 text-xs text-gray-400"><strong>💡 Try:</strong> "Marks of Rahul" or "Attendance of Priya"</p>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-3 md:p-4 border-t dark:border-gray-700 bg-white/90 dark:bg-gray-900/70 backdrop-blur-md">
            <form id="chatForm" onsubmit="handleChat(event)" class="relative flex items-center">
                <input type="text" id="userInput" aria-label="Message"
                    class="w-full bg-gray-100/80 dark:bg-gray-700/80 border-0 rounded-full pl-6 pr-14 py-4 focus:ring-2 focus:ring-blue-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 transition text-gray-800 dark:text-gray-200 placeholder-gray-500"
                    placeholder="Type a message..." autocomplete="off">
                
                <button type="submit" id="sendBtn" aria-label="Send message"
                    class="absolute right-2 bg-blue-500 hover:bg-blue-600 text-white rounded-full p-2.5 shadow-lg transition transform hover:scale-105 focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
            <p class="text-center text-xs text-gray-400 mt-2">AI responses may vary. Check dashboard for critical data.</p>
        </div>
    </div>
</div>

<div class="mt-4 text-center text-xs text-gray-500 dark:text-gray-400">
    EduSync AI Assistant &middot; Chat responses are for guidance. Verify critical data in the dashboard.
</div>

<div id="errorPanel" class="fixed bottom-4 right-4 max-w-sm w-[90%] sm:w-[22rem] hidden z-50">
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl shadow-md p-3">
        <div class="flex justify-between items-center mb-2">
            <span class="font-semibold text-sm">Error Details</span>
            <button id="copyErrorBtn" class="text-xs px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700">Copy</button>
        </div>
        <pre id="errorContent" class="text-xs whitespace-pre-wrap break-words max-h-40 overflow-auto"></pre>
    </div>
</div>

<style>
    /* Smooth bouncing animation for typing indicator */
    @keyframes typing-bounce {
        0%, 60%, 100% {
            transform: translateY(0);
            opacity: 0.3;
        }
        30% {
            transform: translateY(-8px);
            opacity: 1;
        }
    }

    .typing-dot {
        width: 0.7rem;
        height: 0.7rem;
        border-radius: 9999px;
        background-color: #2563eb; /* bright blue for visibility */
        box-shadow: 0 0 0 2px rgba(37,99,235,0.2);
        animation: typing-bounce 1.4s infinite ease-in-out;
    }

    .typing-dot:nth-child(1) {
        animation-delay: 0s;
    }

    .typing-dot:nth-child(2) {
        animation-delay: 0.2s;
    }

    .typing-dot:nth-child(3) {
        animation-delay: 0.4s;
    }

    /* Respect reduced motion preferences */
    @media (prefers-reduced-motion: reduce) {
        .typing-dot {
            animation: none;
            opacity: 0.6;
        }
    }
</style>

<script>
    const animeCdn = document.createElement('script');
    animeCdn.src = 'https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js';
    animeCdn.defer = true;
    document.head.appendChild(animeCdn);
    // ========================================
    // EduSync AI Chatbot Frontend
    // Real-time chat with proper error handling
    // ========================================

    const chatContainer = document.getElementById('chatContainer');
    const userInput = document.getElementById('userInput');
    const sendBtn = document.getElementById('sendBtn');
    
    let isProcessing = false;
    const THINK_MIN = 800;
    const THINK_MAX = 2000;
    const API_TIMEOUT = 10000;     // 10 second API timeout
    const REDUCED_MOTION = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const typingIntervals = {};
    const errorPanel = document.getElementById('errorPanel');
    const errorContent = document.getElementById('errorContent');
    const copyErrorBtn = document.getElementById('copyErrorBtn');
    copyErrorBtn.addEventListener('click', () => {
        const txt = errorContent.textContent || '';
        if (navigator.clipboard && txt) {
            navigator.clipboard.writeText(txt);
        }
    });

    /**
     * Add message bubble to chat with fade-in animation
     * XSS-Safe: Uses textContent for user input
     */
    function appendMessage(text, isUser = false, source = null) {
        const div = document.createElement('div');
        div.className = `flex items-start space-x-3 ${isUser ? 'flex-row-reverse space-x-reverse' : ''}`;
        
        const avatar = isUser ? 
            `<div class="w-10 h-10 rounded-full bg-blue-600 flex-shrink-0 flex items-center justify-center text-white shadow-md">
                <i class="fas fa-user text-lg"></i>
            </div>` :
            `<div class="w-10 h-10 rounded-full bg-gradient-to-tr from-green-500 to-blue-600 flex-shrink-0 flex items-center justify-center text-white shadow-md">
                <i class="fas fa-robot text-lg"></i>
            </div>`;

        const bubbleClass = isUser ? 
            'bg-blue-600 text-white rounded-tr-none shadow-md' : 
            'bg-white/80 dark:bg-gray-800/70 backdrop-blur-md rounded-tl-none border border-gray-100/60 dark:border-gray-700/60 text-gray-800 dark:text-gray-200';

        let badge = '';
        if (!isUser && source === 'gemini') {
            const label = 'AI';
            const color = 'bg-purple-600';
            badge = `<span class="ml-2 px-2 py-0.5 text-[10px] font-semibold text-white ${color} rounded-full align-middle">${label}</span>`;
        }

        const bubbleExtras = isUser ? '' : 'shadow-sm ring-1 ring-gray-100 dark:ring-gray-700';
        const widthClass = isUser ? 'max-w-[75%] md:max-w-[55%]' : 'max-w-[80%] md:max-w-[60%]';
        div.innerHTML = `
            ${avatar}
            <div class="msg-bubble ${bubbleClass} ${bubbleExtras} p-4 rounded-2xl ${widthClass} max-h-[60vh] overflow-y-auto text-sm md:text-base leading-relaxed break-words whitespace-pre-wrap overflow-hidden">
                <p class="flex items-center break-words"><span class="msg-content">${isUser ? escapeHtml(text) : ''}</span> ${badge}</p>
            </div>
        `;
        
        chatContainer.appendChild(div);
        if (!REDUCED_MOTION && window.anime) {
            anime({
                targets: div,
                opacity: [0, 1],
                translateY: [-8, 0],
                duration: 300,
                easing: 'easeOutQuad',
                complete: () => {
                    if (!isUser) {
                        const contentEl = div.querySelector('.msg-content');
                        if (contentEl) {
                            const safeText = escapeHtml(text);
                            const len = Math.min(safeText.length, 1500);
                            const duration = Math.min(2000 + len * 8, 4000);
                            anime({
                                targets: { progress: 0 },
                                progress: len,
                                round: 1,
                                duration,
                                easing: 'linear',
                                update: (a) => {
                                    const n = a.animations[0].currentValue;
                                    contentEl.innerHTML = safeText.slice(0, n);
                                },
                                complete: autoScroll
                            });
                        } else {
                            autoScroll();
                        }
                    } else {
                        autoScroll();
                    }
                }
            });
        } else {
            if (!isUser) {
                const contentEl = div.querySelector('.msg-content');
                if (contentEl) contentEl.innerHTML = escapeHtml(text);
            }
            autoScroll();
        }
    }

    /**
     * Escape HTML special characters to prevent XSS
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function fetchWithTimeout(resource, options = {}) {
        const { timeout } = options || {};
        if (typeof AbortSignal !== 'undefined' && typeof AbortSignal.timeout === 'function' && timeout) {
            return fetch(resource, { ...options, signal: AbortSignal.timeout(timeout) });
        }
        if (typeof AbortController === 'undefined' || !timeout) {
            return fetch(resource, options);
        }
        const controller = new AbortController();
        const id = setTimeout(() => controller.abort(), timeout);
        return fetch(resource, { ...options, signal: controller.signal }).finally(() => clearTimeout(id));
    }

    /**
     * Show typing indicator animation (three bouncing dots)
     * Returns element ID for later removal
     */
    function showTypingIndicator() {
        if (REDUCED_MOTION) {
            const id = 'typing-skip-' + Date.now();
            return id;
        }
        const id = 'typing-' + Date.now();
        const div = document.createElement('div');
        div.id = id;
        div.className = 'flex items-start space-x-3';
        
        div.innerHTML = `
            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-green-500 to-blue-600 flex-shrink-0 flex items-center justify-center text-white shadow-md">
                <i class="fas fa-robot text-lg"></i>
            </div>
            <div class="bg-white/80 dark:bg-gray-800/70 backdrop-blur-md p-4 rounded-2xl rounded-tl-none shadow-sm border border-gray-100/60 dark:border-gray-700/60">
                <div class="flex space-x-2 items-center">
                    <div class="typing-dot w-2.5 h-2.5 bg-blue-500 rounded-full"></div>
                    <div class="typing-dot w-2.5 h-2.5 bg-blue-500 rounded-full"></div>
                    <div class="typing-dot w-2.5 h-2.5 bg-blue-500 rounded-full"></div>
                </div>
            </div>
        `;
        
        chatContainer.appendChild(div);
        autoScroll();
        if (window.anime) {
            const dots = div.querySelectorAll('.typing-dot');
            anime({
                targets: dots,
                opacity: [
                    { value: 0.3, duration: 0 },
                    { value: 1, duration: 400 }
                ],
                translateY: [
                    { value: -6, duration: 400 },
                    { value: 0, duration: 400 }
                ],
                delay: anime.stagger(120),
                loop: true,
                easing: 'easeInOutSine'
            });
        }
        return id;
    }

    /**
     * Auto-scroll chat to bottom (smooth)
     */
    function autoScroll() {
        if (REDUCED_MOTION) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
            return;
        }
        chatContainer.scrollTo({ top: chatContainer.scrollHeight, behavior: 'smooth' });
    }

    /**
     * Simulate realistic delay (300-700ms) for natural conversation feel
     */
    async function simulateTypingDelay(delayMs) {
        if (REDUCED_MOTION) return Promise.resolve();
        return new Promise(resolve => setTimeout(resolve, delayMs));
    }

    /**
     * Handle chat form submission
     * Flow:
     *   1. Validate input
     *   2. Show user message
     *   3. Show typing indicator
     *   4. Call API with timeout
     *   5. Simulate delay
     *   6. Show bot response or error
     *   7. Re-enable input
     */
    async function handleChat(e) {
        e.preventDefault();
        
        // Prevent duplicate submissions while processing
        if (isProcessing) {
            return;
        }

        const text = userInput.value.trim();
        if (!text) {
            return;
        }

        // Validate message length
        if (text.length > 500) {
            appendMessage('Your message is too long. Please keep it under 500 characters.', false);
            userInput.value = '';
            return;
        }

        // ===== Start Processing =====
        isProcessing = true;
        sendBtn.disabled = true;
        userInput.disabled = true;

        // Show user message
        appendMessage(text, true);
        userInput.value = '';

        // Show typing indicator
        const typingId = showTypingIndicator();
        const chosenDelay = Math.floor(Math.random() * (THINK_MAX - THINK_MIN + 1)) + THINK_MIN;

        try {
            // Start timing to ensure minimum delay
            const startTime = Date.now();
            
            const endpoint = '../../api/ai/chat.php';
            const payload = { message: text };
            console.log('[EduSync Chat] Request', { endpoint, payload });
            const response = await fetchWithTimeout(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
                timeout: API_TIMEOUT
            });

            // Parse response immediately
            let data;
            try {
                data = await response.json();
            } catch (jsonErr) {
                const rawText = await response.text().catch(() => '');
                console.error('[EduSync Chat] JSON parse error', jsonErr);
                showError({ status: response.status, ok: response.ok, raw: rawText });
                throw jsonErr;
            }
            console.log('[EduSync Chat] Response', { status: response.status, ok: response.ok, data });
            
            // Calculate elapsed time and wait for minimum typing delay
            const elapsedTime = Date.now() - startTime;
            const remainingDelay = Math.max(0, chosenDelay - elapsedTime);
            
            // Wait for remaining delay to ensure smooth animation
            if (remainingDelay > 0) {
                await new Promise(resolve => setTimeout(resolve, remainingDelay));
            }
            
            // Remove typing indicator
            const typingElement = document.getElementById(typingId);
            if (typingElement) {
                typingElement.remove();
            }
            
            // Display response or error message
            if (response.ok && data?.response && data?.status !== 'error') {
                appendMessage(data.response, false, data?.source || null);
            } else if (data?.response) {
                // Even if HTTP error, show the response if available
                appendMessage(data.response, false, data?.source || null);
                showError({ status: response.status, ok: response.ok, data });
            } else {
                // Generic error
                appendMessage('Sorry, I encountered an error. Please try again.', false, 'internal');
                showError({ status: response.status, ok: response.ok, data });
            }

        } catch (error) {
            // Remove typing indicator on error
            const typingElement = document.getElementById(typingId);
            if (typingElement) {
                typingElement.remove();
            }

            // Handle specific error types
            if (error.name === 'AbortError') {
                appendMessage('Request timeout. Please try again.', false, 'internal');
                showError({ type: 'AbortError', message: String(error) });
            } else if (error instanceof TypeError) {
                appendMessage('Network error. Please check your connection.', false, 'internal');
                showError({ type: 'TypeError', message: String(error) });
            } else {
                appendMessage('An unexpected error occurred. Please try again.', false, 'internal');
                showError({ type: 'Unexpected', message: String(error) });
            }

        } finally {
            // ===== Reset Processing State =====
            isProcessing = false;
            sendBtn.disabled = false;
            userInput.disabled = false;
            userInput.focus();
        }
    }

    /**
     * Clear entire chat history with confirmation
     */
    function clearChat() {
        // Don't allow clearing while API request is processing
        if (isProcessing) {
            return;
        }

        // Clear all messages
        chatContainer.innerHTML = '';

        // Show welcome message
        const div = document.createElement('div');
        div.className = 'flex items-start space-x-3';
        div.innerHTML = `
            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-green-500 to-blue-600 flex-shrink-0 flex items-center justify-center text-white shadow-md">
                <i class="fas fa-robot text-lg"></i>
            </div>
            <div class="bg-white/80 dark:bg-gray-800/70 backdrop-blur-md p-4 rounded-2xl rounded-tl-none shadow-sm max-w-[85%] md:max-w-[70%] border border-gray-100/60 dark:border-gray-700/60 text-sm md:text-base text-gray-800 dark:text-gray-200">
                <p>Chat history cleared. How can I help? 😊</p>
            </div>
        `;
        chatContainer.appendChild(div);
        autoScroll();
    }

    // Ensure input is focused on page load
    document.addEventListener('DOMContentLoaded', () => {
        userInput.focus();
    });

    function showError(obj) {
        try {
            const txt = typeof obj === 'string' ? obj : JSON.stringify(obj, null, 2);
            errorContent.textContent = txt;
            if (!REDUCED_MOTION && window.anime) {
                errorPanel.style.display = '';
                anime({
                    targets: errorPanel,
                    opacity: [0, 1],
                    translateY: [8, 0],
                    duration: 250,
                    easing: 'easeOutQuad'
                });
            } else {
                errorPanel.style.display = '';
            }
            console.error('[EduSync Chat] Error', obj);
        } catch (e) {
            console.error('[EduSync Chat] showError failed', e);
        }
    }

    // Search filter
    const chatSearch = document.getElementById('chatSearch');
    chatSearch.addEventListener('input', () => {
        const q = chatSearch.value.toLowerCase();
        const nodes = chatContainer.children;
        Array.from(nodes).forEach(n => {
            const t = n.textContent.toLowerCase();
            n.style.display = t.includes(q) ? '' : 'none';
        });
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            chatSearch.focus();
            chatSearch.select();
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>
