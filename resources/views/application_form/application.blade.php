<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OGS - Application Form</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
    <style>
        .field-error {
            border-color: #EF4444 !important;
            border-width: 2px;
        }
        .error-message {
            color: #DC2626;
            font-size: 0.75rem;
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        .submit-disabled {
            opacity: 0.5;
            cursor: not-allowed !important;
            pointer-events: none;
        }
        .section-incomplete-badge {
            background-color: #FEF2F2;
            border: 1px solid #FCA5A5;
            color: #DC2626;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        .section-complete-badge {
            background-color: #F0FDF4;
            border: 1px solid #86EFAC;
            color: #16A34A;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
    </style>
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
                <button id="homeBtn"
                    class="px-6 text-xs py-2 border border-ogs-navy text-ogs-navy rounded-full hover:bg-ogs-navy hover:text-white transition-colors">
                    HOME
                </button>
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
        <button id="homeBtnMobile"
            class="w-full px-6 text-xs py-2 border border-ogs-navy text-ogs-navy rounded-full hover:bg-ogs-navy hover:text-white transition-colors">
            HOME
        </button>
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

    <main class="max-w-7xl mx-auto p-3 sm:p-4 bg-gray-50">
        <!-- Form Header -->
        <div class="bg-ogs-green shadow-lg text-ogs-dark-navy font-bold text-center text-2xl rounded-md py-3 mb-3">
            APPLICATION FORM
        </div>

        <!-- Form Container -->
        <div class="bg-white rounded-xl shadow-md p-6 sm:px-6 py-2">

            <!-- Display Validation Errors -->
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
                    <h4 class="font-bold mb-2">Please fix the following errors:</h4>
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

        <form id="applicationForm" action="{{ route('application.form.submit') }}" method="POST" class="space-y-4" x-data="{ openSection: 1 }">
                @csrf
                
                <!-- Section 1: Personal Information -->
                <div class="border border-gray-200 rounded-lg overflow-hidden bg-white shadow-sm">
                    <button type="button" @click="openSection = openSection === 1 ? null : 1" 
                        class="w-full flex justify-between items-center p-4 bg-gray-50 hover:bg-gray-100 transition-colors">
                        <div class="flex items-center gap-3 flex-1">
                            <h3 class="font-semibold text-ogs-dark-navy text-lg flex items-center">
                                <i class="fas fa-user mr-2 text-ogs-green"></i>
                                Personal Information
                            </h3>
                            <!-- Section Status Indicator -->
                            <div id="section1Status" class="section-incomplete-badge" style="margin-bottom: 0; padding: 0.25rem 0.75rem; font-size: 0.75rem;">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>Incomplete</span>
                            </div>
                        </div>
                        <i class="fas transition-transform duration-200" 
                            :class="openSection === 1 ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </button>
                    <div x-show="openSection === 1" x-collapse>
                        <div class="p-6 space-y-4">
                            <div class="grid md:grid-cols-3 gap-4 items-start">
                                <!-- Row 1 -->
                                <div class="flex flex-col">
                                    <label class="text-sm font-normal text-gray-500">First Name <span
                                            class="text-red-600">*</span></label>
                                    <input type="text" name="first_name" placeholder="First name" value="{{ old('first_name') }}"
                                        class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg" required>
                                </div>
                                <div class="flex flex-col">
                                    <label class="text-sm font-normal text-gray-500">Middle Name</label>
                                    <input type="text" name="middle_name" placeholder="Middle name" value="{{ old('middle_name') }}"
                                        class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg">
                                </div>
                                <div class="flex flex-col">
                                    <label class="text-sm font-normal text-gray-500">Last Name <span
                                            class="text-red-600">*</span></label>
                                    <input type="text" name="last_name" placeholder="Last name" value="{{ old('last_name') }}"
                                        class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg" required>
                                </div>

                                <!-- Row 2 -->
                                <div class="flex flex-col">
                                    <label class="text-sm font-normal text-gray-500">Birth Date <span
                                            class="text-red-600">*</span></label>
                                    <input type="date" name="birth_date" value="{{ old('birth_date') }}"
                                        min="{{ \Carbon\Carbon::now()->subYears(70)->format('Y-m-d') }}"
                                        max="{{ \Carbon\Carbon::now()->subYears(18)->format('Y-m-d') }}"
                                        class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg" required>
                                    <span class="text-xs text-gray-500 mt-1">Must be between 18 and 70 years old</span>
                                </div>
                                <div class="flex flex-col">
                                    <label class="text-sm font-normal text-gray-500">Contact Number <span
                                            class="text-red-600">*</span></label>
                                    <input type="tel" name="contact_number" pattern="^09\d{9}$" placeholder="Ex. 09123456789" value="{{ old('contact_number') }}"
                                        class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg" required>
                                </div>
                                <div class="flex flex-col">
                                    <label class="text-sm font-normal text-gray-500">Email <span
                                            class="text-red-600">*</span></label>
                                    <input type="email" name="email" placeholder="Email" value="{{ old('email') }}"
                                        class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg" required>
                                </div>

                                <!-- Row 3 -->
                                <div class="flex flex-col md:col-span-3">
                                    <label class="text-sm font-normal text-gray-500">Address <span
                                            class="text-red-600">*</span></label>
                                    <input type="text" name="address" placeholder="Address" value="{{ old('address') }}"
                                        class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg" required>
                                </div>
                                
                                <!-- Row 4 -->
                                <div class="flex flex-col md:col-span-3">
                                    <label class="text-sm font-normal text-gray-500">MS Teams (e.g., live:.cid...)</label>
                                    <input type="text" name="ms_teams" placeholder="MS Teams" value="{{ old('ms_teams') }}"
                                        class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Education & Work Background -->
                <div class="border border-gray-200 rounded-lg overflow-hidden bg-white shadow-sm">
                    <button type="button" @click="openSection = openSection === 2 ? null : 2" 
                        class="w-full flex justify-between items-center p-4 bg-gray-50 hover:bg-gray-100 transition-colors">
                        <div class="flex items-center gap-3 flex-1">
                            <h3 class="font-semibold text-ogs-dark-navy text-lg flex items-center">
                                <i class="fas fa-graduation-cap mr-2 text-ogs-green"></i>
                                Education & Work Background
                            </h3>
                            <!-- Section Status Indicator -->
                            <div id="section2Status" class="section-incomplete-badge" style="margin-bottom: 0; padding: 0.25rem 0.75rem; font-size: 0.75rem;">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>Incomplete</span>
                            </div>
                        </div>
                        <i class="fas transition-transform duration-200" 
                            :class="openSection === 2 ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </button>
                    <div x-show="openSection === 2" x-collapse>
                        <div class="p-6 space-y-4">
                            <div class="grid md:grid-cols-2 gap-4 items-start">
                                <!-- Highest Educational Attainment -->
                                <div class="flex flex-col">
                                    <label class="text-sm font-normal text-gray-500">Highest Educational Attainment <span
                                            class="text-red-600">*</span></label>
                                    <select name="education"
                                        class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg" required>
                                        <option value="" disabled {{ old('education') ? '' : 'selected' }}>Select your education</option>
                                        <option value="shs" {{ old('education') == 'shs' ? 'selected' : '' }}>SHS Graduate</option>
                                        <option value="college_undergrad" {{ old('education') == 'college_undergrad' ? 'selected' : '' }}>College Undergraduate</option>
                                        <option value="bachelor" {{ old('education') == 'bachelor' ? 'selected' : '' }}>College Graduate / Bachelor's Degree</option>
                                        <option value="master" {{ old('education') == 'master' ? 'selected' : '' }}>Master's Degree</option>
                                        <option value="doctorate" {{ old('education') == 'doctorate' ? 'selected' : '' }}>Doctorate / PhD</option>
                                    </select>
                                </div>

                                <!-- ESL Teaching Experience -->
                                <div class="flex flex-col">
                                    <label class="text-sm font-normal text-gray-500">ESL Teaching Experience <span
                                            class="text-red-600">*</span></label>
                                    <select name="esl_experience"
                                        class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg" required>
                                        <option value="" disabled {{ old('esl_experience') ? '' : 'selected' }}>Select experience</option>
                                        <option value="na" {{ old('esl_experience') == 'na' ? 'selected' : '' }}>N/A</option>
                                        <option value="1-2" {{ old('esl_experience') == '1-2' ? 'selected' : '' }}>1–2 years</option>
                                        <option value="3-4" {{ old('esl_experience') == '3-4' ? 'selected' : '' }}>3–4 years</option>
                                        <option value="5plus" {{ old('esl_experience') == '5plus' ? 'selected' : '' }}>5 years and above</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Requirements & Referral -->
                <div class="border border-gray-200 rounded-lg overflow-hidden bg-white shadow-sm">
                    <button type="button" @click="openSection = openSection === 3 ? null : 3" 
                        class="w-full flex justify-between items-center p-4 bg-gray-50 hover:bg-gray-100 transition-colors">
                        <div class="flex items-center gap-3 flex-1">
                            <h3 class="font-semibold text-ogs-dark-navy text-lg flex items-center">
                                <i class="fas fa-file-alt mr-2 text-ogs-green"></i>
                                Requirements & Referral
                            </h3>
                            <!-- Section Status Indicator -->
                            <div id="section3Status" class="section-incomplete-badge" style="margin-bottom: 0; padding: 0.25rem 0.75rem; font-size: 0.75rem;">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>Incomplete</span>
                            </div>
                        </div>
                        <i class="fas transition-transform duration-200" 
                            :class="openSection === 3 ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </button>
                    <div x-show="openSection === 3" x-collapse>
                        <div class="p-6 space-y-6">
                            <!-- Documents -->
                            <div class="grid md:grid-cols-2 gap-4">
                                <div class="flex flex-col">
                                    <label class="text-sm font-normal text-gray-500">Resume Link (GDrive / GDocs) <span
                                            class="text-red-600">*</span></label>
                                    <input type="url" name="resume_link" placeholder="Resume Link" value="{{ old('resume_link') }}"
                                        class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg" required>
                                </div>
                                <div class="flex flex-col">
                                    <label class="text-sm font-normal text-gray-500">Intro Video (GDrive Link) <span
                                            class="text-red-600">*</span></label>
                                    <input type="url" name="intro_video" placeholder="Intro Video" value="{{ old('intro_video') }}"
                                        class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg" required>
                                </div>
                            </div>

                            <!-- Work Setup -->
                            <div>
                                <label class="text-sm font-semibold text-gray-700 mb-2 block">Work Setup:</label>
                                <div class="flex space-x-6">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="work_type" id="workFromHome" value="work_from_home"
                                            class="form-radio text-ogs-green" {{ old('work_type') == 'work_from_home' ? 'checked' : '' }} required>
                                        <span class="ml-2">Work from Home</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="work_type" id="workAtSite" value="work_at_site"
                                            class="form-radio text-ogs-green" {{ old('work_type') == 'work_at_site' ? 'checked' : '' }} required>
                                        <span class="ml-2">Work at Site</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Device Specs (WFH only) -->
                            <div class="grid md:grid-cols-3 gap-4">
                                <div class="flex flex-col">
                                    <label class="text-sm font-normal text-gray-500">Ookla Speedtest (GDrive Link)</label>
                                    <input type="url" id="speedtest" name="speedtest" value="{{ old('speedtest') }}"
                                        placeholder="Speedtest (screenshot in GDrive link)"
                                        class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg"
                                        disabled>
                                </div>
                                <div class="flex flex-col">
                                    <label class="text-sm font-normal text-gray-500">Main Device Specs (dxdiag)</label>
                                    <input type="url" id="mainDevice" name="main_device" value="{{ old('main_device') }}"
                                        placeholder="Main Device Specs (screenshot in GDrive link)"
                                        class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg"
                                        disabled>
                                </div>
                                <div class="flex flex-col">
                                    <label class="text-sm font-normal text-gray-500">Backup Device Specs (dxdiag)</label>
                                    <input type="url" id="backupDevice" name="backup_device" value="{{ old('backup_device') }}"
                                        placeholder="Backup Device Specs (screenshot in GDrive link)"
                                        class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg"
                                        disabled>
                                </div>
                            </div>

                            <!-- Referral Source -->
                            <div class="border-t pt-6">
                                <h4 class="font-semibold text-gray-700 mb-3">How Did You Hear About Us?</h4>
                                <div class="flex flex-col md:flex-row md:items-center md:space-x-4">
                                    <div class="flex space-x-6 mb-4 md:mb-0">
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="source" value="fb_boosting"
                                                class="form-radio text-ogs-green" {{ old('source') == 'fb_boosting' ? 'checked' : '' }} required>
                                            <span class="ml-2">FB Boosting</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="source" value="referral"
                                                class="form-radio text-ogs-green" id="referralRadio" {{ old('source') == 'referral' ? 'checked' : '' }} required>
                                            <span class="ml-2">Referral</span>
                                        </label>
                                    </div>
                                    <div class="flex flex-col">
                                        <label class="text-sm font-normal text-gray-500">Referrer Name <span
                                                class="text-red-600">*</span></label>
                                        <input type="text" id="referrerName" name="referrer_name" placeholder="ex., JORDAN CLARKSON" value="{{ old('referrer_name') }}"
                                            class="p-2 border rounded-md w-64 focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg"
                                            disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Work Preferences -->
                <div class="border border-gray-200 rounded-lg overflow-hidden bg-white shadow-sm">
                    <button type="button" @click="openSection = openSection === 4 ? null : 4" 
                        class="w-full flex justify-between items-center p-4 bg-gray-50 hover:bg-gray-100 transition-colors">
                        <div class="flex items-center gap-3 flex-1">
                            <h3 class="font-semibold text-ogs-dark-navy text-lg flex items-center">
                                <i class="fas fa-clock mr-2 text-ogs-green"></i>
                                Work Preferences
                            </h3>
                            <!-- Section Status Indicator -->
                            <div id="section4Status" class="section-incomplete-badge" style="margin-bottom: 0; padding: 0.25rem 0.75rem; font-size: 0.75rem;">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>Incomplete</span>
                            </div>
                        </div>
                        <i class="fas transition-transform duration-200" 
                            :class="openSection === 4 ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </button>
                    <div x-show="openSection === 4" x-collapse>
                        <div class="p-6">
                            <div class="grid md:grid-cols-3 gap-6">
                                <!-- Column 1: Working Availability -->
                                <div class="flex flex-col">
                            <label class="text-sm font-normal text-gray-500 mb-2">Working Availability</label>
                            <div class="p-4 border rounded-lg shadow-lg space-y-4">
                                <!-- Row 1: Start Time / End Time Headers -->
                                <div class="grid grid-cols-2 gap-4 text-sm font-medium text-gray-700">
                                    <div class="text-start">Start Time: <span class="text-red-500">*</span></div>
                                    <div class="text-start">End Time:<span class="text-red-500">*</span></div>
                                </div>

                                <!-- Row 2: Start / End Time Selects -->
                                <div class="grid grid-cols-2 gap-4">
                                    <!-- Start Time -->
                                    <div class="flex flex-col">
                                        <select name="start_time"
                                            class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg"
                                            required>
                                            <option value="" disabled {{ old('start_time') ? '' : 'selected' }}>Select Start</option>
                                            <option value="12:00 AM" {{ old('start_time') == '12:00 AM' ? 'selected' : '' }}>12:00 AM</option>
                                            <option value="1:00 AM" {{ old('start_time') == '1:00 AM' ? 'selected' : '' }}>1:00 AM</option>
                                            <option value="2:00 AM" {{ old('start_time') == '2:00 AM' ? 'selected' : '' }}>2:00 AM</option>
                                            <option value="3:00 AM" {{ old('start_time') == '3:00 AM' ? 'selected' : '' }}>3:00 AM</option>
                                            <option value="4:00 AM" {{ old('start_time') == '4:00 AM' ? 'selected' : '' }}>4:00 AM</option>
                                            <option value="5:00 AM" {{ old('start_time') == '5:00 AM' ? 'selected' : '' }}>5:00 AM</option>
                                            <option value="6:00 AM" {{ old('start_time') == '6:00 AM' ? 'selected' : '' }}>6:00 AM</option>
                                            <option value="7:00 AM" {{ old('start_time') == '7:00 AM' ? 'selected' : '' }}>7:00 AM</option>
                                            <option value="8:00 AM" {{ old('start_time') == '8:00 AM' ? 'selected' : '' }}>8:00 AM</option>
                                            <option value="9:00 AM" {{ old('start_time') == '9:00 AM' ? 'selected' : '' }}>9:00 AM</option>
                                            <option value="10:00 AM" {{ old('start_time') == '10:00 AM' ? 'selected' : '' }}>10:00 AM</option>
                                            <option value="11:00 AM" {{ old('start_time') == '11:00 AM' ? 'selected' : '' }}>11:00 AM</option>
                                            <option value="12:00 PM" {{ old('start_time') == '12:00 PM' ? 'selected' : '' }}>12:00 PM</option>
                                            <option value="1:00 PM" {{ old('start_time') == '1:00 PM' ? 'selected' : '' }}>1:00 PM</option>
                                            <option value="2:00 PM" {{ old('start_time') == '2:00 PM' ? 'selected' : '' }}>2:00 PM</option>
                                            <option value="3:00 PM" {{ old('start_time') == '3:00 PM' ? 'selected' : '' }}>3:00 PM</option>
                                            <option value="4:00 PM" {{ old('start_time') == '4:00 PM' ? 'selected' : '' }}>4:00 PM</option>
                                            <option value="5:00 PM" {{ old('start_time') == '5:00 PM' ? 'selected' : '' }}>5:00 PM</option>
                                            <option value="6:00 PM" {{ old('start_time') == '6:00 PM' ? 'selected' : '' }}>6:00 PM</option>
                                            <option value="7:00 PM" {{ old('start_time') == '7:00 PM' ? 'selected' : '' }}>7:00 PM</option>
                                            <option value="8:00 PM" {{ old('start_time') == '8:00 PM' ? 'selected' : '' }}>8:00 PM</option>
                                            <option value="9:00 PM" {{ old('start_time') == '9:00 PM' ? 'selected' : '' }}>9:00 PM</option>
                                            <option value="10:00 PM" {{ old('start_time') == '10:00 PM' ? 'selected' : '' }}>10:00 PM</option>
                                            <option value="11:00 PM" {{ old('start_time') == '11:00 PM' ? 'selected' : '' }}>11:00 PM</option>
                                        </select>
                                    </div>

                                    <!-- End Time -->
                                    <div class="flex flex-col">
                                        <select name="end_time"
                                            class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg"
                                            required>
                                            <option value="" disabled {{ old('end_time') ? '' : 'selected' }}>Select End</option>
                                            <option value="12:00 AM" {{ old('end_time') == '12:00 AM' ? 'selected' : '' }}>12:00 AM</option>
                                            <option value="1:00 AM" {{ old('end_time') == '1:00 AM' ? 'selected' : '' }}>1:00 AM</option>
                                            <option value="2:00 AM" {{ old('end_time') == '2:00 AM' ? 'selected' : '' }}>2:00 AM</option>
                                            <option value="3:00 AM" {{ old('end_time') == '3:00 AM' ? 'selected' : '' }}>3:00 AM</option>
                                            <option value="4:00 AM" {{ old('end_time') == '4:00 AM' ? 'selected' : '' }}>4:00 AM</option>
                                            <option value="5:00 AM" {{ old('end_time') == '5:00 AM' ? 'selected' : '' }}>5:00 AM</option>
                                            <option value="6:00 AM" {{ old('end_time') == '6:00 AM' ? 'selected' : '' }}>6:00 AM</option>
                                            <option value="7:00 AM" {{ old('end_time') == '7:00 AM' ? 'selected' : '' }}>7:00 AM</option>
                                            <option value="8:00 AM" {{ old('end_time') == '8:00 AM' ? 'selected' : '' }}>8:00 AM</option>
                                            <option value="9:00 AM" {{ old('end_time') == '9:00 AM' ? 'selected' : '' }}>9:00 AM</option>
                                            <option value="10:00 AM" {{ old('end_time') == '10:00 AM' ? 'selected' : '' }}>10:00 AM</option>
                                            <option value="11:00 AM" {{ old('end_time') == '11:00 AM' ? 'selected' : '' }}>11:00 AM</option>
                                            <option value="12:00 PM" {{ old('end_time') == '12:00 PM' ? 'selected' : '' }}>12:00 PM</option>
                                            <option value="1:00 PM" {{ old('end_time') == '1:00 PM' ? 'selected' : '' }}>1:00 PM</option>
                                            <option value="2:00 PM" {{ old('end_time') == '2:00 PM' ? 'selected' : '' }}>2:00 PM</option>
                                            <option value="3:00 PM" {{ old('end_time') == '3:00 PM' ? 'selected' : '' }}>3:00 PM</option>
                                            <option value="4:00 PM" {{ old('end_time') == '4:00 PM' ? 'selected' : '' }}>4:00 PM</option>
                                            <option value="5:00 PM" {{ old('end_time') == '5:00 PM' ? 'selected' : '' }}>5:00 PM</option>
                                            <option value="6:00 PM" {{ old('end_time') == '6:00 PM' ? 'selected' : '' }}>6:00 PM</option>
                                            <option value="7:00 PM" {{ old('end_time') == '7:00 PM' ? 'selected' : '' }}>7:00 PM</option>
                                            <option value="8:00 PM" {{ old('end_time') == '8:00 PM' ? 'selected' : '' }}>8:00 PM</option>
                                            <option value="9:00 PM" {{ old('end_time') == '9:00 PM' ? 'selected' : '' }}>9:00 PM</option>
                                            <option value="10:00 PM" {{ old('end_time') == '10:00 PM' ? 'selected' : '' }}>10:00 PM</option>
                                            <option value="11:00 PM" {{ old('end_time') == '11:00 PM' ? 'selected' : '' }}>11:00 PM</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Row 3: Days Available Header -->
                                <div class="text-sm font-medium text-gray-700">Days Available: <span class="text-red-500">*</span></div>

                                <!-- Row 4: Checkboxes Mon-Thu -->
                                <div class="grid grid-cols-4 gap-2">
                                    <label class="flex items-center gap-2"><input type="checkbox" name="days[]" value="monday" class="form-checkbox text-ogs-green" {{ in_array('monday', old('days', [])) ? 'checked' : '' }}> <span>Mon</span></label>
                                    <label class="flex items-center gap-2"><input type="checkbox" name="days[]" value="tuesday" class="form-checkbox text-ogs-green" {{ in_array('tuesday', old('days', [])) ? 'checked' : '' }}> <span>Tue</span></label>
                                    <label class="flex items-center gap-2"><input type="checkbox" name="days[]" value="wednesday" class="form-checkbox text-ogs-green" {{ in_array('wednesday', old('days', [])) ? 'checked' : '' }}> <span>Wed</span></label>
                                    <label class="flex items-center gap-2"><input type="checkbox" name="days[]" value="thursday" class="form-checkbox text-ogs-green" {{ in_array('thursday', old('days', [])) ? 'checked' : '' }}> <span>Thu</span></label>
                                </div>

                                <!-- Row 5: Checkboxes Fri-Sun -->
                                <div class="grid grid-cols-4 gap-2">
                                    <label class="flex items-center gap-2"><input type="checkbox" name="days[]" value="friday" class="form-checkbox text-ogs-green" {{ in_array('friday', old('days', [])) ? 'checked' : '' }}> <span>Fri</span></label>
                                    <label class="flex items-center gap-2"><input type="checkbox" name="days[]" value="saturday" class="form-checkbox text-ogs-green" {{ in_array('saturday', old('days', [])) ? 'checked' : '' }}> <span>Sat</span></label>
                                    <label class="flex items-center gap-2"><input type="checkbox" name="days[]" value="sunday" class="form-checkbox text-ogs-green" {{ in_array('sunday', old('days', [])) ? 'checked' : '' }}> <span>Sun</span></label>
                                </div>
                            </div>
                        </div>

                        <!-- Column 2: Platform Familiarity & Interview Time -->
                        <div class="flex flex-col">
                            <label class="text-sm font-normal text-gray-500 mb-2">Platform Familiarity <span class="text-red-500">*</span></label>
                            <div class="p-4 border rounded-lg shadow-lg space-y-3">
                                <!-- Row 1: Checkboxes ClassIn, Zoom, Voov -->
                                <div class="grid grid-cols-3 gap-2">
                                    <label class="flex items-center gap-2"><input type="checkbox" name="platforms[]" value="classin" class="form-checkbox text-ogs-green" {{ in_array('classin', old('platforms', [])) ? 'checked' : '' }}> <span>ClassIn</span></label>
                                    <label class="flex items-center gap-2"><input type="checkbox" name="platforms[]" value="zoom" class="form-checkbox text-ogs-green" {{ in_array('zoom', old('platforms', [])) ? 'checked' : '' }}> <span>Zoom</span></label>
                                    <label class="flex items-center gap-2"><input type="checkbox" name="platforms[]" value="voov" class="form-checkbox text-ogs-green" {{ in_array('voov', old('platforms', [])) ? 'checked' : '' }}> <span>Voov</span></label>
                                </div>
                                <!-- Row 2: Checkboxes MS Teams, Others -->
                                <div class="grid grid-cols-3 gap-2">
                                    <label class="flex items-center gap-2"><input type="checkbox" name="platforms[]" value="ms_teams" class="form-checkbox text-ogs-green" {{ in_array('ms_teams', old('platforms', [])) ? 'checked' : '' }}> <span>MS Teams</span></label>
                                    <label class="flex items-center gap-2"><input type="checkbox" name="platforms[]" value="others" class="form-checkbox text-ogs-green" {{ in_array('others', old('platforms', [])) ? 'checked' : '' }}> <span>Others</span></label>
                                </div>
                            </div>

                            <!-- Preferred Time for Interview Call -->
                            <label class="text-sm font-normal text-gray-500 mt-4 mb-2">Preferred Time for Interview
                                Call <span class="text-red-600">*</span></label>
                            <input type="datetime-local" name="interview_time" value="{{ old('interview_time') }}"
                             min="{{ \Carbon\Carbon::now()->format('Y-m-d\TH:i') }}"
                            class="p-2 border rounded-md w-full focus:outline-none focus:border-blue-500 focus:border-[0.5px] focus:shadow-lg" required>
                        </div>

                        <!-- Column 3: Can Teach -->
                        <div class="flex flex-col">
                            <label class="text-sm font-normal text-gray-500 mb-2">Can Teach <span class="text-red-500">*</span></label>
                            <div class="p-4 border rounded-lg shadow-lg space-y-3">
                                <!-- Row 1: Kids, Teenager, Adults -->
                                <div class="grid grid-cols-2 gap-2">
                                    <label class="flex items-center gap-2"><input type="checkbox" name="can_teach[]" value="kids" class="form-checkbox text-ogs-green" {{ in_array('kids', old('can_teach', [])) ? 'checked' : '' }}> <span>Kids</span></label>
                                    <label class="flex items-center gap-2"><input type="checkbox" name="can_teach[]" value="teenager" class="form-checkbox text-ogs-green" {{ in_array('teenager', old('can_teach', [])) ? 'checked' : '' }}> <span>Teenager</span></label>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <label class="flex items-center gap-2"><input type="checkbox" name="can_teach[]" value="adults" class="form-checkbox text-ogs-green" {{ in_array('adults', old('can_teach', [])) ? 'checked' : '' }}> <span>Adults</span></label>
                                </div>
                            </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 5: Terms and Conditions -->
                <div class="border border-gray-200 rounded-lg overflow-hidden bg-white shadow-sm">
                    <button type="button" @click="openSection = openSection === 5 ? null : 5" 
                        class="w-full flex justify-between items-center p-4 bg-gray-50 hover:bg-gray-100 transition-colors">
                        <div class="flex items-center gap-3 flex-1">
                            <h3 class="font-semibold text-ogs-dark-navy text-lg flex items-center">
                                <i class="fas fa-shield-alt mr-2 text-ogs-green"></i>
                                Terms and Conditions
                            </h3>
                            <!-- Section Status Indicator -->
                            <div id="section5Status" class="section-incomplete-badge" style="margin-bottom: 0; padding: 0.25rem 0.75rem; font-size: 0.75rem;">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>Incomplete</span>
                            </div>
                        </div>
                        <i class="fas transition-transform duration-200" 
                            :class="openSection === 5 ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </button>
                    <div x-show="openSection === 5" x-collapse>
                        <div class="p-6">
                            <div class="flex items-start space-x-3 p-4 bg-gray-50 rounded-lg border" id="termsContainer">
                        <input type="checkbox" id="termsAgreement" name="terms_agreement" required
                            class="mt-1 form-checkbox text-ogs-green focus:ring-ogs-green focus:ring-2">
                        <label for="termsAgreement" class="text-sm text-gray-700 leading-relaxed">
                            <span class="text-red-600 font-bold">*</span> I agree to the 
                            <button type="button" onclick="showTermsModal()" class="text-ogs-navy underline hover:text-ogs-dark-navy font-medium">
                                Terms and Conditions
                            </button> 
                            and consent to the collection, processing, and storage of my personal information in accordance with the 
                            <strong>Data Privacy Act of 2012 (Republic Act No. 10173)</strong>. 
                            I understand that my information will be used for employment purposes and will be kept confidential.
                        </label>
                    </div>
                            
                            <!-- Terms Agreement Status Indicator -->
                            <div id="termsStatusIndicator" class="hidden flex items-center space-x-2 p-3rounded-lg animate-pulse mt-3">
                                <i class="fas fa-exclamation-triangle text-red-500"></i>
                                <span class="text-sm text-red-700 font-medium">
                                    You must agree to the Terms and Conditions before submitting your application.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 flex flex-col sm:flex-row justify-center gap-4">
                    <button type="button" id="cancelBtn"
                        class="w-full sm:w-auto max-w-md shadow-lg px-6 py-3 rounded-full bg-gray-500 text-white hover:bg-gray-600 transition-colors font-medium">
                        CANCEL APPLICATION
                    </button>
                    <button type="button" id="submitBtn"
                        class="w-full sm:w-auto max-w-md shadow-lg px-6 py-3 rounded-full bg-gray-400 text-gray-200 transition-colors font-medium flex items-center justify-center gap-2 submit-disabled">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>COMPLETE ALL REQUIRED FIELDS</span>
                    </button>
                </div>

                <!-- JavaScript for dynamic form behavior and validation -->
                <script>
                    // Section validation functions
                    function validateSection1() {
                        const fields = ['first_name', 'last_name', 'birth_date', 'address', 'contact_number', 'email'];
                        for (const field of fields) {
                            const input = document.querySelector(`[name="${field}"]`);
                            if (!input || !input.value || input.value.trim() === '') {
                                return false;
                            }
                        }
                        return true;
                    }
                    
                    function validateSection2() {
                        const education = document.querySelector('[name="education"]');
                        const esl = document.querySelector('[name="esl_experience"]');
                        return education && education.value && esl && esl.value;
                    }
                    
                    function validateSection3() {
                        const resume = document.querySelector('[name="resume_link"]');
                        const video = document.querySelector('[name="intro_video"]');
                        const workType = document.querySelector('input[name="work_type"]:checked');
                        const source = document.querySelector('input[name="source"]:checked');
                        
                        let valid = resume && resume.value && video && video.value && workType && source;
                        
                        // Check referrer name if referral selected
                        const referralRadio = document.getElementById('referralRadio');
                        if (referralRadio && referralRadio.checked) {
                            const referrerName = document.getElementById('referrerName');
                            if (!referrerName || !referrerName.value || referrerName.value.trim() === '') {
                                valid = false;
                            }
                        }
                        
                        // Check WFH fields if work from home selected
                        const wfh = document.getElementById('workFromHome');
                        if (wfh && wfh.checked) {
                            const speedtest = document.getElementById('speedtest');
                            const mainDevice = document.getElementById('mainDevice');
                            const backupDevice = document.getElementById('backupDevice');
                            if (!speedtest || !speedtest.value || speedtest.value.trim() === '') valid = false;
                            if (!mainDevice || !mainDevice.value || mainDevice.value.trim() === '') valid = false;
                            if (!backupDevice || !backupDevice.value || backupDevice.value.trim() === '') valid = false;
                        }
                        
                        return valid;
                    }
                    
                    function validateSection4() {
                        const startTime = document.querySelector('[name="start_time"]');
                        const endTime = document.querySelector('[name="end_time"]');
                        const interviewTime = document.querySelector('[name="interview_time"]');
                        const days = document.querySelectorAll('input[name="days[]"]:checked');
                        const platforms = document.querySelectorAll('input[name="platforms[]"]:checked');
                        const canTeach = document.querySelectorAll('input[name="can_teach[]"]:checked');
                        
                        return startTime && startTime.value && 
                               endTime && endTime.value && 
                               interviewTime && interviewTime.value &&
                               days.length > 0 && 
                               platforms.length > 0 && 
                               canTeach.length > 0;
                    }
                    
                    function validateSection5() {
                        const terms = document.getElementById('termsAgreement');
                        return terms && terms.checked;
                    }
                    
                    function updateSectionStatus(sectionNum, isValid) {
                        const statusDiv = document.getElementById(`section${sectionNum}Status`);
                        if (!statusDiv) return;
                        
                        if (isValid) {
                            statusDiv.className = 'section-complete-badge';
                            statusDiv.style.cssText = 'margin-bottom: 0; padding: 0.25rem 0.75rem; font-size: 0.75rem;';
                            statusDiv.innerHTML = '<i class="fas fa-check-circle"></i><span>Complete</span>';
                        } else {
                            statusDiv.className = 'section-incomplete-badge';
                            statusDiv.style.cssText = 'margin-bottom: 0; padding: 0.25rem 0.75rem; font-size: 0.75rem;';
                            statusDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i><span>Incomplete</span>';
                        }
                    }
                    
                    function updateAllSectionStatuses() {
                        updateSectionStatus(1, validateSection1());
                        updateSectionStatus(2, validateSection2());
                        updateSectionStatus(3, validateSection3());
                        updateSectionStatus(4, validateSection4());
                        updateSectionStatus(5, validateSection5());
                    }

                    // Form validation and submit button state management
                    function checkFormValidity() {
                        const submitBtn = document.getElementById('submitBtn');
                        if (!submitBtn) return;
                        
                        // Check all required text/select fields
                        const requiredFields = ['first_name', 'last_name', 'birth_date', 'address', 'contact_number', 'email', 'education', 'esl_experience', 'resume_link', 'intro_video', 'start_time', 'end_time', 'interview_time'];
                        let allValid = true;
                        
                        for (const field of requiredFields) {
                            const input = document.querySelector(`[name="${field}"]`);
                            if (!input || !input.value || input.value.trim() === '') {
                                allValid = false;
                                break;
                            }
                        }
                        
                        // Check radio groups
                        if (!document.querySelector('input[name="work_type"]:checked')) allValid = false;
                        if (!document.querySelector('input[name="source"]:checked')) allValid = false;
                        
                        // Check checkbox groups
                        if (document.querySelectorAll('input[name="days[]"]:checked').length === 0) allValid = false;
                        if (document.querySelectorAll('input[name="platforms[]"]:checked').length === 0) allValid = false;
                        if (document.querySelectorAll('input[name="can_teach[]"]:checked').length === 0) allValid = false;
                        
                        // Check terms
                        const terms = document.getElementById('termsAgreement');
                        if (!terms || !terms.checked) allValid = false;
                        
                        // Check referrer name if referral selected
                        const referralRadio = document.getElementById('referralRadio');
                        if (referralRadio && referralRadio.checked) {
                            const referrerName = document.getElementById('referrerName');
                            if (!referrerName || !referrerName.value || referrerName.value.trim() === '') {
                                allValid = false;
                            }
                        }
                        
                        // Check WFH fields if work from home selected
                        const wfh = document.getElementById('workFromHome');
                        if (wfh && wfh.checked) {
                            const speedtest = document.getElementById('speedtest');
                            const mainDevice = document.getElementById('mainDevice');
                            const backupDevice = document.getElementById('backupDevice');
                            if (!speedtest || !speedtest.value || speedtest.value.trim() === '') allValid = false;
                            if (!mainDevice || !mainDevice.value || mainDevice.value.trim() === '') allValid = false;
                            if (!backupDevice || !backupDevice.value || backupDevice.value.trim() === '') allValid = false;
                        }
                        
                        // Update button state
                        if (allValid) {
                            submitBtn.classList.remove('bg-gray-400', 'text-gray-200', 'submit-disabled');
                            submitBtn.classList.add('bg-ogs-green', 'text-white', 'hover:bg-ogs-dark-green', 'cursor-pointer');
                            submitBtn.innerHTML = 'SUBMIT APPLICATION';
                            submitBtn.disabled = false;
                        } else {
                            submitBtn.classList.remove('bg-ogs-green', 'text-white', 'hover:bg-ogs-dark-green', 'cursor-pointer');
                            submitBtn.classList.add('bg-gray-400', 'text-gray-200', 'submit-disabled');
                            submitBtn.innerHTML = '<i class="fas fa-exclamation-circle"></i> <span>COMPLETE ALL REQUIRED FIELDS</span>';
                            submitBtn.disabled = true;
                        }
                    }
                    
                    // Run validation on page load and periodically
                    document.addEventListener('DOMContentLoaded', () => {
                        checkFormValidity();
                        updateAllSectionStatuses();
                        setInterval(() => {
                            checkFormValidity();
                            updateAllSectionStatuses();
                        }, 500);
                        
                        // Birth date validation
                        const birthDateInput = document.querySelector('input[name="birth_date"]');
                        if (birthDateInput) {
                            birthDateInput.addEventListener('change', function() {
                                const selectedDate = new Date(this.value);
                                const today = new Date();
                                const age = Math.floor((today - selectedDate) / (365.25 * 24 * 60 * 60 * 1000));
                                
                                // Remove previous error
                                const existingError = this.parentElement.querySelector('.birth-date-error');
                                if (existingError) existingError.remove();
                                this.classList.remove('field-error');
                                
                                if (selectedDate > today) {
                                    this.classList.add('field-error');
                                    const errorMsg = document.createElement('span');
                                    errorMsg.className = 'error-message birth-date-error';
                                    errorMsg.innerHTML = '<i class="fas fa-exclamation-circle"></i> Birth date cannot be in the future';
                                    this.parentElement.appendChild(errorMsg);
                                    this.value = '';
                                } else if (age < 18) {
                                    this.classList.add('field-error');
                                    const errorMsg = document.createElement('span');
                                    errorMsg.className = 'error-message birth-date-error';
                                    errorMsg.innerHTML = '<i class="fas fa-exclamation-circle"></i> You must be at least 18 years old to apply';
                                    this.parentElement.appendChild(errorMsg);
                                    this.value = '';
                                } else if (age > 70) {
                                    this.classList.add('field-error');
                                    const errorMsg = document.createElement('span');
                                    errorMsg.className = 'error-message birth-date-error';
                                    errorMsg.innerHTML = '<i class="fas fa-exclamation-circle"></i> Age cannot exceed 70 years';
                                    this.parentElement.appendChild(errorMsg);
                                    this.value = '';
                                }
                            });
                        }
                    });
                </script>

                <!-- JavaScript for dynamic form behavior -->
                <script>
                    // Work Setup: Enable/disable device fields based on work type
                    const workFromHome = document.getElementById('workFromHome');
                    const workAtSite = document.getElementById('workAtSite');
                    const speedtest = document.getElementById('speedtest');
                    const mainDevice = document.getElementById('mainDevice');
                    const backupDevice = document.getElementById('backupDevice');

                    function updateDeviceFields() {
                        const enabled = workFromHome && workFromHome.checked;
                        if (speedtest) speedtest.disabled = !enabled;
                        if (mainDevice) mainDevice.disabled = !enabled;
                        if (backupDevice) backupDevice.disabled = !enabled;
                    }

                    if (workFromHome) workFromHome.addEventListener('change', updateDeviceFields);
                    if (workAtSite) workAtSite.addEventListener('change', updateDeviceFields);
                    
                    // Initialize on page load
                    document.addEventListener('DOMContentLoaded', updateDeviceFields);

                    // Referral: Enable/disable referrer name based on source selection
                    const referralRadio = document.getElementById('referralRadio');
                    const referrerName = document.getElementById('referrerName');
                    const radios = document.getElementsByName('source');

                    radios.forEach(radio => {
                        radio.addEventListener('change', () => {
                            if (referralRadio && referralRadio.checked) {
                                if (referrerName) referrerName.disabled = false;
                            } else {
                                if (referrerName) {
                                    referrerName.disabled = true;
                                    referrerName.value = '';
                                }
                            }
                        });
                    });
                </script>
            </form>
        </div>
    </main>

    <!-- All Modals -->

    <!-- Modal: Please fill all the required fields -->
    <div id="requiredFieldsModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden p-4">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-sm sm:max-w-md text-center">
            <div class="mb-4">
                <i class="fas fa-exclamation-circle text-[#F65353] text-4xl"></i>
            </div>
            <h2 class="text-lg font-semibold text-[#0E335D] mb-4">Please fill all the required fields.
            </h2>
            <button onclick="closeRequiredFieldsModal()"
                class="mt-4 px-6 py-2 bg-[#F65353] text-white rounded-full font-medium">
                Okay
            </button>
        </div>
    </div>

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

    <!-- Cancel Confirmation Modal -->
    <div id="cancelModal"
        class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-sm sm:max-w-md text-left shadow-lg">
            <!-- Row 1: Header -->
            <h3
                class="text-base sm:text-lg font-bold text-gray-900 mb-2 text-center sm:text-left">
                Cancel Application?
            </h3>

            <!-- Row 2: Subheading -->
            <p class="text-sm sm:text-base text-gray-700 mb-6 text-center sm:text-left">
                Are you sure you want to cancel your application? All entered information will be lost and cannot be recovered.
            </p>

            <!-- Row 3: Buttons -->
            <div class="flex flex-col sm:flex-row justify-end gap-3 sm:gap-4">
                <button id="goBackCancelBtn"
                    class="w-full sm:w-auto px-6 py-2 rounded-full bg-[#9CA3AF] text-white font-semibold text-sm hover:bg-[#7B8790] hover:scale-105 transition-transform duration-200">
                    GO BACK
                </button>
                <button id="confirmCancelBtn" 
                    class="w-full sm:w-auto px-6 py-2 rounded-full bg-[#F65353] text-white font-semibold text-sm hover:bg-[#E53E3E] hover:scale-105 transition-transform duration-200">
                    CANCEL APPLICATION
                </button>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg p-8 max-w-sm w-full mx-4">
            <div class="text-center">
                <i class="fas fa-spinner fa-spin text-4xl text-ogs-green mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Submitting Application...</h3>
                <p class="text-sm text-gray-600 mb-4">Please wait while we process your application.</p>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div id="progressBar" class="bg-ogs-green h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Home Confirmation Modal -->
    <div id="homeModal"
        class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-sm sm:max-w-md text-left shadow-lg">
            <!-- Row 1: Header -->
            <h3
                class="text-base sm:text-lg font-bold text-gray-900 mb-2 text-center sm:text-left">
                Leave Application Page?
            </h3>

            <!-- Row 2: Subheading -->
            <p class="text-sm sm:text-base text-gray-700 mb-6 text-center sm:text-left">
                Are you sure you want to go back to the home page? All entered information will be lost and cannot be recovered.
            </p>

            <!-- Row 3: Buttons -->
            <div class="flex flex-col sm:flex-row justify-end gap-3 sm:gap-4">
                <button id="goBackHomeBtn"
                    class="w-full sm:w-auto px-6 py-2 rounded-full bg-[#9CA3AF] text-white font-semibold text-sm hover:bg-[#7B8790] hover:scale-105 transition-transform duration-200">
                    GO BACK
                </button>
                <button id="confirmHomeBtn" 
                    class="w-full sm:w-auto px-6 py-2 rounded-full bg-[#F65353] text-white font-semibold text-sm hover:bg-[#E53E3E] hover:scale-105 transition-transform duration-200">
                    LEAVE PAGE
                </button>
            </div>
        </div>
    </div>

    <script>
        // --- SUBMIT FLOW ---
        const form = document.getElementById('applicationForm');
        const submitBtn = document.getElementById('submitBtn');
        const submitModal = document.getElementById('submitModal');
        const goBackSubmitBtn = document.getElementById('goBackSubmitBtn');
        const confirmSubmitBtn = document.getElementById('confirmSubmitBtn');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const progressBar = document.getElementById('progressBar');
        const requiredFieldsModal = document.getElementById('requiredFieldsModal');

        // --- CANCEL FLOW ---
        const cancelBtn = document.getElementById('cancelBtn');
        const cancelModal = document.getElementById('cancelModal');
        const goBackCancelBtn = document.getElementById('goBackCancelBtn');
        const confirmCancelBtn = document.getElementById('confirmCancelBtn');

        // --- HOME FLOW ---
        const homeBtn = document.getElementById('homeBtn');
        const homeBtnMobile = document.getElementById('homeBtnMobile');
        const homeModal = document.getElementById('homeModal');
        const goBackHomeBtn = document.getElementById('goBackHomeBtn');
        const confirmHomeBtn = document.getElementById('confirmHomeBtn');

        let shouldSubmitForm = false;

        // --- Field Mapping ---
        const fieldNames = {
            'first_name': 'First Name',
            'last_name': 'Last Name',
            'birth_date': 'Birth Date',
            'address': 'Address',
            'contact_number': 'Contact Number',
            'email': 'Email',
            'education': 'Highest Educational Attainment',
            'esl_experience': 'ESL Teaching Experience',
            'resume_link': 'Resume Link',
            'intro_video': 'Intro Video',
            'work_type': 'Work Setup',
            'start_time': 'Start Time',
            'end_time': 'End Time',
            'source': 'How Did You Hear About Us',
            'referrer_name': 'Referrer Name',
            'interview_time': 'Interview Time',
            'terms_agreement': 'Terms and Conditions Agreement'
        };

        // --- Form Validation ---
        function validateForm() {
            console.log('Starting form validation...');
            const requiredFields = [
                'first_name', 'last_name', 'birth_date', 'address', 'contact_number', 'email',
                'education', 'esl_experience', 'resume_link', 'intro_video', 'work_type',
                'start_time', 'end_time', 'interview_time', 'terms_agreement'
            ];

            const missingFields = [];
            const formData = new FormData(form);
            console.log('Form data collected');

            // Interview time check
            const interviewTime = formData.get('interview_time');
            if (interviewTime) {
                const now = new Date();
                const interviewDate = new Date(interviewTime);
                if (interviewDate < now) {
                    missingFields.push('Interview time must not be before the current date and time');
                }
            }

            // Basic required fields
            requiredFields.forEach(fieldName => {
                const value = formData.get(fieldName);
                if (!value || value.trim() === '') {
                    missingFields.push(fieldNames[fieldName] || fieldName);
                }
            });

            // Special handling for radio buttons (source field)
            const sourceValue = formData.get('source');
            if (!sourceValue) {
                missingFields.push('How Did You Hear About Us');
            }

            // Referral check
            const referralRadio = document.getElementById('referralRadio');
            if (referralRadio && referralRadio.checked) {
                const referrerName = formData.get('referrer_name');
                if (!referrerName || referrerName.trim() === '') {
                    missingFields.push('Referrer Name');
                }
            }

            // Work from home device specs
            const workFromHome = document.getElementById('workFromHome');
            if (workFromHome && workFromHome.checked) {
                const speedtest = formData.get('speedtest');
                const mainDevice = formData.get('main_device');
                const backupDevice = formData.get('backup_device');
                
                if (!speedtest || speedtest.trim() === '') {
                    missingFields.push('Speedtest Link');
                }
                if (!mainDevice || mainDevice.trim() === '') {
                    missingFields.push('Main Device Specs');
                }
                if (!backupDevice || backupDevice.trim() === '') {
                    missingFields.push('Backup Device Specs');
                }
            }

            // At least one checkbox from groups
            if (document.querySelectorAll('input[name="days[]"]:checked').length === 0) {
                missingFields.push('At least one day of availability');
            }
            if (document.querySelectorAll('input[name="platforms[]"]:checked').length === 0) {
                missingFields.push('At least one platform familiarity');
            }
            if (document.querySelectorAll('input[name="can_teach[]"]:checked').length === 0) {
                missingFields.push('At least one teaching option');
            }

            // Terms agreement check
            const termsAgreement = document.getElementById('termsAgreement');
            if (!termsAgreement || !termsAgreement.checked) {
                missingFields.push('Terms and Conditions Agreement');
            }

            console.log('Validation complete. Missing fields:', missingFields);
            return missingFields;
        }

        // --- Highlight Missing Fields ---
        function highlightMissingFields(missingFields) {
            document.querySelectorAll('.border-red-500').forEach(el => {
                el.classList.remove('border-red-500');
            });

            const fieldSelectors = {
                'First Name': 'input[name="first_name"]',
                'Last Name': 'input[name="last_name"]',
                'Birth Date': 'input[name="birth_date"]',
                'Address': 'input[name="address"]',
                'Contact Number': 'input[name="contact_number"]',
                'Email': 'input[name="email"]',
                'Highest Educational Attainment': 'select[name="education"]',
                'ESL Teaching Experience': 'select[name="esl_experience"]',
                'Resume Link': 'input[name="resume_link"]',
                'Intro Video': 'input[name="intro_video"]',
                'Work Setup': 'input[name="work_type"]',
                'Start Time': 'select[name="start_time"]',
                'End Time': 'select[name="end_time"]',
                'How Did You Hear About Us': 'input[name="source"], select[name="source"]',
                'Referrer Name': 'input[name="referrer_name"]',
                'Speedtest Link': 'input[name="speedtest"]',
                'Main Device Specs': 'input[name="main_device"]',
                'Backup Device Specs': 'input[name="backup_device"]',
                'Interview Time': 'input[name="interview_time"]',
                'Interview time must not be before the current date and time': 'input[name="interview_time"]',
                'Terms and Conditions Agreement': 'input[name="terms_agreement"]'
            };

            missingFields.forEach(field => {
                const selector = fieldSelectors[field];
                if (selector) {
                    document.querySelectorAll(selector).forEach(el => {
                        el.classList.add('border-red-500');
                    });
                }
            });

            if (missingFields.includes('At least one day of availability')) {
                document.querySelectorAll('input[name="days[]"]').forEach(el => {
                    el.classList.add('border-red-500');
                });
                // Also highlight the container without changing size
                const daysContainer = document.querySelector('input[name="days[]"]').closest('.grid');
                if (daysContainer) {
                    daysContainer.classList.add('border-red-500', 'border-2', 'rounded-lg');
                }
            }
            if (missingFields.includes('At least one platform familiarity')) {
                document.querySelectorAll('input[name="platforms[]"]').forEach(el => {
                    el.classList.add('border-red-500');
                });
                // Also highlight the container without changing size
                const platformsContainer = document.querySelector('input[name="platforms[]"]').closest('.space-y-4');
                if (platformsContainer) {
                    platformsContainer.classList.add('border-red-500', 'border-2', 'rounded-lg');
                }
            }
            if (missingFields.includes('At least one teaching option')) {
                document.querySelectorAll('input[name="can_teach[]"]').forEach(el => {
                    el.classList.add('border-red-500');
                });
                // Also highlight the container without changing size
                const canTeachContainer = document.querySelector('input[name="can_teach[]"]').closest('.space-y-4');
                if (canTeachContainer) {
                    canTeachContainer.classList.add('border-red-500', 'border-2', 'rounded-lg');
                }
            }
        }

        // --- Terms Modal Functions ---
        function showTermsModal() {
            const modal = document.getElementById('termsModal');
            if (modal) {
                modal.classList.remove('hidden');
            } else {
                console.error('Terms modal not found');
            }
        }
        
        function hideTermsModal() {
            const modal = document.getElementById('termsModal');
            if (modal) {
                modal.classList.add('hidden');
                
                // Auto-check the terms agreement checkbox when clicking "I Understand"
                const termsCheckbox = document.getElementById('termsAgreement');
                if (termsCheckbox && !termsCheckbox.checked) {
                    termsCheckbox.checked = true;
                    // Trigger the change event to update status
                    updateTermsStatus();
                }
            }
        }
        
        // --- Required Fields Modal Functions ---
        function showRequiredFieldsModal() {
            const modal = document.getElementById('requiredFieldsModal');
            if (modal) {
                modal.classList.remove('hidden');
            } else {
                console.error('Required fields modal not found');
            }
        }
        
        function closeRequiredFieldsModal() {
            const modal = document.getElementById('requiredFieldsModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }
        
        // --- Terms Agreement Status Management ---
        function updateTermsStatus() {
            const termsCheckbox = document.getElementById('termsAgreement');
            const statusIndicator = document.getElementById('termsStatusIndicator');
            const submitBtn = document.getElementById('submitBtn');
            
            if (termsCheckbox.checked) {
                // Hide indicator
                statusIndicator.classList.add('hidden');
                // Enable submit button
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                submitBtn.classList.add('hover:bg-ogs-dark-green');
            } else {
                // Show indicator
                statusIndicator.classList.remove('hidden');
                // Disable submit button visually
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                submitBtn.classList.remove('hover:bg-ogs-dark-green');
            }
        }
        
        // Add event listener to terms checkbox
        document.addEventListener('DOMContentLoaded', function() {
            const termsCheckbox = document.getElementById('termsAgreement');
            if (termsCheckbox) {
                termsCheckbox.addEventListener('change', updateTermsStatus);
                // Initial check
                updateTermsStatus();
            }
        });

        // --- Submit Flow ---
        submitBtn.addEventListener('click', (e) => {
            e.preventDefault();
            console.log('Submit button clicked');
            const missingFields = validateForm();
            console.log('Missing fields:', missingFields);
            highlightMissingFields(missingFields);

            if (missingFields.length > 0) {
                console.log('Validation failed, showing required fields modal');
                // Show terms indicator if terms agreement is missing
                if (missingFields.includes('Terms and Conditions Agreement')) {
                    updateTermsStatus();
                }
                showRequiredFieldsModal();
            } else {
                console.log('Validation passed, showing submit modal');
                if (submitModal) {
                    submitModal.classList.remove('hidden');
                } else {
                    console.error('Submit modal not found');
                }
            }
        });

        // Go back from submit modal
        goBackSubmitBtn.addEventListener('click', (e) => {
            e.preventDefault();
            shouldSubmitForm = false;
            if (submitModal) {
                submitModal.classList.add('hidden'); // stays on form with data intact
            }
        });

        // Confirm submit
        confirmSubmitBtn.addEventListener('click', (e) => {
            e.preventDefault();
            shouldSubmitForm = true;

            if (submitModal) {
                submitModal.classList.add('hidden');
            }
            if (loadingOverlay) {
                loadingOverlay.classList.remove('hidden');
            }

            // Simulate progress bar
            let progress = 0;
            const interval = setInterval(() => {
                progress += 10;
                if (progressBar) {
                    progressBar.style.width = progress + "%";
                }

                if (progress >= 100) {
                    clearInterval(interval);
                    // Small delay to ensure progress bar completes
                    setTimeout(() => {
                        if (shouldSubmitForm) {
                            console.log('Submitting form...');
                            console.log('Form data:', new FormData(form));
                            form.submit(); // finally send to DB
                        }
                    }, 100);
                }
            }, 100);
        });

        // Prevent accidental submit unless confirmed
        form.addEventListener('submit', function(e) {
            console.log('Form submit event triggered, shouldSubmitForm:', shouldSubmitForm);
            if (!shouldSubmitForm) {
                console.log('Preventing form submission');
                e.preventDefault();
                return false;
            } else {
                console.log('Allowing form submission');
                // Hide loading overlay when form actually submits
                if (loadingOverlay) {
                    loadingOverlay.classList.add('hidden');
                }
            }
        });

        // --- CANCEL FLOW EVENT LISTENERS ---
        // Cancel button click
        cancelBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (cancelModal) {
                cancelModal.classList.remove('hidden');
            }
        });

        // Go back from cancel modal
        goBackCancelBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (cancelModal) {
                cancelModal.classList.add('hidden');
            }
        });

        // Confirm cancel
        confirmCancelBtn.addEventListener('click', (e) => {
            e.preventDefault();
            // Navigate to cancel page
            window.location.href = '{{ route("application.form.cancel") }}';
        });

        // --- HOME FLOW EVENT LISTENERS ---
        // Home button click (desktop)
        if (homeBtn) {
            homeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (homeModal) {
                    homeModal.classList.remove('hidden');
                }
            });
        }

        // Home button click (mobile)
        if (homeBtnMobile) {
            homeBtnMobile.addEventListener('click', (e) => {
                e.preventDefault();
                if (homeModal) {
                    homeModal.classList.remove('hidden');
                }
            });
        }

        // Go back from home modal
        if (goBackHomeBtn) {
            goBackHomeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (homeModal) {
                    homeModal.classList.add('hidden');
                }
            });
        }

        // Confirm home navigation
        if (confirmHomeBtn) {
            confirmHomeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                window.location.href = '{{ route("landing") }}';
            });
        }

        // --- Extra: Instant red highlight on blur/input ---
        document.addEventListener('DOMContentLoaded', function() {
            const requiredFields = document.querySelectorAll(
                '#applicationForm input[required], #applicationForm select[required]'
            );

            requiredFields.forEach(field => {
                field.addEventListener('blur', function() {
                    if (!field.value.trim()) {
                        field.classList.add('border-red-500');
                    } else {
                        field.classList.remove('border-red-500');
                    }
                });
                field.addEventListener('input', function() {
                    if (field.value.trim()) {
                        field.classList.remove('border-red-500');
                    }
                });
            });
            
            // Add checkbox event listeners to clear red highlighting
            const checkboxGroups = [
                { name: 'days[]', container: 'input[name="days[]"]' },
                { name: 'platforms[]', container: 'input[name="platforms[]"]' },
                { name: 'can_teach[]', container: 'input[name="can_teach[]"]' }
            ];
            
            checkboxGroups.forEach(group => {
                const checkboxes = document.querySelectorAll(`input[name="${group.name}"]`);
                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        // Clear red highlighting from all checkboxes in this group
                        checkboxes.forEach(cb => {
                            cb.classList.remove('border-red-500');
                        });
                        
                        // Clear red highlighting from container
                        const container = document.querySelector(group.container).closest('.grid, .space-y-4');
                        if (container) {
                            container.classList.remove('border-red-500', 'border-2', 'rounded-lg');
                        }
                    });
                });
            });
        });
    </script>

                        </div>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <!-- Terms and Conditions Modal -->
    <div id="termsModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-4xl max-h-[80vh] overflow-y-auto shadow-lg">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-ogs-dark-navy">Terms and Conditions</h3>
                <button onclick="hideTermsModal()" class="text-gray-500 hover:text-gray-700 text-2xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Content -->
            <div class="space-y-4 text-sm text-gray-700 leading-relaxed">
                <div>
                    <h4 class="font-semibold text-ogs-dark-navy mb-2">1. Data Collection and Processing</h4>
                    <p>By submitting this application, you consent to the collection, processing, and storage of your personal information by OGS Global Solutions for employment purposes. We collect information including but not limited to:</p>
                    <ul class="list-disc list-inside ml-4 mt-2">
                        <li>Personal identification details (name, birth date, address)</li>
                        <li>Contact information (phone number, email address)</li>
                        <li>Educational background and work experience</li>
                        <li>Resume and introductory video</li>
                        <li>Work preferences and availability</li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold text-ogs-dark-navy mb-2">2. Data Privacy Act Compliance</h4>
                    <p>Our data processing practices comply with the <strong>Data Privacy Act of 2012 (Republic Act No. 10173)</strong> of the Philippines. We ensure that:</p>
                    <ul class="list-disc list-inside ml-4 mt-2">
                        <li>Personal data is collected for legitimate purposes only</li>
                        <li>Data is processed fairly and lawfully</li>
                        <li>Data is accurate and up-to-date</li>
                        <li>Data is protected against unauthorized access</li>
                        <li>Data is retained only as long as necessary</li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold text-ogs-dark-navy mb-2">3. Use of Information</h4>
                    <p>Your personal information will be used for:</p>
                    <ul class="list-disc list-inside ml-4 mt-2">
                        <li>Employment application processing</li>
                        <li>Background verification and screening</li>
                        <li>Interview scheduling and communication</li>
                        <li>Employment decision-making</li>
                        <li>Legal compliance and reporting requirements</li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold text-ogs-dark-navy mb-2">4. Data Security</h4>
                    <p>We implement appropriate technical and organizational measures to protect your personal data against unauthorized access, alteration, disclosure, or destruction.</p>
                </div>
                
                <div>
                    <h4 class="font-semibold text-ogs-dark-navy mb-2">5. Your Rights</h4>
                    <p>Under the Data Privacy Act, you have the right to:</p>
                    <ul class="list-disc list-inside ml-4 mt-2">
                        <li>Be informed about the processing of your personal data</li>
                        <li>Object to the processing of your personal data</li>
                        <li>Access and correct your personal data</li>
                        <li>Request the erasure or blocking of your personal data</li>
                        <li>Data portability</li>
                        <li>File a complaint with the National Privacy Commission</li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold text-ogs-dark-navy mb-2">6. Contact Information</h4>
                    <p>For any questions or concerns regarding your personal data, you may contact our Data Protection Officer at:</p>
                    <ul class="list-disc list-inside ml-4 mt-2">
                        <li>Email: privacy@ogsglobalsolutions.com</li>
                        <li>Phone: +63 (XXX) XXX-XXXX</li>
                        <li>Address: [Company Address]</li>
                    </ul>
                </div>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-6">
                    <p class="text-sm text-yellow-800">
                        <strong>Important:</strong> By checking the agreement checkbox and submitting this application, you acknowledge that you have read, understood, and agree to these Terms and Conditions and consent to the processing of your personal data as described above.
                    </p>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="flex justify-end mt-6">
                <button onclick="hideTermsModal()" 
                    class="px-6 py-2 bg-ogs-navy text-white rounded-full font-medium hover:bg-ogs-dark-navy transition-colors">
                    I Understand
                </button>
            </div>
        </div>
    </div>
</body>

</html>

