@if(request('day'))
    {{-- Show the daily schedule view --}}
    @include('schedules.tabs.views.per-day-schedule', ['date' => request('day')])
@else
    <!-- Page Title -->
    <div class="bg-white border-b border-gray-200 px-6 py-4">
        <h2 class="text-xl font-semibold text-gray-800">Class Scheduling</h2>
    </div>

    <!-- Search Filters -->
    <div class="bg-white px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-medium text-gray-700">Search Filters</h3>
            <button class="flex items-center space-x-2 bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors">
                <i class="fas fa-upload"></i>
                <span>Upload Excel</span>
            </button>
        </div>
        <form method="GET" action="{{ route('schedules.index') }}">
            <input type="hidden" name="tab" value="class">
            <div class="flex items-center space-x-4">
                <div class="relative flex-1 max-w-md">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" 
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="search school..." 
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                </div>
                <select name="date" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white">
                    <option value="">All Dates</option>
                    @foreach($availableDates ?? [] as $date)
                        <option value="{{ $date }}" {{ request('date') == $date ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}
                        </option>
                    @endforeach
                </select>
                <select name="day" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white">
                    <option value="">All Days</option>
                    <option value="Monday" {{ request('day') == 'Monday' ? 'selected' : '' }}>Monday</option>
                    <option value="Tuesday" {{ request('day') == 'Tuesday' ? 'selected' : '' }}>Tuesday</option>
                    <option value="Wednesday" {{ request('day') == 'Wednesday' ? 'selected' : '' }}>Wednesday</option>
                    <option value="Thursday" {{ request('day') == 'Thursday' ? 'selected' : '' }}>Thursday</option>
                    <option value="Friday" {{ request('day') == 'Friday' ? 'selected' : '' }}>Friday</option>
                    <option value="Saturday" {{ request('day') == 'Saturday' ? 'selected' : '' }}>Saturday</option>
                    <option value="Sunday" {{ request('day') == 'Sunday' ? 'selected' : '' }}>Sunday</option>
                </select>
                <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-teal-700 transition-colors">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Class Scheduling Table -->
    <div class="bg-white overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Day</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Schools</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Classes</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total Required</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($dailyData ?? [] as $data)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        {{ $data->date ? \Carbon\Carbon::parse($data->date)->format('M d, Y') : '-' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $data->day ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                        {{ $data->schools ?? '-' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-center text-gray-500">
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                            {{ $data->class_count }} {{ $data->class_count == 1 ? 'class' : 'classes' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-center text-gray-500 font-semibold">{{ $data->total_required ?? 0 }}</td>
                    <td class="px-6 py-4 text-sm">
                        <a href="{{ route('schedules.index', ['tab' => 'class', 'day' => $data->date]) }}"
                            class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 rounded-md text-sm hover:bg-blue-200 transition-colors">
                            <i class="fas fa-eye mr-1"></i>
                            View Details
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-calendar-times text-4xl mb-4 opacity-50"></i>
                        <p class="text-lg font-medium">No scheduling data found</p>
                        <p class="text-sm">Upload an Excel file to get started</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if(isset($dailyData) && method_exists($dailyData, 'links'))
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $dailyData->appends(request()->query())->links() }}
    </div>
    @else
    <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
        <div class="text-sm text-gray-500">
            Showing {{ count($dailyData ?? []) }} results
        </div>
    </div>
    @endif
@endif