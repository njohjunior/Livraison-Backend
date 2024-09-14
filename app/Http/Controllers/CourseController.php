<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Mail\CourseCreated;
use App\Models\LivreurCourse;
use Illuminate\Support\Facades\Mail;

class CourseController extends Controller
{
    //Récupérer les courses disponible (statut : disponibles)
    public function index()
    {
        try {
            // Récupère toutes les courses avec tri par date de création (les plus récentes en premier) et pagination
            $courses = Course::where('statut', 'Disponible')->orderBy('created_at', 'desc')->paginate(10);
            return response()->json($courses, 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la récupération des courses.'
            ], 500);
        }
    }

    // Créer une nouvelle course
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'titre' => 'required|string|max:255',
                'description' => 'nullable|string',
                'adresseRamassage' => 'required|string|max:255',
                'adresseLivraison' => 'required|string|max:255',
                'emailClient' => 'required|email|max:255',
                'typeDeCourse' => 'required|in:Normal,Express,Fragile',
            ]);

            $fournisseur = Auth::user();

            if (!$fournisseur) {
                return response()->json([
                    "message" => "Utilisateur non authentifié"
                ], 401);
            }

            $fournisseur = Fournisseur::where('email', $fournisseur->email)->firstOrFail();

            $course = DB::transaction(function () use ($fournisseur, $validatedData) {
                return Course::create([
                    "fournisseur_id" => $fournisseur->id,
                    "titre" => $validatedData['titre'],
                    "description" => $validatedData['description'],
                    'adresseRamassage' => $validatedData['adresseRamassage'],
                    'adresseLivraison' => $validatedData['adresseLivraison'],
                    'emailClient' => $validatedData['emailClient'],
                    'typeDeCourse' => $validatedData['typeDeCourse'],
                    'statut' => 'Disponible'
                ]);
            });

            // Envoi de l'e-mail
            Mail::to($validatedData['emailClient'])->send(new CourseCreated($course));

            return response()->json([
                "message" => "Course créée avec succès et e-mail envoyé au client",
                "course" => $course,
                "emailSent" => true
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Une erreur est survenue lors de la création de la course",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    //Accepter une course
    public function acceptCourse(Request $request)
    {
        // Validation des données
        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        // Trouver la course disponible
        $course = Course::where('id', $request->course_id)
            ->where('statut', 'Disponible')
            ->first();

        if (!$course) {
            return response()->json(['message' => 'Course non disponible.'], 404);
        }

        // Récupérer l'ID du livreur connecté
        $livreur = Auth::user();
        $livreurId = DB::table('livreurs')
            ->select('id')
            ->where('email', $livreur->email)
            ->first();

        if (!$livreurId) {
            return response()->json(['message' => 'Livreur non trouvé.'], 404);
        }

        // Convertir l'ID du livreur en entier
        $livreurId = (int) $livreurId->id;

        // Vérifier si le livreur a déjà accepté la course
        $alreadyAccepted = LivreurCourse::where('course_id', $request->course_id)
            ->where('livreur_id', $livreurId)
            ->exists();

        if ($alreadyAccepted) {
            return response()->json(['message' => 'Vous avez déjà accepté cette course.'], 400);
        }

        // Insérer l'acceptation dans la table livreur_course
        LivreurCourse::create([
            'course_id' => $request->course_id,
            'livreur_id' => $livreurId,
        ]);

        // Mettre à jour le statut de la course
        $course->statut = 'Acceptée';
        $course->save();

        return response()->json(['message' => 'Course acceptée avec succès.']);
    }
}
