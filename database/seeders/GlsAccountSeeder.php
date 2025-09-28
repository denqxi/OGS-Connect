<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Tutor;

class GlsAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample GLS data - update existing tutors with GLS account information
        $glsData = [
            [
                'gls_id' => '890',
                'username' => 'OGS-John',
                'screen_name' => 'OGS-John',
                'sex' => 'M'
            ],
            [
                'gls_id' => '17928',
                'username' => 'OGS-Sarah',
                'screen_name' => 'OGS-Sarah',
                'sex' => 'F'
            ],
            [
                'gls_id' => '1245',
                'username' => 'OGS-Mike',
                'screen_name' => 'OGS-Mike',
                'sex' => 'M'
            ],
            [
                'gls_id' => '3456',
                'username' => 'OGS-Emma',
                'screen_name' => 'OGS-Emma',
                'sex' => 'F'
            ],
            [
                'gls_id' => '7891',
                'username' => 'OGS-David',
                'screen_name' => 'OGS-David',
                'sex' => 'M'
            ],
            [
                'gls_id' => '9876',
                'username' => 'OGS-Lisa',
                'screen_name' => 'OGS-Lisa',
                'sex' => 'F'
            ],
            [
                'gls_id' => '5432',
                'username' => 'OGS-Alex',
                'screen_name' => 'OGS-Alex',
                'sex' => 'M'
            ],
            [
                'gls_id' => '2468',
                'username' => 'OGS-Kate',
                'screen_name' => 'OGS-Kate',
                'sex' => 'F'
            ]
        ];

        // First, set sex for ALL tutors based on their names
        $allTutors = Tutor::all();
        
        // Common male/female name patterns
        $maleNames = ['john', 'bob', 'david', 'frank', 'henry', 'jack', 'lucas', 'noah', 'peter', 'quinn', 'mike', 'alex'];
        $femaleNames = ['jane', 'alice', 'carol', 'emily', 'grace', 'iris', 'katie', 'maya', 'olivia', 'rachel', 'sarah', 'emma', 'lisa', 'kate'];
        
        foreach ($allTutors as $tutor) {
            $firstName = strtolower(explode(' ', $tutor->full_name)[0]);
            
            // Determine sex based on first name
            if (in_array($firstName, $maleNames)) {
                $sex = 'M';
            } elseif (in_array($firstName, $femaleNames)) {
                $sex = 'F';
            } else {
                // Default logic for unknown names - check for common endings
                if (str_ends_with($firstName, 'a') || str_ends_with($firstName, 'e')) {
                    $sex = 'F';
                } else {
                    $sex = 'M';
                }
            }
            
            $tutor->update([
                'sex' => $sex
            ]);
            
            $this->command->info("Set sex '{$sex}' for tutor {$tutor->full_name}");
        }

        // Then handle GLS accounts for the first 8 tutors
        $tutors = Tutor::limit(8)->get();
        
        foreach ($tutors as $index => $tutor) {
            if (isset($glsData[$index])) {
                $data = $glsData[$index];
                
                // Update tutor sex with specific GLS data
                $tutor->update([
                    'sex' => $data['sex']
                ]);

                // Find or create GLS account for this tutor
                $glsAccount = DB::table('tutor_accounts')
                    ->where('tutor_id', $tutor->tutorID)
                    ->where('account_name', 'GLS')
                    ->first();

                if ($glsAccount) {
                    // Update existing GLS account
                    DB::table('tutor_accounts')
                        ->where('id', $glsAccount->id)
                        ->update([
                            'gls_id' => $data['gls_id'],
                            'username' => $data['username'],
                            'screen_name' => $data['screen_name'],
                            'updated_at' => now()
                        ]);
                } else {
                    // Create new GLS account
                    DB::table('tutor_accounts')->insert([
                        'tutor_id' => $tutor->tutorID,
                        'account_name' => 'GLS',
                        'gls_id' => $data['gls_id'],
                        'username' => $data['username'],
                        'screen_name' => $data['screen_name'],
                        'status' => 'active',
                        'available_days' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
                        'available_times' => json_encode([
                            'monday' => ['start' => '09:00', 'end' => '17:00'],
                            'tuesday' => ['start' => '09:00', 'end' => '17:00'],
                            'wednesday' => ['start' => '09:00', 'end' => '17:00'],
                            'thursday' => ['start' => '09:00', 'end' => '17:00'],
                            'friday' => ['start' => '09:00', 'end' => '17:00']
                        ]),
                        'preferred_time_range' => 'flexible',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                $this->command->info("Updated tutor {$tutor->full_name} with GLS ID: {$data['gls_id']} and sex: {$data['sex']}");
            }
        }

        $this->command->info('GLS account data seeding completed!');
    }
}
