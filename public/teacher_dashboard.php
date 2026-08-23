<?php 
require_once '../api/common/auth.php';
// Role check specific to this page
if ($_SESSION['role'] !== 'teacher') {
    header("Location: dashboard.php");
    exit;
}
$current_page = 'dashboard';
include 'includes/header.php'; 
?>

<!-- Content -->
<div class="space-y-8">
    
    <!-- Welcome Section -->
    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
        <div>
            <h2 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 to-purple-600 dark:from-indigo-400 dark:to-purple-400">
                Teacher Dashboard
            </h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">
                Department: <span class="text-blue-500 font-semibold"><?php echo htmlspecialchars($_SESSION['dept_name'] ?? $_SESSION['dept_id']); ?></span>
            </p>
        </div>
        <a href="ai-chat/chatbot.php" class="flex items-center space-x-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-6 py-2 rounded-full shadow-lg hover:scale-105 transition transform">
            <i class="fas fa-robot"></i>
            <span>AI Assistant</span>
        </a>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="glass-effect p-6 rounded-xl border-l-4 border-blue-500 transform hover:scale-105 transition-transform duration-300">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">My Students</h3>
                    <p class="text-3xl font-bold mt-2 text-gray-800 dark:text-white" id="totalStudents">...</p>
                </div>
                <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg text-blue-600 dark:text-blue-400">
                    <i class="fas fa-users text-xl"></i>
                </div>
            </div>
        </div>

        <div class="glass-effect p-6 rounded-xl border-l-4 border-green-500 transform hover:scale-105 transition-transform duration-300">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">Avg Attendance</h3>
                    <p class="text-3xl font-bold mt-2 text-gray-800 dark:text-white" id="avgAttendance">...</p>
                </div>
                <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg text-green-600 dark:text-green-400">
                    <i class="fas fa-chart-line text-xl"></i>
                </div>
            </div>
        </div>

        <div class="glass-effect p-6 rounded-xl border-l-4 border-yellow-500 transform hover:scale-105 transition-transform duration-300">
             <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">Pending Fees</h3>
                    <p class="text-3xl font-bold mt-2 text-gray-800 dark:text-white" id="pendingFees">...</p>
                </div>
                <div class="p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg text-yellow-600 dark:text-yellow-400">
                    <i class="fas fa-file-invoice-dollar text-xl"></i>
                </div>
            </div>
        </div>

        <div class="glass-effect p-6 rounded-xl border-l-4 border-red-500 transform hover:scale-105 transition-transform duration-300">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">Needs Attention</h3>
                    <p class="text-3xl font-bold mt-2 text-gray-800 dark:text-white" id="needsAttention">...</p>
                </div>
                <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg text-red-600 dark:text-red-400">
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Attendance Line Chart -->
        <div class="glass-effect p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Attendance Trend</h3>
                <span class="text-xs font-medium px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded">Last 10 Weeks</span>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>

        <!-- Fee Pie Chart -->
        <div class="glass-effect p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
             <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Fee Collection Status</h3>
                <span class="text-xs font-medium px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded">Overview</span>
            </div>
            <div class="relative h-64 w-full flex justify-center">
                <canvas id="feeChart"></canvas>
            </div>
        </div>

        <!-- Performance Bar Chart -->
        <div class="glass-effect p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
             <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Student Performance</h3>
                <span class="text-xs font-medium px-2 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded">Overview</span>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="performanceChart"></canvas>
            </div>
        </div>

        <!-- Student Trend Line Chart -->
        <div class="glass-effect p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
             <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Year Level Analysis</h3>
                <span class="text-xs font-medium px-2 py-1 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded">Avg Marks</span>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="studentTrendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Quick Actions Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="attendance/mark.php" class="glass-effect p-6 rounded-xl shadow-lg hover:shadow-xl transition group transform hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 group-hover:bg-blue-600 group-hover:text-white transition">
                    <i class="fas fa-calendar-check text-xl"></i>
                </div>
                <span class="text-gray-400 group-hover:translate-x-1 transition">→</span>
            </div>
            <h3 class="text-xl font-bold text-gray-800 dark:text-white">Mark Attendance</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Weekly attendance entry</p>
        </a>

        <a href="fees/manage.php" class="glass-effect p-6 rounded-xl shadow-lg hover:shadow-xl transition group transform hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 group-hover:bg-green-600 group-hover:text-white transition">
                    <i class="fas fa-dollar-sign text-xl"></i>
                </div>
                <span class="text-gray-400 group-hover:translate-x-1 transition">→</span>
            </div>
            <h3 class="text-xl font-bold text-gray-800 dark:text-white">Collect Fees</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Update payment records</p>
        </a>

        <a href="marks/entry.php" class="glass-effect p-6 rounded-xl shadow-lg hover:shadow-xl transition group transform hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 group-hover:bg-purple-600 group-hover:text-white transition">
                    <i class="fas fa-chart-bar text-xl"></i>
                </div>
                <span class="text-gray-400 group-hover:translate-x-1 transition">→</span>
            </div>
            <h3 class="text-xl font-bold text-gray-800 dark:text-white">Enter Marks</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Record exam scores</p>
        </a>
    </div>

    <div class="glass-effect p-6 rounded-xl shadow-lg animate-slide-up" style="animation-delay: 0.3s;">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white">Students List</h3>
            <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                <select id="yearFilter" class="px-4 py-2 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Years</option>
                    <option value="1">Year 1</option>
                    <option value="2">Year 2</option>
                    <option value="3">Year 3</option>
                </select>
                <input id="studentSearch" type="text" placeholder="Search by Name or Reg No" class="w-full md:w-64 px-4 py-2 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-white">
                <select id="statusFilter" class="px-4 py-2 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Status</option>
                    <option value="Good">Good</option>
                    <option value="Average">Average</option>
                    <option value="Needs Attention">Needs Attention</option>
                </select>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-b dark:border-gray-600">
                        <th class="p-4 font-semibold">Student</th>
                        <th class="p-4 font-semibold">Reg No</th>
                        <th class="p-4 font-semibold text-center">Avg Attendance</th>
                        <th class="p-4 font-semibold text-center">Avg Marks</th>
                        <th class="p-4 font-semibold text-center">Status</th>
                    </tr>
                </thead>
                <tbody id="studentsTableBody" class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr><td colspan="5" class="p-8 text-center text-gray-500 dark:text-gray-400">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    // --- Global Chart Instances ---
    let charts = {};
    let pendingData = {}; 
    let dashboardStudents = [];
    // --- HTML Tooltip (Fade) ---
    function externalTooltipHandler(context) {
        const {chart, tooltip} = context;
        const canvasParent = chart.canvas.parentNode;
        let el = canvasParent.querySelector('.chart-tooltip');
        if (!el) {
            el = document.createElement('div');
            el.className = 'chart-tooltip pointer-events-none absolute z-50 opacity-0 transition-opacity duration-300';
            el.style.background = 'rgba(255,255,255,0.95)';
            el.style.border = '1px solid #e5e7eb';
            el.style.borderRadius = '0.5rem';
            el.style.boxShadow = '0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1)';
            el.style.padding = '10px';
            el.style.transform = 'translate(-50%, -110%)';
            el.style.whiteSpace = 'nowrap';
            canvasParent.style.position = 'relative';
            canvasParent.appendChild(el);
        }
        if (tooltip.opacity === 0) {
            el.style.opacity = '0';
            return;
        }
        const isDark = document.documentElement.classList.contains('dark');
        el.style.background = isDark ? 'rgba(17,24,39,0.95)' : 'rgba(255,255,255,0.95)';
        el.style.borderColor = isDark ? '#374151' : '#e5e7eb';
        el.style.color = isDark ? '#e5e7eb' : '#1f2937';
        const title = tooltip.title || [];
        const bodyLines = tooltip.body?.map(b => b.lines).flat() || [];
        el.innerHTML = `
            <div style="font-weight:600;margin-bottom:6px">${title.join(' ')}</div>
            <div style="font-size:12px">${bodyLines.join('<br/>')}</div>
        `;
        const {offsetLeft: positionX, offsetTop: positionY} = chart.canvas;
        el.style.left = positionX + tooltip.caretX + 'px';
        el.style.top = positionY + tooltip.caretY + 'px';
        el.style.opacity = '1';
    }

    // --- Intersection Observer for Lazy Loading ---
    const chartObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const canvasId = entry.target.id;
                if (pendingData[canvasId] && !charts[canvasId]) {
                    createChartInstance(canvasId, pendingData[canvasId].type, pendingData[canvasId].data);
                    chartObserver.unobserve(entry.target);
                }
            }
        });
    }, { rootMargin: '100px' });

    // --- Fetch & Update Data ---
    async function fetchStats() {
        try {
            const res = await fetch('../api/teacher/stats.php');
            const data = await res.json();

            if (data.error) throw new Error(data.error);

            // Update Metrics
            animateValue('totalStudents', 0, data.metrics.total_students, 1000);
            document.getElementById('avgAttendance').textContent = data.metrics.avg_attendance + '%';
            document.getElementById('pendingFees').textContent = '₹' + Number(data.metrics.pending_fees).toLocaleString();
            animateValue('needsAttention', 0, data.metrics.needs_attention, 1000);

            // Update Charts
            updateCharts(data.charts);
            dashboardStudents = Array.isArray(data.students) ? data.students : [];
            renderStudents(dashboardStudents);

        } catch (err) {}
    }

    function animateValue(id, start, end, duration) {
        if (start === end) return;
        const range = end - start;
        let current = start;
        const increment = end > start ? 1 : -1;
        const stepTime = Math.abs(Math.floor(duration / range));
        const obj = document.getElementById(id);
        const timer = setInterval(function() {
            current += increment;
            obj.textContent = current;
            if (current == end) {
                clearInterval(timer);
            }
        }, stepTime);
    }

    function updateCharts(data) {
        // 1. Attendance Line Chart
        updateChart('attendanceChart', 'line', {
            labels: data.attendance_trend.map(d => d.week_date),
            datasets: [{
                label: 'Avg Attendance %',
                data: data.attendance_trend.map(d => d.percentage),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        });

        // 2. Fee Pie Chart
        const feeLabels = data.fee_status.map(d => d.status);
        const feeCounts = data.fee_status.map(d => d.count);
        updateChart('feeChart', 'doughnut', {
            labels: feeLabels,
            datasets: [{
                data: feeCounts,
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        });

        // 3. Performance Bar Chart
        const perfLabels = data.performance.map(d => d.status);
        const perfCounts = data.performance.map(d => d.count);
        const perfColors = perfLabels.map(l => {
            if (l === 'Good') return '#10b981';
            if (l === 'Average') return '#f59e0b';
            return '#ef4444';
        });

        updateChart('performanceChart', 'bar', {
            labels: perfLabels,
            datasets: [{
                label: 'Students',
                data: perfCounts,
                backgroundColor: perfColors,
                borderRadius: 6,
                barThickness: 40
            }]
        });

        // 4. Student Trend Line Chart (Marks)
        updateChart('studentTrendChart', 'line', {
            labels: data.student_trend.map(d => 'Year ' + d.year),
            datasets: [{
                label: 'Avg Marks',
                data: data.student_trend.map(d => d.avg_marks),
                borderColor: '#8b5cf6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        });
    }

    function updateChart(canvasId, type, chartData) {
        if (charts[canvasId]) {
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#e5e7eb' : '#374151';
            const gridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';

            charts[canvasId].data = chartData;
             // Update options for theme
            if (charts[canvasId].options.plugins.legend) {
                charts[canvasId].options.plugins.legend.labels.color = textColor;
            }
            if (charts[canvasId].options.scales.y) {
                charts[canvasId].options.scales.y.grid.color = gridColor;
                charts[canvasId].options.scales.y.ticks.color = textColor;
            }
            if (charts[canvasId].options.scales.x) {
                charts[canvasId].options.scales.x.grid.color = gridColor;
                charts[canvasId].options.scales.x.ticks.color = textColor;
            }
            charts[canvasId].update('active');
        } else {
            pendingData[canvasId] = { type, data: chartData };
            const canvas = document.getElementById(canvasId);
            if (canvas) {
                chartObserver.observe(canvas);
            }
        }
    }

    function createChartInstance(canvasId, type, chartData) {
        const ctx = document.getElementById(canvasId).getContext('2d');
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#e5e7eb' : '#374151';
        const gridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';

        charts[canvasId] = new Chart(ctx, {
            type: type,
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: textColor, padding: 20, font: { family: "'Inter', sans-serif" } }
                    },
                    tooltip: {
                        enabled: false,
                        external: externalTooltipHandler
                    }
                },
                scales: type === 'doughnut' ? {} : {
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor, drawBorder: false },
                        ticks: { color: textColor, font: { family: "'Inter', sans-serif" } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: textColor, font: { family: "'Inter', sans-serif" } }
                    }
                },
                animation: { duration: 400, easing: 'easeOutCubic', animateRotate: type === 'doughnut', animateScale: type === 'doughnut' },
                animations: {
                    colors: { type: 'color', duration: 400, easing: 'easeOutCubic' },
                    x: { duration: 400, easing: 'easeOutCubic' },
                    y: { duration: 400, easing: 'easeOutCubic' },
                    tension: { duration: 400, easing: 'easeOutCubic', from: 0.6, to: 0.4 },
                    borderWidth: { duration: 400 },
                    radius: { duration: 300 }
                },
                transitions: {
                    show: { animations: { x: { duration: 400 }, y: { duration: 400 } } },
                    hide: { animations: { x: { duration: 400 }, y: { duration: 400 } } },
                    active: { animation: { duration: 400, easing: 'easeOutCubic' } }
                },
                elements: type === 'doughnut' ? { arc: { borderWidth: 0 } } : {}
            }
        });
        const canvasEl = ctx.canvas;
        canvasEl.classList.add('opacity-0');
        requestAnimationFrame(() => {
            canvasEl.classList.add('transition-opacity','duration-300','opacity-100');
            canvasEl.classList.remove('opacity-0');
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        fetchStats();
        setInterval(fetchStats, 30000);
        const s = document.getElementById('studentSearch');
        const f = document.getElementById('statusFilter');
        const y = document.getElementById('yearFilter');
        if (s) s.addEventListener('input', filterAndRender);
        if (f) f.addEventListener('change', filterAndRender);
        if (y) y.addEventListener('change', filterAndRender);

        document.addEventListener('themeChanged', () => {
            Object.keys(charts).forEach(id => {
                const chart = charts[id];
                const isDark = document.documentElement.classList.contains('dark');
                const textColor = isDark ? '#e5e7eb' : '#374151';
                const gridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
                
                if (chart.options.plugins.legend) chart.options.plugins.legend.labels.color = textColor;
                if (chart.options.scales.y) {
                    chart.options.scales.y.grid.color = gridColor;
                    chart.options.scales.y.ticks.color = textColor;
                }
                if (chart.options.scales.x) {
                    chart.options.scales.x.grid.color = gridColor;
                    chart.options.scales.x.ticks.color = textColor;
                }
                chart.update();
            });
        });
    });

    function renderStudents(data) {
        const tbody = document.getElementById('studentsTableBody');
        if (!tbody) return;
        if (!Array.isArray(data) || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="p-8 text-center text-gray-500 dark:text-gray-400">No students found</td></tr>';
            return;
        }
        tbody.innerHTML = data.map((s, idx) => `
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <td class="p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs">
                            ${s.name ? s.name.charAt(0) : '?'}
                        </div>
                        <div class="font-bold text-gray-800 dark:text-gray-200">${s.name || ''}</div>
                    </div>
                </td>
                <td class="p-4 text-sm text-gray-500 dark:text-gray-400 font-mono">${s.reg_no || ''}</td>
                <td class="p-4 text-center font-semibold">${typeof s.avg_att !== 'undefined' ? Number(s.avg_att).toFixed(1) + '%' : '-'}</td>
                <td class="p-4 text-center font-semibold">${typeof s.avg_marks !== 'undefined' ? Math.round(Number(s.avg_marks)) : '-'}</td>
                <td class="p-4 text-center">
                    <span class="px-2 py-1 rounded text-xs font-semibold ${s.status === 'Good' ? 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400' : s.status === 'Average' ? 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400' : 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400'}">
                        ${s.status || ''}
                    </span>
                </td>
            </tr>
        `).join('');
    }

    function filterAndRender() {
        const q = (document.getElementById('studentSearch')?.value || '').toLowerCase();
        const status = document.getElementById('statusFilter')?.value || '';
        const year = document.getElementById('yearFilter')?.value || '';
        const filtered = dashboardStudents.filter(s => {
            const matchText = (s.name || '').toLowerCase().includes(q) || (s.reg_no || '').toLowerCase().includes(q);
            const matchStatus = status ? (s.status === status) : true;
            const matchYear = year ? (String(s.year) === String(year)) : true;
            return matchText && matchStatus && matchYear;
        });
        renderStudents(filtered);
    }
</script>
<?php require_once 'includes/footer.php'; ?>
