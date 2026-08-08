<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\AiDiagnosisResource;
use App\Http\Traits\ApiResponse;
use App\Jobs\GenerateAiDiagnosisJob;
use App\Models\AiDiagnosis;
use App\Models\Child;
use App\Services\AiEvaluatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoints pour l'Évaluateur Pédagogique IA (AI Evaluator) — Areta API v1.
 *
 * GET  /api/v1/children/{child}/diagnoses   Historique des bilans IA de l'enfant
 * POST /api/v1/children/{child}/diagnoses   Générer un nouveau bilan (→ 202 Accepted, async job)
 * GET  /api/v1/diagnoses/{diagnosis}       Détail d'un bilan IA spécifique
 */
class AiDiagnosisController extends Controller
{
    use ApiResponse;

    /**
     * Historique des bilans IA d'un enfant.
     */
    public function index(Request $request, Child $child): JsonResponse
    {
        $user = $request->user();

        // Vérifier que le parent possède l'enfant OU que le répétiteur a un contrat actif pour cet enfant
        $hasAccess = ($child->parent_id === $user->id) ||
            $user->tutorContracts()->where('child_id', $child->id)->where('status', 'active')->exists();

        if (! $hasAccess) {
            return $this->forbidden('Accès refusé aux bilans de cet enfant.');
        }

        $diagnoses = $child->aiDiagnoses()
            ->latest('generated_at')
            ->paginate(15);

        $diagnoses->through(fn ($d) => new AiDiagnosisResource($d));

        return $this->paginated($diagnoses);
    }

    /**
     * Déclencher la génération d'un nouveau bilan IA.
     *
     * Retourne un HTTP 202 Accepted : le job est placé en file d'attente
     * pour ne pas bloquer l'interface. En option, si `sync=1` est passé en query,
     * la génération s'exécute immédiatement en synchrone.
     */
    public function generate(Request $request, Child $child, AiEvaluatorService $aiService): JsonResponse
    {
        $user = $request->user();

        $hasAccess = ($child->parent_id === $user->id) ||
            $user->tutorContracts()->where('child_id', $child->id)->where('status', 'active')->exists();

        if (! $hasAccess) {
            return $this->forbidden('Accès refusé.');
        }

        // Option d'exécution synchrone pour les tests / dev
        if ($request->boolean('sync')) {
            $diagnosis = $aiService->generateForChild($child);

            return $this->created(
                new AiDiagnosisResource($diagnosis),
                'Bilan IA généré avec succès !'
            );
        }

        // Sinon dispatch asynchrone dans la Queue
        GenerateAiDiagnosisJob::dispatch($child);

        return $this->accepted(
            'L\'IA analyse actuellement les performances de l\'élève. Le bilan sera disponible dans quelques secondes.',
            ['child_id' => $child->id]
        );
    }

    /**
     * Détail d'un bilan IA spécifique.
     */
    public function show(Request $request, AiDiagnosis $diagnosis): JsonResponse
    {
        $user = $request->user();

        $child = $diagnosis->child;
        $hasAccess = ($child->parent_id === $user->id) ||
            $user->tutorContracts()->where('child_id', $child->id)->where('status', 'active')->exists();

        if (! $hasAccess) {
            return $this->forbidden('Accès refusé.');
        }

        return $this->success(
            new AiDiagnosisResource($diagnosis),
            'Bilan IA récupéré.'
        );
    }
}
