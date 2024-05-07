<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback 360º</title>
    <style>
        /* Estilos Generales */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #eeeeee;
            color: #333;
        }
        .container {
            max-width: 650px;
            margin: 40px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            border: 1px solid #d0d0d0;
        }
        .header {
            background-color: #355C7D; /* Dark blue shade */
            color: #ffffff;
            text-align: center;
            padding: 20px;
            border-radius: 12px 12px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 20px;
            font-size: 16px;
            line-height: 1.6;
            color: #555;
        }
        .content p {
            margin: 18px 0;
        }
        .button {
            display: inline-block;
            background-color: #48A9A6; /* Muted teal */
            color: #ffffff;
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            text-align: center;
            width: 100%;
            max-width: 250px; /* Controls the width of the button */
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 14px;
            color: #666;
        }
        .footer a {
            color: #355C7D; /* Link color */
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Feedback 360º</h1>
        </div>
        <div class="content">
            <p>Estimado(a) {{ $evaluador }},</p>
            <p>Está participando en el proceso de Feedback 360º diseñado para identificar fortalezas y áreas de mejora en relación a su liderazgo. Sus respuestas serán tratadas con total confidencialidad y anonimato.</p>
            <p>Por favor, pulse sobre el siguiente enlace para completar el cuestionario:</p>
            <a href="{{ $urlEncuesta }}" target="_blank" class="button" style="color: #ffffff;">Comenzar Encuesta</a>
            <p>Si experimenta dificultades para acceder al enlace, copie y pegue la URL completa en un navegador moderno como Chrome o Safari. Si necesita pausar la encuesta, seleccione "GUARDAR Y COMPLETAR MÁS TARDE". Podrá retomarla utilizando el enlace contenido en este e-mail.</p>
            <p>Este sistema enviará recordatorios automáticos solo a quienes no han finalizado la encuesta.</p>
            <p>Para consultas, comuníquese con el Departamento de Proceso de Datos de X-Factor Consulting Group a través del correo <a href="mailto:support@xfactorcg.com">support@xfactorcg.com</a>.</p>
        </div>
        <div class="footer">
            <p>Gracias por su valiosa colaboración.</p>
            <p><strong>Equipo de X-Factor Consulting Group</strong></p>
        </div>
    </div>
</body>
</html>
