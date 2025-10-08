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
        $query = Tutor::with(['accounts' => function($q) {
            $q->where('account_name', 'GLS');
        }])
        ->whereHas('accounts', function($q) {
            $q->where('account_name', 'GLS');
        });

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Apply time slot filter
        if ($request->filled('time_slot')) {
            $timeSlot = $request->get('time_slot');
            $query->whereHas('accounts', function($q) use ($timeSlot) {
                $q->where('account_name', 'GLS')
                  ->whereJsonContains('available_times', $timeSlot);
            });
        }

        // Apply day filter
        if ($request->filled('day')) {
            $day = $request->get('day');
            $query->whereHas('accounts', function($q) use ($day) {
                $q->where('account_name', 'GLS')
                  ->whereJsonContains('available_days', $day);
            });
        }

        $tutors = $query->orderBy('first_name')->orderBy('last_name')->paginate(5)->withQueryString();

        return view('emp_management.index', compact('tutors', 'tab'));
    }

    private function getTutloTutors(Request $request, $tab)
    {
        $query = Tutor::with(['accounts' => function($q) {
            $q->where('account_name', 'Tutlo');
        }, 'tutorDetails', 'paymentInformation'])
        ->whereHas('accounts', function($q) {
            $q->where('account_name', 'Tutlo');
        });

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Apply time slot filter
        if ($request->filled('time_slot')) {
            $timeSlot = $request->get('time_slot');
            $query->whereHas('accounts', function($q) use ($timeSlot) {
                $q->where('account_name', 'Tutlo')
                  ->whereJsonContains('available_times', $timeSlot);
            });
        }

        // Apply day filter
        if ($request->filled('day')) {
            $day = $request->get('day');
            $query->whereHas('accounts', function($q) use ($day) {
                $q->where('account_name', 'Tutlo')
                  ->whereJsonContains('available_days', $day);
            });
        }

        $tutors = $query->orderBy('first_name')->orderBy('last_name')->paginate(5)->withQueryString();

        return view('emp_management.index', compact('tutors', 'tab'));
    }

    private function getBabilalaTutors(Request $request, $tab)
    {
        $query = Tutor::with(['accounts' => function($q) {
            $q->where('account_name', 'Babilala');
        }, 'tutorDetails', 'paymentInformation'])
        ->whereHas('accounts', function($q) {
            $q->where('account_name', 'Babilala');
        });

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Apply time slot filter
        if ($request->filled('time_slot')) {
            $timeSlot = $request->get('time_slot');
            $query->whereHas('accounts', function($q) use ($timeSlot) {
                $q->where('account_name', 'Babilala')
                  ->whereJsonContains('available_times', $timeSlot);
            });
        }

        // Apply day filter
        if ($request->filled('day')) {
            $day = $request->get('day');
            $query->whereHas('accounts', function($q) use ($day) {
                $q->where('account_name', 'Babilala')
                  ->whereJsonContains('available_days', $day);
            });
        }

        $tutors = $query->orderBy('first_name')->orderBy('last_name')->paginate(5)->withQueryString();

        return view('emp_management.index', compact('tutors', 'tab'));
    }

    private function getTalk915Tutors(Request $request, $tab)
    {
        $query = Tutor::with(['accounts' => function($q) {
            $q->where('account_name', 'Talk915');
        }, 'tutorDetails', 'paymentInformation'])
        ->whereHas('accounts', function($q) {
            $q->where('account_name', 'Talk915');
        });

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Apply time slot filter
        if ($request->filled('time_slot')) {
            $timeSlot = $request->get('time_slot');
            $query->whereHas('accounts', function($q) use ($timeSlot) {
                $q->where('account_name', 'Talk915')
                  ->whereJsonContains('available_times', $timeSlot);
            });
        }

        // Apply day filter
        if ($request->filled('day')) {
            $day = $request->get('day');
            $query->whereHas('accounts', function($q) use ($day) {
                $q->where('account_name', 'Talk915')
                  ->whereJsonContains('available_days', $day);
            });
        }

        $tutors = $query->orderBy('first_name')->orderBy('last_name')->paginate(5)->withQueryString();

        return view('emp_management.index', compact('tutors', 'tab'));
    }

    private function getSupervisors(Request $request, $tab)
    {
        $query = Supervisor::query();

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('sfname', 'like', "%{$search}%")
                  ->orWhere('slname', 'like', "%{$search}%")
                  ->orWhere('semail', 'like', "%{$search}%")
                  ->orWhere('sconNum', 'like', "%{$search}%")
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

        $supervisors = $query->orderBy('assigned_account')->orderBy('sfname')->orderBy('slname')->paginate(5)->withQueryString();

        return view('emp_management.index', compact('supervisors', 'tab'));
    }

    public function viewTutor(Tutor $tutor)
    {
        $tutor->load(['accounts', 'tutorDetails', 'paymentInformation']);
        
        // Return JSON data for modal
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $tutor->tutorID,
                'full_name' => $tutor->full_name,
                'first_name' => $tutor->first_name,
                'last_name' => $tutor->last_name,
                'email' => $tutor->email,
                'phone_number' => $tutor->phone_number,
                'date_of_birth' => $tutor->date_of_birth,
                'created_at' => $tutor->created_at ? $tutor->created_at->format('M j, Y') : null,
                'status' => $tutor->status,
                'payment_information' => $tutor->paymentInformation ? [
                    'payment_method' => $tutor->paymentInformation->payment_method,
                    'payment_method_uppercase' => $tutor->paymentInformation->payment_method_uppercase,
                    'bank_name' => $tutor->paymentInformation->bank_name,
                    'account_number' => $tutor->paymentInformation->account_number,
                    'account_name' => $tutor->paymentInformation->account_name,
                    'gcash_number' => $tutor->paymentInformation->gcash_number,
                    'gcash_name' => $tutor->paymentInformation->gcash_name,
                    'paypal_email' => $tutor->paymentInformation->paypal_email,
                ] : null,
                'accounts' => $tutor->accounts->map(function($account) {
                    return [
                        'account_name' => $account->account_name,
                        'status' => $account->status,
                        'gls_id' => $account->gls_id,
                        'formatted_available_time' => $account->formatted_available_time,
                        'formatted_available_days' => $account->formatted_available_days,
                        'ms_teams_id' => $account->ms_teams_id,
                        'available_times' => $account->available_times,
                        'available_days' => $account->available_days,
                    ];
                }),
                'tutor_details' => $tutor->tutorDetails ? [
                    'address' => $tutor->tutorDetails->address,
                    'educational_attainment' => $tutor->tutorDetails->educational_attainment,
                    'esl_teaching_experience' => $tutor->tutorDetails->esl_teaching_experience,
                    'work_setup' => $tutor->tutorDetails->work_setup,
                    'first_day_of_teaching' => $tutor->tutorDetails->first_day_of_teaching,
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
                'email' => $supervisor->semail,
                'phone_number' => $supervisor->sconNum,
                'birth_date' => $supervisor->birth_date,
                'created_at' => $supervisor->created_at ? $supervisor->created_at->format('M j, Y') : null,
                'updated_at' => $supervisor->updated_at ? $supervisor->updated_at->format('M j, Y g:i A') : null,
                'status' => $supervisor->status,
                'assigned_account' => $supervisor->assigned_account,
                'role' => $supervisor->srole,
                'shift' => $supervisor->sshift,
                'ms_teams' => $supervisor->steams,
            ]
        ]);
    }
}