<?php

namespace App\Http\Controllers;

use App\Models\EmployeePaymentInformation;
use App\Models\Tutor;
use App\Models\Supervisor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentInformationController extends Controller
{
    /**
     * Display the payment information form
     */
    public function index()
    {
        $user = Auth::user();
        $employeeType = $this->getEmployeeType($user);
        $employeeId = $this->getEmployeeId($user);
        
        $paymentInfo = EmployeePaymentInformation::where('employee_id', $employeeId)
                                                ->where('employee_type', $employeeType)
                                                ->first();
        
        return view('profile_management.payment_information', compact('paymentInfo', 'employeeType', 'employeeId'));
    }

    /**
     * Store or update payment information
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $employeeType = $this->getEmployeeType($user);
        $employeeId = $this->getEmployeeId($user);

        try {
            $request->validate([
                'payment_method' => 'required|in:bank_transfer,paypal,gcash,paymaya,cash',
                'bank_name' => 'required_if:payment_method,bank_transfer|nullable|string|max:255',
                'account_number' => 'required_if:payment_method,bank_transfer|nullable|string|max:255',
                'account_name' => 'required_if:payment_method,bank_transfer|nullable|string|max:255',
                'paypal_email' => 'required_if:payment_method,paypal|nullable|email|max:255',
                'gcash_number' => 'required_if:payment_method,gcash|nullable|string|max:20',
                'paymaya_number' => 'required_if:payment_method,paymaya|nullable|string|max:20',
                'notes' => 'nullable|string|max:1000',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        try {
            $data = [
                'employee_id' => $employeeId,
                'employee_type' => $employeeType,
                'payment_method' => $request->payment_method,
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'account_name' => $request->account_name,
                'paypal_email' => $request->paypal_email,
                'gcash_number' => $request->gcash_number,
                'paymaya_number' => $request->paymaya_number,
                'notes' => $request->notes,
                'is_active' => true,
            ];

            $paymentInfo = EmployeePaymentInformation::updateOrCreate(
                [
                    'employee_id' => $employeeId,
                    'employee_type' => $employeeType,
                ],
                $data
            );

            // Check if request is AJAX
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment information updated successfully!',
                    'payment_info' => $paymentInfo
                ]);
            }

            return redirect()->back()->with('success', 'Payment information updated successfully!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update payment information. Please try again.'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to update payment information. Please try again.');
        }
    }

    /**
     * Get payment information for a specific employee (for admin use)
     */
    public function show($employeeType, $employeeId)
    {
        $paymentInfo = EmployeePaymentInformation::where('employee_id', $employeeId)
                                                ->where('employee_type', $employeeType)
                                                ->first();

        if (!$paymentInfo) {
            return response()->json(['error' => 'Payment information not found'], 404);
        }

        return response()->json($paymentInfo);
    }

    /**
     * Get all payment information (for admin use)
     */
    public function getAll()
    {
        $paymentInfo = EmployeePaymentInformation::with('employee')->get();
        return response()->json($paymentInfo);
    }

    /**
     * Determine employee type from authenticated user
     */
    private function getEmployeeType($user)
    {
        if ($user instanceof Tutor) {
            return 'tutor';
        } elseif ($user instanceof Supervisor) {
            return 'supervisor';
        }
        
        throw new \Exception('Invalid user type');
    }

    /**
     * Get employee ID from authenticated user
     */
    private function getEmployeeId($user)
    {
        if ($user instanceof Tutor) {
            return $user->tutorID;
        } elseif ($user instanceof Supervisor) {
            return $user->supID;
        }
        
        throw new \Exception('Invalid user type');
    }
}