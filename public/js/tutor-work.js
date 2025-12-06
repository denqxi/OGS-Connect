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
    function approveWorkDetail(id) {
    if (!confirm("Approve this work detail?")) return;

    fetch(`/payroll/work-detail/${id}/approve`, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
            "Accept": "application/json"
        }
    })
    .then(res => res.json())
    .then(data => {
        showToast(data.message || 'Updated', 'success');
        const container = document.getElementById('payrollWorkDetailsContainer');
        if (container) {
            // notify payroll partial to reload via AJAX
            document.dispatchEvent(new CustomEvent('workDetails:reload'));
        } else {
            // fallback: full page reload
            setTimeout(() => location.reload(), 300);
        }
    })
    .catch(err => {
        console.error(err);
        showToast("Something went wrong", 'error');
    });
}


    function ensureModal() {
        if (document.getElementById('tutorWorkModal')) return;

        const modal = document.createElement('div');
        modal.id = 'tutorWorkModal';
        modal.className = 'fixed inset-0 z-50 flex items-center justify-center';
        modal.style.display = 'none';

        modal.innerHTML = `
            <div class="modal-backdrop absolute inset-0 bg-black opacity-30"></div>
            <div class="modal-content bg-white rounded-lg shadow-lg w-full max-w-lg z-10 overflow-hidden">
                <div class="p-4 border-b flex items-center justify-between">
                    <h3 class="font-medium">Edit Work Detail</h3>
                    <button type="button" id="tutorWorkModalClose" class="text-gray-600">&times;</button>
                </div>
                <div class="p-4">
                    <form id="tutorWorkForm">
                        <input type="hidden" name="id" id="twd_id">
                        <div class="mb-3">
                            <label class="block text-xs text-gray-600">Date</label>
                            <input type="date" id="twd_date" name="date" class="mt-1 block w-full border rounded px-2 py-1">
                        </div>
                        <div class="mb-3">
                            <label class="block text-xs text-gray-600">Start Time</label>
                            <input type="time" id="twd_start_time" name="start_time" class="mt-1 block w-full border rounded px-2 py-1">
                        </div>

                        <div class="mb-3">
                            <label class="block text-xs text-gray-600">End Time</label>
                            <input type="time" id="twd_end_time" name="end_time" class="mt-1 block w-full border rounded px-2 py-1">
                        </div>
                        <div class="mb-3">
                                    <label class="block text-xs text-gray-600">Image</label>
                                    <input type="file" id="twd_image" name="image" accept="image/*" class="mt-1 block w-full">
                        <img id="twd_image_preview" src="" alt="Image Preview" class="mt-2 w-32 h-32 object-cover rounded border hidden" style="display:none;" >

                                </div>


                        <div class="flex justify-end space-x-2">
                            <button type="button" id="twd_cancel" class="px-3 py-1 border rounded text-sm">Cancel</button>
                            <button type="submit" id="twd_save" class="px-3 py-1 bg-indigo-600 text-white rounded text-sm">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        document.getElementById('tutorWorkModalClose').addEventListener('click', hideModal);
        document.getElementById('twd_cancel').addEventListener('click', hideModal);
        document.getElementById('tutorWorkForm').addEventListener('submit', handleSave);
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
    window.openWorkDetailEditor = async function (id) {
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
            if (document.getElementById('twd_date')) document.getElementById('twd_date').value = w.date ? (new Date(w.date)).toISOString().slice(0, 10) : '';
            if (document.getElementById('twd_start_time')) document.getElementById('twd_start_time').value = w.start_time ?? '';
            if (document.getElementById('twd_end_time')) document.getElementById('twd_end_time').value = w.end_time ?? '';
            if (document.getElementById('twd_class')) document.getElementById('twd_class').value = w.class_no ?? w.classNo ?? '';
            if (document.getElementById('twd_status')) document.getElementById('twd_status').value = w.status ?? 'pending';
            const imgPreview = document.getElementById('twd_image_preview');
            if (imgPreview) {
                const path = w.screenshot ?? w.screenshot;
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
    window.createWorkDetail = function () {
        ensureModal();
        // clear fields for creating
        document.getElementById('twd_id').value = '';
        if (document.getElementById('twd_date')) document.getElementById('twd_date').value = '';
        if (document.getElementById('twd_start_time')) document.getElementById('twd_start_time').value = '';
        if (document.getElementById('twd_end_time')) document.getElementById('twd_end_time').value = '';
        if (document.getElementById('twd_class')) document.getElementById('twd_class').value = '';
        if (document.getElementById('twd_image')) document.getElementById('twd_image').value = '';
        if (document.getElementById('twd_status')) document.getElementById('twd_status').value = 'pending';
        // hide image preview when creating a new work detail
        const imgPreview = document.getElementById('twd_image_preview');
        if (imgPreview) {
            imgPreview.src = '';
            imgPreview.style.display = 'none';
            imgPreview.classList.add('hidden');
        }
        showModal();
    };

    // Save handler
    async function handleSave(e) {
        e.preventDefault();
        const id = document.getElementById('twd_id').value;
        // If id is empty, perform a create (POST). Otherwise update (PUT).
        const isCreate = !id;

        const payload = {
            date: document.getElementById('twd_date')?.value || null,
            class_no: document.getElementById('twd_class')?.value || null,
            start_time: document.getElementById('twd_start_time')?.value || null,
            end_time: document.getElementById('twd_end_time')?.value || null,
            status: document.getElementById('twd_status')?.value || null,

        };
        console.log(payload);


        const csrf = getCsrfToken();
        try {
            const imageInput = document.getElementById('twd_image');
            const file = imageInput?.files?.[0] || null;
            let res;
            if (isCreate || file) {
                const form = new FormData();
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

            // Update the table row values if present
            try {
                const btn = document.querySelector(`button[onclick="openWorkDetailEditor('${id}')"]`);
                const tr = btn ? btn.closest('tr') : null;
                if (tr) {
                    if (payload.date) tr.children[0].textContent = payload.date;
                    if (payload.ph_time) tr.children[1].textContent = payload.ph_time;
                    if (payload.class_no) tr.children[2].textContent = payload.class_no;
                    if (payload.status) tr.children[3].querySelector('span').textContent = payload.status.charAt(0).toUpperCase() + payload.status.slice(1);
                } else {
                    // fallback: notify payroll partial or reload
                    const container = document.getElementById('payrollWorkDetailsContainer');
                    if (container) document.dispatchEvent(new CustomEvent('workDetails:reload'));
                    else setTimeout(() => location.reload(), 600);
                }
            } catch (err) { console.warn(err); setTimeout(() => location.reload(), 600); }

        } catch (err) {
            console.error(err);
            showToast('An error occurred while saving', 'error');
        }
    }

    // Delete confirmation & request
    window.confirmDeleteWorkDetail = async function (id) {
        if (!id) return showToast('Missing id', 'error');
        if (!confirm('Delete this work detail? This action cannot be undone.')) return;

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
            const btn = document.querySelector(`button[onclick="confirmDeleteWorkDetail('${id}')"]`);
            const tr = btn ? btn.closest('tr') : null;
            if (tr) tr.remove();
            // notify payroll partial to refresh if present
            const container = document.getElementById('payrollWorkDetailsContainer');
            if (container) document.dispatchEvent(new CustomEvent('workDetails:reload'));

        } catch (err) {
            console.error(err);
            showToast('An error occurred while deleting', 'error');
        }
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
    

})();
