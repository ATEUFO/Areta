<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiDiagnosisResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                      => $this->id,
            'child_id'                => $this->child_id,
            'contract_id'             => $this->contract_id,
            'lacunes_detectees'       => $this->lacunes_detectees ?? [],
            'progression_estimee'     => $this->progression_estimee ?? [],
            'plan_action'             => $this->plan_action ?? [],
            'generated_at'            => $this->generated_at?->toISOString(),
            'model_used'              => $this->model_used,
            'sessions_analyzed_count' => $this->sessions_analyzed_count,
            'created_at'              => $this->created_at?->toISOString(),
        ];
    }
}
