<?php

namespace App\Http\Controllers;

use App\Models\Tutor;
use App\Models\Supervisor;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

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
            case 'archive':
                return $this->getArchivedEmployees($request, $tab);
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

    private function getArchivedEmployees(Request $request, $tab)
    {
        // Get archived tutors and supervisors
        $archivedTutors = Tutor::where('status', 'inactive')
            ->with(['accounts', 'tutorDetails', 'paymentInformation'])
            ->orderBy('updated_at', 'desc');

        $archivedSupervisors = Supervisor::where('status', 'inactive')
            ->orderBy('updated_at', 'desc');

        // Apply search filter to tutors
        if ($request->filled('search')) {
            $search = $request->get('search');
            $archivedTutors->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });

            $archivedSupervisors->where(function($q) use ($search) {
                $q->where('sfname', 'like', "%{$search}%")
                  ->orWhere('slname', 'like', "%{$search}%")
                  ->orWhere('semail', 'like', "%{$search}%")
                  ->orWhere('sconNum', 'like', "%{$search}%");
            });
        }

        // Apply reason filter
        if ($request->filled('reason')) {
            $reason = $request->get('reason');
            // You can add a reason field to the database if needed
            // For now, we'll filter based on status or other criteria
        }

        $tutors = $archivedTutors->get();
        $supervisors = $archivedSupervisors->get();

        // Combine and format the data
        $archivedEmployees = collect();

        // Add tutors to the collection
        foreach ($tutors as $tutor) {
            $archivedEmployees->push([
                'id' => $tutor->tutorID,
                'type' => 'tutor',
                'name' => $tutor->full_name,
                'email' => $tutor->email,
                'phone' => $tutor->phone_number,
                'reason' => 'Inactive', // You can add a reason field to the database
                'status' => 'Archived',
                'archived_at' => $tutor->updated_at->format('M j, Y'),
                'accounts' => $tutor->accounts->pluck('account_name')->toArray(),
            ]);
        }

        // Add supervisors to the collection
        foreach ($supervisors as $supervisor) {
            $archivedEmployees->push([
                'id' => $supervisor->supID,
                'type' => 'supervisor',
                'name' => $supervisor->full_name,
                'email' => $supervisor->semail,
                'phone' => $supervisor->sconNum,
                'reason' => 'Inactive', // You can add a reason field to the database
                'status' => 'Archived',
                'archived_at' => $supervisor->updated_at->format('M j, Y'),
                'accounts' => [$supervisor->assigned_account],
            ]);
        }

        // Sort by archived date
        $archivedEmployees = $archivedEmployees->sortByDesc('archived_at');

        // Paginate the results
        $perPage = 5;
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $items = $archivedEmployees->slice($offset, $perPage)->values();
        
        $paginatedData = new LengthAwarePaginator(
            $items,
            $archivedEmployees->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'pageName' => 'page',
            ]
        );

        $paginatedData->withQueryString();

        return view('emp_management.index', [
            'archivedEmployees' => $paginatedData,
            'tab' => $tab
        ]);
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

    public function restoreEmployee(Request $request)
    {
        $request->validate([
            'id' => 'required|string',
            'type' => 'required|in:tutor,supervisor'
        ]);

        try {
            if ($request->type === 'tutor') {
                $tutor = Tutor::findOrFail($request->id);
                $oldStatus = $tutor->status;
                $tutor->update(['status' => 'active']);
                
                // Get current user info
                $currentUser = $this->getCurrentAuthenticatedUser();
                
                // Log the restore activity
                \App\Models\AuditLog::logEvent(
                    'tutor_restored', // eventType
                    $currentUser['type'], // userType
                    $currentUser['id'], // userId
                    $currentUser['email'], // userEmail
                    $currentUser['name'], // userName
                    'Tutor Restored', // action
                    "Tutor {$tutor->full_name} ({$tutor->tutorID}) restored from archive (status changed from {$oldStatus} to active)", // description
                    ['tutor_id' => $tutor->tutorID, 'old_status' => $oldStatus, 'new_status' => 'active'], // metadata
                    'medium', // severity
                    true // isImportant
                );
                
                return response()->json([
                    'success' => true,
                    'message' => 'Tutor has been restored successfully!'
                ]);
            } else {
                $supervisor = Supervisor::findOrFail($request->id);
                $oldStatus = $supervisor->status;
                $supervisor->update(['status' => 'active']);
                
                // Get current user info
                $currentUser = $this->getCurrentAuthenticatedUser();
                
                // Log the restore activity
                \App\Models\AuditLog::logEvent(
                    'supervisor_restored', // eventType
                    $currentUser['type'], // userType
                    $currentUser['id'], // userId
                    $currentUser['email'], // userEmail
                    $currentUser['name'], // userName
                    'Supervisor Restored', // action
                    "Supervisor {$supervisor->full_name} ({$supervisor->supID}) restored from archive (status changed from {$oldStatus} to active)", // description
                    ['supervisor_id' => $supervisor->supID, 'old_status' => $oldStatus, 'new_status' => 'active'], // metadata
                    'medium', // severity
                    true // isImportant
                );
                
                return response()->json([
                    'success' => true,
                    'message' => 'Supervisor has been restored successfully!'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore employee: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkRestore(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'type' => 'required|in:tutor,supervisor'
        ]);

        try {
            $restoredCount = 0;
            
            if ($request->type === 'tutor') {
                // Get tutor names before update for logging
                $tutors = Tutor::whereIn('tutorID', $request->ids)->get(['tutorID', 'first_name', 'last_name']);
                
                $restoredCount = Tutor::whereIn('tutorID', $request->ids)
                    ->update(['status' => 'active']);
                
                // Get current user info
                $currentUser = $this->getCurrentAuthenticatedUser();
                
                // Log bulk restore activity
                \App\Models\AuditLog::logEvent(
                    'tutors_bulk_restored', // eventType
                    $currentUser['type'], // userType
                    $currentUser['id'], // userId
                    $currentUser['email'], // userEmail
                    $currentUser['name'], // userName
                    'Bulk Tutor Restore', // action
                    "Bulk restored {$restoredCount} tutors from archive. IDs: " . implode(', ', $request->ids), // description
                    ['restored_count' => $restoredCount, 'tutor_ids' => $request->ids], // metadata
                    'medium', // severity
                    true // isImportant
                );
            } else {
                // Get supervisor names before update for logging
                $supervisors = Supervisor::whereIn('supID', $request->ids)->get(['supID', 'sfname', 'slname']);
                
                $restoredCount = Supervisor::whereIn('supID', $request->ids)
                    ->update(['status' => 'active']);
                
                // Get current user info
                $currentUser = $this->getCurrentAuthenticatedUser();
                
                // Log bulk restore activity
                \App\Models\AuditLog::logEvent(
                    'supervisors_bulk_restored', // eventType
                    $currentUser['type'], // userType
                    $currentUser['id'], // userId
                    $currentUser['email'], // userEmail
                    $currentUser['name'], // userName
                    'Bulk Supervisor Restore', // action
                    "Bulk restored {$restoredCount} supervisors from archive. IDs: " . implode(', ', $request->ids), // description
                    ['restored_count' => $restoredCount, 'supervisor_ids' => $request->ids], // metadata
                    'medium', // severity
                    true // isImportant
                );
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully restored {$restoredCount} employees!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore employees: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle supervisor status (active/inactive)
     */
    public function toggleSupervisorStatus(Request $request, Supervisor $supervisor)
    {
        try {
            // Validate the request
            $request->validate([
                'status' => 'required|in:active,inactive'
            ]);

            $newStatus = $request->status;
            $oldStatus = $supervisor->status;
            $supervisor->update(['status' => $newStatus]);

            $supervisorName = $supervisor->full_name ?? 'Supervisor';
            $actionText = $newStatus === 'active' ? 'activated and now has access to the system' : 'deactivated and no longer has system access';
            
            // Get current user info (works with multi-guard system)
            $currentUser = $this->getCurrentAuthenticatedUser();
            
            // Log the activity with appropriate severity
            \App\Models\AuditLog::logEvent(
                $newStatus === 'active' ? 'supervisor_activated' : 'supervisor_deactivated', // eventType
                $currentUser['type'], // userType
                $currentUser['id'], // userId
                $currentUser['email'], // userEmail
                $currentUser['name'], // userName
                $newStatus === 'active' ? 'Supervisor Activated' : 'Supervisor Deactivated', // action
                "Supervisor {$supervisorName} ({$supervisor->supID}) status changed from {$oldStatus} to {$newStatus}", // description
                ['supervisor_id' => $supervisor->supID, 'old_status' => $oldStatus, 'new_status' => $newStatus], // metadata
                'medium', // severity - Status changes are important operational events
                true // isImportant
            );
            
            return response()->json([
                'success' => true,
                'message' => "{$supervisorName} has been {$actionText}",
                'new_status' => $newStatus
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error toggling supervisor status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update supervisor status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current authenticated user information from any guard
     */
    private function getCurrentAuthenticatedUser()
    {
        // Check supervisor guard first
        if (Auth::guard('supervisor')->check()) {
            $user = Auth::guard('supervisor')->user();
            return [
                'type' => 'supervisor',
                'id' => $user->supID,
                'email' => $user->semail,
                'name' => $user->full_name ?? $user->sfname . ' ' . $user->slname
            ];
        }
        
        // Check regular web guard
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            return [
                'type' => 'admin',
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name
            ];
        }
        
        // Check tutor guard (though unlikely in this controller)
        if (Auth::guard('tutor')->check()) {
            $user = Auth::guard('tutor')->user();
            return [
                'type' => 'tutor',
                'id' => $user->tutorID,
                'email' => $user->email,
                'name' => $user->full_name ?? $user->first_name . ' ' . $user->last_name
            ];
        }
        
        // Fallback for system actions
        return [
            'type' => 'system',
            'id' => 'system',
            'email' => 'system@ogsconnect.com',
            'name' => 'System Admin'
        ];
    }
}