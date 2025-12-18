<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Tutor;
use App\Models\Applicant;
use App\Models\TutorWorkDetail;
use App\Models\TutorWorkDetailApproval;
use App\Models\PayrollHistory;
use App\Models\Notification;
use App\Models\Supervisor;
use Illuminate\Support\Facades\Auth;
use App\Mail\PayslipMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Helpers\PayPeriodHelper;

class PayrollController extends Controller
{
    // main payrol dashboard
    public function index(Request $request)
    {
        try {
            // Base query with relationships
            $query = Tutor::with(['applicant', 'workDetails', 'account'])
                ->where('status', 'active');

            // If supervisor is logged in, filter tutors by their assigned account
            if (Auth::guard('supervisor')->check()) {
                $supervisor = Auth::guard('supervisor')->user();
                if ($supervisor->assigned_account) {
                    $query->whereHas('account', function ($q) use ($supervisor) {
                        $q->whereRaw('LOWER(account_name) = ?', [strtolower($supervisor->assigned_account)]);
                    });
                } else {
                    // Supervisor with no assigned account sees no tutors
                    return view('payroll.index')->with(['payrolls' => collect(), 'workDetails' => collect(), 'tutors' => collect()]);
                }
            }

            // Search
            if ($request->filled('search')) {

                $search = $request->input('search');

                $query->where(function ($q) use ($search) {

                    $q->where('username', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone_number', 'like', "%{$search}%")
                      ->orWhereHas('applicant', function ($aq) use ($search) {

                          $aq->where('first_name', 'like', "%{$search}%")
                             ->orWhere('last_name', 'like', "%{$search}%");
                      });

                });
            }

            // Correct ORDER BY related table using subqueries (no joins!)
            $query->orderBy(
                Applicant::select('first_name')
                    ->whereColumn('applicant_id', 'tutors.applicant_id')
            )->orderBy(
                Applicant::select('last_name')
                    ->whereColumn('applicant_id', 'tutors.applicant_id')
            );

            // Pagination
            $tutors = $query->paginate(10)->withQueryString();

            // Add computed payroll values
            $tutors->getCollection()->transform(function ($tutor) {

                // $payment = $tutor->paymentInformation;
                // $amount = $payment ? ($payment->monthly_salary ?? $payment->hourly_rate) : null;
                
                $tutor->salary = null;
                $tutor->amount = null;
                $tutor->pay_date = null;

                return $tutor;
            });

            // Calculate total pending and approved amounts across all tutors
            $totalPendingAmount = 0;
            $totalApprovedAmount = 0;
            
            // Build the base tutor query (same filters as above)
            $baseQuery = Tutor::where('status', 'active');
            if (Auth::guard('supervisor')->check()) {
                $supervisor = Auth::guard('supervisor')->user();
                if ($supervisor->assigned_account) {
                    $baseQuery->whereHas('account', function ($q) use ($supervisor) {
                        $q->whereRaw('LOWER(account_name) = ?', [strtolower($supervisor->assigned_account)]);
                    });
                }
            }
            
            // Get all tutors' formatted IDs (not paginated) to calculate totals
            $allTutorIds = $baseQuery->pluck('tutorID');
            
            // Sum pending work details (try rate_per_class first, fallback to computed_amount)
            $pendingWorkDetails = TutorWorkDetail::whereIn('tutor_id', $allTutorIds)
                ->where('status', 'pending')
                ->get();
            
            foreach ($pendingWorkDetails as $detail) {
                $amount = $detail->rate_per_class ?? $detail->computed_amount ?? 0;
                $totalPendingAmount += (float)$amount;
            }
            
            // Sum approved work details (try rate_per_class first, fallback to computed_amount)
            $approvedWorkDetails = TutorWorkDetail::whereIn('tutor_id', $allTutorIds)
                ->where('status', 'approved')
                ->get();
            
            foreach ($approvedWorkDetails as $detail) {
                $amount = $detail->rate_per_class ?? $detail->computed_amount ?? 0;
                $totalApprovedAmount += (float)$amount;
            }

            $viewData = [
                'payrolls' => $tutors,
                'workDetails' => $tutors,
                'tutors' => $tutors,
                'totalPendingAmount' => $totalPendingAmount,
                'totalApprovedAmount' => $totalApprovedAmount,
            ];

            // If history tab requested, load approvals paginator with optional filters
            if ($request->query('tab') === 'history') {
                $approvalsQuery = TutorWorkDetailApproval::with(['workDetail.tutor.applicant', 'supervisor']);

                // If supervisor is logged in, filter by their assigned account
                if (Auth::guard('supervisor')->check()) {
                    $supervisor = Auth::guard('supervisor')->user();
                    if ($supervisor->assigned_account) {
                        $approvalsQuery->whereHas('workDetail.tutor.account', function ($q) use ($supervisor) {
                            $q->whereRaw('LOWER(account_name) = ?', [strtolower($supervisor->assigned_account)]);
                        });
                    } else {
                        // Supervisor with no assigned account sees no approvals
                        $viewData['workApprovals'] = collect();
                        return view('payroll.index', $viewData);
                    }
                }

                $year = $request->query('year');
                $month = $request->query('month');
                $tutorName = $request->query('tutor_name');

                // Filter by approved_at date (year/month)
                if ($year && $month) {
                    $start = Carbon::create((int) $year, (int) $month, 1)->startOfMonth();
                    $end = $start->copy()->endOfMonth();
                    $approvalsQuery->whereBetween('approved_at', [$start, $end]);
                } elseif ($year) {
                    $start = Carbon::create((int) $year, 1, 1)->startOfYear();
                    $end = $start->copy()->endOfYear();
                    $approvalsQuery->whereBetween('approved_at', [$start, $end]);
                }

                // Filter by tutor name (first or last)
                if ($tutorName) {
                    $approvalsQuery->whereHas('workDetail.tutor.applicant', function ($q) use ($tutorName) {
                        $q->where('first_name', 'like', "%{$tutorName}%")
                          ->orWhere('last_name', 'like', "%{$tutorName}%");
                    });
                }

                // Handle sorting
                $sortColumn = $request->query('sort', 'approved_at');
                $sortDirection = $request->query('direction', 'desc');
                
                // Validate sort direction
                if (!in_array($sortDirection, ['asc', 'desc'])) {
                    $sortDirection = 'desc';
                }

                // Apply sorting based on column
                switch ($sortColumn) {
                    case 'tutor_name':
                        $approvalsQuery->join('tutor_work_details', 'tutor_work_detail_approvals.work_detail_id', '=', 'tutor_work_details.work_detail_id')
                            ->join('tutors', 'tutor_work_details.tutor_id', '=', 'tutors.tutorID')
                            ->join('applicants', 'tutors.applicantID', '=', 'applicants.applicantID')
                            ->orderBy('applicants.first_name', $sortDirection)
                            ->orderBy('applicants.last_name', $sortDirection)
                            ->select('tutor_work_detail_approvals.*');
                        break;
                    case 'approved_at':
                    case 'new_status':
                        $approvalsQuery->orderBy($sortColumn, $sortDirection);
                        break;
                    default:
                        $approvalsQuery->orderBy('approved_at', 'desc');
                }

                $approvals = $approvalsQuery
                    ->paginate(10)
                    ->withQueryString();

                $viewData['workApprovals'] = $approvals;
            }

            // If payroll-history tab requested, load payroll submissions with optional filters
            if ($request->query('tab') === 'payroll-history') {
                $historyQuery = PayrollHistory::with(['tutor.applicant']);

                $year = $request->query('year');
                $month = $request->query('month');
                $tutorName = $request->query('tutor_name');

                // Filter by pay period (year/month)
                if ($year && $month) {
                    $period = sprintf('%04d-%02d', (int) $year, (int) $month);
                    $historyQuery->where('pay_period', $period);
                } elseif ($year) {
                    $historyQuery->where('pay_period', 'like', sprintf('%04d-%%', (int) $year));
                }

                // Filter by tutor name (first or last)
                if ($tutorName) {
                    $historyQuery->whereHas('tutor.applicant', function ($q) use ($tutorName) {
                        $q->where('first_name', 'like', "%{$tutorName}%")
                          ->orWhere('last_name', 'like', "%{$tutorName}%");
                    });
                }

                // Handle sorting
                $sortColumn = $request->query('sort', 'submitted_at');
                $sortDirection = $request->query('direction', 'desc');
                
                // Validate sort direction
                if (!in_array($sortDirection, ['asc', 'desc'])) {
                    $sortDirection = 'desc';
                }

                // Apply sorting based on column
                switch ($sortColumn) {
                    case 'tutor_name':
                        $historyQuery->join('tutors', 'payroll_history.tutor_id', '=', 'tutors.tutor_id')
                            ->join('applicants', 'tutors.applicantID', '=', 'applicants.applicantID')
                            ->orderBy('applicants.first_name', $sortDirection)
                            ->orderBy('applicants.last_name', $sortDirection)
                            ->select('payroll_history.*');
                        break;
                    case 'submitted_at':
                    case 'pay_period':
                    case 'total_amount':
                    case 'submission_type':
                    case 'status':
                        $historyQuery->orderBy($sortColumn, $sortDirection);
                        break;
                    default:
                        $historyQuery->orderBy('submitted_at', 'desc');
                }

                $payrollHistory = $historyQuery
                    ->paginate(10)
                    ->withQueryString();

                // Attach total earnings, preferring stored value and falling back to computation
                $controller = $this;
                $payrollHistory->getCollection()->transform(function ($record) use ($controller) {
                    try {
                        $tutorFormattedId = $record->tutor?->tutorID;

                        // If already stored (including 0), keep it; otherwise compute on the fly
                        if ($record->total_amount === null) {
                            $record->total_amount = $controller->calculateTotalAmountForPeriod($tutorFormattedId, $record->pay_period);
                        }
                    } catch (\Exception $e) {
                        Log::warning('PayrollHistory total computation failed: ' . $e->getMessage(), [
                            'payroll_history_id' => $record->payroll_history_id ?? null,
                        ]);
                        $record->total_amount = $record->total_amount ?? 0;
                    }

                    return $record;
                });

                $viewData['payrollHistory'] = $payrollHistory;
            }

            return view('payroll.index', $viewData);

        } catch (\Exception $e) {
            Log::error('PayrollController error: ' . $e->getMessage());
            return view('payroll.index')->with('error', 'Something went wrong.');
        }
    }

    // display ang tutors work details para sa supervisor
public function workDetails(Request $request)
    {
        try {
            $search = $request->input('search');

            $workQuery = TutorWorkDetail::with(['tutor.applicant', 'assignment', 'schedule'])
                ->where('status', '=', 'pending');        if (Auth::guard('supervisor')->check()) {
            $supervisor = Auth::guard('supervisor')->user();
            if ($supervisor->assigned_account) {
                $workQuery->whereHas('tutor.account', function ($q) use ($supervisor) {
                    $q->whereRaw('LOWER(account_name) = ?', [strtolower($supervisor->assigned_account)]);
                });
            } else {
                // Supervisor with no assigned account sees no work details
                return view('payroll.partials.tutor_payroll_details', ['workDetails' => collect()]);
            }
        }

        if (!empty($search)) {
            $workQuery->whereHas('tutor.applicant', function ($aq) use ($search) {
                $aq->where('first_name', 'like', "%{$search}%")
                   ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        // Handle sorting
        $sortColumn = $request->query('sort', 'created_at');
        $sortDirection = $request->query('direction', 'desc');
        
        // Validate sort direction
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        // Apply sorting based on column
        switch ($sortColumn) {
            case 'tutor_name':
                $workQuery->join('tutors', 'tutor_work_details.tutor_id', '=', 'tutors.tutorID')
                    ->join('applicants', 'tutors.applicantID', '=', 'applicants.applicantID')
                    ->orderBy('applicants.first_name', $sortDirection)
                    ->orderBy('applicants.last_name', $sortDirection)
                    ->select('tutor_work_details.*');
                break;
            case 'day':
            case 'start_time':
                $workQuery->orderBy($sortColumn, $sortDirection);
                break;
            case 'rate':
                // Sort by rate_per_class, fallback to rate_per_hour
                $workQuery->orderByRaw("COALESCE(rate_per_class, rate_per_hour) $sortDirection");
                break;
            default:
                $workQuery->orderBy('created_at', 'desc');
        }

        $workDetails = $workQuery
            ->paginate(10)
            ->withQueryString();

        return view('payroll.partials.tutor_payroll_details', compact('workDetails'));

    } catch (\Exception $e) {
        Log::error('PayrollController@workDetails error: ' . $e->getMessage());
        return response('Error loading work details', 500);
    }
}

// buhat ug bagong work detail gikan kay tutor
    public function storeWorkDetail(Request $request)
{
    $request->validate([
        'tutor_id'      => 'required|exists:tutors,id',
        'work_type'     => 'nullable|in:hourly,per_class', 
        'day'           => 'required|date',
        'start_time'    => 'required|date_format:H:i',
        'end_time'      => 'required|date_format:H:i|after:start_time',
        'screenshot'    => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
    ]);

    $tutor = Tutor::with('account')->findOrFail($request->tutor_id);
    $accountId = (int) ($tutor->account_id ?? 0);
    $isTutlo = $accountId === 2; // account_id 2 = Tutlo

    $start = Carbon::parse($request->start_time);
    $end   = Carbon::parse($request->end_time);
    $duration = $end->diffInMinutes($start);

    $path = null;
    if ($request->hasFile('screenshot')) {
        $path = $request->file('screenshot')->store('screenshots', 'public');
    }

    //Tutlo (account_id 2) = hourly @ 120; others = per class @ 50
    $actualWorkType = $isTutlo ? 'hourly' : 'per class';
    $rateHourly = $isTutlo ? 120 : 0;
    $rateClass  = $isTutlo ? 0 : 50;

    $record = TutorWorkDetail::create([
        'tutor_id'        => $request->tutor_id,
        'work_type'       => $actualWorkType,
        'day'             => $request->day,
        'start_time'      => $request->start_time,
        'end_time'        => $request->end_time,
        'duration_minutes'=> $duration,
        'rate_per_hour'   => $rateHourly,
        'rate_per_class'  => $rateClass,
        'screenshot'      => $path,
    ]);

    return back()->with('success', 'Work details added successfully.');
}

// sa tutor ni nga side, iapprove or not ang work detail
    public function approveWorkDetail(Request $request, $id)
    {
        try {
            $detail = TutorWorkDetail::find($id);

            if (! $detail) {
                return response()->json(['message' => 'Record not found'], 404);
            }

            if (! (Auth::guard('supervisor')->check() || Auth::check())) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            // Check if supervisor is authorized to approve this tutor's work
            if (Auth::guard('supervisor')->check()) {
                $supervisor = Auth::guard('supervisor')->user();
                $tutor = $detail->tutor;
                
                // Verify tutor belongs to supervisor's assigned account (case-insensitive)
                if ($supervisor->assigned_account && $tutor->account) {
                    if (strtolower($tutor->account->account_name) !== strtolower($supervisor->assigned_account)) {
                        return response()->json(['message' => 'You are not authorized to approve work for this tutor'], 403);
                    }
                }
            }

            // Idempotency: if already approved and no change requested
            $requestedStatus = $request->input('status', 'approved');
            if ($detail->status === $requestedStatus) {
                return response()->json(['message' => 'No change: already ' . $requestedStatus]);
            }

            // Transactional update + audit log
            $newStatus = $requestedStatus;
            $oldStatus = $detail->status;

            $supervisor = Auth::guard('supervisor')->user() ?: Auth::user();
            $supervisorId = $supervisor->supervisor_id ?? $supervisor->id ?? null;

            DB::transaction(function () use ($detail, $newStatus, $oldStatus, $request, $supervisorId) {
                $detail->status = $newStatus;
                $detail->save();

                TutorWorkDetailApproval::create([
                    'work_detail_id' => $detail->id,
                    'supervisor_id' => $supervisorId,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'approved_at' => now(),
                    'note' => $request->input('note'),
                ]);
            });

            // After-commit notification
            DB::afterCommit(function () use ($detail, $supervisorId) {
                try {
                    $tutor = $detail->tutor;
                    $schedule = $detail->schedule;
                    if ($tutor && $schedule) {
                        Notification::create([
                            'user_id' => $tutor->tutor_id,
                            'user_type' => 'tutor',
                            'type' => 'work_detail_approved',
                            'title' => 'Work Detail Approved',
                            'message' => "Your work details for {$schedule->school} - {$schedule->class} on {$schedule->date} have been approved. Payment will be processed.",
                            'icon' => 'fas fa-check-circle',
                            'color' => 'green',
                            'is_read' => false,
                            'data' => [
                                'work_detail_id' => $detail->id,
                                'schedule_id' => $detail->schedule_daily_data_id,
                                'amount' => $detail->rate_per_class ?? $detail->rate_per_hour,
                                'supervisor_id' => $supervisorId
                            ]
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to create approval notification after commit: ' . $e->getMessage());
                }
            });

            return response()->json(['message' => 'Work detail ' . $newStatus . ' successfully']);
        } catch (\Exception $e) {
            Log::error('PayrollController@approveWorkDetail exception: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['message' => 'Server error while processing request'], 500);
        }
    }

// sa tutor ni nga side, ireject ang work detail
    public function rejectWorkDetail(Request $request, $id)
    {
        try {
            $request->validate([
                'note' => 'required|string|max:2000',
            ]);

            $detail = TutorWorkDetail::find($id);
            if (! $detail) {
                return response()->json(['message' => 'Record not found'], 404);
            }

            if (! (Auth::guard('supervisor')->check() || Auth::check())) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            // Check if supervisor is authorized to reject this tutor's work
            if (Auth::guard('supervisor')->check()) {
                $supervisor = Auth::guard('supervisor')->user();
                $tutor = $detail->tutor;
                
                // Verify tutor belongs to supervisor's assigned account (case-insensitive)
                if ($supervisor->assigned_account && $tutor->account) {
                    if (strtolower($tutor->account->account_name) !== strtolower($supervisor->assigned_account)) {
                        return response()->json(['message' => 'You are not authorized to reject work for this tutor'], 403);
                    }
                }
            }

            // Idempotency: if already rejected
            if ($detail->status === 'rejected') {
                return response()->json(['message' => 'No change: already rejected']);
            }

            $oldStatus = $detail->status;
            $supervisor = Auth::guard('supervisor')->user() ?: Auth::user();
            $supervisorId = $supervisor->supervisor_id ?? $supervisor->id ?? null;

            DB::transaction(function () use ($detail, $oldStatus, $request, $supervisorId) {
                $detail->status = 'rejected';
                $detail->save();

                TutorWorkDetailApproval::create([
                    'work_detail_id' => $detail->id,
                    'supervisor_id' => $supervisorId,
                    'old_status' => $oldStatus,
                    'new_status' => 'rejected',
                    'approved_at' => now(),
                    'note' => $request->input('note'),
                ]);
            });

            DB::afterCommit(function () use ($detail, $supervisor, $supervisorId, $request) {
                try {
                    $tutor = $detail->tutor;
                    $schedule = $detail->schedule;
                    $note = $request->input('note', 'No reason provided');
                    $supervisorName = $supervisor->name ?? $supervisor->username ?? 'Supervisor';

                    if ($tutor && $schedule) {
                        Notification::create([
                            'user_id' => $tutor->tutor_id,
                            'user_type' => 'tutor',
                            'type' => 'work_detail_rejected',
                            'title' => 'Work Detail Rejected',
                            'message' => "Your work details for {$schedule->school} - {$schedule->class} on {$schedule->date} have been rejected by {$supervisorName}. Reason: {$note}",
                            'icon' => 'fas fa-times-circle',
                            'color' => 'red',
                            'is_read' => false,
                            'data' => [
                                'work_detail_id' => $detail->id,
                                'schedule_id' => $detail->schedule_daily_data_id,
                                'rejection_reason' => $note,
                                'supervisor_id' => $supervisorId
                            ]
                        ]);
                    }

                    Log::info('Rejection notification created for tutor', [
                        'work_detail_id' => $detail->id,
                        'tutor_id' => $tutor->tutor_id ?? null
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to create rejection notification after commit: ' . $e->getMessage());
                }
            });

            return response()->json(['message' => 'Work detail rejected successfully']);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json(['message' => 'Validation failed', 'errors' => $ve->errors()], 422);
        } catch (\Exception $e) {
            Log::error('PayrollController@rejectWorkDetail exception: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['message' => 'Server error while processing request'], 500);
        }
    }

//payslip para sa tutor
    public function tutorSummary(Request $request, $tutor)
    {
        try {
            $tutorModel = Tutor::where('tutorID', $tutor)->with(['applicant', 'account'])->first();
            if (! $tutorModel) {
                return response('Tutor not found', 404);
            }

            // Check if supervisor is authorized to view this tutor's summary
            if (Auth::guard('supervisor')->check()) {
                $supervisor = Auth::guard('supervisor')->user();
                if ($supervisor->assigned_account && $tutorModel->account) {
                    if (strtolower($tutorModel->account->account_name) !== strtolower($supervisor->assigned_account)) {
                        return response('Unauthorized', 403);
                    }
                }
            }

            // Use correct pay period: 28→12 or 13→27
            $period = PayPeriodHelper::getCurrentPeriod();
            $periodStart = $period['start']->format('Y-m-d');
            $periodEnd = $period['end']->format('Y-m-d');
            $releaseDate = $period['release']->format('Y-m-d');

            $approvedQuery = TutorWorkDetail::where('tutor_id', $tutor)
                ->where('status', 'approved')
                ->whereDate('created_at', '>=', $periodStart)
                ->whereDate('created_at', '<=', $periodEnd)
                ->orderBy('created_at', 'desc');

            $approved = $approvedQuery->get();

            $totalItems = $approved->count();
            $totalMinutes = $approved->sum('duration_minutes');

            $totalEarnings = $approved->reduce(function ($carry, $wd) {
                if (($wd->work_type ?? '') === 'hourly') {
                    $hours = ($wd->duration_minutes ?? 0) / 60;
                    $carry += ($wd->rate_per_hour ?? 0) * $hours;
                } else {
                    $carry += ($wd->rate_per_class ?? 0);
                }
                return $carry;
            }, 0);

            $deductions = 0; // adjust if needed

            $summary = [
                'tutor' => $tutorModel,
                'total_items' => $totalItems,
                'total_minutes' => $totalMinutes,
                'total_hours' => round($totalMinutes / 60, 2),
                'total_earnings' => round($totalEarnings, 2),
                'deductions' => $deductions,
                'details' => $approved,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'release_date' => $releaseDate,
            ];

            return view('payroll.partials.tutor_summary', $summary);

        } catch (\Exception $e) {
            Log::error('PayrollController@tutorSummary error: ' . $e->getMessage());
            return response('Error generating summary', 500);
        }
    }

    // Finalize payroll for a specific tutor and period
    public function finalizePayroll(Request $request)
    {
        try {
            Log::info('finalizePayroll called', ['request' => $request->all()]);
            
            $tutorID = $request->input('tutor_id');
            $periodStart = $request->input('period_start');
            $periodEnd = $request->input('period_end');

            Log::info('finalizePayroll params', ['tutorID' => $tutorID, 'periodStart' => $periodStart, 'periodEnd' => $periodEnd]);
            
            $tutor = Tutor::where('tutorID', $tutorID)->first();
            if (!$tutor) {
                Log::warning('finalizePayroll: Tutor not found', ['tutorID' => $tutorID]);
                return response()->json(['success' => false, 'message' => 'Tutor not found'], 404);
            }
            
            Log::info('finalizePayroll: Tutor found', ['tutor_id' => $tutor->tutor_id, 'tutorID' => $tutor->tutorID]);

            // Check supervisor authorization
            if (Auth::guard('supervisor')->check()) {
                $supervisor = Auth::guard('supervisor')->user();
                Log::info('finalizePayroll: Supervisor check', ['supervisor_id' => $supervisor->id, 'supervisor_assigned_account' => $supervisor->assigned_account, 'tutor_account_id' => $tutor->account_id, 'tutor_account' => $tutor->account ? $tutor->account->account_name : 'null']);
                
                if ($supervisor->assigned_account && $tutor->account) {
                    if (strtolower($tutor->account->account_name) !== strtolower($supervisor->assigned_account)) {
                        Log::warning('finalizePayroll: Authorization failed', ['supervisor_account' => $supervisor->assigned_account, 'tutor_account' => $tutor->account->account_name]);
                        return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
                    }
                }
            }
            Log::info('finalizePayroll: Authorization passed or not applicable');

            // Determine pay period using helper
            $startDate = Carbon::parse($periodStart);
            $endDate = Carbon::parse($periodEnd);
            $payPeriod = PayPeriodHelper::formatPeriodLabel($startDate, $endDate);

            // Transaction to avoid partial state and re-check idempotency
            $result = DB::transaction(function () use ($tutor, $tutorID, $periodStart, $periodEnd, $payPeriod) {
                $existing = PayrollHistory::where('tutor_id', $tutor->tutor_id)
                    ->where('pay_period', $payPeriod)
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    return ['already' => true, 'payPeriod' => $payPeriod, 'amount' => $existing->total_amount];
                }

                // Get approved work details for this period
                $workDetails = TutorWorkDetail::where('tutor_id', $tutorID)
                    ->where('status', 'approved')
                    ->whereBetween('created_at', [$periodStart . ' 00:00:00', $periodEnd . ' 23:59:59'])
                    ->get();

                // Calculate total amount
                $totalAmount = $workDetails->reduce(function ($carry, $wd) {
                    if (($wd->work_type ?? '') === 'hourly') {
                        $hours = ($wd->duration_minutes ?? 0) / 60;
                        $carry += ($wd->rate_per_hour ?? 0) * $hours;
                    } else {
                        $carry += ($wd->rate_per_class ?? 0);
                    }
                    return $carry;
                }, 0);

                $totalAmount = round($totalAmount, 2);

                // Create PayrollHistory record
                $history = PayrollHistory::create([
                    'tutor_id' => $tutor->tutor_id,
                    'pay_period' => $payPeriod,
                    'total_amount' => $totalAmount,
                    'submission_type' => 'email',
                    'status' => 'finalized',
                    'recipient_email' => $tutor->email,
                    'notes' => "Finalized by supervisor. Work details count: {$workDetails->count()}",
                    'submitted_at' => now(),
                ]);

                // Create PayrollFinalization record
                \App\Models\PayrollFinalization::create([
                    'tutor_id' => $tutor->tutor_id,
                    'pay_period' => $payPeriod,
                    'total_amount' => $totalAmount,
                    'work_details_count' => $workDetails->count(),
                    'status' => 'locked',
                    'finalized_at' => now(),
                    'notes' => "Manually finalized by supervisor " . (Auth::guard('supervisor')->user()->username ?? 'system'),
                ]);

                return ['already' => false, 'payPeriod' => $payPeriod, 'amount' => $totalAmount, 'tutor_id' => $tutor->tutor_id];
            });

            // Create notification AFTER transaction completes successfully
            if (!($result['already'] ?? false)) {
                try {
                    Log::info('About to create payroll finalized notification', ['result' => $result]);
                    
                    $notification = Notification::create([
                        'user_id' => $result['tutor_id'],
                        'user_type' => 'tutor',
                        'type' => 'payroll_finalized',
                        'title' => 'Payroll Finalized & Locked',
                        'message' => "Your payroll for {$result['payPeriod']} has been finalized and locked. Amount: ₱" . number_format($result['amount'], 2),
                        'icon' => 'fas fa-lock',
                        'color' => 'blue',
                        'is_read' => false,
                        'data' => [
                            'pay_period' => $result['payPeriod'],
                            'total_amount' => $result['amount'],
                        ]
                    ]);
                    
                    Log::info('Payroll finalized notification created successfully', ['notification_id' => $notification->id ?? null]);
                } catch (\Exception $e) {
                    Log::error('Failed to create finalize notification', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                }
            }

            if ($result['already'] ?? false) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payroll already finalized for this period',
                    'pay_period' => $result['payPeriod'],
                    'amount' => $result['amount']
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Payroll finalized successfully! Amount locked: ₱" . number_format($result['amount'], 2),
                'pay_period' => $result['payPeriod'],
                'amount' => $result['amount']
            ]);

        } catch (\Exception $e) {
            Log::error('PayrollController@finalizePayroll error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error finalizing payroll: ' . $e->getMessage()
            ], 500);
        }
    }

    // send payslip email sa tutor
    public function sendPayslipEmail($tutor)
    {
        try {
            // Handle both numeric ID and formatted tutorID
            if (is_numeric($tutor)) {
                $tutorModel = Tutor::where('tutor_id', $tutor)->with(['account'])->firstOrFail();
            } else {
                $tutorModel = Tutor::where('tutorID', $tutor)->with(['account'])->firstOrFail();
            }

            // Determine current pay period using helper (28→12 or 13→27)
            $period = PayPeriodHelper::getCurrentPeriod();
            $periodStart = $period['start']->format('Y-m-d');
            $periodEnd = $period['end']->format('Y-m-d');

            // Approved work details
            // TutorWorkDetail.tutor_id stores the formatted tutorID string (e.g. OGS-T0001)
            $approved = TutorWorkDetail::where('tutor_id', $tutorModel->tutorID)
                ->where('status', 'approved')
                ->whereDate('created_at', '>=', $periodStart)
                ->whereDate('created_at', '<=', $periodEnd)
                ->orderBy('created_at', 'desc')
                ->get();

            // Compute total earnings and deductions
            $totalEarnings = $approved->reduce(function ($carry, $wd) {
                if (($wd->work_type ?? '') === 'hourly') {
                    $hours = ($wd->duration_minutes ?? 0) / 60;
                    $carry += ($wd->rate_per_hour ?? 0) * $hours;
                } else {
                    $carry += ($wd->rate_per_class ?? 0);
                }
                return $carry;
            }, 0);

            $deductions = 0; // adjust if needed

            $email = $tutorModel->email ?? $tutorModel->account?->email;
            if (!$email) {
                return response()->json(['success' => false, 'message' => 'No email for this tutor.']);
            }

            Mail::to($email)->send(new PayslipMail(
                $tutorModel,
                $approved,
                $totalEarnings,
                $deductions,
                $periodStart,
                $periodEnd
            ));

            // Notify tutor that payslip email was sent
            try {
                Notification::create([
                    'user_id' => $tutorModel->tutor_id,
                    'user_type' => 'tutor',
                    'type' => 'payslip_email_sent',
                    'title' => 'Payslip Sent',
                    'message' => "Your payslip for {$periodStart} to {$periodEnd} was sent to {$email}.",
                    'icon' => 'fas fa-envelope',
                    'color' => 'green',
                    'is_read' => false,
                    'data' => [
                        'period_start' => $periodStart,
                        'period_end' => $periodEnd,
                        'recipient_email' => $email,
                    ]
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to create payslip email notification: ' . $e->getMessage());
            }

            return response()->json(['success' => true, 'message' => 'Payslip emailed successfully']);

        } catch (\Exception $e) {
            Log::error('PayrollController@sendPayslipEmail error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['success' => false, 'message' => 'Failed to send payslip: ' . $e->getMessage()]);
        }
    }

// log payroll email submission
    public function logPayrollEmail(Request $request)
    {
        try {
            Log::info('PayrollHistory: logPayrollEmail called', ['payload' => $request->all()]);

            $validated = $request->validate([
                'tutor_id' => 'required|integer|exists:tutors,tutor_id',
                'pay_period' => 'required|string',
                'recipient_email' => 'required|email'
            ]);

            Log::info('PayrollHistory: Validation passed', ['validated' => $validated]);

            $tutor = Tutor::find($validated['tutor_id']);
            $totalAmount = $this->calculateTotalAmountForPeriod($tutor?->tutorID, $validated['pay_period']);

            $record = PayrollHistory::create([
                'tutor_id' => $validated['tutor_id'],
                'pay_period' => $validated['pay_period'],
                'total_amount' => $totalAmount,
                'submission_type' => 'email',
                'status' => 'sent',
                'recipient_email' => $validated['recipient_email'],
                'submitted_at' => now()
            ]);

            Log::info('PayrollHistory: Email record created', ['record_id' => $record->payroll_history_id]);

            // Also notify tutor
            try {
                Notification::create([
                    'user_id' => $validated['tutor_id'],
                    'user_type' => 'tutor',
                    'type' => 'payslip_email_logged',
                    'title' => 'Payslip Email Logged',
                    'message' => "Your payslip email was logged for period {$validated['pay_period']}.",
                    'icon' => 'fas fa-paper-plane',
                    'color' => 'green',
                    'is_read' => false,
                    'data' => [
                        'pay_period' => $validated['pay_period'],
                        'recipient_email' => $validated['recipient_email']
                    ]
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to create payslip email logged notification: ' . $e->getMessage());
            }

            return response()->json(['success' => true, 'message' => 'Payroll submission logged']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('PayrollController@logPayrollEmail validation error: ' . json_encode($e->errors()));
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('PayrollController@logPayrollEmail error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to log submission: ' . $e->getMessage()], 500);
        }
    }

// log payroll PDF export
    public function logPayrollPdf(Request $request)
    {
        try {
            Log::info('PayrollHistory: logPayrollPdf called', ['payload' => $request->all()]);

            $validated = $request->validate([
                'tutor_id' => 'required|integer|exists:tutors,tutor_id',
                'pay_period' => 'required|string',
                'submission_type' => 'required|in:pdf,print'
            ]);

            Log::info('PayrollHistory: Validation passed', ['validated' => $validated]);

            $tutor = Tutor::find($validated['tutor_id']);
            $totalAmount = $this->calculateTotalAmountForPeriod($tutor?->tutorID, $validated['pay_period']);

            $record = PayrollHistory::create([
                'tutor_id' => $validated['tutor_id'],
                'pay_period' => $validated['pay_period'],
                'total_amount' => $totalAmount,
                'submission_type' => $validated['submission_type'],
                'status' => 'sent',
                'submitted_at' => now()
            ]);

            Log::info('PayrollHistory: PDF record created', ['record_id' => $record->payroll_history_id]);

            return response()->json(['success' => true, 'message' => 'Payroll submission logged']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('PayrollController@logPayrollPdf validation error: ' . json_encode($e->errors()));
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('PayrollController@logPayrollPdf error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to log submission: ' . $e->getMessage()], 500);
        }
    }

    // calculate total amount for a given tutor and pay period
    private function calculateTotalAmountForPeriod(?string $tutorFormattedId, ?string $payPeriod): float
    {
        if (! $tutorFormattedId || ! $payPeriod) {
            return 0.0;
        }

        try {
            $start = Carbon::parse($payPeriod . '-01')->startOfMonth();
            $end = $start->copy()->endOfMonth();
        } catch (\Exception $e) {
            Log::warning('PayrollHistory total_amount parse failed: ' . $e->getMessage(), [
                'pay_period' => $payPeriod,
                'tutor_id' => $tutorFormattedId,
            ]);
            return 0.0;
        }

        $details = TutorWorkDetail::where('tutor_id', $tutorFormattedId)
            ->where('status', 'approved')
            ->where('payment_blocked', false)
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $total = $details->reduce(function ($carry, $wd) {
            if (($wd->work_type ?? '') === 'hourly') {
                $hours = ($wd->duration_minutes ?? 0) / 60;
                $carry += ($wd->rate_per_hour ?? 0) * $hours;
            } else {
                $carry += ($wd->rate_per_class ?? 0);
            }
            return $carry;
        }, 0);

        return round($total, 2);
    }

    /**
     * Fetch salary history for a tutor (API endpoint)
     */
    public function salaryHistory(Request $request, $tutorID)
    {
        try {
            // Get tutor
            $tutor = Tutor::where('tutorID', $tutorID)->firstOrFail();

            // Check authorization - only supervisors with matching account can view
            if (Auth::guard('supervisor')->check()) {
                $supervisor = Auth::guard('supervisor')->user();
                if ($supervisor->assigned_account) {
                    $tutorAccount = $tutor->account?->account_name ?? '';
                    if (strtolower($tutorAccount) !== strtolower($supervisor->assigned_account)) {
                        return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
                    }
                }
            }

            // Pagination
            $perPage = $request->query('per_page', 10);
            
            // Get payroll history with pagination - only finalized status
            $history = PayrollHistory::where('tutor_id', $tutor->tutor_id)
                ->where('status', 'finalized')
                ->orderBy('pay_period', 'desc')
                ->paginate($perPage);

            // Add total work hours for each finalized period
            $historyWithHours = $history->getCollection()->map(function($record) use ($tutor) {
                // Parse pay period to get date range
                $payPeriod = $record->pay_period;
                preg_match('/(\d{4})-(\d{2})\s*\((\d{1,2})-(\d{1,2})\)/', $payPeriod, $matches);
                
                if (count($matches) === 5) {
                    $year = $matches[1];
                    $month = $matches[2];
                    $startDay = $matches[3];
                    $endDay = $matches[4];
                    
                    $periodStart = Carbon::create($year, $month, $startDay)->startOfDay();
                    $periodEnd = Carbon::create($year, $month, $endDay)->endOfDay();
                    
                    // Calculate total minutes from approved work details in this period
                    $totalMinutes = TutorWorkDetail::where('tutor_id', $tutor->tutorID)
                        ->where('status', 'approved')
                        ->whereBetween('created_at', [$periodStart, $periodEnd])
                        ->sum('duration_minutes');
                    
                    $record->total_hours = round($totalMinutes / 60, 2);
                } else {
                    $record->total_hours = 0;
                }
                
                return $record;
            });
            
            $history->setCollection($historyWithHours);

            return response()->json([
                'success' => true,
                'tutor' => [
                    'id' => $tutor->tutorID,
                    'name' => $tutor->full_name ?? $tutor->username,
                    'account' => $tutor->account?->account_name ?? 'N/A'
                ],
                'history' => $history->items(),
                'pagination' => [
                    'total' => $history->total(),
                    'per_page' => $history->perPage(),
                    'current_page' => $history->currentPage(),
                    'last_page' => $history->lastPage(),
                    'from' => $history->firstItem(),
                    'to' => $history->lastItem(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching salary history', [
                'tutor_id' => $tutorID,
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'Error fetching salary history'], 500);
        }
    }
}