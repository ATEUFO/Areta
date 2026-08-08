<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Modèle Abonnement Webhook.
 *
 * @property int $id
 * @property int $user_id
 * @property string $url
 * @property string $secret
 * @property array $events
 * @property bool $is_active
 * @property int $failed_attempts_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 */
class WebhookSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'url',
        'secret',
        'events',
        'is_active',
        'failed_attempts_count',
    ];

    protected function casts(): array
    {
        return [
            'events'                => 'array',
            'is_active'             => 'boolean',
            'failed_attempts_count' => 'integer',
        ];
    }

    /**
     * Événements système supportés.
     */
    public const SUPPORTED_EVENTS = [
        'application.received'      => 'Candidature reçue sur une offre',
        'application.accepted'      => 'Candidature acceptée (contrat créé)',
        'application.rejected'      => 'Candidature refusée',
        'session.planned'           => 'Nouvelle séance planifiée',
        'session.report.submitted'  => 'Rapport d\'évaluation et note soumis',
        'ai.diagnosis.ready'        => 'Nouveau bilan IA généré',
        'contract.terminated'       => 'Contrat pédagogique résilié',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Génère un secret aléatoire sécurisé (32 octets hex).
     */
    public static function generateSecret(): string
    {
        return 'whsec_' . Str::random(32);
    }

    /**
     * Calcule la signature HMAC-SHA256 pour un payload donné.
     */
    public function signPayload(string $payload): string
    {
        return 'sha256=' . hash_hmac('sha256', $payload, $this->secret);
    }
}
