<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OGS - Start Your Teaching Journey</title>
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
                <a href="{{ route('login') }}">
                    <button
                        class="px-6 text-xs py-2 border border-ogs-navy text-ogs-navy rounded-full hover:bg-ogs-navy hover:text-white transition-colors">
                        LOG IN
                    </button>
                </a>
                <button onclick="window.location.href='{{ route('application.form') }}'"
                    class="px-6 shadow-lg text-xs py-2 bg-ogs-green font-normal text-white rounded-full hover:bg-ogs-dark-green transition-colors">
                    APPLY NOW
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
        <a href="{{ route('login') }}">
            <button
                class="w-full px-6 text-xs py-2 border border-ogs-navy text-ogs-navy rounded-full hover:bg-ogs-navy hover:text-white transition-colors">
                LOG IN
            </button>
        </a>
        <button onclick="window.location.href='{{ route('application.form') }}'"
            class="w-full shadow-lg px-6 text-xs py-2 bg-ogs-green font-normal text-white rounded-full hover:bg-ogs-dark-green transition-colors">
            APPLY NOW
        </button>
    </div>

    <script>
        const menuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        menuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    </script>


    <!-- Hero Section -->
    <section class="w-full py-12">
        <div class="flex flex-col lg:flex-row items-center gap-8">
            <!-- Left Content with Green BG (flush start) -->
            <div
                class="flex-1 w-full bg-ogs-green rounded-tr-[100px] sm:rounded-tr-[200px] lg:rounded-tr-[200px] 
               p-6 sm:p-10 lg:p-16 flex flex-col justify-center min-h-[400px] lg:h-[548px]">

                <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold mb-6 text-ogs-dark-navy">
                    START YOUR<br>
                    TEACHING JOURNEY<br>
                    WITH OGS!
                </h1>

                <p class="text-sm sm:text-base md:text-lg mb-8 font-semibold text-ogs-dark-navy opacity-80">
                    Outsourcing Global Solutions is a premier provider of English as a Second Language (ESL) services,
                    specializing in connecting learners with highly-qualified Filipino tutors.
                </p>

                <button onclick="window.location.href='{{ route('application.form') }}'"
                    class="bg-ogs-navy text-white px-8 py-3 rounded-full font-semibold hover:bg-ogs-dark-navy transition-colors w-1/2">
                    APPLY NOW
                </button>
            </div>

            <!-- Right Card -->
            <div class="flex-1 w-full flex justify-center px-4 sm:px-6 lg:px-8">
                <img src="images/hiring-card.png" alt="We are Hiring - Online ESL Tutor">
            </div>
        </div>
    </section>


    <!-- Hiring Section -->
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-24 items-center">

                <!-- Image -->
                <div class="flex-1 w-full mb-10 lg:mb-0 flex me-12 justify-center">
                    <img src="images/we-r-h.png" alt="We are Hiring - Online ESL Tutor"
                        class="max-w-full h-auto rounded-3xl">
                </div>

                <!-- Content -->
                <div class="bg-ogs-green rounded-3xl p-6 sm:p-8 lg:p-10 text-white">
                    <div class="mb-6 text-center lg:text-left">
                        <div class="text-base sm:text-lg font-semibold text-ogs-dark-navy mb-2">We are looking for</div>
                        <div class="text-2xl sm:text-3xl font-bold text-ogs-dark-navy">ESL Online Tutors</div>
                    </div>

                    <div class="mb-8">
                        <div class="text-base sm:text-lg font-semibold text-ogs-dark-navy mb-4">Qualifications:</div>
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-ogs-dark-navy mr-3 mt-0.5 flex-shrink-0"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="text-sm sm:text-base text-ogs-dark-navy font-medium">At least college-level
                                    education in any English language-related course</span>
                            </div>
                            <div class="flex items-start">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-ogs-dark-navy mr-3 mt-0.5 flex-shrink-0"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="text-sm sm:text-base text-ogs-dark-navy font-medium">Fluent in English with
                                    clear communication skills and a neutral accent</span>
                            </div>
                            <div class="flex items-start">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-ogs-dark-navy mr-3 mt-0.5 flex-shrink-0"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="text-sm sm:text-base text-ogs-dark-navy font-medium">Responsible,
                                    committed, and friendly</span>
                            </div>
                            <div class="flex items-start">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-ogs-dark-navy mr-3 mt-0.5 flex-shrink-0"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="text-sm sm:text-base text-ogs-dark-navy font-medium">Computer
                                    literate</span>
                            </div>
                            <div class="flex items-start">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-ogs-dark-navy mr-3 mt-0.5 flex-shrink-0"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="text-sm sm:text-base text-ogs-dark-navy font-medium">Age: 18-45 years
                                    old</span>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('application.form') }}"
                        class="bg-ogs-navy text-white px-6 sm:px-8 py-3 rounded-full font-semibold hover:bg-ogs-dark-navy transition-colors w-full text-center inline-block">
                        APPLY NOW
                    </a>


                </div>
            </div>
        </div>
    </section>


    <!-- OGS Connect Section -->
    <section class="py-16 sm:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <!-- Heading -->
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-ogs-green mb-4 sm:mb-6">
                OGS Connect – Where Your OGS Journey Begins
            </h2>

            <!-- Description -->
            <p class="text-sm sm:text-base text-gray-600 mb-8 sm:mb-12 max-w-3xl mx-auto">
                With OGS Connect, applying is simple and fast. From filling out your form to starting your first class,
                everything is done in one smooth process. No hassle, no confusion — just a clear path to becoming part
                of OGS.
            </p>

            <!-- Hiring Process -->
            <div class="mb-10 sm:mb-12">
                <h3 class="text-xl sm:text-2xl font-semibold text-ogs-dark-navy mb-6 sm:mb-8">
                    Our Hiring Process:
                </h3>

                <!-- Steps Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-6">
                    <!-- Step 1 -->
                    <div class="bg-ogs-green rounded-2xl p-6 text-[#0E335D]">
                        <div
                            class="w-14 h-14 sm:w-16 sm:h-16 bg-white bg-opacity-20 rounded-xl flex items-center justify-center mb-4 mx-auto">
                            <img src="images/l1.png" alt="Step Icon" class="w-7 h-7 sm:w-8 sm:h-8">
                        </div>
                        <h4 class="font-semibold text-sm sm:text-base">Fill out the google form.</h4>
                    </div>

                    <!-- Step 2 -->
                    <div class="bg-ogs-green rounded-2xl p-6 text-[#0E335D]">
                        <div
                            class="w-14 h-14 sm:w-16 sm:h-16 bg-white bg-opacity-20 rounded-xl flex items-center justify-center mb-4 mx-auto">
                            <img src="images/l2.png" alt="Phone Call Icon" class="w-7 h-7 sm:w-8 sm:h-8">
                        </div>
                        <h4 class="font-semibold text-sm sm:text-base">Await a phone call for your initial interview.
                        </h4>
                    </div>

                    <!-- Step 3 -->
                    <div class="bg-ogs-green rounded-2xl p-6 text-[#0E335D]">
                        <div
                            class="w-14 h-14 sm:w-16 sm:h-16 bg-white bg-opacity-20 rounded-xl flex items-center justify-center mb-4 mx-auto">
                            <img src="images/l3.png" alt="Training Icon" class="w-7 h-7 sm:w-8 sm:h-8">
                        </div>
                        <h4 class="font-semibold text-sm sm:text-base">Upon successful evaluation, proceed with
                            training and demo.</h4>
                    </div>

                    <!-- Step 4 -->
                    <div class="bg-ogs-green rounded-2xl p-6 text-[#0E335D]">
                        <div
                            class="w-14 h-14 sm:w-16 sm:h-16 bg-white bg-opacity-20 rounded-xl flex items-center justify-center mb-4 mx-auto">
                            <img src="images/l4.png" alt="Onboarding Icon" class="w-7 h-7 sm:w-8 sm:h-8">
                        </div>
                        <h4 class="font-semibold text-sm sm:text-base">If qualified, advance to the onboarding stage
                        </h4>
                    </div>

                    <!-- Step 5 -->
                    <div class="bg-ogs-green rounded-2xl p-6 text-[#0E335D]">
                        <div
                            class="w-14 h-14 sm:w-16 sm:h-16 bg-white bg-opacity-20 rounded-xl flex items-center justify-center mb-4 mx-auto">
                            <img src="images/l5.png" alt="Start Teaching Icon" class="w-7 h-7 sm:w-8 sm:h-8">
                        </div>
                        <h4 class="font-semibold text-sm sm:text-base">Start Teaching</h4>
                    </div>
                </div>
            </div>

            <!-- Apply Button -->
            <button onclick="window.location.href='{{ route('application.form') }}'"
                class="bg-ogs-navy text-white px-8 sm:px-12 py-3 sm:py-4 rounded-full font-semibold text-base sm:text-lg hover:bg-ogs-dark-navy transition-colors">
                APPLY NOW
            </button>
        </div>
    </section>


    <!-- Footer -->
    <footer class="bg-ogs-dark-navy text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">About Us</h3>
                    <p class="text-gray-300 text-sm mb-4">
                        Provide high-quality and affordable ESL education for individuals looking to improve their
                        language skills.
                    </p>
                    <div class="flex items-center">
                        <div class="w-10 h-10 mr-3">
                            <img src="{{ asset('images/logo.png') }}" alt="Icon"
                                class="w-full h-full object-cover rounded-full">
                        </div>

                        <div>
                            <div class="font-semibold">OGS Outsourcing Solutions</div>
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Follow us</h3>
                    <div class="space-y-2 text-sm text-gray-300">
                        <div class="flex items-center space-x-2">
                            <img src="{{ asset('images/fb.png') }}" alt="Book" class="w-4 h-4">
                            <span>Outsourcing Global Solutions - OGS</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <img src="{{ asset('images/fb.png') }}" alt="Book" class="w-4 h-4">
                            <span>Outsourcing Global Solutions | Davao City</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <img src="{{ asset('images/ig.png') }}" alt="Camera" class="w-5 h-5">
                            <span>ogs.2018</span>
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Get in touch</h3>
                    <div class="space-y-2 text-sm text-gray-300">
                        <div class="flex items-center space-x-2">
                            <img src="{{ asset('images/address.png') }}" alt="Location" class="w-4 h-4">
                            <span>Davao, Davao City, 8000</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <img src="{{ asset('images/email.png') }}" alt="Email" class="w-4 h-4">
                            <span>ogs.recruitment@gmail.com</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <img src="{{ asset('images/phone.png') }}" alt="Phone" class="w-4 h-4">
                            <span>+63 939 634 2922</span>
                        </div>
                    </div>

                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-sm text-gray-400">
                <p>© 2023, Outsourcing Global Solutions (OGS). All Rights Reserved.</p>
                <p>OGS Connect - Developed by Team Lupin, University of Mindanao</p>
            </div>
        </div>
    </footer>
</body>

</html>
