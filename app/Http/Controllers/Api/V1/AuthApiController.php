<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Resources\V1\UserResource;
use App\Http\Traits\ApiResponse;
use App\Models\TutorProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Authentification API Areta v1 — Sanctum Bearer Tokens.
 *
 * Flux :
 *   POST /api/v1/auth/register → { user, token, token_type: "Bearer" }
 *   POST /api/v1/auth/login    → { user, token, token_type: "Bearer" }
 *   POST /api/v1/auth/logout   → 200 (révoque le token courant)
 *   GET  /api/v1/auth/me       → { user }
 */
class AuthApiController extends Controller
{
    use ApiResponse;

    /**
     * Inscription d'un nouvel utilisateur (Parent ou Répétiteur).
     *
     * Crée automatiquement un TutorProfile vide si le rôle est "tutor".
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
            'phone'    => $request->phone,
            'city'     => $request->city,
            'district' => $request->district,
        ]);

        // Initialise le profil répétiteur vide
        if ($user->role === 'tutor') {
            TutorProfile::create([
                'user_id' => $user->id,
            ]);
            $user->load('tutorProfile');
        }

        $token = $user->createToken($request->deviceName() ?? 'Areta API')->plainTextToken;

        return $this->created([
            'user'       => new UserResource($user),
            'token'      => $token,
            'token_type' => 'Bearer',
        ], 'Compte créé avec succès. Bienvenue sur Areta !');
    }

    /**
     * Connexion et émission d'un token Sanctum.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->error(
                'INVALID_CREDENTIALS',
                'Email ou mot de passe incorrect.',
                [],
                401
            );
        }

        // Charge conditionnellement le profil répétiteur
        if ($user->role === 'tutor') {
            $user->load('tutorProfile');
        }

        // Révoque les anciens tokens du même device avant d'en créer un nouveau
        $user->tokens()->where('name', $request->deviceName())->delete();

        $token = $user->createToken($request->deviceName())->plainTextToken;

        return $this->success([
            'user'       => new UserResource($user),
            'token'      => $token,
            'token_type' => 'Bearer',
        ], 'Connexion réussie. Bon retour !');
    }

    /**
     * Déconnexion — révoque le token courant.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Déconnexion réussie.');
    }

    /**
     * Profil de l'utilisateur authentifié.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role === 'tutor') {
            $user->load('tutorProfile');
        } elseif ($user->role === 'parent') {
            $user->loadCount('children');
        }

        return $this->success(
            new UserResource($user),
            'Profil récupéré.'
        );
    }

    /**
     * Mise à jour du profil de l'utilisateur connecté.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'        => ['sometimes', 'string', 'max:255'],
            'phone'       => ['sometimes', 'nullable', 'string', 'max:25'],
            'city'        => ['sometimes', 'nullable', 'string', 'max:100'],
            'district'    => ['sometimes', 'nullable', 'string', 'max:100'],
            'bio'         => ['sometimes', 'nullable', 'string', 'max:1000'],
            'hourly_rate' => ['sometimes', 'nullable', 'numeric', 'min:0'],

            // Champs répétiteur uniquement
            'specialties'      => ['sometimes', 'nullable', 'array'],
            'specialties.*'    => ['string', 'max:100'],
            'experience_years' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:50'],
            'diploma'          => ['sometimes', 'nullable', 'string', 'max:255'],
            'is_available'     => ['sometimes', 'boolean'],
        ]);

        // Mise à jour des champs utilisateur
        $userFields = array_intersect_key($validated, array_flip([
            'name', 'phone', 'city', 'district', 'bio', 'hourly_rate',
        ]));
        $user->update($userFields);

        // Mise à jour du profil répétiteur si applicable
        if ($user->role === 'tutor') {
            $tutorFields = array_intersect_key($validated, array_flip([
                'specialties', 'experience_years', 'diploma', 'is_available',
            ]));
            if (! empty($tutorFields)) {
                $user->tutorProfile()->updateOrCreate(
                    ['user_id' => $user->id],
                    $tutorFields
                );
            }
            $user->load('tutorProfile');
        }

        return $this->success(
            new UserResource($user),
            'Profil mis à jour avec succès.'
        );
    }
}
