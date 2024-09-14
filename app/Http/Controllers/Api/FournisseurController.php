<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fournisseur;
use App\Models\Livreur;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    //Obtenir la liste des livreurs créé par un fournisseur
    public function hasLivreur()
    {
        try {
            // Récupérer l'utilisateur authentifié
            $fournisseur = Auth::user();

            // Récupérer le fournisseur à partir de son email dans la table fournisseur
            $fournisseur = DB::table('fournisseurs')
                ->select('id')
                ->where('email', $fournisseur->email)
                ->first();

            if ($fournisseur) {
                // Récupérer les livreurs associés au fournisseur
                $livreurs = DB::table('fournisseur_livreurs')
                    ->select('livreur_id')
                    ->where('fournisseur_id', '=', $fournisseur->id)
                    ->get();

                // Vérifier si des livreurs existent
                if ($livreurs->isEmpty()) {
                    return response()->json([
                        "message" => "Aucun livreur n'est associé à ce fournisseur."
                    ], 200);
                } else {
                    // Récupérer les détails de tous les livreurs associés
                    $livreursIds = $livreurs->pluck('livreur_id'); // Pluck IDs
                    $livreursDetails = DB::table('livreurs')
                        ->whereIn('id', $livreursIds)
                        ->get();

                    return response()->json([
                        "message" => "Des livreurs sont associés à ce fournisseur.",
                        "livreurs" => $livreursDetails
                    ], 200);
                }
            } else {
                return response()->json([
                    "message" => "Fournisseur non authentifié."
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Une erreur est survenue",
                "error" => $e->getMessage()
            ], 500);
        }
    }


    //Création d'un livreur par un fournisseur
    public function createLivreur(Request $request)
    {
        try {
            // Validation des champs
            $validatedData = $request->validate([
                'email' => 'required|email|string|max:255|unique:livreurs,email|unique:users,email',
                'typeDeVehicule' => 'required|string|max:255',
            ]);

            // Générer le mot de passe basé sur l'email
            $password = Hash::make($validatedData['email']);

            // Création du livreur
            $livreur = Livreur::create([
                "nom" => "livreur",
                "prenom" => "null",
                "email" => $validatedData['email'],
                "adresse" => "null",
                "contact" => "null",
                "typeDeVehicule" => $validatedData['typeDeVehicule'],
                "password" => $password,
            ]);

            // Création de l'utilisateur associé
            $user = User::create([
                "nom" => "livreur",
                "prenom" => "null",
                "email" => $validatedData['email'],
                'role' => 'Livreur',
                "adresse" => "null",
                "contact" => "null",
                "typeDeVehicule" => $validatedData['typeDeVehicule'],
                "password" => $password,
            ]);

            // Récupérer l'utilisateur authentifié
            $fournisseur = Auth::user();

            if (!$fournisseur) {
                return response()->json([
                    "message" => "Utilisateur non authentifié"
                ], 401);
            }

            // Récupérer le fournisseur à partir de son email
            $fournisseur = DB::table('fournisseurs')
                ->select('id')
                ->where('email', $fournisseur->email)
                ->first();

            if (!$fournisseur) {
                return response()->json([
                    "message" => "Fournisseur non trouvé"
                ], 404);
            }

            // Ajouter une entrée dans la table fournisseur_livreurs pour établir la relation
            DB::table('fournisseur_livreurs')->insert([
                'fournisseur_id' => $fournisseur->id, // Utiliser l'ID du fournisseur
                'livreur_id' => $livreur->id,         // Utiliser l'ID du livreur nouvellement créé
            ]);

            return response()->json([
                "message" => "Livreur créé avec succès",
                "livreur" => $livreur
            ], 200);
        } catch (\Exception $e) {
            // Gestion des erreurs
            return response()->json([
                "message" => "Une erreur est survenue",
                "error" => $e->getMessage()
            ], 500);
        }
    }


    // Mise à jour du profil d'un fournisseur
    public function updateProfile(Request $request, $id)
    {
        $fournisseur = Fournisseur::find($id);
        if ($fournisseur) {
            $request->validate([
                'nom' => 'string|max:255|min:3|nullable',
                'prenom' => 'string|max:255|min:3|nullable',
                'adresse' => 'string|nullable',
                'contact' => 'string|max:255|nullable',
            ]);

            $fournisseur->update($request->only('nom', 'prenom', 'adresse', 'contact'));

            $user = User::where('email', $fournisseur->email)->first();
            if ($user) {
                $user->update($request->only('nom', 'prenom', 'adresse', 'contact'));
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
