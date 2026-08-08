<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'status'      => $this->status,
            'agreed_rate' => $this->agreed_rate ? (float) $this->agreed_rate : null,
            'started_at'  => $this->started_at?->toISOString(),
            'ended_at'    => $this->ended_at?->toISOString(),

            // Offre d'origine (résumé)
            'job_offer' => $this->when($this->relationLoaded('jobOffer'), fn () => [
                'id'      => $this->jobOffer->id,
                'title'   => $this->jobOffer->title,
                'subject' => $this->jobOffer->subject,
            ]),

            // Parent
            'parent' => $this->when($this->relationLoaded('parent'), fn () => [
                'id'   => $this->parent->id,
                'name' => $this->parent->name,
                'city' => $this->parent->city,
            ]),

            // Répétiteur
            'tutor' => $this->when($this->relationLoaded('tutor'), fn () => [
                'id'         => $this->tutor->id,
                'name'       => $this->tutor->name,
                'avatar_url' => $this->tutor->avatar_url,
            ]),

            // Enfant concerné
            'child' => new ChildResource($this->whenLoaded('child')),

            // Stats séances
            'sessions_stats' => $this->when($this->relationLoaded('sessions'), fn () => [
                'total'     => $this->sessions->count(),
                'completed' => $this->sessions->where('status', 'completed')->count(),
                'planned'   => $this->sessions->where('status', 'planned')->count(),
                'missed'    => $this->sessions->where('status', 'missed')->count(),
            ]),

            // Règle métier : peut-on planifier la prochaine séance ?
            'can_schedule_next' => $this->canScheduleNextSession(),

            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
