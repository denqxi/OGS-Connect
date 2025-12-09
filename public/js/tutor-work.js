// Tutor Work JS
// Functions used by the tutor work details Blade view.
// - openWorkDetailEditor(id)
// - confirmDeleteWorkDetail(id)
document.addEventListener('change', function (e) {
    if (e.target && e.target.id === 'twd_image') {
        const fileInput = e.target;
        const file = fileInput.files[0];
        const preview = document.getElementById('twd_image_preview');

        if (file) {
            const reader = new FileReader();
            reader.onload = function (event) {
                preview.src = event.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            preview.src = '';
            preview.style.display = 'none';
        }
    }
});

(function () {
    function getCsrfToken() {
        const m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : null;
    }

    function showToast(message, type = 'info') {
        const ttl = 3500;
        let toast = document.createElement('div');
        toast.className = 'tutor-work-toast fixed bottom-6 right-6 px-4 py-2 rounded shadow-lg text-sm text-white';
        toast.style.zIndex = 9999;
        if (type === 'success') toast.style.background = '#16a34a';
        else if (type === 'error') toast.style.background = '#dc2626';
        else toast.style.background = '#334155';
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.transition = 'opacity 200ms';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 220);
        }, ttl);
    }
    // Open Payroll Detail Modal
    window.openPayrollDetailModal = function(detail, status) {
        const modal = document.getElementById('payrollWorkDetailModal');
        if (!modal) return;
        
        const tutor = detail.tutor;
        const schedule = detail.schedule;
        
        // Populate schedule information - use schedule.date and schedule.day from the relationship
        let dateObj = null;
        let dayText = 'N/A';
        
        if (schedule?.date) {
            dateObj = new Date(schedule.date);
            dayText = schedule.day || dateObj.toLocaleDateString('en-US', { weekday: 'long' });
        } else if (detail.day) {
            dateObj = new Date(detail.day);
            dayText = dateObj.toLocaleDateString('en-US', { weekday: 'long' });
        } else if (detail.created_at) {
            dateObj = new Date(detail.created_at);
            dayText = dateObj.toLocaleDateString('en-US', { weekday: 'long' });
        }
        
        document.getElementById('payroll-modal-date').textContent = dateObj ? dateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : 'N/A';
        document.getElementById('payroll-modal-day').textContent = dayText;
        document.getElementById('payroll-modal-school').textContent = schedule?.school || 'N/A';
        document.getElementById('payroll-modal-class').textContent = schedule?.class || 'N/A';
        
        // Populate tutor information
        document.getElementById('payroll-modal-name').textContent = tutor?.full_name || tutor?.username || 'N/A';
        
        // Populate time and rate
        const startTime = detail.start_time ? new Date('1970-01-01 ' + detail.start_time).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true }) : '—';
        const endTime = detail.end_time ? new Date('1970-01-01 ' + detail.end_time).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true }) : '—';
        
        document.getElementById('payroll-modal-start-time').textContent = startTime;
        document.getElementById('payroll-modal-end-time').textContent = endTime;
        
        // Calculate and display hours
        let hoursText = '—';
        if (detail.start_time && detail.end_time) {
            const start = new Date('1970-01-01 ' + detail.start_time);
            const end = new Date('1970-01-01 ' + detail.end_time);
            const diffMs = end - start;
            const diffHours = diffMs / (1000 * 60 * 60);
            hoursText = diffHours.toFixed(2) + ' hours';
        } else if (detail.duration) {
            const hours = detail.duration / 60;
            hoursText = hours.toFixed(2) + ' hours';
        }
        document.getElementById('payroll-modal-hours').textContent = hoursText;
        
        const rate = detail.rate_per_class || detail.rate_per_hour || 'N/A';
        document.getElementById('payroll-modal-rate').textContent = isNaN(rate) ? rate : '₱' + parseFloat(rate).toFixed(2);
        
        const amount = detail.computed_amount || '0';
        document.getElementById('payroll-modal-amount').textContent = '₱' + parseFloat(amount).toFixed(2);
        
        // Populate proof
        const proofContainer = document.getElementById('payroll-modal-proof-container');
        const proofPath = detail.proof_image || detail.screenshot;
        if (proofPath) {
            proofContainer.innerHTML = `<img src="/storage/${proofPath}" alt="Proof" class="w-full max-w-md h-64 object-cover rounded-lg border-2 border-gray-300 shadow-md cursor-pointer hover:opacity-90 transition-opacity" onclick="window.open('/storage/${proofPath}', '_blank')">`;
        } else {
            proofContainer.innerHTML = '<p class="text-gray-500 text-center py-8">No proof image uploaded</p>';
        }
        
        // Handle action buttons based on status
        const actionButtons = document.getElementById('payroll-modal-action-buttons');
        const detailId = detail.id || detail.work_detail_id;
        
        if (status?.toLowerCase() !== 'approved') {
            actionButtons.innerHTML = `
                <button type="button" onclick="approveWorkDetailFromModal('${detailId}');" 
                    class="px-6 py-2 bg-green-600 text-white rounded-md font-medium hover:bg-green-700 transition-colors flex items-center gap-2">
                    <i class="fas fa-check"></i> Approve
                </button>
                <button type="button" onclick="rejectWorkDetailFromModal('${detailId}');" 
                    class="px-6 py-2 bg-red-600 text-white rounded-md font-medium hover:bg-red-700 transition-colors flex items-center gap-2">
                    <i class="fas fa-times"></i> Reject
                </button>
            `;
        } else {
            actionButtons.innerHTML = '<span class="text-sm text-green-600 font-medium flex items-center gap-2"><i class="fas fa-check-circle"></i> Approved</span>';
        }
        
        modal.classList.remove('hidden');
    };
    
    window.closePayrollDetailModal = function() {
        const modal = document.getElementById('payrollWorkDetailModal');
        if (modal) modal.classList.add('hidden');
    };
    
    // Approve from modal
    window.approveWorkDetailFromModal = function(id) {
        closePayrollDetailModal();
        approveWorkDetail(id);
    };
    
    // Reject from modal
    window.rejectWorkDetailFromModal = function(id) {
        closePayrollDetailModal();
        rejectWorkDetail(id);
    };

    function approveWorkDetail(id) {
        // Create confirmation modal
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-50 flex items-center justify-center';
        modal.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
        
        const modalContent = document.createElement('div');
        modalContent.className = 'bg-white rounded-lg shadow-xl max-w-md w-full mx-4';
        
        modalContent.innerHTML = `
            <div class="p-6">
                <div class="flex items-start mb-4">
                    <div class="flex-shrink-0 bg-green-100 rounded-full p-3 mr-4">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Approve Work Detail</h3>
                        <p class="text-sm text-gray-600">Are you sure you want to approve this work detail?</p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 rounded-b-lg">
                <button id="cancelApproveBtn" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md font-medium hover:bg-gray-400 transition-colors">
                    Cancel
                </button>
                <button id="confirmApproveBtn" class="px-4 py-2 bg-green-600 text-white rounded-md font-medium hover:bg-green-700 transition-colors">
                    Approve
                </button>
            </div>
        `;
        
        modal.appendChild(modalContent);
        document.body.appendChild(modal);
        
        // Handle cancel
        const cancelBtn = modal.querySelector('#cancelApproveBtn');
        cancelBtn.onclick = () => modal.remove();
        
        // Handle backdrop click
        modal.onclick = (e) => {
            if (e.target === modal) modal.remove();
        };
        
        // Handle confirm
        const confirmBtn = modal.querySelector('#confirmApproveBtn');
        confirmBtn.onclick = () => {
            modal.remove();
            
            fetch(`/payroll/work-detail/${id}/approve`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    "Accept": "application/json"
                }
            })
            .then(res => res.json())
            .then(data => {
                showToast(data.message || 'Approved successfully', 'success');
                const container = document.getElementById('payrollWorkDetailsContainer');
                if (container) {
                    document.dispatchEvent(new CustomEvent('workDetails:reload'));
                } else {
                    setTimeout(() => location.reload(), 500);
                }
            })
            .catch(err => {
                console.error(err);
                showToast("Something went wrong", 'error');
            });
        };
    }


    function ensureModal() {
        if (document.getElementById('tutorWorkModal')) return;

        const modal = document.createElement('div');
        modal.id = 'tutorWorkModal';
        modal.className = 'fixed inset-0 z-50 flex items-center justify-center';
        modal.style.display = 'none';
        modal.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';

        modal.innerHTML = `
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-hidden flex flex-col">
                <!-- Header -->
                <div class="bg-[#0E335D] text-white px-6 py-4 flex items-center justify-between flex-shrink-0">
                    <h2 class="text-xl font-bold">Work Details</h2>
                    <button type="button" id="tutorWorkModalClose" class="text-white hover:text-gray-200 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <!-- Body -->
                <div class="overflow-y-auto flex-grow p-6">
                    <form id="tutorWorkForm">
                        <input type="hidden" name="id" id="twd_id">
                        <input type="hidden" name="assignment_id" id="twd_assignment_id">
                        <input type="hidden" name="schedule_daily_data_id" id="twd_schedule_id">
                        
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-5 mb-6 border border-blue-200 shadow-sm">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-clock text-blue-600 mr-2"></i>
                                Time Details
                            </h3>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Start Time <span class="text-red-500">*</span></label>
                                    <input type="time" id="twd_start_time" name="start_time" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">End Time <span class="text-red-500">*</span></label>
                                    <input type="time" id="twd_end_time" name="end_time" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-5 border border-green-200 shadow-sm">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-image text-green-600 mr-2"></i>
                                Proof of Work
                            </h3>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Screenshot/Image <span class="text-red-500">*</span></label>
                                <input type="file" id="twd_image" name="image" accept="image/*" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                                <img id="twd_image_preview" src="" alt="Image Preview" 
                                    class="mt-3 w-full max-w-xs h-48 object-cover rounded-lg border-2 border-gray-300 shadow-sm hidden cursor-pointer hover:opacity-90 transition-opacity">
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Footer -->
                <div class="px-6 py-4 bg-gray-50 flex justify-end gap-3 border-t flex-shrink-0">
                    <button type="button" id="twd_cancel" 
                        class="px-6 py-2 bg-gray-500 text-white rounded-md font-medium hover:bg-gray-600 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" id="twd_save" form="tutorWorkForm"
                        class="px-6 py-2 bg-blue-600 text-white rounded-md font-medium hover:bg-blue-700 transition-colors">
                        Save
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        document.getElementById('tutorWorkModalClose').addEventListener('click', hideModal);
        document.getElementById('twd_cancel').addEventListener('click', hideModal);
        document.getElementById('tutorWorkForm').addEventListener('submit', handleSave);
        
        // Close on backdrop click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) hideModal();
        });
    }

    // Expose approveWorkDetail to global scope for inline onclick handlers
    window.approveWorkDetail = approveWorkDetail;

    // Reject (mark as rejected) using a modal to collect the reason
    window.rejectWorkDetail = function (id) {
        if (!id) return showToast('Missing work detail id', 'error');

        const modal = document.getElementById('twdRejectModal');
        if (!modal) return showToast('Reject modal not found', 'error');

        const input = modal.querySelector('input[name="reject_id"]');
        const ta = modal.querySelector('textarea[name="reject_note"]');
        input.value = id;
        ta.value = '';
        modal.style.display = 'flex';
        ta.focus();
    };

    function initRejectModal() {
        const modal = document.getElementById('twdRejectModal');
        if (!modal) return;

        const closeBtn = modal.querySelector('.reject-close');
        const cancelBtn = modal.querySelector('.reject-cancel');
        const submitBtn = modal.querySelector('.reject-submit');
        const input = modal.querySelector('input[name="reject_id"]');
        const ta = modal.querySelector('textarea[name="reject_note"]');

        const hide = () => { modal.style.display = 'none'; };

        if (closeBtn) closeBtn.addEventListener('click', hide);
        if (cancelBtn) cancelBtn.addEventListener('click', hide);

        if (submitBtn) submitBtn.addEventListener('click', async function () {
            const id = input.value;
            const reason = (ta.value || '').trim();
            if (!reason) return showToast('Rejection reason is required', 'error');
            if (!confirm('Confirm rejection with provided reason?')) return;

            const csrf = getCsrfToken();
            try {
                const res = await fetch(`/payroll/work-detail/${encodeURIComponent(id)}/reject`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: 'rejected', note: reason })
                });

                if (!res.ok) {
                    const body = await res.json().catch(() => null);
                    console.error('Reject failed', res.status, body);
                    showToast(body?.message || 'Failed to reject', 'error');
                    return;
                }

                const body = await res.json().catch(() => null);
                showToast(body?.message || 'Rejected', 'success');
                hide();
                const container = document.getElementById('payrollWorkDetailsContainer');
                if (container) {
                    document.dispatchEvent(new CustomEvent('workDetails:reload'));
                } else {
                    setTimeout(() => location.reload(), 500);
                }
            } catch (err) {
                console.error(err);
                showToast('Something went wrong', 'error');
            }
        });

        // close when clicking backdrop area
        modal.addEventListener('click', function (e) {
            if (e.target === modal) hide();
        });
    }

    // initialize modal handlers if modal exists
    try { initRejectModal(); } catch (err) { console.warn('initRejectModal:', err); }

    function showModal() {
        ensureModal();
        const modal = document.getElementById('tutorWorkModal');
        modal.style.display = 'flex';
    }

    function hideModal() {
        const modal = document.getElementById('tutorWorkModal');
        if (modal) modal.style.display = 'none';
    }

    // Fetch detail and open modal
    window.openWorkDetailEditor = async function (id, assignmentId = null, scheduleId = null) {
        if (!id) return showToast('Missing work detail id', 'error');
        ensureModal();
        const saveBtn = document.getElementById('twd_save');
        saveBtn.disabled = true;
        saveBtn.textContent = 'Loading...';

        try {
            const res = await fetch(`/tutor/work-details/${encodeURIComponent(id)}`, {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            });

            if (!res.ok) {
                const txt = await res.text();
                showToast('Failed to load work detail', 'error');
                console.error('Load work detail error', res.status, txt);
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save';
                return;
            }

            const data = await res.json();
            const w = data.data ?? data;

            document.getElementById('twd_id').value = w.id ?? w.work_detail_id ?? id;
            document.getElementById('twd_assignment_id').value = assignmentId || w.assignment_id || '';
            document.getElementById('twd_schedule_id').value = scheduleId || w.schedule_daily_data_id || '';
            if (document.getElementById('twd_date')) document.getElementById('twd_date').value = w.date ? (new Date(w.date)).toISOString().slice(0, 10) : '';
            if (document.getElementById('twd_start_time')) document.getElementById('twd_start_time').value = w.start_time ?? '';
            if (document.getElementById('twd_end_time')) document.getElementById('twd_end_time').value = w.end_time ?? '';
            if (document.getElementById('twd_class')) document.getElementById('twd_class').value = w.class_no ?? w.classNo ?? '';
            if (document.getElementById('twd_status')) document.getElementById('twd_status').value = w.status ?? 'pending';
            const imgPreview = document.getElementById('twd_image_preview');
            if (imgPreview) {
                const path = w.proof_image ?? w.screenshot;
                if (path) {
                    imgPreview.src = '/storage/' + path;
                    imgPreview.classList.remove('hidden');
                    imgPreview.style.display = 'block';
                } else {
                    imgPreview.src = '';
                    imgPreview.classList.add('hidden');
                    imgPreview.style.display = 'none';
                }
            }


            showModal();

        } catch (err) {
            console.error(err);
            showToast('An error occurred while loading', 'error');
        } finally {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save';
        }
    };

    // Create new work detail
    window.createWorkDetail = function (assignmentId = null, scheduleId = null) {
        ensureModal();
        document.getElementById('twd_id').value = '';
        document.getElementById('twd_assignment_id').value = assignmentId || '';
        document.getElementById('twd_schedule_id').value = scheduleId || '';
        if (document.getElementById('twd_date')) document.getElementById('twd_date').value = '';
        if (document.getElementById('twd_start_time')) document.getElementById('twd_start_time').value = '';
        if (document.getElementById('twd_end_time')) document.getElementById('twd_end_time').value = '';
        if (document.getElementById('twd_class')) document.getElementById('twd_class').value = '';
        if (document.getElementById('twd_image')) document.getElementById('twd_image').value = '';
        if (document.getElementById('twd_status')) document.getElementById('twd_status').value = 'pending';
        const imgPreview = document.getElementById('twd_image_preview');
        if (imgPreview) {
            imgPreview.src = '';
            imgPreview.style.display = 'none';
            imgPreview.classList.add('hidden');
        }
        showModal();
    };

    // Unified entry point from the Work Details table buttons
    window.openWorkDetailForm = function (assignmentId, workDetailId = null, scheduleId = null) {
        if (workDetailId) {
            return window.openWorkDetailEditor(workDetailId, assignmentId, scheduleId);
        }
        return window.createWorkDetail(assignmentId, scheduleId);
    };

    // Save handler
    async function handleSave(e) {
        e.preventDefault();
        const id = document.getElementById('twd_id').value;
        // If id is empty, perform a create (POST). Otherwise update (PUT).
        const isCreate = !id;

        const payload = {
            assignment_id: document.getElementById('twd_assignment_id')?.value || null,
            schedule_daily_data_id: document.getElementById('twd_schedule_id')?.value || null,
            date: document.getElementById('twd_date')?.value || null,
            class_no: document.getElementById('twd_class')?.value || null,
            start_time: document.getElementById('twd_start_time')?.value || null,
            end_time: document.getElementById('twd_end_time')?.value || null,
            status: document.getElementById('twd_status')?.value || 'pending',
        };
        console.log(payload);


        const csrf = getCsrfToken();
        try {
            const imageInput = document.getElementById('twd_image');
            const file = imageInput?.files?.[0] || null;
            let res;
            if (isCreate || file) {
                if (isCreate && !file) {
                    showToast('Screenshot is required', 'error');
                    return;
                }
                const form = new FormData();
                if (payload.assignment_id) form.append('assignment_id', payload.assignment_id);
                if (payload.schedule_daily_data_id) form.append('schedule_daily_data_id', payload.schedule_daily_data_id);
                if (payload.date) form.append('date', payload.date);
                if (payload.start_time) form.append('start_time', payload.start_time);
                if (payload.end_time) form.append('end_time', payload.end_time);
                if (payload.class_no) form.append('class_no', payload.class_no);
                if (payload.status) form.append('status', payload.status);
                if (file) form.append('image', file);
                if (isCreate) {
                    res = await fetch(`/tutor/work-details`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: form
                    });
                } else {
                    form.append('_method', 'PUT');
                    res = await fetch(`/tutor/work-details/${encodeURIComponent(id)}`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: form
                    });
                }
            } else {
                res = await fetch(`/tutor/work-details/${encodeURIComponent(id)}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify(payload)
                });
            }

            if (!res.ok) {
                const body = await res.json().catch(() => null);
                console.error('Save failed', res.status, body);
                showToast(body?.message || 'Failed to save', 'error');
                return;
            }

            const body = await res.json().catch(() => null);
            showToast('Saved', 'success');
            hideModal();

            // Always reload the page to refresh the table with updated data including proof images
            setTimeout(() => location.reload(), 600);

        } catch (err) {
            console.error(err);
            showToast('An error occurred while saving', 'error');
        }
    }

    // Open Tutor Work Detail Modal
    window.openTutorWorkDetailModal = function(assignment, schedule, workDetail) {
        const modal = document.getElementById('tutorWorkDetailModal');
        if (!modal) return;
        
        // Populate schedule information
        document.getElementById('modal-date').textContent = schedule?.date ? new Date(schedule.date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : 'N/A';
        document.getElementById('modal-day').textContent = schedule?.date ? new Date(schedule.date).toLocaleDateString('en-US', { weekday: 'long' }) : 'N/A';
        document.getElementById('modal-school').textContent = schedule?.school || 'N/A';
        document.getElementById('modal-class').textContent = schedule?.class || 'N/A';
        
        // Populate time and rate
        const startTime = workDetail?.start_time || (schedule?.time ? new Date('1970-01-01 ' + schedule.time).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true }) : '—');
        const endTime = workDetail?.end_time ? new Date('1970-01-01 ' + workDetail.end_time).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true }) : 
                        (schedule?.time && schedule?.duration ? new Date(new Date('1970-01-01 ' + schedule.time).getTime() + schedule.duration * 60000).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true }) : '—');
        
        document.getElementById('modal-start-time').textContent = startTime;
        document.getElementById('modal-end-time').textContent = endTime;
        document.getElementById('modal-duration').textContent = schedule?.duration ? schedule.duration + ' min' : 'N/A';
        document.getElementById('modal-rate').textContent = schedule?.rate ? '₱' + parseFloat(schedule.rate).toFixed(2) : 'N/A';
        
        // Populate proof
        const proofContainer = document.getElementById('modal-proof-container');
        if (workDetail?.proof_image) {
            proofContainer.innerHTML = `<img src="/storage/${workDetail.proof_image}" alt="Proof" class="w-full max-w-md h-64 object-cover rounded-lg border-2 border-gray-300 shadow-md cursor-pointer hover:opacity-90 transition-opacity" onclick="window.open('/storage/${workDetail.proof_image}', '_blank')">`;
        } else {
            proofContainer.innerHTML = '<p class="text-gray-500 text-center py-8">No proof image uploaded yet</p>';
        }
        
        // Handle action buttons based on status
        const actionButtons = document.getElementById('modal-action-buttons');
        const isApproved = workDetail && workDetail.status?.toLowerCase() === 'approved';
        const isPendingAcceptance = assignment.class_status === 'pending_acceptance';
        
        if (isPendingAcceptance) {
            actionButtons.innerHTML = `
                <button type="button" onclick="acceptAssignment(${assignment.id}); closeTutorWorkDetailModal();" 
                    class="px-6 py-2 bg-green-600 text-white rounded-md font-medium hover:bg-green-700 transition-colors flex items-center gap-2">
                    <i class="fas fa-check"></i> Accept Assignment
                </button>
                <button type="button" onclick="rejectAssignment(${assignment.id}); closeTutorWorkDetailModal();" 
                    class="px-6 py-2 bg-red-600 text-white rounded-md font-medium hover:bg-red-700 transition-colors flex items-center gap-2">
                    <i class="fas fa-times"></i> Reject Assignment
                </button>
            `;
        } else if (!isApproved) {
            const hasSubmitted = workDetail !== null;
            actionButtons.innerHTML = `
                <button type="button" onclick="closeTutorWorkDetailModal(); openWorkDetailForm(${assignment.id}, ${workDetail ? workDetail.id : 'null'}, ${assignment.schedule_daily_data_id});" 
                    class="px-6 py-2 bg-blue-600 text-white rounded-md font-medium hover:bg-blue-700 transition-colors flex items-center gap-2">
                    <i class="fas ${hasSubmitted ? 'fa-edit' : 'fa-plus'}"></i> ${hasSubmitted ? 'Edit Work Details' : 'Add Work Details'}
                </button>
                ${hasSubmitted ? `<button type="button" onclick="confirmDeleteWorkDetail(${workDetail.id}); closeTutorWorkDetailModal();" 
                    class="px-6 py-2 bg-red-600 text-white rounded-md font-medium hover:bg-red-700 transition-colors flex items-center gap-2">
                    <i class="fas fa-trash"></i> Delete
                </button>` : ''}
            `;
        } else {
            actionButtons.innerHTML = '<span class="text-sm text-green-600 font-medium flex items-center gap-2"><i class="fas fa-check-circle"></i> Approved</span>';
        }
        
        modal.classList.remove('hidden');
    };
    
    window.closeTutorWorkDetailModal = function() {
        const modal = document.getElementById('tutorWorkDetailModal');
        if (modal) modal.classList.add('hidden');
    };

    // Delete confirmation & request
    window.confirmDeleteWorkDetail = async function (id) {
        if (!id) return showToast('Missing id', 'error');
        
        // Create modal
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-50 flex items-center justify-center';
        modal.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
        
        const modalContent = document.createElement('div');
        modalContent.className = 'bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4';
        
        modalContent.innerHTML = `
            <div class="flex items-start mb-4">
                <div class="flex-shrink-0 bg-red-100 rounded-full p-3 mr-4">
                    <i class="fas fa-trash text-red-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Confirm Delete</h3>
                    <p class="text-sm text-gray-600">Delete this work detail? This action cannot be undone.</p>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button id="cancelDeleteBtn" class="px-4 py-2 text-gray-700 bg-gray-300 rounded-md hover:bg-gray-400 transition-colors">
                    Cancel
                </button>
                <button id="confirmDeleteBtn" class="px-4 py-2 text-white bg-red-600 rounded-md hover:bg-red-700 transition-colors">
                    Delete
                </button>
            </div>
        `;
        
        modal.appendChild(modalContent);
        document.body.appendChild(modal);
        
        // Handle cancel
        const cancelBtn = modal.querySelector('#cancelDeleteBtn');
        cancelBtn.onclick = () => modal.remove();
        
        // Handle backdrop click
        modal.onclick = (e) => {
            if (e.target === modal) modal.remove();
        };
        
        // Handle confirm
        const confirmBtn = modal.querySelector('#confirmDeleteBtn');
        confirmBtn.onclick = async () => {
            modal.remove();
            
            const csrf = getCsrfToken();
            try {
                const res = await fetch(`/tutor/work-details/${encodeURIComponent(id)}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    }
                });

                if (!res.ok) {
                    const body = await res.json().catch(() => null);
                    console.error('Delete failed', res.status, body);
                    showToast(body?.message || 'Failed to delete', 'error');
                    return;
                }

                showToast('Deleted', 'success');
                setTimeout(() => window.location.reload(), 500);

            } catch (err) {
                console.error(err);
                showToast('An error occurred while deleting', 'error');
            }
        };
    };
    

    // Image viewer
    function ensureImageViewer() {
        if (document.getElementById('twdImageViewer')) return;

        const viewer = document.createElement('div');
        viewer.id = 'twdImageViewer';
        viewer.className = 'fixed inset-0 z-60 flex items-center justify-center';
        viewer.style.display = 'none';

        viewer.innerHTML = `
            <div class="viewer-backdrop absolute inset-0 bg-black bg-opacity-80"></div>
            <div class="viewer-content relative z-10 max-w-full max-h-full flex items-center justify-center">
                <button type="button" class="viewer-close absolute top-4 right-4 text-white text-2xl" aria-label="Close">&times;</button>
                <img id="twdImageViewerImg" src="" alt="Preview" style="max-width:90vw; max-height:90vh; transform-origin: 0 0; transition: transform 80ms ease, opacity 120ms; cursor: zoom-in; opacity:0; user-select: none;" />
            </div>
        `;

        document.body.appendChild(viewer);

        // ensure viewer is above other modals
        viewer.style.zIndex = '999999';

        const img = viewer.querySelector('#twdImageViewerImg');
        const content = viewer.querySelector('.viewer-content');
        if (content) content.style.zIndex = '1000000';
        const backdrop = viewer.querySelector('.viewer-backdrop');
        const closeBtn = viewer.querySelector('.viewer-close');

        let scale = 1, tx = 0, ty = 0;
        let isPanning = false, lastX = 0, lastY = 0;

        function setTransform() {
            img.style.transform = `translate(${tx}px, ${ty}px) scale(${scale})`;
        }

        function reset() {
            scale = 1; tx = 0; ty = 0; setTransform();
        }

        function open() {
            const editModal = document.getElementById('tutorWorkModal');
            if (editModal) editModal.style.pointerEvents = 'none';
            viewer.style.display = 'flex';
            setTimeout(() => { img.style.opacity = '1'; }, 10);
        }

        function close() {
            const editModal = document.getElementById('tutorWorkModal');
            if (editModal) editModal.style.pointerEvents = 'auto';
            img.style.opacity = '0';
            setTimeout(() => {
                viewer.style.display = 'none';
                img.src = '';
                reset();
            }, 140);
        }

        function onWheel(e) {
            e.preventDefault();
            const rect = img.getBoundingClientRect();
            const offsetX = e.clientX - rect.left;
            const offsetY = e.clientY - rect.top;
            const delta = e.deltaY > 0 ? 0.9 : 1.1;
            const prev = scale;
            scale = Math.min(5, Math.max(0.5, scale * delta));
            tx = (tx - offsetX) * (scale / prev) + offsetX;
            ty = (ty - offsetY) * (scale / prev) + offsetY;
            setTransform();
        }

        img.addEventListener('wheel', onWheel, { passive: false });

        img.addEventListener('mousedown', function (e) {
            isPanning = true;
            lastX = e.clientX;
            lastY = e.clientY;
            img.style.cursor = 'grabbing';
            e.preventDefault();
        });

        document.addEventListener('mousemove', function (e) {
            if (!isPanning) return;
            const dx = e.clientX - lastX;
            const dy = e.clientY - lastY;
            lastX = e.clientX; lastY = e.clientY;
            tx += dx; ty += dy;
            setTransform();
        });

        document.addEventListener('mouseup', function () {
            if (!isPanning) return;
            isPanning = false;
            img.style.cursor = 'zoom-in';
        });

        img.addEventListener('dblclick', function () { reset(); });

        backdrop.addEventListener('click', close);
        closeBtn.addEventListener('click', close);

        viewer._open = function (src) {
            img.src = src;
            reset();
            open();
        };
        viewer._close = close;
    }

    function openImageViewer(src) {
        if (!src) return;
        ensureImageViewer();
        const v = document.getElementById('twdImageViewer');
        if (v && v._open) v._open(src);
    }

    function closeImageViewer() {
        const v = document.getElementById('twdImageViewer');
        if (v && v._close) v._close();
    }

    document.addEventListener('click', function (e) {
        if (!e.target) return;

        // Click from the image preview inside the edit modal
        if (e.target.id === 'twd_image_preview' && e.target.src) {
            openImageViewer(e.target.src);
            return;
        }

        // Click on a table thumbnail added to the payroll table
        if (e.target.classList && e.target.classList.contains('twd-table-thumb')) {
            const src = e.target.getAttribute('src') || e.target.dataset.src;
            if (src) openImageViewer(src);
            return;
        }
    });
    // Global handler for filter dropdowns used across views.
    // - If a `#tutorFilterForm` exists, dispatch its submit event (AJAX reload in payroll view).
    // - Otherwise perform a client-side filter on the tutor table rows.
    window.handleTutorFilterChange = function (field) {
        try {
            const form = document.getElementById('tutorFilterForm');
            if (form) {
                // trigger the form submit which is handled in the payroll partial script
                form.dispatchEvent(new Event('submit', { cancelable: true }));
                return;
            }

            // Client-side filtering for tutor tab table
            if (field === 'status') {
                const sel = document.getElementById('filterStatus');
                const val = sel ? (sel.value || '').toLowerCase() : '';
                const tbody = document.getElementById('tutorTableBody');
                if (!tbody) return;
                const rows = Array.from(tbody.querySelectorAll('.tutor-row'));

                let anyVisible = false;
                rows.forEach(tr => {
                    try {
                        const statusSpan = tr.querySelector('td span');
                        const statusText = statusSpan ? (statusSpan.textContent || '').trim().toLowerCase() : '';
                        const showAll = !val || val === 'all';
                        const match = showAll || statusText === val || (val === 'reject' && statusText === 'rejected');
                        tr.style.display = match ? '' : 'none';
                        if (match) anyVisible = true;
                    } catch (err) { console.warn(err); }
                });

                const noResults = document.getElementById('noSearchResults') || document.getElementById('noResultsRow');
                if (noResults) {
                    // show the appropriate message when none visible
                    if (!anyVisible) {
                        noResults.style.display = '';
                    } else {
                        noResults.style.display = 'none';
                    }
                }
            }
        } catch (err) {
            console.error('handleTutorFilterChange error', err);
        }
    };

    // Accept assignment
    window.acceptAssignment = async function(assignmentId) {
        // Create modal
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-50 flex items-center justify-center';
        modal.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
        
        const modalContent = document.createElement('div');
        modalContent.className = 'bg-white rounded-lg shadow-xl max-w-md w-full mx-4';
        
        modalContent.innerHTML = `
            <div class="p-6">
                <div class="flex items-start mb-4">
                    <div class="flex-shrink-0 bg-green-100 rounded-full p-3 mr-4">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Accept Assignment</h3>
                        <p class="text-sm text-gray-600">Are you sure you want to accept this assignment?</p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 rounded-b-lg">
                <button id="cancelAcceptBtn" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md font-medium hover:bg-gray-400 transition-colors">
                    Cancel
                </button>
                <button id="confirmAcceptBtn" class="px-4 py-2 bg-green-600 text-white rounded-md font-medium hover:bg-green-700 transition-colors">
                    Accept
                </button>
            </div>
        `;
        
        modal.appendChild(modalContent);
        document.body.appendChild(modal);
        
        // Handle cancel
        const cancelBtn = modal.querySelector('#cancelAcceptBtn');
        cancelBtn.onclick = () => modal.remove();
        
        // Handle backdrop click
        modal.onclick = (e) => {
            if (e.target === modal) modal.remove();
        };
        
        // Handle confirm
        const confirmBtn = modal.querySelector('#confirmAcceptBtn');
        confirmBtn.onclick = async () => {
            modal.remove();
            
            try {
                const response = await fetch('/tutor/assignment/accept', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({ assignment_id: assignmentId })
                });

                const data = await response.json();

                if (data.success) {
                    showToast(data.message || 'Assignment accepted successfully!', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showToast(data.message || 'Failed to accept assignment', 'error');
                }
            } catch (error) {
                console.error('Error accepting assignment:', error);
                showToast('An error occurred while accepting the assignment', 'error');
            }
        };
    };

    // Reject assignment
    window.rejectAssignment = async function(assignmentId) {
        // Create modal
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-50 flex items-center justify-center';
        modal.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
        
        const modalContent = document.createElement('div');
        modalContent.className = 'bg-white rounded-lg shadow-xl max-w-md w-full mx-4';
        
        modalContent.innerHTML = `
            <div class="p-6">
                <div class="flex items-start mb-4">
                    <div class="flex-shrink-0 bg-red-100 rounded-full p-3 mr-4">
                        <i class="fas fa-times-circle text-red-600 text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Reject Assignment</h3>
                        <p class="text-sm text-gray-600 mb-4">Please provide a reason for rejecting this assignment:</p>
                        <textarea id="rejectReasonInput" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500" rows="3" placeholder="Enter reason (optional)..."></textarea>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 rounded-b-lg">
                <button id="cancelRejectBtn" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md font-medium hover:bg-gray-400 transition-colors">
                    Cancel
                </button>
                <button id="confirmRejectBtn" class="px-4 py-2 bg-red-600 text-white rounded-md font-medium hover:bg-red-700 transition-colors">
                    Reject
                </button>
            </div>
        `;
        
        modal.appendChild(modalContent);
        document.body.appendChild(modal);
        
        // Focus on textarea
        const textarea = modal.querySelector('#rejectReasonInput');
        textarea.focus();
        
        // Handle cancel
        const cancelBtn = modal.querySelector('#cancelRejectBtn');
        cancelBtn.onclick = () => modal.remove();
        
        // Handle backdrop click
        modal.onclick = (e) => {
            if (e.target === modal) modal.remove();
        };
        
        // Handle confirm
        const confirmBtn = modal.querySelector('#confirmRejectBtn');
        confirmBtn.onclick = async () => {
            const reason = textarea.value.trim();
            modal.remove();
            
            try {
                const response = await fetch('/tutor/assignment/reject', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({ 
                        assignment_id: assignmentId,
                        reason: reason || 'No reason provided'
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showToast(data.message || 'Assignment rejected successfully!', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showToast(data.message || 'Failed to reject assignment', 'error');
                }
            } catch (error) {
                console.error('Error rejecting assignment:', error);
                showToast('An error occurred while rejecting the assignment', 'error');
            }
        };
    };
    

})();
