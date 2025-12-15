<!-- Backdrop cover for header -->
<div id="headerBackdrop" class="fixed top-0 left-20 right-0 h-24 bg-gray-50 z-40 transition-all duration-500 ease-in-out"></div>

<div id="mainHeader" class="bg-white shadow-md rounded-xl px-7 py-2.5 flex items-center justify-between fixed top-3 ml-20 mr-0 left-6 right-6 z-50 transition-all duration-500 ease-in-out">
    <h1 class="text-base md:text-lg font-bold text-[#0E335D]">{{ $pageTitle ?? 'OGS Connect' }}</h1>

    <div class="flex items-center space-x-3 md:space-x-4">
        <!-- Theme Toggle -->
        <button class="p-2 text-[#0E335D] hover:text-[#0B284A] rounded-lg transition-colors duration-300">
            <i class="fas fa-sun text-lg md:text-xl"></i>
        </button>

        <!-- Notifications -->
        <div x-data="notificationSystem()" class="relative">
            <button @click="toggleNotifications()"
                class="p-2 text-[#0E335D] hover:text-[#0B284A] rounded-lg relative transition-colors duration-300">
                <i class="fas fa-bell text-lg md:text-xl"></i>
                <span x-show="unreadCount > 0" x-text="unreadCount"
                    class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full text-xs text-white flex items-center justify-center min-w-[12px]"></span>
            </button>

            <!-- Notification Dropdown -->
            <div x-show="isOpen" @click.away="isOpen = false" x-cloak
                class="absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-lg shadow-lg z-50 max-h-96 overflow-hidden">

                <!-- Header -->
                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-[#0E335D]">Notifications</h3>
                        <div class="flex items-center space-x-2">
                            <button @click="markAllAsRead()"
                                class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                Mark all read
                            </button>
                            <button @click="refreshNotifications()" class="text-xs text-gray-500 hover:text-gray-700">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Notifications List -->
                <div class="max-h-64 overflow-y-auto">
                    <template x-if="notifications.length === 0">
                        <div class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-bell-slash text-2xl mb-2"></i>
                            <p class="text-sm">No notifications</p>
                        </div>
                    </template>

                    <template x-for="notification in notifications" :key="notification.id">
                        <div :class="notification.is_read ? 'bg-white' : 'bg-blue-50'"
                            class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start space-x-3">
                                <!-- Icon -->
                                <div :class="getIconColor(notification.color)"
                                    class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center">
                                    <i :class="notification.icon" class="text-sm"></i>
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0 cursor-pointer" @click="markAsRead(notification.id)">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-gray-900" x-text="notification.title"></p>
                                        <span class="text-xs text-gray-500"
                                            x-text="formatTime(notification.created_at)"></span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1" x-text="notification.message"></p>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center space-x-2">
                                    <!-- Unread indicator -->
                                    <div x-show="!notification.is_read"
                                        class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full"></div>
                                    <!-- Delete button -->
                                    <button @click.stop="deleteNotification(notification.id)" 
                                        class="flex-shrink-0 text-gray-400 hover:text-red-500 transition-colors"
                                        title="Delete notification">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Footer -->
                <div class="px-4 py-2 border-t border-gray-200 bg-gray-50">
                    <a href="{{ route('notifications.index') }}"
                        class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                        View all notifications
                    </a>
                </div>
            </div>
        </div>

        <!-- Profile Image with Dropdown -->
        <div x-data="{ open: false }" class="relative w-10 h-10 md:w-10 cursor-pointer">
            @php
                $user = Auth::guard('supervisor')->user() ?? Auth::guard('tutor')->user();
                $defaultPhoto = 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name ?? 'User') . '&color=0E335D&background=BCE6D4&size=128';
                $profilePhoto = $user->profile_photo ?? null;
                $photoUrl = $profilePhoto ? asset('storage/' . $profilePhoto) : $defaultPhoto;
            @endphp
            <img @click="open = !open"
                src="{{ $photoUrl }}"
                alt="Profile" class="w-full h-full object-cover rounded-full">

            <!-- Dropdown -->
            <div x-show="open" x-cloak @click.away="open = false" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute right-0 mt-2 w-40 bg-white border border-gray-200 rounded-lg shadow-lg z-50">
                <a href="{{ route('supervisor.profile') }}"
                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <i class="fas fa-user mr-2"></i> Profile
                </a>

                <div class="border-t border-gray-200"></div>
                <form method="POST" action="{{ route('supervisor.logout') }}" class="w-full">
                    @csrf
                    <button type="submit"
                        class="flex items-center w-full px-4 py-2 text-sm text-red-500 hover:bg-red-50">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Spacer to prevent content from being hidden behind fixed header -->
<div class="h-20"></div>

<!-- Alpine.js for dropdown -->
<script src="//unpkg.com/alpinejs" defer></script>


<!-- Notification System JavaScript -->
<script>
    function notificationSystem() {
        return {
            isOpen: false,
            notifications: [],
            unreadCount: 0,
            isLoading: false,

            init() {
                this.loadNotifications();
                // Refresh notifications every 30 seconds
                setInterval(() => {
                    if (!this.isOpen) {
                        this.loadNotifications();
                    }
                }, 30000);
            },

            async loadNotifications() {
                try {
                    const response = await fetch('/notifications/api', {
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
                    }
                } catch (error) {
                    console.error('Error loading notifications:', error);
                }
            },

            toggleNotifications() {
                this.isOpen = !this.isOpen;
                if (this.isOpen) {
                    this.loadNotifications();
                }
            },

            async markAsRead(notificationId) {
                try {
                    const response = await fetch(`/notifications/${notificationId}/read`, {
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
                            
                            // Handle navigation based on notification type
                            if (notification.type === 'work_detail_submitted') {
                                // Redirect to work details tab in payroll page
                                const url = new URL(window.location);
                                url.pathname = '/payroll';
                                url.searchParams.set('tab', 'payroll');
                                if (notification.data?.tutor_id) {
                                    url.searchParams.set('tutor_name', notification.data.tutor_id);
                                }
                                window.location.href = url.toString();
                            } else if (notification.type === 'assignment_accepted' || notification.type === 'assignment_rejected') {
                                // Redirect supervisor to class scheduling page
                                const url = new URL(window.location);
                                url.pathname = '/schedules';
                                url.searchParams.set('tab', 'class-scheduling');
                                window.location.href = url.toString();
                            } else if (notification.type === 'assignment_request') {
                                // Redirect tutor to assignments page
                                const url = new URL(window.location);
                                url.pathname = '/tutor-portal';
                                url.searchParams.set('tab', 'assignments');
                                window.location.href = url.toString();
                            }
                        }
                    }
                } catch (error) {
                    console.error('Error marking notification as read:', error);
                }
            },

            async markAllAsRead() {
                try {
                    const response = await fetch('/notifications/read-all', {
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
                    const response = await fetch(`/notifications/${notificationId}/delete`, {
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

            getIconColor(color) {
                const colors = {
                    'green': 'bg-green-100 text-green-600',
                    'yellow': 'bg-yellow-100 text-yellow-600',
                    'red': 'bg-red-100 text-red-600',
                    'blue': 'bg-blue-100 text-blue-600'
                };
                return colors[color] || colors['blue'];
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
