<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table des bilans et diagnostics pédagogiques générés par l'IA (AI Evaluator).
     *
     * Format JSON strict renvoyé par le LLM :
     * - lacunes_detectees : array de tags/sujets à renforcer
     * - progression_estimee : objet { tendance, evolution_pct, score_confiance }
     * - plan_action : array de recommandations concrètes pour répétiteur et parent
     */
    public function up(): void
    {
        Schema::create('ai_diagnoses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('child_id')
                  ->constrained('children')
                  ->cascadeOnDelete();

            $table->foreignId('contract_id')
                  ->nullable()
                  ->constrained('contracts')
                  ->nullOnDelete();

            // Résultats structurés IA (JSON)
            $table->json('lacunes_detectees');
            $table->json('progression_estimee');
            $table->json('plan_action');

            // Métadonnées de la génération
            $table->timestamp('generated_at');
            $table->string('model_used', 100)->default('deepseek-chat');
            $table->unsignedInteger('sessions_analyzed_count')->default(0);

            $table->timestamps();

            $table->index(['child_id', 'generated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_diagnoses');
    }
};
