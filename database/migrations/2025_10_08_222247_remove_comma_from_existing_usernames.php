<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing usernames to remove comma
        $tutors = DB::table('tutors')->where('tusername', 'like', '%,%@ogsconnect.com')->get();
        foreach ($tutors as $tutor) {
            $newUsername = str_replace(',', '', $tutor->username);
            $tutor->update(['username' => $newUsername]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not reversible as we can't determine where commas should be added back
    }
};
