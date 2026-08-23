<?php
require_once '../../api/common/auth.php';
checkTeacher();
$current_page = 'fees';
require_once '../includes/header.php';
?>

<!-- Header -->
<div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
    <div>
        <h2 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-500 to-purple-500">Fee Management</h2>
        <p class="text-gray-500 dark:text-gray-400">Track and update student fee payments</p>
    </div>
    <div>
        <button onclick="loadStudents()" class="bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-lg transition flex items-center gap-2">
            <i class="fas fa-sync-alt"></i> Refresh List
        </button>
    </div>
</div>

<!-- Fees Table -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-b dark:border-gray-600">
                    <th class="p-4 font-semibold">Student</th>
                    <th class="p-4 font-semibold text-right">Total Fee</th>
                    <th class="p-4 font-semibold text-right">Paid Fee</th>
                    <th class="p-4 font-semibold text-right">Balance</th>
                    <th class="p-4 font-semibold text-center">Status</th>
                    <th class="p-4 font-semibold text-center">Action</th>
                </tr>
            </thead>
            <tbody id="studentList" class="divide-y divide-gray-200 dark:divide-gray-700">
                <tr>
                    <td colspan="6" class="p-8 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-spinner fa-spin text-3xl mb-3 text-blue-500"></i>
                            <p>Loading fee records...</p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    async function loadStudents() {
        const tbody = document.getElementById('studentList');
        
        // Show loading state if re-loading
        if (tbody.children.length > 1) { // Only if table is populated
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="p-8 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-spinner fa-spin text-3xl mb-3 text-blue-500"></i>
                            <p>Loading fee records...</p>
                        </div>
                    </td>
                </tr>
            `;
        }

        try {
            const res = await fetch('../../api/fees/read.php');
            const students = await res.json();
            
            if (students.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-500">No students found.</td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = students.map((student, idx) => {
                const total = parseFloat(student.total_fee);
                const paid = parseFloat(student.paid_fee);
                const balance = parseFloat(student.balance_fee);
                const percent = total > 0 ? (paid / total) * 100 : 0;
                
                // Determine status badge
                let statusBadge = '';
                if (balance <= 0) {
                    statusBadge = '<span class="px-2 py-1 rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-xs font-bold">Paid</span>';
                } else if (paid > 0) {
                    statusBadge = '<span class="px-2 py-1 rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 text-xs font-bold">Partial</span>';
                } else {
                    statusBadge = '<span class="px-2 py-1 rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 text-xs font-bold">Unpaid</span>';
                }

                return `
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900 flex items-center justify-center text-purple-600 dark:text-purple-300 font-bold text-xs">
                                    ${student.name.charAt(0)}
                                </div>
                                <div>
                                    <div class="font-bold text-gray-800 dark:text-gray-200">${student.name}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">${student.reg_no}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-4 text-right font-mono text-gray-600 dark:text-gray-300">₹${total.toFixed(2)}</td>
                        <td class="p-4 text-right font-mono text-green-600 dark:text-green-400 font-bold">₹${paid.toFixed(2)}</td>
                        <td class="p-4 text-right font-mono text-red-500 dark:text-red-400 font-bold">₹${balance.toFixed(2)}</td>
                        <td class="p-4 text-center">${statusBadge}</td>
                        <td class="p-4 text-center">
                            <button onclick="openUpdateModal(${student.id}, '${student.name.replace(/'/g, "\\'")}', ${paid}, ${total})" 
                                class="bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-900/50 px-3 py-1 rounded-lg text-sm transition flex items-center gap-1 mx-auto">
                                <i class="fas fa-edit"></i> Update
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        } catch (e) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load fee records.',
                confirmButtonColor: '#3b82f6'
            });
        }
    }

    async function openUpdateModal(id, name, currentPaid, total) {
        const { value: paidAmount } = await Swal.fire({
            title: 'Update Fee Payment',
            html: `
                <div class="text-left mb-4">
                    <p class="text-sm text-gray-500">Student: <strong>${name}</strong></p>
                    <p class="text-sm text-gray-500">Total Fee: <strong>₹${total.toFixed(2)}</strong></p>
                </div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 text-left">Total Paid Amount</label>
                <input id="swal-input-paid" type="number" class="swal2-input" placeholder="Enter total paid amount" value="${currentPaid}" step="0.01" min="0">
            `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Update Record',
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: '#ef4444',
            preConfirm: () => {
                const val = document.getElementById('swal-input-paid').value;
                if (!val) {
                    Swal.showValidationMessage('Please enter an amount');
                }
                return val;
            }
        });

        if (paidAmount) {
            await saveFee(id, paidAmount);
        }
    }

    async function saveFee(studentId, paidAmount) {
        // Show loading
        Swal.fire({
            title: 'Updating...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        try {
            const res = await fetch('../../api/fees/update.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    student_id: studentId,
                    paid_fee: paidAmount
                })
            });
            const result = await res.json();
            
            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Updated!',
                    text: 'Fee record has been updated.',
                    confirmButtonColor: '#3b82f6',
                    timer: 1500
                });
                loadStudents();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.error || 'Failed to update fee record.',
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

    // Initial Load
    document.addEventListener('DOMContentLoaded', loadStudents);
</script>

<?php require_once '../includes/footer.php'; ?>
