<?php

namespace App\Http\Controllers;

use App\Models\Tutor;
use App\Models\Supervisor;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class EmployeeManagementController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'gls');
        
        switch ($tab) {
            case 'gls':
                return $this->getGlsTutors($request, $tab);
            case 'tutlo':
                return $this->getTutloTutors($request, $tab);
            case 'babilala':
                return $this->getBabilalaTutors($request, $tab);
            case 'talk915':
                return $this->getTalk915Tutors($request, $tab);
            case 'supervisors':
                return $this->getSupervisors($request, $tab);
            default:
                return $this->getGlsTutors($request, $tab);
        }
    }

    private function getGlsTutors(Request $request, $tab)
    {
        // All tutors now use work_preferences, filter by primary account assignment
        $query = Tutor::with(['workPreferences', 'applicant', 'account'])
            ->whereHas('account', function($q) {
                $q->where('account_name', 'GLS');
            });        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('applicant', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }

        // Apply time slot filter
        if ($request->filled('time_slot')) {
            $timeSlot = $request->get('time_slot');
            $query->whereHas('workPreferences', function($q) use ($timeSlot) {
                $q->whereTime('start_time', '<=', $timeSlot)
                  ->whereTime('end_time', '>=', $timeSlot);
            });
        }

        // Apply day filter
        if ($request->filled('day')) {
            $day = $request->get('day');
            // Map short day names to full names
            $dayMap = [
                'mon' => 'monday', 'tue' => 'tuesday', 'wed' => 'wednesday',
                'thur' => 'thursday', 'fri' => 'friday', 'sat' => 'saturday', 'sun' => 'sunday'
            ];
            $fullDay = $dayMap[$day] ?? $day;
            
            $query->whereHas('workPreferences', function($q) use ($day, $fullDay) {
                $q->where(function($query) use ($day, $fullDay) {
                    $query->whereJsonContains('days_available', $day)
                          ->orWhereJsonContains('days_available', strtolower($day))
                          ->orWhereJsonContains('days_available', ucfirst($day))
                          ->orWhereJsonContains('days_available', $fullDay)
                          ->orWhereJsonContains('days_available', strtolower($fullDay))
                          ->orWhereJsonContains('days_available', ucfirst($fullDay));
                });
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Apply sorting
        $sortField = $request->get('sort', 'tutorID');
        $sortDirection = $request->get('direction', 'asc');
        
        if ($sortField === 'name') {
            $query->join('applicants', 'tutor.applicant_id', '=', 'applicants.applicant_id')
                ->orderBy('applicants.first_name', $sortDirection)
                ->select('tutor.*');
        } elseif ($sortField === 'status') {
            $query->orderBy('tutor.status', $sortDirection);
        } else {
            $query->orderBy('tutor.tutorID', $sortDirection);
        }

        $tutors = $query->paginate(5)->withQueryString();

        return view('emp_management.index', compact('tutors', 'tab'));
    }

    private function getTutloTutors(Request $request, $tab)
    {
        $query = Tutor::with(['workPreferences', 'applicant', 'account', 'applicant.qualification', 'applicant.requirement' /* TODO: , 'paymentInformation' */])
            ->whereHas('account', function($q) {
                $q->where('account_name', 'Tutlo');
            });        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('applicant', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }

        // Apply time slot filter
        if ($request->filled('time_slot')) {
            $timeSlot = $request->get('time_slot');
            $query->whereHas('workPreferences', function($q) use ($timeSlot) {
                $q->whereTime('start_time', '<=', $timeSlot)
                  ->whereTime('end_time', '>=', $timeSlot);
            });
        }

        // Apply day filter
        if ($request->filled('day')) {
            $day = $request->get('day');
            // Map short day names to full names
            $dayMap = [
                'mon' => 'monday', 'tue' => 'tuesday', 'wed' => 'wednesday',
                'thur' => 'thursday', 'fri' => 'friday', 'sat' => 'saturday', 'sun' => 'sunday'
            ];
            $fullDay = $dayMap[$day] ?? $day;
            
            $query->whereHas('workPreferences', function($q) use ($day, $fullDay) {
                $q->where(function($query) use ($day, $fullDay) {
                    $query->whereJsonContains('days_available', $day)
                          ->orWhereJsonContains('days_available', strtolower($day))
                          ->orWhereJsonContains('days_available', ucfirst($day))
                          ->orWhereJsonContains('days_available', $fullDay)
                          ->orWhereJsonContains('days_available', strtolower($fullDay))
                          ->orWhereJsonContains('days_available', ucfirst($fullDay));
                });
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Apply sorting
        $sortField = $request->get('sort', 'tutorID');
        $sortDirection = $request->get('direction', 'asc');
        
        if ($sortField === 'name') {
            $query->join('applicants', 'tutor.applicant_id', '=', 'applicants.applicant_id')
                ->orderBy('applicants.first_name', $sortDirection)
                ->select('tutor.*');
        } elseif ($sortField === 'status') {
            $query->orderBy('tutor.status', $sortDirection);
        } else {
            $query->orderBy('tutor.tutorID', $sortDirection);
        }

        $tutors = $query->paginate(5)->withQueryString();

        return view('emp_management.index', compact('tutors', 'tab'));
    }

    private function getBabilalaTutors(Request $request, $tab)
    {
        $query = Tutor::with(['workPreferences', 'applicant', 'account', 'applicant.qualification', 'applicant.requirement' /* TODO: , 'paymentInformation' */])
            ->whereHas('account', function($q) {
                $q->where('account_name', 'Babilala');
            });        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('applicant', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }

        // Apply time slot filter
        if ($request->filled('time_slot')) {
            $timeSlot = $request->get('time_slot');
            $query->whereHas('workPreferences', function($q) use ($timeSlot) {
                $q->whereTime('start_time', '<=', $timeSlot)
                  ->whereTime('end_time', '>=', $timeSlot);
            });
        }

        // Apply day filter
        if ($request->filled('day')) {
            $day = $request->get('day');
            // Map short day names to full names
            $dayMap = [
                'mon' => 'monday', 'tue' => 'tuesday', 'wed' => 'wednesday',
                'thur' => 'thursday', 'fri' => 'friday', 'sat' => 'saturday', 'sun' => 'sunday'
            ];
            $fullDay = $dayMap[$day] ?? $day;
            
            $query->whereHas('workPreferences', function($q) use ($day, $fullDay) {
                $q->where(function($query) use ($day, $fullDay) {
                    $query->whereJsonContains('days_available', $day)
                          ->orWhereJsonContains('days_available', strtolower($day))
                          ->orWhereJsonContains('days_available', ucfirst($day))
                          ->orWhereJsonContains('days_available', $fullDay)
                          ->orWhereJsonContains('days_available', strtolower($fullDay))
                          ->orWhereJsonContains('days_available', ucfirst($fullDay));
                });
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Apply sorting
        $sortField = $request->get('sort', 'tutorID');
        $sortDirection = $request->get('direction', 'asc');
        
        if ($sortField === 'name') {
            $query->join('applicants', 'tutor.applicant_id', '=', 'applicants.applicant_id')
                ->orderBy('applicants.first_name', $sortDirection)
                ->select('tutor.*');
        } elseif ($sortField === 'status') {
            $query->orderBy('tutor.status', $sortDirection);
        } else {
            $query->orderBy('tutor.tutorID', $sortDirection);
        }

        $tutors = $query->paginate(5)->withQueryString();

        return view('emp_management.index', compact('tutors', 'tab'));
    }

    private function getTalk915Tutors(Request $request, $tab)
    {
        $query = Tutor::with(['workPreferences', 'applicant', 'account', 'applicant.qualification', 'applicant.requirement' /* TODO: , 'paymentInformation' */])
            ->whereHas('account', function($q) {
                $q->where('account_name', 'Talk915');
            });        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('applicant', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }

        // Apply time slot filter
        if ($request->filled('time_slot')) {
            $timeSlot = $request->get('time_slot');
            $query->whereHas('workPreferences', function($q) use ($timeSlot) {
                $q->whereTime('start_time', '<=', $timeSlot)
                  ->whereTime('end_time', '>=', $timeSlot);
            });
        }

        // Apply day filter
        if ($request->filled('day')) {
            $day = $request->get('day');
            $query->whereHas('workPreferences', function($q) use ($day) {
                $q->where(function($query) use ($day) {
                    $query->whereJsonContains('days_available', $day)
                          ->orWhereJsonContains('days_available', ucfirst($day));
                });
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Apply sorting
        $sortField = $request->get('sort', 'tutorID');
        $sortDirection = $request->get('direction', 'asc');
        
        if ($sortField === 'name') {
            $query->join('applicants', 'tutor.applicant_id', '=', 'applicants.applicant_id')
                ->orderBy('applicants.first_name', $sortDirection)
                ->select('tutor.*');
        } elseif ($sortField === 'status') {
            $query->orderBy('tutor.status', $sortDirection);
        } else {
            $query->orderBy('tutor.tutorID', $sortDirection);
        }

        $tutors = $query->paginate(5)->withQueryString();

        return view('emp_management.index', compact('tutors', 'tab'));
    }

    private function getSupervisors(Request $request, $tab)
    {
        $query = Supervisor::query();

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('contact_number', 'like', "%{$search}%")
                  ->orWhere('assigned_account', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Apply account filter
        if ($request->filled('account')) {
            $query->where('assigned_account', $request->get('account'));
        }

        $supervisors = $query->orderBy('assigned_account')->orderBy('first_name')->orderBy('last_name')->paginate(5)->withQueryString();

        return view('emp_management.index', compact('supervisors', 'tab'));
    }

    public function viewTutor(Tutor $tutor)
    {
        $tutor->load(['account', 'workPreferences', 'applicant.qualification', 'applicant.requirement', 'applicant.workPreference' /* TODO: , 'paymentInformation' */]);
        
        // Return JSON data for modal
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $tutor->id,
                'tutorID' => $tutor->tutorID ?? 'N/A',
                'username' => $tutor->username ?? 'N/A',
                'full_name' => $tutor->applicant ? trim(($tutor->applicant->first_name ?? '') . ' ' . ($tutor->applicant->middle_name ?? '') . ' ' . ($tutor->applicant->last_name ?? '')) : 'N/A',
                'first_name' => $tutor->applicant?->first_name ?? 'N/A',
                'middle_name' => $tutor->applicant?->middle_name ?? '',
                'last_name' => $tutor->applicant?->last_name ?? 'N/A',
                'email' => $tutor->email, // Company email
                'personal_email' => $tutor->applicant?->email, // Personal email from application
                'phone_number' => $tutor->applicant?->contact_number ?? 'N/A',
                'date_of_birth' => $tutor->applicant?->birth_date ? \Carbon\Carbon::parse($tutor->applicant->birth_date)->format('M j, Y') : 'N/A',
                'created_at' => $tutor->created_at ? $tutor->created_at->format('M j, Y') : null,
                'status' => $tutor->status,
                // TODO: Uncomment when employee_payment_information table exists
                // 'payment_information' => $tutor->paymentInformation ? [
                //     'payment_method' => $tutor->paymentInformation->payment_method,
                //     'payment_method_uppercase' => $tutor->paymentInformation->payment_method_uppercase,
                //     'bank_name' => $tutor->paymentInformation->bank_name,
                //     'account_number' => $tutor->paymentInformation->account_number,
                //     'account_name' => $tutor->paymentInformation->account_name,
                //     'gcash_number' => $tutor->paymentInformation->gcash_number,
                //     'gcash_name' => $tutor->paymentInformation->gcash_name,
                //     'paypal_email' => $tutor->paymentInformation->paypal_email,
                // ] : null,
                'payment_information' => null, // Temporary
                'availability' => $tutor->workPreferences ? [
                    'account_name' => $tutor->account->account_name ?? 'N/A',
                    'start_time' => $tutor->workPreferences->start_time ? \Carbon\Carbon::parse($tutor->workPreferences->start_time)->format('g:i A') : 'N/A',
                    'end_time' => $tutor->workPreferences->end_time ? \Carbon\Carbon::parse($tutor->workPreferences->end_time)->format('g:i A') : 'N/A',
                    'timezone' => $tutor->workPreferences->timezone ?? 'UTC',
                    'days_available' => $tutor->workPreferences->days_available,
                ] : null,
                'tutor_details' => $tutor->applicant ? [
                    'address' => $tutor->applicant->address,
                    'educational_attainment' => $tutor->applicant->educational_attainment,
                    'esl_teaching_experience' => $tutor->applicant->esl_teaching_experience,
                    'work_setup' => $tutor->applicant->work_setup,
                    'first_day_of_teaching' => $tutor->created_at ? $tutor->created_at->format('Y-m-d') : null,
                ] : null,
            ]
        ]);
    }

    public function viewSupervisor(Supervisor $supervisor)
    {
        // Return JSON data for modal
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $supervisor->supID,
                'full_name' => $supervisor->full_name,
                'email' => $supervisor->email,
                'phone_number' => $supervisor->contact_number,
                'birth_date' => $supervisor->birth_date,
                'created_at' => $supervisor->created_at ? $supervisor->created_at->format('M j, Y') : null,
                'updated_at' => $supervisor->updated_at ? $supervisor->updated_at->format('M j, Y g:i A') : null,
                'status' => $supervisor->status ?? 'active',
                'assigned_account' => $supervisor->assigned_account,
                'role' => 'Supervisor',
                'shift' => $supervisor->shift,
                'ms_teams' => $supervisor->ms_teams,
            ]
        ]);
    }
}