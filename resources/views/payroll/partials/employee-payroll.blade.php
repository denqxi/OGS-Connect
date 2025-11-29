<div class="p-6 border-b border-gray-200">
    <h2 class="text-xl font-semibold text-gray-800">Tutor Payroll</h2>
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
                        placeholder="Search full name, email, phone..." id="tutorSearch"
                        class="w-full pl-10 pr-10 py-2 border border-gray-300 rounded-md text-sm 
                                  focus:outline-none focus:border-[0.5px] focus:border-[#2A5382] 
                                  focus:ring-0 focus:shadow-xl">
                    <button type="button" id="clearSearch"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <select name="status" id="filterStatus"
                    class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white"
                    onchange="handleTutorFilterChange('status')">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <!-- Right side -->
            <div class="flex items-center space-x-4">
                
                <div class="flex items-center space-x-2">
                    <button type="button" id="addClassModal" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white rounded text-xs">
                        Add Class
                    </button>

                    <!-- Modal -->
                    <div id="payrollModal" class="fixed inset-0 z-50 hidden items-center justify-center">
                        <!-- Overlay -->
                        <div id="payrollModalOverlay" class="absolute inset-0 bg-black bg-opacity-50"></div>

                        <!-- Modal panel -->
                        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md mx-4 z-10">
                            <div class="flex items-center justify-between px-4 py-3 border-b">
                                <h3 class="text-sm font-medium text-gray-800">Create Payroll</h3>
                                <button type="button" id="closePayrollModal" class="text-gray-500 hover:text-gray-700">
                                    <span class="sr-only">Close</span>
                                    &times;
                                </button>
                            </div>

                            <div class="p-4">
                                <!-- Simple form skeleton — adjust fields as needed -->
                                <form id="createPayrollForm">
                                    <div class="mb-3">
                                        <label class="block text-xs text-gray-600 mb-1">Tutor ID</label>
                                        <input type="text" name="tutor_id" class="w-full border px-3 py-2 rounded text-sm" placeholder="Enter tutor id">
                                    </div>

                                    <div class="mb-3">
                                        <label class="block text-xs text-gray-600 mb-1">Amount</label>
                                        <input type="number" step="0.01" name="amount" class="w-full border px-3 py-2 rounded text-sm" placeholder="0.00">
                                    </div>

                                    <div class="flex justify-end space-x-2 mt-4">
                                        <button type="button" id="cancelPayrollBtn" class="px-3 py-1.5 bg-gray-200 text-gray-700 rounded text-sm">Cancel</button>
                                        <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded text-sm">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <script>
                        (function () {
                            const openBtn = document.getElementById('addClassModal');
                            const modal = document.getElementById('payrollModal');
                            const overlay = document.getElementById('payrollModalOverlay');
                            const closeBtn = document.getElementById('closePayrollModal');
                            const cancelBtn = document.getElementById('cancelPayrollBtn');

                            function showModal() {
                                modal.classList.remove('hidden');
                                modal.classList.add('flex');
                                document.body.style.overflow = 'hidden';
                            }
                            function hideModal() {
                                modal.classList.add('hidden');
                                modal.classList.remove('flex');
                                document.body.style.overflow = '';
                            }

                            openBtn && openBtn.addEventListener('click', showModal);
                            closeBtn && closeBtn.addEventListener('click', hideModal);
                            cancelBtn && cancelBtn.addEventListener('click', hideModal);
                            overlay && overlay.addEventListener('click', hideModal);

                            document.addEventListener('keydown', function (e) {
                                if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                                    hideModal();
                                }
                            });
                        })();
                    </script>
                </div>
            </div>
        </div>
    </form>
</div>
