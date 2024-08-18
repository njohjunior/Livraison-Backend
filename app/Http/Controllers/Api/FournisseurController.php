<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fournisseur;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class FournisseurController extends Controller
{
    // Récupérer tous les fournisseurs
    public function index()
    {
        return response()->json(Fournisseur::all(), 200);
    }

    // Récupérer un fournisseur spécifique
    public function show($id)
    {
        $fournisseur = Fournisseur::find($id);
        if ($fournisseur) {
            return response()->json($fournisseur, 200);
        } else {
            return response()->json(['message' => 'Ce fournisseur n\'existe pas!'], 404);
        }
    }

    // Création d'un nouveau fournisseur
    public function store(Request $request)
    {
        // Validation des données
        $validatedData = $request->validate([
            'nom' => 'required|string|max:255|min:3',
            'email' => 'required|email|string|max:255|unique:fournisseurs,email|unique:users,email',
            'adresse' => 'required|string',
            'contact' => 'required|string|max:255',
            'password' => 'required|string|min:7|confirmed'
        ]);

        // Hashage du mot de passe
        $validatedData['password'] = Hash::make($validatedData['password']);

        // Création du fournisseur
        $fournisseur = Fournisseur::create($validatedData);

        // Création de l'utilisateur dans la table "users"
        $user = User::create([
            'nom' => $validatedData['nom'],
            'email' => $validatedData['email'],
            'role' => 'Fournisseur', 
            'password' => $validatedData['password'],
        ]);

        $token = $user->createToken($request->nom)->plainTextToken;

        return response()->json([
            "fournisseur" => $fournisseur,
            "Status Code" => 200,
            "token" => $token
        ] , 200);
    }

    // Mise à jour du profil d'un fournisseur
    public function updateProfile(Request $request, $id)
    {
        $fournisseur = Fournisseur::find($id);
        if ($fournisseur) {
            $request->validate([
                'nom' => 'string|max:255|min:3|nullable',
                'adresse' => 'string|nullable',
                'contact' => 'string|max:255|nullable',
            ]);

            $fournisseur->update($request->only('nom', 'adresse', 'contact'));

            $user = User::where('email', $fournisseur->email)->first();
            if ($user) {
                $user->update(['nom' => $request->nom]);
            }

            return response()->json(['message' => 'Profil mis à jour avec succès', 'fournisseur' => $fournisseur], 200);
        } else {
            return response()->json(['message' => 'Ce fournisseur n\'existe pas!'], 404);
        }
    }

    // Mise à jour du mot de passe d'un fournisseur
    public function updatePassword(Request $request, $id)
    {
        $fournisseur = Fournisseur::find($id);

        if ($fournisseur) {
            $request->validate([
                'password' => 'required|string|min:7|confirmed',
            ]);

            $fournisseur->password = Hash::make($request->password);
            $fournisseur->save();

            $user = User::where('email', $fournisseur->email)->first();
            if ($user) {
                $user->password = Hash::make($request->password);
                $user->save();
            }

            return response()->json(['message' => 'Mot de passe mis à jour avec succès'], 200);
        } else {
            return response()->json(['message' => 'Ce fournisseur n\'existe pas!'], 404);
        }
    }

    // Suppression d'un fournisseur
    public function delete($id)
    {
        $fournisseur = Fournisseur::find($id);
        if ($fournisseur) {
            $user = User::where('email', $fournisseur->email)->first();
            if ($user) {
                $user->delete();
            }

            $fournisseur->delete();
            return response()->json(['message' => 'Fournisseur supprimé avec succès'], 200);
        } else {
            return response()->json(['message' => 'Ce fournisseur n\'existe pas!'], 404);
        }
    }
}
