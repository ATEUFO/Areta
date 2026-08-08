<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Trait ApiResponse
 *
 * Garantit un format de réponse JSON uniforme pour toute l'API Areta v1.
 * Toutes les réponses (succès ET erreurs) suivent la même enveloppe.
 *
 * Succès  : { success: true, data: {...}, message: "...", meta: {...} }
 * Erreur  : { success: false, error: { code: "...", message: "...", details: {...} } }
 * Paginé  : { success: true, data: [...], meta: { current_page, per_page, total, last_page }, links: {...} }
 */
trait ApiResponse
{
    /**
     * Réponse de succès standard.
     *
     * @param mixed $data
     */
    protected function success(
        mixed $data = null,
        string $message = 'Succès',
        int $status = 200,
        array $meta = []
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data'    => $data,
            'message' => $message,
            'meta'    => array_merge([
                'version'   => '1.0',
                'timestamp' => now()->toISOString(),
            ], $meta),
        ], $status);
    }

    /**
     * Réponse de création (201 Created).
     *
     * @param mixed $data
     */
    protected function created(
        mixed $data = null,
        string $message = 'Ressource créée avec succès'
    ): JsonResponse {
        return $this->success($data, $message, 201);
    }

    /**
     * Réponse vide (204 No Content).
     */
    protected function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Réponse de traitement asynchrone accepté (202 Accepted).
     * Utilisé pour les jobs IA placés en file d'attente.
     */
    protected function accepted(
        string $message = 'Traitement en cours',
        array $meta = []
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data'    => null,
            'message' => $message,
            'meta'    => array_merge([
                'version'   => '1.0',
                'timestamp' => now()->toISOString(),
            ], $meta),
        ], 202);
    }

    /**
     * Réponse d'erreur standard.
     */
    protected function error(
        string $code,
        string $message,
        array $details = [],
        int $status = 422
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'error'   => [
                'code'    => $code,
                'message' => $message,
                'details' => $details,
            ],
        ], $status);
    }

    /**
     * Erreur 404 Not Found.
     */
    protected function notFound(string $message = 'Ressource introuvable'): JsonResponse
    {
        return $this->error('NOT_FOUND', $message, [], 404);
    }

    /**
     * Erreur 403 Forbidden.
     */
    protected function forbidden(string $message = 'Accès refusé'): JsonResponse
    {
        return $this->error('FORBIDDEN', $message, [], 403);
    }

    /**
     * Erreur 401 Unauthorized.
     */
    protected function unauthorized(string $message = 'Non authentifié'): JsonResponse
    {
        return $this->error('UNAUTHORIZED', $message, [], 401);
    }

    /**
     * Réponse paginée avec liens de navigation.
     */
    protected function paginated(LengthAwarePaginator $paginator): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $paginator->items(),
            'meta'    => [
                'version'      => '1.0',
                'timestamp'    => now()->toISOString(),
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last'  => $paginator->url($paginator->lastPage()),
                'prev'  => $paginator->previousPageUrl(),
                'next'  => $paginator->nextPageUrl(),
            ],
        ], 200);
    }
}
