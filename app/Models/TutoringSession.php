<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Modèle pour une séance de répétition (TutoringSession).
 *
 * Règle métier : tant qu'une séance terminée n'a pas de rapport soumis,
 * le système bloque la planification de la séance suivante pour ce contrat.
 *
 * @property int $id
 * @property int $contract_id
 * @property Carbon $scheduled_at
 * @property int $duration_minutes
 * @property string $subject
 * @property string $status  planned|completed|missed|cancelled
 * @property int|null $grade
 * @property string|null $report_text
 * @property Carbon|null $report_submitted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Contract $contract
 */
class TutoringSession extends Model
{
    use HasFactory;

    protected $table = 'tutoring_sessions';

    protected $fillable = [
        'contract_id',
        'scheduled_at',
        'duration_minutes',
        'subject',
        'status',
        'grade',
        'report_text',
        'report_submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at'        => 'datetime',
            'duration_minutes'    => 'integer',
            'grade'               => 'integer',
            'report_submitted_at' => 'datetime',
        ];
    }

    // ──────────────────────────────────────────────────
    // Relations
    // ──────────────────────────────────────────────────

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    // ──────────────────────────────────────────────────
    // Helpers & Logique Métier
    // ──────────────────────────────────────────────────

    public function isPlanned(): bool
    {
        return $this->status === 'planned';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isReportSubmitted(): bool
    {
        return $this->report_submitted_at !== null;
    }

    public function isPendingReport(): bool
    {
        return $this->status === 'completed' && ! $this->isReportSubmitted();
    }

    /**
     * Soumet le rapport d'évaluation pour la séance.
     * Recalcule la moyenne de l'enfant et incrémente le streak du répétiteur.
     */
    public function submitReport(int $grade, string $reportText): void
    {
        $this->update([
            'status'              => 'completed',
            'grade'               => $grade,
            'report_text'         => $reportText,
            'report_submitted_at' => now(),
        ]);

        // 1. Recalculer la moyenne de l'enfant lié au contrat
        if ($this->contract->child) {
            $this->contract->child->recalculateGradeAverage();
        }

        // 2. Mettre à jour le streak du répétiteur s'il a soumis le rapport
        if ($this->contract->tutor && $this->contract->tutor->tutorProfile) {
            $this->contract->tutor->tutorProfile->incrementStreak();
        }
    }
}
