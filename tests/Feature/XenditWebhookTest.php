<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    Config::set('services.xendit.webhook_token', 'valid-webhook-secret-token');
});

test('xendit webhook returns 401 unauthorized when x-callback-token header is missing', function (string $uri) {
    $response = $this->postJson($uri, [
        'id' => 'xnd_inv_123',
        'status' => 'PAID',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Unauthorized',
        ]);
})->with([
    '/webhooks/xendit',
    '/api/webhooks/xendit',
]);

test('xendit webhook returns 401 unauthorized when x-callback-token is invalid', function (string $uri) {
    $response = $this->postJson($uri, [
        'id' => 'xnd_inv_123',
        'status' => 'PAID',
    ], [
        'x-callback-token' => 'invalid-token-here',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Unauthorized',
        ]);
})->with([
    '/webhooks/xendit',
    '/api/webhooks/xendit',
]);

test('xendit webhook successfully processes valid paid invoice and logs payload', function (string $uri) {
    Log::spy();

    $payload = [
        'id' => 'xnd_inv_valid_123',
        'external_id' => 'INVOICE-TEST-001',
        'status' => 'PAID',
        'amount' => 150000,
        'payment_method' => 'BANK_TRANSFER',
    ];

    $response = $this->postJson($uri, $payload, [
        'x-callback-token' => 'valid-webhook-secret-token',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    Log::shouldHaveReceived('info')->with('Xendit Webhook Received', Mockery::on(function ($loggedPayload) use ($payload) {
        return isset($loggedPayload['id']) && $loggedPayload['id'] === $payload['id'];
    }));
})->with([
    '/webhooks/xendit',
    '/api/webhooks/xendit',
]);
