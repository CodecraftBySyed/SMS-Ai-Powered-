<?php
require_once '../../api/common/auth.php';
$current_page = 'student-analysis';
require_once '../includes/header.php';
?>
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex flex-col gap-4">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-600 dark:from-blue-400 dark:to-purple-400">
                    Student Analysis
                </h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">Visual report of attendance, marks and performance</p>
            </div>
        <div class="no-print flex items-center gap-3 w-full md:w-auto">
                <select id="studentSelect" class="bg-white dark:bg-gray-800 border rounded-lg px-3 py-2 text-sm w-full md:w-96">
                    <option value="">Select Student</option>
                </select>
                <button id="downloadPdf" class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm transition">Download Report as PDF</button>
            </div>
        </div>
        <div class="no-print bg-white dark:bg-gray-900 border rounded-xl p-4 grid grid-cols-1 md:grid-cols-3 gap-3">
            <select id="deptFilter" class="bg-white dark:bg-gray-800 border rounded-lg px-3 py-2 text-sm">
                <option value="">All Departments</option>
            </select>
            <select id="yearFilter" class="bg-white dark:bg-gray-800 border rounded-lg px-3 py-2 text-sm">
                <option value="">All Years</option>
                <option value="1">Year 1</option>
                <option value="2">Year 2</option>
                <option value="3">Year 3</option>
            </select>
            <button id="resetFilters" class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-sm">Reset</button>
        </div>
    </div>

    <div id="infoCard" class="rounded-xl border bg-white dark:bg-gray-900 p-5 hidden">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Student Name</div>
                <div id="sName" class="font-semibold text-lg"></div>
            </div>
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Department</div>
                <div id="sDept" class="font-semibold"></div>
            </div>
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Year • Reg No</div>
                <div id="sYearReg" class="font-semibold"></div>
            </div>
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Parent Name</div>
                <div id="pName" class="font-semibold"></div>
            </div>
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Parent Mobile</div>
                <div id="pMobile" class="font-semibold"></div>
            </div>
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Performance Status</div>
                <div id="sStatus" class="inline-block px-3 py-1 rounded-full text-xs font-semibold"></div>
            </div>
        </div>
    </div>

    <div id="kpiGrid" class="grid grid-cols-1 sm:grid-cols-4 gap-4 hidden">
        <div class="rounded-xl border bg-white dark:bg-gray-900 p-5 shadow-sm">
            <div class="text-xs text-gray-500 dark:text-gray-400">Average Attendance</div>
            <div id="kpiAttendance" class="text-2xl font-extrabold mt-1"></div>
        </div>
        <div class="rounded-xl border bg-white dark:bg-gray-900 p-5 shadow-sm">
            <div class="text-xs text-gray-500 dark:text-gray-400">Average Marks</div>
            <div id="kpiMarks" class="text-2xl font-extrabold mt-1"></div>
        </div>
        <div class="rounded-xl border bg-white dark:bg-gray-900 p-5 shadow-sm">
            <div class="text-xs text-gray-500 dark:text-gray-400">Fees Balance</div>
            <div id="kpiBalance" class="text-2xl font-extrabold mt-1"></div>
        </div>
        <div class="rounded-xl border bg-white dark:bg-gray-900 p-5 shadow-sm">
            <div class="text-xs text-gray-500 dark:text-gray-400">Last Month Attendance</div>
            <div id="kpiLastMonth" class="text-2xl font-extrabold mt-1"></div>
        </div>
    </div>

    <div id="reportGrid" class="grid grid-cols-1 md:grid-cols-2 gap-6 hidden">
        <div class="rounded-xl border bg-white dark:bg-gray-900 p-5 shadow-sm h-72">
            <div class="font-semibold mb-2">Attendance</div>
            <canvas id="attendanceChart" class="w-full h-full"></canvas>
        </div>
        <div class="rounded-xl border bg-white dark:bg-gray-900 p-5 shadow-sm h-72">
            <div class="font-semibold mb-2">Subject-wise Marks</div>
            <canvas id="marksChart" class="w-full h-full"></canvas>
        </div>
        <div class="rounded-xl border bg-white dark:bg-gray-900 p-5 shadow-sm md:col-span-2 h-64">
            <div class="font-semibold mb-2">Attendance Trend</div>
            <canvas id="attendanceTrend" class="w-full h-full"></canvas>
        </div>
        <div class="rounded-xl border bg-white dark:bg-gray-900 p-5 shadow-sm md:col-span-2">
            <div class="font-semibold mb-2">Fees Summary</div>
            <div id="feesSummary" class="text-sm text-gray-700 dark:text-gray-300"></div>
        </div>
        <div class="rounded-xl border bg-white dark:bg-gray-900 p-5 shadow-sm md:col-span-2">
            <div class="font-semibold mb-3">Subject Marks Table</div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-600 dark:text-gray-300">
                            <th class="py-2 px-2">Subject</th>
                            <th class="py-2 px-2">Marks</th>
                            <th class="py-2 px-2">Total</th>
                            <th class="py-2 px-2">%</th>
                        </tr>
                    </thead>
                    <tbody id="marksTBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<style>
@media print {
  .no-print { display: none !important; }
  #sidebar, #sidebarOverlay { display: none !important; }
  .md\:ml-64 { margin-left: 0 !important; }
  .avoid-break { break-inside: avoid; page-break-inside: avoid; }
}
</style>
<script>
    // Load html2pdf library via CDN
    (function() {
        const s = document.createElement('script');
        s.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
        s.defer = true;
        document.head.appendChild(s);
    })();

    const studentSelect = document.getElementById('studentSelect');
    const infoCard = document.getElementById('infoCard');
    const reportGrid = document.getElementById('reportGrid');
    const kpiGrid = document.getElementById('kpiGrid');
    const sName = document.getElementById('sName');
    const sDept = document.getElementById('sDept');
    const sYearReg = document.getElementById('sYearReg');
    const pName = document.getElementById('pName');
    const pMobile = document.getElementById('pMobile');
    const sStatus = document.getElementById('sStatus');
    const feesSummary = document.getElementById('feesSummary');
    const downloadBtn = document.getElementById('downloadPdf');
    const kpiAttendance = document.getElementById('kpiAttendance');
    const kpiMarks = document.getElementById('kpiMarks');
    const kpiBalance = document.getElementById('kpiBalance');
    const marksTBody = document.getElementById('marksTBody');
    const kpiLastMonth = document.getElementById('kpiLastMonth');

    let attendanceChart = null;
    let marksChart = null;
    let attendanceTrend = null;
    let allStudents = [];
    let reportLoading = false;
    const DEBUG = false;
    const dlog = (...args) => { if (DEBUG) console.log('[StudentAnalysis]', ...args); };

    async function ensureChart() {
        if (window.Chart) return;
        await new Promise((resolve, reject) => {
            const sc = document.createElement('script');
            sc.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            sc.onload = resolve;
            sc.onerror = reject;
            document.head.appendChild(sc);
        });
    }

    async function loadStudents() {
        try {
            const res = await fetch('../../api/students/read.php', { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('Failed to load students');
            allStudents = await res.json();
            applyFilters();
        } catch (e) { Swal.fire('Error', 'Failed to load students', 'error'); }
    }

    async function loadDepartments() {
        try {
            const res = await fetch('../../api/common/departments.php');
            const deps = await res.json();
            const deptFilter = document.getElementById('deptFilter');
            deptFilter.innerHTML = '<option value=\"\">All Departments</option>' + deps.map(d => `<option value=\"${d.id}\">${d.name}</option>`).join('');
        } catch (e) { /* silent; optional filter */ }
    }

    function applyFilters() {
        const d = document.getElementById('deptFilter').value;
        const y = document.getElementById('yearFilter').value;
        const selected = studentSelect.value;
        const filtered = allStudents.filter(s => {
            const okD = d === '' || String(s.dept_id) === String(d);
            const okY = y === '' || String(s.year) === String(y);
            return okD && okY;
        });
        studentSelect.innerHTML = '<option value=\"\">Select Student</option>' + filtered.map(s => `<option value=\"${s.id}\">${s.name} (${s.reg_no})</option>`).join('');
        if (selected && filtered.some(s => String(s.id) === String(selected))) {
            studentSelect.value = selected;
        } else {
            studentSelect.value = '';
        }
        if (window.anime) {
            anime({ targets: '#studentSelect', opacity:[0,1], translateY:[6,0], duration: 220, easing: 'easeOutQuad' });
        }
    }

    function statusClass(status) {
        switch((status || '').toLowerCase()) {
            case 'good': return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
            case 'average': return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400';
            case 'needs attention': return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
            case 'ok': return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
            default: return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
        }
    }

    function renderAttendanceChart(percent) {
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        const val = Math.max(0, Math.min(100, percent));
        const data = [val, 100 - val];
        if (window.attendanceChart) { try { window.attendanceChart.destroy(); } catch(e) {} }
        if (attendanceChart) { try { attendanceChart.destroy(); } catch(e) {} }
        attendanceChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Present %', 'Absent %'],
                datasets: [{ data, backgroundColor: ['#22c55e', '#e5e7eb'] }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: true, position: 'bottom' } },
                cutout: '60%'
            }
        });
        window.attendanceChart = attendanceChart;
    }

    function dedupeSubjects(items) {
        const map = new Map();
        for (const it of items) {
            const key = String(it.subject_name || '').trim();
            const cur = map.get(key);
            const marks = Number(it.marks || 0);
            const total = Number(it.total_marks || 100) || 100;
            if (!cur || marks / total > Number(cur.marks || 0) / (Number(cur.total_marks || 100) || 100)) {
                map.set(key, it);
            }
        }
        return Array.from(map.values());
    }

    function renderMarksChart(items) {
        const deduped = dedupeSubjects(items);
        const ctx = document.getElementById('marksChart').getContext('2d');
        const labels = deduped.map(x => x.subject_name);
        const data = deduped.map(x => Number(x.marks));
        if (window.marksChart) { try { window.marksChart.destroy(); } catch(e) {} }
        if (marksChart) { try { marksChart.destroy(); } catch(e) {} }
        marksChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Marks',
                    data,
                    backgroundColor: '#3b82f6'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { suggestedMax: 100, beginAtZero: true }
                }
            }
        });
        window.marksChart = marksChart;
    }

    function renderAttendanceTrend(rows) {
        const ctx = document.getElementById('attendanceTrend').getContext('2d');
        const labels = rows.map(r => r.week_date);
        const data = rows.map(r => Number(r.percentage));
        if (window.attendanceTrend) { try { window.attendanceTrend.destroy(); } catch(e) {} }
        if (attendanceTrend) { try { attendanceTrend.destroy(); } catch(e) {} }
        attendanceTrend = new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Attendance %',
                    data,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16,185,129,0.2)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { suggestedMax: 100, beginAtZero: true } }
            }
        });
        window.attendanceTrend = attendanceTrend;
    }

    function renderMarksTable(items) {
        const deduped = dedupeSubjects(items);
        marksTBody.innerHTML = deduped.map(it => {
            const total = Number(it.total_marks || 100) || 100;
            const marks = Number(it.marks || 0);
            const pct = total > 0 ? Math.round((marks / total) * 100) : 0;
            let cls = 'text-gray-700 dark:text-gray-300';
            if (pct >= 75) cls = 'text-green-600';
            else if (pct >= 50) cls = 'text-yellow-600';
            else cls = 'text-red-600';
            return `<tr class="border-t dark:border-gray-800">
                <td class="py-2 px-2">${it.subject_name}</td>
                <td class="py-2 px-2">${marks}</td>
                <td class="py-2 px-2">${total}</td>
                <td class="py-2 px-2 font-semibold ${cls}">${pct}%</td>
            </tr>`;
        }).join('');
    }

    async function loadReport(id) {
        if (reportLoading) {
            dlog('Ignored loadReport: already loading');
            return;
        }
        reportLoading = true;
        dlog('loadReport start for id', id);
        try {
            const res = await fetch(`../../api/students/analysis.php?student_id=${id}`, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('Failed to load report');
            const data = await res.json();
            if (!data.success) {
                Swal.fire('Error', data.error || 'Unable to load report', 'error');
                return; 
            }
            await ensureChart();
            window._lastAnalysisData = data;
            const s = data.student;
            infoCard.classList.remove('hidden');
            reportGrid.classList.remove('hidden');
            kpiGrid.classList.remove('hidden');

            sName.textContent = s.name;
            sDept.textContent = s.dept_name || '-';
            sYearReg.textContent = `Year ${s.year} • ${s.reg_no}`;
            pName.textContent = s.parent_name || '-';
            pMobile.textContent = s.parent_mobile || '-';
            sStatus.textContent = s.status || 'N/A';
            sStatus.className = `inline-block px-3 py-1 rounded-full text-xs font-semibold ${statusClass(s.status)}`;

            renderAttendanceChart(data.attendance || 0);
            renderMarksChart(data.marks || []);
            renderAttendanceTrend(data.attendance_records || []);
            renderMarksTable(data.marks || []);
            feesSummary.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800/60">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Total Fee</div>
                        <div class="font-semibold">₹${Number(s.total_fee).toLocaleString()}</div>
                    </div>
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800/60">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Paid Fee</div>
                        <div class="font-semibold">₹${Number(s.paid_fee).toLocaleString()}</div>
                    </div>
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800/60">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Balance</div>
                        <div class="font-semibold ${Number(s.balance_fee) > 0 ? 'text-red-600' : 'text-green-600'}">₹${Number(s.balance_fee).toLocaleString()}</div>
                    </div>
                </div>`;
            kpiAttendance.textContent = `${Number(data.attendance || 0).toFixed(1)}%`;
            kpiMarks.textContent = `${Number(data.marks_overall || 0).toFixed(1)}%`;
            kpiBalance.textContent = `₹${Number(s.balance_fee).toLocaleString()}`;
            kpiLastMonth.textContent = `${Number(data.attendance_last_month || 0).toFixed(1)}%`;
            if (window.anime) {
                anime({ targets: '#kpiGrid .rounded-xl', opacity:[0,1], translateY:[8,0], delay: anime.stagger(40), duration: 220, easing: 'easeOutQuad' });
                anime({ targets: '#reportGrid .rounded-xl', opacity:[0,1], translateY:[8,0], delay: anime.stagger(40), duration: 220, easing: 'easeOutQuad' });
            }
        } catch (e) { 
            Swal.fire('Error', 'Failed to fetch report', 'error'); 
        } finally {
            reportLoading = false;
            dlog('loadReport end for id', id);
        }
    }

    studentSelect.addEventListener('change', () => {
        const id = studentSelect.value;
        if (id) {
            loadReport(id);
        } else {
            infoCard.classList.add('hidden');
            reportGrid.classList.add('hidden');
        }
    });

    function tweakChartForPdf(chart) {
        if (!chart || !chart.options) return () => {};
        const old = {
            legend: chart.options.plugins && chart.options.plugins.legend ? chart.options.plugins.legend.display : undefined,
            animation: chart.options.animation
        };
        if (chart.options.plugins && chart.options.plugins.legend) chart.options.plugins.legend.display = false;
        chart.options.animation = false;
        try { chart.update('none'); } catch(e) {}
        return () => {
            if (chart.options.plugins && chart.options.plugins.legend && typeof old.legend !== 'undefined') {
                chart.options.plugins.legend.display = old.legend;
            }
            chart.options.animation = old.animation;
            try { chart.update('none'); } catch(e) {}
        };
    }

    function captureChartImg(chart) {
        try { return chart && chart.toBase64Image ? chart.toBase64Image() : null; } catch(e){ return null; }
    }

    function buildPdfReport() {
        const data = window._lastAnalysisData || {};
        const s = data.student || {};
        const marks = (data.marks || []);
        const deduped = (typeof dedupeSubjects === 'function') ? dedupeSubjects(marks) : marks;
        const restoreAtt = tweakChartForPdf(window.attendanceChart);
        const restoreMarks = tweakChartForPdf(window.marksChart);
        const attImg = captureChartImg(window.attendanceChart);
        const marksImg = captureChartImg(window.marksChart);
        restoreAtt(); restoreMarks();

        const wrap = document.createElement('div');
        wrap.id = 'pdfReport';
        wrap.style.maxWidth = '900px';
        wrap.style.margin = '0 auto';
        wrap.style.padding = '16px';

        const header = `
            <div style="text-align:center; margin-bottom:8px;">
                <div style="font-size:19px; font-weight:800; letter-spacing:0.3px;">STUDENT ANALYSIS VIRTUAL REPORT</div>
                <div style="margin-top:4px; font-size:13px; font-weight:600;">Attendance, Marks & Performance Analysis Using AI</div>
                <div style="margin-top:2px; font-size:11px; color:#333;">EduSync – A Smart Student Management System</div>
            </div>
        `;
        const info = `
            <div class="avoid-break" style="border:1px solid #e5e7eb; border-radius:10px; padding:8px; margin-bottom:8px; background:#fff;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px; font-size:10px; line-height:1.25;">
                    <div><strong>Name:</strong> ${s.name || '-'}</div>
                    <div><strong>Register Number:</strong> ${s.reg_no || '-'}</div>
                    <div><strong>Department:</strong> ${s.dept_name || '-'}</div>
                    <div><strong>Year:</strong> ${s.year ? 'Year ' + s.year : '-'}</div>
                    <div><strong>Parent Name:</strong> ${s.parent_name || '-'}</div>
                    <div><strong>Parent Mobile:</strong> ${s.parent_mobile || '-'}</div>
                </div>
            </div>
        `;
        const attendanceSec = `
            <div class="avoid-break" style="border:1px solid #e5e7eb; border-radius:10px; padding:8px; margin-bottom:8px; background:#fff;">
                <div style="font-weight:700; margin-bottom:6px; font-size:12px;">Attendance</div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px; margin-bottom:6px; font-size:10px;">
                    <div><strong>Overall Attendance:</strong> ${Number(data.attendance || 0).toFixed(1)}%</div>
                    <div><strong>Last Month Attendance:</strong> ${Number(data.attendance_last_month || 0).toFixed(1)}%</div>
                </div>
                ${attImg ? `<img src="${attImg}" style="width:100%; max-height:160px; object-fit:contain;" />` : ''}
            </div>
        `;
        const marksRows = deduped.map(it => {
            const total = Number(it.total_marks || 100) || 100;
            const m = Number(it.marks || 0);
            const pct = total > 0 ? Math.round((m/total)*100) : 0;
            return `<tr>
                <td style="padding:4px 6px; border-top:1px solid #eee; font-size:10px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${it.subject_name}</td>
                <td style="padding:4px 6px; border-top:1px solid #eee; text-align:right; font-size:10px;">${m}</td>
                <td style="padding:4px 6px; border-top:1px solid #eee; text-align:right; font-size:10px;">${total}</td>
                <td style="padding:4px 6px; border-top:1px solid #eee; text-align:right; font-size:10px;">${pct}%</td>
            </tr>`;
        }).join('');
        const perfSec = `
            <div class="avoid-break" style="border:1px solid #e5e7eb; border-radius:10px; padding:8px; margin-bottom:8px; background:#fff;">
                <div style="font-weight:700; margin-bottom:6px; font-size:12px;">Academic Performance</div>
                ${marksImg ? `<img src="${marksImg}" style="width:100%; max-height:160px; object-fit:contain; margin-bottom:6px;" />` : ''}
                <table style="width:100%; border-collapse:collapse; font-size:10px;">
                    <thead>
                        <tr style="text-align:left; color:#374151;">
                            <th style="padding:4px 6px;">Subject</th>
                            <th style="padding:4px 6px; text-align:right;">Marks</th>
                            <th style="padding:4px 6px; text-align:right;">Total</th>
                            <th style="padding:4px 6px; text-align:right;">%</th>
                        </tr>
                    </thead>
                    <tbody>${marksRows}</tbody>
                </table>
                <div style="margin-top:6px; font-size:10px;"><strong>Average Percentage:</strong> ${Number(data.marks_overall || 0).toFixed(1)}%</div>
            </div>
        `;
        const good = deduped.filter(x => (Number(x.marks||0)/(Number(x.total_marks||100)||100)) >= 0.8).map(x => x.subject_name).slice(0,3);
        const weak = deduped.filter(x => (Number(x.marks||0)/(Number(x.total_marks||100)||100)) < 0.6).map(x => x.subject_name).slice(0,3);
        const overall = Number(data.marks_overall || 0);
        const evalTxt = overall >= 80 ? 'Strong overall academic performance.' :
                        overall >= 60 ? 'Consistent performance with room for improvement.' :
                        'Performance requires focused improvement.';
        const aiSec = `
            <div class="avoid-break" style="border:1px solid #e5e7eb; border-radius:10px; padding:8px; background:#fff;">
                <div style="font-weight:700; margin-bottom:6px; font-size:12px;">AI Performance Summary</div>
                <div style="font-size:10px;"><strong>Strengths:</strong> ${good.length ? good.join(', ') : '—'}</div>
                <div style="font-size:10px; margin-top:4px;"><strong>Weaknesses:</strong> ${weak.length ? weak.join(', ') : '—'}</div>
                <div style="font-size:10px; margin-top:4px; text-align:justify; line-height:1.25;">
                    <strong>Overall Evaluation:</strong> ${evalTxt}
                </div>
            </div>
        `;
        wrap.innerHTML = header + info + attendanceSec + perfSec + aiSec;
        return wrap;
    }

    downloadBtn.addEventListener('click', async () => {
        try {
            if (!window.html2pdf) {
                Swal.fire('Error', 'PDF library not loaded', 'error');
                return;
            }
            const ok = await Swal.fire({
                title: 'Generate PDF Report?',
                text: 'A professional academic report will be generated.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Confirm'
            }).then(r => r.isConfirmed);
            if (!ok) return;

            const pdfNode = buildPdfReport();
            document.body.appendChild(pdfNode);
            const opt = {
                margin:       10,
                filename:     'student-report.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 1.5, useCORS: true, letterRendering: true, backgroundColor: '#ffffff' },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
                pagebreak:    { mode: ['avoid-all', 'css', 'legacy'] }
            };
            await html2pdf().from(pdfNode).set(opt).save();
            pdfNode.remove();
            Swal.fire('Success', 'PDF downloaded successfully.', 'success');
        } catch (e) { Swal.fire('Error', 'Failed to generate PDF', 'error'); }
    });

    document.addEventListener('DOMContentLoaded', () => {
        loadDepartments();
        loadStudents();
        document.getElementById('deptFilter').addEventListener('change', applyFilters);
        document.getElementById('yearFilter').addEventListener('change', applyFilters);
        document.getElementById('resetFilters').addEventListener('click', () => {
            document.getElementById('deptFilter').value = '';
            document.getElementById('yearFilter').value = '';
            applyFilters();
        });
        if (window.anime) {
            anime({ targets: '.rounded-xl', opacity:[0,1], translateY:[8,0], delay: anime.stagger(50), duration: 250, easing: 'easeOutQuad' });
        }
    });

    (function() {
        const cart = document.querySelector('.js-cart');
        if (!cart) return;
        const container = cart.parentElement;
        container.style.position = container.style.position || 'relative';
        cart.style.position = 'absolute';
        let x = parseFloat(cart.dataset.x || '0');
        let y = parseFloat(cart.dataset.y || '0');
        let vx = 0, vy = 0;
        const g = 1500;
        const maxVy = 2200;
        const friction = 1100;
        const accel = 1800;
        let leftKey = false, rightKey = false;
        let last = performance.now();
        function bounds() {
            const w = container.clientWidth;
            const cw = cart.offsetWidth;
            return { left: 0, right: Math.max(0, w - cw) };
        }
        function groundY() {
            const h = container.clientHeight;
            const ch = cart.offsetHeight;
            return Math.max(0, h - ch);
        }
        function step(t) {
            const dt = Math.min(0.033, (t - last) / 1000);
            last = t;
            const gy = groundY();
            if (y < gy) {
                vy = Math.min(maxVy, vy + g * dt);
            } else {
                y = gy;
                vy = 0;
            }
            if (leftKey) vx -= accel * dt;
            if (rightKey) vx += accel * dt;
            if (!leftKey && !rightKey) {
                if (vx > 0) vx = Math.max(0, vx - friction * dt);
                else if (vx < 0) vx = Math.min(0, vx + friction * dt);
            }
            x += vx * dt;
            y += vy * dt;
            const b = bounds();
            if (x < b.left) { x = b.left; vx = 0; }
            if (x > b.right) { x = b.right; vx = 0; }
            if (y > gy) { y = gy; vy = 0; }
            if (!isFinite(x) || !isFinite(y)) {
                x = 0; y = gy; vx = 0; vy = 0;
            }
            cart.style.transform = 'translate(' + x + 'px,' + y + 'px)';
            requestAnimationFrame(step);
        }
        window.addEventListener('keydown', e => {
            if (e.key === 'ArrowLeft') leftKey = true;
            if (e.key === 'ArrowRight') rightKey = true;
        });
        window.addEventListener('keyup', e => {
            if (e.key === 'ArrowLeft') leftKey = false;
            if (e.key === 'ArrowRight') rightKey = false;
        });
        requestAnimationFrame(t => { last = t; y = groundY(); cart.style.transform = 'translate(' + x + 'px,' + y + 'px)'; requestAnimationFrame(step); });
        window.addEventListener('resize', () => {
            const gy = groundY();
            if (y > gy) y = gy;
        });
    })();
</script>
<?php require_once '../includes/footer.php'; ?>
