<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tutor;

$id = $argv[1] ?? 'OGS-T0005';
$t = Tutor::where('tutorID', $id)->first();
if ($t) {
    echo "FOUND: {$t->tutorID} email={$t->email} username={$t->username}\n";
} else {
    echo "NOT FOUND: {$id}\n";
}
