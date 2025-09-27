<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tutor;
use Illuminate\Support\Facades\Hash;

class TutorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Add phone numbers to existing tutors
        $existingTutors = Tutor::whereNull('phone_number')->get();
        $phoneNumbers = [
            '+1-555-0101', '+1-555-0102', '+1-555-0103', '+1-555-0104', 
            '+1-555-0105', '+1-555-0106', '+1-555-0107', '+1-555-0108'
        ];
        
        $phoneIndex = 0;
        foreach ($existingTutors as $tutor) {
            if ($phoneIndex < count($phoneNumbers)) {
                $tutor->update(['phone_number' => $phoneNumbers[$phoneIndex]]);
                $phoneIndex++;
            }
        }
        
        $this->command->info("Updated {$existingTutors->count()} existing tutors with phone numbers");

        // Add more tutors with phone numbers
        $newTutors = [
            [
                'applicantID' => 'APPL003',
                'email' => 'alice.wong@example.com',
                'phone_number' => '+1-555-0201',
                'tusername' => 'alicewong',
                'tpassword' => Hash::make('password'),
                'status' => 'active'
            ],
            [
                'applicantID' => 'APPL004',
                'email' => 'bob.chen@example.com',
                'phone_number' => '+1-555-0202',
                'tusername' => 'bobchen',
                'tpassword' => Hash::make('password'),
                'status' => 'active'
            ],
            [
                'applicantID' => 'APPL005',
                'email' => 'carol.kim@example.com',
                'phone_number' => '+1-555-0203',
                'tusername' => 'carolkim',
                'tpassword' => Hash::make('password'),
                'status' => 'active'
            ],
            [
                'applicantID' => 'APPL006',
                'email' => 'david.lee@example.com',
                'phone_number' => '+1-555-0204',
                'tusername' => 'davidlee',
                'tpassword' => Hash::make('password'),
                'status' => 'active'
            ],
            [
                'applicantID' => 'APPL007',
                'email' => 'emily.tan@example.com',
                'phone_number' => '+1-555-0205',
                'tusername' => 'emilytan',
                'tpassword' => Hash::make('password'),
                'status' => 'active'
            ],
            [
                'applicantID' => 'APPL008',
                'email' => 'frank.ng@example.com',
                'phone_number' => '+1-555-0206',
                'tusername' => 'frankng',
                'tpassword' => Hash::make('password'),
                'status' => 'inactive'
            ],
            [
                'applicantID' => 'APPL009',
                'email' => 'grace.lim@example.com',
                'phone_number' => '+1-555-0207',
                'tusername' => 'gracelim',
                'tpassword' => Hash::make('password'),
                'status' => 'active'
            ],
            [
                'applicantID' => 'APPL010',
                'email' => 'henry.wang@example.com',
                'phone_number' => '+1-555-0208',
                'tusername' => 'henrywang',
                'tpassword' => Hash::make('password'),
                'status' => 'active'
            ],
            [
                'applicantID' => 'APPL011',
                'email' => 'iris.zhao@example.com',
                'phone_number' => '+1-555-0209',
                'tusername' => 'iriszhao',
                'tpassword' => Hash::make('password'),
                'status' => 'active'
            ],
            [
                'applicantID' => 'APPL012',
                'email' => 'jack.liu@example.com',
                'phone_number' => '+1-555-0210',
                'tusername' => 'jackliu',
                'tpassword' => Hash::make('password'),
                'status' => 'active'
            ],
            [
                'applicantID' => 'APPL013',
                'email' => 'katie.huang@example.com',
                'phone_number' => '+1-555-0211',
                'tusername' => 'katiehuang',
                'tpassword' => Hash::make('password'),
                'status' => 'active'
            ],
            [
                'applicantID' => 'APPL014',
                'email' => 'lucas.ma@example.com',
                'phone_number' => '+1-555-0212',
                'tusername' => 'lucasma',
                'tpassword' => Hash::make('password'),
                'status' => 'active'
            ],
            [
                'applicantID' => 'APPL015',
                'email' => 'maya.singh@example.com',
                'phone_number' => '+1-555-0213',
                'tusername' => 'mayasingh',
                'tpassword' => Hash::make('password'),
                'status' => 'inactive'
            ],
            [
                'applicantID' => 'APPL016',
                'email' => 'noah.park@example.com',
                'phone_number' => '+1-555-0214',
                'tusername' => 'noahpark',
                'tpassword' => Hash::make('password'),
                'status' => 'active'
            ],
            [
                'applicantID' => 'APPL017',
                'email' => 'olivia.xu@example.com',
                'phone_number' => '+1-555-0215',
                'tusername' => 'oliviaxu',
                'tpassword' => Hash::make('password'),
                'status' => 'active'
            ],
            [
                'applicantID' => 'APPL018',
                'email' => 'peter.yam@example.com',
                'phone_number' => '+1-555-0216',
                'tusername' => 'peteryam',
                'tpassword' => Hash::make('password'),
                'status' => 'active'
            ],
            [
                'applicantID' => 'APPL019',
                'email' => 'quinn.taylor@example.com',
                'phone_number' => '+1-555-0217',
                'tusername' => 'quinntaylor',
                'tpassword' => Hash::make('password'),
                'status' => 'active'
            ],
            [
                'applicantID' => 'APPL020',
                'email' => 'rachel.brown@example.com',
                'phone_number' => '+1-555-0218',
                'tusername' => 'rachelbrown',
                'tpassword' => Hash::make('password'),
                'status' => 'active'
            ]
        ];

        $created = [];
        foreach ($newTutors as $tutorData) {
            // Check if tutor already exists by email or applicantID
            $existing = Tutor::where('email', $tutorData['email'])
                            ->orWhere('applicantID', $tutorData['applicantID'])
                            ->first();
            
            if (!$existing) {
                $tutor = Tutor::create($tutorData);
                $created[] = [
                    'username' => $tutor->tusername,
                    'phone' => $tutor->phone_number,
                    'status' => $tutor->status
                ];
            }
        }

        $this->command->info('Created ' . count($created) . ' new tutors:');
        foreach ($created as $tutor) {
            $statusIcon = $tutor['status'] === 'active' ? '✅' : '❌';
            $this->command->info("  {$statusIcon} {$tutor['username']} - {$tutor['phone']}");
        }

        $totalTutors = Tutor::count();
        $activeTutors = Tutor::where('status', 'active')->count();
        $inactiveTutors = Tutor::where('status', 'inactive')->count();
        
        $this->command->info("=== Tutor Statistics ===");
        $this->command->info("Total tutors: {$totalTutors}");
        $this->command->info("Active tutors: {$activeTutors}");
        $this->command->info("Inactive tutors: {$inactiveTutors}");
        $this->command->info("Tutors with phone numbers: " . Tutor::whereNotNull('phone_number')->count());
    }
}