<div id="payrollWorkDetailsInner">
    <div class="overflow-x-auto">
        <table class="w-full" id="tutorTable">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Time - End Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="tutorTableBody">
                @php
                    $rows = $workDetails ?? collect();
                @endphp

                @forelse($rows as $detail)
                    @php
                        $tutor = $detail->tutor;
                        // Format date as "December 10, 2025"
                        $dateObj = $detail->day ? \Carbon\Carbon::parse($detail->day) : ($detail->created_at ?? null);
                        $displayDate = $dateObj ? $dateObj->format('F j, Y') : 'N/A';
                        
                        $StartTime = $detail->start_time ? \Carbon\Carbon::parse($detail->start_time)->format('g:i A') : '—';
                        $EndTime = $detail->end_time ? \Carbon\Carbon::parse($detail->end_time)->format('g:i A') : '—';
                        $status = $detail->status ?? 'pending';
                        
                        // Per Class and Rate
                        $perClass = $detail->class_no ?? 'N/A';
                        $rate = $detail->rate_per_class ?? $detail->rate_per_hour ?? 'N/A';
                    @endphp
                    <tr class="hover:bg-gray-50 tutor-row" data-searchable="{{ strtolower(($tutor->tutorID ?? '') . ' ' . ($tutor->email ?? '')) }}">
                        <!-- Date -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $displayDate }}</td>
                        
                        <!-- Name -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $tutor?->full_name ?? ($tutor?->username ?? 'N/A') }}</td>
                        
                        <!-- Start Time - End Time -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $StartTime }} - {{ $EndTime }}</td>
                        
                        <!-- Rate -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ is_numeric($rate) ? '₱' . number_format($rate, 2) : $rate }}
                        </td>
                        
                        <!-- Actions -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex items-center justify-center space-x-2">
                                <button type="button" 
                                    class="w-8 h-8 flex items-center justify-center bg-blue-100 text-blue-600 rounded-md hover:bg-blue-200 transition-colors"
                                    onclick="openPayrollDetailModal({{ json_encode($detail) }}, '{{ $status }}')"
                                    title="View Details">
                                    <i class="fas fa-eye text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr id="noResultsRow">
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-users text-4xl mb-4 opacity-50"></i>
                            <p class="text-lg font-medium">No work details found</p>
                            <p class="text-sm">Try adjusting your search criteria</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination - Only show if there are pages --}}
        @if(isset($workDetails) && method_exists($workDetails, 'hasPages') && $workDetails->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $workDetails->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Payroll Work Detail Modal -->
<div id="payrollWorkDetailModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl mx-4 max-h-[90vh] overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="bg-[#0E335D] text-white px-6 py-4 flex items-center justify-between flex-shrink-0">
            <h2 class="text-xl font-bold">Work Details</h2>
            <button type="button" onclick="closePayrollDetailModal()" class="text-white hover:text-gray-200 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- Body -->
        <div class="overflow-y-auto flex-grow">
            <div class="p-6">
                <!-- Schedule Information Card -->
                <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg p-5 mb-6 border border-purple-200 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-calendar-alt text-purple-600 mr-2"></i>
                        Schedule Information
                    </h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
                            <p class="text-sm font-semibold text-gray-800" id="payroll-modal-date">-</p>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Day</label>
                            <p class="text-sm font-semibold text-gray-800" id="payroll-modal-day">-</p>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">School</label>
                            <p class="text-sm font-semibold text-gray-800" id="payroll-modal-school">-</p>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Class</label>
                            <p class="text-sm font-semibold text-gray-800" id="payroll-modal-class">-</p>
                        </div>
                    </div>
                </div>

                <!-- Tutor Information Card -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-5 mb-6 border border-blue-200 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-user text-blue-600 mr-2"></i>
                        Tutor Information
                    </h3>
                    
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Name</label>
                            <p class="text-sm font-semibold text-gray-800" id="payroll-modal-name">-</p>
                        </div>
                    </div>
                </div>

                <!-- Time & Rate Information Card -->
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-5 mb-6 border border-green-200 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-clock text-green-600 mr-2"></i>
                        Time & Rate Details
                    </h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Start Time</label>
                            <p class="text-sm font-semibold text-gray-800" id="payroll-modal-start-time">-</p>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">End Time</label>
                            <p class="text-sm font-semibold text-gray-800" id="payroll-modal-end-time">-</p>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Hours</label>
                            <p class="text-sm font-semibold text-gray-800" id="payroll-modal-hours">-</p>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Rate</label>
                            <p class="text-sm font-semibold text-gray-800" id="payroll-modal-rate">-</p>
                        </div>
                        
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Amount</label>
                            <p class="text-lg font-bold text-green-700" id="payroll-modal-amount">-</p>
                        </div>
                    </div>
                </div>

                <!-- Proof of Work Card -->
                <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg p-5 border border-purple-200 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-image text-purple-600 mr-2"></i>
                        Proof of Work
                    </h3>
                    
                    <div class="flex justify-center" id="payroll-modal-proof-container">
                        <p class="text-gray-500 text-center py-8">No proof image uploaded</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer with Actions -->
        <div class="px-6 py-4 bg-gray-50 flex justify-between items-center border-t flex-shrink-0">
            <div class="flex gap-3" id="payroll-modal-action-buttons">
                <!-- Dynamic buttons will be inserted here -->
            </div>
            
            <button type="button" onclick="closePayrollDetailModal()" class="px-6 py-2 bg-gray-500 text-white rounded-md font-medium hover:bg-gray-600 transition-colors">
                Close
            </button>
        </div>
    </div>
</div>
