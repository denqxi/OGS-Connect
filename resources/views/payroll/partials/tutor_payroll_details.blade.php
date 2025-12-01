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
                        $rate = $detail->rate_per_class ?? 'N/A';
                        $status = $detail->status ?? 'pending';
                    @endphp
                    <tr class="hover:bg-gray-50 tutor-row" data-searchable="{{ strtolower(($tutor->tutorID ?? '') . ' ' . ($tutor->email ?? '') . ' ' . ($classNo ?? '')) }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $displayDate }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $tutor?->full_name ?? ($tutor?->tusername ?? 'N/A') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $StartTime }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $EndTime }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $classNo }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">₱{{ $rate }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">{{ ucfirst($status) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                            <div class="flex items-center space-x-2">
                                @if(!empty($detail->screenshot))
                                    <img src="{{ asset('storage/' . ltrim($detail->screenshot, '/')) }}" alt="screenshot" class="w-10 h-10 object-cover rounded twd-table-thumb cursor-pointer border" style="max-width:40px; max-height:40px;" />
                                @endif
                                <div>
                                    <button type="button" class="px-3 py-1 bg-indigo-600 text-white rounded text-xs" onclick="approveWorkDetail('{{ $detail->id ?? $detail->work_detail_id ?? $detail->id }}')">Approve</button>
                                    <button type="button" class="px-3 py-1 bg-red-100 text-red-600 rounded text-xs" onclick="rejectWorkDetail('{{ $detail->id ?? $detail->work_detail_id ?? $detail->id }}')">Reject</button>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr id="noResultsRow">
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
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
