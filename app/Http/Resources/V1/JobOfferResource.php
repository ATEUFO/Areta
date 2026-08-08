<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Sérialise une offre d'emploi pour l'API Areta v1.
 * Inclut les informations parent et enfant si chargées.
 */
class JobOfferResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'subject'      => $this->subject,
            'objective'    => $this->objective,
            'frequency'    => $this->frequency,
            'school_level' => $this->school_level,

            // Localisation
            'location' => [
                'city'     => $this->city,
                'district' => $this->district,
                'lat'      => $this->lat ? (float) $this->lat : null,
                'lng'      => $this->lng ? (float) $this->lng : null,
            ],

            // Budget
            'budget' => [
                'min'     => $this->budget_min ? (float) $this->budget_min : null,
                'max'     => $this->budget_max ? (float) $this->budget_max : null,
                'display' => $this->budget_range, // accesseur formaté
            ],

            'status'       => $this->status,
            'views_count'  => $this->views_count,

            // Nombre de candidatures reçues
            'applications_count' => $this->whenCounted('applications'),

            // Parent (anonymisé — seulement prénom + ville)
            'parent' => $this->when($this->relationLoaded('parent'), [
                'id'   => $this->parent->id,
                'name' => $this->parent->name,
                'city' => $this->parent->city,
            ]),

            // Enfant concerné (si défini)
            'child' => $this->when(
                $this->child_id && $this->relationLoaded('child'),
                fn () => [
                    'school_level' => $this->child?->school_level,
                    'age'          => $this->child?->age,
                ]
            ),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
