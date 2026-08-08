<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Concerns\HasTeams;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Modèle utilisateur Areta.
 * Un utilisateur est soit un Parent (donneur d'ordre) soit un Répétiteur (prestataire).
 * Les enfants ne possèdent pas de compte — ils sont des "Shadow Profiles" gérés par le Parent.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $role  'parent' | 'tutor'
 * @property string|null $phone
 * @property string|null $city
 * @property string|null $district
 * @property string|null $avatar
 * @property string|null $bio
 * @property bool $is_verified
 * @property float|null $hourly_rate
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property int|null $current_team_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Team|null $currentTeam
 * @property-read Collection<int, Team> $ownedTeams
 * @property-read Collection<int, Membership> $teamMemberships
 * @property-read Collection<int, Team> $teams
 * @property-read Collection<int, Child> $children
 * @property-read TutorProfile|null $tutorProfile
 * @property-read Collection<int, JobOffer> $jobOffers
 */
#[Fillable([
    'name', 'email', 'password', 'current_team_id',
    'role', 'phone', 'city', 'district', 'avatar', 'bio',
    'is_verified', 'hourly_rate',
])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasTeams, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Cast des attributs.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'         => 'datetime',
            'password'                  => 'hashed',
            'two_factor_confirmed_at'   => 'datetime',
            'is_verified'               => 'boolean',
            'hourly_rate'               => 'decimal:2',
        ];
    }

    // ──────────────────────────────────────────────────
    // Relations — Espace Parent
    // ──────────────────────────────────────────────────

    /**
     * Les profils enfants gérés par ce parent (Shadow Profiles).
     */
    public function children(): HasMany
    {
        return $this->hasMany(Child::class, 'parent_id');
    }

    /**
     * Les offres d'emploi publiées par ce parent.
     */
    public function jobOffers(): HasMany
    {
        return $this->hasMany(JobOffer::class, 'parent_id');
    }

    /**
     * Les contrats actifs en tant que parent.
     */
    public function parentContracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'parent_id');
    }

    // ──────────────────────────────────────────────────
    // Relations — Espace Répétiteur
    // ──────────────────────────────────────────────────

    /**
     * Le profil étendu du répétiteur (spécialités, rating, streak…).
     */
    public function tutorProfile(): HasOne
    {
        return $this->hasOne(TutorProfile::class, 'user_id');
    }

    /**
     * Les candidatures soumises par ce répétiteur.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'tutor_id');
    }

    /**
     * Les contrats actifs en tant que répétiteur.
     */
    public function tutorContracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'tutor_id');
    }

    /**
     * Les abonnements webhooks de cet utilisateur.
     */
    public function webhookSubscriptions(): HasMany
    {
        return $this->hasMany(WebhookSubscription::class, 'user_id');
    }

    // ──────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────

    public function isParent(): bool
    {
        return $this->role === 'parent';
    }

    public function isTutor(): bool
    {
        return $this->role === 'tutor';
    }

    /**
     * URL publique de l'avatar.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : null;
    }
}
