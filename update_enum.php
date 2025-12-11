<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::statement("ALTER TABLE assigned_daily_data MODIFY COLUMN class_status ENUM('not_assigned', 'partially_assigned', 'fully_assigned', 'cancelled') DEFAULT 'not_assigned'");
    echo "Successfully updated class_status enum!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
