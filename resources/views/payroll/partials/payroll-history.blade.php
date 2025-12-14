
<div class="p-6 border-b border-gray-200">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-medium text-gray-700">Payslip Submissions</h3>
    </div>

    <form method="GET" action="{{ route('payroll.index') }}" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-3">
        <input type="hidden" name="tab" value="payroll-history">
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
            <a href="{{ route('payroll.index', ['tab' => 'payroll-history']) }}" class="px-3 py-2 text-sm text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">Reset</a>
        </div>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full" id="payrollHistoryTable">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('payroll.index', array_merge(request()->query(), ['tab' => 'payroll-history', 'sort' => request('sort') === 'submitted_at' && request('direction') === 'desc' ? '' : 'submitted_at', 'direction' => request('sort') === 'submitted_at' ? (request('direction') === 'asc' ? 'desc' : '') : 'asc'])) }}'">
                        <div class="flex items-center gap-1">
                            Submitted Date
                            @if(request('sort') === 'submitted_at')
                                <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                            @else
                                <i class="fas fa-sort text-xs opacity-30"></i>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('payroll.index', array_merge(request()->query(), ['tab' => 'payroll-history', 'sort' => request('sort') === 'tutor_name' && request('direction') === 'desc' ? '' : 'tutor_name', 'direction' => request('sort') === 'tutor_name' ? (request('direction') === 'asc' ? 'desc' : '') : 'asc'])) }}'">
                        <div class="flex items-center gap-1">
                            Tutor Name
                            @if(request('sort') === 'tutor_name')
                                <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                            @else
                                <i class="fas fa-sort text-xs opacity-30"></i>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('payroll.index', array_merge(request()->query(), ['tab' => 'payroll-history', 'sort' => request('sort') === 'pay_period' && request('direction') === 'desc' ? '' : 'pay_period', 'direction' => request('sort') === 'pay_period' ? (request('direction') === 'asc' ? 'desc' : '') : 'asc'])) }}'">
                        <div class="flex items-center gap-1">
                            Pay Period
                            @if(request('sort') === 'pay_period')
                                <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                            @else
                                <i class="fas fa-sort text-xs opacity-30"></i>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('payroll.index', array_merge(request()->query(), ['tab' => 'payroll-history', 'sort' => request('sort') === 'total_amount' && request('direction') === 'desc' ? '' : 'total_amount', 'direction' => request('sort') === 'total_amount' ? (request('direction') === 'asc' ? 'desc' : '') : 'asc'])) }}'">
                        <div class="flex items-center gap-1">
                            Total Amount
                            @if(request('sort') === 'total_amount')
                                <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                            @else
                                <i class="fas fa-sort text-xs opacity-30"></i>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('payroll.index', array_merge(request()->query(), ['tab' => 'payroll-history', 'sort' => request('sort') === 'submission_type' && request('direction') === 'desc' ? '' : 'submission_type', 'direction' => request('sort') === 'submission_type' ? (request('direction') === 'asc' ? 'desc' : '') : 'asc'])) }}'">
                        <div class="flex items-center gap-1">
                            Submission Type
                            @if(request('sort') === 'submission_type')
                                <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                            @else
                                <i class="fas fa-sort text-xs opacity-30"></i>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recipient/Details</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('payroll.index', array_merge(request()->query(), ['tab' => 'payroll-history', 'sort' => request('sort') === 'status' && request('direction') === 'desc' ? '' : 'status', 'direction' => request('sort') === 'status' ? (request('direction') === 'asc' ? 'desc' : '') : 'asc'])) }}'">
                        <div class="flex items-center gap-1">
                            Status
                            @if(request('sort') === 'status')
                                <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                            @else
                                <i class="fas fa-sort text-xs opacity-30"></i>
                            @endif
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($payrollHistory ?? collect() as $record)
                    @php
                        $tutor = $record->tutor;
                        $tutorName = $tutor?->applicant?->first_name . ' ' . $tutor?->applicant?->last_name;
                        $submittedDate = $record->submitted_at ? $record->submitted_at->format('M d, Y H:i') : 'N/A';
                        
                        // For finalized status, show em dash for submission type
                        $isFinalized = ($record->status === 'finalized');
                        
                        $typeColors = [
                            'email' => 'bg-blue-100 text-blue-800',
                            'pdf' => 'bg-purple-100 text-purple-800',
                            'print' => 'bg-orange-100 text-orange-800'
                        ];
                        $typeBadgeColor = $typeColors[$record->submission_type] ?? 'bg-gray-100 text-gray-800';
                        $typeLabel = $isFinalized ? '—' : match($record->submission_type) {
                            'email' => 'Email',
                            'pdf' => 'PDF Download',
                            'print' => 'Print',
                            default => ucfirst($record->submission_type)
                        };
                        
                        // Determine status color
                        $statusColor = match($record->status ?? '') {
                            'sent' => 'bg-green-100 text-green-800',
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'failed' => 'bg-red-100 text-red-800',
                            'draft' => 'bg-gray-100 text-gray-800',
                            'finalized' => 'bg-indigo-100 text-indigo-800',
                            default => 'bg-gray-100 text-gray-800'
                        };
                        
                        $statusLabel = match($record->status ?? '') {
                            'finalized' => 'Finalized',
                            default => ucfirst($record->status ?? 'unknown')
                        };
                        
                        // Recipient/Details
                        $details = $isFinalized ? '—' : ($record->submission_type === 'email' ? $record->recipient_email : $record->notes ?? '—');
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $submittedDate }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $tutorName }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $record->pay_period ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">₱{{ number_format($record->total_amount ?? 0, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($isFinalized)
                                <span class="text-gray-500">{{ $typeLabel }}</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeBadgeColor }}">
                                    {{ $typeLabel }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            @if($isFinalized)
                                <span class="text-gray-500">{{ $details }}</span>
                            @elseif($record->submission_type === 'email' && $record->recipient_email)
                                <a href="mailto:{{ $record->recipient_email }}" class="text-blue-600 hover:underline">
                                    {{ $record->recipient_email }}
                                </a>
                            @else
                                {{ $details }}
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-envelope-open text-4xl mb-4 opacity-50"></i>
                            <p class="text-lg font-medium">No payroll submissions found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if(isset($payrollHistory) && method_exists($payrollHistory, 'links'))
            <div class="mt-4">{{ $payrollHistory->links() }}</div>
        @endif
    </div>
</div>

