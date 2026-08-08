<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Profil étendu des répétiteurs.
     * Séparé de `users` pour garder le modèle User léger
     * et permettre un chargement conditionnel (whenLoaded).
     */
    public function up(): void
    {
        Schema::create('tutor_profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->unique()
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Matières enseignées (ex: ["Mathématiques", "Physique"])
            $table->json('specialties')->nullable();

            // Expérience déclarée (en années)
            $table->unsignedTinyInteger('experience_years')->default(0);

            // Diplôme le plus élevé
            $table->string('diploma', 255)->nullable();

            // Disponibilité en temps réel (pour le filtrage marketplace)
            $table->boolean('is_available')->default(true);

            // Métriques calculées (dénormalisées pour la performance)
            $table->decimal('rating_avg', 3, 2)->default(0.00)
                  ->comment('Moyenne des évaluations parents (0-5)');
            $table->unsignedInteger('total_sessions')->default(0)
                  ->comment('Nombre total de séances terminées');
            $table->unsignedInteger('total_reviews')->default(0);

            // Streak : séances consécutives avec rapport soumis à temps
            $table->unsignedInteger('current_streak')->default(0);
            $table->unsignedInteger('best_streak')->default(0);

            // Coordonnées de localisation du répétiteur
            $table->string('city', 100)->nullable();
            $table->string('district', 100)->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            $table->timestamps();

            $table->index('is_available');
            $table->index(['city', 'district']);
            $table->index('rating_avg');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutor_profiles');
    }
};
