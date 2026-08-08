<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Sérialise un profil répétiteur pour l'API v1.
 */
class TutorProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'specialties'      => $this->specialties ?? [],
            'experience_years' => $this->experience_years,
            'diploma'          => $this->diploma,
            'is_available'     => $this->is_available,
            'rating_avg'       => (float) $this->rating_avg,
            'total_sessions'   => $this->total_sessions,
            'total_reviews'    => $this->total_reviews,
            'streak'           => [
                'current' => $this->current_streak,
                'best'    => $this->best_streak,
            ],
            'location' => [
                'city'     => $this->city,
                'district' => $this->district,
                'lat'      => $this->lat ? (float) $this->lat : null,
                'lng'      => $this->lng ? (float) $this->lng : null,
            ],
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
