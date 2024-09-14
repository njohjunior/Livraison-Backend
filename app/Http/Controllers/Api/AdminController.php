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
