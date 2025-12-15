<!-- CALL Modal -->
<div x-show="showModal" x-cloak
    style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 50;"
    class="flex justify-center items-center w-full h-full bg-black bg-opacity-50"
    x-transition>

    <div class="bg-[#0E335D] rounded-xl shadow-2xl w-full max-w-md mx-auto relative">
        <!-- Header -->
        <div class="flex justify-between items-center px-6 py-2 border-b border-gray-200" style="min-height: 40px;">
            <h2 class="text-white text-base font-bold">Initial Interview</h2>
            <button type="button"
                @click="
                    showModal = false;
                    showProgress = true;
                    $nextTick(() => {
                        $refs.progressBar.style.width = '0%';
                        setTimeout(() => { $refs.progressBar.style.width = '100%'; }, 10);
                        setTimeout(() => { 
                            showProgress = false; 
                            window.location.href = '{{ route('hiring_onboarding.index', ['tab' => 'new']) }}';
                        }, 2500);
                    });
                "
                class="text-white text-2xl font-bold">&times;
            </button>
        </div>
        <!-- Content -->
        <div class="bg-white rounded-b-xl px-6 py-4 text-center" style="min-height: 120px;">
            <div class="text-[#0E335D] text-sm font-medium mb-2">
                Calling <span>{{ $application->first_name ?? 'Applicant' }} {{ $application->last_name ?? 'Name' }}</span> for initial interview.
            </div>
            <div class="text-gray-700 text-sm font-bold mb-4">- Attempt {{ ($application->attempt_count ?? 0) + 1 }} -</div>
            <!-- Fail or Pass Button need improvement -->
            <div class="flex justify-center space-x-6">
                <button type="button" 
                    @click="showModal = false; showFailModal = true"
                    class="bg-[#F65353] text-white px-6 py-2 rounded-lg font-bold hover:opacity-90 text-sm">
                    Fail
                </button>
                <button type="button" 
                    @click="showModal = false; showPassModal = true"
                    class="bg-[#65DB7F] text-white px-6 py-2 rounded-lg font-bold hover:opacity-90 text-sm">
                    Pass
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Progress Modal -->
<div x-show="showProgress" x-cloak x-transition
    style="position: fixed; inset: 0; z-index: 60;"
    class="flex justify-center items-center w-full h-full bg-black bg-opacity-50">
    
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-xs mx-auto relative px-6 py-4 text-center">
        <div class="text-[#0E335D] text-sm font-medium mb-4">
            This call attempt will not be counted...
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2">
            <div x-ref="progressBar"
                 class="bg-[#0E335D] h-2.5 rounded-full transition-all duration-1000"
                 style="width:0%">
            </div>
        </div>
    </div>
</div>

<!-- Include Fail Modal -->
@include('hiring_onboarding.tabs.partials.modals.fail_modal')

<!-- Include Pass Modal -->
@include('hiring_onboarding.tabs.partials.modals.pass_modal')

