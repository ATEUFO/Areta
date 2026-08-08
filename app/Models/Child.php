<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Profil enfant (Shadow Profile).
 * Les enfants n'ont pas de compte — leur profil est géré par le Parent.
 * Ils peuvent être rattachés à une Offre et à un Contrat.
 *
 * @property int $id
 * @property int $parent_id
 * @property string $first_name
 * @property string $last_name
 * @property int|null $age
 * @property string|null $school_level
 * @property float|null $grade_average
 * @property string|null $personal_notes
 * @property string|null $avatar
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AiDiagnosis> $aiDiagnoses
 */
class Child extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'first_name',
        'last_name',
        'age',
        'school_level',
        'grade_average',
        'personal_notes',
        'avatar',
    ];

    protected function casts(): array
    {
        return [
            'age'           => 'integer',
            'grade_average' => 'decimal:2',
        ];
    }

    // ──────────────────────────────────────────────────
    // Relations
    // ──────────────────────────────────────────────────

    /**
     * Le parent propriétaire de ce profil.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    /**
     * Offres d'emploi qui mentionnent cet enfant.
     */
    public function jobOffers(): HasMany
    {
        return $this->hasMany(JobOffer::class, 'child_id');
    }

    /**
     * Contrats pédagogiques liés à cet enfant.
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'child_id');
    }

    /**
     * Bilans IA générés pour cet enfant.
     */
    public function aiDiagnoses(): HasMany
    {
        return $this->hasMany(AiDiagnosis::class, 'child_id');
    }

    // ──────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────

    /**
     * Nom complet de l'enfant.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * URL de l'avatar ou null.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? asset('storage/' . $this->avatar) : null;
    }

    /**
     * Recalcule et sauvegarde la moyenne de l'enfant
     * à partir de toutes les séances avec une note soumise.
     */
    public function recalculateGradeAverage(): void
    {
        $avg = TutoringSession::whereHas('contract', function ($q) {
                $q->where('child_id', $this->id);
            })
            ->whereNotNull('grade')
            ->avg('grade');

        $this->update(['grade_average' => $avg ? round($avg, 2) : null]);
    }
}
