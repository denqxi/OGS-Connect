<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OGS - Application Form</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'ogs-green': '#4ADE80',
                        'ogs-dark-green': '#22C55E',
                        'ogs-navy': '#1E3A8A',
                        'ogs-dark-navy': '#0E335D'
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm w-full relative z-50" style="height: 75px;">
        <div class="flex justify-between items-center h-16 px-4 sm:px-6 lg:px-8">
            <!-- Left Logo -->
            <div class="flex items-center">
                <img src="{{ asset('images/logo.png') }}" alt="GLS Scheduling" class="h-12 ms-4 object-contain">
                <div class="ml-3">
                    <div class="text-lg font-bold text-ogs-dark-navy">OUTSOURCING</div>
                    <div class="text-xs font-bold text-gray-600">GLOBAL SOLUTIONS</div>
                </div>
            </div>

            <!-- Desktop Buttons -->
            <div class="hidden sm:flex items-center space-x-4">
                <a>
                    <button
                        class="px-6 text-xs py-2 border border-ogs-navy text-ogs-navy rounded-full hover:bg-ogs-navy hover:text-white transition-colors">
                        HOME
                    </button>
                </a>
                <button onclick="window.location.href='{{ route('login') }}'"
                    class="px-6 shadow-lg text-xs py-2 bg-ogs-green font-normal text-white rounded-full hover:bg-ogs-dark-green transition-colors">
                    LOG IN
                </button>
            </div>

            <!-- Mobile Button -->
            <div class="sm:hidden flex items-center">
                <button id="mobile-menu-button" class="focus:outline-none">
                    <svg class="w-6 h-6 text-ogs-dark-navy" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobile Overlay Menu -->
    <div id="mobile-menu"
        class="hidden sm:hidden fixed top-16 left-0 w-full bg-white shadow-md px-4 py-4 space-y-2 z-50">
        <a href="{{ route('landing') }}">
            <button
                class="w-full px-6 text-xs py-2 border border-ogs-navy text-ogs-navy rounded-full hover:bg-ogs-navy hover:text-white transition-colors">
                HOME
            </button>
        </a>
        <button onclick="window.location.href='{{ route('login') }}'"
            class="w-full shadow-lg px-6 text-xs py-2 bg-ogs-green font-normal text-white rounded-full hover:bg-ogs-dark-green transition-colors">
            LOG IN
        </button>
    </div>

    <script>
        const menuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        menuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    </script>

    <main class="max-w-full mx-auto p-6 sm:p-8 bg-gray-50">
        <!-- Form Header -->
        <div class="bg-ogs-green shadow-lg text-ogs-dark-navy font-bold text-center text-2xl rounded-md py-3 mb-6">
            APPLICATION FORM
        </div>

        <!-- Form Container -->
        <div class="bg-white rounded-xl shadow-md p-6 sm:p-10">


            <form action="#" method="POST" class="space-y-6">
                <!-- Personal Information -->
                <div>
                    <h3 class="font-semibold text-ogs-dark-navy text-lg mb-3">Personal Information</h3>
                    <div class="grid md:grid-cols-3 gap-4 items-start">
                        <!-- Row 1 -->
                        <div class="flex flex-col">
                            <label class="text-sm font-normal text-gray-500">First Name <span
                                    class="text-red-600">*</span></label>
                            <input type="text" placeholder="First name"
                                class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg">
                        </div>
                        <div class="flex flex-col">
                            <label class="text-sm font-normal text-gray-500">Last Name <span
                                    class="text-red-600">*</span></label>
                            <input type="text" placeholder="Last name"
                                class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg">
                        </div>
                        <div class="flex flex-col">
                            <label class="text-sm font-normal text-gray-500">Birth Date <span
                                    class="text-red-600">*</span></label>
                            <input type="date"
                                class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg">
                        </div>

                        <!-- Row 2 -->
                        <div class="flex flex-col">
                            <label class="text-sm font-normal text-gray-500">Address <span
                                    class="text-red-600">*</span></label>
                            <input type="text" placeholder="Address"
                                class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg">
                        </div>
                        <div class="flex flex-col">
                            <label class="text-sm font-normal text-gray-500">Contact Number <span
                                    class="text-red-600">*</span></label>
                            <input type="text" placeholder="Contact Number"
                                class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg">
                        </div>
                        <div class="flex flex-col">
                            <label class="text-sm font-normal text-gray-500">Email <span
                                    class="text-red-600">*</span></label>
                            <input type="email" placeholder="Email"
                                class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg">
                        </div>

                        <!-- Row 3 -->
                        <div class="flex flex-col md:col-span-3">
                            <label class="text-sm font-normal text-gray-500">MS Teams (e.g., live:.cid...)</label>
                            <input type="text" placeholder="MS Teams"
                                class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg">
                        </div>
                    </div>
                </div>

                <hr class="my-10">

                <!-- Education & Work Background -->
                <div>
                    <h3 class="font-semibold text-ogs-dark-navy text-lg mb-3">Education & Work Background</h3>
                    <div class="grid md:grid-cols-2 gap-4 items-start">
                        <!-- Highest Educational Attainment -->
                        <div class="flex flex-col">
                            <label class="text-sm font-normal text-gray-500">Highest Educational Attainment <span
                                    class="text-red-600">*</span></label>
                            <select
                                class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg">
                                <option value="" disabled selected>Select your education</option>
                                <option value="shs">SHS Graduate</option>
                                <option value="college_undergrad">College Undergraduate</option>
                                <option value="bachelor">College Graduate / Bachelor's Degree</option>
                                <option value="master">Master's Degree</option>
                                <option value="doctorate">Doctorate / PhD</option>
                            </select>

                        </div>

                        <!-- ESL Teaching Experience -->
                        <div class="flex flex-col">
                            <label class="text-sm font-normal text-gray-500">ESL Teaching Experience <span
                                    class="text-red-600">*</span></label>
                            <select
                                class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg">
                                <option value="" disabled selected>Select experience</option>
                                <option value="na">N/A</option>
                                <option value="1-2">1–2 years</option>
                                <option value="3-4">3–4 years</option>
                                <option value="5plus">5 years and above</option>
                            </select>
                        </div>
                    </div>
                </div>



                <hr class="my-4">

                <!-- Requirements -->
                <div>
                    <h3 class="font-semibold text-ogs-dark-navy text-lg mb-3">Requirements</h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <!-- Row 1: Resume & Intro Video -->
                        <div class="flex flex-col">
                            <label class="text-sm font-normal text-gray-500">Resume Link (GDrive / GDocs) <span
                                    class="text-red-600">*</span></label>
                            <input type="text" placeholder="Resume Link"
                                class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg">
                        </div>
                        <div class="flex flex-col">
                            <label class="text-sm font-normal text-gray-500">Intro Video (GDrive Link) <span
                                    class="text-red-600">*</span></label>
                            <input type="text" placeholder="Intro Video"
                                class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg">
                        </div>

                        <!-- Row 2: Work Setup header -->
                        <div class="md:col-span-2 mt-2">
                            <label class="text-sm font-normal text-gray-500 font-semibold">Work Setup:</label>
                        </div>

                        <!-- Row 3: Radio Buttons -->
                        <div class="flex space-x-6 md:col-span-2">
                            <label class="inline-flex items-center">
                                <input type="radio" name="workType" id="workFromHome"
                                    class="form-radio text-ogs-green">
                                <span class="ml-2">Work from Home</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="workType" id="workAtSite"
                                    class="form-radio text-ogs-green">
                                <span class="ml-2">Work at Site</span>
                            </label>
                        </div>


                        <!-- Row 4: Speedtest & Device Specs (enabled only if Work from Home is checked) -->
                        <div class="grid md:grid-cols-3 gap-4 mt-4 md:col-span-2">
                            <div class="flex flex-col">
                                <label class="text-sm font-normal text-gray-500">Ookla Speedtest (GDrive Link)</label>
                                <input type="text" id="speedtest"
                                    placeholder="Speedtest (screenshot in GDrive link)"
                                    class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg"
                                    disabled>
                            </div>
                            <div class="flex flex-col">
                                <label class="text-sm font-normal text-gray-500">Main Device Specs (dxdiag
                                    Screenshot)</label>
                                <input type="text" id="mainDevice"
                                    placeholder="Main Device Specs (screenshot in GDrive link)"
                                    class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg"
                                    disabled>
                            </div>
                            <div class="flex flex-col">
                                <label class="text-sm font-normal text-gray-500">Backup Device Specs (dxdiag
                                    Screenshot)</label>
                                <input type="text" id="backupDevice"
                                    placeholder="Backup Device Specs (screenshot in GDrive link)"
                                    class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg"
                                    disabled>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Script to enable fields only if Work from Home is selected -->
                <script>
                    const workFromHome = document.getElementById('workFromHome');
                    const workAtSite = document.getElementById('workAtSite');
                    const speedtest = document.getElementById('speedtest');
                    const mainDevice = document.getElementById('mainDevice');
                    const backupDevice = document.getElementById('backupDevice');

                    function updateDeviceFields() {
                        const enabled = workFromHome.checked;
                        speedtest.disabled = !enabled;
                        mainDevice.disabled = !enabled;
                        backupDevice.disabled = !enabled;
                    }

                    // Listen to both radio buttons
                    workFromHome.addEventListener('change', updateDeviceFields);
                    workAtSite.addEventListener('change', updateDeviceFields);
                </script>


                <hr class="my-4">

                <!-- How Did You Hear About Us? -->
                <div>
                    <h3 class="font-semibold text-ogs-dark-navy text-lg mb-3">How Did You Hear About Us?</h3>
                    <div class="flex flex-col md:flex-row md:items-center md:space-x-4">
                        <!-- Radio Buttons -->
                        <div class="flex space-x-6 mb-4 md:mb-0">
                            <label class="inline-flex items-center">
                                <input type="radio" name="source" value="fb_boosting"
                                    class="form-radio text-ogs-green">
                                <span class="ml-2">FB Boosting</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="source" value="referral"
                                    class="form-radio text-ogs-green" id="referralRadio">
                                <span class="ml-2">Referral</span>
                            </label>
                        </div>

                        <!-- Referrer Name Field -->
                        <div class="flex flex-col">
                            <label class="text-sm font-normal text-gray-500">Referrer Name <span
                                    class="text-red-600">*</span></label>
                            <input type="text" id="referrerName" placeholder="ex., JORDAN CLARKSON"
                                class="p-2 border rounded-md w-64 focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg"
                                disabled>
                        </div>
                    </div>
                </div>

                <script>
                    const referralRadio = document.getElementById('referralRadio');
                    const referrerName = document.getElementById('referrerName');
                    const radios = document.getElementsByName('source');

                    radios.forEach(radio => {
                        radio.addEventListener('change', () => {
                            if (referralRadio.checked) {
                                referrerName.disabled = false;
                            } else {
                                referrerName.disabled = true;
                                referrerName.value = '';
                            }
                        });
                    });
                </script>


                <hr class="my-4">

                <!-- Work Preference -->
                <div>
                    <h3 class="font-semibold text-ogs-dark-navy text-lg mb-3">Work Preference</h3>
                    <div class="grid md:grid-cols-3 gap-6">

                        <!-- Column 1: Working Availability -->
                        <div class="flex flex-col">
                            <label class="text-sm font-normal text-gray-500 mb-2">Working Availability</label>
                            <div class="p-4 border rounded-lg shadow-lg space-y-4">
                                <!-- Row 1: Start Time / End Time Headers -->
                                <div class="grid grid-cols-2 gap-4 text-sm font-medium text-gray-700">
                                    <div class="text-start">Start Time:</div>
                                    <div class="text-start">End Time:</div>
                                </div>

                                <!-- Row 2: Start / End Time Selects -->
                                <div class="grid grid-cols-2 gap-4">
                                    <!-- Start Time -->
                                    <div class="flex flex-col">
                                        <select
                                            class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg"
                                            required>
                                            <option value="" disabled selected>Select Start</option>
                                            <option value="12:00 AM">12:00 AM</option>
                                            <option value="1:00 AM">1:00 AM</option>
                                            <option value="2:00 AM">2:00 AM</option>
                                            <option value="3:00 AM">3:00 AM</option>
                                            <option value="4:00 AM">4:00 AM</option>
                                            <option value="5:00 AM">5:00 AM</option>
                                            <option value="6:00 AM">6:00 AM</option>
                                            <option value="7:00 AM">7:00 AM</option>
                                            <option value="8:00 AM">8:00 AM</option>
                                            <option value="9:00 AM">9:00 AM</option>
                                            <option value="10:00 AM">10:00 AM</option>
                                            <option value="11:00 AM">11:00 AM</option>
                                            <option value="12:00 PM">12:00 PM</option>
                                            <option value="1:00 PM">1:00 PM</option>
                                            <option value="2:00 PM">2:00 PM</option>
                                            <option value="3:00 PM">3:00 PM</option>
                                            <option value="4:00 PM">4:00 PM</option>
                                            <option value="5:00 PM">5:00 PM</option>
                                            <option value="6:00 PM">6:00 PM</option>
                                            <option value="7:00 PM">7:00 PM</option>
                                            <option value="8:00 PM">8:00 PM</option>
                                            <option value="9:00 PM">9:00 PM</option>
                                            <option value="10:00 PM">10:00 PM</option>
                                            <option value="11:00 PM">11:00 PM</option>
                                        </select>
                                    </div>

                                    <!-- End Time -->
                                    <div class="flex flex-col">
                                        <select
                                            class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg"
                                            required>
                                            <option value="" disabled selected>Select End</option>
                                            <option value="12:00 AM">12:00 AM</option>
                                            <option value="1:00 AM">1:00 AM</option>
                                            <option value="2:00 AM">2:00 AM</option>
                                            <option value="3:00 AM">3:00 AM</option>
                                            <option value="4:00 AM">4:00 AM</option>
                                            <option value="5:00 AM">5:00 AM</option>
                                            <option value="6:00 AM">6:00 AM</option>
                                            <option value="7:00 AM">7:00 AM</option>
                                            <option value="8:00 AM">8:00 AM</option>
                                            <option value="9:00 AM">9:00 AM</option>
                                            <option value="10:00 AM">10:00 AM</option>
                                            <option value="11:00 AM">11:00 AM</option>
                                            <option value="12:00 PM">12:00 PM</option>
                                            <option value="1:00 PM">1:00 PM</option>
                                            <option value="2:00 PM">2:00 PM</option>
                                            <option value="3:00 PM">3:00 PM</option>
                                            <option value="4:00 PM">4:00 PM</option>
                                            <option value="5:00 PM">5:00 PM</option>
                                            <option value="6:00 PM">6:00 PM</option>
                                            <option value="7:00 PM">7:00 PM</option>
                                            <option value="8:00 PM">8:00 PM</option>
                                            <option value="9:00 PM">9:00 PM</option>
                                            <option value="10:00 PM">10:00 PM</option>
                                            <option value="11:00 PM">11:00 PM</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Row 3: Days Available Header -->
                                <div class="text-sm font-medium text-gray-700">Days Available:</div>

                                <!-- Row 4: Checkboxes Mon-Thu -->
                                <div class="grid grid-cols-4 gap-2">
                                    <label><input type="checkbox" class="form-checkbox text-ogs-green"> Mon</label>
                                    <label><input type="checkbox" class="form-checkbox text-ogs-green"> Tue</label>
                                    <label><input type="checkbox" class="form-checkbox text-ogs-green"> Wed</label>
                                    <label><input type="checkbox" class="form-checkbox text-ogs-green"> Thu</label>
                                </div>

                                <!-- Row 5: Checkboxes Fri-Sun -->
                                <div class="grid grid-cols-3 gap-2">
                                    <label><input type="checkbox" class="form-checkbox text-ogs-green"> Fri</label>
                                    <label><input type="checkbox" class="form-checkbox text-ogs-green"> Sat</label>
                                    <label><input type="checkbox" class="form-checkbox text-ogs-green"> Sun</label>
                                </div>
                            </div>
                        </div>

                        <!-- Column 2: Platform Familiarity & Interview Time -->
                        <div class="flex flex-col">
                            <label class="text-sm font-normal text-gray-500 mb-2">Platform Familiarity</label>
                            <div class="p-4 border rounded-lg shadow-lg space-y-4">
                                <!-- Row 1: Checkboxes ClassIn, Zoom, Voov -->
                                <div class="grid grid-cols-3 gap-2">
                                    <label><input type="checkbox" class="form-checkbox text-ogs-green">
                                        ClassIn</label>
                                    <label><input type="checkbox" class="form-checkbox text-ogs-green"> Zoom</label>
                                    <label><input type="checkbox" class="form-checkbox text-ogs-green"> Voov</label>
                                </div>
                                <!-- Row 2: Checkboxes MS Teams, Others -->
                                <div class="grid grid-cols-2 gap-2">
                                    <label><input type="checkbox" class="form-checkbox text-ogs-green"> MS
                                        Teams</label>
                                    <label><input type="checkbox" class="form-checkbox text-ogs-green"> Others</label>
                                </div>
                            </div>

                            <!-- Preferred Time for Interview Call -->
                            <label class="text-sm font-normal text-gray-500 mt-4 mb-2">Preferred Time for Interview
                                Call</label>
                            <div class="p-4 border rounded-lg shadow-lg">
                                <input type="datetime-local"
                                    class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg">
                            </div>
                        </div>

                        <!-- Column 3: Can Teach -->
                        <div class="flex flex-col">
                            <label class="text-sm font-normal text-gray-500 mb-2">Can Teach</label>
                            <div class="p-4 border rounded-lg shadow-lg space-y-4">
                                <!-- Row 1: Kids, Teenager -->
                                <div class="grid grid-cols-2 gap-2">
                                    <label><input type="checkbox" class="form-checkbox text-ogs-green"> Kids</label>
                                    <label><input type="checkbox" class="form-checkbox text-ogs-green">
                                        Teenager</label>
                                </div>
                                <!-- Row 2: Adults -->
                                <div>
                                    <label><input type="checkbox" class="form-checkbox text-ogs-green"> Adults</label>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="flex flex-col mt-6 space-y-3">
                                <button type="button" id="cancelBtn"
                                    class="w-full px-6 py-2 rounded-full border border-ogs-navy text-ogs-navy hover:bg-ogs-navy hover:text-white transition-colors">
                                    CANCEL
                                </button>
                                <button type="submit" id="submitBtn"
                                    class="w-full shadow-lg px-6 py-2 rounded-full bg-ogs-green text-white hover:bg-ogs-dark-green transition-colors shadow-md">
                                    SUBMIT
                                </button>
                            </div>
                            <!-- Cancel Confirmation Modal -->
                            <div id="cancelModal"
                                class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center hidden z-50 p-4">
                                <div class="bg-white rounded-2xl p-6 w-full max-w-sm sm:max-w-md text-left shadow-lg">

                                    <!-- Row 1: Header -->
                                    <h3
                                        class="text-base sm:text-lg font-bold text-gray-900 mb-2 text-center sm:text-left">
                                        Do you want to cancel your application?
                                    </h3>

                                    <!-- Row 2: Subheading -->
                                    <p class="text-sm sm:text-base text-gray-700 mb-6 text-center sm:text-left">
                                        Your entered information will not be saved if you cancel. Do you want to
                                        continue?
                                    </p>

                                    <!-- Row 3: Buttons -->
                                    <div class="flex flex-col sm:flex-row justify-end gap-3 sm:gap-4">
                                        <button id="goBackBtn"
                                            class="w-full sm:w-auto px-6 py-2 rounded-full bg-[#9CA3AF] text-white font-semibold text-sm hover:bg-[#7B8790] hover:scale-105 transition-transform duration-200">
                                            GO BACK
                                        </button>
                                        <button id="confirmCancelBtn"
                                            class="w-full sm:w-auto px-6 py-2 rounded-full bg-[#EF4444] text-white font-semibold text-sm hover:bg-[#B91C1C] hover:scale-105 transition-transform duration-200">
                                            CANCEL
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Fullscreen Dim Overlay for Cancel -->
                            <div id="cancelOverlay"
                                class="fixed inset-0 bg-black bg-opacity-60 flex flex-col items-center justify-center hidden z-50">
                                <p class="text-white font-semibold text-lg mb-4">Cancelling...</p>
                                <div class="w-3/4 max-w-sm bg-gray-200 rounded-full h-3 overflow-hidden">
                                    <div id="cancelProgressBar"
                                        class="bg-red-500 h-3 w-0 transition-all duration-100 ease-linear"></div>
                                </div>
                            </div>

                            <script>
                                const cancelBtn = document.getElementById('cancelBtn');
                                const cancelModal = document.getElementById('cancelModal');
                                const goBackBtn = document.getElementById('goBackBtn');
                                const confirmCancelBtn = document.getElementById('confirmCancelBtn');
                                const cancelOverlay = document.getElementById('cancelOverlay');
                                const cancelProgressBar = document.getElementById('cancelProgressBar');

                                // Show modal on cancel button click
                                cancelBtn.addEventListener('click', () => {
                                    cancelModal.classList.remove('hidden');
                                });

                                // Close modal when Go Back is clicked
                                goBackBtn.addEventListener('click', () => {
                                    cancelModal.classList.add('hidden');
                                });

                                // Cancel modal redirect with progress bar
                                confirmCancelBtn.addEventListener('click', () => {
                                    // Hide modal
                                    cancelModal.classList.add('hidden');

                                    // Show overlay
                                    cancelOverlay.classList.remove('hidden');

                                    // Animate progress bar
                                    let progress = 0;
                                    const interval = setInterval(() => {
                                        progress += 5;
                                        cancelProgressBar.style.width = progress + "%";

                                        if (progress >= 100) {
                                            clearInterval(interval);
                                            window.location.href = "{{ route('application.form.cancel') }}";
                                        }
                                    }, 100); // ~2 seconds
                                });
                            </script>

                            <!-- Submit Confirmation Modal -->
                            <div id="submitModal"
                                class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center hidden z-50 p-4">
                                <div class="bg-white rounded-2xl p-6 w-full max-w-sm sm:max-w-md text-left shadow-lg">

                                    <!-- Row 1: Header -->
                                    <h3
                                        class="text-base sm:text-lg font-bold text-gray-900 mb-2 text-center sm:text-left">
                                        Submit Application?
                                    </h3>

                                    <!-- Row 2: Subheading -->
                                    <p class="text-sm sm:text-base text-gray-700 mb-6 text-center sm:text-left">
                                        Once submitted, you cannot edit your information. Do you want to continue?
                                    </p>

                                    <!-- Row 3: Buttons -->
                                    <div class="flex flex-col sm:flex-row justify-end gap-3 sm:gap-4">
                                        <button id="goBackSubmitBtn"
                                            class="w-full sm:w-auto px-6 py-2 rounded-full bg-[#9CA3AF] text-white font-semibold text-sm hover:bg-[#7B8790] hover:scale-105 transition-transform duration-200">
                                            GO BACK
                                        </button>
                                        <button id="confirmSubmitBtn"
                                            class="w-full sm:w-auto px-6 py-2 rounded-full bg-[#65DB7F] text-white font-semibold text-sm hover:bg-[#3CB45C] hover:scale-105 transition-transform duration-200">
                                            SUBMIT
                                        </button>
                                    </div>
                                </div>
                            </div>


                            <!-- Loading Overlay with Progress Bar -->
                            <div id="loadingOverlay"
                                class="hidden fixed inset-0 bg-black/60 flex flex-col items-center justify-center z-50 text-white">
                                <p class="mb-4 text-lg font-medium">Submitting your application...</p>
                                <div class="w-64 bg-gray-300 rounded-full h-3 overflow-hidden">
                                    <div id="progressBar" class="bg-[#65DB7F] h-3 w-0"></div>
                                </div>
                            </div>

                            <script>
                                const submitBtn = document.getElementById('submitBtn');
                                const submitModal = document.getElementById('submitModal');
                                const goBackSubmitBtn = document.getElementById('goBackSubmitBtn');
                                const confirmSubmitBtn = document.getElementById('confirmSubmitBtn');
                                const loadingOverlay = document.getElementById('loadingOverlay');
                                const progressBar = document.getElementById('progressBar');

                                // Show modal on submit button click
                                submitBtn.addEventListener('click', (e) => {
                                    e.preventDefault(); // Prevent form submission until confirmed
                                    submitModal.classList.remove('hidden');
                                });

                                // Close modal when Go Back is clicked
                                goBackSubmitBtn.addEventListener('click', () => {
                                    submitModal.classList.add('hidden');
                                });

                                // Submit modal redirect with progress bar
                                confirmSubmitBtn.addEventListener('click', () => {
                                    // Hide modal
                                    submitModal.classList.add('hidden');

                                    // Show overlay
                                    loadingOverlay.classList.remove('hidden');

                                    // Animate progress bar
                                    let progress = 0;
                                    const interval = setInterval(() => {
                                        progress += 5;
                                        progressBar.style.width = progress + "%";

                                        if (progress >= 100) {
                                            clearInterval(interval);
                                            window.location.href = "{{ route('application.form.submit') }}";
                                        }
                                    }, 100); // progress fills in ~2 seconds
                                });
                            </script>

                        </div>
                    </div>
                </div>
            </form>
        </div>
    </main>

</body>

</html>
