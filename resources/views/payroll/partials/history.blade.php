<div class="p-6 border-b border-gray-200">
    <h2 class="text-xl font-semibold text-gray-800">Approval History</h2>
</div>

<div class="p-6 border-b border-gray-200">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-medium text-gray-700">Recent Approvals / Rejections</h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full" id="approvalsTable">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tutor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Work Detail</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supervisor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Old Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">New Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Note</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($workApprovals ?? collect() as $a)
                    @php
                        $tutor = $a->workDetail?->tutor;
                        $displayDate = $a->approved_at ? (\Carbon\Carbon::parse($a->approved_at))->format('Y-m-d H:i') : ($a->created_at?->format('Y-m-d H:i') ?? 'N/A');
                    @endphp
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $displayDate }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $tutor?->full_name ?? ($tutor?->tusername ?? 'N/A') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $a->workDetail?->class_no ?? $a->workDetail?->work_type ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $a->supervisor?->full_name ?? $a->supervisor?->supID ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $a->old_status ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">{{ $a->new_status }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $a->note ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-history text-4xl mb-4 opacity-50"></i>
                            <p class="text-lg font-medium">No approval history found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if(isset($workApprovals) && method_exists($workApprovals, 'links'))
            <div class="mt-4">{{ $workApprovals->links() }}</div>
        @endif
    </div>
</div>
