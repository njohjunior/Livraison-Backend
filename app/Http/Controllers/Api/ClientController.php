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

    // Mise a jour du profil
    public function updateProfile(Request $request, $id)
    {
        $user = User::find($id);;

        if ($user) {
            $validatedData = $request->validate([
                'nom' => 'string|max:255|min:3|nullable',
                'prenom' => 'string|max:255|nullable',
                'adresse' => 'nullable|string|max:255|nullable',
                'contact' => 'string|max:255|nullable',
            ]);

            $user->update($validatedData);

            // Mise à jour dans la table users
            $client = Client::where('email', $user->email)->first();
            if ($user) {
                $client->update($request->only('nom', 'prenom', 'adresse', 'contact'));
            }

            return response()->json($user , 200);
        } else {
            return response()->json(['message' => 'Client introuvable'], 404);
        }
    }

    public function updatePassword(Request $request, $id)
    {
        $client = Client::find($id);

        if ($client) {
            $request->validate([
                'password' => 'required|string|min:7|confirmed',
            ]);

            $client->update(['password' => Hash::make($request->password)]);

            // Mise à jour dans la table users
            $user = User::where('email', $client->email)->first();
            if ($user) {
                $user->update(['password' => Hash::make($request->password)]);
            }

            return response()->json(['message' => 'Mot de passe mis à jour avec succès'], 200);
        } else {
            return response()->json(['message' => 'Client introuvable'], 404);
        }
    }

    public function delete($id)
    {
        $client = Client::find($id);
        if ($client) {
            $user = User::where('email', $client->email)->first();
            if ($user) {
                $user->delete();
            }

            $client->delete();

            return response()->json(['message' => 'Client supprimé avec succès'], 200);
        } else {
            return response()->json(['message' => 'Client introuvable'], 404);
        }
    }
}
