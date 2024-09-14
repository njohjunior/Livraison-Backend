<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            "nom" => "required|string|min:3",
            "email" => "required|email|max:255",
            "message" => "required"
        ]);

        try {
            $message = Message::create($validatedData);

            return response()->json([
                "message" => $message,
                "status_code" => 200,
                "message_de_succes" => "Message envoyé avec succès"
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Erreur lors de l'envoi du message",
                "status_code" => 500,
                "error" => $e->getMessage()
            ], 500);
        }
    }
}
