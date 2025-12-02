@forelse($tutors ?? [] as $tutor)
<tr class="hover:bg-gray-50 tutor-row" data-searchable="{{ strtolower(($tutor->full_name ?? '') . ' ' . ($tutor->email ?? '') . ' ' . ($tutor->phone_number ?? '')) }}">
    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
        {{ $tutor->full_name ?? 'N/A' }}
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $tutor->phone_number ?? 'N/A' }}</td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 underline">
        <a href="mailto:{{ $tutor->email ?? '' }}">{{ $tutor->email ?? 'N/A' }}</a>
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
        @if($tutor->workPreferences)
            <div class="flex flex-col">
                <span class="font-medium text-gray-700">{{ $tutor->formatted_available_time }}</span>
                <span class="text-xs text-green-600 font-medium">(GLS Account)</span>
            </div>
        @else
            <span class="text-red-500 text-sm">No GLS availability</span>
        @endif
    </td>
    <td class="px-6 py-4 whitespace-nowrap">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
            {{ $tutor->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
            {{ ucfirst($tutor->status) }}
        </span>
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
        @if($tutor->status === 'active')
            <button class="px-3 py-1 bg-red-100 text-red-600 rounded-md hover:bg-red-200 transition-colors text-xs font-medium"
                    onclick="toggleTutorStatus('{{ $tutor->tutorID }}', 'inactive')">
                Deactivate
            </button>
        @else
            <button class="px-3 py-1 bg-green-100 text-green-600 rounded-md hover:bg-green-200 transition-colors text-xs font-medium"
                    onclick="toggleTutorStatus('{{ $tutor->tutorID }}', 'active')">
                Activate
            </button>
        @endif
    </td>
</tr>
@empty
<tr id="noResultsRow">
    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
        <i class="fas fa-users text-4xl mb-4 opacity-50"></i>
        <p class="text-lg font-medium">No tutors found</p>
        <p class="text-sm">Try adjusting your search criteria</p>
    </td>
</tr>
@endforelse
