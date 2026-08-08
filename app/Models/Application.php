<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * Candidature d'un répétiteur à une offre d'emploi.
 *
 * Règle métier : l'acceptation d'une candidature déclenche
 * automatiquement la création d'un Contrat Pédagogique et
 * le rejet automatique de toutes les autres candidatures
 * pour la même offre.
 *
 * @property int $id
 * @property int $job_offer_id
 * @property int $tutor_id
 * @property string|null $cover_letter
 * @property float|null $proposed_rate
 * @property string $status  pending|accepted|rejected|withdrawn
 * @property string|null $rejection_reason
 * @property Carbon|null $decided_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read JobOffer $jobOffer
 * @property-read User $tutor
 * @property-read Contract|null $contract
 */
class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_offer_id', 'tutor_id', 'cover_letter',
        'proposed_rate', 'status', 'rejection_reason', 'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'proposed_rate' => 'decimal:2',
            'decided_at'    => 'datetime',
        ];
    }

    // ──────────────────────────────────────────────────
    // Relations
    // ──────────────────────────────────────────────────

    public function jobOffer(): BelongsTo
    {
        return $this->belongsTo(JobOffer::class, 'job_offer_id');
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function contract(): HasOne
    {
        return $this->hasOne(Contract::class, 'application_id');
    }

    // ──────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    /**
     * Accepte cette candidature et rejette toutes les autres sur la même offre.
     * Génère automatiquement le Contrat Pédagogique.
     *
     * @return Contract Le contrat nouvellement créé
     */
    public function accept(): Contract
    {
        // 1. Accepter cette candidature
        $this->update([
            'status'     => 'accepted',
            'decided_at' => now(),
        ]);

        // 2. Rejeter toutes les autres candidatures de l'offre
        Application::where('job_offer_id', $this->job_offer_id)
            ->where('id', '!=', $this->id)
            ->where('status', 'pending')
            ->update([
                'status'           => 'rejected',
                'rejection_reason' => 'Un autre répétiteur a été sélectionné.',
                'decided_at'       => now(),
            ]);

        // 3. Mettre à jour le statut de l'offre
        $this->jobOffer->update(['status' => 'in_progress']);

        // 4. Créer le Contrat Pédagogique
        $offer = $this->jobOffer;

        return Contract::create([
            'job_offer_id'   => $this->job_offer_id,
            'application_id' => $this->id,
            'parent_id'      => $offer->parent_id,
            'tutor_id'       => $this->tutor_id,
            'child_id'       => $offer->child_id,
            'agreed_rate'    => $this->proposed_rate ?? $offer->budget_max,
            'status'         => 'active',
            'started_at'     => now(),
        ]);
    }

    /**
     * Rejette cette candidature.
     */
    public function reject(string $reason = ''): void
    {
        $this->update([
            'status'           => 'rejected',
            'rejection_reason' => $reason,
            'decided_at'       => now(),
        ]);
    }
}
