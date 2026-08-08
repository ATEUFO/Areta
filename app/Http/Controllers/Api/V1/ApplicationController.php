<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ApplicationResource;
use App\Http\Resources\V1\ContractResource;
use App\Http\Traits\ApiResponse;
use App\Models\Application;
use App\Models\JobOffer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Gestion des candidatures — Marketplace Areta v1.
 *
 * POST /api/v1/job-offers/{id}/apply     → Postuler (répétiteur)
 * GET  /api/v1/applications              → Mes candidatures
 * POST /api/v1/applications/{id}/accept  → Accepter (parent) → génère contrat
 * POST /api/v1/applications/{id}/reject  → Rejeter (parent)
 * POST /api/v1/applications/{id}/withdraw → Retirer (répétiteur)
 */
class ApplicationController extends Controller
{
    use ApiResponse;

    /**
     * Postuler à une offre (répétiteur uniquement).
     */
    public function store(Request $request, JobOffer $jobOffer): JsonResponse
    {
        // Vérifications préalables
        if (! $request->user()->isTutor()) {
            return $this->forbidden('Seul un répétiteur peut postuler à une offre.');
        }

        if (! $jobOffer->isOpen()) {
            return $this->error('OFFER_CLOSED', 'Cette offre n\'est plus ouverte.', [], 422);
        }

        // Vérifier qu'il n'a pas déjà postulé
        $exists = Application::where('job_offer_id', $jobOffer->id)
            ->where('tutor_id', $request->user()->id)
            ->exists();

        if ($exists) {
            return $this->error('ALREADY_APPLIED', 'Vous avez déjà postulé à cette offre.', [], 422);
        }

        // Empêcher un répétiteur de postuler à sa propre offre
        if ($jobOffer->parent_id === $request->user()->id) {
            return $this->forbidden('Vous ne pouvez pas postuler à votre propre offre.');
        }

        $validated = $request->validate([
            'cover_letter'  => ['nullable', 'string', 'max:2000'],
            'proposed_rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        $application = Application::create([
            ...$validated,
            'job_offer_id' => $jobOffer->id,
            'tutor_id'     => $request->user()->id,
            'status'       => 'pending',
        ]);

        $application->load('tutor', 'jobOffer.parent');

        return $this->created(
            new ApplicationResource($application),
            'Candidature envoyée ! Le parent sera notifié.'
        );
    }

    /**
     * Liste des candidatures de l'utilisateur courant.
     * - Parent : candidatures reçues sur ses offres
     * - Répétiteur : ses propres candidatures
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isParent()) {
            $applications = Application::whereHas('jobOffer', fn ($q) => $q->where('parent_id', $user->id))
                ->with(['tutor.tutorProfile', 'jobOffer'])
                ->latest()
                ->paginate(20);
        } else {
            $applications = Application::where('tutor_id', $user->id)
                ->with(['jobOffer.parent'])
                ->latest()
                ->paginate(20);
        }

        $applications->through(fn ($app) => new ApplicationResource($app));

        return $this->paginated($applications);
    }

    /**
     * Accepter une candidature (parent uniquement).
     * Génère automatiquement un Contrat Pédagogique.
     */
    public function accept(Request $request, Application $application): JsonResponse
    {
        $user = $request->user();

        // Vérifier que le parent est propriétaire de l'offre
        if ($application->jobOffer->parent_id !== $user->id) {
            return $this->forbidden('Vous n\'êtes pas autorisé à accepter cette candidature.');
        }

        if (! $application->isPending()) {
            return $this->error(
                'APPLICATION_NOT_PENDING',
                'Cette candidature n\'est plus en attente.',
                [],
                422
            );
        }

        // Transaction : accepter + rejeter les autres + créer le contrat
        $contract = DB::transaction(fn () => $application->accept());

        return $this->success([
            'application' => new ApplicationResource($application->fresh()->load('tutor')),
            'contract'    => new ContractResource($contract->load('parent', 'tutor', 'child', 'jobOffer')),
        ], '🎉 Répétiteur sélectionné ! Le Contrat Pédagogique a été créé.');
    }

    /**
     * Rejeter une candidature (parent uniquement).
     */
    public function reject(Request $request, Application $application): JsonResponse
    {
        if ($application->jobOffer->parent_id !== $request->user()->id) {
            return $this->forbidden('Vous n\'êtes pas autorisé à rejeter cette candidature.');
        }

        if (! $application->isPending()) {
            return $this->error('APPLICATION_NOT_PENDING', 'Cette candidature n\'est plus en attente.', [], 422);
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $application->reject($validated['reason'] ?? '');

        return $this->success(
            new ApplicationResource($application->fresh()),
            'Candidature déclinée.'
        );
    }

    /**
     * Retirer sa candidature (répétiteur uniquement).
     */
    public function withdraw(Request $request, Application $application): JsonResponse
    {
        if ($application->tutor_id !== $request->user()->id) {
            return $this->forbidden('Vous ne pouvez retirer que vos propres candidatures.');
        }

        if (! $application->isPending()) {
            return $this->error('APPLICATION_NOT_PENDING', 'Impossible de retirer une candidature déjà traitée.', [], 422);
        }

        $application->update(['status' => 'withdrawn', 'decided_at' => now()]);

        return $this->success(null, 'Candidature retirée.');
    }
}
