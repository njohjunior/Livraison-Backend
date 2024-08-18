<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    // Récupérer tous les clients
    public function index()
    {
        return response()->json(Client::all(), 200);
    }

    // Récupérer un client spécifique
    public function show($id)
    {
        $client = Client::find($id);
        if ($client) {
            return response()->json($client, 200);
        } else {
            return response()->json(['message' => 'Ce client n\'existe pas!'], 404);
        }
    }

    // Création d'un nouveau client
    public function store(Request $request)
    {
        // Validation des données
        $validatedData = $request->validate([
            'nom' => 'required|string|max:255|min:3',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|string|max:255|unique:clients,email|unique:users,email',
            'contact' => 'required|string|max:255',
            'password' => 'required|string|min:7|confirmed'
        ]);

        // Hashage du mot de passe
        $validatedData['password'] = Hash::make($validatedData['password']);

        // Création du client
        $client = Client::create($validatedData);

        // Enregistrement dans la table users pour la gestion des rôles
        $user = User::create([
            'nom' => $validatedData['nom'],
            'email' => $validatedData['email'],
            'role' => 'Client',
            'password' => $validatedData['password']
        ]);

        // Génération du token
        $token = $user->createToken('Client Token')->plainTextToken;

        return response()->json([
            "Client" => $client,
            "Status Code" => 200,
            "token" => $token
        ], 200);
    }

    // Mise à jour du profil du client
    public function updateProfile(Request $request, $id)
    {
        $client = Client::find($id);

        if ($client) {
            $request->validate([
                'nom' => 'string|max:255|min:3|nullable',
                'prenom' => 'string|max:255|nullable',
                'contact' => 'string|max:255|nullable',
            ]);

            $client->update($request->only('nom', 'prenom', 'contact'));

            // Mise à jour dans la table users
            $user = User::where('email', $client->email)->first();
            if ($user && $request->nom) {
                $user->update(['nom' => $request->nom]);
            }

            return response()->json(['message' => 'Profil mis à jour avec succès', 'client' => $client], 200);
        } else {
            return response()->json(['message' => 'Client introuvable'], 404);
        }
    }

    // Mise à jour du mot de passe du client
    public function updatePassword(Request $request, $id)
    {
        $client = Client::find($id);

        if ($client) {
            $request->validate([
                'password' => 'required|string|min:7|confirmed',
            ]);

            $client->password = Hash::make($request->password);
            $client->save();

            // Mise à jour dans la table users
            $user = User::where('email', $client->email)->first();
            if ($user) {
                $user->password = Hash::make($request->password);
                $user->save();
            }

            return response()->json(['message' => 'Mot de passe mis à jour avec succès'], 200);
        } else {
            return response()->json(['message' => 'Client introuvable'], 404);
        }
    }

    // Suppression d'un client
    public function delete($id)
    {
        $client = Client::find($id);
        if ($client) {
            $client->delete();
            $user = User::where('email', $client->email)->first();
            if ($user) {
                $user->delete();
            }

            return response()->json(['message' => 'Client supprimé avec succès'], 200);
        } else {
            return response()->json(['message' => 'Client introuvable'], 404);
        }
    }
}
