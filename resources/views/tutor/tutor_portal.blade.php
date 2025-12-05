<!DOCTYPE html>
<html lang="en" x-data="darkModeHandler()" :class="{ 'dark': darkMode }" x-cloak>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tutor Portal</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {},
            }
        }
    </script>

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- AlpineJS -->
    <script src="//unpkg.com/alpinejs" defer></script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-100 dark:bg-gray-900 font-sans transition-colors duration-300">

    <!-- Header -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div
            class="bg-white dark:bg-gray-800 shadow-md rounded-xl px-4 py-3 flex items-center justify-between mb-4 md:mb-6 transition-colors duration-300">

            <!-- Left Logo -->
            <div class="flex items-center">
                <img src="{{ asset('images/logo.png') }}" alt="GLS Scheduling" class="h-8 md:h-12 ms-2 object-contain">
                <div class="ml-2 md:ml-3">
                    <div class="text-sm md:text-lg font-bold text-[#0E335D] dark:text-gray-200">OUTSOURCING</div>
                    <div class="text-xs md:text-sm font-bold text-gray-600 dark:text-gray-400">GLOBAL SOLUTIONS</div>
                </div>
            </div>

            <!-- Right Controls -->
            <div class="flex items-center space-x-2 md:space-x-4 relative">

                <!-- Name -->
                <span
                    class="text-sm md:text-base font-semibold text-gray-800 dark:text-gray-200 uppercase hidden sm:inline">{{ $tutor->full_name ?? 'TUTOR' }}</span>

                <!-- Notifications -->
                <div x-data="tutorNotificationSystem()" class="relative">
                    <button @click="toggleNotifications()"
                        class="relative p-1 md:p-2 text-[#0E335D] dark:text-gray-200 rounded-lg transition-transform duration-200 hover:text-[#0B284A] hover:dark:text-white hover:scale-110">
                        <i class="fas fa-bell text-lg md:text-xl"></i>
                        <span x-show="unreadCount > 0" x-text="unreadCount"
                            class="absolute -top-1 -right-1 min-w-[18px] h-[18px] bg-red-500 rounded-full text-xs text-white flex items-center justify-center font-semibold"></span>
                    </button>

                    <!-- Notification Dropdown -->
                    <div x-show="isOpen" @click.away="isOpen = false" x-cloak
                        class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl z-50 max-h-96 overflow-hidden">

                        <!-- Header -->
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-[#0E335D] dark:text-gray-200">Notifications</h3>
                                <div class="flex items-center space-x-2">
                                    <button @click="markAllAsRead()"
                                        class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium">
                                        Mark all read
                                    </button>
                                    <button @click="refreshNotifications()" class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                                        <i class="fas fa-sync-alt" :class="{ 'fa-spin': isLoading }"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Notifications List -->
                        <div class="max-h-80 overflow-y-auto">
                            <template x-if="notifications.length === 0">
                                <div class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-bell-slash text-2xl mb-2"></i>
                                    <p class="text-sm">No notifications</p>
                                </div>
                            </template>

                            <template x-for="notification in notifications" :key="notification.id">
                                <div :class="notification.is_read ? 'bg-white dark:bg-gray-800' : 'bg-blue-50 dark:bg-blue-900/30'"
                                    class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <div class="flex items-start space-x-3">
                                        <!-- Icon -->
                                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                            <i class="fas fa-chalkboard-teacher text-sm text-green-600 dark:text-green-400"></i>
                                        </div>

                                        <!-- Content -->
                                        <div class="flex-1 min-w-0 cursor-pointer" @click="markAsRead(notification.id)">
                                            <div class="flex items-center justify-between">
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="notification.title"></p>
                                                <span class="text-xs text-gray-500 dark:text-gray-400"
                                                    x-text="formatTime(notification.created_at)"></span>
                                            </div>
                                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1" x-text="notification.message"></p>
                                        </div>

                                        <!-- Actions -->
                                        <div class="flex items-center space-x-2">
                                            <!-- Unread indicator -->
                                            <div x-show="!notification.is_read"
                                                class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full"></div>
                                            <!-- Delete button -->
                                            <button @click.stop="deleteNotification(notification.id)" 
                                                class="flex-shrink-0 text-gray-400 dark:text-gray-500 hover:text-red-500 dark:hover:text-red-400 transition-colors"
                                                title="Delete notification">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Show More Button -->
                        <div x-show="hasMore && notifications.length > 0" 
                            class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                            <button @click="loadMoreNotifications()" 
                                :disabled="isLoadingMore"
                                class="w-full text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-show="!isLoadingMore">Show More</span>
                                <span x-show="isLoadingMore" class="flex items-center justify-center">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>Loading...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Burger Menu -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                        class="p-1 md:p-2 text-[#0E335D] dark:text-gray-200 rounded-lg transition-transform duration-200 hover:text-[#0B284A] hover:dark:text-white hover:scale-110">
                        <i class="fas fa-bars text-lg md:text-xl"></i>
                    </button>

                    <!-- Dropdown -->
                    <div x-show="open" x-cloak @click.away="open = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-36 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50">

                        <!-- Dark / Light Mode -->
                        <button @click="toggleDarkMode()"
                            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                            <i :class="darkMode ? 'fas fa-sun mr-2' : 'fas fa-moon mr-2'"></i>
                            <span x-text="darkMode ? 'Light Mode' : 'Dark Mode'"></span>
                        </button>

                        <div class="border-t border-gray-200 dark:border-gray-700"></div>

                        <!-- Logout -->
                        <form method="POST" action="{{ route('tutor.logout') }}" class="w-full">
                            @csrf
                            <button type="submit"
                                class="flex items-center w-full px-4 py-2 text-sm text-red-500 hover:bg-red-500/20 dark:text-red-400 dark:hover:bg-red-500/20 transition-colors duration-200">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </button>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-4 md:mb-6">
        <div
            class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-2 md:px-4 rounded-xl shadow-sm overflow-x-auto transition-colors duration-300">
            @php $activeTab = request('tab', 'profile'); @endphp
            <nav class="flex md:space-x-8 no-scrollbar relative">

                <!-- Profile Info -->
                <a href="{{ route('tutor.portal', ['tab' => 'profile']) }}"
                    class="flex-shrink-0 py-3 md:py-4 px-3 md:px-4 relative
                    {{ $activeTab == 'profile' ? 'after:absolute after:-bottom-0 after:left-0 after:w-full after:h-0.5 after:bg-[#0E335D] dark:after:bg-[#AFC3E0] after:rounded-full after:transition-all after:duration-300' : '' }}">
                    <div
                        class="flex flex-col md:flex-row items-center md:space-x-2 font-medium text-base md:text-sm
                        text-[#0E335D] dark:text-[#AFC3E0] hover:text-[#0E335D] dark:hover:text-[#D1E2F0]
                        transform hover:scale-105 transition-transform duration-200">
                        <i class="fas fa-id-card text-xl md:text-lg"></i>
                        <span class="hidden sm:inline">Profile Info</span>
                    </div>
                </a>

                <!-- Payment Details -->
                <a href="{{ route('tutor.portal', ['tab' => 'payment']) }}"
                    class="flex-shrink-0 py-3 md:py-4 px-3 md:px-4 relative
                    {{ $activeTab == 'payment' ? 'after:absolute after:-bottom-0 after:left-0 after:w-full after:h-0.5 after:bg-[#D35400] dark:after:bg-[#F0B37A] after:rounded-full after:transition-all after:duration-300' : '' }}">
                    <div
                        class="flex flex-col md:flex-row items-center md:space-x-2 font-medium text-base md:text-sm
                           text-[#D35400] dark:text-[#F0B37A] hover:text-[#D35400] dark:hover:text-[#F8C99E]
                           transform hover:scale-105 transition-transform duration-200">
                        <i class="fas fa-credit-card text-xl md:text-lg"></i>
                        <span class="hidden sm:inline">Payment Details</span>
                    </div>
                </a>

                <!-- Account Management -->
                <a href="{{ route('tutor.portal', ['tab' => 'account']) }}"
                    class="flex-shrink-0 py-3 md:py-4 px-3 md:px-4 relative
                    {{ $activeTab == 'account' ? 'after:absolute after:-bottom-0 after:left-0 after:w-full after:h-0.5 after:bg-[#4B5563] dark:after:bg-[#D1D5DB] after:rounded-full after:transition-all after:duration-300' : '' }}">
                    <div
                        class="flex flex-col md:flex-row items-center md:space-x-2 font-medium text-base md:text-sm
                        text-[#4B5563] dark:text-[#D1D5DB] hover:text-[#4B5563] dark:hover:text-[#E5E7EB]
                        transform hover:scale-105 transition-transform duration-200">
                        <i class="fas fa-user-cog text-xl md:text-lg"></i>
                        <span class="hidden sm:inline">Account Management</span>
                    </div>
                </a>


            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-4 md:p-6 mb-6 transition-colors duration-300">
            @if ($activeTab == 'profile')
                @include('tutor.tabs.profile_info')
            @elseif ($activeTab == 'payment')
                @include('tutor.tabs.payment_details')
            @elseif ($activeTab == 'account')
                @include('tutor.tabs.account_management')
            @endif
        </div>
    </div>

    <script>
        function darkModeHandler() {
            return {
                darkMode: localStorage.getItem('darkMode') === 'true', // load from storage
                toggleDarkMode() {
                    this.darkMode = !this.darkMode;
                    localStorage.setItem('darkMode', this.darkMode); // save to storage
                }
            }
        }

        function tutorNotificationSystem() {
            return {
                isOpen: false,
                notifications: [],
                unreadCount: 0,
                isLoading: false,
                isLoadingMore: false,
                hasMore: true,
                currentLimit: 10,

                init() {
                    this.loadNotifications();
                    // Refresh notifications every 30 seconds
                    setInterval(() => {
                        if (!this.isOpen) {
                            this.loadNotifications();
                        }
                    }, 30000);
                },

                async loadNotifications(limit = 10) {
                    try {
                        const response = await fetch(`/tutor/notifications/api?limit=${limit}`, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        if (response.ok) {
                            const data = await response.json();
                            this.notifications = data.notifications;
                            this.unreadCount = data.unread_count;
                            this.hasMore = data.has_more;
                        }
                    } catch (error) {
                        console.error('Error loading notifications:', error);
                    }
                },

                async loadMoreNotifications() {
                    this.isLoadingMore = true;
                    this.currentLimit += 10;
                    await this.loadNotifications(this.currentLimit);
                    this.isLoadingMore = false;
                },

                toggleNotifications() {
                    this.isOpen = !this.isOpen;
                    if (this.isOpen) {
                        this.loadNotifications();
                    }
                },

                async markAsRead(notificationId) {
                    try {
                        const response = await fetch(`/tutor/notifications/${notificationId}/read`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        if (response.ok) {
                            // Update local state
                            const notification = this.notifications.find(n => n.id === notificationId);
                            if (notification) {
                                notification.is_read = true;
                                this.unreadCount = Math.max(0, this.unreadCount - 1);
                            }
                        }
                    } catch (error) {
                        console.error('Error marking notification as read:', error);
                    }
                },

                async markAllAsRead() {
                    try {
                        const response = await fetch('/tutor/notifications/read-all', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        if (response.ok) {
                            // Update local state
                            this.notifications.forEach(notification => {
                                notification.is_read = true;
                            });
                            this.unreadCount = 0;
                        }
                    } catch (error) {
                        console.error('Error marking all notifications as read:', error);
                    }
                },

                async deleteNotification(notificationId) {
                    try {
                        const response = await fetch(`/tutor/notifications/${notificationId}/delete`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        if (response.ok) {
                            // Remove from local state
                            const notification = this.notifications.find(n => n.id === notificationId);
                            if (notification && !notification.is_read) {
                                this.unreadCount = Math.max(0, this.unreadCount - 1);
                            }
                            this.notifications = this.notifications.filter(n => n.id !== notificationId);
                            
                            // Update hasMore flag if we deleted a notification
                            if (this.notifications.length < this.currentLimit) {
                                this.hasMore = false;
                            }
                        }
                    } catch (error) {
                        console.error('Error deleting notification:', error);
                    }
                },

                async refreshNotifications() {
                    this.isLoading = true;
                    await this.loadNotifications();
                    this.isLoading = false;
                },

                formatTime(timestamp) {
                    const date = new Date(timestamp);
                    const now = new Date();
                    const diffInMinutes = Math.floor((now - date) / (1000 * 60));

                    if (diffInMinutes < 1) return 'Just now';
                    if (diffInMinutes < 60) return `${diffInMinutes}m ago`;
                    if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)}h ago`;
                    return date.toLocaleDateString();
                }
            }
        }
    </script>


</body>

</html>
