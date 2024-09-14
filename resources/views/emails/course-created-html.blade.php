<!-- resources/views/emails/course-created-html.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Nouvelle course créée</title>
</head>
<body>
    <h1>Nouvelle course créée</h1>
    <p>Bonjour,</p>
    <p>Une nouvelle course a été créée avec les détails suivants :</p>
    <ul>
        <li><strong>id :</strong> {{ $course->id }}</li>
        <li><strong>Titre :</strong> {{ $course->titre }}</li>
        <li><strong>Description :</strong> {{ $course->description }}</li>
        <li><strong>Adresse de livraison :</strong> {{ $course->adresseLivraison }}</li>
        <li><strong>Type de course :</strong> {{ $course->typeDeCourse }}</li>
    </ul>
    <p>Merci d'utiliser nos services !</p>
    {{ config('app.name') }}
</body>
</html>