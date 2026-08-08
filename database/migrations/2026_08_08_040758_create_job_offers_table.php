<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table des offres d'emploi publiées par les parents.
     *
     * Règle métier clé : une offre est liée à un PARENT, pas obligatoirement
     * à un enfant. Un oncle ou tuteur temporaire peut chercher un répétiteur
     * pour un enfant non encore enregistré dans le système.
     */
    public function up(): void
    {
        Schema::create('job_offers', function (Blueprint $table) {
            $table->id();

            // Le parent qui publie l'offre (obligatoire)
            $table->foreignId('parent_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // L'enfant concerné (optionnel — indépendance des offres)
            $table->foreignId('child_id')
                  ->nullable()
                  ->constrained('children')
                  ->nullOnDelete();

            // Informations pédagogiques
            $table->string('title', 255);
            $table->string('subject', 100);
            $table->text('objective')->nullable();

            // Fréquence souhaitée
            $table->enum('frequency', [
                '1x_semaine',
                '2x_semaine',
                '3x_semaine',
                'quotidien',
                'weekend',
                'a_definir',
            ])->default('2x_semaine');

            // Niveau scolaire de l'élève
            $table->enum('school_level', [
                'maternelle',
                'primaire',
                'collège',
                'lycée',
                'supérieur',
            ])->nullable();

            // Localisation de l'offre
            $table->string('city', 100);
            $table->string('district', 100)->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            // Budget (fourchette indicative)
            $table->decimal('budget_min', 8, 2)->nullable();
            $table->decimal('budget_max', 8, 2)->nullable();

            // Statut de l'offre
            $table->enum('status', [
                'open',        // visible et ouverte aux candidatures
                'in_progress', // répétiteur accepté, en cours
                'closed',      // fermée manuellement ou expirée
            ])->default('open');

            // Nombre de vues (analytics simple)
            $table->unsignedInteger('views_count')->default(0);

            $table->timestamps();

            // Index pour le filtrage marketplace
            $table->index(['status', 'city']);
            $table->index(['subject', 'status']);
            $table->index('parent_id');
            $table->index('child_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_offers');
    }
};
