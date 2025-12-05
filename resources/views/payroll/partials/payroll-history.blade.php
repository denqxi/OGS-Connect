<div class="p-6 border-b border-gray-200">
    <h2 class="text-xl font-semibold text-gray-800">Payroll History</h2>
</div>

<div class="p-6 border-b border-gray-200">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-medium text-gray-700">Payslip Submissions</h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full" id="payrollHistoryTable">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tutor Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pay Period</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submission Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recipient/Details</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($payrollHistory ?? collect() as $record)
                    @php
                        $tutor = $record->tutor;
                        $tutorName = $tutor?->applicant?->first_name . ' ' . $tutor?->applicant?->last_name;
                        $submittedDate = $record->submitted_at ? $record->submitted_at->format('Y-m-d H:i') : 'N/A';
                        
                        $typeColors = [
                            'email' => 'bg-blue-100 text-blue-800',
                            'pdf' => 'bg-purple-100 text-purple-800',
                            'print' => 'bg-orange-100 text-orange-800'
                        ];
                        $typeBadgeColor = $typeColors[$record->submission_type] ?? 'bg-gray-100 text-gray-800';
                        $typeLabel = match($record->submission_type) {
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
                            default => 'bg-gray-100 text-gray-800'
                        };
                        
                        // Recipient/Details
                        $details = $record->submission_type === 'email' ? $record->recipient_email : $record->notes ?? '—';
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $submittedDate }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $tutorName }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $record->pay_period ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeBadgeColor }}">
                                {{ $typeLabel }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            @if($record->submission_type === 'email' && $record->recipient_email)
                                <a href="mailto:{{ $record->recipient_email }}" class="text-blue-600 hover:underline">
                                    {{ $record->recipient_email }}
                                </a>
                            @else
                                {{ $details }}
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                {{ ucfirst($record->status ?? 'unknown') }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
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

