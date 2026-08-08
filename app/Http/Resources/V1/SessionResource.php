<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'contract_id'         => $this->contract_id,
            'scheduled_at'        => $this->scheduled_at?->toISOString(),
            'duration_minutes'    => $this->duration_minutes,
            'subject'             => $this->subject,
            'status'              => $this->status,
            'grade'               => $this->grade,
            'report_text'         => $this->report_text,
            'report_submitted_at' => $this->report_submitted_at?->toISOString(),
            'is_pending_report'   => $this->isPendingReport(),

            // Résumé du contrat si chargé
            'contract' => $this->when($this->relationLoaded('contract'), fn () => [
                'id'          => $this->contract->id,
                'parent_name' => $this->contract->parent?->name,
                'tutor_name'  => $this->contract->tutor?->name,
                'child_name'  => $this->contract->child?->full_name,
            ]),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
