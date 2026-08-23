<?php
require_once '../../api/common/auth.php';
checkTeacher();
$current_page = 'attendance';
require_once '../includes/header.php';
?>

<!-- Header & Actions -->
<div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
    <div>
        <h2 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-500 to-purple-500">Mark Attendance</h2>
        <p class="text-gray-500 dark:text-gray-400">Record daily student attendance</p>
    </div>
    
    <div class="flex flex-wrap items-center gap-4">
        <div class="flex flex-col">
            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Select Week</label>
            <input type="date" id="weekPicker" class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all shadow-sm" onchange="loadAttendance()">
        </div>
        
        <button onclick="bulkMarkPresent()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg transition transform hover:scale-105 flex items-center gap-2 mt-auto">
            <i class="fas fa-check-double"></i> Mark All Present
        </button>
        
        <button onclick="saveAttendance()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg shadow-lg transition transform hover:scale-105 flex items-center gap-2 mt-auto">
            <i class="fas fa-save"></i> Save Changes
        </button>
    </div>
</div>

<!-- Attendance Table -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-b dark:border-gray-600">
                    <th class="p-4 font-semibold">Student</th>
                    <th class="p-4 text-center font-semibold min-w-[80px]">Mon <div id="date-0" class="text-xs font-normal text-gray-500 dark:text-gray-400 mt-1"></div></th>
                    <th class="p-4 text-center font-semibold min-w-[80px]">Tue <div id="date-1" class="text-xs font-normal text-gray-500 dark:text-gray-400 mt-1"></div></th>
                    <th class="p-4 text-center font-semibold min-w-[80px]">Wed <div id="date-2" class="text-xs font-normal text-gray-500 dark:text-gray-400 mt-1"></div></th>
                    <th class="p-4 text-center font-semibold min-w-[80px]">Thu <div id="date-3" class="text-xs font-normal text-gray-500 dark:text-gray-400 mt-1"></div></th>
                    <th class="p-4 text-center font-semibold min-w-[80px]">Fri <div id="date-4" class="text-xs font-normal text-gray-500 dark:text-gray-400 mt-1"></div></th>
                    <th class="p-4 text-center font-semibold">Stats</th>
                </tr>
            </thead>
            <tbody id="studentList" class="divide-y divide-gray-200 dark:divide-gray-700">
                <tr>
                    <td colspan="7" class="p-8 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-spinner fa-spin text-3xl mb-3"></i>
                            <p>Loading attendance data...</p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    let currentDates = [];
    let studentsData = [];

    // Set default date to today
    document.getElementById('weekPicker').valueAsDate = new Date();

    async function loadAttendance() {
        const picker = document.getElementById('weekPicker');
        const date = new Date(picker.value);
        // Get Monday
        const day = date.getDay();
        const diff = date.getDate() - day + (day == 0 ? -6 : 1); 
        const monday = new Date(date.setDate(diff));
        const start_date = monday.toISOString().split('T')[0];

        // Show loading state
        document.getElementById('studentList').innerHTML = `
            <tr>
                <td colspan="7" class="p-8 text-center text-gray-500">
                    <div class="flex flex-col items-center justify-center">
                        <i class="fas fa-spinner fa-spin text-3xl mb-3 text-blue-500"></i>
                        <p>Loading attendance data...</p>
                    </div>
                </td>
            </tr>
        `;

        try {
            const res = await fetch(`../../api/attendance/get_weekly.php?start_date=${start_date}`);
            const data = await res.json();
            
            studentsData = data.students;
            currentDates = data.dates;

            renderTable();
        } catch (e) {
            console.error(e);
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Failed to load attendance data. Please try again.',
                confirmButtonColor: '#3b82f6'
            });
        }
    }

    function renderTable() {
        const tbody = document.getElementById('studentList');
        
        // Update Headers
        if (currentDates && currentDates.length > 0) {
            currentDates.forEach((d, i) => {
                if (document.getElementById(`date-${i}`)) {
                    document.getElementById(`date-${i}`).textContent = d.split('-').slice(1).join('/');
                }
            });
        }

        if (!studentsData || studentsData.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="p-8 text-center text-gray-500">
                        <p>No students found for this class.</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = studentsData.map((student, idx) => `
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <td class="p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-blue-600 dark:text-blue-300 font-bold text-xs">
                            ${student.name.charAt(0)}
                        </div>
                        <div>
                            <div class="font-bold text-gray-800 dark:text-gray-200">${student.name}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">${student.reg_no}</div>
                        </div>
                    </div>
                </td>
                ${currentDates.map(date => {
                    const isPresent = student.attendance[date] == 1;
                    return `
                        <td class="p-4 text-center">
                            <label class="inline-flex items-center cursor-pointer relative">
                                <input type="checkbox" 
                                    class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 cursor-pointer transition-transform transform active:scale-95"
                                    data-student="${student.id}"
                                    data-date="${date}"
                                    ${isPresent ? 'checked' : ''}
                                >
                            </label>
                        </td>
                    `;
                }).join('')}
                <td class="p-4 text-center text-sm">
                    <div class="flex justify-center">
                        <span class="px-2 py-1 rounded-full text-xs font-bold ${getPercentageColor(student.percentage)}">
                             ${student.percentage !== null ? parseFloat(student.percentage).toFixed(1) + '%' : 'N/A'}
                        </span>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function getPercentageColor(percentage) {
        if (percentage === null) return 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300';
        const p = parseFloat(percentage);
        if (p >= 90) return 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400';
        if (p >= 75) return 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400';
        if (p >= 60) return 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400';
        return 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400';
    }

    function bulkMarkPresent() {
        const checkboxes = document.querySelectorAll('input[type="checkbox"]');
        let changed = false;
        checkboxes.forEach(cb => {
            if (!cb.checked) {
                cb.checked = true;
                changed = true;
            }
        });
        
        if (changed) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Marked all as present',
                showConfirmButton: false,
                timer: 1500
            });
        }
    }

    async function saveAttendance() {
        const updates = [];
        const checkboxes = document.querySelectorAll('input[type="checkbox"]');
        
        checkboxes.forEach(cb => {
            updates.push({
                student_id: cb.dataset.student,
                date: cb.dataset.date,
                present: cb.checked
            });
        });

        // Show loading
        Swal.fire({
            title: 'Saving...',
            text: 'Please wait while we update attendance records.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        try {
            const res = await fetch('../../api/attendance/save_weekly.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({updates})
            });
            const result = await res.json();
            
            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: 'Attendance records have been updated successfully.',
                    confirmButtonColor: '#3b82f6',
                    timer: 2000
                });
                loadAttendance(); 
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.error || 'Failed to save attendance.',
                    confirmButtonColor: '#3b82f6'
                });
            }
        } catch (e) {
            console.error(e);
            Swal.fire({
                icon: 'error',
                title: 'Network Error',
                text: 'Could not connect to the server.',
                confirmButtonColor: '#3b82f6'
            });
        }
    }

    // Initial Load
    document.addEventListener('DOMContentLoaded', loadAttendance);
</script>

<?php require_once '../includes/footer.php'; ?>
