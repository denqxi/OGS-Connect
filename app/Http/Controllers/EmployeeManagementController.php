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
}