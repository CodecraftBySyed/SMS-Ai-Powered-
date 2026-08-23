<?php
require_once '../../api/common/auth.php';
checkAdmin();
$current_page = 'students';
require_once '../includes/header.php';
?>

<!-- Header & Actions -->
<div class="flex flex-col md:flex-row justify-between items-center gap-4">
    <div>
        <h2 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-500 to-purple-500">Students</h2>
        <p class="text-gray-500 dark:text-gray-400">Manage student records and details</p>
    </div>
    <button onclick="openModal('add')" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg shadow-lg transition transform hover:scale-105 flex items-center gap-2">
        <span>+</span> Add Student
    </button>
</div>

<!-- Search & Filters -->
<div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-md flex flex-wrap gap-4 items-center">
    <div class="flex-1 min-w-[200px]">
        <input type="text" id="searchInput" placeholder="Search by Name or RegNo..." class="w-full px-4 py-2 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white transition-all">
    </div>
    <select id="deptFilter" class="px-4 py-2 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">All Departments</option>
        <!-- Populated by JS -->
    </select>
    <select id="yearFilter" class="px-4 py-2 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">All Years</option>
        <option value="1">1st Year</option>
        <option value="2">2nd Year</option>
        <option value="3">3rd Year</option>
    </select>
</div>

<!-- Data Table -->
<div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl shadow-lg">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 uppercase text-xs font-semibold tracking-wider">
                <th class="py-4 px-6 rounded-tl-xl">Reg No</th>
                <th class="py-4 px-6">Name</th>
                <th class="py-4 px-6">Department</th>
                <th class="py-4 px-6">Year</th>
                <th class="py-4 px-6">Balance</th>
                <th class="py-4 px-6">Status</th>
                <th class="py-4 px-6 text-center rounded-tr-xl">Actions</th>
            </tr>
        </thead>
        <tbody id="studentsTable" class="text-gray-600 dark:text-gray-300 text-sm font-medium divide-y divide-gray-100 dark:divide-gray-700">
            <!-- Rows populated by JS -->
        </tbody>
    </table>
</div>

<!-- Modal -->
<div id="studentModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 transition-opacity duration-300">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8 w-full max-w-lg transform transition-all scale-100 border border-gray-100 dark:border-gray-700">
        <h3 id="modalTitle" class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Add Student</h3>
        <form id="studentForm" class="space-y-4">
            <input type="hidden" id="studentId">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Reg No</label>
                    <input type="text" id="regNo" class="w-full px-4 py-2 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-blue-500 dark:text-white" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Name</label>
                    <input type="text" id="name" class="w-full px-4 py-2 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-blue-500 dark:text-white" required>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Department</label>
                    <select id="deptId" class="w-full px-4 py-2 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-blue-500 dark:text-white" required>
                        <!-- Populated by JS -->
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Year</label>
                    <select id="year" class="w-full px-4 py-2 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-blue-500 dark:text-white">
                        <option value="1">1st Year</option>
                        <option value="2">2nd Year</option>
                        <option value="3">3rd Year</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Parent Name</label>
                    <input type="text" id="parentName" class="w-full px-4 py-2 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-blue-500 dark:text-white" placeholder="Father or Mother name">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Parent Mobile</label>
                    <input type="tel" id="parentMobile" class="w-full px-4 py-2 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-blue-500 dark:text-white" placeholder="10 digits, +91…, or 0…">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                 <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Phone</label>
                    <input type="text" id="phone" class="w-full px-4 py-2 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-blue-500 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Email</label>
                    <input type="email" id="email" class="w-full px-4 py-2 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-blue-500 dark:text-white">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Total Fee</label>
                    <input type="number" id="totalFee" class="w-full px-4 py-2 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-blue-500 dark:text-white" value="50000">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Paid Fee</label>
                    <input type="number" id="paidFee" class="w-full px-4 py-2 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-blue-500 dark:text-white" value="0">
                </div>
            </div>
            <div class="flex justify-end space-x-4 mt-8">
                <button type="button" onclick="closeModal()" class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Cancel</button>
                <button type="submit" class="px-6 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-lg shadow-blue-500/30 transition-all transform hover:-translate-y-0.5">Save Student</button>
            </div>
        </form>
    </div>
</div>

<script>
    let students = [];
    let departments = [];

    async function init() {
        await fetchDepartments();
        await fetchStudents();
        setupFilters();
    }

    async function fetchDepartments() {
        try {
            const res = await fetch('../../api/common/departments.php');
            departments = await res.json();
            const deptSelects = [document.getElementById('deptFilter'), document.getElementById('deptId')];
            
            deptSelects.forEach(select => {
                const isFilter = select.id === 'deptFilter';
                select.innerHTML = (isFilter ? '<option value="">All Departments</option>' : '') + 
                    departments.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
            });
        } catch (e) { console.error(e); Swal.fire('Error', 'Failed to load departments', 'error'); }
    }

    async function fetchStudents() {
        try {
            const res = await fetch('../../api/students/read.php');
            students = await res.json();
            renderTable(students);
        } catch (e) { console.error(e); Swal.fire('Error', 'Failed to load students', 'error'); }
    }

    function renderTable(data) {
        const tbody = document.getElementById('studentsTable');
        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="py-8 text-center text-gray-500 dark:text-gray-400">No students found</td></tr>';
            return;
        }
        tbody.innerHTML = data.map(s => `
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                <td class="py-4 px-6 font-medium text-gray-900 dark:text-white">${s.reg_no}</td>
                <td class="py-4 px-6">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs">
                            ${s.name.charAt(0)}
                        </div>
                        ${s.name}
                    </div>
                </td>
                <td class="py-4 px-6 text-gray-500 dark:text-gray-400">${s.dept_name || '-'}</td>
                <td class="py-4 px-6">
                    <span class="px-2 py-1 rounded-md bg-gray-100 dark:bg-gray-700 text-xs font-medium">Year ${s.year}</span>
                </td>
                <td class="py-4 px-6 font-bold ${s.balance_fee > 0 ? 'text-red-500' : 'text-green-500'}">
                    ₹${Number(s.balance_fee).toLocaleString()}
                </td>
                <td class="py-4 px-6">
                    <span class="px-3 py-1 rounded-full text-xs font-medium ${getStatusColor(s.status)}">
                        ${s.status}
                    </span>
                </td>
                <td class="py-4 px-6 text-center">
                    <div class="flex item-center justify-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button onclick="editStudent(${s.id})" class="p-2 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 text-blue-600 transition-colors" title="Edit">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </button>
                        <button onclick="deleteStudent(${s.id})" class="p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600 transition-colors" title="Delete">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function getStatusColor(status) {
        switch(status) {
            case 'Good': return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
            case 'Average': return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400';
            case 'Needs Attention': return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
            default: return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
        }
    }

    function setupFilters() {
        const search = document.getElementById('searchInput');
        const dept = document.getElementById('deptFilter');
        const year = document.getElementById('yearFilter');

        const filterFn = () => {
            const term = search.value.toLowerCase();
            const d = dept.value;
            const y = year.value;

            const filtered = students.filter(s => 
                (s.name.toLowerCase().includes(term) || s.reg_no.toLowerCase().includes(term)) &&
                (d === '' || s.dept_id == d) &&
                (y === '' || s.year == y)
            );
            renderTable(filtered);
        };

        search.addEventListener('input', filterFn);
        dept.addEventListener('change', filterFn);
        year.addEventListener('change', filterFn);
    }

    // Modal Logic
    const modal = document.getElementById('studentModal');
    const form = document.getElementById('studentForm');

    function openModal(mode, id = null) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        // Small delay for animation
        setTimeout(() => modal.firstElementChild.classList.remove('scale-95', 'opacity-0'), 10);
        
        if (mode === 'edit' && id) {
            const s = students.find(x => x.id === id);
            document.getElementById('modalTitle').textContent = 'Edit Student';
            document.getElementById('studentId').value = s.id;
            document.getElementById('regNo').value = s.reg_no;
            document.getElementById('name').value = s.name;
            document.getElementById('deptId').value = s.dept_id;
            document.getElementById('year').value = s.year;
            document.getElementById('parentName').value = s.parent_name || '';
            document.getElementById('parentMobile').value = s.parent_mobile || '';
            document.getElementById('phone').value = s.phone;
            document.getElementById('email').value = s.email;
            document.getElementById('totalFee').value = s.total_fee;
            document.getElementById('paidFee').value = s.paid_fee;
        } else {
            document.getElementById('modalTitle').textContent = 'Add Student';
            form.reset();
            document.getElementById('studentId').value = '';
        }
    }

    function closeModal() {
        modal.firstElementChild.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 200);
    }

    window.editStudent = (id) => openModal('edit', id);

    window.deleteStudent = async (id) => {
        const result = await Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        });

        if (result.isConfirmed) {
            try {
                const res = await fetch('../../api/students/delete.php', {
                    method: 'POST',
                    body: JSON.stringify({ id })
                });
                const data = await res.json();
                if (data.success) {
                    Swal.fire('Deleted!', 'Student has been deleted.', 'success');
                    fetchStudents();
                } else {
                    Swal.fire('Error', data.error, 'error');
                }
            } catch (e) { Swal.fire('Error', 'Network error', 'error'); }
        }
    };

    form.onsubmit = async (e) => {
        e.preventDefault();
        const id = document.getElementById('studentId').value;
        let parentMobile = document.getElementById('parentMobile').value.trim();
        if (parentMobile) {
            let digits = parentMobile.replace(/\\D/g, '');
            if (digits.startsWith('91') && digits.length === 12) digits = digits.slice(-10);
            if (digits.startsWith('0') && digits.length === 11) digits = digits.slice(-10);
            if (digits.length !== 10) {
                Swal.fire('Error', 'Parent mobile must be a valid 10-digit number', 'error');
                return;
            }
            parentMobile = digits;
        }
        const payload = {
            id: id || undefined,
            reg_no: document.getElementById('regNo').value,
            name: document.getElementById('name').value,
            dept_id: document.getElementById('deptId').value,
            year: document.getElementById('year').value,
            parent_name: document.getElementById('parentName').value,
            parent_mobile: parentMobile || '',
            phone: document.getElementById('phone').value,
            email: document.getElementById('email').value,
            total_fee: document.getElementById('totalFee').value,
            paid_fee: document.getElementById('paidFee').value
        };

        const url = id ? '../../api/students/update.php' : '../../api/students/create.php';
        
        try {
            const res = await fetch(url, {
                method: 'POST',
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                closeModal();
                Swal.fire('Success', 'Student saved successfully', 'success');
                fetchStudents();
            } else {
                Swal.fire('Error', data.error || 'Operation failed', 'error');
            }
        } catch (e) { Swal.fire('Error', 'Network error', 'error'); }
    };

    init();
</script>

<?php require_once '../includes/footer.php'; ?>
