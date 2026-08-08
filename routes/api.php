<?php

use App\Http\Controllers\Api\V1\AiDiagnosisController;
use App\Http\Controllers\Api\V1\ApplicationController;
use App\Http\Controllers\Api\V1\AuthApiController;
use App\Http\Controllers\Api\V1\ChildController;
use App\Http\Controllers\Api\V1\ContractController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\JobOfferController;
use App\Http\Controllers\Api\V1\SessionController;
use App\Http\Controllers\Api\V1\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Areta v1 — Routes
|--------------------------------------------------------------------------
|
| Architecture API-First : toutes les routes sous /api/v1/ retournent
| du JSON pur, authentifiées par Bearer Token (Sanctum).
|
| Le frontend React/Inertia est le premier consommateur de cette API.
| Des apps mobiles ou des partenaires peuvent l'utiliser de la même façon.
|
*/

Route::prefix('v1')->name('api.v1.')->group(function () {

    // ──────────────────────────────────────────────────────────────
    // Authentification — Routes publiques (pas besoin de token)
    // ──────────────────────────────────────────────────────────────
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('register', [AuthApiController::class, 'register'])->name('register');
        Route::post('login',    [AuthApiController::class, 'login'])->name('login');
    });

    // ──────────────────────────────────────────────────────────────
    // Routes protégées par token Sanctum
    // ──────────────────────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth — routes authentifiées
        Route::prefix('auth')->name('auth.')->group(function () {
            Route::post('logout',   [AuthApiController::class, 'logout'])->name('logout');
            Route::get('me',        [AuthApiController::class, 'me'])->name('me');
            Route::patch('profile', [AuthApiController::class, 'updateProfile'])->name('profile.update');
        });

        // ── Phase 2 : Marketplace ─────────────────────────────────────────────
        Route::get('job-offers', [JobOfferController::class, 'index'])->name('job-offers.index');
        Route::post('job-offers', [JobOfferController::class, 'store'])->name('job-offers.store');
        Route::get('job-offers/{jobOffer}', [JobOfferController::class, 'show'])->name('job-offers.show');
        Route::put('job-offers/{jobOffer}', [JobOfferController::class, 'update'])->name('job-offers.update');
        Route::delete('job-offers/{jobOffer}', [JobOfferController::class, 'destroy'])->name('job-offers.destroy');
        Route::post('job-offers/{jobOffer}/apply', [ApplicationController::class, 'store'])->name('applications.store');

        Route::get('applications', [ApplicationController::class, 'index'])->name('applications.index');
        Route::post('applications/{application}/accept', [ApplicationController::class, 'accept'])->name('applications.accept');
        Route::post('applications/{application}/reject', [ApplicationController::class, 'reject'])->name('applications.reject');
        Route::post('applications/{application}/withdraw', [ApplicationController::class, 'withdraw'])->name('applications.withdraw');

        // ── Phase 3 : Contrats & Séances (Core Tracker) ───────────────────────
        Route::get('contracts', [ContractController::class, 'index'])->name('contracts.index');
        Route::get('contracts/{contract}', [ContractController::class, 'show'])->name('contracts.show');
        Route::post('contracts/{contract}/terminate', [ContractController::class, 'terminate'])->name('contracts.terminate');

        Route::get('contracts/{contract}/sessions', [SessionController::class, 'index'])->name('sessions.index');
        Route::post('contracts/{contract}/sessions', [SessionController::class, 'store'])->name('sessions.store');
        Route::post('sessions/{session}/complete', [SessionController::class, 'complete'])->name('sessions.complete');
        Route::post('sessions/{session}/report', [SessionController::class, 'report'])->name('sessions.report');
        Route::post('sessions/{session}/cancel', [SessionController::class, 'cancel'])->name('sessions.cancel');

        // ── Phase 3 : Enfants (Shadow Profiles) ──────────────────────────────
        Route::apiResource('children', ChildController::class);

        // ── Phase 4 : Dashboards ─────────────────────────────────────────────
        Route::get('dashboard/parent', [DashboardController::class, 'parent'])->name('dashboard.parent');
        Route::get('dashboard/tutor',  [DashboardController::class, 'tutor'])->name('dashboard.tutor');

        // ── Phase 5 : Module IA (AI Evaluator) ───────────────────────────────
        Route::get('children/{child}/diagnoses',  [AiDiagnosisController::class, 'index'])->name('children.diagnoses.index');
        Route::post('children/{child}/diagnoses', [AiDiagnosisController::class, 'generate'])->name('children.diagnoses.generate');
        Route::get('diagnoses/{diagnosis}',       [AiDiagnosisController::class, 'show'])->name('diagnoses.show');

        // ── Phase 6 : Webhooks ───────────────────────────────────────────────
        Route::get('webhooks', [WebhookController::class, 'index'])->name('webhooks.index');
        Route::post('webhooks', [WebhookController::class, 'store'])->name('webhooks.store');
        Route::get('webhooks/{webhook}', [WebhookController::class, 'show'])->name('webhooks.show');
        Route::delete('webhooks/{webhook}', [WebhookController::class, 'destroy'])->name('webhooks.destroy');
        Route::post('webhooks/{webhook}/test', [WebhookController::class, 'test'])->name('webhooks.test');

        // ── Phase 6 : Notifications (à implémenter) ────────────────
        // Route::get('notifications', [NotificationController::class, 'index']);
        // Route::patch('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        // Route::patch('notifications/read-all', [NotificationController::class, 'markAllAsRead']);

        // ── Phase 7 : Profils répétiteurs publics (à implémenter) ──
        // Route::get('tutors', [TutorController::class, 'index']);
        // Route::get('tutors/{user}', [TutorController::class, 'show']);
    });
});
