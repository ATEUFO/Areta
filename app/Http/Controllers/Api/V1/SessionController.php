<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\SessionResource;
use App\Http\Traits\ApiResponse;
use App\Models\Contract;
use App\Models\TutoringSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Gestion des séances de répétition — Core Tracker API v1.
 *
 * GET  /api/v1/contracts/{contract}/sessions  Liste des séances d'un contrat
 * POST /api/v1/contracts/{contract}/sessions  Planifier une nouvelle séance
 * POST /api/v1/sessions/{session}/complete     Marquer comme terminée
 * POST /api/v1/sessions/{session}/report       Soumettre la note et le rapport pédagogique
 * POST /api/v1/sessions/{session}/cancel       Annuler une séance
 */
class SessionController extends Controller
{
    use ApiResponse;

    /**
     * Liste des séances d'un contrat.
     */
    public function index(Request $request, Contract $contract): JsonResponse
    {
        $user = $request->user();

        if ($contract->parent_id !== $user->id && $contract->tutor_id !== $user->id) {
            return $this->forbidden('Accès refusé.');
        }

        $sessions = $contract->sessions()
            ->latest('scheduled_at')
            ->get();

        return $this->success(
            SessionResource::collection($sessions),
            'Séances récupérées.'
        );
    }

    /**
     * Planifier une nouvelle séance pour un contrat.
     *
     * Règle métier bloquante : le système interdit la planification
     * si la dernière séance terminée n'a pas encore de rapport soumis.
     */
    public function store(Request $request, Contract $contract): JsonResponse
    {
        $user = $request->user();

        if ($contract->parent_id !== $user->id && $contract->tutor_id !== $user->id) {
            return $this->forbidden('Seules les parties au contrat peuvent planifier des séances.');
        }

        if (! $contract->isActive()) {
            return $this->error('CONTRACT_INACTIVE', 'Impossible de planifier sur un contrat inactif.', [], 422);
        }

        // RÈGLE MÉTIER CŒUR : blocage tant que le rapport précédent n'est pas rempli
        if (! $contract->canScheduleNextSession()) {
            return $this->error(
                'REPORT_REQUIRED',
                'Le rapport de la séance précédente doit être rempli avant de pouvoir planifier une nouvelle séance.',
                [],
                422
            );
        }

        $validated = $request->validate([
            'scheduled_at'     => ['required', 'date', 'after:now'],
            'duration_minutes' => ['nullable', 'integer', 'min:30', 'max:240'],
            'subject'          => ['nullable', 'string', 'max:100'],
        ]);

        $session = TutoringSession::create([
            'contract_id'      => $contract->id,
            'scheduled_at'     => $validated['scheduled_at'],
            'duration_minutes' => $validated['duration_minutes'] ?? 60,
            'subject'          => $validated['subject'] ?? $contract->jobOffer->subject,
            'status'           => 'planned',
        ]);

        return $this->created(
            new SessionResource($session),
            'Séance planifiée avec succès. Elle apparaît sur les deux tableaux de bord.'
        );
    }

    /**
     * Marquer une séance comme terminée (cours effectué).
     */
    public function complete(Request $request, TutoringSession $session): JsonResponse
    {
        $user = $request->user();

        if ($session->contract->tutor_id !== $user->id && $session->contract->parent_id !== $user->id) {
            return $this->forbidden('Accès refusé.');
        }

        if (! $session->isPlanned()) {
            return $this->error('INVALID_STATUS', 'Seule une séance planifiée peut être marquée comme terminée.', [], 422);
        }

        $session->update(['status' => 'completed']);

        return $this->success(
            new SessionResource($session),
            'Séance terminée. Veuillez saisir la note et le rapport pour débloquer la suite.'
        );
    }

    /**
     * Soumettre le rapport d'évaluation pédagogique (note sur 20 + texte).
     *
     * Conséquences système :
     * - Recalcule la moyenne générale de l'élève en temps réel.
     * - Incrémente le streak du répétiteur.
     * - Débloque la planification de la séance suivante.
     */
    public function report(Request $request, TutoringSession $session): JsonResponse
    {
        $user = $request->user();

        // Seul le répétiteur saisit le rapport
        if ($session->contract->tutor_id !== $user->id) {
            return $this->forbidden('Seul le répétiteur peut saisir le rapport d\'évaluation.');
        }

        $validated = $request->validate([
            'grade'       => ['required', 'integer', 'between:0,20'],
            'report_text' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        // Exécute la logique métier complète
        $session->submitReport(
            (int) $validated['grade'],
            (string) $validated['report_text']
        );

        return $this->success(
            new SessionResource($session->fresh(['contract.child'])),
            'Rapport enregistré ! La moyenne de l\'élève a été mise à jour en temps réel.'
        );
    }

    /**
     * Annuler une séance.
     */
    public function cancel(Request $request, TutoringSession $session): JsonResponse
    {
        $user = $request->user();

        if ($session->contract->parent_id !== $user->id && $session->contract->tutor_id !== $user->id) {
            return $this->forbidden('Accès refusé.');
        }

        if (! $session->isPlanned()) {
            return $this->error('CANNOT_CANCEL', 'Seule une séance planifiée peut être annulée.', [], 422);
        }

        $session->update(['status' => 'cancelled']);

        return $this->success(
            new SessionResource($session),
            'Séance annulée.'
        );
    }
}
