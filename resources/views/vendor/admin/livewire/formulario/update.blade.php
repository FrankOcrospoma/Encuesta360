<?php

use App\Models\Pregunta;


$preguntas = Pregunta::all();


?>
<div class="card">
    <div class="card-header p-0">
        <h3 class="card-title">{{ __('UpdateTitle', ['name' => __('Formulario') ]) }}</h3>
        <div class="px-2 mt-4">
            <ul class="breadcrumb mt-3 py-3 px-4 rounded">
                <li class="breadcrumb-item"><a href="@route(getRouteName().'')" class="text-decoration-none">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="@route(getRouteName().'.formulario.read')" class="text-decoration-none">{{ __(\Illuminate\Support\Str::plural('Formulario')) }}</a></li>
                <li class="breadcrumb-item active">{{ __('Update') }}</li>
            </ul>
        </div>
    </div>

    <form class="form-horizontal" wire:submit.prevent="update" enctype="multipart/form-data">

        <div class="card-body">

                        <!-- Nombre Input -->
            <div class='form-group'>
                <label for='input-nombre' class='col-sm-2 control-label '> {{ __('Nombre') }}</label>
                <input type='text' id='input-nombre' wire:model.lazy='nombre' class="form-control  @error('nombre') is-invalid @enderror" placeholder='' autocomplete='on'>
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
            <button type="submit" class="btn btn-info ml-4">{{ __('Update') }}</button>
            <a href="@route(getRouteName().'.formulario.read')" class="btn btn-default float-left">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>


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