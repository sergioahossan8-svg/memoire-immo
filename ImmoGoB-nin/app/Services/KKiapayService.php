<?php

namespace App\Services;

use App\Models\Agence;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KKiapayService
{
    private string $publicKey;
    private string $privateKey;
    private string $secretKey;
    private bool   $sandbox;

    public function __construct(?Agence $agence = null)
    {
        if ($agence && $agence->hasKkiapay()) {
            $this->publicKey  = $agence->kkiapay_public_key;
            $this->privateKey = $agence->kkiapay_private_key;
            $this->secretKey  = $agence->kkiapay_secret;
            $this->sandbox    = (bool) ($agence->kkiapay_sandbox ?? true);
        } else {
            $this->publicKey  = config('services.kkiapay.public_key', '');
            $this->privateKey = config('services.kkiapay.private_key', '');
            $this->secretKey  = config('services.kkiapay.secret', '');
            $this->sandbox    = (bool) config('services.kkiapay.sandbox', true);
        }
    }

    public function getPublicKey(): string { return $this->publicKey; }
    public function isSandbox(): bool      { return $this->sandbox; }
    public function isConfigured(): bool
    {
        return !empty($this->publicKey) && !empty($this->privateKey) && !empty($this->secretKey);
    }

    public function verifyTransaction(string $transactionId): array
    {
        $baseUrl = $this->sandbox
            ? 'https://api-sandbox.kkiapay.me'
            : 'https://api.kkiapay.me';

        $response = Http::withHeaders([
            'Accept'        => 'application/json',
            'X-API-KEY'     => $this->publicKey,
            'X-PRIVATE-KEY' => $this->privateKey,
            'X-SECRET-KEY'  => $this->secretKey,
        ])->withoutVerifying()
          ->post($baseUrl . '/api/v1/transactions/status', [
              'transactionId' => $transactionId,
          ]);

        Log::info('KKiapay verify', [
            'transactionId' => $transactionId,
            'url'           => $baseUrl . '/api/v1/transactions/status',
            'status'        => $response->status(),
            'body'          => $response->body(),
        ]);

        if ($response->failed()) {
            throw new \Exception('KKiapay verify error: ' . $response->body());
        }

        return $response->json() ?? [];
    }

    public function isApproved(string $transactionId): bool
    {
        try {
            $data = $this->verifyTransaction($transactionId);

            Log::info('KKiapay isApproved data', ['data' => $data]);

            $status = $data['status']
                ?? $data['transactionData']['status']
                ?? $data['data']['status']
                ?? $data['state']
                ?? '';

            Log::info('KKiapay status extracted', ['status' => $status]);

            return strtoupper($status) === 'SUCCESS';

        } catch (\Exception $e) {
            Log::error('KKiapay isApproved error: ' . $e->getMessage());
            return false;
        }
    }
}
