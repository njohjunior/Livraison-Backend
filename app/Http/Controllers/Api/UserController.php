<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:7'
        ]);

        // Récupérer l'utilisateur en fonction de l'email
        $user = User::where('email', $validatedData['email'])->first();

        // Vérifier si le mot de passe est correct
        if (!$user || !Hash::check($validatedData['password'], $user->password)) {
            return response()->json([
                'message' => 'Les informations de connexion sont incorrectes.',
                'status_code' => 403
            ], 403);
        }

        // Générer le token
        $token = $user->createToken('Auth Token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'status_code' => 200,
            'token' => $token
        ], 200);
    }

    public function logout(Request $request)
    {
        // Supprimer tous les tokens de l'utilisateur connecté
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Vous avez été déconnecté avec succès',
            'status_code' => 200
        ], 200);
    }
}
