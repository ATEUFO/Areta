<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table des profils enfants (Shadow Profiles).
     * Les enfants n'ont pas de compte — ils sont des entités
     * de données gérées par leur Parent. Un même enfant peut
     * être rattaché à plusieurs contrats successifs.
     */
    public function up(): void
    {
        Schema::create('children', function (Blueprint $table) {
            $table->id();

            // Relation tutelle (le parent propriétaire du profil)
            $table->foreignId('parent_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Identité
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->unsignedTinyInteger('age')->nullable();

            // Niveau scolaire
            $table->enum('school_level', [
                'maternelle',
                'primaire',
                'collège',
                'lycée',
                'supérieur',
            ])->nullable();

            // Notes et moyenne calculée dynamiquement à chaque rapport
            $table->decimal('grade_average', 4, 2)->nullable()
                  ->comment('Recalculée après chaque rapport de séance');

            // Notes personnelles du parent (visibles seulement par le parent)
            $table->text('personal_notes')->nullable();

            // Avatar optionnel (généré ou uploadé)
            $table->string('avatar')->nullable();

            $table->timestamps();

            // Index pour les requêtes fréquentes
            $table->index('parent_id');
            $table->index('school_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('children');
    }
};
