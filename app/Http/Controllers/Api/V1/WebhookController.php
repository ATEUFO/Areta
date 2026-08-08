<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\WebhookSubscriptionResource;
use App\Http\Traits\ApiResponse;
use App\Models\WebhookSubscription;
use App\Services\WebhookDispatcherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Endpoints de gestion des Webhooks — Areta API v1.
 *
 * GET    /api/v1/webhooks           Lister les abonnements webhooks de l'utilisateur
 * POST   /api/v1/webhooks           Créer un nouvel abonnement
 * GET    /api/v1/webhooks/{id}      Détail d'un abonnement
 * DELETE /api/v1/webhooks/{id}      Supprimer un abonnement
 * POST   /api/v1/webhooks/{id}/test Envoyer un ping de test
 */
class WebhookController extends Controller
{
    use ApiResponse;

    /**
     * Liste des abonnements webhooks du compte.
     */
    public function index(Request $request): JsonResponse
    {
        $subscriptions = $request->user()->webhookSubscriptions ?? WebhookSubscription::where('user_id', $request->user()->id)->get();

        return $this->success(
            WebhookSubscriptionResource::collection($subscriptions),
            'Abonnements webhooks récupérés.'
        );
    }

    /**
     * Créer un abonnement webhook.
     */
    public function store(Request $request): JsonResponse
    {
        $supportedEvents = array_keys(WebhookSubscription::SUPPORTED_EVENTS);
        $supportedEvents[] = '*';

        $validated = $request->validate([
            'url'      => ['required', 'url', 'max:500'],
            'events'   => ['required', 'array', 'min:1'],
            'events.*' => ['string', Rule::in($supportedEvents)],
        ]);

        $subscription = WebhookSubscription::create([
            'user_id'   => $request->user()->id,
            'url'       => $validated['url'],
            'secret'    => WebhookSubscription::generateSecret(),
            'events'    => $validated['events'],
            'is_active' => true,
        ]);

        return $this->created(
            new WebhookSubscriptionResource($subscription),
            'Abonnement webhook créé avec succès. Conservez la clé secrète pour vérifier les signatures.'
        );
    }

    /**
     * Détail d'un abonnement.
     */
    public function show(Request $request, WebhookSubscription $webhook): JsonResponse
    {
        if ($webhook->user_id !== $request->user()->id) {
            return $this->forbidden('Accès refusé.');
        }

        return $this->success(
            new WebhookSubscriptionResource($webhook),
            'Abonnement récupéré.'
        );
    }

    /**
     * Supprimer un abonnement.
     */
    public function destroy(Request $request, WebhookSubscription $webhook): JsonResponse
    {
        if ($webhook->user_id !== $request->user()->id) {
            return $this->forbidden('Accès refusé.');
        }

        $webhook->delete();

        return $this->success(null, 'Abonnement webhook supprimé.');
    }

    /**
     * Tester l'envoi d'un webhook (ping de vérification).
     */
    public function test(Request $request, WebhookSubscription $webhook, WebhookDispatcherService $dispatcher): JsonResponse
    {
        if ($webhook->user_id !== $request->user()->id) {
            return $this->forbidden('Accès refusé.');
        }

        $success = $dispatcher->sendWebhook($webhook, 'ping', [
            'message' => 'Ceci est un événement de test d\'Areta API.',
            'ping'    => 'pong',
        ]);

        if (! $success) {
            return $this->error(
                'WEBHOOK_DELIVERY_FAILED',
                "L'envoi du ping à {$webhook->url} a échoué. Vérifiez que votre serveur répond HTTP 200.",
                [],
                422
            );
        }

        return $this->success([
            'delivered' => true,
            'url'       => $webhook->url,
        ], 'Ping webhook envoyé et reçu avec succès (HTTP 200).');
    }
}
