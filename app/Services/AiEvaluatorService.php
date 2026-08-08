<?php

namespace App\Services;

use App\Models\AiDiagnosis;
use App\Models\Child;
use App\Models\Contract;
use App\Models\TutoringSession;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service IA d'évaluation pédagogique (AI Evaluator).
 *
 * Analyse les séances récentes, les notes sur 20 et les comptes-rendus
 * textuels rédigés par les répétiteurs pour générer un diagnostic structuré :
 * - lacunes_detectees
 * - progression_estimee
 * - plan_action
 *
 * Supporte DeepSeek API (par défaut) et OpenAI.
 * Fallback heuristique automatique si aucune clé API n'est configurée.
 */
class AiEvaluatorService
{
    /**
     * Génère un bilan pédagogique IA pour un enfant.
     */
    public function generateForChild(Child $child, ?Contract $contract = null): AiDiagnosis
    {
        // 1. Rassembler les séances récentes avec un rapport soumis
        $sessions = TutoringSession::whereHas('contract', fn ($q) => $q->where('child_id', $child->id))
            ->where('status', 'completed')
            ->whereNotNull('report_submitted_at')
            ->latest('scheduled_at')
            ->limit(10)
            ->get();

        if ($sessions->isEmpty()) {
            return $this->generateInitialBaselineDiagnosis($child, $contract);
        }

        // 2. Préparer les données textuelles pour le prompt
        $sessionsPayload = $sessions->map(fn ($s) => [
            'subject'     => $s->subject,
            'grade'       => $s->grade . '/20',
            'report_text' => $s->report_text,
            'date'        => $s->scheduled_at->format('d/m/Y'),
        ])->values()->all();

        // 3. Tenter l'appel API LLM (DeepSeek ou OpenAI)
        $aiResult = $this->callLlmApi($child, $sessionsPayload);

        if (! $aiResult) {
            // Fallback si pas de clé API ou erreur réseau
            $aiResult = $this->generateHeuristicDiagnosis($child, $sessions);
        }

        // 4. Enregistrer en base
        return AiDiagnosis::create([
            'child_id'                => $child->id,
            'contract_id'             => $contract?->id ?? $sessions->first()?->contract_id,
            'lacunes_detectees'       => $aiResult['lacunes_detectees'],
            'progression_estimee'     => $aiResult['progression_estimee'],
            'plan_action'             => $aiResult['plan_action'],
            'generated_at'            => now(),
            'model_used'              => $aiResult['model_used'] ?? 'areta-heuristic-engine',
            'sessions_analyzed_count' => $sessions->count(),
        ]);
    }

    /**
     * Appelle l'API DeepSeek / OpenAI avec mode JSON strict.
     *
     * @return array{lacunes_detectees: array, progression_estimee: array, plan_action: array, model_used: string}|null
     */
    protected function callLlmApi(Child $child, array $sessionsPayload): ?array
    {
        $deepseekKey = config('services.deepseek.key') ?? env('DEEPSEEK_API_KEY');
        $openaiKey   = config('services.openai.key')   ?? env('OPENAI_API_KEY');

        $systemPrompt = <<<PROMPT
Tu es un expert en pédagogie et en suivi scolaire pour la plateforme Areta.
Analyse les notes et comptes-rendus de séances de l'élève ci-dessous et génère un bilan synthétique au format JSON strict.

Format JSON EXACT attendu (n'inclus AUCUN texte en dehors du JSON) :
{
  "lacunes_detectees": [
    "Nom court du concept 1 à renforcer",
    "Nom court du concept 2 à renforcer"
  ],
  "progression_estimee": {
    "tendance": "en_hausse" | "stable" | "en_baisse",
    "evolution_pct": "+15%",
    "score_confiance": 85,
    "commentaire_synthetique": "Court résumé d'une phrase"
  },
  "plan_action": [
    {
      "cible": "repetiteur",
      "recommandation": "Action concrète pour le répétiteur"
    },
    {
      "cible": "parent",
      "recommandation": "Conseil pour le parent à la maison"
    }
  ]
}
PROMPT;

        $userPrompt = "Élève: {$child->full_name}, Niveau: {$child->school_level}\n\nHistorique des séances:\n" . json_encode($sessionsPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        try {
            if ($deepseekKey) {
                $response = Http::withToken($deepseekKey)
                    ->timeout(15)
                    ->post('https://api.deepseek.com/v1/chat/completions', [
                        'model'           => 'deepseek-chat',
                        'response_format' => ['type' => 'json_object'],
                        'messages'        => [
                            ['role' => 'system', 'content' => $systemPrompt],
                            ['role' => 'user',   'content' => $userPrompt],
                        ],
                    ]);

                if ($response->successful()) {
                    $jsonContent = $response->json('choices.0.message.content');
                    $parsed = json_decode($jsonContent, true);

                    if ($parsed && isset($parsed['lacunes_detectees'], $parsed['progression_estimee'], $parsed['plan_action'])) {
                        $parsed['model_used'] = 'deepseek-chat';
                        return $parsed;
                    }
                }
            } elseif ($openaiKey) {
                $response = Http::withToken($openaiKey)
                    ->timeout(15)
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model'           => 'gpt-4o-mini',
                        'response_format' => ['type' => 'json_object'],
                        'messages'        => [
                            ['role' => 'system', 'content' => $systemPrompt],
                            ['role' => 'user',   'content' => $userPrompt],
                        ],
                    ]);

                if ($response->successful()) {
                    $jsonContent = $response->json('choices.0.message.content');
                    $parsed = json_decode($jsonContent, true);

                    if ($parsed && isset($parsed['lacunes_detectees'], $parsed['progression_estimee'], $parsed['plan_action'])) {
                        $parsed['model_used'] = 'gpt-4o-mini';
                        return $parsed;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Erreur lors de l\'appel API IA : ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Fallback heuristique déterministe quand aucune clé API LLM n'est présente.
     */
    protected function generateHeuristicDiagnosis(Child $child, $sessions): array
    {
        $avgGrade = $sessions->avg('grade');
        $recentGraded = $sessions->pluck('grade')->values();

        $trend = 'stable';
        $pct = '0%';
        if ($recentGraded->count() >= 2) {
            $first = $recentGraded->last();
            $last  = $recentGraded->first();
            $diff  = $last - $first;
            if ($diff > 1) {
                $trend = 'en_hausse';
                $pct   = '+' . round(($diff / max($first, 1)) * 100) . '%';
            } elseif ($diff < -1) {
                $trend = 'en_baisse';
                $pct   = '-' . round((abs($diff) / max($first, 1)) * 100) . '%';
            }
        }

        $lacunes = [];
        if ($avgGrade < 10) {
            $lacunes[] = 'Bases fondamentales de la matière';
            $lacunes[] = 'Résolution de problèmes et méthodologie';
        } elseif ($avgGrade < 14) {
            $lacunes[] = 'Rigueur dans les calculs et démonstrations';
            $lacunes[] = 'Gestion du temps lors des évaluations';
        } else {
            $lacunes[] = 'Approfondissement des notions complexes';
        }

        return [
            'lacunes_detectees'   => $lacunes,
            'progression_estimee' => [
                'tendance'                => $trend,
                'evolution_pct'           => $pct,
                'score_confiance'         => 80,
                'commentaire_synthetique' => "Moyenne récente de " . round($avgGrade, 1) . "/20 sur les " . $sessions->count() . " dernières séances.",
            ],
            'plan_action' => [
                [
                    'cible'          => 'repetiteur',
                    'recommandation' => "Consacrer 15 min par séance aux exercices d'application directe sur " . ($lacunes[0] ?? 'les points faibles') . '.',
                ],
                [
                    'cible'          => 'parent',
                    'recommandation' => "Vérifier que l'élève relit la fiche de révision élaborée par le répétiteur 24h avant la séance suivante.",
                ],
            ],
            'model_used' => 'areta-heuristic-engine',
        ];
    }

    /**
     * Bilan initial pour un élève sans séances terminées.
     */
    protected function generateInitialBaselineDiagnosis(Child $child, ?Contract $contract): AiDiagnosis
    {
        return AiDiagnosis::create([
            'child_id'          => $child->id,
            'contract_id'       => $contract?->id,
            'lacunes_detectees' => ['Évaluation initiale en cours'],
            'progression_estimee' => [
                'tendance'                => 'stable',
                'evolution_pct'           => '0%',
                'score_confiance'         => 50,
                'commentaire_synthetique' => 'Première séance planifiée. L\'IA analysera le niveau dès le premier rapport soumis.',
            ],
            'plan_action' => [
                [
                    'cible'          => 'repetiteur',
                    'recommandation' => 'Effectuer un test de niveau global lors du premier cours.',
                ],
            ],
            'generated_at'            => now(),
            'model_used'              => 'areta-baseline-engine',
            'sessions_analyzed_count' => 0,
        ]);
    }
}
