<?php
require_once '../../api/common/auth.php';
checkTeacher();
$current_page = 'marks';
require_once '../includes/header.php';
?>

<!-- Header -->
<div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
    <div>
        <h2 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-500 to-purple-500">Marks Entry</h2>
        <p class="text-gray-500 dark:text-gray-400">Record student examination marks</p>
    </div>
    <div>
        <button onclick="saveMarks()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg shadow-lg transition transform hover:scale-105 flex items-center gap-2">
            <i class="fas fa-save"></i> Save Marks
        </button>
    </div>
</div>

<!-- Controls -->
<div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md mb-6">
    <div class="flex flex-col md:flex-row gap-6">
        <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Year</label>
            <div class="relative">
                <select id="yearSelect" class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all" onchange="loadSubjects()">
                    <option value="1">Year 1</option>
                    <option value="2">Year 2</option>
                    <option value="3">Year 3</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                    <i class="fas fa-chevron-down text-xs"></i>
                </div>
            </div>
        </div>
        <div class="flex-[2]">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Subject</label>
            <div class="relative">
                <select id="subjectSelect" class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all" onchange="loadStudents()">
                    <option value="">Loading...</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                    <i class="fas fa-chevron-down text-xs"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Marks Table -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-b dark:border-gray-600">
                    <th class="p-4 font-semibold">Student</th>
                    <th class="p-4 font-semibold">Reg No</th>
                    <th class="p-4 font-semibold text-center">Marks (0-100)</th>
                </tr>
            </thead>
            <tbody id="studentList" class="divide-y divide-gray-200 dark:divide-gray-700">
                <tr><td colspan="3" class="p-8 text-center text-gray-500">Select Year and Subject to view students</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    async function loadSubjects() {
        const year = document.getElementById('yearSelect').value;
        const subjectSelect = document.getElementById('subjectSelect');
        
        // Disable and show loading
        subjectSelect.disabled = true;
        subjectSelect.innerHTML = '<option>Loading subjects...</option>';
        
        try {
            const res = await fetch(`../../api/marks/get_subjects.php?year=${year}`);
            const subjects = await res.json();
            
            subjectSelect.disabled = false;
            
            if (subjects.length === 0) {
                subjectSelect.innerHTML = '<option value="">No Subjects Found</option>';
                document.getElementById('studentList').innerHTML = `
                    <tr><td colspan="3" class="p-8 text-center text-gray-500">No subjects found for Year ${year}</td></tr>
                `;
                return;
            }

            subjectSelect.innerHTML = subjects.map(s => `<option value="${s.id}">${s.code} - ${s.name}</option>`).join('');
            loadStudents();
        } catch (e) {
            subjectSelect.disabled = false;
            subjectSelect.innerHTML = '<option value="">Error loading subjects</option>';
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load subjects.',
                confirmButtonColor: '#3b82f6'
            });
        }
    }

    async function loadStudents() {
        const year = document.getElementById('yearSelect').value;
        const subjectId = document.getElementById('subjectSelect').value;
        
        if (!subjectId) return;

        const tbody = document.getElementById('studentList');
        tbody.innerHTML = `
            <tr>
                <td colspan="3" class="p-8 text-center text-gray-500">
                    <div class="flex flex-col items-center justify-center">
                        <i class="fas fa-spinner fa-spin text-3xl mb-3 text-blue-500"></i>
                        <p>Loading students...</p>
                    </div>
                </td>
            </tr>
        `;

        try {
            const res = await fetch(`../../api/marks/get_student_marks.php?year=${year}&subject_id=${subjectId}`);
            const students = await res.json();
            
            if (!Array.isArray(students)) {
                const message = students && students.error ? students.error : 'Unexpected server response.';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message,
                    confirmButtonColor: '#3b82f6'
                });
                tbody.innerHTML = '<tr><td colspan="3" class="p-8 text-center text-gray-500">Could not load students</td></tr>';
                return;
            }
            
            if (students.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="p-8 text-center text-gray-500">No students found for this subject</td></tr>';
                return;
            }

            tbody.innerHTML = students.map((student, idx) => `
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors animate-fade-in" style="animation-delay: ${idx * 0.05}s">
                    <td class="p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-blue-600 dark:text-blue-300 font-bold text-xs">
                                ${student.name.charAt(0)}
                            </div>
                            <div class="font-bold text-gray-800 dark:text-gray-200">${student.name}</div>
                        </div>
                    </td>
                    <td class="p-4 text-sm text-gray-500 dark:text-gray-400 font-mono">${student.reg_no}</td>
                    <td class="p-4 text-center">
                        <input type="number" min="0" max="100" 
                            class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 w-32 text-center font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all mark-input"
                            data-student="${student.id}"
                            value="${student.marks_obtained !== null ? student.marks_obtained : ''}"
                            placeholder="-"
                        >
                    </td>
                </tr>
            `).join('');
        } catch (e) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load students.',
                confirmButtonColor: '#3b82f6'
            });
        }
    }

    async function saveMarks() {
        const subjectId = document.getElementById('subjectSelect').value;
        if (!subjectId) {
            Swal.fire({
                icon: 'warning',
                title: 'No Subject Selected',
                text: 'Please select a subject first.',
                confirmButtonColor: '#3b82f6'
            });
            return;
        }

        const inputs = document.querySelectorAll('.mark-input');
        const updates = [];
        
        inputs.forEach(input => {
            if (input.value !== '') {
                updates.push({
                    student_id: input.dataset.student,
                    subject_id: subjectId,
                    marks: input.value
                });
            }
        });

        if (updates.length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'No Marks Entered',
                text: 'Please enter marks for at least one student.',
                confirmButtonColor: '#3b82f6'
            });
            return;
        }

        // Show loading
        Swal.fire({
            title: 'Saving...',
            text: 'Please wait while we save the marks.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        try {
            const res = await fetch('../../api/marks/save.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({updates})
            });
            const result = await res.json();
            
            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: 'Marks have been saved successfully.',
                    confirmButtonColor: '#3b82f6',
                    timer: 1500
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.error || 'Failed to save marks.',
                    confirmButtonColor: '#3b82f6'
                });
            }
        } catch (e) {
            Swal.fire({
                icon: 'error',
                title: 'Network Error',
                text: 'Could not connect to the server.',
                confirmButtonColor: '#3b82f6'
            });
        }
    }

    // Init
    document.addEventListener('DOMContentLoaded', loadSubjects);
</script>

<?php require_once '../includes/footer.php'; ?>
