<?php

namespace App\Services;

use App\Models\Agence;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FedaPayService
{
    private string $apiKey;
    private string $baseUrl;

    /**
     * Initialiser avec la clé de l'agence du bien, ou la clé globale par défaut
     */
    public function __construct(?Agence $agence = null)
    {
        // Priorité : clé de l'agence → clé globale du .env
        if ($agence && $agence->hasFedapay()) {
            $this->apiKey = $agence->fedapay_secret_key;
            $env          = $agence->fedapay_env ?? 'sandbox';
        } else {
            $this->apiKey = config('services.fedapay.secret_key');
            $env          = config('services.fedapay.env', 'sandbox');
        }

        $this->baseUrl = $env === 'live'
            ? 'https://api.fedapay.com/v1'
            : 'https://sandbox-api.fedapay.com/v1';
    }

    private function http(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => 'Token ' . $this->apiKey,
            'Content-Type'  => 'application/json',
        ])->withoutVerifying();
    }

    public function createTransaction(array $data): array
    {
        $payload = [
            'description'  => $data['description'],
            'amount'       => (int) $data['amount'],
            'currency'     => ['iso' => 'XOF'],
            'callback_url' => $data['callback_url'],
            'customer'     => $data['customer'],
        ];

        Log::info('FedaPay request', [
            'url'    => $this->baseUrl . '/transactions',
            'apiKey' => substr($this->apiKey, 0, 15) . '...',
        ]);

        $response = $this->http()->post($this->baseUrl . '/transactions', $payload);

        Log::info('FedaPay response', [
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);

        if ($response->failed()) {
            $error = $response->json('message') ?? $response->body();
            throw new \Exception('FedaPay: ' . $error);
        }

        $body        = $response->json();
        $transaction = $body['v1/transaction'] ?? $body['transaction'] ?? null;

        if (!$transaction) {
            Log::error('FedaPay: structure réponse inattendue', ['body' => $body]);
            throw new \Exception('FedaPay: réponse inattendue');
        }

        $transactionId = $transaction['id'];

        // Générer le token avec return_url
        $tokenResponse = $this->http()
            ->post($this->baseUrl . '/transactions/' . $transactionId . '/token', [
                'return_url' => url('/paiement/retour'),
            ]);

        Log::info('FedaPay token response', [
            'status' => $tokenResponse->status(),
            'body'   => $tokenResponse->body(),
        ]);

        if ($tokenResponse->failed()) {
            throw new \Exception('FedaPay token: ' . ($tokenResponse->json('message') ?? $tokenResponse->body()));
        }

        $tokenBody = $tokenResponse->json();

        Log::info('FedaPay token body', ['body' => $tokenBody]);

        if (isset($tokenBody['token']) && is_array($tokenBody['token'])) {
            $token      = $tokenBody['token']['token'];
            $paymentUrl = $tokenBody['token']['url'] ?? 'https://checkout.fedapay.com/' . $token;
        } elseif (isset($tokenBody['token']) && is_string($tokenBody['token'])) {
            $token      = $tokenBody['token'];
            $paymentUrl = $tokenBody['url'] ?? 'https://checkout.fedapay.com/' . $token;
        } elseif (isset($tokenBody['url'])) {
            $token      = $tokenBody['token'] ?? '';
            $paymentUrl = $tokenBody['url'];
        } else {
            Log::error('FedaPay: structure token inconnue', ['body' => $tokenBody]);
            throw new \Exception('FedaPay: structure token inattendue');
        }

        return [
            'transaction_id' => $transactionId,
            'token'          => $token,
            'payment_url'    => $paymentUrl,
        ];
    }

    public function verifyTransaction(int $transactionId): string
    {
        $response = $this->http()->get($this->baseUrl . '/transactions/' . $transactionId);

        if ($response->failed()) {
            throw new \Exception('FedaPay verify: ' . ($response->json('message') ?? $response->body()));
        }

        $body = $response->json();
        return $body['v1/transaction']['status'] ?? $body['transaction']['status'] ?? 'pending';
    }
}
