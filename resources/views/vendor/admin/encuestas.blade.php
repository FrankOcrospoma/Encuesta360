@component('admin::layouts.app')


<?php

use App\Models\Pregunta;
use App\Models\Respuesta;
use App\Models\Personal; // Asegúrate de usar el modelo correcto para tu caso.
use App\Models\Encuesta;
// Obtener todas las preguntas y todas las respuestas
$preguntas = Pregunta::all();
$respuestas = Respuesta::all()->take(5); // Tomar solo las primeras 5 respuestas

// Obtener todos los usuarios (o el modelo correspondiente) para el campo destinatario
$usuarios = Personal::all(); // Asegúrate de ajustar esto según tu caso.
$encuestas = Encuesta::all();
?>
<ul class="nav nav-tabs">
    <li class="nav-item"><a class="nav-link active" id="crearEncuestaLink">Crear Encuesta</a></li>
    <li class="nav-item"><a class="nav-link" id="enviarEncuestaLink">Enviar Encuesta</a></li>
</ul>

<div id="crearEncuestaBlock" class="mt-4">
    <div class="card">
        <form class="form-horizontal" method="POST" action="{{ route('encuestas.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                <!-- Titulo Input -->
                <div class='form-group'>
                    <label for='input-titulo' class='col-sm-2 control-label'> {{ __('Titulo') }}</label>
                    <input type='text' name='nombre' id='input-titulo' class="form-control @error('nombre') is-invalid @enderror" placeholder='' autocomplete='on'>
                    @error('nombre') <div class='invalid-feedback'>{{ $message }}</div> @enderror
                </div>
                <!-- Empresa Input -->
                <div class='form-group'>
                    <label for='input-empresa' class='col-sm-2 control-label'> {{ __('Empresa') }}</label>
                    <select name='empresa' id='input-empresa' class="form-control @error('empresa') is-invalid @enderror">
                        @foreach(getCrudConfig('Personal')->inputs()['empresa']['select'] as $key => $value)
                        <option value='{{ $key }}'>{{ $value }}</option>
                        @endforeach
                    </select>
                    @error('empresa') <div class='invalid-feedback'>{{ $message }}</div> @enderror
                </div>
                <!-- Fecha Input -->
                <div class='form-group'>
                    <label for='input-fecha' class='col-sm-2 control-label'> {{ __('Fecha') }}</label>
                    <input type='date' name='fecha' id='input-fecha' class="form-control @error('fecha') is-invalid @enderror" autocomplete='on'>
                    @error('fecha') <div class='invalid-feedback'>{{ $message }}</div> @enderror
                </div>

                <!-- Selección de Preguntas -->
                <div class="form-group">
                    <label for="accordionPreguntas" class="col-sm-2 control-label">{{ __('Preguntas') }}</label>

                    <div class="accordion" id="accordionPreguntas">
                        <div class="mt-3 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="selectAllPreguntas">
                                <label class="form-check-label" for="selectAllPreguntas">
                                    Seleccionar todas las preguntas y respuestas
                                </label>
                            </div>
                        </div>

                        <div class="row"> <!-- Añadido para dividir en dos columnas -->
                        @foreach($preguntas as $index => $pregunta)
    <input type="hidden" name="todasLasPreguntas[]" value="{{ $pregunta->id }}">                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header" id="heading{{ $index }}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h2 class="mb-0">
                                                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse{{ $index }}" aria-expanded="true" aria-controls="collapse{{ $index }}">
                                                    {{ $pregunta->texto }}
                                                </button>
                                            </h2>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input preguntaCheck" type="checkbox" id="pregunta{{ $pregunta->id }}" data-index="{{ $index }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div id="collapse{{ $index }}" class="collapse" aria-labelledby="heading{{ $index }}" data-parent="#accordionPreguntas">
                                        <div class="card-body">
                                            @if($pregunta->estado) <!-- Asegúrate de ajustar la comprobación según cómo se almacena el estado en tu modelo Pregunta -->
                                            <div class="form-check">
                                                <input class="form-check-input respuestaCheck{{ $index }} select-all" type="checkbox" value="all" id="selectAll{{ $index }}">
                                                <label class="form-check-label" for="selectAll{{ $index }}">
                                                    Todas
                                                </label>
                                            </div>
                                            @foreach($respuestas as $respuesta)
                                            <div class="form-check">
                                                
                                                <input class="form-check-input respuestaCheck{{ $index }}" type="checkbox" name="preguntasSeleccionadas[{{ $pregunta->id }}][]" value="{{ $respuesta->id }}" id="defaultCheck{{ $respuesta->id }}">
                                                <label class="form-check-label" for="defaultCheck{{ $respuesta->id }}">
                                                    {{ $respuesta->texto }}
                                                </label>
                                            </div>
                                            @endforeach
                                            @else
                                            <p>No hay respuestas para esta pregunta.</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach

                        </div>
                    </div>
                </div>

            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-info ml-4">{{ __('Create') }}</button>
                <a href="@route(getRouteName().'.encuesta.read')" class="btn btn-default float-left">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</div>

<div id="enviarEncuestaBlock" class="mt-4" style="display: none;">
    <h2>Enviar Encuesta por Correo</h2>
    <div class="card">
        <form class="form-horizontal card-body" method="POST" action="{{ route('enviar.encuesta') }}">
            @csrf
            <!-- Campo Encuesta -->
            <div class="form-group">
                <label for="encuesta" class="col-sm-3 control-label">Encuesta:</label>
                <div class="col-sm-9">
                    <select name="encuesta" id="encuesta" class="form-control" required>
                        <option value="">Seleccione una encuesta</option>
                        @foreach($encuestas as $encuesta)
                        <option value="{{ $encuesta->id }}">{{ $encuesta->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="destinatario" class="col-sm-3 control-label">Destinatario:</label>
                <div class="col-sm-9">
                    <select name="destinatarioCorreo" id="destinatarioCorreo" class="form-control" required>
                        <option value="">Seleccione un correo electrónico</option>
                        @foreach($usuarios as $usuario)
                        <option value="{{ $usuario->correo }}">{{ $usuario->correo }}</option>
                        @endforeach
                    </select>
                    <!-- Campo oculto para el ID del personal -->
                </div>
            </div>

            <!-- <div class="form-group">
                <label for="asunto" class="col-sm-3 control-label">Asunto:</label>
                <div class="col-sm-9">
                    <input type="text" name="asunto" id="asunto" class="form-control" placeholder="Asunto del correo" required>
                </div>
            </div>
            <div class="form-group">
                <label for="mensaje" class="col-sm-3 control-label">Mensaje:</label>
                <div class="col-sm-9">
                    <textarea name="mensaje" id="mensaje" class="form-control" rows="5" placeholder="Contenido del mensaje" required></textarea>
                </div>
            </div> -->
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-9">
                    <button type="submit" class="btn btn-primary">Enviar Correo</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function seleccionarTodasRespuestas(control, index) {
        const estado = control.checked;
        document.querySelectorAll('.respuestaCheck' + index).forEach((chk) => {
            chk.checked = estado;
        });
    }
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Manejar el clic en "Crear Encuesta"
        $("#crearEncuestaLink").click(function() {
            $("#crearEncuestaBlock").show();
            $("#enviarEncuestaBlock").hide();
            $(this).addClass('active');
            $("#enviarEncuestaLink").removeClass('active');
        });

        // Manejar el clic en "Enviar Encuesta"
        $("#enviarEncuestaLink").click(function() {
            $("#crearEncuestaBlock").hide();
            $("#enviarEncuestaBlock").show();
            $(this).addClass('active');
            $("#crearEncuestaLink").removeClass('active');
        });
    });
</script>
<script>
    $(document).ready(function() {
        // Expansión del acordeón y selección del checkbox al hacer clic en cualquier parte del card-header
        $(".card-header").click(function() {
            // Obtiene el ID del acordeón clickeado, extraído del data-target de su botón asociado
            let targetId = $(this).find('button').data('target').replace("#collapse", "");
            let preguntaId = $("#collapse" + targetId).prev('.card-header').find('.preguntaCheck').attr('id').replace("pregunta", "");

            // Cambia el estado del collapse
            $("#collapse" + targetId).collapse('toggle');

            // Cambia el estado del checkbox de la pregunta correspondiente al acordeón desplegado
            // Nota: Ahora se utiliza el ID de la pregunta para seleccionar el checkbox correcto
            let preguntaCheck = $("#pregunta" + preguntaId);
            $("#collapse" + targetId).on('shown.bs.collapse', function() {
                preguntaCheck.prop("checked", true);
            }).on('hidden.bs.collapse', function() {
                // Si se desactiva el collapse, no hacemos nada para mantener la selección
            });
        });

        // Manejar la selección de todas las respuestas cuando se selecciona la opción "Todas"
        $(".select-all").change(function() {
            let collapseId = $(this).closest('.collapse').attr('id');
            let preguntaId = collapseId.replace("collapse", "");

            if ($(this).is(":checked")) {
                $(".respuestaCheck" + preguntaId).prop("checked", true);
            } else {
                $(".respuestaCheck" + preguntaId).prop("checked", false);
            }
        });

        // Manejar el clic en "Crear Encuesta"
        $("#crearEncuestaLink").click(function() {
            $("#crearEncuestaBlock").show();
            $("#enviarEncuestaBlock").hide();
            $(this).addClass('active');
            $("#enviarEncuestaLink").removeClass('active');
        });

        // Manejar el clic en "Enviar Encuesta"
        $("#enviarEncuestaLink").click(function() {
            $("#crearEncuestaBlock").hide();
            $("#enviarEncuestaBlock").show();
            $(this).addClass('active');
            $("#crearEncuestaLink").removeClass('active');
        });
  // Nuevo código para manejar el checkbox "Seleccionar Todas las Preguntas y Respuestas"
  $("#selectAllPreguntas").change(function() {
        var estado = $(this).is(":checked");

        // Marcar o desmarcar todos los checkboxes de preguntas
        $(".preguntaCheck").prop("checked", estado);
        
        // Para cada pregunta, manejar la selección de todas las respuestas
        $('.preguntaCheck').each(function() {
            var index = $(this).data('index');

            // Marcar o desmarcar el checkbox "Todas" de las respuestas
            $('#selectAll' + index).prop("checked", estado);

            // Marcar o desmarcar todos los checkboxes de respuestas individuales
            $('.respuestaCheck' + index).prop("checked", estado);
        });

        // Expandir o colapsar todas las preguntas según el estado
        if (estado) {
            $(".collapse").collapse('show');
        } else {
            $(".collapse").collapse('hide');
        }
    });
});
</script>


@endcomponent