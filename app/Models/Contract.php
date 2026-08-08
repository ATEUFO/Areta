<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Contrat pédagogique Areta.
 *
 * Généré automatiquement lors de l'acceptation d'une candidature.
 * Relie le Parent, le Répétiteur, et optionnellement l'Enfant.
 * Toutes les séances (Sessions) sont rattachées au contrat.
 *
 * @property int $id
 * @property int $job_offer_id
 * @property int $application_id
 * @property int $parent_id
 * @property int $tutor_id
 * @property int|null $child_id
 * @property float|null $agreed_rate
 * @property string $status  active|paused|completed|terminated
 * @property Carbon|null $started_at
 * @property Carbon|null $ended_at
 * @property string|null $termination_reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read JobOffer $jobOffer
 * @property-read Application $application
 * @property-read User $parent
 * @property-read User $tutor
 * @property-read Child|null $child
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TutoringSession> $sessions
 */
class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_offer_id', 'application_id', 'parent_id', 'tutor_id', 'child_id',
        'agreed_rate', 'status', 'started_at', 'ended_at', 'termination_reason',
    ];

    protected function casts(): array
    {
        return [
            'agreed_rate' => 'decimal:2',
            'started_at'  => 'datetime',
            'ended_at'    => 'datetime',
        ];
    }

    // ──────────────────────────────────────────────────
    // Relations
    // ──────────────────────────────────────────────────

    public function jobOffer(): BelongsTo
    {
        return $this->belongsTo(JobOffer::class, 'job_offer_id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class, 'child_id');
    }

    /**
     * Toutes les séances de ce contrat.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(TutoringSession::class, 'contract_id');
    }

    /**
     * Séances planifiées (non encore effectuées).
     */
    public function plannedSessions(): HasMany
    {
        return $this->sessions()->where('status', 'planned');
    }

    /**
     * Séances terminées avec rapport soumis.
     */
    public function completedSessions(): HasMany
    {
        return $this->sessions()->where('status', 'completed');
    }

    // ──────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Vérifie si le répétiteur peut planifier une nouvelle séance.
     * Règle métier : impossible si la dernière séance terminée
     * n'a pas encore de rapport soumis.
     */
    public function canScheduleNextSession(): bool
    {
        $lastCompleted = $this->sessions()
            ->where('status', 'completed')
            ->latest('scheduled_at')
            ->first();

        if (! $lastCompleted) {
            return true; // Première séance — toujours autorisée
        }

        return $lastCompleted->report_submitted_at !== null;
    }

    /**
     * Termine le contrat.
     */
    public function terminate(string $reason = ''): void
    {
        $this->update([
            'status'               => 'terminated',
            'ended_at'             => now(),
            'termination_reason'   => $reason,
        ]);

        // Fermer l'offre d'emploi associée
        $this->jobOffer->update(['status' => 'closed']);
    }
}
