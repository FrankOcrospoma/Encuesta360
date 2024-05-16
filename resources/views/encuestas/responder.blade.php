<?php

use App\Models\Pregunta;
use App\Models\Respuesta;
use App\Models\Encuesta;
use App\Models\Envio;
use App\Models\Formulario;
use App\Models\Detalle_Pregunta;
use App\Models\Persona_Respuesta;

$uuid = request()->segment(3); 
$envio = Envio::where('uuid', $uuid)->first();

$encuesta = Encuesta::where('id', $envio->encuesta)->first(); 

$nombreModeloEncuesta = $encuesta->nombre;

$preguntas = Pregunta::select('preguntas.*')
    ->distinct()
    ->join('detalle_preguntas as dp', 'dp.pregunta', '=', 'preguntas.id')
    ->whereIn('dp.id', function($query) use ($encuesta) {
        $query->select('detalle_id')
              ->from('formularios')
              ->where('id', $encuesta->formulario_id); 
    })
    ->orderBy('preguntas.categoria')  
    ->get();


$formulario = Formulario::where('id', $encuesta->formulario_id)->first(); 
$respuestas = Respuesta::where('vigencia',1)->get();

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
        .bi-star-fill {
                color: #ccc; /* Cambiar el color a gris claro */
            }

                    .form-check-label {
                display: flex;
                align-items: center;
            }
            .bi-star-fill.amarillo {
                color: #ffc107; /* Amarillo */
            }


            .form-check-label i {
                margin-left: 5px; /* Espaciado entre texto y estrellas */
            }

                .categoria-titulo {
                    font-size: 1.5rem; /* Tamaño de fuente */
                    font-weight: bold; /* Negrita */
                    color: var(--color-primary); /* Color primario */
                    margin-top: 2rem; /* Margen superior */
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
@if (session('error'))
    <div class="alert alert-danger" role="alert">
        {{ session('error') }}
    </div>
@endif

<body>
    <?php if ($envio->estado=='Pendiente'): ?>
    <div class="container mt-5">
        <div class="card">
            <div style="padding: 15px" class="card-header-custom d-flex justify-content-between align-items-center">
                <h2> {{$encuesta->Empresa}}</h2>
                <h2>{{$nombreModeloEncuesta}}   </h2>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('guardar.respuestas') }}" class="needs-validation" id="formularioEncuesta">
                    @csrf
                    <input type="hidden" name="uuid" value="<?php echo $uuid; ?>">
                    <input type="hidden" name="accion" value="definitivo" id="accionFormulario">  <!-- Input para la acción -->

                    <div class="accordion" id="accordionExample">
                        <?php $index = 1; ?>
                        <?php $currentCategoria = null; ?>
                        {{-- Mostrar preguntas de opción múltiple --}}
                        <?php foreach ($preguntas->where('estado', true) as $pregunta): ?>
                            <?php
                            if ($currentCategoria != $pregunta->Categoria) {
                                $currentCategoria = $pregunta->Categoria;
                                echo "<h2 class='categoria-titulo'>{$currentCategoria}</h2>"; // Imprimir el título de la categoría
                            }
                            ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="true" aria-controls="collapse<?php echo $index; ?>">
                                        <?php echo $index; ?>. <?php echo $pregunta->texto; ?>
                                    </button>
                                </h2>
                                <div id="collapse<?php echo $index; ?>" class="accordion-collapse" aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <?php
                                        $detalles = Detalle_Pregunta::join('formularios', 'formularios.detalle_id', '=', 'detalle_preguntas.id')->join('respuestas', 'respuestas.id', '=', 'detalle_preguntas.respuesta')
                                            ->where('detalle_preguntas.pregunta', $pregunta->id)
                                            ->where('formularios.id', $formulario->id)
                                            ->select('detalle_preguntas.*')
                                            ->orderBy('respuestas.score')  // Selecciona todos los campos de detalle_preguntas
                                            ->get();
                                        // Obtener la respuesta guardada si existe
                                        $respuestaGuardada = Persona_Respuesta::where('persona', $envio->persona)
                                       ->where('encuesta_id', $envio->encuesta)
                                       ->whereIn('detalle', function($query) use ($pregunta) {
                                           $query->select('id')->from('detalle_preguntas')->where('pregunta', $pregunta->id);
                                       })->first();

                                       ?>
                                        
                                        <?php foreach ($detalles as $detalle): ?>
                                        <?php
                                        $respuesta = Respuesta::find($detalle->respuesta);
                                        $score = $respuesta->score; // Suponemos que cada respuesta tiene un 'score' asociado
                                        ?>
                                        <div class="form-check">
                                            <input type="radio" name="detalle[{{ $pregunta->id }}]" value="{{ $detalle->id }}"
                                                class="form-check-input" id="detalle{{ $detalle->id }}"
                                                required {{ $respuestaGuardada && $respuestaGuardada->detalle == $detalle->id ? 'checked' : '' }}>
                                            <label class="form-check-label" for="detalle{{ $detalle->id }}">
                                                {{ $respuesta->texto }}
                                                <span id="stars{{ $detalle->id }}">
                                                    <?php for ($i = 0; $i < $score; $i++): ?>
                                                        <i class="bi bi-star-fill"></i> <!-- Estrellas llenas ahora en gris -->
                                                    <?php endfor; ?>
                                                    <?php for ($i = $score; $i < 5; $i++): ?>
                                                        <i class="bi bi-star" style="color: #ccc;"></i> <!-- Estrellas vacías -->
                                                    <?php endfor; ?>
                                                </span>
                                            </label>
                                        </div>
                                    <?php endforeach ?>
                                    
                                  
                                    </div>
                                </div>
                            </div>
                            <?php $index++; ?>
                        <?php endforeach; ?>
                        <h2 class='categoria-titulo'>Preguntas Abiertas</h2>
                        {{-- Mostrar preguntas abiertas --}}
                        <?php foreach ($preguntas->where('estado', false) as $pregunta): ?>
                
                        <?php
                            // Obtener la respuesta guardada si existe
                            $respuestaGuardada = Persona_Respuesta::where('persona', $envio->persona)
                                        ->where('encuesta_id', $envio->encuesta)
                                        ->whereIn('detalle', function($query) use ($pregunta) {
                                            $query->select('id')->from('detalle_preguntas')->where('pregunta', $pregunta->id);
                                        })->first();

                            // Inicializa $respuestatexto como null para manejar casos donde no hay respuesta guardada
                            $respuestatexto = null;

                            // Verifica si se encontró una respuesta guardada antes de intentar acceder a la propiedad 'detalle'
                            if ($respuestaGuardada) {
                                $respuestatexto = Respuesta::join('detalle_preguntas', 'respuestas.id', '=', 'detalle_preguntas.respuesta')
                                                            ->where('detalle_preguntas.id', $respuestaGuardada->detalle)
                                                            ->select('respuestas.*')  // Selecciona todos los campos de detalle_preguntas
                                                            ->first();
                                }
                                ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="true" aria-controls="collapse<?php echo $index; ?>">
                                        <?php echo $index; ?>. <?php echo $pregunta->texto; ?>
                                    </button>
                                </h2>
                                <div id="collapse<?php echo $index; ?>" class="accordion-collapse" aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <label for="respuestaAbierta<?php echo $pregunta->id; ?>">Tu respuesta:</label>
                                        <textarea class="form-control" name="respuestaAbierta[{{ $pregunta->id }}]" id="respuestaAbierta{{ $pregunta->id }}" rows="4" required>{{ $respuestatexto->texto ?? '' }} </textarea>
                                    </div>
                                </div>
                            </div>
                            <?php $index++; ?>
                            <?php endforeach; ?>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="button" onclick="guardarBorrador()" class="btn btn-secondary">GUARDAR Y COMPLETAR MÁS TARDE</button>
                        <button type="submit" class="btn btn-outline-primary"><i class="bi bi-send"></i> ENVIAR RESPUESTAS</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php endif; ?>
    <?php if($envio->estado=='Finalizado'): ?>
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
    <?php if($envio->estado=='Borrador'): ?>
    <div class="container text-center mt-5">
        <div class="row">
            <div class="col">
                <i class="bi bi-file-earmark" style="font-size: 4rem; color: #28a745;"></i>
            </div>
        </div>
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <div class="row mt-3">
            <div class="col">
                <h3>¡Respuestas guardadas en borrador!</h3>

                <button class="btn btn-primary" onclick="continuarEncuesta()">Continuar Encuesta</button>
            </div>
        </div>
    </div>
<?php endif; ?>

    <!-- Bootstrap Bundle with Popper -->
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> --}}
    <script>
function continuarEncuesta() {
    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    if (!csrfTokenMeta) {
        alert('Token CSRF no encontrado. Asegúrese de que el meta tag está presente en el HTML.');
        return;
    }
    const csrfToken = csrfTokenMeta.getAttribute('content');
    const uuid = '<?php echo $uuid; ?>'; // Asegúrate de que el UUID está disponible en JavaScript
    console.log(uuid)
    fetch('/continuar-encuesta/' + uuid, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken // Token CSRF para seguridad
        },
        body: JSON.stringify({ estado: 'Pendiente' })
    })
    .then(response => {
        if (response.ok) {
            location.reload(); // Recarga la página para reflejar los cambios
        } else {
            alert('No se pudo actualizar el estado de la encuesta. Intente nuevamente.');
        }
    })
    .catch(error => {
        console.error('Error al enviar la solicitud:', error);
        alert('Error al procesar la solicitud.');
    });
}
document.addEventListener('DOMContentLoaded', function () {
    // Función para actualizar el color de las estrellas basado en el estado de los radio buttons
    function actualizarEstrellas() {
        document.querySelectorAll('.form-check-input').forEach(input => {
            const label = input.nextElementSibling;
            const stars = label.querySelector('span');
            if (input.checked) {
                stars.querySelectorAll('.bi-star-fill').forEach(star => {
                    star.classList.add('amarillo');
                });
            } else {
                stars.querySelectorAll('.bi-star-fill').forEach(star => {
                    star.classList.remove('amarillo');
                });
            }
        });
    }

    // Escucha de eventos para cambios en los inputs
    document.querySelectorAll('.accordion-item').forEach(item => {
        item.querySelectorAll('.form-check-input').forEach(input => {
            input.addEventListener('change', function() {
                // Primero, elimina la clase 'amarillo' de todas las estrellas dentro del mismo item de acordeón
                item.querySelectorAll('.bi-star-fill').forEach(star => {
                    star.classList.remove('amarillo');
                });

                // Luego, añade la clase 'amarillo' solo a las estrellas dentro del mismo label que el input seleccionado
                const label = input.nextElementSibling;
                const stars = label.querySelector('span');
                stars.querySelectorAll('.bi-star-fill').forEach(star => {
                    star.classList.add('amarillo');
                });
            });
        });
    });

    // Llamar a actualizarEstrellas al cargar la página para manejar los inputs preseleccionados
    actualizarEstrellas();
});

function guardarBorrador() {
    document.getElementById('accionFormulario').value = 'borrador';
    document.getElementById('formularioEncuesta').submit();
}

document.getElementById('formularioEncuesta').addEventListener('submit', function(e) {
    let todasRespondidas = true;
    document.querySelectorAll('.accordion-item').forEach(function(pregunta) {
        if (pregunta.querySelector('textarea')) {
            let respuestaAbierta = pregunta.querySelector('textarea').value.trim();
            if (!respuestaAbierta) {
                todasRespondidas = false;
            }
        } else {
            let opciones = pregunta.querySelectorAll('input[type="radio"]');
            let algunaSeleccionada = Array.from(opciones).some(opcion => opcion.checked);
            if (!algunaSeleccionada) {
                todasRespondidas = false;
            }
        }
    });

    if (!todasRespondidas) {
        e.preventDefault(); // Evita el envío del formulario
        alert('Por favor, responda todas las preguntas de la encuesta.');
    }
});

        </script>
        
</body>
</html>
