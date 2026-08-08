<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Sérialise un utilisateur Areta pour l'API v1.
 * Inclut conditionnellement le profil répétiteur si chargé en mémoire.
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'email'         => $this->email,
            'role'          => $this->role,
            'phone'         => $this->phone,
            'city'          => $this->city,
            'district'      => $this->district,
            'avatar_url'    => $this->avatar_url, // Accesseur sur User
            'bio'           => $this->bio,
            'is_verified'   => $this->is_verified,
            'hourly_rate'   => $this->hourly_rate,

            // Chargé seulement si eager-loaded (évite N+1)
            'tutor_profile' => new TutorProfileResource($this->whenLoaded('tutorProfile')),
            'children_count'=> $this->when(
                $this->role === 'parent',
                fn () => $this->children()->count()
            ),

            // Timestamps
            'created_at'    => $this->created_at?->toISOString(),
            'updated_at'    => $this->updated_at?->toISOString(),
        ];
    }
}
