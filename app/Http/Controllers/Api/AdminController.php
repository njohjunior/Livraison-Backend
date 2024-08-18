<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index() 
    {
        return response()->json(Admin::all(), 200);
    }

    public function store(Request $request)
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

    public function delete($id)
    {
        $admin = Admin::find($id);

        if (!$admin) {
            return response()->json(['message' => 'Cet administrateur n\'existe pas!'], 404);
        }

        // Suppression dans la table users si l'utilisateur existe
        $user = User::where('email', $admin->email)->first();
        if ($user) {
            $user->delete();
        }

        $admin->delete();
        return response()->json(['message' => 'Administrateur supprimé avec succès'], 200);
    }
}
