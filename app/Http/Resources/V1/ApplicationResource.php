<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'job_offer_id'   => $this->job_offer_id,
            'cover_letter'   => $this->cover_letter,
            'proposed_rate'  => $this->proposed_rate ? (float) $this->proposed_rate : null,
            'status'         => $this->status,
            'decided_at'     => $this->decided_at?->toISOString(),

            // Motif de refus (visible seulement si rejeté)
            'rejection_reason' => $this->when(
                $this->status === 'rejected',
                $this->rejection_reason
            ),

            // Répétiteur (pour la vue parent)
            'tutor' => $this->when($this->relationLoaded('tutor'), fn () => [
                'id'           => $this->tutor->id,
                'name'         => $this->tutor->name,
                'city'         => $this->tutor->city,
                'avatar_url'   => $this->tutor->avatar_url,
                'hourly_rate'  => $this->tutor->hourly_rate,
                'is_verified'  => $this->tutor->is_verified,
                'tutor_profile'=> new TutorProfileResource(
                    $this->whenLoaded('tutor', fn () => $this->tutor->tutorProfile)
                ),
            ]),

            // Offre associée (pour la vue répétiteur)
            'job_offer' => new JobOfferResource($this->whenLoaded('jobOffer')),

            // Contrat généré si acceptée
            'contract_id' => $this->when(
                $this->status === 'accepted',
                fn () => $this->contract?->id
            ),

            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
