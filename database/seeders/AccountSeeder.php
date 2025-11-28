<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            [
                'account_name' => 'gls',
                'description' => 'GLS - Global Learning Solutions',
                'industry' => 'Education',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'account_name' => 'talk915',
                'description' => 'Talk915 - English Conversation Platform',
                'industry' => 'Education',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'account_name' => 'babilala',
                'description' => 'Babilala - Language Learning Platform',
                'industry' => 'Education',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'account_name' => 'tutlo',
                'description' => 'Tutlo - Online Tutoring Services',
                'industry' => 'Education',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('accounts')->insert($accounts);
    }
}
