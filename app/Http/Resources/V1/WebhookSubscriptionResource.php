<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WebhookSubscriptionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'url'                   => $this->url,
            'secret'                => $this->secret, // Clé secrète pour vérifier la signature X-Areta-Signature
            'events'                => $this->events ?? [],
            'is_active'             => $this->is_active,
            'failed_attempts_count' => $this->failed_attempts_count,
            'created_at'            => $this->created_at?->toISOString(),
            'updated_at'            => $this->updated_at?->toISOString(),
        ];
    }
}
