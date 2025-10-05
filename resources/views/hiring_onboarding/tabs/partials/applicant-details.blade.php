<!-- Back Button -->
<div class="mb-4 flex justify-end">
    <a href="{{ route('hiring_onboarding.index', ['tab' => 'new']) }}"
        class="flex items-center space-x-2 px-4 py-2 bg-[#606979] text-white rounded-full text-sm font-medium 
              hover:bg-[#4f5a66] transform transition duration-200 hover:scale-105"
        style="width: 200px; justify-content: center;">
        <i class="fas fa-arrow-left"></i>
        <span>Back</span>
    </a>
</div>

<!-- Form Header -->
<div class="bg-[#65DB7F] shadow-lg text-[#0E335D] font-bold text-center text-2xl rounded-md py-3 mb-6">
    APPLICANT DETAILS
</div>

<!-- Form Container -->
<div class="bg-white rounded-xl shadow-md p-6 sm:p-10">

    <form action="#" method="POST" class="space-y-6">
        <!-- Personal Information -->
        <div>
            <h3 class="font-semibold text-[#0E335D] text-lg mb-3">Personal Information</h3>
            <div class="grid md:grid-cols-3 gap-4 items-start">
                <!-- Row 1 -->
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">First Name <span
                            class="text-red-600">*</span></label>
                    <input type="text" value="Ivan Josh" class="p-2 border rounded-md w-full">
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Last Name <span
                            class="text-red-600">*</span></label>
                    <input type="text" value="Dumadapat" class="p-2 border rounded-md w-full">
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Birth Date <span
                            class="text-red-600">*</span></label>
                    <input type="date" value="2001-05-15" class="p-2 border rounded-md w-full">
                </div>

                <!-- Row 2 -->
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Address <span class="text-red-600">*</span></label>
                    <input type="text" value="Davao City, Philippines" class="p-2 border rounded-md w-full">
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Contact Number <span
                            class="text-red-600">*</span></label>
                    <input type="text" value="+63 912 345 6789" class="p-2 border rounded-md w-full">
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Email <span class="text-red-600">*</span></label>
                    <input type="email" value="ivan.dumadapat@example.com" class="p-2 border rounded-md w-full">
                </div>

                <!-- Row 3 -->
                <div class="flex flex-col md:col-span-3">
                    <label class="text-sm font-normal text-gray-500">MS Teams (e.g., live:.cid...)</label>
                    <input type="text" value="live:.cid.1234567890abcdef" class="p-2 border rounded-md w-full">
                </div>
            </div>
        </div>

        <hr class="my-10">

        <!-- Education & Work Background -->
        <div>
            <h3 class="font-semibold text-[#0E335D] text-lg mb-3">Education & Work Background</h3>
            <div class="grid md:grid-cols-2 gap-4 items-start">
                <!-- Highest Educational Attainment -->
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Highest Educational Attainment <span
                            class="text-red-600">*</span></label>
                    <select class="p-2 border rounded-md w-full">
                        <option>College Graduate / Bachelor's Degree</option>
                    </select>
                </div>

                <!-- ESL Teaching Experience -->
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">ESL Teaching Experience <span
                            class="text-red-600">*</span></label>
                    <select class="p-2 border rounded-md w-full">
                        <option>3–4 years</option>
                    </select>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <!-- Requirements -->
        <div>
            <h3 class="font-semibold text-[#0E335D] text-lg mb-3">Requirements</h3>
            <div class="grid md:grid-cols-2 gap-4">
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Resume Link (GDrive / GDocs) <span
                            class="text-red-600">*</span></label>
                    <input type="text" value="https://drive.google.com/file/resume123"
                        class="p-2 border rounded-md w-full">
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Intro Video (GDrive Link) <span
                            class="text-red-600">*</span></label>
                    <input type="text" value="https://drive.google.com/file/introvideo456"
                        class="p-2 border rounded-md w-full">
                </div>

                <!-- Work Setup -->
                <div class="md:col-span-2 mt-2">
                    <label class="text-sm font-normal text-gray-500 font-semibold">Work Setup:</label>
                </div>
                <div class="flex space-x-6 md:col-span-2">
                    <label class="inline-flex items-center">
                        <input type="radio" name="workSetup" value="home" checked onclick="toggleWorkSetup()">
                        <span class="ml-2">Work from Home</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="workSetup" value="site" onclick="toggleWorkSetup()">
                        <span class="ml-2">Work at Site</span>
                    </label>
                </div>

                <div class="grid md:grid-cols-3 gap-4 mt-4 md:col-span-2">
                    <div class="flex flex-col">
                        <label class="text-sm font-normal text-gray-500">Ookla Speedtest (GDrive Link)</label>
                        <input id="speedtestField" type="text" value="https://drive.google.com/file/speedtest789"
                            class="p-2 border rounded-md w-full">
                    </div>
                    <div class="flex flex-col">
                        <label class="text-sm font-normal text-gray-500">Main Device Specs (dxdiag Screenshot)</label>
                        <input id="mainDeviceField" type="text" value="https://drive.google.com/file/mainDevice"
                            class="p-2 border rounded-md w-full">
                    </div>
                    <div class="flex flex-col">
                        <label class="text-sm font-normal text-gray-500">Backup Device Specs (dxdiag
                            Screenshot)</label>
                        <input id="backupDeviceField" type="text"
                            value="https://drive.google.com/file/backupDevice" class="p-2 border rounded-md w-full">
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <!-- How Did You Hear About Us? -->
        <div>
            <h3 class="font-semibold text-[#0E335D] text-lg mb-3">How Did You Hear About Us?</h3>
            <div class="flex flex-col md:flex-row md:items-center md:space-x-4">
                <div class="flex space-x-6 mb-4 md:mb-0">
                    <label class="inline-flex items-center">
                        <input type="radio" checked>
                        <span class="ml-2">Referral</span>
                    </label>
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Referrer Name <span
                            class="text-red-600">*</span></label>
                    <input type="text" value="Jordan Clarkson" class="p-2 border rounded-md w-64">
                </div>
            </div>
        </div>

        <hr class="my-4">

        <!-- Work Preference -->
        <div>
            <h3 class="font-semibold text-[#0E335D] text-lg mb-3">Work Preference</h3>
            <div class="grid md:grid-cols-3 gap-6">

                <!-- Column 1: Working Availability -->
                <div class="flex flex-col h-full">
                    <label class="text-sm font-normal text-gray-500 mb-2">Working Availability</label>
                    <div class="p-4 border rounded-lg shadow-lg flex-1">
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <select class="p-2 border rounded-md w-full">
                                <option>8:00 AM</option>
                            </select>
                            <select class="p-2 border rounded-md w-full">
                                <option>5:00 PM</option>
                            </select>
                        </div>
                        <div class="text-sm font-medium text-gray-700 mb-2">Days Available:</div>
                        <div class="grid grid-cols-4 gap-2 mb-2">
                            <label><input type="checkbox" checked> Mon</label>
                            <label><input type="checkbox" checked> Tue</label>
                            <label><input type="checkbox" checked> Wed</label>
                            <label><input type="checkbox" checked> Thu</label>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <label><input type="checkbox" checked> Fri</label>
                            <label><input type="checkbox"> Sat</label>
                            <label><input type="checkbox"> Sun</label>
                        </div>
                    </div>
                </div>

                <!-- Column 2: Platform Familiarity + Preferred Time -->
                <div class="flex flex-col h-full justify-between">
                    <div>
                        <label class="text-sm font-normal text-gray-500 mb-2">Platform Familiarity</label>
                        <div class="p-4 border rounded-lg shadow-lg mb-4">
                            <div class="grid grid-cols-3 gap-2 mb-2">
                                <label><input type="checkbox" checked> ClassIn</label>
                                <label><input type="checkbox" checked> Zoom</label>
                                <label><input type="checkbox"> Voov</label>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <label><input type="checkbox" checked> MS Teams</label>
                                <label><input type="checkbox"> Others</label>
                            </div>
                        </div>

                        <label class="text-sm font-normal text-gray-500 mb-2">Preferred Time for Interview Call</label>
                        <div class="p-4 border rounded-lg shadow-lg">
                            <input type="datetime-local" value="2025-09-30T14:00"
                                class="p-2 border rounded-md w-full">
                        </div>
                    </div>
                </div>

                <!-- Column 3: Can Teach + CALL Button -->
                <div class="flex flex-col h-full justify-between">
                    <div>
                        <label class="text-sm font-normal text-gray-500 mb-2">Can Teach</label>
                        <div class="p-4 border rounded-lg shadow-lg space-y-4">
                            <div class="grid grid-cols-2 gap-2">
                                <label><input type="checkbox" checked> Kids</label>
                                <label><input type="checkbox" checked> Teenager</label>
                            </div>
                            <div>
                                <label><input type="checkbox" checked> Adults</label>
                            </div>
                        </div>
                    </div>

                    <!-- CALL BUTTON -->
                    <div x-data="{ showModal: false, showProgress: false }" class="flex mt-6 mb-4">
                        <button type="button"
                            class="w-full px-6 py-2 rounded-full bg-[#636363] text-white hover:opacity-90"
                            @click="showModal = true">
                            CALL
                        </button>

                        @include('hiring_onboarding.tabs.partials.modals.call_mdl', [
                            'fullName' => 'Ivan Josh Dumadapat',
                        ])
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function toggleWorkSetup() {
        const workFromHome = document.querySelector('input[name="workSetup"][value="home"]').checked;

        const fields = [
            document.getElementById("speedtestField"),
            document.getElementById("mainDeviceField"),
            document.getElementById("backupDeviceField")
        ];

        fields.forEach(field => {
            field.disabled = !workFromHome;
            if (!workFromHome) {
                field.classList.add("bg-gray-100");
            } else {
                field.classList.remove("bg-gray-100");
            }
        });
    }
</script>
