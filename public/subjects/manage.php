<?php
require_once '../../api/common/auth.php';
checkAdmin();
$current_page = 'subjects';
require_once '../includes/header.php';
?>
<div class="space-y-6 max-w-6xl mx-auto">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-600 dark:from-blue-400 dark:to-purple-400">
                Subject Management
            </h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">Regulation-based subjects with common first-year rules</p>
        </div>
        <button id="addBtn" class="px-4 py-2 rounded-full bg-blue-600 hover:bg-blue-700 text-white text-sm transition transform hover:scale-105">
            Add Subject
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <input id="search" type="text" placeholder="Search subjects..." class="bg-white dark:bg-gray-800 border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
        <select id="dept" class="bg-white dark:bg-gray-800 border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            <option value="">All Departments</option>
            <option value="1">DCSE</option>
            <option value="2">DECE</option>
            <option value="3">DEEE</option>
            <option value="4">DME</option>
        </select>
        <select id="year" class="bg-white dark:bg-gray-800 border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            <option value="1">Year 1</option>
            <option value="2">Year 2</option>
            <option value="3">Year 3</option>
        </select>
    </div>

    <div class="rounded-xl border bg-white dark:bg-gray-900 p-2 sm:p-4 overflow-x-auto">
        <table class="min-w-full text-xs sm:text-sm">
            <thead>
                <tr class="text-left text-gray-600 dark:text-gray-300">
                    <th class="py-2 px-2 sm:px-3">Subject</th>
                    <th class="py-2 px-2 sm:px-3 hidden sm:table-cell">Department</th>
                    <th class="py-2 px-2 sm:px-3">Year</th>
                    <th class="py-2 px-2 sm:px-3 hidden md:table-cell">Common</th>
                    <th class="py-2 px-2 sm:px-3 hidden md:table-cell">Regulation</th>
                    <th class="py-2 px-2 sm:px-3">Actions</th>
                </tr>
            </thead>
            <tbody id="tbody"></tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="modal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-900 rounded-xl p-6 w-[95%] sm:w-[480px] shadow-lg">
        <h2 class="text-lg font-bold mb-3">Add Subject</h2>
        <div class="space-y-3">
            <input id="m_name" type="text" placeholder="Subject name" class="w-full bg-white dark:bg-gray-800 border rounded-lg px-3 py-2 text-sm">
            <select id="m_is_common" class="w-full bg-white dark:bg-gray-800 border rounded-lg px-3 py-2 text-sm">
                <option value="1">Common (Year 1)</option>
                <option value="0">Department-specific</option>
            </select>
            <select id="m_dept" class="w-full bg-white dark:bg-gray-800 border rounded-lg px-3 py-2 text-sm">
                <option value="">Select Department (if specific)</option>
                <option value="1">DCSE</option>
                <option value="2">DECE</option>
                <option value="3">DEEE</option>
                <option value="4">DME</option>
            </select>
            <select id="m_year" class="w-full bg-white dark:bg-gray-800 border rounded-lg px-3 py-2 text-sm">
                <option value="1">Year 1</option>
                <option value="2">Year 2</option>
                <option value="3">Year 3</option>
            </select>
            <input id="m_reg" type="number" min="2020" max="2100" value="<?php echo date('Y'); ?>" class="w-full bg-white dark:bg-gray-800 border rounded-lg px-3 py-2 text-sm">
        </div>
        <div class="flex gap-2 mt-4">
            <button id="saveBtn" class="flex-1 px-4 py-2 rounded-full bg-green-600 hover:bg-green-700 text-white text-sm transition">Save</button>
            <button id="cancelBtn" class="flex-1 px-4 py-2 rounded-full bg-gray-300 hover:bg-gray-400 text-gray-900 text-sm transition">Cancel</button>
        </div>
    </div>
 </div>

<script>
const animeScript = document.createElement('script');
animeScript.src = 'https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js';
document.head.appendChild(animeScript);

const tbody = document.getElementById('tbody');
const search = document.getElementById('search');
const dept = document.getElementById('dept');
const year = document.getElementById('year');
const modal = document.getElementById('modal');
const addBtn = document.getElementById('addBtn');
const cancelBtn = document.getElementById('cancelBtn');
const saveBtn = document.getElementById('saveBtn');
const m_name = document.getElementById('m_name');
const m_is_common = document.getElementById('m_is_common');
const m_dept = document.getElementById('m_dept');
const m_year = document.getElementById('m_year');
const m_reg = document.getElementById('m_reg');

function rowHtml(r) {
    const dname = r.is_common == 1 ? 'All' : (r.department == 1 ? 'DCSE' : r.department == 2 ? 'DECE' : r.department == 3 ? 'DEEE' : r.department == 4 ? 'DME' : '—');
    return `
        <tr class="border-t border-gray-200 dark:border-gray-800">
            <td class="py-2 px-2 sm:px-3 whitespace-nowrap">${escapeHtml(r.subject_name)}</td>
            <td class="py-2 px-2 sm:px-3 hidden sm:table-cell">${dname}</td>
            <td class="py-2 px-2 sm:px-3">Year ${r.year}</td>
            <td class="py-2 px-2 sm:px-3 hidden md:table-cell">${r.is_common == 1 ? 'Yes' : 'No'}</td>
            <td class="py-2 px-2 sm:px-3 hidden md:table-cell">${r.regulation_year}</td>
            <td class="py-2 px-2 sm:px-3">
                <button data-id="${r.id}" class="delBtn px-2 py-1 rounded bg-red-600 hover:bg-red-700 text-white text-[11px] sm:text-xs">Delete</button>
            </td>
        </tr>
    `;
}

function escapeHtml(t) { const d = document.createElement('div'); d.textContent = t; return d.innerHTML; }

async function load() {
    try {
        const params = new URLSearchParams();
        const yv = parseInt(year.value, 10);
        params.set('year', yv);
        if (dept.value) params.set('dept_id', parseInt(dept.value, 10));
        if (search.value.trim() !== '') params.set('q', search.value.trim());
        const res = await fetch(`../../api/subjects/read.php?${params.toString()}`);
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        const rows = Array.isArray(data?.data) ? data.data : [];
        if (rows.length === 0) {
            tbody.innerHTML = `
            <tr>
                <td colspan="6" class="py-4 px-3 text-center text-gray-500 dark:text-gray-400 text-xs sm:text-sm">
                    No subjects found for the selected filters.
                </td>
            </tr>`;
        } else {
            tbody.innerHTML = rows.map(rowHtml).join('');
        }
        if (window.anime) {
            anime({
                targets: '#tbody tr',
                opacity: [0,1],
                translateY: [-6,0],
                delay: anime.stagger(30),
                duration: 200,
                easing: 'easeOutQuad'
            });
        }
        bindDelete();
    } catch (e) {
        console.error(e);
        if (window.Swal) Swal.fire('Error', 'Failed to load subjects. Please try again.', 'error');
        tbody.innerHTML = `
        <tr>
            <td colspan="6" class="py-4 px-3 text-center text-gray-500 dark:text-gray-400 text-xs sm:text-sm">
                Unable to load subjects.
            </td>
        </tr>`;
    }
}

function bindDelete() {
    document.querySelectorAll('.delBtn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = parseInt(btn.getAttribute('data-id'), 10);
            if (window.Swal) {
                const ok = await Swal.fire({
                    title:'Delete subject?',
                    text:'This action cannot be undone.',
                    icon:'warning',
                    showCancelButton:true,
                    confirmButtonText:'Delete',
                    confirmButtonColor:'#d33'
                }).then(r => r.isConfirmed);
                if (!ok) return;
            }
            try {
                const res = await fetch('../../api/subjects/delete.php', {
                    method:'POST',
                    headers:{'Content-Type':'application/json'},
                    body: JSON.stringify({ id })
                });
                const j = await res.json().catch(()=> ({}));
                if (!res.ok || !j?.success) throw new Error(j?.error || `HTTP ${res.status}`);
                if (window.Swal) Swal.fire('Deleted', 'Subject removed successfully.', 'success');
                load();
            } catch (err) {
                console.error(err);
                if (window.Swal) Swal.fire('Error', 'Failed to delete subject.', 'error');
            }
        });
    });
}

function showModal() {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    if (window.anime) {
        anime({ targets: '#modal .rounded-xl', opacity:[0,1], translateY:[10,0], duration:200, easing:'easeOutQuad' });
    }
}
function hideModal() {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

addBtn.addEventListener('click', showModal);
cancelBtn.addEventListener('click', hideModal);

saveBtn.addEventListener('click', async () => {
    const payload = {
        subject_name: m_name.value.trim(),
        is_common: parseInt(m_is_common.value, 10),
        department: m_is_common.value === '1' ? null : (m_dept.value ? parseInt(m_dept.value, 10) : null),
        year: parseInt(m_year.value, 10),
        regulation_year: parseInt(m_reg.value, 10)
    };
    try {
        const res = await fetch('../../api/subjects/create.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
        const data = await res.json().catch(()=> ({}));
        if (!res.ok || !data?.success) throw new Error(data?.error || `HTTP ${res.status}`);
        hideModal();
        load();
        if (window.Swal) {
            const msg = data?.duplicate ? 'Subject already exists for the selected year/department.' : 'Subject added successfully';
            Swal.fire('Success', msg, 'success');
        }
        if (window.anime) {
            anime({ targets:'#addBtn', scale:[1,1.08,1], duration:180, easing:'easeInOutSine' });
        }
    } catch (e) {
        if (window.Swal) Swal.fire('Error', e?.message || 'Failed to add subject', 'error');
    }
});

search.addEventListener('input', () => load());
dept.addEventListener('change', () => load());
year.addEventListener('change', () => {
    if (year.value !== '1' && dept.value === '') {
        dept.value = '1';
    }
    load();
});

document.addEventListener('DOMContentLoaded', load);
</script>
<?php require_once '../includes/footer.php'; ?>
