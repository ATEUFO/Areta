<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Profil étendu d'un répétiteur.
 * Séparé du modèle User pour un chargement conditionnel efficace.
 *
 * @property int $id
 * @property int $user_id
 * @property array|null $specialties
 * @property int $experience_years
 * @property string|null $diploma
 * @property bool $is_available
 * @property float $rating_avg
 * @property int $total_sessions
 * @property int $total_reviews
 * @property int $current_streak
 * @property int $best_streak
 * @property string|null $city
 * @property string|null $district
 * @property float|null $lat
 * @property float|null $lng
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 */
class TutorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'specialties',
        'experience_years',
        'diploma',
        'is_available',
        'rating_avg',
        'total_sessions',
        'total_reviews',
        'current_streak',
        'best_streak',
        'city',
        'district',
        'lat',
        'lng',
    ];

    protected function casts(): array
    {
        return [
            'specialties'      => 'array',
            'is_available'     => 'boolean',
            'rating_avg'       => 'decimal:2',
            'lat'              => 'decimal:7',
            'lng'              => 'decimal:7',
            'experience_years' => 'integer',
            'total_sessions'   => 'integer',
            'total_reviews'    => 'integer',
            'current_streak'   => 'integer',
            'best_streak'      => 'integer',
        ];
    }

    // ──────────────────────────────────────────────────
    // Relations
    // ──────────────────────────────────────────────────

    /**
     * L'utilisateur (répétiteur) associé à ce profil.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ──────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────

    /**
     * Incrémente le streak courant et met à jour le record.
     */
    public function incrementStreak(): void
    {
        $newStreak = $this->current_streak + 1;
        $this->update([
            'current_streak' => $newStreak,
            'best_streak'    => max($this->best_streak, $newStreak),
            'total_sessions' => $this->total_sessions + 1,
        ]);
    }

    /**
     * Remet le streak à zéro (rapport non soumis à temps).
     */
    public function resetStreak(): void
    {
        $this->update(['current_streak' => 0]);
    }

    /**
     * Recalcule la note moyenne à partir des contrats.
     * Appelé après chaque nouvelle évaluation parent.
     */
    public function recalculateRating(): void
    {
        // Cette méthode sera implémentée lors de la Phase 3 (reviews)
        // pour l'instant elle est un placeholder documenté
    }
}
