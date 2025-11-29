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
                            <label class="block text-xs text-gray-600">Class No.</label>
                            <input type="text" id="twd_class" name="class_no" class="mt-1 block w-full border rounded px-2 py-1">
                        </div>
                        <div class="mb-3">
                                    <label class="block text-xs text-gray-600">Image</label>
                                    <input type="file" id="twd_image" name="image" accept="image/*" class="mt-1 block w-full">
                        <img id="twd_image_preview" src="" alt="Image Preview" class="mt-2 w-32 h-32 object-cover rounded border hidden" style="display:none;" >

                                </div>

                        
                        <div class="mb-3">
                            <label class="block text-xs text-gray-600">Notes</label>
                            <textarea id="twd_notes" name="notes" class="mt-1 block w-full border rounded px-2 py-1" rows="3"></textarea>
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
            if (document.getElementById('twd_notes')) document.getElementById('twd_notes').value = w.notes ?? '';
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
        if (document.getElementById('twd_notes')) document.getElementById('twd_notes').value = '';
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
            notes: document.getElementById('twd_notes')?.value || null,
            status: document.getElementById('twd_status')?.value || null,

        };
        console.log(payload);


        const csrf = getCsrfToken();
        try {
            // if a file is present or creating, send FormData; otherwise send JSON for update
            const imageInput = document.getElementById('twd_image');
            const file = imageInput?.files?.[0] || null;
            let res;
            if (isCreate || file) {
                const form = new FormData();
                if (payload.date) form.append('date', payload.date);
                if (payload.start_time) form.append('start_time', payload.start_time);
                if (payload.end_time) form.append('end_time', payload.end_time);
                if (payload.class_no) form.append('class_no', payload.class_no);
                if (payload.notes) form.append('notes', payload.notes);
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
                    // fallback: reload
                    setTimeout(() => location.reload(), 600);
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
            // Remove row
            const btn = document.querySelector(`button[onclick="confirmDeleteWorkDetail('${id}')"]`);
            const tr = btn ? btn.closest('tr') : null;
            if (tr) tr.remove();

        } catch (err) {
            console.error(err);
            showToast('An error occurred while deleting', 'error');
        }
    };

})();
