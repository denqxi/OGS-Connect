<div id="payrollWorkDetailsInner">
    <div class="overflow-x-auto">
        <table class="w-full" id="tutorTable">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Work Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proof</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="tutorTableBody">
                @php
                    $rows = $workDetails ?? collect();
                @endphp

                @forelse($rows as $detail)
                    @php
                        $tutor = $detail->tutor;
                        $displayDate = $detail->day ?? $detail->created_at?->format('Y-m-d') ?? 'N/A';
                        $StartTime = $detail->start_time ?? 'N/A';
                        $EndTime = $detail->end_time ?? 'N/A';
                        $classNo = $detail->class_no ?? ($detail->work_type ?? '—');
                        $rate = ($detail->work_type ?? '') === 'hourly'
                            ? ($detail->rate_per_hour ?? 'N/A')
                            : ($detail->rate_per_class ?? 'N/A');
                        $status = $detail->status ?? 'pending';
                    @endphp
                    <tr class="hover:bg-gray-50 tutor-row" data-searchable="{{ strtolower(($tutor->tutorID ?? '') . ' ' . ($tutor->email ?? '') . ' ' . ($classNo ?? '')) }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $displayDate }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $tutor?->full_name ?? ($tutor?->username ?? 'N/A') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $StartTime }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $EndTime }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $classNo }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">₱{{ $rate }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $detail->duration_hours ?? '0' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">₱{{ $detail->computed_amount ?? '0' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            @php
                                $proofPath = $detail->proof_image ?? $detail->screenshot ?? null;
                            @endphp
                            @if($proofPath)
                                <a href="{{ asset('storage/' . $proofPath) }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-image"></i> View
                                </a>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-400',
                                        'approved' => 'bg-green-500',
                                        'reject' => 'bg-red-500',
                                    ];
                                    $circleColor = $statusColors[strtolower($status)] ?? 'bg-gray-500';
                                @endphp
                                <span class="w-2.5 h-2.5 rounded-full {{ $circleColor }}"></span>
                                <span class="text-xs font-medium text-gray-500">{{ ucfirst($status) }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                            <div class="flex items-center space-x-2">
                                <button type="button" 
                                    class="px-4 py-1.5 bg-indigo-600 text-white rounded text-xs hover:bg-indigo-700 transition-colors"
                                    onclick="approveWorkDetail('{{ $detail->id ?? $detail->work_detail_id ?? $detail->id }}')">Approve</button>
                                <button type="button" 
                                    class="px-4 py-1.5 bg-red-100 text-red-600 rounded text-xs hover:bg-red-200 transition-colors"
                                    onclick="rejectWorkDetail('{{ $detail->id ?? $detail->work_detail_id ?? $detail->id }}')">Reject</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr id="noResultsRow">
                        <td colspan="11" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-users text-4xl mb-4 opacity-50"></i>
                            <p class="text-lg font-medium">No work details found</p>
                            <p class="text-sm">Try adjusting your search criteria</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        @if(isset($workDetails) && method_exists($workDetails, 'links'))
            @include('payroll.partials.work_details_pagination', ['workDetails' => $workDetails])
        @else
            <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between h-16 w-full" id="paginationSection">
                <div class="text-sm text-gray-500">Showing 0 results</div>
                <div class="flex items-center justify-center space-x-2 w-[300px]">
                    <button class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 flex items-center justify-center" disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="w-8 h-8 bg-slate-700 text-white rounded text-sm flex items-center justify-center">1</button>
                    <button class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 flex items-center justify-center" disabled>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
