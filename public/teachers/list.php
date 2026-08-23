<?php
require_once '../../api/common/auth.php';
checkAdmin();
$current_page = 'teachers';
require_once '../includes/header.php';
?>

<!-- Header & Actions -->
<div class="flex flex-col md:flex-row justify-between items-center gap-4">
    <div>
        <h2 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-500 to-pink-500">Teachers</h2>
        <p class="text-gray-500 dark:text-gray-400">Manage faculty members and assignments</p>
    </div>
    <button onclick="openModal('add')" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg shadow-lg transition transform hover:scale-105 flex items-center gap-2">
        <span>+</span> Add Teacher
    </button>
</div>

<!-- Search & Filters -->
<div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-md flex flex-wrap gap-4 items-center">
    <div class="flex-1 min-w-[200px]">
        <input type="text" id="searchInput" placeholder="Search by Name or Email..." class="w-full px-4 py-2 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-purple-500 dark:text-white transition-all">
    </div>
    <select id="deptFilter" class="px-4 py-2 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
        <option value="">All Departments</option>
        <!-- Populated by JS -->
    </select>
</div>

<!-- Data Table -->
<div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl shadow-lg">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 uppercase text-xs font-semibold tracking-wider">
                <th class="py-4 px-6 rounded-tl-xl">Name</th>
                <th class="py-4 px-6">Email</th>
                <th class="py-4 px-6">Department</th>
                <th class="py-4 px-6">Joined Date</th>
                <th class="py-4 px-6 text-center rounded-tr-xl">Actions</th>
            </tr>
        </thead>
        <tbody id="teachersTable" class="text-gray-600 dark:text-gray-300 text-sm font-medium divide-y divide-gray-100 dark:divide-gray-700">
            <!-- Rows populated by JS -->
        </tbody>
    </table>
</div>

<!-- Modal -->
<div id="teacherModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 transition-opacity duration-300">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8 w-full max-w-lg transform transition-all scale-100 border border-gray-100 dark:border-gray-700">
        <h3 id="modalTitle" class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Add Teacher</h3>
        <form id="teacherForm" class="space-y-4">
            <input type="hidden" id="teacherId">
            <div>
                <label class="block text-sm font-medium mb-1 dark:text-gray-300">Name</label>
                <input type="text" id="name" class="w-full px-4 py-2 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-purple-500 dark:text-white" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1 dark:text-gray-300">Email</label>
                <input type="email" id="email" class="w-full px-4 py-2 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-purple-500 dark:text-white" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1 dark:text-gray-300">Department</label>
                <select id="deptId" class="w-full px-4 py-2 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-purple-500 dark:text-white" required>
                    <!-- Populated by JS -->
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1 dark:text-gray-300">Password <span id="passwordHint" class="text-xs text-gray-400 font-normal hidden">(Leave blank to keep current)</span></label>
                <input type="password" id="password" class="w-full px-4 py-2 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-purple-500 dark:text-white">
            </div>
            
            <div class="flex justify-end space-x-4 mt-8">
                <button type="button" onclick="closeModal()" class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Cancel</button>
                <button type="submit" class="px-6 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-bold shadow-lg shadow-purple-500/30 transition-all transform hover:-translate-y-0.5">Save Teacher</button>
            </div>
        </form>
    </div>
</div>

<script>
    let teachers = [];
    let departments = [];

    async function init() {
        await fetchDepartments();
        await fetchTeachers();
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

    async function fetchTeachers() {
        try {
            const res = await fetch('../../api/teachers/read.php');
            teachers = await res.json();
            renderTable(teachers);
        } catch (e) { console.error(e); Swal.fire('Error', 'Failed to load teachers', 'error'); }
    }

    function renderTable(data) {
        const tbody = document.getElementById('teachersTable');
        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="py-8 text-center text-gray-500 dark:text-gray-400">No teachers found</td></tr>';
            return;
        }
        tbody.innerHTML = data.map(t => `
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                <td class="py-4 px-6 font-medium text-gray-900 dark:text-white">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-xs">
                            ${t.name.charAt(0)}
                        </div>
                        ${t.name}
                    </div>
                </td>
                <td class="py-4 px-6 text-gray-500 dark:text-gray-400">${t.email}</td>
                <td class="py-4 px-6 text-gray-500 dark:text-gray-400">${t.dept_name || '-'}</td>
                <td class="py-4 px-6 text-gray-500 dark:text-gray-400">${new Date(t.created_at).toLocaleDateString()}</td>
                <td class="py-4 px-6 text-center">
                    <div class="flex item-center justify-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button onclick="editTeacher(${t.id})" class="p-2 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 text-blue-600 transition-colors" title="Edit">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </button>
                        <button onclick="deleteTeacher(${t.id})" class="p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600 transition-colors" title="Delete">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function setupFilters() {
        const search = document.getElementById('searchInput');
        const dept = document.getElementById('deptFilter');

        const filterFn = () => {
            const term = search.value.toLowerCase();
            const d = dept.value;

            const filtered = teachers.filter(t => 
                (t.name.toLowerCase().includes(term) || t.email.toLowerCase().includes(term)) &&
                (d === '' || t.dept_id == d)
            );
            renderTable(filtered);
        };

        search.addEventListener('input', filterFn);
        dept.addEventListener('change', filterFn);
    }

    // Modal Logic
    const modal = document.getElementById('teacherModal');
    const form = document.getElementById('teacherForm');

    function openModal(mode, id = null) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        // Small delay for animation
        setTimeout(() => modal.firstElementChild.classList.remove('scale-95', 'opacity-0'), 10);
        
        const passwordHint = document.getElementById('passwordHint');
        const passwordInput = document.getElementById('password');
        
        if (mode === 'edit' && id) {
            const t = teachers.find(x => x.id === id);
            document.getElementById('modalTitle').textContent = 'Edit Teacher';
            document.getElementById('teacherId').value = t.id;
            document.getElementById('name').value = t.name;
            document.getElementById('email').value = t.email;
            document.getElementById('deptId').value = t.dept_id;
            passwordInput.required = false;
            passwordHint.classList.remove('hidden');
        } else {
            document.getElementById('modalTitle').textContent = 'Add Teacher';
            form.reset();
            document.getElementById('teacherId').value = '';
            passwordInput.required = true;
            passwordHint.classList.add('hidden');
        }
    }

    function closeModal() {
        modal.firstElementChild.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 200);
    }

    window.editTeacher = (id) => openModal('edit', id);

    window.deleteTeacher = async (id) => {
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
                const res = await fetch('../../api/teachers/delete.php', {
                    method: 'POST',
                    body: JSON.stringify({ id })
                });
                const data = await res.json();
                if (data.success) {
                    Swal.fire('Deleted!', 'Teacher has been deleted.', 'success');
                    fetchTeachers();
                } else {
                    Swal.fire('Error', data.error, 'error');
                }
            } catch (e) { Swal.fire('Error', 'Network error', 'error'); }
        }
    };

    form.onsubmit = async (e) => {
        e.preventDefault();
        const id = document.getElementById('teacherId').value;
        const payload = {
            id: id || undefined,
            name: document.getElementById('name').value,
            email: document.getElementById('email').value,
            dept_id: document.getElementById('deptId').value,
            password: document.getElementById('password').value
        };

        const url = id ? '../../api/teachers/update.php' : '../../api/teachers/create.php';
        
        try {
            const res = await fetch(url, {
                method: 'POST',
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                closeModal();
                Swal.fire('Success', 'Teacher saved successfully', 'success');
                fetchTeachers();
            } else {
                Swal.fire('Error', data.error || 'Operation failed', 'error');
            }
        } catch (e) { Swal.fire('Error', 'Network error', 'error'); }
    };

    init();
</script>

<?php require_once '../includes/footer.php'; ?>
