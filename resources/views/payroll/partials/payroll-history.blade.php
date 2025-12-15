
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
                        $dateObj = $record->submitted_at ?? null;
                        $submittedDate = $dateObj ? $dateObj->format('F j, Y') : 'N/A';
                        
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
                        
                        // Determine status color for circle
                        $statusCircleColor = match($record->status ?? '') {
                            'sent' => 'bg-green-500',
                            'pending' => 'bg-yellow-500',
                            'failed' => 'bg-red-500',
                            'draft' => 'bg-gray-500',
                            'finalized' => 'bg-indigo-500',
                            default => 'bg-gray-500'
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            @php
                                $typeLabel = $isFinalized ? '—' : match($record->submission_type) {
                                    'email' => 'Email',
                                    'pdf' => 'PDF Download',
                                    'print' => 'Print',
                                    default => ucfirst($record->submission_type)
                                };
                            @endphp

                            <span class="{{ $isFinalized ? 'text-gray-500' : '' }}">
                                {{ $typeLabel }}
                            </span>
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $statusCircleColor }}"></span>
                                <span>{{ $statusLabel }}</span>
                            </div>
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

        @php
            $queryParams = request()->query();
            unset($queryParams['page']);
            $baseUrl = route('payroll.index', array_merge($queryParams, ['tab' => 'payroll-history']));
            $separator = strpos($baseUrl, '?') !== false ? '&' : '?';
            $currentPage = isset($payrollHistory) && method_exists($payrollHistory, 'currentPage') ? $payrollHistory->currentPage() : 1;
            $lastPage = isset($payrollHistory) && method_exists($payrollHistory, 'lastPage') ? $payrollHistory->lastPage() : 1;
            $totalRows = isset($payrollHistory) && method_exists($payrollHistory, 'total') ? $payrollHistory->total() : (isset($payrollHistory) ? $payrollHistory->count() : 0);
            $useCompactPagination = $lastPage > 7;
            if (!$useCompactPagination) {
                $startPage = max(1, $currentPage - 2);
                $endPage = min($lastPage, $currentPage + 2);
                if ($endPage - $startPage < 4) {
                    if ($startPage == 1) {
                        $endPage = min($lastPage, $startPage + 4);
                    } else {
                        $startPage = max(1, $endPage - 4);
                    }
                }
            }
        @endphp
        @if(isset($payrollHistory) && method_exists($payrollHistory, 'hasPages') && $payrollHistory->hasPages() && $totalRows >= 5)
            <div class="mt-4 px-6 py-4 border-t border-gray-200 flex items-center justify-between h-16 w-full">
                <div class="text-sm text-gray-500">
                    @if($totalRows > 0)
                        Showing {{ $payrollHistory->firstItem() }} to {{ $payrollHistory->lastItem() }} of {{ $totalRows }} results
                    @else
                        Showing 0 results
                    @endif
                </div>
                <div class="flex items-center justify-center space-x-2 w-[300px]">
                    @if ($payrollHistory->onFirstPage())
                        <button class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 flex items-center justify-center" disabled>
                            <i class="fas fa-chevron-left"></i>
                        </button>
                    @else
                        <a href="{{ $baseUrl . $separator . 'page=' . ($currentPage - 1) }}"
                           class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-center transition-colors"
                           data-page="{{ $currentPage - 1 }}">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    @endif

                    @if($useCompactPagination)
                        <button class="w-8 h-8 bg-slate-700 text-white rounded text-sm flex items-center justify-center font-medium">{{ $currentPage }}</button>
                    @else
                        @if($startPage > 1)
                            <a href="{{ $baseUrl . $separator . 'page=1' }}"
                               class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-center transition-colors"
                               data-page="1">1</a>
                            @if($startPage > 2)
                                <span class="w-8 h-8 flex items-center justify-center text-gray-400">...</span>
                            @endif
                        @endif

                        @for($page = $startPage; $page <= $endPage; $page++)
                            @if ($page == $currentPage)
                                <button class="w-8 h-8 bg-slate-700 text-white rounded text-sm flex items-center justify-center font-medium">{{ $page }}</button>
                            @else
                                <a href="{{ $baseUrl . $separator . 'page=' . $page }}"
                                   class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-center transition-colors"
                                   data-page="{{ $page }}">{{ $page }}</a>
                            @endif
                        @endfor

                        @if($endPage < $lastPage)
                            @if($endPage < $lastPage - 1)
                                <span class="w-8 h-8 flex items-center justify-center text-gray-400">...</span>
                            @endif
                            <a href="{{ $baseUrl . $separator . 'page=' . $lastPage }}"
                               class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-center transition-colors"
                               data-page="{{ $lastPage }}">{{ $lastPage }}</a>
                        @endif
                    @endif

                    @if ($payrollHistory->hasMorePages())
                        <a href="{{ $baseUrl . $separator . 'page=' . ($currentPage + 1) }}"
                           class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-center transition-colors"
                           data-page="{{ $currentPage + 1 }}">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    @else
                        <button class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 flex items-center justify-center" disabled>
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    @endif
                </div>
            </div>
        @elseif($totalRows > 0)
            <div class="mt-4 px-6 py-4 border-t border-gray-200">
                <div class="text-sm text-gray-500">
                    Showing {{ isset($payrollHistory) && method_exists($payrollHistory, 'firstItem') ? $payrollHistory->firstItem() : 1 }} to {{ isset($payrollHistory) && method_exists($payrollHistory, 'lastItem') ? $payrollHistory->lastItem() : $totalRows }} of {{ $totalRows }} results
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Date validation handled in parent index view -->

