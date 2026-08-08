<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table des séances de répétition (Core Tracker).
     *
     * Cycle de vie d'une séance :
     * 1. Planned (Planifiée) : date et heure fixées.
     * 2. Completed (Terminée) : le cours s'est déroulé.
     * 3. Report Submitted : le répétiteur a saisi la note et le compte-rendu textuel.
     *    -> Bloque le paiement/planification de la séance suivante tant que non rempli.
     *    -> Recalcule la moyenne de l'élève en temps réel.
     */
    public function up(): void
    {
        Schema::create('tutoring_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('contract_id')
                  ->constrained('contracts')
                  ->cascadeOnDelete();

            $table->dateTime('scheduled_at');
            $table->unsignedSmallInteger('duration_minutes')->default(60);

            $table->string('subject', 100);

            $table->enum('status', [
                'planned',   // planifiée
                'completed', // cours terminé, en attente de rapport
                'missed',    // élève ou répétiteur absent
                'cancelled', // annulée au préalable
            ])->default('planned');

            // Évaluation de la séance
            $table->unsignedTinyInteger('grade')->nullable()
                  ->comment('Note attribuée sur 20');
            $table->text('report_text')->nullable()
                  ->comment('Rapport pédagogique détaillé rédigé par le répétiteur');

            $table->timestamp('report_submitted_at')->nullable();

            $table->timestamps();

            $table->index(['contract_id', 'status']);
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutoring_sessions');
    }
};
