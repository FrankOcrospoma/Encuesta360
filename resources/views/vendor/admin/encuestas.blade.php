@component('admin::layouts.app')


<?php

use App\Models\Pregunta;
use App\Models\Respuesta;
use App\Models\Personal; 
use App\Models\Encuesta;

use App\Models\Vinculo;
use App\Models\Envio;
use App\Models\Evaluado;

$preguntas = Pregunta::all();
$respuestas = Respuesta::all()->take(5); 

$usuarios = Personal::all();
$encuestas = Encuesta::with('evaluados.personal')->get();
$vinculos = Vinculo::all();
$evals = Evaluado::all();
$envios = Envio::all();
?>

<style>
    .list-group-item.over {
  border: 1px dashed #000;
  
}


</style>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />


<style>
    .dragging {
        border: 2px dashed #000000; /* Usa el color que prefieras */
    }
    .over {
        border: 2px dashed #000; /* Este estilo ya estaba definido */
    }
    .list-group {
        margin-bottom: 0; /* Elimina el margen inferior */
    }

    .list-group-item-info {
        font-weight: bold; /* Hace que el texto de los encabezados sea más prominente */
        display: flex; /* Usa flexbox para alinear los elementos */
        justify-content: space-between; /* Distribuye el espacio uniformemente entre los elementos */
    }
    .list-group-item span {
        font-weight: normal; /* Restaura el peso de la fuente normal para los elementos span dentro de los elementos li */
        justify-content: space-between; /* Distribuye el espacio uniformemente entre los elementos */

    }
    
    
</style>


<ul class="nav nav-tabs">
    <li class="nav-item"><a class="nav-link active" id="listarEncuestaLink">Listado de Encuestas</a></li> <!-- Nuevo elemento de menú -->
    <li class="nav-item"><a class="nav-link" id="crearEncuestaLink">Crear Encuesta</a></li>
    <li class="nav-item"><a class="nav-link" id="enviarEncuestaLink">Enviados</a></li>
</ul>

@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div id="crearEncuestaBlock" class="mt-4" style="display: none;">
    <div class="card">
            @isset($encuesta)
                <form action="{{ route('encuestas.update') }}" method="POST">
                @method('PUT')
            @else
                <form action="{{ route('encuestas.store') }}" method="POST">
            @endisset
            @csrf
            <div class="card-body">
                <input type='hidden' name='encuesta_id' value="{{ $encuesta->id ?? '' }}">

    
                <div class='form-group'>
                    <div class='row'>
                        <!-- Empresa Input -->
                        <div class='col-sm-6'>
                            <label for='input-empresa' class='control-label'> {{ __('Empresa') }}</label>
                            <select name='empresa' id='input-empresa' class="form-control @error('empresa') is-invalid @enderror">
                                @foreach(getCrudConfig('Personal')->inputs()['empresa']['select'] as $key => $value)
                                    <option value='{{ $key }}' {{ isset($encuesta) && $encuesta->empresa == $key ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                            @error('empresa') <div class='invalid-feedback'>{{ $message }}</div> @enderror
                        </div>
                
                        <!-- Fecha Input -->
                        <div class='col-sm-6'>
                            <label for='input-fecha' class='control-label'> {{ __('Fecha') }}</label>
                            <input type='date' name='fecha' id='input-fecha' class="form-control @error('fecha') is-invalid @enderror" autocomplete='on' value="{{ $encuesta->fecha ?? ''}}">
                            @error('fecha') <div class='invalid-feedback'>{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            

                
                
               <!-- Evaluados Input -->
                <div class='form-group'>
                    <label for='input-evaluados' class='form-label'>{{ __('Evaluado') }}</label>

                    <div class="d-flex" style="align-items: stretch;">
       
                        <select name='evaluado' id='input-evaluados' class="form-control @error('evaluado') is-invalid @enderror">
                            @isset($evaluados)
                                @foreach($usuarios as $usuario)
                                <option value='{{ $usuario->id }}' {{ $per !== null && $usuario->id == $per->id ? 'selected' : '' }}>
                                    {{ $usuario->nombre }}
                                </option>
                                
                                @endforeach
    
                            @else
                                @foreach($usuarios as $usuario)
                                <option value='{{ $usuario->id }}'>
                                    {{ $usuario->nombre }}
                                </option>
                                @endforeach
                            @endisset
                        </select>
     
                       
                            <button class="btn btn-outline-secondary ml-2" type="button" onclick="añadirEvaluado()">Añadir</button>
                            <button type="button" class="btn btn-outline-primary ml-2" onclick="añadirTodosLosEvaluados()" style="height: 38px; width: 180px">Añadir Todos</button>


                    </div>
                </div>

                <div id="lista-evaluados" class="mt-3">
                    <h4>Evaluados seleccionados</h4>
                    <ul class="list-group" id="lista-evaluados-ul">
                        <li class="list-group-item list-group-item-info d-flex justify-content-between align-items-center">
                            <span class="col-1">#</span>
                            <span class="col-4">Nombre del Evaluado</span>
                          
                            <span>Acciones</span>
                        </li>
                        @isset($evaluados)
                            @foreach($evaluados as $index => $evaluado)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="col-1">{{ $index + 1 }}</span>
                                    <span class="col-4">{{ $evaluado->evaluado }} </span> 
                               
                                    <input type="hidden" name="evaluadosSeleccionados[]" value="{{ $evaluado->evaluado_id }}">
                                    <button type="button" style="border-radius: 15%; width: 67px;" class="btn btn-danger btn-sm quitar-evaluado" data-evaluado-id="{{ $evaluado->id }}">
                                        <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                    </button>
                                                          
                                </li>
                            @endforeach
                               
                        @endisset
                    </ul>
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
                    <h4>Preguntas seleccionadas</h4>
                    <ul class="list-group" id="lista-preguntas-ul">
                        <li class="list-group-item list-group-item-info d-flex justify-content-between align-items-center">
                            <span class="col-1">#</span>
                            <span class="col-3">Texto de la Pregunta</span>
                            <span class="col-3">Estado</span>
                            <span class="col-3">Categoria</span>
                            <span>Acciones</span>
                        </li>
                        @isset($preguntasEncuesta)
                        @foreach($preguntasEncuesta as $index => $pregunta)
                        <li class="list-group-item d-flex justify-content-between align-items-center"  draggable="true" ondragstart="handleDragStart(event)" ondragover="handleDragOver(event)" ondrop="handleDrop(event)" ondragend="handleDragEnd(event)">
                                <span class="col-1">{{ $index + 1 }}</span> <!-- Índice incremental -->

                                <span class="col-3"> {{ $pregunta->texto }} </span> 
                                <span class="col-3">  @if($pregunta->estado)(Para marcar)@else
                                    (Abierta)@endif</span> 
                                <span class="col-3"> {{ $pregunta->Categoria }} </span> 

                                <input type="hidden" name="preguntasSeleccionadas[]" value="{{ $pregunta->id }}">
                                <button style="border-radius: 15%; width: 67px;" class="btn btn-danger btn-sm quitar-pregunta" data-pregunta-id="{{ $pregunta->id }}"> <i class="fas fa-trash-alt" aria-hidden="true"></i></button>
                            </li>
                        @endforeach
                        @endisset
                    </ul>
                </div>            
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-info ml-4">{{ isset($encuesta) ? __('Actualizar Encuesta') : __('Crear Encuesta') }}</button>
                <a href="" class="btn btn-default float-left">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</div>

<div id="enviarEncuestaBlock" class="mt-4" style="display: none;">
    <h2>Enviados</h2>
    <div class="accordion" id="accordionEncuestas">
        @foreach($encuestas as $index => $encuesta)
        <div class="card">
            <div class="card-header" id="heading{{ $index }}">
                <h2 class="mb-0">
                    <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse{{ $index }}" aria-expanded="true" aria-controls="collapse{{ $index }}">
                        {{ $encuesta->nombre }}
                    </button>
                </h2>
            </div>

            <div id="collapse{{ $index }}" class="collapse" aria-labelledby="heading{{ $index }}" data-parent="#accordionEncuestas">
                <div class="card-body">
                    
                    @foreach($evals as $eval)
                    @if($eval->encuesta_id == $encuesta->id)
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">{{ $eval->evaluador }}</h5>
                            @php
                                $send=Envio::where('persona', $eval->evaluador_id)->where('encuesta', $encuesta->id)->first();
                            @endphp
                            @isset($send)
                            <p class="card-text">Estado:{{$send->estado}} </p>
                          
                            @if($send->estado == 'Respondido')
                            <button type="button" class="btn btn-primary ver-respuestas" data-toggle="modal" data-target="#modal{{$index}}" data-encuesta-id="{{ $eval->encuesta_id }}" data-persona-id="{{ $eval->evaluador_id }}">Ver</button>

                            @endif
                            @endisset
                        </div>
                    </div>
                    @endif
                    @endforeach
                    
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Modal -->
@foreach($encuestas as $index => $encuesta)
<!-- Modal -->
<div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Respuestas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

            
            </div>
            <div  class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@endforeach



<div id="listarEncuestaBlock" class="mt-4">
    <h2></h2>
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Evaluado</th> <!-- Nueva columna -->
                        <th>Empresa</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($encuestas as $encuesta)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $encuesta->nombre }}</td>
                        <td>
                            @php
                            // Crear una colección a partir de los evaluados para filtrar y mostrar nombres únicos
                            $nombresUnicos = $encuesta->evaluados->map(function($evaluado) {
                                return $evaluado->personal->nombre;
                            })->unique();
                            @endphp
                        
                            @foreach($nombresUnicos as $nombre)
                                {{ $nombre }}<br>
                            @endforeach
                        </td>
                        
                        <td>{{ $encuesta->Empresa }}</td> <!-- Asumiendo que empresa es accesible directamente -->
                        <td>{{ \Carbon\Carbon::parse($encuesta->fecha)->format('d/m/Y') }}</td>

                        <td>
                            @php
                           $envios = Envio::where('encuesta',$encuesta->id)->get();
                            
                            @endphp
                        @if($envios->isNotEmpty())
                        <button class="btn btn-primary btn-sm" disabled><i class="icon-pencil"></i></button>
                        <button class="btn btn-warning btn-sm" disabled><i class="fas fa-paper-plane"></i></button>
                          
                        @else
                        <a href="{{ route('encuestas.edit', $encuesta->id) }}" class="btn btn-primary btn-sm"><i class="icon-pencil"></i></a>
                        <a href="{{ route('enviar.encuesta', $encuesta->id) }}" class="btn btn-warning btn-sm"><i class="fas fa-paper-plane"></i></a>
                        @endif
                            <a href="{{ route('encuestas.destroy', $encuesta->id) }}" class="btn btn-danger btn-sm"> <i class="icon-trash"></i></a>
                            
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>

<!-- Incluir CSS de Select2 -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />


<!-- Incluir JS de Select2 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    $('#lista-evaluados').on('click', '.quitar-evaluado', function() {
        $(this).closest('li').remove();  // Elimina el <li> del evaluado
        actualizarIndicesEvaluados();   // Actualiza los índices después de eliminar
    });
    $('#input-preguntas').select2({
        placeholder: "Seleccione una opción",
        allowClear: true,
        width: '100%',
  

    });
    $('#input-evaluados').select2({
        placeholder: "Seleccione una opción",
        allowClear: true,
        width: '100%',
  

    });
    

    $('.select2-container--default .select2-selection--single').css({'height': '100%'});


});

</script>


<script>
    function seleccionarTodasRespuestas(control, index) {
        const estado = control.checked;
        document.querySelectorAll('.respuestaCheck' + index).forEach((chk) => {
            chk.checked = estado;
        });
    }
</script>

<script>
    


    function agregarCabezalEvaluados() {
        var listaEvaluados = document.getElementById('lista-evaluados');
        if (!listaEvaluados.querySelector('.list-group-item-info')) {
            var cabezal = document.createElement('div');
            cabezal.className = 'list-group-item list-group-item-info d-flex justify-content-between align-items-center';
            cabezal.innerHTML = `
            <span class="col-1">#</span>

                <span class="col-3">Evaluado</span>
                <span>Acciones</span>
            `;
            listaEvaluados.querySelector('.list-group').prepend(cabezal);
        }
    }

   

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

    function actualizarIndicesEvaluados() {
        // Obtener todas las filas de la lista de preguntas
        var listItems = document.querySelectorAll('#lista-evaluados .list-group-item');
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

    function añadirEvaluado() {
    var selectEvaluados = document.getElementById('input-evaluados');
    var evaluadorId = selectEvaluados.value;
    var evaluadorNombre = selectEvaluados.options[selectEvaluados.selectedIndex].text;

    // Combinar el nombre del evaluador y la relación para formar una cadena única
    var combinacionEvaluador = evaluadorNombre ;
    var totalFilas = document.querySelectorAll('#lista-evaluados .list-group-item').length;
    var indice = totalFilas; // Calcular el índice sumando 1 al número total de filas
    if (indice === 0){
        indice = 1 ;
    } 
    // Verificar si el evaluador ya ha sido añadido
    var evaluadorYaAgregado = false;
    document.querySelectorAll('#lista-evaluados input[type="hidden"]').forEach(function(input) {
        if (input.value === evaluadorId) {
            evaluadorYaAgregado = true;
        }
    });

    if (!evaluadorYaAgregado) {
        agregarCabezalEvaluados();
        var li = document.createElement('div');
        li.className = 'list-group-item d-flex justify-content-between align-items-center';
        li.draggable = true;
        li.innerHTML = `
            <span class="col-1">${indice}</span>

            <span class="col-3">${evaluadorNombre}</span>
      
            <input type="hidden" name="evaluadosSeleccionados[]" value="${evaluadorId}">
       
            <button style="border-radius: 15%; width: 67px;"  class="btn btn-danger btn-sm quitar-evaluado" data-evaluado-id="${ evaluadorId }">  <i class="fas fa-trash-alt" aria-hidden="true"></i></button>
        `;

        li.querySelector('.quitar-evaluado').addEventListener('click', function() {
            li.remove();
            actualizarIndicesEvaluados();
        });
        // Añadir a la lista
        document.getElementById('lista-evaluados').querySelector('.list-group').appendChild(li);
        // Agregar cabezal si no está presente
       
    } else {
        alert("Este evaluado ya ha sido seleccionado.");
    }
    }
    function añadirTodosLosEvaluados() {
    var selectEvaluados = document.getElementById('input-evaluados');
    var evaluados = selectEvaluados.options;

    for (var i = 0; i < evaluados.length; i++) {
        var evaluadoId = evaluados[i].value;
        var evaluadoNombre = evaluados[i].text;

        // Verificar si el evaluador ya ha sido añadido
        var evaluadorYaAgregado = false;
        document.querySelectorAll('#lista-evaluados input[type="hidden"]').forEach(function(input) {
            if (input.value === evaluadoId) {
                evaluadorYaAgregado = true;
            }
        });

        if (!evaluadorYaAgregado) {
            var totalFilas = document.querySelectorAll('#lista-evaluados .list-group-item').length;
            var indice = totalFilas + 1; // Calcular el índice sumando 1 al número total de filas
            if (indice === 0){
                indice = 1 ;
            } 
            var li = document.createElement('div');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            li.draggable = true;
            li.innerHTML = `
            <span class="col-1">${indice}</span>

            <span class="col-3">${evaluadoNombre}</span>
      
            <input type="hidden" name="evaluadosSeleccionados[]" value="${evaluadoId}">
       
            <button style="border-radius: 15%; width: 67px;"  class="btn btn-danger btn-sm quitar-evaluado" data-evaluado-id="${ evaluadoId }">  <i class="fas fa-trash-alt" aria-hidden="true"></i></button>
            `;


            document.getElementById('lista-evaluados-ul').appendChild(li);
        }
    }
    actualizarIndicesEvaluados(); // Actualizar los índices después de añadir todos los evaluados
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
        $('.ver-respuestas').click(function() {
            var persona_id = $(this).data('persona-id');
            var encuesta_id = $(this).data('encuesta-id');
            
            // Hacer solicitud AJAX para obtener las respuestas
            $.get('/encuestas/ver/' + persona_id + '/' + encuesta_id, function(data) {
                // Obtener el modal
                var modal = $('#modal');
                
                // Limpiar el contenido anterior del modal-body
                modal.find('.modal-body').empty();

                // Agregar el contenido del partial al modal-body
                modal.find('.modal-body').html(data);

                // Mostrar el modal
                modal.modal('show');
            });
        });
        $("#crearEncuestaLink").click(function() {
      
            $("#input-empresa").prop('selectedIndex', 0);
            $("#input-evaluados").prop('selectedIndex', 0);
            var hoy = new Date();
            var fecha = hoy.getFullYear() + '-' + ('0' + (hoy.getMonth() + 1)).slice(-2) + '-' + ('0' + hoy.getDate()).slice(-2);
            $("#input-fecha").val(fecha);
            $("#input-preguntas").prop('selectedIndex', 0);


            $("#lista-preguntas-ul").empty();
            $("#lista-evaluados-ul").empty();


            // Mostrar el bloque para crear encuesta y ocultar los otros bloques
            $("#listarEncuestaBlock").hide(); 
            $("#crearEncuestaBlock").show();
            $("#enviarEncuestaBlock").hide();
            // Agregar la clase 'active' al enlace 'Crear Encuesta'
            $(this).addClass('active');
            // Remover la clase 'active' de los otros enlaces
            $("#enviarEncuestaLink").removeClass('active');
            $("#listarEncuestaLink").removeClass('active');
        });

        // Manejar el clic en "Enviar Encuesta"
        $("#enviarEncuestaLink").click(function() {
            $("#listarEncuestaBlock").hide(); 
            $("#crearEncuestaBlock").hide();
            $("#enviarEncuestaBlock").show();
            $(this).addClass('active');
            $("#crearEncuestaLink").removeClass('active');
            $("#listarEncuestaLink").removeClass('active');
        });
      
        $("#listarEncuestaLink").click(function() {
            $("#crearEncuestaBlock").hide();
            $("#enviarEncuestaBlock").hide();
            $("#listarEncuestaBlock").show(); // Mostrar el listado de encuestas
            $(this).addClass('active');
            $("#crearEncuestaLink").removeClass('active');
            $("#enviarEncuestaLink").removeClass('active');
        });
        // Expansión del acordeón y selección del checkbox al hacer clic en cualquier parte del card-header
        $(".card-header").click(function() {
            let targetId = $(this).find('button').data('target').replace("#collapse", "");
            let preguntaId = $("#collapse" + targetId).prev('.card-header').find('.preguntaCheck').attr('id').replace("pregunta", "");

            // Cambia el estado del collapse
            $("#collapse" + targetId).collapse('toggle');


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
        $('#input-empresa').change(function() {
        var empresaId = $(this).val();
        $.get('/usuarios-por-empresa/' + empresaId, function(data) {
            console.log(data); // Agrega esta línea para depurar
            $('#input-evaluados').empty();
            
            // Añadir los usuarios al select de evaluado
            $.each(data, function(index, usuario) {
                $('#input-evaluados').append($('<option>', { 
                    value: usuario.id,
                    text : usuario.nombre 
                }));
            });
            
      
        });
        });
        $('#lista-preguntas').on('click', 'button', function() {
            $(this).closest('li').remove();
        });


    });
</script>

<script>
    var dragSrcEl = null;
    
    function handleDragStart(e) {
    dragSrcEl = this;
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/html', this.outerHTML);
    this.classList.add('dragging'); // Añadir clase para estilo de arrastre
    }


    
    function handleDragOver(e) {
        e.preventDefault(); // Necesario para permitir soltar
        e.dataTransfer.dropEffect = 'move'; // Ver el ícono de mover
        return false;
    }
    
    function handleDragEnter(e) {
        // Este evento es opcional: puede servir para visualizar el elemento sobre el que se puede soltar
    }
    
    function handleDragLeave(e) {
        // Este evento es opcional: puede servir para revertir visualizaciones hechas en handleDragEnter
    }
    
    function handleDrop(e) {
        e.stopPropagation(); // Evita la redirección del navegador
        e.preventDefault();
        // No hacer nada si se está soltando el elemento sobre sí mismo
        if (dragSrcEl !== this) {
            // Remover la clase de 'dragging' del elemento arrastrado
            dragSrcEl.classList.remove('dragging');
            // Remover el elemento original y agregar el nuevo en la posición
            this.parentNode.removeChild(dragSrcEl);
            var dropHTML = e.dataTransfer.getData('text/html');
            this.insertAdjacentHTML('beforebegin', dropHTML);
            var dropElem = this.previousSibling;
            addDnDEvents(dropElem);
        }
        return false;
    }
    
    function handleDragEnd(e) {
    // Quitar la clase de 'dragging' de este elemento
    this.classList.remove('dragging');

    // También es buena idea quitar la clase 'over' de todos los elementos, por si acaso
    var listItems = document.querySelectorAll('#lista-preguntas .list-group-item');
    [].forEach.call(listItems, function(item) {
        item.classList.remove('over');
    });
    }
    
    // Agregar eventos DnD a un elemento específico
    function addDnDEvents(elem) {
        elem.addEventListener('dragstart', handleDragStart, false);
        elem.addEventListener('dragenter', handleDragEnter, false);
        elem.addEventListener('dragover', handleDragOver, false);
        elem.addEventListener('dragleave', handleDragLeave, false);
        elem.addEventListener('drop', handleDrop, false);
        elem.addEventListener('dragend', handleDragEnd, false);
    }
    
    // Agregar eventos DnD a todos los elementos .list-group-item al cargar y cada vez que se añade uno nuevo
    document.querySelectorAll('#lista-preguntas .list-group-item').forEach(addDnDEvents);
</script>
    


</script>
@isset($id)
<script>
     $(document).ready(function() {
    $("#listarEncuestaBlock").hide(); 
        $("#crearEncuestaBlock").show();
     });
  
</script>
@else
<script>
    $(document).ready(function() {
   $("#listarEncuestaBlock").show(); 
       $("#crearEncuestaBlock").hide();
    });
 
</script>
<style>
    
</style>
@endisset



@endcomponent
