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
                'latitudeRamassage' => 'nullable|numeric',
                'longitudeRamassage' => 'nullable|numeric',
                'latitudeLivraison' => 'nullable|numeric',
                'longitudeLivraison' => 'nullable|numeric',
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
                    'statut' => 'Disponible',
                    'latitudeRamassage' => $validatedData['latitudeRamassage'],
                    'longitudeRamassage' => $validatedData['longitudeRamassage'],
                    'latitudeLivraison' => $validatedData['latitudeLivraison'],
                    'longitudeLivraison' => $validatedData['longitudeLivraison'],
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

    // Créer une nouvelle course à partir d'un site d'e-commerce
    public function storeFrom(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'titre' => 'required|string|max:255',
                'description' => 'nullable|string',
                'adresseRamassage' => 'required|string|max:255',
                'adresseLivraison' => 'required|string|max:255',
                'emailClient' => 'required|email|max:255',
                'typeDeCourse' => 'required|in:Normal,Express,Fragile',
                'latitudeRamassage' => 'nullable|numeric',
                'longitudeRamassage' => 'nullable|numeric',
                'latitudeLivraison' => 'nullable|numeric',
                'longitudeLivraison' => 'nullable|numeric',
            ]);

            // Définir les coordonnées de ramassage si elles ne sont pas fournies
            $latitudeRamassage = $validatedData['latitudeRamassage'] ?? 3.8314046;
            $longitudeRamassage = $validatedData['longitudeRamassage'] ?? 11.4713942;
            $adresseRamassage = $validatedData['adresseRamassage'] ?? "Entrée Simbock, Route de Kribi, Mendong, Yaoundé VI, Yaoundé, Mfoundi, Centre, Cameroon";

            $course = DB::transaction(function () use ($validatedData, $latitudeRamassage, $longitudeRamassage, $adresseRamassage) {
                return Course::create([
                    "fournisseur_id" => 5,
                    "titre" => $validatedData['titre'],
                    "description" => $validatedData['description'],
                    'adresseRamassage' => $adresseRamassage,
                    'adresseLivraison' => $validatedData['adresseLivraison'],
                    'emailClient' => $validatedData['emailClient'],
                    'typeDeCourse' => $validatedData['typeDeCourse'],
                    'statut' => 'Disponible',
                    'latitudeRamassage' => $latitudeRamassage,
                    'longitudeRamassage' => $longitudeRamassage,
                    'latitudeLivraison' => $validatedData['latitudeLivraison'],
                    'longitudeLivraison' => $validatedData['longitudeLivraison'],
                ]);
            });

            // Envoi de l'e-mail de confirmation au client
            Mail::to($validatedData['emailClient'])->send(new CourseCreated($course));

            return response()->json([
                "message" => "Course créée avec succès et e-mail envoyé au client",
                "course" => $course,
                "emailSent" => true
            ], 201);
        } catch (\Exception $e) {
            // Gérer les erreurs et les renvoyer
            return response()->json([
                "message" => "Une erreur est survenue lors de la création de la course",
                "error" => $e->getMessage()
            ], 500);
        }
    }



    // Accepter une course
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

    // Courses créées par le fournisseur connecté
    public function mesCourses()
    {
        // Récupérer l'utilisateur connecté
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                "message" => "Utilisateur non authentifié"
            ], 401);
        }

        $fournisseur = Fournisseur::where('email', $user->email)->first();

        if (!$fournisseur) {
            return response()->json([
                "message" => "Fournisseur introuvable"
            ], 404);
        }

        // Récupérer uniquement les courses du fournisseur connecté via son ID
        $courses = Course::where('fournisseur_id', $fournisseur->id)->get();

        return response()->json($courses);
    }


    //Obtenir la liste des courses accepteé par un livreur
    public function hasCourse()
    {
        try {
            // Récupérer l'utilisateur authentifié
            $livreur = Auth::user();

            // Récupérer le livreur à partir de son email dans la table livreurs
            $livreur = DB::table('livreurs')
                ->select('id')
                ->where('email', $livreur->email)
                ->first();

            if ($livreur) {
                // Récupérer des courses associés au livreur
                $courses = DB::table('livreur_courses')
                    ->select('course_id')
                    ->where('livreur_id', '=', $livreur->id)
                    ->get();

                // Vérifier si les courses existent
                if ($courses->isEmpty()) {
                    return response()->json([
                        "message" => "Aucune course n'est associé à ce livreur."
                    ], 200);
                } else {
                    // Récupérer les détails de tous les livreurs associés
                    $coursesIds = $courses->pluck('course_id'); // Pluck IDs
                    $coursesDetails = DB::table('courses')
                        ->whereIn('id', $coursesIds)
                        ->get();

                    return response()->json([
                        "message" => "Des courses sont associés à ce livreur.",
                        "livreurs" => $coursesDetails
                    ], 200);
                }
            } else {
                return response()->json([
                    "message" => "Livreur non authentifié."
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Une erreur est survenue",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    // Méthode pour terminer la course
    public function completeCourse($id)
    {
        $course = Course::find($id);
        if ($course) {
            $course->statut = 'Terminée';
            $course->save();
            return response()->json(['message' => 'Course terminée avec succès.'], 200);
        } else {
            return response()->json(['message' => 'Course non trouvée.'], 404);
        }
    }

    //suppression
    public function destroy($id)
    {
        // Récupérer l'utilisateur connecté
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                "message" => "Utilisateur non authentifié"
            ], 401);
        }

        $fournisseur = Fournisseur::where('email', $user->email)->first();

        if (!$fournisseur) {
            return response()->json([
                "message" => "Fournisseur introuvable"
            ], 404);
        }

        $course = Course::find($id);

        // Vérifier que la course appartient bien au fournisseur connecté
        if ($course && $course->fournisseur_id === $fournisseur->id) {
            $course->delete();
            return response()->json(['message' => 'Course supprimée avec succès']);
        } else {
            return response()->json(['message' => 'Non autorisé ou course introuvable'], 403);
        }
    }


    // Récupérer une course
    public function show($id)
    {
        $course = Course::find($id);
        return response()->json($course);
    }
}
