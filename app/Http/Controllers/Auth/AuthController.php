<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Client;
use App\Models\Fournisseur;
use App\Models\Livreur;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    //Registrations

    // Création d'un nouveau admin
    public function storeAdmin(Request $request)
    {
        $validatedData = $request->validate([
            'nom' => 'required|string|max:255|min:3',
            'email' => 'required|email|string|max:255|unique:admins,email|unique:users,email',
            'password' => 'required|string|min:7|confirmed'
        ]);

        // Hashage du mot de passe
        $validatedData['password'] = Hash::make($validatedData['password']);

        // Création de l'administrateur
        $admin = Admin::create([
            'nom' => $validatedData['nom'],
            'email' => $validatedData['email'],
            'role' => 'admin',
            'password' => $validatedData['password'],
        ]);

        // Création de l'utilisateur dans la table users
        $user = User::create([
            'nom' => $validatedData['nom'],
            'email' => $validatedData['email'],
            'role' => 'admin',
            'password' => $validatedData['password'],
        ]);

        // Génération du token
        $token = $user->createToken('Admin Token')->plainTextToken;

        return response()->json([
            'Admin' => $admin,
            'status_code' => 200,
            'token' => $token
        ], 200);
    }

    // Création d'un nouveau fournisseur
    public function storeFournisseur(Request $request)
    {
        try {
            // Validation des données
            $validatedData = $request->validate([
                'nom' => 'required|string|max:255|min:3',
                'prenom' => 'required|string|max:255',
                'email' => 'required|email|string|max:255|unique:fournisseurs,email|unique:users,email',
                'adresse' => 'required|string',
                'contact' => 'required|string|max:255',
                'password' => 'required|string|min:7|confirmed',
                'typeDeVehicule' => 'nullable|string|max:50'
            ]);

            // Hashage du mot de passe
            $validatedData['password'] = Hash::make($validatedData['password']);

            // Création du fournisseur
            $fournisseur = Fournisseur::create($validatedData);

            // Création de l'utilisateur dans la table "users"
            $user = User::create([
                'nom' => $validatedData['nom'],
                'prenom' => $validatedData['prenom'],
                'email' => $validatedData['email'],
                'role' => 'Fournisseur',
                'contact' => $validatedData['contact'],
                'adresse' => $validatedData['adresse'],
                'typeDeVehicule' => null,
                'password' => $validatedData['password']
            ]);

            $token = $user->createToken($request->nom)->plainTextToken;

            return response()->json([
                "fournisseur" => $fournisseur,
                "Status Code" => 200,
                "token" => $token
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Une erreur est survenue",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    // Création d'un nouveau client
    public function storeClient(Request $request)
    {
        try {
            // Validation des données
            $validatedData = $request->validate([
                'nom' => 'required|string|max:255|min:3',
                'prenom' => 'required|string|max:255',
                'email' => 'required|email|string|max:255|unique:clients,email|unique:users,email',
                'contact' => 'required|string|max:255',
                'password' => 'required|string|min:7|confirmed',
                'adresse' => 'nullable|string|max:255',
                'typeDeVehicule' => 'nullable|string|max:50'
            ]);

            // Hashage du mot de passe
            $validatedData['password'] = Hash::make($validatedData['password']);

            // Création du client
            $client = Client::create($validatedData);

            // Création de l'utilisateur
            $user = User::create([
                'nom' => $validatedData['nom'],
                'prenom' => $validatedData['prenom'],
                'email' => $validatedData['email'],
                'role' => 'Client',
                'contact' => $validatedData['contact'],
                'adresse' => null,
                'typeDeVehicule' => null,
                'password' => $validatedData['password']
            ]);

            // Génération du token
            $token = $user->createToken('Client Token')->plainTextToken;

            return response()->json([
                "Client" => $client,
                "Status Code" => 200,
                "token" => $token
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Une erreur est survenue",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    // Créer un nouveau livreur
    public function storeLivreur(Request $request)
    {
        // Validation des données
        $validatedData = $request->validate([
            'nom' => 'required|string|max:255|min:3',
            'prenom' => 'required|string|max:255|min:3',
            'email' => 'required|email|string|max:255|unique:livreurs,email|unique:users,email',
            'adresse' => 'required|string|max:255',
            'contact' => 'required|string|max:255',
            'typeDeVehicule' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed'
        ]);

        // Hashage du mot de passe
        $validatedData['password'] = Hash::make($validatedData['password']);

        // Création du livreur
        $livreur = Livreur::create($validatedData);

        // Création de l'utilisateur associé
        $user = User::create([
            'nom' => $validatedData['nom'],
            'email' => $validatedData['email'],
            'role' => 'Livreur',
            'password' => $validatedData['password']
        ]);

        // Génération du token
        $token = $user->createToken('Livreur Token')->plainTextToken;

        return response()->json([
            "Livreur" => $livreur,
            "status_code" => 200,
            "token" => $token
        ], 200);
    }


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
            return response()->json(['error' => 'Email ou mot de passe incorrect'], 401);
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
