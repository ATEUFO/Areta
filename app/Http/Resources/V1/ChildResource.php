<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Sérialise un profil enfant pour l'API v1.
 * Les données personnelles (personal_notes) ne sont visibles
 * que par le parent propriétaire — la restriction est faite
 * au niveau du controller via Policy.
 */
class ChildResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'parent_id'     => $this->parent_id,
            'first_name'    => $this->first_name,
            'last_name'     => $this->last_name,
            'full_name'     => $this->full_name, // Accesseur
            'age'           => $this->age,
            'school_level'  => $this->school_level,
            'grade_average' => $this->grade_average ? (float) $this->grade_average : null,
            'avatar_url'    => $this->avatar_url,

            // Chargé seulement si explicitly requested (confidentialité)
            'personal_notes' => $this->when(
                $request->user()?->id === $this->parent_id,
                $this->personal_notes
            ),

            // Dernier bilan IA (si chargé)
            'latest_diagnosis' => new AiDiagnosisResource(
                $this->whenLoaded('aiDiagnoses', fn () => $this->aiDiagnoses->first())
            ),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
