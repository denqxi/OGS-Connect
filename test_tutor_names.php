<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$tutors = DB::select("
    SELECT 
        t.tutor_id, 
        t.tutorID, 
        t.applicant_id, 
        a.first_name, 
        a.last_name
    FROM tutor t
    JOIN applicants a ON t.applicant_id = a.applicant_id
    WHERE t.tutorID LIKE 'OGS-T%'
    ORDER BY t.tutorID
    LIMIT 10
");

echo json_encode($tutors, JSON_PRETTY_PRINT);
