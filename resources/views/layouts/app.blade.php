<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>@yield('title', 'OGS Connect')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        [x-cloak] {
            display: none !important;
        }
        
        /* Font Size Classes - Only for main content, exclude sidebar */
        .font-size-small main,
        .font-size-small main * {
            font-size: 0.875rem !important; /* 14px */
        }
        
        .font-size-small main h1, 
        .font-size-small main .text-xl {
            font-size: 1.125rem !important; /* 18px */
        }
        
        .font-size-small main h2, 
        .font-size-small main .text-lg {
            font-size: 1rem !important; /* 16px */
        }
        
        .font-size-small main .text-sm {
            font-size: 0.75rem !important; /* 12px */
        }
        
        .font-size-small main .text-xs {
            font-size: 0.625rem !important; /* 10px */
        }
        
        .font-size-large main,
        .font-size-large main * {
            font-size: 1.125rem !important; /* 18px */
        }
        
        .font-size-large main h1, 
        .font-size-large main .text-xl {
            font-size: 1.5rem !important; /* 24px */
        }
        
        .font-size-large main h2, 
        .font-size-large main .text-lg {
            font-size: 1.25rem !important; /* 20px */
        }
        
        .font-size-large main .text-sm {
            font-size: 1rem !important; /* 16px */
        }
        
        .font-size-large main .text-xs {
            font-size: 0.875rem !important; /* 14px */
        }
        
        /* Default medium size (no class needed) */
        .font-size-medium main,
        .font-size-medium main * {
            font-size: 1rem !important; /* 16px - default */
        }
    </style>
    <script>
        // Prevent back button access after logout
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                // Page was loaded from cache, redirect to login
                window.location.href = '/login';
            }
        });
        
        // Clear browser history on page load to prevent back button access
        if (window.history && window.history.pushState) {
            window.history.pushState(null, null, window.location.href);
            window.addEventListener('popstate', function(event) {
                window.history.pushState(null, null, window.location.href);
            });
        }
        
        // Font Size Changer Functions
        function setFontSize(size) {
            const body = document.body;
            
            // Remove existing font size classes
            body.classList.remove('font-size-small', 'font-size-medium', 'font-size-large');
            
            // Add new font size class
            if (size !== 'medium') {
                body.classList.add('font-size-' + size);
            }
            
            // Save preference to localStorage
            localStorage.setItem('fontSize', size);
            
            // Close dropdown
            const dropdown = document.querySelector('[x-data]');
            if (dropdown && dropdown._x_dataStack) {
                dropdown._x_dataStack[0].open = false;
            }
        }
        
        // Apply saved font size on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedFontSize = localStorage.getItem('fontSize');
            if (savedFontSize) {
                setFontSize(savedFontSize);
            }
        });
    </script>
</head>
<body class="bg-gray-50 font-sans">
    <div class="flex">
        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-6 page-transition ml-20 transition-all duration-500 ease-in-out" style="transition: margin-left 0.5s ease-in-out;">
            <div class="transition-all duration-300 ease-in-out">
                @yield('content')
            </div>
        </main>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const toggle = document.getElementById('sidebarToggle');

        toggle?.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        });

        overlay?.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });

        // Enhanced smooth transitions for sidebar navigation
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('.sidebar-nav-item');
            const sidebar = document.querySelector('div[class*="group"]');
            const mainContent = document.querySelector('main');
            
            // Handle sidebar hover to adjust main content margin
            if (sidebar && mainContent) {
                // Set initial margin
                mainContent.style.marginLeft = '5rem';
                
                sidebar.addEventListener('mouseenter', function() {
                    mainContent.style.marginLeft = 'calc(18rem - 30px)';
                });
                
                sidebar.addEventListener('mouseleave', function() {
                    mainContent.style.marginLeft = '5rem'; // 80px
                });
                
                // Handle window resize to reset margin
                window.addEventListener('resize', function() {
                    if (!sidebar.matches(':hover')) {
                        mainContent.style.marginLeft = '5rem';
                    }
                });
            }
            
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    // Add loading state
                    this.style.opacity = '0.7';
                    this.style.transform = 'scale(0.95)';
                    
                    // Add ripple effect
                    const ripple = document.createElement('span');
                    ripple.style.position = 'absolute';
                    ripple.style.borderRadius = '50%';
                    ripple.style.background = 'rgba(255, 255, 255, 0.3)';
                    ripple.style.transform = 'scale(0)';
                    ripple.style.animation = 'ripple 0.6s linear';
                    ripple.style.left = '50%';
                    ripple.style.top = '50%';
                    ripple.style.width = '20px';
                    ripple.style.height = '20px';
                    ripple.style.marginLeft = '-10px';
                    ripple.style.marginTop = '-10px';
                    
                    this.appendChild(ripple);
                    
                    // Remove ripple after animation
                    setTimeout(() => {
                        if (ripple.parentNode) {
                            ripple.parentNode.removeChild(ripple);
                        }
                    }, 600);
                });
            });
        });

        // Add CSS for ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
            
            .sidebar-nav-item {
                position: relative;
                overflow: hidden;
            }
            
            .sidebar-nav-item::after {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                width: 0;
                height: 0;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.1);
                transform: translate(-50%, -50%);
                transition: width 0.3s ease, height 0.3s ease;
            }
            
            .sidebar-nav-item:hover::after {
                width: 100px;
                height: 100px;
            }
        `;
        document.head.appendChild(style);
    </script>
    
    @stack('scripts')
</body>
</html>
