<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$poliza = \App\Models\Poliza::orderBy('id', 'desc')->first();
$ren = $poliza->renovacionDe;

file_put_contents('out.json', json_encode($ren, JSON_PRETTY_PRINT));
