<div class="p-6 border-b border-gray-200">
    <h2 class="text-xl font-semibold text-gray-800">Payroll Approval</h2>
</div>

<!-- Summary Cards -->
<div class="p-6 border-b border-gray-200">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Total Tutors -->
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-600 mb-1">Total Tutors</p>
                    <p class="text-2xl font-bold text-blue-700">{{ $tutors->total() ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-200 rounded-full flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-lg"></i>
                </div>
            </div>
        </div>

        <!-- Total Pending Amount -->
        <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-lg p-4 border border-yellow-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-600 mb-1">Pending Amount</p>
                    <p class="text-2xl font-bold text-orange-700">₱{{ number_format($totalPendingAmount ?? 0, 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-200 rounded-full flex items-center justify-center">
                    <i class="fas fa-clock text-orange-600 text-lg"></i>
                </div>
            </div>
        </div>

        <!-- Approved This Period -->
        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-600 mb-1">Approved Amount</p>
                    <p class="text-2xl font-bold text-green-700">₱{{ number_format($totalApprovedAmount ?? 0, 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-200 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-lg"></i>
                </div>
            </div>
        </div>

        <!-- Total Payroll -->
        <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg p-4 border border-purple-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-600 mb-1">Total Payroll</p>
                    <p class="text-2xl font-bold text-purple-700">₱{{ number_format($totalApprovedAmount ?? 0, 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-200 rounded-full flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-purple-600 text-lg"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search Filter -->
<div class="p-6 border-b border-gray-200">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-medium text-gray-700">Search Filters</h3>
    </div>

    <form method="GET" action="{{ route('payroll.index') }}" id="payrollFilterForm">
        <input type="hidden" name="tab" value="payrolls">
        <div class="flex justify-between items-center space-x-4">
            <div class="flex items-center space-x-4 flex-1 max-w-lg">
                <div class="relative flex-1">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search tutor name..." id="payrollSearch"
                        class="w-full pl-10 pr-10 py-2 border border-gray-300 rounded-md text-sm 
                                  focus:outline-none focus:border-[0.5px] focus:border-[#2A5382] 
                                  focus:ring-0 focus:shadow-xl">
                    <button type="button" id="clearPayrollSearch"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <button type="submit"
                class="px-4 py-2 bg-[#0E335D] text-white rounded-md text-sm font-medium hover:bg-[#0B2847] transition-colors">
                <i class="fas fa-search mr-2"></i>Search
            </button>
        </div>
    </form>
</div>

<div class="p-6">
    <div class="overflow-x-auto">
        <table class="w-full" id="payrollsTable">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tutor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($tutors as $tutor)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $tutor->full_name ?? $tutor->username }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $tutor->account?->account_name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex items-center justify-center space-x-2">
                                <button type="button" 
                                    class="w-8 h-8 flex items-center justify-center bg-slate-700 text-white rounded-md hover:bg-slate-800 transition-colors"
                                    onclick="openTutorSummary('{{ $tutor->tutorID }}')"
                                    title="View Summary">
                                    <i class="fas fa-file-invoice-dollar text-xs"></i>
                                </button>
                                <button type="button" 
                                    class="w-8 h-8 flex items-center justify-center bg-blue-100 text-blue-600 rounded-md hover:bg-blue-200 transition-colors"
                                    onclick="openSalaryHistory('{{ $tutor->tutorID }}')"
                                    title="View Salary History">
                                    <i class="fas fa-history text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-users text-4xl mb-4 opacity-50"></i>
                            <p class="text-lg font-medium">No tutors found</p>
                            <p class="text-sm">Try adjusting your search criteria</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($tutors->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
            <div class="text-sm text-gray-500">
                Showing {{ $tutors->firstItem() ?? 0 }} to {{ $tutors->lastItem() ?? 0 }} of {{ $tutors->total() }} results
            </div>
            <div class="flex items-center space-x-2">
                {{-- Previous Button --}}
                @if ($tutors->onFirstPage())
                    <button class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-400 flex items-center justify-center cursor-not-allowed" disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                @else
                    <a href="{{ $tutors->previousPageUrl() }}" class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-center">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                @endif

                {{-- Page Numbers --}}
                @foreach ($tutors->getUrlRange(1, $tutors->lastPage()) as $page => $url)
                    @if ($page == $tutors->currentPage())
                        <button class="w-8 h-8 bg-slate-700 text-white rounded text-sm flex items-center justify-center font-medium">
                            {{ $page }}
                        </button>
                    @else
                        <a href="{{ $url }}" class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-center">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach

                {{-- Next Button --}}
                @if ($tutors->hasMorePages())
                    <a href="{{ $tutors->nextPageUrl() }}" class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-center">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                @else
                    <button class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-400 flex items-center justify-center cursor-not-allowed" disabled>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                @endif
            </div>
        </div>
    @endif
</div>

<div id="tutorSummaryModal" class="fixed inset-0 z-50 flex items-center justify-center" style="display:none;">
    <div class="absolute inset-0 bg-black opacity-40"></div>
    <div class="relative bg-white rounded-lg shadow-lg w-full max-w-3xl mx-4 z-10 max-h-[90vh] overflow-y-auto" id="tutorSummaryModalContent">
        
    </div>
</div>

<script>
    (function () {
        const modal = document.getElementById('tutorSummaryModal');
        const modalContent = document.getElementById('tutorSummaryModalContent');
        const baseUrl = "{{ url('payroll/tutor') }}";
        const searchInput = document.getElementById('payrollSearch');
        const clearBtn = document.getElementById('clearPayrollSearch');

        // Search functionality
        if (searchInput && clearBtn) {
            searchInput.addEventListener('input', function() {
                clearBtn.classList.toggle('hidden', !this.value);
            });

            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                clearBtn.classList.add('hidden');
                document.getElementById('payrollFilterForm').submit();
            });

            // Show clear button if there's a value on load
            if (searchInput.value) {
                clearBtn.classList.remove('hidden');
            }
        }

        window.openTutorSummary = async function (tutorID) {
            if (!tutorID) return;
            try {
                modalContent.innerHTML = '<div class="p-6 text-center text-gray-500">Loading summary...</div>';
                modal.style.display = 'flex';

                const res = await fetch(`${baseUrl}/${encodeURIComponent(tutorID)}/summary`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) {
                    modalContent.innerHTML = '<div class="p-6 text-center text-red-500">Failed to load summary</div>';
                    return;
                }
                const html = await res.text();
                modalContent.innerHTML = html;
            } catch (err) {
                console.error(err);
                modalContent.innerHTML = '<div class="p-6 text-center text-red-500">Error loading summary</div>';
            }
        };

        window.openSalaryHistory = async function (tutorID) {
            if (!tutorID) return;
            try {
                modalContent.innerHTML = '<div class="p-6 text-center text-gray-500">Loading salary history...</div>';
                modal.style.display = 'flex';

                const res = await fetch(`${baseUrl}/${encodeURIComponent(tutorID)}/salary-history`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) {
                    modalContent.innerHTML = '<div class="p-6 text-center text-red-500">Failed to load salary history</div>';
                    return;
                }
                const data = await res.json();
                
                // Display salary history in modal
                let historyHtml = `
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-bold text-gray-800">Salary History</h2>
                            <button onclick="closeTutorSummary()" class="text-gray-500 hover:text-gray-700">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                        <div class="space-y-4">
                `;
                
                if (data.success && data.history && data.history.length > 0) {
                    data.history.forEach(record => {
                        historyHtml += `
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">Period: ${record.pay_period}</p>
                                        <p class="text-xs text-gray-500">Finalized: ${new Date(record.created_at).toLocaleDateString()}</p>
                                    </div>
                                    <p class="text-lg font-bold text-green-600">₱${parseFloat(record.total_amount).toLocaleString('en-PH', {minimumFractionDigits: 2})}</p>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    historyHtml += '<p class="text-center text-gray-500 py-8">No salary history found</p>';
                }
                
                historyHtml += `
                        </div>
                    </div>
                `;
                
                modalContent.innerHTML = historyHtml;
            } catch (err) {
                console.error(err);
                modalContent.innerHTML = '<div class="p-6 text-center text-red-500">Error loading salary history</div>';
            }
        };

        window.closeTutorSummary = function () {
            modal.style.display = 'none';
            modalContent.innerHTML = '';
        };

        window.finalizePayroll = function(tutorID, periodStart, periodEnd) {
            if (!confirm('Finalize and lock payroll for this period? This cannot be undone.')) {
                return;
            }

            fetch('{{ url("payroll/finalize") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    tutor_id: tutorID,
                    period_start: periodStart,
                    period_end: periodEnd
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✓ ' + data.message);
                    closeTutorSummary();
                    location.reload(); // Refresh to update totals
                } else {
                    alert('✗ ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error finalizing payroll');
            });
        };

        // Close when clicking overlay or pressing ESC
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeTutorSummary();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeTutorSummary();
        });
    })();
</script>

