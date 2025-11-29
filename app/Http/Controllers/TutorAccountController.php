<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tutor;

class TutorAccountController extends Controller
{
    /**
     * Return tutor accounts by formatted tutor ID (tutorID)
     * Example: GET /api/tutors/OGS-T0001/accounts
     */
    public function byTutorId($tutorID, Request $request)
    {
        // Try to find tutor by formatted tutorID (primary identifier used across app)
        $tutor = Tutor::where('tutorID', $tutorID)->first();

        // Fallback: try legacy tutor_id column
        if (!$tutor) {
            $tutor = Tutor::where('tutor_id', $tutorID)->first();
        }

        if (!$tutor) {
            return response()->json(['error' => 'Tutor not found'], 404);
        }

        // Load accounts relationship
        $accounts = $tutor->accounts()->get();

        return response()->json([
            'tutor' => [
                'tutorID' => $tutor->tutorID,
                'tusername' => $tutor->tusername,
                'full_name' => $tutor->full_name,
                'status' => $tutor->status,
            ],
            'accounts' => $accounts
        ]);
    }
}
