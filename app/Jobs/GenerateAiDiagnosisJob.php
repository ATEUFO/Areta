<?php

namespace App\Jobs;

use App\Models\Child;
use App\Models\Contract;
use App\Services\AiEvaluatorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job asynchrone placé dans la file d'attente (Queue)
 * pour générer un diagnostic IA sans bloquer la requête HTTP.
 */
class GenerateAiDiagnosisJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Nombre de tentatives en cas d'échec API.
     */
    public int $tries = 3;

    /**
     * Temps d'attente avant réessai (secondes).
     */
    public int $backoff = 10;

    public function __construct(
        public Child $child,
        public ?Contract $contract = null
    ) {}

    public function handle(AiEvaluatorService $aiService): void
    {
        Log::info("Début du job de diagnostic IA pour l'enfant #{$this->child->id}");

        $diagnosis = $aiService->generateForChild($this->child, $this->contract);

        Log::info("Diagnostic IA #{$diagnosis->id} généré avec succès pour l'enfant #{$this->child->id} (Modèle: {$diagnosis->model_used})");
    }
}
