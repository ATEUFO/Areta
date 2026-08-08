<?php

namespace App\Services;

use App\Models\User;
use App\Models\WebhookSubscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service de diffusion des Webhooks Areta pour l'intégration multi-plateformes.
 */
class WebhookDispatcherService
{
    /**
     * Diffuse un événement aux abonnés concernés.
     *
     * @param string $eventName Ex: "session.report.submitted"
     * @param array $payload Données de l'événement
     * @param User|null $targetUser Si restreint à un utilisateur spécifique
     */
    public function dispatch(string $eventName, array $payload, ?User $targetUser = null): void
    {
        $query = WebhookSubscription::where('is_active', true);

        if ($targetUser) {
            $query->where('user_id', $targetUser->id);
        }

        $subscriptions = $query->get();

        foreach ($subscriptions as $subscription) {
            if (in_array($eventName, $subscription->events, true) || in_array('*', $subscription->events, true)) {
                $this->sendWebhook($subscription, $eventName, $payload);
            }
        }
    }

    /**
     * Envoie une requête HTTP POST signée à l'URL du webhook.
     */
    public function sendWebhook(WebhookSubscription $subscription, string $eventName, array $data): bool
    {
        $body = [
            'event'     => $eventName,
            'timestamp' => now()->toISOString(),
            'data'      => $data,
        ];

        $jsonPayload = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $signature   = $subscription->signPayload($jsonPayload);

        try {
            $response = Http::withHeaders([
                'Content-Type'      => 'application/json',
                'User-Agent'        => 'Areta-Webhook-Engine/1.0',
                'X-Areta-Event'     => $eventName,
                'X-Areta-Signature' => $signature,
            ])
            ->timeout(5)
            ->withBody($jsonPayload, 'application/json')
            ->post($subscription->url);

            if ($response->successful()) {
                // Réinitialiser le compteur d'échecs
                if ($subscription->failed_attempts_count > 0) {
                    $subscription->update(['failed_attempts_count' => 0]);
                }
                return true;
            }

            $this->handleFailure($subscription);
            return false;

        } catch (\Throwable $e) {
            Log::warning("Échec webhook {$subscription->url} (Event: {$eventName}) : {$e->getMessage()}");
            $this->handleFailure($subscription);
            return false;
        }
    }

    protected function handleFailure(WebhookSubscription $subscription): void
    {
        $newCount = $subscription->failed_attempts_count + 1;
        $subscription->update([
            'failed_attempts_count' => $newCount,
            'is_active'             => $newCount < 10, // Désactiver après 10 échecs consécutifs
        ]);
    }
}
