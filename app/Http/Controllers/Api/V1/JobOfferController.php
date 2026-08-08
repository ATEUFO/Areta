<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreJobOfferRequest;
use App\Http\Resources\V1\JobOfferResource;
use App\Http\Traits\ApiResponse;
use App\Models\JobOffer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Gestion des offres d'emploi — Marketplace Areta v1.
 *
 * GET    /api/v1/job-offers              Liste paginée + filtres dynamiques
 * POST   /api/v1/job-offers              Créer une offre (parent uniquement)
 * GET    /api/v1/job-offers/{id}         Détail d'une offre
 * PUT    /api/v1/job-offers/{id}         Modifier (owner uniquement)
 * DELETE /api/v1/job-offers/{id}         Supprimer (owner uniquement)
 */
class JobOfferController extends Controller
{
    use ApiResponse;

    /**
     * Liste paginée des offres ouvertes avec filtres.
     *
     * Paramètres de requête supportés :
     *   ?city=Abidjan
     *   ?subject=Mathématiques
     *   ?budget_max=50000
     *   ?school_level=lycée
     *   ?per_page=15  (défaut: 15, max: 50)
     *   ?page=1
     */
    public function index(Request $request): JsonResponse
    {
        $query = JobOffer::query()
            ->open()
            ->with(['parent:id,name,city', 'child:id,school_level,age'])
            ->withCount('applications');

        // Filtres dynamiques
        if ($city = $request->query('city')) {
            $query->byCity((string) $city);
        }

        if ($subject = $request->query('subject')) {
            $query->bySubject((string) $subject);
        }

        if ($budgetMax = $request->query('budget_max')) {
            $query->byBudget((float) $budgetMax);
        }

        if ($level = $request->query('school_level')) {
            $query->bySchoolLevel((string) $level);
        }

        // Tri
        $sortBy  = in_array($request->query('sort'), ['created_at', 'budget_max', 'views_count'])
            ? $request->query('sort')
            : 'created_at';
        $sortDir = $request->query('order', 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy((string) $sortBy, $sortDir);

        // Pagination
        $perPage = min((int) $request->query('per_page', 15), 50);
        $paginator = $query->paginate($perPage);

        // Transformer avec Resource
        $paginator->through(fn ($offer) => new JobOfferResource($offer));

        return $this->paginated($paginator);
    }

    /**
     * Créer une nouvelle offre (parent uniquement).
     */
    public function store(StoreJobOfferRequest $request): JsonResponse
    {
        // Vérifier que child_id appartient bien au parent
        if ($request->child_id) {
            $child = $request->user()->children()->find($request->child_id);
            if (! $child) {
                return $this->error('FORBIDDEN', 'Cet enfant ne vous appartient pas.', [], 403);
            }
        }

        $offer = JobOffer::create([
            ...$request->validated(),
            'parent_id' => $request->user()->id,
        ]);

        return $this->created(
            new JobOfferResource($offer->load(['parent', 'child'])),
            'Offre publiée avec succès !'
        );
    }

    /**
     * Détail d'une offre — incrémente le compteur de vues.
     */
    public function show(JobOffer $jobOffer): JsonResponse
    {
        $jobOffer->incrementViews();
        $jobOffer->load(['parent', 'child', 'applications' => fn ($q) => $q->with('tutor')]);
        $jobOffer->loadCount('applications');

        return $this->success(
            new JobOfferResource($jobOffer),
            'Offre récupérée.'
        );
    }

    /**
     * Modifier une offre (owner uniquement, seulement si encore ouverte).
     */
    public function update(StoreJobOfferRequest $request, JobOffer $jobOffer): JsonResponse
    {
        if ($jobOffer->parent_id !== $request->user()->id) {
            return $this->forbidden('Vous ne pouvez modifier que vos propres offres.');
        }

        if (! $jobOffer->isOpen()) {
            return $this->error(
                'OFFER_NOT_EDITABLE',
                'Cette offre ne peut plus être modifiée (en cours ou fermée).',
                [],
                422
            );
        }

        $jobOffer->update($request->validated());

        return $this->success(
            new JobOfferResource($jobOffer->fresh()->load('parent', 'child')),
            'Offre mise à jour.'
        );
    }

    /**
     * Supprimer une offre (owner uniquement, seulement si aucun contrat actif).
     */
    public function destroy(Request $request, JobOffer $jobOffer): JsonResponse
    {
        if ($jobOffer->parent_id !== $request->user()->id) {
            return $this->forbidden('Vous ne pouvez supprimer que vos propres offres.');
        }

        if ($jobOffer->contract()->exists()) {
            return $this->error(
                'CONTRACT_EXISTS',
                'Impossible de supprimer une offre avec un contrat actif.',
                [],
                422
            );
        }

        $jobOffer->delete();

        return $this->success(null, 'Offre supprimée.');
    }
}
