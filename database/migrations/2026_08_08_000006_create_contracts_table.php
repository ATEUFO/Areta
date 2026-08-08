<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table des contrats pédagogiques.
     *
     * Un contrat est généré automatiquement lorsqu'un parent
     * accepte la candidature d'un répétiteur. Il relie :
     * - Le parent (donneur d'ordre)
     * - Le répétiteur (prestataire)
     * - L'enfant concerné (optionnel)
     * - L'offre d'emploi d'origine
     */
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();

            // Références
            $table->foreignId('job_offer_id')
                  ->constrained('job_offers')
                  ->restrictOnDelete();

            $table->foreignId('application_id')
                  ->constrained('applications')
                  ->restrictOnDelete();

            $table->foreignId('parent_id')
                  ->constrained('users')
                  ->restrictOnDelete();

            $table->foreignId('tutor_id')
                  ->constrained('users')
                  ->restrictOnDelete();

            $table->foreignId('child_id')
                  ->nullable()
                  ->constrained('children')
                  ->nullOnDelete();

            // Tarif convenu (issu de la candidature acceptée)
            $table->decimal('agreed_rate', 8, 2)->nullable();

            // Statut du contrat
            $table->enum('status', [
                'active',      // en cours
                'paused',      // mis en pause (vacances, etc.)
                'completed',   // terminé normalement
                'terminated',  // résilié prématurément
            ])->default('active');

            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();

            // Motif de résiliation (si terminé prématurément)
            $table->text('termination_reason')->nullable();

            $table->timestamps();

            $table->index(['parent_id', 'status']);
            $table->index(['tutor_id', 'status']);
            $table->index('child_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
