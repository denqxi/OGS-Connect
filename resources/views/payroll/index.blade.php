@extends('layouts.app')
@section('title', 'OGS Connect/Payroll')

@section('content')
    @include('layouts.header', ['pageTitle' => 'Payroll'])

    <div class="bg-white border-b border-gray-200 px-4 md:px-6 rounded-xl shadow-sm mb-6">
        <nav class="flex overflow-x-auto md:space-x-8 no-scrollbar">
            <a href="{{ route('payroll.index', ['tab' => 'payroll']) }}"
                class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 
               {{ request('tab', 'payroll') == 'payroll' ? 'border-b-2 border-[#0E335D] text-[#0E335D]' : 'text-[#0E335D] hover:text-[#0E335D]/70' }} 
               font-medium text-sm md:text-base flex items-center space-x-2">
                <i class="fas fa-user-clock"></i>
                <span class="hidden sm:inline">Tutor Work Details</span>
            </a>

            

            <a href="{{ route('payroll.index', ['tab' => 'payrolls']) }}"
                class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 
               {{ request('tab') == 'payrolls' ? 'border-b-2 border-[#0E335D] text-[#0E335D]' : 'text-[#0E335D] hover:text-[#0E335D]/70' }} 
               font-medium text-sm md:text-base flex items-center space-x-2">
                <i class="fas fa-history"></i>
                <span class="hidden sm:inline">Payroll</span>
            </a>
            <a href="{{ route('payroll.index', ['tab' => 'history']) }}"
                class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 
               {{ request('tab') == 'history' || request('tab') == 'payroll-history' ? 'border-b-2 border-[#0E335D] text-[#0E335D]' : 'text-[#0E335D] hover:text-[#0E335D]/70' }} 
               font-medium text-sm md:text-base flex items-center space-x-2">
                <i class="fas fa-history"></i>
                <span class="hidden sm:inline">History</span>
            </a>
        </nav>

        
    </div>

    <!-- Subtabs for History Section -->
    @if(request('tab') == 'history' || request('tab') == 'payroll-history')
        <div class="bg-gray-50 border-b border-gray-200 px-4 md:px-6">
            <nav class="flex overflow-x-auto md:space-x-4 no-scrollbar">
                <a href="{{ route('payroll.index', ['tab' => 'history']) }}"
                    class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 
                   {{ request('tab', 'history') == 'history' ? 'border-b-2 border-[#0E335D] text-[#0E335D]' : 'text-gray-600 hover:text-gray-800' }} 
                   font-medium text-sm md:text-base flex items-center space-x-2">
                    <i class="fas fa-check-circle"></i>
                    <span class="hidden sm:inline">Approval History</span>
                </a>
                <a href="{{ route('payroll.index', ['tab' => 'payroll-history']) }}"
                    class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 
                   {{ request('tab') == 'payroll-history' ? 'border-b-2 border-[#0E335D] text-[#0E335D]' : 'text-gray-600 hover:text-gray-800' }} 
                   font-medium text-sm md:text-base flex items-center space-x-2">
                    <i class="fas fa-file-invoice"></i>
                    <span class="hidden sm:inline">Payroll History</span>
                </a>
            </nav>
        </div>
    @endif

    <div>
        <div class="max-w-full mx-auto">
            <div class="bg-white rounded-lg shadow-sm p-4 md:p-6">
                @if (request('tab', 'payroll') == 'payroll')
                    @include('payroll.partials.tutor-payroll')
                @elseif(request('tab') == 'payrolls')
                    @include('payroll.partials.payrolls')
                @elseif(request('tab') == 'history')
                    @include('payroll.partials.history')
                @elseif(request('tab') == 'payroll-history')
                    @include('payroll.partials.payroll-history')
                @endif
        </div>
    </div>
    <script>
    window.printPayslip = function () {
        var content = document.getElementById('payslipContent').innerHTML;
        var printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head><title>Payslip</title>');
        printWindow.document.write(
            '<style>body{font-family:Arial,Helvetica,sans-serif;padding:20px;color:#111}.header{display:flex;justify-content:space-between;align-items:center}.header .title{font-size:18px;font-weight:700}.header .meta{font-size:12px;color:#666}table{width:100%;border-collapse:collapse;margin-top:8px}th,td{border:1px solid #e5e7eb;padding:8px;text-align:left}th{background:#f3f4f6;font-weight:600}.text-right{text-align:right}.totals{font-weight:700}</style>'
        );
        printWindow.document.write('</head><body>');
        printWindow.document.write(content);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        setTimeout(function () {
            printWindow.print();
        }, 250);
    };

    // notification ug nasent ang paylsip, yey!
function showPayslipModal(message, success = true) {
    var overlay = document.getElementById('payslipModalOverlay');
    var box = document.getElementById('payslipModalBox');
    var msg = document.getElementById('payslipModalMessage');
    var icon = document.getElementById('payslipModalIcon');

    if (!overlay || !box) return alert(message);

    // Set message
    msg.textContent = message || '';

    // Set icon and colors
    if (success) {
        icon.innerHTML = '✔'; 
        box.classList.remove('border-red-400', 'bg-red-50', 'text-red-700');
        box.classList.add('border-emerald-500', 'bg-emerald-50', 'text-emerald-800');
    } else {
        icon.innerHTML = '⚠'; 
        box.classList.remove('border-emerald-500', 'bg-emerald-50', 'text-emerald-800');
        box.classList.add('border-red-400', 'bg-red-50', 'text-red-700');
    }

    box.classList.add('animate-fadeIn', 'scale-105');
    setTimeout(() => box.classList.remove('scale-105'), 300);

    overlay.classList.remove('hidden');

    // Auto-hide after 3 seconds
    clearTimeout(window._payslipModalTimeout);
    window._payslipModalTimeout = setTimeout(function () {
        overlay.classList.add('hidden');
    }, 3000);
}


    window.emailPayslip = function (tutorID) {
    fetch('/payroll/tutor/' + tutorID + '/send-email', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
    })
    .then(res => res.json())
    .then(data => {
            if (data.success) {
                showPayslipModal(data.message || 'Payslip emailed successfully', true);
            } else {
                showPayslipModal('Failed to send payslip: ' + (data.message || 'Unknown error'), false);
            }
    })
    .catch(err => {
        console.error(err);
            showPayslipModal('An error occurred while sending the payslip.', false);
    });
};
</script>

<!-- Modal overlay (Tailwind) -->
<div id="payslipModalOverlay" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center px-4 py-6 pointer-events-none">
    <div id="payslipModalBox" class="pointer-events-auto w-full max-w-md bg-white border rounded-lg shadow-lg p-4 flex items-start gap-3 border-emerald-500">
        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center" id="payslipModalIcon">✔</div>
        <div class="flex-1">
            <div id="payslipModalMessage" class="text-sm text-gray-800">Payslip emailed successfully</div>
        </div>
        <button onclick="document.getElementById('payslipModalOverlay').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 ml-3">✕</button>
    </div>
</div>

@endsection
