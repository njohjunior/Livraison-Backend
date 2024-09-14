<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Livreur;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LivreurController extends Controller
{
    // Récupérer tous les livreurs
    public function index()
    {
        return response()->json(Livreur::all(), 200);
    }

    // Récupérer un livreur spécifique par ID
    public function show($id)
    {
        $livreur = Livreur::find($id);
        if ($livreur) {
            return response()->json($livreur, 200);
        }
        return response()->json(['message' => 'Livreur non trouvé'], 404);
    }

    

    // Mettre à jour les informations du livreur
    public function updateProfile(Request $request, $id)
    {
        $user = User::find($id);

        if ($user) {
            $validatedData = $request->validate([
                'nom' => 'string|max:255|min:3|nullable',
                'prenom' => 'string|max:255|min:3|nullable',
                'adresse' => 'string|max:255|nullable',
                'contact' => 'string|max:255|nullable',
                'typeDeVehicule' => 'string|max:255|nullable',
            ]);

            $user->update($validatedData);

            $livreur = Livreur::where('email', $user->email)->first();
            if ($user) {
                $livreur->update($request->only('nom', 'prenom', 'adresse', 'contact', 'typeDeVehicule'));
            }

            return response()->json([
                'message' => 'Profil mis à jour avec succès',
                'livreur' => $livreur
            ], 200);
        }

        return response()->json(['message' => 'Livreur non trouvé'], 404);
    }

    // Mettre à jour le mot de passe du livreur
    public function updatePassword(Request $request, $id)
    {
        $livreur = Livreur::find($id);

        if ($livreur) {
            $validatedData = $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);

            $hashedPassword = Hash::make($validatedData['password']);
            $livreur->update(['password' => $hashedPassword]);

            $user = User::where('email', $livreur->email)->first();
            if ($user) {
                $user->update(['password' => $hashedPassword]);
            }

            return response()->json(['message' => 'Mot de passe mis à jour avec succès'], 200);
        }

        return response()->json(['message' => 'Livreur non trouvé'], 404);
    }

    // Supprimer un livreur
    public function delete($id)
    {
        $livreur = Livreur::find($id);

        if ($livreur) {
            $livreur->delete();

            $user = User::where('email', $livreur->email)->first();
            if ($user) {
                $user->delete();
            }

            return response()->json(['message' => 'Livreur supprimé avec succès'], 200);
        }

        return response()->json(['message' => 'Livreur non trouvé'], 404);
    }
}
