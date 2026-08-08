<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * Offre d'emploi publiée par un parent sur la marketplace Areta.
 *
 * Règle d'indépendance : une offre est liée à un Parent, pas
 * obligatoirement à un Enfant. Cela permet à un oncle ou tuteur
 * temporaire de chercher un répétiteur librement.
 *
 * @property int $id
 * @property int $parent_id
 * @property int|null $child_id
 * @property string $title
 * @property string $subject
 * @property string|null $objective
 * @property string $frequency
 * @property string|null $school_level
 * @property string $city
 * @property string|null $district
 * @property float|null $lat
 * @property float|null $lng
 * @property float|null $budget_min
 * @property float|null $budget_max
 * @property string $status  open|in_progress|closed
 * @property int $views_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $parent
 * @property-read Child|null $child
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Application> $applications
 * @property-read Application|null $acceptedApplication
 * @property-read Contract|null $contract
 */
class JobOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id', 'child_id', 'title', 'subject', 'objective',
        'frequency', 'school_level', 'city', 'district', 'lat', 'lng',
        'budget_min', 'budget_max', 'status', 'views_count',
    ];

    protected function casts(): array
    {
        return [
            'lat'        => 'decimal:7',
            'lng'        => 'decimal:7',
            'budget_min' => 'decimal:2',
            'budget_max' => 'decimal:2',
            'views_count'=> 'integer',
        ];
    }

    // ──────────────────────────────────────────────────
    // Relations
    // ──────────────────────────────────────────────────

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class, 'child_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'job_offer_id');
    }

    public function acceptedApplication(): HasOne
    {
        return $this->hasOne(Application::class, 'job_offer_id')
                    ->where('status', 'accepted');
    }

    public function contract(): HasOne
    {
        return $this->hasOne(Contract::class, 'job_offer_id');
    }

    // ──────────────────────────────────────────────────
    // Scopes (filtres marketplace)
    // ──────────────────────────────────────────────────

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    public function scopeByCity(Builder $query, string $city): Builder
    {
        return $query->where('city', 'like', "%{$city}%");
    }

    public function scopeBySubject(Builder $query, string $subject): Builder
    {
        return $query->where('subject', 'like', "%{$subject}%");
    }

    public function scopeByBudget(Builder $query, ?float $max): Builder
    {
        return $query->when($max, fn ($q) => $q->where('budget_max', '<=', $max));
    }

    public function scopeBySchoolLevel(Builder $query, string $level): Builder
    {
        return $query->where('school_level', $level);
    }

    // ──────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Fourchette de budget formatée pour l'affichage.
     */
    public function getBudgetRangeAttribute(): ?string
    {
        if ($this->budget_min && $this->budget_max) {
            return "{$this->budget_min} – {$this->budget_max} FCFA/h";
        }
        if ($this->budget_max) {
            return "Jusqu'à {$this->budget_max} FCFA/h";
        }

        return null;
    }
}
