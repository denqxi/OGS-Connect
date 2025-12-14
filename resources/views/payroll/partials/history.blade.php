
<div class="p-6 border-b border-gray-200">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-medium text-gray-700">Recent Approvals / Rejections</h3>
    </div>

    <form method="GET" action="{{ route('payroll.index') }}" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-3">
        <input type="hidden" name="tab" value="history">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Tutor Name</label>
            <input type="text" name="tutor_name" value="{{ request('tutor_name') }}" placeholder="Search tutor" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Month</label>
            <select name="month" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                <option value="">All</option>
                @for ($m = 1; $m <= 12; $m++)
                    @php $value = str_pad($m, 2, '0', STR_PAD_LEFT); @endphp
                    <option value="{{ $value }}" {{ request('month') == $value ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Year</label>
            <select name="year" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                <option value="">All</option>
                @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                    <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="px-4 py-2 bg-[#0E335D] text-white text-sm rounded-md hover:bg-[#0c294a]">Filter</button>
            <a href="{{ route('payroll.index', ['tab' => 'history']) }}" class="px-3 py-2 text-sm text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">Reset</a>
        </div>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full" id="approvalsTable">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('payroll.index', array_merge(request()->query(), ['tab' => 'history', 'sort' => request('sort') === 'approved_at' && request('direction') === 'desc' ? '' : 'approved_at', 'direction' => request('sort') === 'approved_at' ? (request('direction') === 'asc' ? 'desc' : '') : 'asc'])) }}'">
                        <div class="flex items-center gap-1">
                            Date
                            @if(request('sort') === 'approved_at')
                                <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                            @else
                                <i class="fas fa-sort text-xs opacity-30"></i>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('payroll.index', array_merge(request()->query(), ['tab' => 'history', 'sort' => request('sort') === 'tutor_name' && request('direction') === 'desc' ? '' : 'tutor_name', 'direction' => request('sort') === 'tutor_name' ? (request('direction') === 'asc' ? 'desc' : '') : 'asc'])) }}'">
                        <div class="flex items-center gap-1">
                            Tutor
                            @if(request('sort') === 'tutor_name')
                                <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                            @else
                                <i class="fas fa-sort text-xs opacity-30"></i>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Work Detail</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supervisor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Old Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('payroll.index', array_merge(request()->query(), ['tab' => 'history', 'sort' => request('sort') === 'new_status' && request('direction') === 'desc' ? '' : 'new_status', 'direction' => request('sort') === 'new_status' ? (request('direction') === 'asc' ? 'desc' : '') : 'asc'])) }}'">
                        <div class="flex items-center gap-1">
                            New Status
                            @if(request('sort') === 'new_status')
                                <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                            @else
                                <i class="fas fa-sort text-xs opacity-30"></i>
                            @endif
                        </div>
                    </th>
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
