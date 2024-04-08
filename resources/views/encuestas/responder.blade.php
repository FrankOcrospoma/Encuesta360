<?php

use App\Models\Pregunta;
use App\Models\Respuesta;
use App\Models\Encuesta;
use App\Models\Envio;
use App\Models\Detalle_Pregunta; 
$uuid = request()->segment(3); 
$envio = Envio::where('uuid', $uuid)->first();

$encuesta = Encuesta::where('id', $envio->encuesta)->first(); 

$nombreModeloEncuesta = $encuesta->nombre;

$preguntas = Pregunta::whereIn('id', function ($query) use ($encuesta) {
    $query->select('pregunta_id')
          ->from('encuesta_preguntas')
          ->where('encuesta_id', $encuesta->id);
})->get();

$respuestas = Respuesta::all();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario Avanzado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --color-primary: #1e4381; /* Azul oscuro */
            --color-secondary: #1e4381; /* Azul claro */
            --color-accent: #00ff00; /* Verde fosforescente */
            --color-light: #f5f5f5; /* Gris claro */
            --color-dark: #343a40; /* Gris oscuro */
        }
        body {
            background-color: var(--color-light);
        }
    
        .accordion-item {
            margin-bottom: 1rem;
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 2px 5px 0 rgba(0,0,0,0.1);
            transition: box-shadow 0.3s ease-in-out;
        }
        .accordion-item:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .accordion-button {
            font-weight: 600;
            color: var(--color-dark);
            background-color: var(--color-light);
        }
        .accordion-button:not(.collapsed) {
            color: var(--color-light);
            background-color: var(--color-primary);
        }
        .accordion-button:focus {
            box-shadow: none;
        }
        .accordion-body {
            font-size: 16px;
            background-color: var(--color-light);
        }
        .btn-custom {
            background-color: var(--color-secondary);
            border: 0;
            transition: background-color 0.2s ease-in-out;
        }
        .btn-custom:hover {
            background-color: var(--color-accent);
        }
        .form-check-input:checked {
            background-color: var(--color-secondary);
            border-color: var(--color-secondary);
        }
        .card-header-custom {
            background-color: var(--color-primary);
            color: var(--color-light);
            border-bottom: 0;
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
        }
        .d-inline-block {
        display: inline-block;
                margin: 0 auto;
            }


    </style>
</head>
<body>
    <?php if ($envio->estado=='Pendiente'): ?>
    <div class="container mt-5">
        <div class="card">
            <h2 class="text-center card-header-custom"><?php echo $nombreModeloEncuesta; ?></h2>
            <div class="card-body">
                <form method="POST" action="{{ route('guardar.respuestas') }}" class="needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="uuid" value="<?php echo $uuid; ?>">
                    <div class="accordion" id="accordionExample">
                        <?php $index = 1; ?>
                        <?php foreach ($preguntas as $pregunta): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="true" aria-controls="collapse<?php echo $index; ?>">
                                     <?php echo $index; ?>. <?php echo $pregunta->texto; ?>
                                </button>
                            </h2>
                            <div id="collapse<?php echo $index; ?>" class="accordion-collapse" aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#accordionExample">
                                <div class="accordion-body">
                                    <?php if ($pregunta->estado): ?>
                                        <?php
                                        $detalles = Detalle_pregunta::where('pregunta', $pregunta->id)->get();
                                        foreach ($detalles as $detalle):
                                            $respuesta = Respuesta::find($detalle->respuesta);
                                        ?>
                                        <div class="form-check">
                                            <input type="radio" name="detalle[<?php echo $pregunta->id; ?>]" value="<?php echo $detalle->id; ?>" class="form-check-input" id="detalle<?php echo $detalle->id; ?>" required>
                                            <label class="form-check-label" for="detalle<?php echo $detalle->id; ?>"><?php echo $respuesta->texto; ?></label>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <label for="respuestaAbierta<?php echo $pregunta->id; ?>">Tu respuesta:</label>
                                        <textarea class="form-control" name="respuestaAbierta[<?php echo $pregunta->id; ?>]" id="respuestaAbierta<?php echo $pregunta->id; ?>" rows="4" required></textarea>
                                        <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php $index++; ?>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Enviar Respuestas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php else: ?>
        <div class="container text-center mt-5">
            <div class="row">
                <div class="col">
                    <i class="bi bi-check-circle" style="font-size: 4rem; color: #28a745;"></i>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col">
                    <h3>¡Respuestas enviadas con éxito!</h3>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Bootstrap Bundle with Popper -->
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> --}}
</body>
</html>