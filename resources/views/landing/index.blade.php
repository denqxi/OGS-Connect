<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OGS Connect - Start Your Teaching Journey</title>
    <meta name="description" content="Join OGS Connect - Premier ESL tutoring platform connecting learners with qualified Filipino tutors. Start your teaching journey today!">
    <meta name="keywords" content="ESL tutoring, online teaching, Filipino tutors, English language learning, OGS Connect">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'ogs-green': '#4ADE80',
                        'ogs-dark-green': '#22C55E',
                        'ogs-navy': '#1E3A8A',
                        'ogs-dark-navy': '#0E335D',
                        'ogs-light-blue': '#3B82F6',
                        'ogs-gradient-start': '#4ADE80',
                        'ogs-gradient-end': '#22C55E'
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.8s ease-in-out',
                        'slide-up': 'slideUp 0.8s ease-out',
                        'bounce-slow': 'bounce 2s infinite',
                        'pulse-slow': 'pulse 3s infinite'
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' }
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(30px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' }
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #4ADE80 0%, #22C55E 100%);
        }
        .hero-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        /* Image transparency methods */
        .transparent-bg {
            background: transparent !important;
        }
        
        .remove-white-bg {
            mix-blend-mode: multiply;
            background: transparent;
        }
        
        .remove-black-bg {
            mix-blend-mode: screen;
            background: transparent;
        }
        
        .png-transparent {
            background: transparent;
            /* For PNG images that already have transparency */
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white/95 backdrop-blur-md shadow-lg w-full relative z-50 sticky top-0" style="height: 75px;">
        <div class="flex justify-between items-center h-16 px-4 sm:px-6 lg:px-8">
            <!-- Left Logo -->
            <div class="flex items-center animate-fade-in">
                <img src="{{ asset('images/logo.png') }}" alt="OGS Connect" class="h-12 ms-4 object-contain hover:scale-105 transition-transform duration-300">
                <div class="ml-3">
                    <div class="text-lg font-bold text-ogs-dark-navy">OUTSOURCING</div>
                    <div class="text-xs font-bold text-gray-600">GLOBAL SOLUTIONS</div>
                </div>
            </div>

            <!-- Desktop Buttons -->
            <div class="hidden sm:flex items-center space-x-4 animate-slide-up">
                <a href="{{ route('login') }}">
                    <button
                        class="px-6 text-xs py-2 border-2 border-ogs-navy text-ogs-navy rounded-full hover:bg-ogs-navy hover:text-white transition-all duration-300 transform hover:scale-105 shadow-md hover:shadow-lg">
                        <i class="fas fa-sign-in-alt mr-2"></i>LOG IN
                    </button>
                </a>
                <button onclick="window.location.href='{{ route('application.form') }}'"
                    class="px-6 shadow-lg text-xs py-2 bg-gradient-to-r from-ogs-green to-ogs-dark-green font-normal text-white rounded-full hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <i class="fas fa-rocket mr-2"></i>APPLY NOW
                </button>
            </div>

            <!-- Mobile Button -->
            <div class="sm:hidden flex items-center">
                <button id="mobile-menu-button" class="focus:outline-none p-2 rounded-lg hover:bg-gray-100 transition-colors">
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
    <section class="w-full py-12 relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 hero-pattern opacity-5"></div>
        
        <div class="flex flex-col lg:flex-row items-center gap-8 relative z-10">
            <!-- Left Content with Enhanced Green BG -->
            <div
                class="flex-1 w-full gradient-bg rounded-tr-[100px] sm:rounded-tr-[200px] lg:rounded-tr-[200px] 
               p-6 sm:p-10 lg:p-16 flex flex-col justify-center min-h-[400px] lg:h-[548px] relative overflow-hidden">

                <!-- Floating Elements -->
                <div class="absolute top-10 right-10 w-20 h-20 bg-white/10 rounded-full floating"></div>
                <div class="absolute bottom-20 left-10 w-16 h-16 bg-white/10 rounded-full floating" style="animation-delay: 1s;"></div>

                <div class="relative z-10 animate-slide-up">
                    <div class="inline-block bg-white/20 backdrop-blur-sm rounded-full px-4 py-2 mb-4">
                        <span class="text-ogs-dark-navy font-semibold text-sm">
                            <i class="fas fa-star mr-2"></i>Join 1000+ Successful Tutors
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold mb-6 text-ogs-dark-navy leading-tight">
                        START YOUR<br>
                        <span class="text-white drop-shadow-lg">TEACHING JOURNEY</span><br>
                        WITH OGS!
                    </h1>

                    <p class="text-sm sm:text-base md:text-lg mb-8 font-semibold text-ogs-dark-navy opacity-90 leading-relaxed">
                        Outsourcing Global Solutions is a premier provider of English as a Second Language (ESL) services,
                        specializing in connecting learners with highly-qualified Filipino tutors.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4">
                        <button onclick="window.location.href='{{ route('application.form') }}'"
                            class="bg-ogs-navy text-white px-8 py-4 rounded-full font-semibold hover:bg-ogs-dark-navy transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center justify-center">
                            <i class="fas fa-rocket mr-2"></i>APPLY NOW
                        </button>
                        <button onclick="document.getElementById('hiring-section').scrollIntoView({behavior: 'smooth'})"
                            class="bg-white/20 backdrop-blur-sm text-ogs-dark-navy px-8 py-4 rounded-full font-semibold hover:bg-white/30 transition-all duration-300 transform hover:scale-105 flex items-center justify-center">
                            <i class="fas fa-info-circle mr-2"></i>LEARN MORE
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Card with Enhanced Animation -->
            <div class="flex-1 w-full flex justify-center px-4 sm:px-6 lg:px-8 animate-fade-in">
                <div class="relative">
                    <img src="images/hiring-card.png" alt="We are Hiring - Online ESL Tutor" 
                         class="max-w-full h-auto floating" 
                         style="background: transparent; mix-blend-mode: multiply;">
                    <!-- Decorative elements -->
                    <div class="absolute -top-4 -right-4 w-8 h-8 bg-ogs-green rounded-full animate-pulse-slow"></div>
                    <div class="absolute -bottom-4 -left-4 w-6 h-6 bg-ogs-navy rounded-full animate-bounce-slow"></div>
                </div>
            </div>
        </div>
    </section>


    <!-- Hiring Section -->
    <section id="hiring-section" class="py-20 bg-gradient-to-br from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-24 items-center">

                <!-- Image -->
                <div class="flex-1 w-full mb-10 lg:mb-0 flex me-12 justify-center animate-fade-in">
                    <div class="relative">
                        <img src="images/we-r-h.png" alt="We are Hiring - Online ESL Tutor"
                            class="max-w-full h-auto rounded-3xl shadow-2xl hover:shadow-3xl transition-shadow duration-300">
                        <!-- Decorative overlay -->
                        <div class="absolute inset-0 bg-gradient-to-tr from-ogs-green/20 to-transparent rounded-3xl"></div>
                    </div>
                </div>

                <!-- Content -->
                <div class="gradient-bg rounded-3xl p-6 sm:p-8 lg:p-10 text-white relative overflow-hidden animate-slide-up">
                    <!-- Background Pattern -->
                    <div class="absolute inset-0 hero-pattern opacity-10"></div>
                    
                    <div class="relative z-10">
                        <div class="mb-6 text-center lg:text-left">
                            <div class="inline-block bg-white/20 backdrop-blur-sm rounded-full px-4 py-2 mb-4">
                                <span class="text-ogs-dark-navy font-semibold text-sm">
                                    <i class="fas fa-users mr-2"></i>Join Our Team
                                </span>
                            </div>
                            <div class="text-base sm:text-lg font-semibold text-ogs-dark-navy mb-2">We are looking for</div>
                            <div class="text-2xl sm:text-3xl font-bold text-ogs-dark-navy">ESL Online Tutors</div>
                        </div>

                        <div class="mb-8">
                            <div class="text-base sm:text-lg font-semibold text-ogs-dark-navy mb-6 flex items-center">
                                <i class="fas fa-clipboard-check mr-3 text-ogs-navy"></i>Qualifications:
                            </div>
                            <div class="space-y-4">
                                <div class="flex items-start bg-white/10 backdrop-blur-sm rounded-xl p-4 hover:bg-white/20 transition-colors">
                                    <div class="w-8 h-8 bg-ogs-navy rounded-full flex items-center justify-center mr-4 mt-0.5 flex-shrink-0">
                                        <i class="fas fa-graduation-cap text-white text-sm"></i>
                                    </div>
                                    <span class="text-sm sm:text-base text-ogs-dark-navy font-medium">At least college-level
                                        education in any English language-related course</span>
                                </div>
                                <div class="flex items-start bg-white/10 backdrop-blur-sm rounded-xl p-4 hover:bg-white/20 transition-colors">
                                    <div class="w-8 h-8 bg-ogs-navy rounded-full flex items-center justify-center mr-4 mt-0.5 flex-shrink-0">
                                        <i class="fas fa-comments text-white text-sm"></i>
                                    </div>
                                    <span class="text-sm sm:text-base text-ogs-dark-navy font-medium">Fluent in English with
                                        clear communication skills and a neutral accent</span>
                                </div>
                                <div class="flex items-start bg-white/10 backdrop-blur-sm rounded-xl p-4 hover:bg-white/20 transition-colors">
                                    <div class="w-8 h-8 bg-ogs-navy rounded-full flex items-center justify-center mr-4 mt-0.5 flex-shrink-0">
                                        <i class="fas fa-heart text-white text-sm"></i>
                                    </div>
                                    <span class="text-sm sm:text-base text-ogs-dark-navy font-medium">Responsible,
                                        committed, and friendly</span>
                                </div>
                                <div class="flex items-start bg-white/10 backdrop-blur-sm rounded-xl p-4 hover:bg-white/20 transition-colors">
                                    <div class="w-8 h-8 bg-ogs-navy rounded-full flex items-center justify-center mr-4 mt-0.5 flex-shrink-0">
                                        <i class="fas fa-laptop text-white text-sm"></i>
                                    </div>
                                    <span class="text-sm sm:text-base text-ogs-dark-navy font-medium">Computer
                                        literate</span>
                                </div>
                                <div class="flex items-start bg-white/10 backdrop-blur-sm rounded-xl p-4 hover:bg-white/20 transition-colors">
                                    <div class="w-8 h-8 bg-ogs-navy rounded-full flex items-center justify-center mr-4 mt-0.5 flex-shrink-0">
                                        <i class="fas fa-birthday-cake text-white text-sm"></i>
                                    </div>
                                    <span class="text-sm sm:text-base text-ogs-dark-navy font-medium">Age: 18-45 years
                                        old</span>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('application.form') }}"
                            class="bg-ogs-navy text-white px-6 sm:px-8 py-4 rounded-full font-semibold hover:bg-ogs-dark-navy transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl w-full text-center inline-block flex items-center justify-center">
                            <i class="fas fa-rocket mr-2"></i>APPLY NOW
                        </a>


                </div>
            </div>
        </div>
    </section>


    <!-- OGS Connect Section -->
    <section class="py-16 sm:py-20 bg-gradient-to-br from-white to-gray-50 relative overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute top-20 left-10 w-32 h-32 bg-ogs-green/10 rounded-full blur-xl"></div>
        <div class="absolute bottom-20 right-10 w-40 h-40 bg-ogs-navy/10 rounded-full blur-xl"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <!-- Heading -->
            <div class="animate-fade-in">
                <div class="inline-block bg-ogs-green/10 backdrop-blur-sm rounded-full px-6 py-3 mb-6">
                    <span class="text-ogs-navy font-semibold text-sm">
                        <i class="fas fa-link mr-2"></i>Your Gateway to Success
                    </span>
                </div>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-ogs-navy mb-4 sm:mb-6 leading-tight">
                    OGS Connect – Where Your<br>
                    <span class="text-ogs-green">OGS Journey Begins</span>
                </h2>
            </div>

            <!-- Description -->
            <p class="text-sm sm:text-base text-gray-600 mb-8 sm:mb-12 max-w-3xl mx-auto leading-relaxed animate-slide-up">
                With OGS Connect, applying is simple and fast. From filling out your form to starting your first class,
                everything is done in one smooth process. No hassle, no confusion — just a clear path to becoming part
                of OGS.
            </p>

            <!-- Hiring Process -->
            <div class="mb-10 sm:mb-12">
                <h3 class="text-xl sm:text-2xl font-semibold text-ogs-dark-navy mb-6 sm:mb-8 animate-fade-in">
                    Our Hiring Process:
                </h3>

                <!-- Steps Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-6">
                    <!-- Step 1 -->
                    <div class="bg-white rounded-2xl p-6 text-ogs-dark-navy shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 animate-slide-up border border-ogs-green/20">
                        <div class="w-14 h-14 sm:w-16 sm:h-16 bg-gradient-to-br from-ogs-green to-ogs-dark-green rounded-xl flex items-center justify-center mb-4 mx-auto shadow-md">
                            <img src="images/l1.png" alt="Step Icon" class="w-7 h-7 sm:w-8 sm:h-8">
                        </div>
                        <div class="text-xs font-bold text-ogs-green mb-2">STEP 1</div>
                        <h4 class="font-semibold text-sm sm:text-base">Fill out the google form.</h4>
                    </div>

                    <!-- Step 2 -->
                    <div class="bg-white rounded-2xl p-6 text-ogs-dark-navy shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 animate-slide-up border border-ogs-green/20" style="animation-delay: 0.1s;">
                        <div class="w-14 h-14 sm:w-16 sm:h-16 bg-gradient-to-br from-ogs-green to-ogs-dark-green rounded-xl flex items-center justify-center mb-4 mx-auto shadow-md">
                            <img src="images/l2.png" alt="Phone Call Icon" class="w-7 h-7 sm:w-8 sm:h-8">
                        </div>
                        <div class="text-xs font-bold text-ogs-green mb-2">STEP 2</div>
                        <h4 class="font-semibold text-sm sm:text-base">Await a phone call for your initial interview.</h4>
                    </div>

                    <!-- Step 3 -->
                    <div class="bg-white rounded-2xl p-6 text-ogs-dark-navy shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 animate-slide-up border border-ogs-green/20" style="animation-delay: 0.2s;">
                        <div class="w-14 h-14 sm:w-16 sm:h-16 bg-gradient-to-br from-ogs-green to-ogs-dark-green rounded-xl flex items-center justify-center mb-4 mx-auto shadow-md">
                            <img src="images/l3.png" alt="Training Icon" class="w-7 h-7 sm:w-8 sm:h-8">
                        </div>
                        <div class="text-xs font-bold text-ogs-green mb-2">STEP 3</div>
                        <h4 class="font-semibold text-sm sm:text-base">Upon successful evaluation, proceed with training and demo.</h4>
                    </div>

                    <!-- Step 4 -->
                    <div class="bg-white rounded-2xl p-6 text-ogs-dark-navy shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 animate-slide-up border border-ogs-green/20" style="animation-delay: 0.3s;">
                        <div class="w-14 h-14 sm:w-16 sm:h-16 bg-gradient-to-br from-ogs-green to-ogs-dark-green rounded-xl flex items-center justify-center mb-4 mx-auto shadow-md">
                            <img src="images/l4.png" alt="Onboarding Icon" class="w-7 h-7 sm:w-8 sm:h-8">
                        </div>
                        <div class="text-xs font-bold text-ogs-green mb-2">STEP 4</div>
                        <h4 class="font-semibold text-sm sm:text-base">If qualified, advance to the onboarding stage</h4>
                    </div>

                    <!-- Step 5 -->
                    <div class="bg-white rounded-2xl p-6 text-ogs-dark-navy shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 animate-slide-up border border-ogs-green/20" style="animation-delay: 0.4s;">
                        <div class="w-14 h-14 sm:w-16 sm:h-16 bg-gradient-to-br from-ogs-green to-ogs-dark-green rounded-xl flex items-center justify-center mb-4 mx-auto shadow-md">
                            <img src="images/l5.png" alt="Start Teaching Icon" class="w-7 h-7 sm:w-8 sm:h-8">
                        </div>
                        <div class="text-xs font-bold text-ogs-green mb-2">STEP 5</div>
                        <h4 class="font-semibold text-sm sm:text-base">Start Teaching</h4>
                    </div>
                </div>
            </div>

            <!-- Apply Button -->
            <button onclick="window.location.href='{{ route('application.form') }}'"
                class="bg-gradient-to-r from-ogs-navy to-ogs-dark-navy text-white px-8 sm:px-12 py-4 sm:py-5 rounded-full font-semibold text-base sm:text-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 animate-fade-in flex items-center mx-auto">
                <i class="fas fa-rocket mr-3"></i>START YOUR JOURNEY NOW
            </button>
        </div>
    </section>


    <!-- Footer -->
    <footer class="bg-gradient-to-br from-ogs-dark-navy to-ogs-navy text-white py-16 relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 hero-pattern opacity-5"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid md:grid-cols-3 gap-8">
                <div class="animate-fade-in">
                    <h3 class="text-lg font-semibold mb-6 flex items-center">
                        <i class="fas fa-info-circle mr-3 text-ogs-green"></i>About Us
                    </h3>
                    <p class="text-gray-300 text-sm mb-6 leading-relaxed">
                        Provide high-quality and affordable ESL education for individuals looking to improve their
                        language skills through our innovative OGS Connect platform.
                    </p>
                    <div class="flex items-center bg-white/10 backdrop-blur-sm rounded-xl p-4">
                        <div class="w-12 h-12 mr-4">
                            <img src="{{ asset('images/logo.png') }}" alt="OGS Logo"
                                class="w-full h-full object-cover rounded-full">
                        </div>
                        <div>
                            <div class="font-semibold text-ogs-green">OGS Outsourcing Solutions</div>
                            <div class="text-xs text-gray-400">Connecting the world through education</div>
                        </div>
                    </div>
                </div>
                <div class="animate-slide-up">
                    <h3 class="text-lg font-semibold mb-6 flex items-center">
                        <i class="fas fa-share-alt mr-3 text-ogs-green"></i>Follow us
                    </h3>
                    <div class="space-y-4">
                        <a href="#" class="flex items-center space-x-3 bg-white/10 backdrop-blur-sm rounded-xl p-3 hover:bg-white/20 transition-colors">
                            <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                                <i class="fab fa-facebook-f text-white text-sm"></i>
                            </div>
                            <span class="text-sm">Outsourcing Global Solutions - OGS</span>
                        </a>
                        <a href="#" class="flex items-center space-x-3 bg-white/10 backdrop-blur-sm rounded-xl p-3 hover:bg-white/20 transition-colors">
                            <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                                <i class="fab fa-facebook-f text-white text-sm"></i>
                            </div>
                            <span class="text-sm">Outsourcing Global Solutions | Davao City</span>
                        </a>
                        <a href="#" class="flex items-center space-x-3 bg-white/10 backdrop-blur-sm rounded-xl p-3 hover:bg-white/20 transition-colors">
                            <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center">
                                <i class="fab fa-instagram text-white text-sm"></i>
                            </div>
                            <span class="text-sm">@ogs.2018</span>
                        </a>
                    </div>
                </div>
                <div class="animate-slide-up" style="animation-delay: 0.2s;">
                    <h3 class="text-lg font-semibold mb-6 flex items-center">
                        <i class="fas fa-envelope mr-3 text-ogs-green"></i>Get in touch
                    </h3>
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3 bg-white/10 backdrop-blur-sm rounded-xl p-3">
                            <div class="w-8 h-8 bg-ogs-green rounded-full flex items-center justify-center">
                                <i class="fas fa-map-marker-alt text-white text-sm"></i>
                            </div>
                            <span class="text-sm">Davao, Davao City, 8000</span>
                        </div>
                        <div class="flex items-center space-x-3 bg-white/10 backdrop-blur-sm rounded-xl p-3">
                            <div class="w-8 h-8 bg-ogs-green rounded-full flex items-center justify-center">
                                <i class="fas fa-envelope text-white text-sm"></i>
                            </div>
                            <span class="text-sm">ogs.recruitment@gmail.com</span>
                        </div>
                        <div class="flex items-center space-x-3 bg-white/10 backdrop-blur-sm rounded-xl p-3">
                            <div class="w-8 h-8 bg-ogs-green rounded-full flex items-center justify-center">
                                <i class="fas fa-phone text-white text-sm"></i>
                            </div>
                            <span class="text-sm">+63 939 634 2922</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-t border-white/20 mt-12 pt-8 text-center">
                <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                    <div class="text-sm text-gray-400">
                        <p>© 2024, Outsourcing Global Solutions (OGS). All Rights Reserved.</p>
                    </div>
                    <div class="text-sm text-gray-400">
                        <p>OGS Connect - Developed by Team Lupin, University of Mindanao</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</body>

</html>
