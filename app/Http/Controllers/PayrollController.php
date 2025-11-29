<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Tutor;
use App\Models\Applicant;
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

            return view('payroll.index', [
                'payrolls' => $tutors
            ]);

        } catch (\Exception $e) {
            Log::error('PayrollController error: ' . $e->getMessage());
            return view('payroll.index')->with('error', 'Something went wrong.');
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

    // Compute duration
    $start = Carbon::parse($request->start_time);
    $end   = Carbon::parse($request->end_time);
    $duration = $end->diffInMinutes($start);

    $path = null;
    if ($request->hasFile('screenshot')) {
        $path = $request->file('screenshot')->store('screenshots', 'public');
    }

    $rateHourly = 120;
    $rateClass  = 50;

    $record = \App\Models\TutorWorkDetail::create([
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
}