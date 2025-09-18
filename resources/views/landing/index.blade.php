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
                        'ogs-dark-navy': '#1E293B'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm" style="height: 75px;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <img src="{{ asset('images/logo.png') }}" 
                            alt="GLS Scheduling" 
                            class="w-full h-full object-contain">
                        <div class="ml-3">
                            <div class="text-lg font-bold text-ogs-dark-navy">OUTSOURCING</div>
                            <div class="text-xs text-gray-600">GLOBAL SOLUTIONS</div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('login') }}">
                        <button class="px-6 py-2 border border-ogs-navy text-ogs-navy rounded-full hover:bg-ogs-navy hover:text-white transition-colors">
                            LOG IN
                        </button>
                    </a>
                    <button class="px-6 py-2 bg-ogs-green text-white rounded-full hover:bg-ogs-dark-green transition-colors">
                        APPLY NOW
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="w-full py-12">
    <div class="flex flex-col lg:flex-row items-center gap-8">

        <!-- Left Content with Green BG (sticks to very left) -->
        <div class="flex-1 w-full bg-green-400 rounded-tr-[200px] p-10 lg:p-16 flex flex-col justify-center" style="height: 678px;">
        <h1 class="text-4xl md:text-5xl font-bold mb-6 text-ogs-dark-navy">
            START YOUR<br>
            TEACHING JOURNEY<br>
            WITH OGS!
        </h1>
        <p class="text-base md:text-lg mb-8 text-ogs-dark-navy opacity-80">
            Outsourcing Global Solutions is a premier provider of English as a Second Language (ESL) services, specializing in connecting learners with highly-qualified Filipino tutors.
        </p>
        <button class="bg-ogs-navy text-white px-8 py-3 rounded-full font-semibold hover:bg-ogs-dark-navy transition-colors w-full md:w-auto">
            APPLY NOW
        </button>
        </div>

        <!-- Right Card -->
        <div class="flex-1 w-full px-4 sm:px-6 lg:px-8 flex justify-center">
            <img src="images/hiring-card.png" alt="We are Hiring - Online ESL Tutor">
        </div>


    </div>
    </section>

    <!-- Hiring Section -->
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="relative">
                    <div class="bg-gradient-to-br from-ogs-green to-ogs-dark-green rounded-3xl p-12 text-center">
                        <div class="relative">
                            <div class="absolute inset-0 bg-white opacity-10 rounded-full transform -rotate-12"></div>
                            <div class="absolute inset-0 bg-white opacity-5 rounded-full transform rotate-12"></div>
                            <h2 class="text-4xl font-bold text-ogs-dark-navy mb-4 relative z-10">
                                WE ARE<br>
                                <span class="text-5xl">HIRING</span>
                            </h2>
                            <div class="flex justify-center items-center relative z-10">
                                <svg class="w-8 h-8 text-ogs-dark-navy" fill="currentColor" viewBox="0 0 20 20">
                                    <path fillRule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clipRule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-ogs-green rounded-3xl p-8 text-white">
                    <div class="mb-6">
                        <div class="text-lg font-semibold text-ogs-dark-navy mb-2">We are looking for</div>
                        <div class="text-3xl font-bold text-ogs-dark-navy">ESL Online Tutors</div>
                    </div>
                    <div class="mb-8">
                        <div class="text-lg font-semibold text-ogs-dark-navy mb-4">Qualifications:</div>
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-ogs-dark-navy mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd"/>
                                </svg>
                                <span class="text-ogs-dark-navy">At least college-level education in any English language-related course</span>
                            </div>
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-ogs-dark-navy mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd"/>
                                </svg>
                                <span class="text-ogs-dark-navy">Fluent in English with clear communication skills and a neutral accent</span>
                            </div>
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-ogs-dark-navy mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd"/>
                                </svg>
                                <span class="text-ogs-dark-navy">Responsible, committed, and friendly</span>
                            </div>
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-ogs-dark-navy mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd"/>
                                </svg>
                                <span class="text-ogs-dark-navy">Computer literate</span>
                            </div>
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-ogs-dark-navy mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd"/>
                                </svg>
                                <span class="text-ogs-dark-navy">Age: 18-45 years old</span>
                            </div>
                        </div>
                    </div>
                    <button class="bg-ogs-navy text-white px-8 py-3 rounded-full font-semibold hover:bg-ogs-dark-navy transition-colors w-full">
                        APPLY NOW
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- OGS Connect Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl font-bold text-ogs-green mb-6">
                OGS Connect – Where Your OGS Journey Begins
            </h2>
            <p class="text-lg text-gray-600 mb-12 max-w-3xl mx-auto">
                With OGS Connect, applying is simple and fast. From filling out your form to starting your first class, everything is done in one smooth process. No hassle, no confusion — just a clear path to becoming part of OGS.
            </p>
            
            <div class="mb-12">
                <h3 class="text-2xl font-semibold text-ogs-dark-navy mb-8">Our Hiring Process:</h3>
                <div class="grid md:grid-cols-5 gap-6">
                    <div class="bg-ogs-green rounded-2xl p-6 text-white">
                        <div class="w-16 h-16 bg-white bg-opacity-20 rounded-xl flex items-center justify-center mb-4 mx-auto">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                <path fillRule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 102 0V3h4v1a1 1 0 102 0V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm2.5 7a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm2.45 4a2.5 2.5 0 10-4.9 0h4.9zM12 9a1 1 0 100 2h3a1 1 0 100-2h-3zm-1 4a1 1 0 011-1h3a1 1 0 110 2h-3a1 1 0 01-1-1z" clipRule="evenodd"/>
                            </svg>
                        </div>
                        <h4 class="font-semibold mb-2">Fill out the google form.</h4>
                    </div>
                    <div class="bg-ogs-green rounded-2xl p-6 text-white">
                        <div class="w-16 h-16 bg-white bg-opacity-20 rounded-xl flex items-center justify-center mb-4 mx-auto">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                            </svg>
                        </div>
                        <h4 class="font-semibold mb-2">Await a phone call for your initial interview.</h4>
                    </div>
                    <div class="bg-ogs-green rounded-2xl p-6 text-white">
                        <div class="w-16 h-16 bg-white bg-opacity-20 rounded-xl flex items-center justify-center mb-4 mx-auto">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                            </svg>
                        </div>
                        <h4 class="font-semibold mb-2">Upon successful evaluation, proceed with training and demo.</h4>
                    </div>
                    <div class="bg-ogs-green rounded-2xl p-6 text-white">
                        <div class="w-16 h-16 bg-white bg-opacity-20 rounded-xl flex items-center justify-center mb-4 mx-auto">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path fillRule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clipRule="evenodd"/>
                            </svg>
                        </div>
                        <h4 class="font-semibold mb-2">If qualified, advance to the onboarding stage</h4>
                    </div>
                    <div class="bg-ogs-green rounded-2xl p-6 text-white">
                        <div class="w-16 h-16 bg-white bg-opacity-20 rounded-xl flex items-center justify-center mb-4 mx-auto">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.75 2.524z"/>
                            </svg>
                        </div>
                        <h4 class="font-semibold mb-2">Start Teaching</h4>
                    </div>
                </div>
            </div>
            
            <button class="bg-ogs-navy text-white px-12 py-4 rounded-full font-semibold text-lg hover:bg-ogs-dark-navy transition-colors">
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
                        Provide high-quality and affordable ESL education for individuals looking to improve their language skills.
                    </p>
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-ogs-green rounded-full flex items-center justify-center mr-3">
                            <div class="w-6 h-6 bg-white rounded-full flex items-center justify-center">
                                <div class="w-3 h-3 bg-ogs-green rounded-full"></div>
                            </div>
                        </div>
                        <div>
                            <div class="font-semibold">OGS</div>
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Follow us</h3>
                    <div class="space-y-2 text-sm text-gray-300">
                        <div>📘 Outsourcing Global Solutions - OGS</div>
                        <div>📘 Outsourcing Global Solutions | Davao City</div>
                        <div>📷 ogs.2018</div>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Get in touch</h3>
                    <div class="space-y-2 text-sm text-gray-300">
                        <div>📍 Davao, Davao City, 8000</div>
                        <div>✉️ ogs.recruitment@gmail.com</div>
                        <div>📞 +63 939 634 2922</div>
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