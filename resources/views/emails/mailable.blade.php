<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encuesta</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: #f4f4f4;
            padding: 20px;
            border-radius: 8px;
        }
        h1 {
            color: #444;
        }
        p {
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>¡Tu opinión es importante para nosotros!</h1>
        <p>Querido participante,</p>
        <p>Hemos preparado una encuesta para entender mejor tus necesidades y cómo podemos mejorar. Tu feedback es muy valioso para nosotros.</p>
        <p>Por favor, toma unos minutos para completar nuestra encuesta.</p>
        <a href="{{ $urlEncuesta }}" target="_blank"style="display: inline-block; background-color: #007bff; color: #ffffff; padding: 10px 20px; margin: 20px 0; border-radius: 5px; text-decoration: none;">Iniciar Encuesta</a>
        <p>Gracias por tu tiempo,</p>
        <p><strong>El Equipo de [Nombre de tu Empresa/Proyecto]</strong></p>
    </div>
</body>
</html>
