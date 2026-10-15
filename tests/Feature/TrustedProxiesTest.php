<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::get('/_proxy-probe', fn (Request $request) => [
        'ip' => $request->ip(),
        'secure' => $request->secure(),
    ]);
});

test('requests forwarded by the tunnel resolve the visitor ip and https scheme', function () {
    $this->withServerVariables(['REMOTE_ADDR' => '172.18.0.5'])
        ->withHeaders([
            'X-Forwarded-For' => '203.0.113.7',
            'X-Forwarded-Proto' => 'https',
        ])
        ->getJson('/_proxy-probe')
        ->assertOk()
        ->assertExactJson(['ip' => '203.0.113.7', 'secure' => true]);
});

test('direct requests keep the connecting ip and plain http', function () {
    $this->withServerVariables(['REMOTE_ADDR' => '172.18.0.5'])
        ->getJson('/_proxy-probe')
        ->assertOk()
        ->assertExactJson(['ip' => '172.18.0.5', 'secure' => false]);
});
