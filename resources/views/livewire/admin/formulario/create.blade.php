<?php

use App\Models\Pregunta;


$preguntas = Pregunta::all();


?>
<div class="card">
    <div class="card-header p-0">
        <h3 class="card-title">{{ __('CreateTitle', ['name' => __('Formulario') ]) }}</h3>
        <div class="px-2 mt-4">
            <ul class="breadcrumb mt-3 py-3 px-4 rounded">
                <li class="breadcrumb-item"><a href="@route(getRouteName().'')" class="text-decoration-none">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="@route(getRouteName().'.formulario.read')" class="text-decoration-none">{{ __(\Illuminate\Support\Str::plural('Formulario')) }}</a></li>
                <li class="breadcrumb-item active">{{ __('Create') }}</li>
            </ul>
        </div>
    </div>

    <form action="{{ route('formularios.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
                        <!-- Nombre Input -->
            <div class='form-group'>
                <label for='input-nombre' class='col-sm-2 control-label '> {{ __('Nombre') }}</label>
                <input type='text' id='input-nombre' name='nombre' class="form-control  @error('nombre') is-invalid @enderror" placeholder='' autocomplete='on'>
                @error('nombre') <div class='invalid-feedback'>{{ $message }}</div> @enderror
            </div>
                <!-- Selección de Preguntas -->
                <div class='form-group'>
                    <label for='input-preguntas' class='form-label'>{{ __('Preguntas') }}</label>
                    <div class="d-flex" style="align-items: stretch;">

                        <!-- Select de preguntas con altura fija -->
                        <select name='preguntas' id='input-preguntas' class="form-control flex-grow-1" style="height: 38px;">
                            @foreach($preguntas as $pregunta)
                                <option value='{{ $pregunta->id }}' data-categoria='{{ $pregunta->Categoria }}' data-estado='{{ $pregunta->estado ? "true" : "false" }}'>{{ $pregunta->texto }}</option>
                            @endforeach
                        </select>
                        
                        <!-- Botones con altura fija para coincidir con el select -->
                        <button class="btn btn-outline-secondary ml-2" type="button" onclick="añadirPregunta()" style="height: 38px;">Añadir</button>
                        <button class="btn btn-outline-primary ml-2" type="button" onclick="añadirTodasLasPreguntas()" style="height: 38px; width: 180px">Añadir Todas</button>
                        
                    </div>
                </div>



                <div id="lista-preguntas" class="mt-3">
                    
                    <ul class="list-group" id="lista-preguntas-ul">
                       
                    </ul>
                </div>  
                <div class='form-group'>
                    <label>{{ __('Respuestas') }}</label>
                    <div>
                        @foreach(App\Models\Respuesta::where('estado', 1)->get() as $respuesta)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="respuestas[]" value="{{ $respuesta->id }}">
                                <label class="form-check-label">
                                    {{ $respuesta->texto }} -> {{ $respuesta->score }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
                
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Crear Formulario</button>
            <a href="@route(getRouteName().'.formulario.read')" class="btn btn-default float-left">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>

<script>
    

    function agregarCabezal() {
        var listaPreguntas = document.getElementById('lista-preguntas');
        if (!listaPreguntas.querySelector('.list-group-item-info')) {
            var cabezal = document.createElement('div');
            cabezal.className = 'list-group-item list-group-item-info d-flex justify-content-between align-items-center';
            cabezal.innerHTML = `
                <span class="col-1">#</span>
                <span class="col-3">Texto de la Pregunta</span>
                <span class="col-3">Estado</span>
                <span class="col-3">Categoria</span>
                <span>Acciones</span>
            `;
            listaPreguntas.querySelector('.list-group').prepend(cabezal);
        }
    }

   

    function crearElementoPregunta(preguntaId, preguntaTexto, estado, categoria) {
        // Obtener el número total de filas en la lista
        var totalFilas = document.querySelectorAll('#lista-preguntas .list-group-item').length;
        var indice = totalFilas; // Calcular el índice sumando 1 al número total de filas
        if (indice === 0){
            indice = 1 ;
        } 
        // Verificar si la pregunta ya ha sido añadida
        var yaSeleccionada = false;
        document.querySelectorAll('#lista-preguntas input[type="hidden"]').forEach(function(input) {
            if (input.value === preguntaId) {
                yaSeleccionada = true;
            }
        });

        if (!yaSeleccionada) {
            var estadoTexto = estado === "true" ? "(Para marcar)" : "(Abierta)";
            var entrada = document.createElement('div');
            entrada.className = 'list-group-item d-flex justify-content-between align-items-center';
            entrada.innerHTML = `
                <span class="col-1">${indice}</span>
                <span class="col-3">${preguntaTexto}</span>
                <span class="col-3">${estadoTexto}</span>
                <span class="col-3">${categoria}</span> <!-- Incluir la categoría aquí -->
                <input type="hidden" name="preguntasSeleccionadas[]" value="${preguntaId}">
                <button style="border-radius: 15%; width: 67px;" class="btn btn-danger btn-sm quitar-pregunta" data-pregunta-id="${preguntaId}"><i class="fas fa-trash-alt" aria-hidden="true"></i></button>
            `;

            // Evento para eliminar la pregunta
            entrada.querySelector('.quitar-pregunta').addEventListener('click', function() {
                entrada.remove();
                // Actualizar los índices después de eliminar la pregunta
                actualizarIndicesPreguntas();
            });

            document.getElementById('lista-preguntas').querySelector('.list-group').appendChild(entrada);
        }
    }

    function actualizarIndicesPreguntas() {
        // Obtener todas las filas de la lista de preguntas
        var listItems = document.querySelectorAll('#lista-preguntas .list-group-item');
        // Iterar sobre las filas y actualizar los índices
        listItems.forEach(function(item, index) {
            // Obtener el índice actual sumando 1 al índice base
            var indice = index ;
            // Obtener el span que muestra el índice
            var indiceSpan = item.querySelector('.col-1');
            // Actualizar el texto del índice
            if( indiceSpan.textContent != '#'){
                indiceSpan.textContent = indice;

            }
        });
    }

    


    function añadirTodasLasPreguntas() {
                    // Agregar cabezal si no está presente
        agregarCabezal();
        var selectPreguntas = document.getElementById('input-preguntas');
        for (var i = 0; i < selectPreguntas.options.length; i++) {
            var preguntaId = selectPreguntas.options[i].value;
            var preguntaTexto = selectPreguntas.options[i].text;
            var estado = selectPreguntas.options[i].getAttribute('data-estado');
            var categoria = selectPreguntas.options[i].getAttribute('data-categoria'); // Obtener la categoría
            crearElementoPregunta(preguntaId, preguntaTexto, estado, categoria); // Pasar la categoría como argumento
        }

    }
    function añadirPregunta() {
        var selectPreguntas = document.getElementById('input-preguntas');
        var preguntaId = selectPreguntas.value;
        var preguntaTexto = selectPreguntas.options[selectPreguntas.selectedIndex].text;
        var preguntasYaSeleccionadas = document.querySelectorAll('#lista-preguntas input[type="hidden"]');
        for (var i = 0; i < preguntasYaSeleccionadas.length; i++) {
            if (preguntasYaSeleccionadas[i].value === preguntaId) {
                alert("Esta pregunta ya ha sido seleccionada.");
                return; // Detener la ejecución si la pregunta ya está en la lista
            }
        }
        var estado = selectPreguntas.options[selectPreguntas.selectedIndex].getAttribute('data-estado');
        var categoria = selectPreguntas.options[selectPreguntas.selectedIndex].getAttribute('data-categoria'); // Obtener la categoría

    crearElementoPregunta(preguntaId, preguntaTexto, estado, categoria); // Pasar la categoría como argumento
    agregarCabezal();

    }


</script>
<script>
    $(document).ready(function() {



    // Configuración de Select2
    $('#input-preguntas').select2({
        placeholder: "Seleccione una opción",
        allowClear: true,
        width: '100%'
    });


    $('.select2-container--default .select2-selection--single').css({'height': '100%'});
});
</script>