<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table des candidatures des répétiteurs aux offres d'emploi.
     *
     * Règle métier : un répétiteur ne peut postuler qu'une seule fois
     * à une même offre (contrainte UNIQUE). L'acceptation d'une candidature
     * déclenche automatiquement la création d'un Contrat Pédagogique.
     */
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_offer_id')
                  ->constrained('job_offers')
                  ->cascadeOnDelete();

            $table->foreignId('tutor_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Lettre de motivation / présentation du répétiteur
            $table->text('cover_letter')->nullable();

            // Tarif proposé par le répétiteur pour cette offre
            $table->decimal('proposed_rate', 8, 2)->nullable();

            // Statut de la candidature
            $table->enum('status', [
                'pending',   // en attente de réponse du parent
                'accepted',  // acceptée → contrat généré
                'rejected',  // refusée
                'withdrawn', // retirée par le répétiteur
            ])->default('pending');

            // Message de refus optionnel (côté parent)
            $table->string('rejection_reason', 500)->nullable();

            // Timestamp de la décision
            $table->timestamp('decided_at')->nullable();

            $table->timestamps();

            // Un répétiteur ne peut postuler qu'une fois à la même offre
            $table->unique(['job_offer_id', 'tutor_id']);

            $table->index('tutor_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
