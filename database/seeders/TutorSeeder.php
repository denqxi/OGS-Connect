<?php

namespace Database\Seeders;

use App\Models\Tutor;
use Illuminate\Database\Seeder;

class TutorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if tutors already exist
        if (Tutor::count() > 0) {
            if ($this->command) {
                $this->command->info('Tutors already exist, skipping...');
            }
            return;
        }

        $tutors = [
            [
                'tusername' => 'alicewong',
                'first_name' => 'Alice',
                'last_name' => 'Wong',
                'email' => 'alice.wong@example.com',
                'tpassword' => bcrypt('password123'),
                'phone_number' => '+1-555-0101',
                'sex' => 'F',
                'status' => 'active'
            ],
            [
                'tusername' => 'bobsmith',
                'first_name' => 'Bob',
                'last_name' => 'Smith',
                'email' => 'bob.smith@example.com',
                'tpassword' => bcrypt('password123'),
                'phone_number' => '+1-555-0102',
                'sex' => 'M',
                'status' => 'active'
            ],
            [
                'tusername' => 'caroljohnson',
                'first_name' => 'Carol',
                'last_name' => 'Johnson',
                'email' => 'carol.johnson@example.com',
                'tpassword' => bcrypt('password123'),
                'phone_number' => '+1-555-0103',
                'sex' => 'F',
                'status' => 'active'
            ],
            [
                'tusername' => 'davidlee',
                'first_name' => 'David',
                'last_name' => 'Lee',
                'email' => 'david.lee@example.com',
                'tpassword' => bcrypt('password123'),
                'phone_number' => '+1-555-0104',
                'sex' => 'M',
                'status' => 'active'
            ],
            [
                'tusername' => 'emilychen',
                'first_name' => 'Emily',
                'last_name' => 'Chen',
                'email' => 'emily.chen@example.com',
                'tpassword' => bcrypt('password123'),
                'phone_number' => '+1-555-0105',
                'sex' => 'F',
                'status' => 'active'
            ],
            [
                'tusername' => 'frankbrown',
                'first_name' => 'Frank',
                'last_name' => 'Brown',
                'email' => 'frank.brown@example.com',
                'tpassword' => bcrypt('password123'),
                'phone_number' => '+1-555-0106',
                'sex' => 'M',
                'status' => 'inactive'
            ],
            [
                'tusername' => 'gracewilson',
                'first_name' => 'Grace',
                'last_name' => 'Wilson',
                'email' => 'grace.wilson@example.com',
                'tpassword' => bcrypt('password123'),
                'phone_number' => '+1-555-0107',
                'sex' => 'F',
                'status' => 'active'
            ],
            [
                'tusername' => 'henrydavis',
                'first_name' => 'Henry',
                'last_name' => 'Davis',
                'email' => 'henry.davis@example.com',
                'tpassword' => bcrypt('password123'),
                'phone_number' => '+1-555-0108',
                'sex' => 'M',
                'status' => 'active'
            ],
            [
                'tusername' => 'irisgarcia',
                'first_name' => 'Iris',
                'last_name' => 'Garcia',
                'email' => 'iris.garcia@example.com',
                'tpassword' => bcrypt('password123'),
                'phone_number' => '+1-555-0109',
                'sex' => 'F',
                'status' => 'active'
            ],
            [
                'tusername' => 'jackmartinez',
                'first_name' => 'Jack',
                'last_name' => 'Martinez',
                'email' => 'jack.martinez@example.com',
                'tpassword' => bcrypt('password123'),
                'phone_number' => '+1-555-0110',
                'sex' => 'M',
                'status' => 'active'
            ],
            [
                'tusername' => 'karenanderson',
                'first_name' => 'Karen',
                'last_name' => 'Anderson',
                'email' => 'karen.anderson@example.com',
                'tpassword' => bcrypt('password123'),
                'phone_number' => '+1-555-0111',
                'sex' => 'F',
                'status' => 'active'
            ],
            [
                'tusername' => 'lukethompson',
                'first_name' => 'Luke',
                'last_name' => 'Thompson',
                'email' => 'luke.thompson@example.com',
                'tpassword' => bcrypt('password123'),
                'phone_number' => '+1-555-0112',
                'sex' => 'M',
                'status' => 'active'
            ],
            [
                'tusername' => 'mariarodriguez',
                'first_name' => 'Maria',
                'last_name' => 'Rodriguez',
                'email' => 'maria.rodriguez@example.com',
                'tpassword' => bcrypt('password123'),
                'phone_number' => '+1-555-0113',
                'sex' => 'F',
                'status' => 'active'
            ],
            [
                'tusername' => 'nickwhite',
                'first_name' => 'Nick',
                'last_name' => 'White',
                'email' => 'nick.white@example.com',
                'tpassword' => bcrypt('password123'),
                'phone_number' => '+1-555-0114',
                'sex' => 'M',
                'status' => 'active'
            ],
            [
                'tusername' => 'oliviataylor',
                'first_name' => 'Olivia',
                'last_name' => 'Taylor',
                'email' => 'olivia.taylor@example.com',
                'tpassword' => bcrypt('password123'),
                'phone_number' => '+1-555-0115',
                'sex' => 'F',
                'status' => 'active'
            ],
            [
                'tusername' => 'peterclark',
                'first_name' => 'Peter',
                'last_name' => 'Clark',
                'email' => 'peter.clark@example.com',
                'tpassword' => bcrypt('password123'),
                'phone_number' => '+1-555-0116',
                'sex' => 'M',
                'status' => 'active'
            ],
            [
                'tusername' => 'quinnlewis',
                'first_name' => 'Quinn',
                'last_name' => 'Lewis',
                'email' => 'quinn.lewis@example.com',
                'tpassword' => bcrypt('password123'),
                'phone_number' => '+1-555-0117',
                'sex' => 'F',
                'status' => 'active'
            ],
            [
                'tusername' => 'ryanwalker',
                'first_name' => 'Ryan',
                'last_name' => 'Walker',
                'email' => 'ryan.walker@example.com',
                'tpassword' => bcrypt('password123'),
                'phone_number' => '+1-555-0118',
                'sex' => 'M',
                'status' => 'active'
            ],
            [
                'tusername' => 'sophiahall',
                'first_name' => 'Sophia',
                'last_name' => 'Hall',
                'email' => 'sophia.hall@example.com',
                'tpassword' => bcrypt('password123'),
                'phone_number' => '+1-555-0119',
                'sex' => 'F',
                'status' => 'active'
            ],
            [
                'tusername' => 'tylerallen',
                'first_name' => 'Tyler',
                'last_name' => 'Allen',
                'email' => 'tyler.allen@example.com',
                'tpassword' => bcrypt('password123'),
                'phone_number' => '+1-555-0120',
                'sex' => 'M',
                'status' => 'active'
            ],
            [
                'tusername' => 'unajones',
                'first_name' => 'Una',
                'last_name' => 'Jones',
                'email' => 'una.jones@example.com',
                'tpassword' => bcrypt('password123'),
                'phone_number' => '+1-555-0121',
                'sex' => 'F',
                'status' => 'active'
            ],
            [
                'tusername' => 'victorwright',
                'first_name' => 'Victor',
                'last_name' => 'Wright',
                'email' => 'victor.wright@example.com',
                'tpassword' => bcrypt('password123'),
                'phone_number' => '+1-555-0122',
                'sex' => 'M',
                'status' => 'active'
            ],
            [
                'tusername' => 'wendylopez',
                'first_name' => 'Wendy',
                'last_name' => 'Lopez',
                'email' => 'wendy.lopez@example.com',
                'tpassword' => bcrypt('password123'),
                'phone_number' => '+1-555-0123',
                'sex' => 'F',
                'status' => 'active'
            ],
            [
                'tusername' => 'xavierhill',
                'first_name' => 'Xavier',
                'last_name' => 'Hill',
                'email' => 'xavier.hill@example.com',
                'tpassword' => bcrypt('password123'),
                'phone_number' => '+1-555-0124',
                'sex' => 'M',
                'status' => 'active'
            ]
        ];

        foreach ($tutors as $index => $tutorData) {
            // Set formatted tutorID as primary key
            $tutorData['tutorID'] = 'OGS-T' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
            Tutor::create($tutorData);
        }

        $this->command->info('✅ Created ' . count($tutors) . ' tutors for testing');
    }
}
