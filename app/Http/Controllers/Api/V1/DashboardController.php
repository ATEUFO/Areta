<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ChildResource;
use App\Http\Resources\V1\ContractResource;
use App\Http\Resources\V1\SessionResource;
use App\Http\Traits\ApiResponse;
use App\Models\Application;
use App\Models\Contract;
use App\Models\TutoringSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoints analytiques pour les Tableaux de Bord — Areta API v1.
 *
 * GET /api/v1/dashboard/parent   Vue analytique complète Parent (cartes enfants, graphiques, alertes IA)
 * GET /api/v1/dashboard/tutor    Vue analytique complète Répétiteur (agenda, streak, recommandations IA)
 */
class DashboardController extends Controller
{
    use ApiResponse;

    /**
     * Dashboard Parent — "Command Center".
     *
     * Retourne :
     * - Liste des enfants avec leur moyenne et évolution des notes par matière
     * - Alerte/Diagnostic IA le plus récent par enfant
     * - Nombre de candidatures en attente de réponse
     * - Prochaines séances prévues
     */
    public function parent(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->isParent()) {
            return $this->forbidden('Seul un parent a accès à ce tableau de bord.');
        }

        // 1. Enfants avec leurs données analytiques
        $children = $user->children()
            ->with(['aiDiagnoses' => fn ($q) => $q->latest()->limit(1)])
            ->get();

        $childrenData = $children->map(function ($child) {
            // Historique des notes par matière pour les graphiques Recharts
            $sessions = TutoringSession::whereHas('contract', fn ($q) => $q->where('child_id', $child->id))
                ->whereNotNull('grade')
                ->latest('scheduled_at')
                ->limit(20)
                ->get();

            $subjectsEvolution = $sessions->groupBy('subject')->map(function ($group, $subject) {
                return [
                    'subject' => $subject,
                    'grades'  => $group->pluck('grade')->reverse()->values(),
                    'average' => round($group->avg('grade'), 2),
                ];
            })->values();

            // Prochaine séance prévue
            $nextSession = TutoringSession::whereHas('contract', fn ($q) => $q->where('child_id', $child->id))
                ->where('status', 'planned')
                ->where('scheduled_at', '>=', now())
                ->orderBy('scheduled_at', 'asc')
                ->first();

            return [
                'id'                  => $child->id,
                'first_name'          => $child->first_name,
                'last_name'           => $child->last_name,
                'full_name'           => $child->full_name,
                'school_level'        => $child->school_level,
                'grade_average'       => $child->grade_average ? (float) $child->grade_average : null,
                'subjects_evolution'  => $subjectsEvolution,
                'latest_ai_diagnosis' => $child->aiDiagnoses->first(),
                'next_session'        => $nextSession ? new SessionResource($nextSession) : null,
            ];
        });

        // 2. Métriques globales du parent
        $pendingApplicationsCount = Application::whereHas('jobOffer', fn ($q) => $q->where('parent_id', $user->id))
            ->where('status', 'pending')
            ->count();

        $activeContractsCount = Contract::where('parent_id', $user->id)
            ->where('status', 'active')
            ->count();

        // 3. Dernières séances effectuées (feed d'activité)
        $recentSessions = TutoringSession::whereHas('contract', fn ($q) => $q->where('parent_id', $user->id))
            ->where('status', 'completed')
            ->latest('scheduled_at')
            ->limit(5)
            ->with(['contract.child', 'contract.tutor'])
            ->get();

        return $this->success([
            'children'                   => $childrenData,
            'pending_applications_count' => $pendingApplicationsCount,
            'active_contracts_count'     => $activeContractsCount,
            'recent_sessions'            => SessionResource::collection($recentSessions),
        ], 'Dashboard Parent récupéré.');
    }

    /**
     * Dashboard Répétiteur — "Professor HQ".
     *
     * Retourne :
     * - Statistiques de réputation (note moyenne, streak courant et meilleur streak)
     * - Vue calendaire des prochaines séances
     * - Candidatures en cours
     * - Recommandations IA pour les cours à venir
     */
    public function tutor(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->isTutor()) {
            return $this->forbidden('Seul un répétiteur a accès à ce tableau de bord.');
        }

        $profile = $user->tutorProfile;

        // 1. Prochaines séances (Agenda)
        $upcomingSessions = TutoringSession::whereHas('contract', fn ($q) => $q->where('tutor_id', $user->id))
            ->where('status', 'planned')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at', 'asc')
            ->with(['contract.parent', 'contract.child', 'contract.jobOffer'])
            ->limit(10)
            ->get();

        // 2. Séances en attente de rapport (action requise bloquante)
        $pendingReportSessions = TutoringSession::whereHas('contract', fn ($q) => $q->where('tutor_id', $user->id))
            ->where('status', 'completed')
            ->whereNull('report_submitted_at')
            ->with(['contract.child'])
            ->get();

        // 3. Candidatures en cours
        $pendingApplicationsCount = Application::where('tutor_id', $user->id)
            ->where('status', 'pending')
            ->count();

        // 4. Contrats actifs
        $activeContracts = Contract::where('tutor_id', $user->id)
            ->where('status', 'active')
            ->with(['child', 'parent', 'jobOffer'])
            ->get();

        return $this->success([
            'metrics' => [
                'rating_avg'       => $profile ? (float) $profile->rating_avg : 0.0,
                'total_sessions'   => $profile ? $profile->total_sessions : 0,
                'current_streak'   => $profile ? $profile->current_streak : 0,
                'best_streak'      => $profile ? $profile->best_streak : 0,
                'is_available'     => $profile ? $profile->is_available : true,
            ],
            'pending_report_sessions'    => SessionResource::collection($pendingReportSessions),
            'upcoming_sessions'          => SessionResource::collection($upcomingSessions),
            'pending_applications_count' => $pendingApplicationsCount,
            'active_contracts'           => ContractResource::collection($activeContracts),
        ], 'Dashboard Répétiteur récupéré.');
    }
}
