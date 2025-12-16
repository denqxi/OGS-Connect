<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\Auth\SimplePasswordResetController;

function callController($method, $payload) {
    $controller = app(SimplePasswordResetController::class);
    $req = new Request();
    $req->replace($payload);
    $resp = $controller->{$method}($req);
    if (is_string($resp)) { echo $resp . "\n"; return; }
    if (method_exists($resp, 'getStatusCode')) {
        echo "STATUS: " . $resp->getStatusCode() . "\n";
    }
    if (method_exists($resp, 'getContent')) {
        echo $resp->getContent() . "\n";
    } else {
        var_dump($resp);
        echo "\n";
    }
}

$tests = [
    ['label' => 'Tutor valid ID', 'method' => 'getSecurityQuestion', 'payload' => ['username' => 'OGS-T0003']],
    ['label' => 'Tutor invalid ID', 'method' => 'getSecurityQuestion', 'payload' => ['username' => 'OGS-T0005']],
    ['label' => 'Supervisor valid ID', 'method' => 'getSecurityQuestion', 'payload' => ['username' => 'OGS-S1002']],
    ['label' => 'Tutor OTP send', 'method' => 'sendOtp', 'payload' => ['username' => 'OGS-T0003']],
    ['label' => 'Supervisor OTP send', 'method' => 'sendOtp', 'payload' => ['username' => 'OGS-S1002']],
];

echo "\n=== Password Reset Flow Tests ===\n\n";
foreach ($tests as $t) {
    echo "-- " . $t['label'] . " --\n";
    callController($t['method'], $t['payload']);
    echo "\n";
}
