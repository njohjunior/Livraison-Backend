<!DOCTYPE html>
<html>

<head>
    <title>Confirmation de livraison</title>
</head>

<body>
    <h2>Votre livraison pour la course {{ $course->id }} a été terminée par le livreur.</h2>

    <p>Veuillez confirmer que vous avez bien reçu la livraison.</p>

    <p>Merci pour votre confiance !</p>

    <!-- Bouton de confirmation -->
    <p>
        <a href="http://localhost:3000/confirm/{{ $course->id }}" style="
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 10px;
        ">Confirmer la réception</a>
    </p>
</body>

</html>