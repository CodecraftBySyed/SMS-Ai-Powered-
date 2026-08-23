<?php
require_once '../api/common/auth.php';
$current_page = 'about';
require_once 'includes/header.php';
?>
<div class="px-4 py-6 max-w-6xl mx-auto">
    <div class="mb-8 text-center">
        <h1 class="text-3xl md:text-4xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-600 dark:from-blue-400 dark:to-purple-400">
            Edusync – A Smart Student Management System
        </h1>
        <h2 class="mt-1 text-xl md:text-2xl font-semibold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-600 dark:from-blue-400 dark:to-purple-400">
            Priyadarshini Polytechnic College, Vaniyambadi – 635 751
        </h2>
        <p class="mt-2 text-gray-600 dark:text-gray-300 text-sm md:text-base">
            Final Year Local Demo Project • PHP • MySQL • Tailwind • Chart.js • SweetAlert
        </p>
    </div>

    <section class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
        <div class="rounded-xl border bg-white dark:bg-gray-900 p-6 hover:shadow-lg transition">
            <h2 class="text-lg font-semibold mb-2">Project Objectives</h2>
            <ul class="space-y-2 text-sm leading-relaxed text-gray-700 dark:text-gray-300">
                <li>• Streamline student registration and academic data</li>
                <li>• Provide attendance and marks tracking with insights</li>
                <li>• Enable admin analytics and fee visibility</li>
                <li>• Deliver mobile‑friendly UX with smooth animations</li>
                <li>• Integrate AI for guided student analyses</li>
            </ul>
        </div>
        <div class="rounded-xl border bg-white dark:bg-gray-900 p-6 hover:shadow-lg transition">
            <h2 class="text-lg font-semibold mb-2">Methodology</h2>
            <ul class="space-y-2 text-sm leading-relaxed text-gray-700 dark:text-gray-300">
                <li>• Iterative sprints with weekly check‑ins</li>
                <li>• Static‑first routing, AI fallback via tools</li>
                <li>• Defensive database access with prepared statements</li>
                <li>• Accessibility and performance first</li>
            </ul>
        </div>
        <div class="rounded-xl border bg-white dark:bg-gray-900 p-6 hover:shadow-lg transition">
            <h2 class="text-lg font-semibold mb-2">Technologies Used</h2>
            <ul class="space-y-2 text-sm leading-relaxed text-gray-700 dark:text-gray-300">
                <li>• PHP, MySQL, HTML5, CSS3, JavaScript</li>
                <li>• Tailwind CSS, Font Awesome, Chart.js</li>
                <li>• SweetAlert for notifications</li>
                <li>• Bootstrap/Ajax referenced in early prototypes</li>
            </ul>
        </div>
    </section>

    <section class="mb-10">
        <h2 class="text-xl font-bold mb-3">Team Members & Contributions</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-xl border p-5 bg-white dark:bg-gray-900 hover:translate-y-[-2px] transition">
                <div class="font-semibold">N Syed Asrar Saqib</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Backend Lead</div>
                <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">Database integrity, AI integration, tool-calling architecture.</p>
            </div>
            <div class="rounded-xl border p-5 bg-white dark:bg-gray-900 hover:translate-y-[-2px] transition">
                <div class="font-semibold">N Syed Maaz</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Frontend Lead</div>
                <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">Responsive UI, dashboards, animations, accessibility.</p>
            </div>
            <div class="rounded-xl border p-5 bg-white dark:bg-gray-900 hover:translate-y-[-2px] transition">
                <div class="font-semibold">M Ragul</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Modules & Testing</div>
                <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">Attendance and marks modules, QA, edge cases.</p>
            </div>
            <div class="rounded-xl border p-5 bg-white dark:bg-gray-900 hover:translate-y-[-2px] transition">
                <div class="font-semibold">K Suresh</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Admin & Notices</div>
                <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">Admin operations, notices, documentation support.</p>
            </div>
        </div>
        <div class="mt-4 rounded-xl border p-5 bg-white dark:bg-gray-900">
            <p class="text-sm text-gray-700 dark:text-gray-300">
                Guided by <span class="font-semibold">Mr. Srinivasan sir</span> — planning, reviews, and final evaluation.
            </p>
        </div>
    </section>

    <section class="mb-10">
        <h2 class="text-xl font-bold mb-3">Project Story & Development Journey</h2>
        <div class="rounded-xl border p-6 bg-white dark:bg-gray-900">
            <p class="text-sm leading-relaxed text-gray-700 dark:text-gray-300">
                The project began with schema design and core APIs, progressed through dashboards and charts, and culminated in an AI‑assisted chat for guided analytics. Performance and accessibility guided decisions, with static‑first routing and light animations ensuring smooth experience on low‑end devices.
            </p>
        </div>
    </section>

    <section class="mb-10">
        <h2 class="text-xl font-bold mb-3">Core Features</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="rounded-xl border p-5 bg-white dark:bg-gray-900">
                <ul class="text-sm space-y-2 text-gray-700 dark:text-gray-300">
                    <li>• Student registration</li>
                    <li>• Attendance tracking</li>
                    <li>• Grade management</li>
                    <li>• Notice board</li>
                    <li>• Admin dashboard</li>
                </ul>
            </div>
            <div class="rounded-xl border p-5 bg-white dark:bg-gray-900">
                <h3 class="font-semibold mb-2">Database Schema Overview</h3>
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    Students, attendance, marks, subjects, departments, users with appropriate foreign keys, unique indexes, and prepared statements throughout the data layer.
                </p>
            </div>
        </div>
    </section>

    <section class="mb-10">
        <h2 class="text-xl font-bold mb-3">Project Timeline</h2>
        <div class="rounded-xl border p-5 bg-white dark:bg-gray-900">
            <ol class="list-decimal ml-5 text-sm text-gray-700 dark:text-gray-300 space-y-1">
                <li>Planning & requirements</li>
                <li>Backend & schema implementation</li>
                <li>Dashboards & charts</li>
                <li>AI integration and stabilization</li>
                <li>Testing, performance tuning, documentation</li>
            </ol>
        </div>
    </section>

    <section class="mb-10">
        <h2 class="text-xl font-bold mb-3">Lessons Learned</h2>
        <div class="rounded-xl border p-6 bg-white dark:bg-gray-900">
            <ul class="text-sm space-y-2 text-gray-700 dark:text-gray-300">
                <li>• Defensive programming prevents runtime surprises</li>
                <li>• Static‑first strategies lower cost and complexity</li>
                <li>• Accessibility and performance must be baked in early</li>
            </ul>
        </div>
    </section>

    <section class="mb-10">
        <h2 class="text-xl font-bold mb-3">Acknowledgments</h2>
        <div class="rounded-xl border p-6 bg-white dark:bg-gray-900">
            <p class="text-sm text-gray-700 dark:text-gray-300">
                Heartfelt thanks to <span class="font-semibold">Mr. Srinivasan sir</span> and our institution for guidance, resources, and continuous support throughout the project.
            </p>
            <div class="mt-4">
                <button id="ackBtn" class="px-4 py-2 rounded-full bg-green-600 hover:bg-green-700 text-white text-sm transition">
                    View Acknowledgements
                </button>
            </div>
        </div>
    </section>

    <section class="mb-10">
        <h2 class="text-xl font-bold mb-3">Screenshots</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <img class="rounded-lg border object-cover w-full h-40" src="assets/images/dashboard.png" alt="Dashboard Overview" />
            <img class="rounded-lg border object-cover w-full h-40" src="assets/images/students_list.png" alt="Student List" />
            <img class="rounded-lg border object-cover w-full h-40" src="assets/images/attendance_trend.png" alt="Attendance Trend" />
        </div>
        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Place images under public/assets/images/*.png</p>
    </section>
</div>
<script>
    // Intersection animations: fade/slide on section reveal
    document.addEventListener('DOMContentLoaded', () => {
        const sections = document.querySelectorAll('section, .rounded-xl');
        sections.forEach(el => {
            el.classList.add('opacity-0','translate-y-2','transition','duration-300');
        });
        const io = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    e.target.classList.remove('opacity-0','translate-y-2');
                    e.target.classList.add('opacity-100','translate-y-0');
                    io.unobserve(e.target);
                }
            });
        }, { rootMargin: '80px' });
        sections.forEach(el => io.observe(el));
    });

    // SweetAlert notification
    document.getElementById('ackBtn')?.addEventListener('click', () => {
        if (window.Swal) {
            Swal.fire({
                title: 'Acknowledgements',
                text: 'Thanks to our guide Mr. Srinivasan sir and our institution for support and encouragement.',
                icon: 'success',
                confirmButtonText: 'Close'
            });
        }
    });
</script>
<?php require_once 'includes/footer.php'; ?>
