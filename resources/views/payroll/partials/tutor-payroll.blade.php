<div class="p-6 border-b border-gray-200">
    <h2 class="text-xl font-semibold text-gray-800">Work Details</h2>
</div>

<div class="p-6 border-b border-gray-200">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-medium text-gray-700">Search Filters</h3>
    </div>

    <form method="GET" action="{{ route('payroll.index') }}" id="tutorFilterForm">
        <input type="hidden" name="filter_applied" value="employee_payroll">
        <div class="flex justify-between items-center space-x-4">
            <div class="flex items-center space-x-4 flex-1 max-w-lg">
                <div class="relative flex-1">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search tutor name..." id="tutorSearch"
                        class="w-full pl-10 pr-10 py-2 border border-gray-300 rounded-md text-sm 
                                  focus:outline-none focus:border-[0.5px] focus:border-[#2A5382] 
                                  focus:ring-0 focus:shadow-xl">
                    <button type="button" id="clearSearch"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <button type="button" id="addClassModal" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white rounded text-xs" onclick="createWorkDetail()">
                        Add Class
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<div id="payrollWorkDetailsContainer">
    <div class="py-6 text-center text-gray-500">Loading work details...</div>
</div>

<!-- Reject Reason Modal -->
<div id="twdRejectModal" class="fixed inset-0 z-50 items-center justify-center" style="display:none;">
    <div class="absolute inset-0 bg-black opacity-40"></div>
    <div class="relative bg-white rounded-lg shadow-lg w-full max-w-lg mx-4 z-10">
        <div class="p-4 border-b flex items-center justify-between">
            <h3 class="font-medium">Provide Rejection Reason</h3>
            <button type="button" class="reject-close text-gray-600">&times;</button>
        </div>
        <div class="p-4">
            <input type="hidden" name="reject_id" value="">
            <div class="mb-3">
                <label class="block text-sm text-gray-700 mb-2">Please provide a reason for rejecting this work detail:</label>
                <textarea name="reject_note" id="reject_note" rows="5" class="w-full border rounded px-3 py-2 text-sm" placeholder="Enter reason (required)"></textarea>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" class="reject-cancel px-3 py-1 border rounded text-sm">Cancel</button>
                <button type="button" class="reject-submit px-3 py-1 bg-red-600 text-white rounded text-sm">Reject</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const container = document.getElementById('payrollWorkDetailsContainer');
        const form = document.getElementById('tutorFilterForm');

        function buildUrl(params = {}) {
            const url = new URL("{{ route('payroll.work-details') }}", window.location.origin);
            const qs = new URLSearchParams(Object.fromEntries(new FormData(form)));
            for (const [k, v] of Object.entries(params)) {
                qs.set(k, v);
            }
            url.search = qs.toString();
            return url.toString();
        }

        async function loadWorkDetails(url) {
            try {
                container.innerHTML = '<div class="py-6 text-center text-gray-500">Loading work details...</div>';
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) throw new Error('Network error');
                const html = await res.text();
                container.innerHTML = html;

                container.querySelectorAll('a[data-page]').forEach(a => {
                    a.addEventListener('click', function (e) {
                        e.preventDefault();
                        const page = this.dataset.page;
                        loadWorkDetails(buildUrl({ page }));
                    });
                });
            } catch (err) {
                container.innerHTML = '<div class="py-6 text-center text-red-500">Failed to load work details</div>';
                console.error(err);
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            loadWorkDetails(buildUrl());
        });

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                loadWorkDetails(buildUrl());
            });
        }

        // Expose a reload handler and listen for external reload events (e.g. after approve/reject/save).
        window.reloadPayrollWorkDetails = function () {
            try { loadWorkDetails(buildUrl()); } catch (e) { console.warn('reloadPayrollWorkDetails:', e); }
        };

        document.addEventListener('workDetails:reload', function () {
            try { loadWorkDetails(buildUrl()); } catch (e) { console.warn('workDetails:reload handler error', e); }
        });

        // Periodic auto-refresh: refresh the work details every 60 seconds
        const AUTO_REFRESH_MS = 60000;
        setInterval(function () {
            try { loadWorkDetails(buildUrl()); } catch (e) { console.warn('auto-refresh failed', e); }
        }, AUTO_REFRESH_MS);
    })();
</script>
<script src="{{ asset('js/tutor-work.js') }}"></script>
