<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== SUPERVISORS TABLE COLUMNS ===\n";
$columns = DB::select('DESCRIBE supervisors');
foreach ($columns as $col) {
    echo "  - {$col->Field} ({$col->Type})\n";
}
