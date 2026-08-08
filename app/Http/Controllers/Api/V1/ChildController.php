<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ChildResource;
use App\Http\Traits\ApiResponse;
use App\Models\Child;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Gestion des profils enfants (Shadow Profiles) — Areta API v1.
 *
 * GET    /api/v1/children          Liste des enfants du parent
 * POST   /api/v1/children          Ajouter un profil enfant
 * GET    /api/v1/children/{id}     Détail d'un enfant
 * PUT    /api/v1/children/{id}     Modifier un profil enfant
 * DELETE /api/v1/children/{id}     Supprimer un profil enfant
 */
class ChildController extends Controller
{
    use ApiResponse;

    /**
     * Liste des enfants du parent connecté.
     */
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->isParent()) {
            return $this->forbidden('Seul un parent peut gérer des profils enfants.');
        }

        $children = $request->user()->children()
            ->with(['aiDiagnoses' => fn ($q) => $q->latest()->limit(1)])
            ->get();

        return $this->success(
            ChildResource::collection($children),
            'Enfants récupérés.'
        );
    }

    /**
     * Ajouter un nouvel enfant (Shadow Profile).
     */
    public function store(Request $request): JsonResponse
    {
        if (! $request->user()->isParent()) {
            return $this->forbidden('Seul un parent peut ajouter un profil enfant.');
        }

        $validated = $request->validate([
            'first_name'     => ['required', 'string', 'max:100'],
            'last_name'      => ['required', 'string', 'max:100'],
            'age'            => ['nullable', 'integer', 'min:3', 'max:25'],
            'school_level'   => ['nullable', 'in:maternelle,primaire,collège,lycée,supérieur'],
            'personal_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $child = Child::create([
            ...$validated,
            'parent_id' => $request->user()->id,
        ]);

        return $this->created(
            new ChildResource($child),
            'Profil enfant créé avec succès.'
        );
    }

    /**
     * Détail d'un profil enfant.
     */
    public function show(Request $request, Child $child): JsonResponse
    {
        if ($child->parent_id !== $request->user()->id) {
            return $this->forbidden('Cet enfant ne vous appartient pas.');
        }

        $child->load(['aiDiagnoses' => fn ($q) => $q->latest()]);

        return $this->success(
            new ChildResource($child),
            'Profil enfant récupéré.'
        );
    }

    /**
     * Modifier un profil enfant.
     */
    public function update(Request $request, Child $child): JsonResponse
    {
        if ($child->parent_id !== $request->user()->id) {
            return $this->forbidden('Cet enfant ne vous appartient pas.');
        }

        $validated = $request->validate([
            'first_name'     => ['sometimes', 'string', 'max:100'],
            'last_name'      => ['sometimes', 'string', 'max:100'],
            'age'            => ['sometimes', 'nullable', 'integer', 'min:3', 'max:25'],
            'school_level'   => ['sometimes', 'nullable', 'in:maternelle,primaire,collège,lycée,supérieur'],
            'personal_notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);

        $child->update($validated);

        return $this->success(
            new ChildResource($child),
            'Profil enfant mis à jour.'
        );
    }

    /**
     * Supprimer un profil enfant.
     */
    public function destroy(Request $request, Child $child): JsonResponse
    {
        if ($child->parent_id !== $request->user()->id) {
            return $this->forbidden('Cet enfant ne vous appartient pas.');
        }

        $child->delete();

        return $this->success(null, 'Profil enfant supprimé.');
    }
}
