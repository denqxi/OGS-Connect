<div class="p-6 border-b border-gray-200">
    <h2 class="text-xl font-semibold text-gray-800">Payroll Approval</h2>
</div>

<div class="p-6">
    <div class="overflow-x-auto">
        <table class="w-full" id="payrollsTable">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tutor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Account</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @if(isset($tutors) && $tutors->count())
                    @foreach($tutors as $tutor)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $tutor->full_name ?? $tutor->username }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $tutor->account?->account_name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm">
                                <div class="flex gap-2">
                                    <button type="button" class="px-3 py-1 bg-slate-700 text-white rounded text-xs" onclick="openTutorSummary('{{ $tutor->tutorID }}')">View Summary</button>
                                    <button type="button" class="px-3 py-1 bg-blue-600 text-white rounded text-xs" onclick="openSalaryHistory('{{ $tutor->tutorID }}')">View Salary History</button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-gray-500">No tutors found</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- Pagination links if provided --}}
    @if(isset($tutors) && method_exists($tutors, 'links'))
        <div class="mt-4">{{ $tutors->links() }}</div>
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

