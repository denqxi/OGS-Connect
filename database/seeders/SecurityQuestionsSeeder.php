<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SecurityQuestion;
use App\Models\Tutor;
use App\Models\Supervisor;

class SecurityQuestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if security questions already exist
        if (SecurityQuestion::count() > 0) {
            if ($this->command) {
                $this->command->info('Security questions already exist, skipping...');
            }
            return;
        }

        // Define common security questions
        $securityQuestions = [
            "What is your mother's maiden name?",
            "What was the name of your first pet?",
            "What city were you born in?",
            "What was your favorite subject in school?",
            "What is the name of your childhood best friend?",
            "What was your first car?",
            "What is your favorite color?",
            "What was the name of your elementary school?",
            "What is your favorite movie?",
            "What was your childhood nickname?"
        ];

        // Seed security questions for all tutors
        $tutors = Tutor::all();
        foreach ($tutors as $tutor) {
            // Create 2 security questions for each tutor
            $question1 = $securityQuestions[array_rand($securityQuestions)];
            $question2 = $securityQuestions[array_rand($securityQuestions)];
            
            // Ensure questions are different
            while ($question1 === $question2) {
                $question2 = $securityQuestions[array_rand($securityQuestions)];
            }

            // Create first security question
            SecurityQuestion::create([
                'user_type' => 'tutor',
                'user_id' => $tutor->tutorID,
                'question' => $question1,
                'answer_hash' => bcrypt('answer1') // Default answer - should be changed by users
            ]);

            // Create second security question
            SecurityQuestion::create([
                'user_type' => 'tutor',
                'user_id' => $tutor->tutorID,
                'question' => $question2,
                'answer_hash' => bcrypt('answer2') // Default answer - should be changed by users
            ]);
        }

        // Seed security questions for all supervisors
        $supervisors = Supervisor::all();
        foreach ($supervisors as $supervisor) {
            // Create 2 security questions for each supervisor
            $question1 = $securityQuestions[array_rand($securityQuestions)];
            $question2 = $securityQuestions[array_rand($securityQuestions)];
            
            // Ensure questions are different
            while ($question1 === $question2) {
                $question2 = $securityQuestions[array_rand($securityQuestions)];
            }

            // Create first security question
            SecurityQuestion::create([
                'user_type' => 'supervisor',
                'user_id' => $supervisor->supID,
                'question' => $question1,
                'answer_hash' => bcrypt('answer1') // Default answer - should be changed by users
            ]);

            // Create second security question
            SecurityQuestion::create([
                'user_type' => 'supervisor',
                'user_id' => $supervisor->supID,
                'question' => $question2,
                'answer_hash' => bcrypt('answer2') // Default answer - should be changed by users
            ]);
        }

        $this->command->info('Security questions seeded successfully for ' . $tutors->count() . ' tutors and ' . $supervisors->count() . ' supervisors.');
    }
}
