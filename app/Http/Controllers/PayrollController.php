<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Tutor;
use App\Models\Applicant;
use App\Models\TutorWorkDetail;
use App\Models\TutorWorkDetailApproval;
use Illuminate\Support\Facades\Auth;
use App\Mail\PayslipMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Base query with relationships
            $query = Tutor::with(['paymentInformation', 'applicant', 'workDetails'])
                ->where('status', 'active');

            // Search
            if ($request->filled('search')) {

                $search = $request->input('search');

                $query->where(function ($q) use ($search) {

                    $q->where('tusername', 'like', "%{$search}%")
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

                $payment = $tutor->paymentInformation;
                $amount = $payment ? ($payment->monthly_salary ?? $payment->hourly_rate) : null;

                $tutor->salary = $amount;
                $tutor->amount = $amount;
                $tutor->pay_date = null;

                return $tutor;
            });

            $viewData = [
                'payrolls' => $tutors,
                'workDetails' => $tutors,
                'tutors' => $tutors,
            ];

            // If history tab requested, load approvals paginator
            if ($request->query('tab') === 'history') {
                $approvals = TutorWorkDetailApproval::with(['workDetail.tutor.applicant', 'supervisor'])
                    ->orderBy('approved_at', 'desc')
                    ->paginate(10)
                    ->withQueryString();

                $viewData['workApprovals'] = $approvals;
            }

            return view('payroll.index', $viewData);

        } catch (\Exception $e) {
            Log::error('PayrollController error: ' . $e->getMessage());
            return view('payroll.index')->with('error', 'Something went wrong.');
        }
    }
    /**
     * Return the work details table HTML for account_id = 1 (GLS).
     * This is a separate method so the original index() stays unchanged
     * (used by tutor side elsewhere).
     */
public function workDetails(Request $request)
{
    try {
        $search = $request->input('search');
        $statusFilter = $request->input('status');

        $workQuery = TutorWorkDetail::with(['tutor.applicant'])
            ->whereHas('tutor', function ($q) {
                $q->where('account_id', 1);
            });

        /**
         * STATUS FILTER LOGIC
         * -------------------
         * Dropdown values supported:
         *   ""         → show PENDING only (default)
         *   "pending"  → show pending
         *   "approved" → show approved
         *   "reject"   → show rejected
         *   "all"      → show all statuses
         */
        
        if ($statusFilter === null || $statusFilter === '') {
            // Default view → ONLY pending
            $workQuery->where('status', 'pending');
        } elseif ($statusFilter !== 'all') {
            // Apply specific filter
            $workQuery->where('status', $statusFilter);
        }
        // if status = all → no filtering


        /** SEARCH FILTER */
        if (!empty($search)) {
            $workQuery->where(function ($q) use ($search) {
                $q->where('work_type', 'like', "%{$search}%")
                  ->orWhere('class_no', 'like', "%{$search}%")
                  ->orWhereHas('tutor', function ($tq) use ($search) {
                      $tq->where('tusername', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('phone_number', 'like', "%{$search}%")
                         ->orWhereHas('applicant', function ($aq) use ($search) {
                             $aq->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                         });
                  });
            });
        }

            $workDetails = $workQuery
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('payroll.partials.tutor_payroll_details', compact('workDetails'));

    } catch (\Exception $e) {
        Log::error('PayrollController@workDetails error: ' . $e->getMessage());
        return response('Error loading work details', 500);
    }
}

    public function storeWorkDetail(Request $request)
{
    $request->validate([
        'tutor_id'      => 'required|exists:tutors,id',
        'work_type'     => 'required|in:hourly,per_class',
        'day'           => 'required|date',
        'start_time'    => 'required|date_format:H:i',
        'end_time'      => 'required|date_format:H:i|after:start_time',
        'screenshot'    => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
    ]);

    $start = Carbon::parse($request->start_time);
    $end   = Carbon::parse($request->end_time);
    $duration = $end->diffInMinutes($start);

    $path = null;
    if ($request->hasFile('screenshot')) {
        $path = $request->file('screenshot')->store('screenshots', 'public');
    }

    $rateHourly = 120;
    $rateClass  = 50;

    $record = TutorWorkDetail::create([
        'tutor_id'        => $request->tutor_id,
        'work_type'       => $request->work_type,
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

            $newStatus = $request->input('status', 'approved');
            $oldStatus = $detail->status;

            $detail->status = $newStatus;
            $detail->save();

            $supervisorId = null;
            try {
                $supervisor = Auth::guard('supervisor')->user() ?: Auth::user();
                if ($supervisor) {
                    $supervisorId = $supervisor->supervisor_id ?? $supervisor->id ?? null;
                }

                TutorWorkDetailApproval::create([
                    'work_detail_id' => $detail->id,
                    'supervisor_id' => $supervisorId,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'approved_at' => now(),
                    'note' => $request->input('note'),
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to record approval: ' . $e->getMessage());
            }

            return response()->json(['message' => 'Work detail ' . $newStatus . ' successfully']);
        } catch (\Exception $e) {
            Log::error('PayrollController@approveWorkDetail exception: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['message' => 'Server error while processing request'], 500);
        }
    }

    /**
     * Reject a work detail with required note.
     */
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

            $oldStatus = $detail->status;
            $detail->status = 'reject';
            $detail->save();

            // record approval/rejection
            try {
                $supervisor = Auth::guard('supervisor')->user() ?: Auth::user();
                $supervisorId = $supervisor->supervisor_id ?? $supervisor->id ?? null;

                TutorWorkDetailApproval::create([
                    'work_detail_id' => $detail->id,
                    'supervisor_id' => $supervisorId,
                    'old_status' => $oldStatus,
                    'new_status' => 'reject',
                    'approved_at' => now(),
                    'note' => $request->input('note'),
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to record rejection: ' . $e->getMessage());
            }

            return response()->json(['message' => 'Work detail rejected successfully']);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json(['message' => 'Validation failed', 'errors' => $ve->errors()], 422);
        } catch (\Exception $e) {
            Log::error('PayrollController@rejectWorkDetail exception: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['message' => 'Server error while processing request'], 500);
        }
    }

    /**
     * Return a small summary view for a tutor's approved work details.
     * Used by the frontend modal to show totals for a specific tutor.
     */
    public function tutorSummary(Request $request, $tutor)
    {
        try {
            $tutorModel = Tutor::where('tutorID', $tutor)->with(['applicant', 'accounts'])->first();
            if (! $tutorModel) {
                return response('Tutor not found', 404);
            }
            $today = Carbon::now();
            $day = (int) $today->format('j');
            if ($day <= 15) {
                $periodStart = $today->copy()->startOfMonth()->format('Y-m-d');
                $periodEnd = $today->copy()->day(15)->format('Y-m-d');
            } else {
                $periodStart = $today->copy()->day(16)->format('Y-m-d');
                $periodEnd = $today->copy()->endOfMonth()->format('Y-m-d');
            }

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

            $summary = [
                'tutor' => $tutorModel,
                'total_items' => $totalItems,
                'total_minutes' => $totalMinutes,
                'total_hours' => round($totalMinutes / 60, 2),
                'total_earnings' => round($totalEarnings, 2),
                'details' => $approved,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
            ];

            return view('payroll.partials.tutor_summary', $summary);

        } catch (\Exception $e) {
            Log::error('PayrollController@tutorSummary error: ' . $e->getMessage());
            return response('Error generating summary', 500);
        }
    }
    public function sendPayslipEmail($tutorID)
    {
        try {
            $tutor = Tutor::where('tutorID', $tutorID)->with(['account'])->firstOrFail();

            // Determine current pay period like in tutorSummary
            $today = Carbon::now();
            if ((int)$today->format('j') <= 15) {
                $periodStart = $today->copy()->startOfMonth()->format('Y-m-d');
                $periodEnd = $today->copy()->day(15)->format('Y-m-d');
            } else {
                $periodStart = $today->copy()->day(16)->format('Y-m-d');
                $periodEnd = $today->copy()->endOfMonth()->format('Y-m-d');
            }

            // Approved work details
            // TutorWorkDetail.tutor_id stores the formatted tutorID string (e.g. OGS-T0001)
            $approved = TutorWorkDetail::where('tutor_id', $tutor->tutorID)
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

            $email = $tutor->email ?? $tutor->account?->email;
            if (!$email) {
                return response()->json(['success' => false, 'message' => 'No email for this tutor.']);
            }

            Mail::to($email)->send(new PayslipMail(
                $tutor,
                $approved,
                $totalEarnings,
                $deductions,
                $periodStart,
                $periodEnd
            ));

            return response()->json(['success' => true, 'message' => 'Payslip emailed successfully']);

        } catch (\Exception $e) {
            Log::error('PayrollController@sendPayslipEmail error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to send payslip']);
        }
    }

}