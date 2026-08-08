<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Modèle Bilan IA (AI Evaluator).
 *
 * Contient le diagnostic automatisé généré par l'IA à partir des notes
 * et commentaires récents d'un élève.
 *
 * @property int $id
 * @property int $child_id
 * @property int|null $contract_id
 * @property array $lacunes_detectees
 * @property array $progression_estimee
 * @property array $plan_action
 * @property Carbon $generated_at
 * @property string $model_used
 * @property int $sessions_analyzed_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Child $child
 * @property-read Contract|null $contract
 */
class AiDiagnosis extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id',
        'contract_id',
        'lacunes_detectees',
        'progression_estimee',
        'plan_action',
        'generated_at',
        'model_used',
        'sessions_analyzed_count',
    ];

    protected function casts(): array
    {
        return [
            'lacunes_detectees'       => 'array',
            'progression_estimee'     => 'array',
            'plan_action'             => 'array',
            'generated_at'            => 'datetime',
            'sessions_analyzed_count' => 'integer',
        ];
    }

    // ──────────────────────────────────────────────────
    // Relations
    // ──────────────────────────────────────────────────

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class, 'child_id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }
}
