@extends('layouts.app')

@section('title', 'Notifications - OGS Connect')

@section('content')
    <!-- Header -->
    @include('layouts.header', ['pageTitle' => 'Notifications'])

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Notifications
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Stay updated with all system notifications
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <button onclick="markAllAsRead()" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-check-double mr-2"></i>
                    Mark All as Read
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-bell text-2xl text-blue-500"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Notifications</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $notifications->total() }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-2xl text-yellow-500"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Unread</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $unreadCount }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-2xl text-green-500"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Read</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $notifications->total() - $unreadCount }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            @if($notifications->count() > 0)
                <ul class="divide-y divide-gray-200">
                    @foreach($notifications as $notification)
                        <li class="notification-item {{ $notification->is_read ? 'bg-gray-50' : 'bg-white' }}" 
                            data-notification-id="{{ $notification->id }}">
                            <div class="px-4 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors duration-150">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="h-10 w-10 flex items-center justify-center rounded-full {{ $notification->getColorClasses() }}">
                                            <i class="{{ $notification->icon }} {{ $notification->getIconColorClasses() }}"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="flex items-center">
                                            <p class="text-sm font-medium text-gray-900 {{ $notification->is_read ? '' : 'font-bold' }}">
                                                {{ $notification->title }}
                                            </p>
                                            @if(!$notification->is_read)
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    New
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-500 mt-1">
                                            {{ $notification->message }}
                                        </p>
                                        <div class="flex items-center mt-2 text-xs text-gray-400">
                                            <i class="fas fa-clock mr-1"></i>
                                            {{ $notification->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    @if(!$notification->is_read)
                                        <button onclick="markAsRead({{ $notification->id }})" 
                                                class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                            Mark as Read
                                        </button>
                                    @else
                                        <span class="text-gray-400 text-sm">
                                            <i class="fas fa-check mr-1"></i>
                                            Read {{ $notification->read_at ? $notification->read_at->diffForHumans() : '' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <!-- Pagination -->
                <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    {{ $notifications->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-bell-slash text-4xl text-gray-400 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No notifications</h3>
                    <p class="text-gray-500">You're all caught up! No notifications to display.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function markAsRead(notificationId) {
    fetch(`/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the notification item
            const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (notificationItem) {
                notificationItem.classList.remove('bg-white');
                notificationItem.classList.add('bg-gray-50');
                
                // Update the button
                const button = notificationItem.querySelector('button');
                if (button) {
                    button.outerHTML = '<span class="text-gray-400 text-sm"><i class="fas fa-check mr-1"></i>Read just now</span>';
                }
                
                // Remove the "New" badge
                const newBadge = notificationItem.querySelector('.bg-blue-100');
                if (newBadge) {
                    newBadge.remove();
                }
                
                // Update font weight
                const title = notificationItem.querySelector('.font-bold');
                if (title) {
                    title.classList.remove('font-bold');
                }
            }
            
            // Update unread count
            updateUnreadCount();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to mark notification as read. Please try again.');
    });
}

function markAllAsRead() {
    if (confirm('Are you sure you want to mark all notifications as read?')) {
        fetch('/notifications/read-all', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload the page to update all notifications
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to mark all notifications as read. Please try again.');
        });
    }
}

function updateUnreadCount() {
    // This would typically make an API call to get the updated count
    // For now, we'll just reload the page
    location.reload();
}
</script>
@endsection
