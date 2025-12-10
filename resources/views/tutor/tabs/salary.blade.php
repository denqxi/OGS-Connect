<div class="p-6 border-b border-gray-200">
    <h2 class="text-xl font-semibold text-gray-800">My Salary</h2>
</div>

<!-- Summary Cards -->
<div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
    <!-- Total Earnings (All Time) -->
    <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-lg p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-green-600 mb-1">Total Earnings</p>
                <p class="text-2xl font-bold text-green-800">₱{{ number_format($totalEarnings, 2) }}</p>
                <p class="text-xs text-green-600 mt-1">All time</p>
            </div>
            <div class="bg-green-200 rounded-full p-3">
                <i class="fas fa-wallet text-green-700 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- This Month -->
    <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-blue-600 mb-1">This Month</p>
                <p class="text-2xl font-bold text-blue-800">₱{{ number_format($thisMonthEarnings, 2) }}</p>
                <p class="text-xs text-blue-600 mt-1">{{ \Carbon\Carbon::now()->format('F Y') }}</p>
            </div>
            <div class="bg-blue-200 rounded-full p-3">
                <i class="fas fa-calendar-alt text-blue-700 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Pending Payment -->
    <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 border border-yellow-200 rounded-lg p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-yellow-600 mb-1">Pending Payment</p>
                <p class="text-2xl font-bold text-yellow-800">₱{{ number_format($pendingPayment, 2) }}</p>
                <p class="text-xs text-yellow-600 mt-1">Awaiting approval</p>
            </div>
            <div class="bg-yellow-200 rounded-full p-3">
                <i class="fas fa-clock text-yellow-700 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Salary History Table -->
<div class="p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Salary History</h3>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Classes</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($salaryHistory as $record)
                    @php
                        $statusColor = 'bg-yellow-100 text-yellow-800';
                        $statusText = 'Pending';
                        
                        if ($record->status === 'approved') {
                            $statusColor = 'bg-green-100 text-green-800';
                            $statusText = 'Approved';
                        } elseif ($record->status === 'paid') {
                            $statusColor = 'bg-blue-100 text-blue-800';
                            $statusText = 'Paid';
                        } elseif ($record->status === 'rejected') {
                            $statusColor = 'bg-red-100 text-red-800';
                            $statusText = 'Rejected';
                        }
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ \Carbon\Carbon::parse($record->date)->format('F j, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $record->total_classes ?? 1 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ number_format($record->total_hours ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                            ₱{{ number_format($record->amount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColor }}">
                                {{ $statusText }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                            <button type="button" 
                                onclick="viewSalaryDetails({{ $record->id }})"
                                class="text-blue-600 hover:text-blue-800 font-medium">
                                View Details
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-3xl mb-2"></i>
                            <p>No salary history available yet</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if(isset($salaryHistory) && method_exists($salaryHistory, 'links'))
        <div class="mt-4">
            {{ $salaryHistory->links() }}
        </div>
    @endif
</div>

<script>
function viewSalaryDetails(id) {
    // TODO: Implement salary details modal
    alert('Salary details for record #' + id);
}
</script>
