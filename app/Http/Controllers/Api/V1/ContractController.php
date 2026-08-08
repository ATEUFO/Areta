<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ContractResource;
use App\Http\Traits\ApiResponse;
use App\Models\Contract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Gestion des contrats pédagogiques — Areta API v1.
 *
 * GET  /api/v1/contracts               Liste des contrats de l'utilisateur (Parent/Répétiteur)
 * GET  /api/v1/contracts/{id}          Détail d'un contrat
 * POST /api/v1/contracts/{id}/terminate Résilier un contrat
 */
class ContractController extends Controller
{
    use ApiResponse;

    /**
     * Liste des contrats de l'utilisateur connecté.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Contract::query()
            ->with(['parent:id,name,city', 'tutor:id,name,avatar', 'child', 'jobOffer:id,title,subject'])
            ->withCount('sessions');

        if ($user->isParent()) {
            $query->where('parent_id', $user->id);
        } else {
            $query->where('tutor_id', $user->id);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $contracts = $query->latest()->paginate(15);
        $contracts->through(fn ($contract) => new ContractResource($contract));

        return $this->paginated($contracts);
    }

    /**
     * Détail d'un contrat.
     */
    public function show(Request $request, Contract $contract): JsonResponse
    {
        $user = $request->user();

        if ($contract->parent_id !== $user->id && $contract->tutor_id !== $user->id) {
            return $this->forbidden('Vous n\'êtes pas partie à ce contrat.');
        }

        $contract->load([
            'parent:id,name,email,phone,city',
            'tutor:id,name,email,phone,avatar',
            'child',
            'jobOffer',
            'sessions' => fn ($q) => $q->latest('scheduled_at'),
        ]);

        return $this->success(
            new ContractResource($contract),
            'Contrat récupéré.'
        );
    }

    /**
     * Résilier un contrat.
     */
    public function terminate(Request $request, Contract $contract): JsonResponse
    {
        $user = $request->user();

        if ($contract->parent_id !== $user->id && $contract->tutor_id !== $user->id) {
            return $this->forbidden('Vous ne pouvez résilier que vos propres contrats.');
        }

        if (! $contract->isActive()) {
            return $this->error('CONTRACT_NOT_ACTIVE', 'Seul un contrat actif peut être résilié.', [], 422);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $contract->terminate($validated['reason']);

        return $this->success(
            new ContractResource($contract->fresh(['parent', 'tutor', 'child', 'jobOffer'])),
            'Le contrat a été résilié.'
        );
    }
}
