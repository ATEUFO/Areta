<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute les champs de profil Areta à la table users.
     * Sépare clairement les attributs Parent (adresse, enfants)
     * des attributs Répétiteur (tarif, vérification, biographie).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Rôle : détermine l'espace de travail de l'utilisateur
            $table->enum('role', ['parent', 'tutor'])->default('parent')->after('name');

            // Coordonnées communes
            $table->string('phone', 25)->nullable()->after('role');
            $table->string('city', 100)->nullable()->after('phone');
            $table->string('district', 100)->nullable()->after('city');

            // Profil commun
            $table->string('avatar')->nullable()->after('district');
            $table->text('bio')->nullable()->after('avatar');

            // Champs spécifiques aux répétiteurs
            $table->boolean('is_verified')->default(false)->after('bio');
            $table->decimal('hourly_rate', 8, 2)->nullable()->after('is_verified');

            // Index pour les recherches géographiques
            $table->index(['city', 'district']);
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['city', 'district']);
            $table->dropIndex(['role']);
            $table->dropColumn([
                'role', 'phone', 'city', 'district',
                'avatar', 'bio', 'is_verified', 'hourly_rate',
            ]);
        });
    }
};
