<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table des abonnements Webhooks pour les plateformes tierces et partenaires.
     *
     * Permet à d'autres plateformes de s'abonner aux événements Areta
     * (ex: application.accepted, session.report.submitted, ai.diagnosis.ready).
     * Chaque payload est signé avec HMAC-SHA256 via le `secret`.
     */
    public function up(): void
    {
        Schema::create('webhook_subscriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // URL de destination du webhook (ex: https://partenaire.com/api/areta-webhook)
            $table->string('url', 500);

            // Clé secrète générée pour la signature HMAC-SHA256 (X-Areta-Signature)
            $table->string('secret', 64);

            // Événements écoutés (ex: ["application.accepted", "session.report.submitted"])
            $table->json('events');

            $table->boolean('is_active')->default(true);

            // Statistique d'erreurs consécutives (désactive si > 10 échecs)
            $table->unsignedSmallInteger('failed_attempts_count')->default(0);

            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_subscriptions');
    }
};
