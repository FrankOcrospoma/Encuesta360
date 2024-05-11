@component('admin::layouts.app')


<?php

use App\Models\Pregunta;
use App\Models\Respuesta;
use App\Models\Personal; 
use App\Models\Encuesta;
use App\Models\Empresa;
use App\Models\Formulario;
use App\Models\Vinculo;
use App\Models\Envio;
use App\Models\Evaluado;
use App\Models\Detalle_empresa;


$preguntas = Pregunta::where('vigencia',1)->get();
$respuestas = Respuesta::where('vigencia',1)->get();
$formularios = Formulario::where('estado', true)->get()->keyBy('id');
$usuarios = Personal::where('estado', true)->get();
$encuestas = Encuesta::with('evaluados.personal')->get();
$vinculos = Vinculo::where('vigencia',1)->get();
$evals = Evaluado::all();
$envios = Envio::all();
$empresas = Empresa::where('estado',1)->get();
$userempresa = auth()->user()->empresa_id;
$emp = Empresa::where('id', auth()->user()->empresa_id)->first();



?>


   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.0/font/bootstrap-icons.css">
<style>
   .card {
    transition: box-shadow .3s ease-in-out;
    border: none;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15);
}

.card-header {
    background-color: #f8f9fa;
    cursor: pointer;
}

.card-header:hover {
    background-color: #e2e6ea;
}

.collapse.show {
    transition: height 0.3s ease; /* Aumenta el tiempo a 0.75 segundos */
}

.collapse {
    transition: height 0.3s ease;
}


    .search-container {
        max-width: 300px; /* Limitar el ancho del campo de búsqueda */
    }

    #searchInput {
        padding: 8px 10px;
        border: 1px solid #ccc;
        border-radius: 5px 0 0 5px; 
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .input-group-text {
        border-radius: 0 5px 5px 0; 
        border: 1px solid #ccc;
        background-color: #fff; 
        color: #333;
        border-left: none; 
    }

    .list-group-item.over {
  border: 1px dashed #000;
  
}

.pdf-export-btn {
        background-color: #d9534f; 
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 0.8rem;
        transition: background-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .pdf-export-btn:hover {
        background-color: #c9302c; 
        color: white;
        text-decoration: none;
    }
    .nombre-empresa {
        color: #2a3f9d; 
        font-weight: bold;

        margin-bottom: 0.5em;
        border-bottom: 2px solid #264653;
        padding-bottom: 0.3em;
        display: inline-block; 
    }
</style>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />


<style>
        .btn-rounded {
        border-radius: 5px; 
    }
    .dragging {
        border: 2px dashed #000000; /
    }
    .over {
        border: 2px dashed #000; 
    }
    .list-group {
        margin-bottom: 0; 
    }

    .list-group-item-info {
        font-weight: bold; 
        display: flex;
        justify-content: space-between; 
    }
    .list-group-item span {
        font-weight: normal;
        justify-content: space-between; 

    }
    .rotate-icon, .rotate-icon-proceso {
    transition: transform 0.3s ease;
}

.rotated, .rotated2 {
    transform: rotate(90deg);
}


    
</style>


<ul class="nav nav-tabs">
    <li class="nav-item"><a class="nav-link active" id="listarEncuestaLink">Listado de Procesos </a></li> 
    @if ($userempresa == null)
    <li class="nav-item"><a class="nav-link" id="crearEncuestaLink">Crear Proceso</a></li>
    @endif
</ul>

@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>


@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@endif

@if ($userempresa  == null) 

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
                <!-- Proceso Input -->
                <div class='form-group'>
                    <div class="row">
                        <div class="col-md-6">
                            <label for='input-proceso' class='control-label'> {{ __('Proceso') }}  <span style="color: red" class="required" >*</span></label>
                            <input type='text' name='proceso' id='input-proceso' class="form-control @error('proceso') is-invalid @enderror" placeholder='' autocomplete='on' required>
                            @error('proceso') <div class='invalid-feedback'>{{ $message }}</div> @enderror
                        </div>
                           <!-- Fecha Input -->
                        <div class="col-md-6">
                            <label for='input-fecha' class='control-label'> {{ __('Fecha') }}</label>
                            <input type='date' name='fecha' id='input-fecha' class="form-control @error('fecha') is-invalid @enderror" autocomplete='on' value="{{ $encuesta->fecha ?? ''}}">
                            @error('fecha') <div class='invalid-feedback'>{{ $message }}</div> @enderror
                        </div>
                    </div>
                  
                </div>
    
                <div class='form-group'>
                    <div class="row">
                        <div class="col-md-6">
                            <label for='input-empresa' class='control-label'> {{ __('Empresa') }}  <span style="color: red" class="required" >*</span></label>
                            <select name='empresa'  id='input-empresa' class="form-control @error('empresa') is-invalid @enderror">
                                @foreach($empresas as $key => $value)
                                    <option value='{{ $value->id }}'>{{ $value }}</option>
                                @endforeach
                            </select>
                            @error('empresa') <div class='invalid-feedback'>{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for='input-formulario' class='control-label'> {{ __('Formulario') }}  <span style="color: red" class="required" >*</span></label>
                            <select name='formulario'  id='input-formulario' class="form-control @error('formulario') is-invalid @enderror">
                                @foreach($formularios as $key => $value)
                                    <option value='{{ $key }}' {{ isset($encuesta) && $encuesta->formulario == $key ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                            @error('formulario') <div class='invalid-feedback'>{{ $message }}</div> @enderror
                        </div>
                       
                    </div>
                </div>
            

                
                
               <!-- Evaluados Input -->
               <div class='form-group'>
                <label for='input-evaluados' class='form-label'>{{ __('Evaluado') }}  <span style="color: red" class="required" >*</span></label>
                <div class="row">
                    <div class="col-md-8 col-12">
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
                    </div>
                    <div class="col-md-4 col-12 mt-md-0 mt-2 text-md-left text-center">
                        <button class="btn btn-outline-secondary" type="button" onclick="añadirEvaluado()">Añadir</button>
                        <button type="button" class="btn btn-outline-primary" onclick="añadirTodosLosEvaluados()" style="height: 38px; width: 180px">Añadir Todos</button>
                    </div>
                </div>
            </div>
            

                <div id="lista-evaluados" class="mt-3">
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
        
  
  
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-info ml-4">{{ isset($encuesta) ? __('Actualizar Encuesta') : __('Crear Encuesta') }}</button>
                <a href="" class="btn btn-default float-left">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</div>

@endif 

<!-- Modal para Vínculos -->

<div class="modal fade" id="vinculosModal" tabindex="-1" role="dialog" aria-labelledby="vinculosModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="vinculosModalLabel">Vínculos del Evaluado </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalBody">
               
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación para Enviar Todas las Encuestas -->
<div class="modal fade" id="confirmacionEnvioTodasModal" tabindex="-1" role="dialog" aria-labelledby="confirmacionEnvioTodasModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmacionEnvioTodasModalLabel">Confirmar Envío de Todas las Encuestas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas enviar todas las encuestas de este proceso?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmarEnvioTodas">Enviar Todas</button>
            </div>
        </div>
    </div>
</div>




<!-- Modal -->
@foreach($encuestas as $index => $encuesta)
<!-- Modal -->
<div style="z-index: 9999; margin-left: 1px" class="modal fade" id="modal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
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
@if ($userempresa  == null) 
<div id="listarEncuestaBlock" class="mt-4">


    <div class="d-flex justify-content-between" style="padding: 10px; margin-bottom: 20px;">
        <div class="col-md-4">
            <a id="crearEncuestaBtn" style="color: white" class="btn btn-success">Crear Proceso</a>
        </div>
        <div class="col-md-4 input-group">
            <input type="text" id="searchInput" class="form-control" placeholder="Buscar procesos por empresa..." onkeyup="filterCompanies()">
            <span class="input-group-text"><i class="fa fa-search"></i></span>
        </div>
    </div>
    
    
    
    <div id="accordion">
        @php
        $encuestasPorEmpresa = $encuestas->groupBy('empresa');
        @endphp

        @foreach($encuestasPorEmpresa as $empresaId => $encuestasDeEmpresa)
        @php
        $empresa = Empresa::find($empresaId);
        $encuestasPorProceso = $encuestasDeEmpresa->groupBy('proceso');
        @endphp

<div class="card card-empresa">
    <div class="card-header" id="headingEmpresa{{ $empresaId }}">
            <h5 class="mb-0">
                
                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseEmpresa{{ $empresaId }}" aria-expanded="false" aria-controls="collapseEmpresa{{ $empresaId }}">
                    <span class="fas fa-chevron-right rotate-icon"></span> {{ $empresa->nombre ?? 'Empresa no encontrada' }}
                </button>
                
                
            </h5>
        </div>

        <div id="collapseEmpresa{{ $empresaId }}" class="collapse collapse-empresa" aria-labelledby="headingEmpresa{{ $empresaId }}" data-parent="#accordion">
            <div class="card-body">
                    @foreach($encuestasPorProceso as $proceso => $encuestasDelProceso)
                    <div class="card">
                        <div class="card-header-proceso" id="headingProceso{{ $empresaId }}_{{ str_replace(' ', '', $proceso) }}">
                            <h5 class="mb-0">
                                <button class="btn btn-link" data-toggle="collapse" data-target="#collapseProceso{{ $empresaId }}_{{ str_replace(' ', '', $proceso) }}" aria-expanded="true" aria-controls="collapseProceso{{ $empresaId }}_{{ str_replace(' ', '', $proceso) }}">
                                    <span class="fas fa-chevron-right rotate-icon-proceso"></span> {{ $proceso }}
                                </button>
                                
                                @php
                                $todasConEnvios = true;
                                foreach ($encuestasDelProceso as $encuesta) {
                                    if ($encuesta->envios->isEmpty()) {
                                        $todasConEnvios = false;
                                        break;
                                    }
                                }
                                @endphp
                                @if(!$todasConEnvios)
                                <button class="btn btn-primary btn-sm enviar-todas float-right" data-proceso="#collapseProceso{{ $empresaId }}_{{ str_replace(' ', '', $proceso) }}"><i class="fas fa-paper-plane"></i> Enviar Todas</button>
                                @else
                                <button class="btn btn-primary btn-sm enviar-todas float-right" disabled><i class="fas fa-paper-plane"></i> Enviar Todas</button>
                                @endif
                            </h5>
                        </div>
                    
                        
                        

                        <div id="collapseProceso{{ $empresaId }}_{{ str_replace(' ', '', $proceso) }}" class="collapse collapse-proceso" aria-labelledby="headingProceso{{ $empresaId }}_{{ str_replace(' ', '', $proceso) }}" data-parent="#collapseEmpresa{{ $empresaId }}">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Nombre</th>
                                            <th>Evaluado</th>
                                            <th>Estado</th>
                                            <th>Fecha</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($encuestasDelProceso as $encuesta)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $encuesta->nombre }}</td>
                                            <td>
                                                @php
                                                $nombresUnicos = $encuesta->evaluados->map(function($evaluado) {
                                                    return $evaluado->personal->nombre;
                                                })->unique();
                                                @endphp
                                        
                                                @foreach($nombresUnicos as $nombre)
                                                {{ $nombre }}<br>
                                                @endforeach
                                            </td>
                                            <td>
                                                @php
                                                $todosFinalizados = true;
                                                $alMenosUnoFinalizado = false;
                                        
                                                foreach ($encuesta->envios as $envio) {
                                                    if ($envio->estado !== 'Finalizado') {
                                                        $todosFinalizados = false;
                                                    }
                                                    if ($envio->estado === 'Finalizado') {
                                                        $alMenosUnoFinalizado = true;
                                                    }
                                                }
                                        
                                                $estadoFinal = $encuesta->envios->isEmpty() ? 'Pendiente' : ($todosFinalizados ? 'Finalizado' : ($alMenosUnoFinalizado ? 'En Proceso' : 'Enviado'));
                                                echo $estadoFinal;
                                                @endphp
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($encuesta->fecha)->format('d/m/Y') }}</td>
                                            <td>
                                                @php
                                                $envios = Envio::where('encuesta', $encuesta->id)->get();
                                                @endphp
                      


                                                @if($envios->isNotEmpty())
                                                    @if($estadoFinal !== 'Enviado')

                                                    <a title="PDF" href="{{ route('encuestas.pdf', ['encuesta' => $encuesta->id]) }}" class="btn pdf-export-btn btn-rounded" target="_blank">
                                                        <i class="bi bi-file-earmark-pdf"></i>
                                                    </a>
                                                
                                                
                                                    @else
                                                    <button class="btn pdf-export-btn btn-rounded" disabled style="opacity: 0.5;">
                                                        <i class="bi bi-file-earmark-pdf"></i>
                                                    </button>
                                                    @endif

                                                    <button title="Ver Envíos" class="btn btn-info btn-sm btn-rounded" data-toggle="modal" data-target="#enviosModal{{$encuesta->id}}">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                
                                                @else
                                                <input type="hidden" class="encuesta-id" value="{{ $encuesta->id }}">

                                                    <button title="Enviar encuesta" class="btn btn-warning btn-sm abrirModalEnvio btn-rounded" data-id="{{ $encuesta->id }}"><i class="fas fa-paper-plane"></i></button>
                                                @endif
                                                    <button class="btn btn-danger btn-sm abrirModalEliminacion btn-rounded" data-id="{{ $encuesta->id }}"><i class="fas fa-trash-alt"></i></button>

                                            </td>
                                        </tr>
                                        
                                        <!-- Modal para mostrar los envíos de la encuesta -->
                                        <div class="modal fade" id="enviosModal{{$encuesta->id}}" tabindex="-1" role="dialog" aria-labelledby="enviosModalLabel{{$encuesta->id}}" aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="enviosModalLabel{{$encuesta->id}}">Detalles de los Envíos para {{ $encuesta->nombre }}</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <!-- Cuerpo del modal con detalles de los envíos -->
                                                        @foreach($evals as $eval)
                                                            @if($eval->encuesta_id == $encuesta->id)
                                                            <div class="card mb-3">
                                                                <div class="card-body">
                                                                    <h5 class="card-title">{{ $eval->evaluador }}</h5>
                                                                    @php
                                                                        $send = Envio::where('persona', $eval->evaluador_id)->where('encuesta', $encuesta->id)->first();
                                                                    @endphp
                                                                    @isset($send)
                                                                        <p class="card-text">Estado: {{$send->estado}}</p>
                                                                      
                                                                    @endisset
                                                                </div>
                                                            </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>


    
</div>
@else
<div id="listarEncuestaBlock" class="mt-4">
    <div id="accordion">
        @php
        // Filtramos las encuestas por la empresa del usuario.
        $encuestasDeEmpresa = $encuestas->where('empresa', $userempresa);
        $encuestasPorProceso = $encuestasDeEmpresa->groupBy('proceso');
        @endphp
        <h4 class="nombre-empresa">{{ $emp->nombre }}</h4>
        @foreach($encuestasPorProceso as $proceso => $encuestasDelProceso)
        <div class="card">
  
            <div class="card-header" id="headingProceso{{ str_replace(' ', '', $proceso) }}">
                <h5 class="mb-0">
                    <button class="btn btn-link" data-toggle="collapse" data-target="#collapseProceso{{ str_replace(' ', '', $proceso) }}" aria-expanded="true" aria-controls="collapseProceso{{ str_replace(' ', '', $proceso) }}">
                        {{ $proceso }}
                    </button>
                </h5>
            </div>

            <div id="collapseProceso{{ str_replace(' ', '', $proceso) }}" class="collapse" aria-labelledby="headingProceso{{ str_replace(' ', '', $proceso) }}" data-parent="#accordion">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Evaluado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($encuestasDelProceso as $encuesta)
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
                                <td>{{ \Carbon\Carbon::parse($encuesta->fecha)->format('d/m/Y') }}</td>
                                <td>
                                    @php
                                    $envios = Envio::where('encuesta', $encuesta->id)->get();
                                    @endphp
                                    @if($envios->isNotEmpty())
                                    <a title="PDF" href="{{ route('encuestas.pdf', ['encuesta' => $encuesta->id]) }}" class="btn pdf-export-btn btn-rounded" target="_blank">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </a>
                                    <button title="Ver Envíos" class="btn btn-info btn-sm btn-rounded" data-toggle="modal" data-target="#enviosModal{{$encuesta->id}}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @else
                                    @endif
                                </td>
                            </tr>
                                <!-- Modal para mostrar los envíos de la encuesta -->
                                <div class="modal fade" id="enviosModal{{$encuesta->id}}" tabindex="-1" role="dialog" aria-labelledby="enviosModalLabel{{$encuesta->id}}" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="enviosModalLabel{{$encuesta->id}}">Detalles de los Envíos para {{ $encuesta->nombre }}</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <!-- Cuerpo del modal con detalles de los envíos -->
                                                @foreach($evals as $eval)
                                                    @if($eval->encuesta_id == $encuesta->id)
                                                    <div class="card mb-3">
                                                        <div class="card-body">
                                                            <h5 class="card-title">{{ $eval->evaluador }}</h5>
                                                            @php
                                                                $send = Envio::where('persona', $eval->evaluador_id)->where('encuesta', $encuesta->id)->first();
                                                            @endphp
                                                            @isset($send)
                                                                <p class="card-text">Estado: {{$send->estado}}</p>
                                                                
                                                            @endisset
                                                        </div>
                                                    </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

@endif 
<!-- Modal de Confirmación para Enviar Encuesta -->
<div class="modal fade" id="confirmacionEnvioModal" tabindex="-1" role="dialog" aria-labelledby="confirmacionEnvioModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmacionEnvioModalLabel">Confirmar Envío</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas enviar esta encuesta?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmarEnvio">Enviar</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal de Confirmación para Eliminar Encuesta -->
<div class="modal fade" id="confirmacionEliminacionModal" tabindex="-1" role="dialog" aria-labelledby="confirmacionEliminacionModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmacionEliminacionModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas eliminar esta encuesta? Esta acción no se puede deshacer.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmarEliminacion">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Estado de Envío -->
<div class="modal fade" id="envioEstadoModal" tabindex="-1" role="dialog" aria-labelledby="envioEstadoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="envioEstadoModalLabel">Enviando Encuestas</h5>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p class="mt-3">Por favor, espere. Este proceso puede tomar algunos minutos.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">



<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>

<!-- Incluir CSS de Select2 -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />


<!-- Incluir JS de Select2 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<!-- Modal para mostrar mensajes de error o éxito -->
<div class="modal fade" id="mensajeModal" tabindex="-1" role="dialog" aria-labelledby="mensajeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mensajeModalLabel">Mensaje</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="mensajeModalCuerpo"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    $('.collapse').on('show.bs.collapse', function() {

        $(this).css({
            'transition': 'height 0.5s ease' // Aumenta el tiempo a 0.75 segundos
        });
    });

    $('.collapse').on('hide.bs.collapse', function() {

        $(this).css({
            'transition': 'height 0.5s ease' // Aumenta el tiempo a 0.75 segundos
        });
    });
});




function filterCompanies() {
    var input = document.getElementById("searchInput");
    var filter = input.value.toUpperCase();
    var accordion = document.getElementById("accordion");
    var cards = accordion.getElementsByClassName("card-empresa");

    for (var i = 0; i < cards.length; i++) {
        var title = cards[i].querySelector(".card-header button");
        if (title) {
            var txtValue = title.textContent || title.innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                cards[i].style.display = "";
            } else {
                cards[i].style.display = "none";
            }
        }
    }
}


function resetAccordion() {
    var filteredCards = document.querySelectorAll('.card.filtered');
    filteredCards.forEach(function(card, index) {
        var headingId = "heading" + index;
        var collapseId = "collapse" + index;

        var cardHeader = card.querySelector('.card-header');
        var collapseDiv = card.querySelector('.collapse');

        cardHeader.id = headingId;
        cardHeader.querySelector('button').dataset.target = "#" + collapseId;
        collapseDiv.id = collapseId;
        collapseDiv.setAttribute('aria-labelledby', headingId);
    });
}

$(document).ready(function() {
    // Interceptar el envío del formulario y enviarlo mediante AJAX
    $('form').submit(function(event) {
        event.preventDefault(); // Prevenir el envío predeterminado

        var form = $(this);
        var actionUrl = form.attr('action');

        $.ajax({
            url: actionUrl,
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                // Mostrar el mensaje en el modal y abrirlo
                $('#mensajeModalCuerpo').text(response.message);
                $('#mensajeModal').modal('show');

                // Cerrar el modal automáticamente después de 2 segundos y recargar la página si fue exitoso
                if (response.status === 'success') {
                    setTimeout(function() {
                        $('#mensajeModal').modal('hide');
                        window.location.reload(true); // Forzar recarga desde el servidor, no de caché
                    }, 1000);  // 1000 milisegundos = 1 segundos
                }
            },
            error: function(xhr) {
                var response = JSON.parse(xhr.responseText);
                $('#mensajeModalCuerpo').text(response.message);
                $('#mensajeModal').modal('show');
            }
        });
    });
});


$('.enviar-todas').click(function() {
    var procesoId = $(this).data('proceso');
    var encuestaIds = [];
    $(procesoId + ' .encuesta-id').each(function() {
        encuestaIds.push($(this).val());
    });

    if (encuestaIds.length > 0) {
        $('#confirmacionEnvioTodasModal').modal('show'); // Mostrar el modal de confirmación en lugar de enviar directamente

        // Manejar la confirmación del envío
        $('#confirmarEnvioTodas').off('click').on('click', function() {
            $('#confirmacionEnvioTodasModal').modal('hide');
            enviarTodasEncuestas(encuestaIds); // Función para enviar todas las encuestas
        });
    } else {
        alert('No hay encuestas para enviar.');
    }
});

function enviarTodasEncuestas(encuestaIds) {
    $('#envioEstadoModal').modal('show'); // Mostrar el modal de estado de envío

    $.ajax({
        url: '/enviar-todas-encuestas',
        type: 'POST',
        data: {
            encuesta_ids: encuestaIds,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(data) {
            $('#envioEstadoModal').modal('hide'); // Ocultar el modal de estado
            alert('Encuestas enviadas correctamente.');
            location.reload();
        },
        error: function(xhr) {
            $('#envioEstadoModal').modal('hide'); // Ocultar el modal de estado
            var errorMsg = 'Error al enviar las encuestas.';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMsg += ' Detalles: ' + xhr.responseJSON.error;
            } else if (xhr.statusText) {
                errorMsg += ' Detalles: ' + xhr.statusText;
            }
            alert(errorMsg);
        }
    });
}
// Íconos de Empresa
$(document).ready(function() {
    $('.collapse-empresa').on('show.bs.collapse', function() {
        var button = $(this).prev('.card-header').find('.rotate-icon');
        button.addClass('rotated');
    });

    $('.collapse-empresa').on('hide.bs.collapse', function() {
        var button = $(this).prev('.card-header').find('.rotate-icon');
        button.removeClass('rotated');
    });

    $('.collapse-proceso').on('show.bs.collapse', function() {
        var button = $(this).prev('.card-header-proceso').find('.rotate-icon-proceso');
        button.addClass('rotated2');
    });

    $('.collapse-proceso').on('hide.bs.collapse', function() {
        var button = $(this).prev('.card-header-proceso').find('.rotate-icon-proceso');
        button.removeClass('rotated2');
    });
});




$(document).ready(function() {
    // Evento click modificado para incluir el nombre del evaluado en el título del modal
    $('#lista-evaluados').on('click', '.abrirVinculosModal', function() {
        var evaluadoId = $(this).data('evaluado-id');
        var evaluadoNombre = $(this).data('evaluado-nombre'); // Obtiene el nombre del evaluado

        // Configura el título del modal con el nombre del evaluado
        $('#vinculosModalLabel').text('Vínculos del Evaluado: ' + evaluadoNombre);

        $('#vinculosModal').modal('show');
        var selectEmpresa = document.getElementById('input-empresa').value;

        // Opcional: Vaciar la lista antes de hacer la llamada AJAX
        $('#modalBody').html('');

        $.ajax({
            url: '/obtener-evaluados/' + selectEmpresa + '/' + evaluadoId,
            type: 'GET',
            success: function(data) {
                $('#modalBody').html(data);
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    });
});




    $(document).ready(function() {
        // Al hacer clic en el botón que debería abrir el modal de confirmación de eliminación
        $('.abrirModalEliminacion').click(function() {
            var encuestaId = $(this).data('id'); // Asegúrate de que el ID se está pasando correctamente
            $('#confirmacionEliminacionModal').modal('show');
    
            // Al hacer clic en confirmar en el modal de eliminación
            $('#confirmarEliminacion').off('click').on('click', function() {
                $('#confirmacionEliminacionModal').modal('hide');
                // Redireccionar para eliminar la encuesta de forma segura
                window.location.href = '/encuestas/destroy/' + encuestaId;
            });
        });
    });


    $(document).ready(function() {
        // Al hacer clic en el botón que debería abrir el modal de confirmación
        $('.abrirModalEnvio').click(function() {
            var encuestaId = $(this).data('id'); // Asegúrate de que el ID se está pasando correctamente
            $('#confirmacionEnvioModal').modal('show');
    
            // Al hacer clic en confirmar en el modal
            $('#confirmarEnvio').off('click').on('click', function() {
                $('#confirmacionEnvioModal').modal('hide');
                // Redireccionar para enviar la encuesta
                window.location.href = '/enviar-encuesta/' + encuestaId;
            });
        });
    });

        $(document).ready(function() {
            // Función para cargar evaluados basados en la empresa seleccionada
            function cargarEvaluados(empresaId) {
                $.get('/usuarios-por-empresa/' + empresaId, function(data) {
                    $('#input-evaluados').empty();
                    $.each(data, function(index, usuario) {
                        $('#input-evaluados').append($('<option>', { 
                            value: usuario.id,
                            text : usuario.nombre 
                        }));
                    });
                    // Vaciar la lista de evaluados ya seleccionados
                    $('#lista-evaluados-ul').empty();
                    agregarCabezalEvaluados(); // Asegúrate de que el cabezal se muestre si es necesario
                });
            }
    
            // Al cambiar el valor en el select de empresa
            $('#input-empresa').change(function() {
                var empresaId = $(this).val();
                cargarEvaluados(empresaId);
            });
    
            // Cargar inicialmente los evaluados para la empresa preseleccionada
            var empresaInicial = $('#input-empresa').val();
            if (empresaInicial) {
                cargarEvaluados(empresaInicial);
            }
    
            // Configuración de Select2
            $('#input-empresa').select2({
                placeholder: "Seleccione una opción",
                allowClear: true,
                width: '100%'
            });
    
            $('#input-evaluados').select2({
                placeholder: "Seleccione una opción",
                allowClear: true,
                width: '100%'
            });
    
            $('#input-formulario').select2({
                placeholder: "Seleccione una opción",
                allowClear: true,
                width: '100%'
            });
                $('[id^="input-evaluadores-"]').each(function() {
                    $(this).select2({
                        placeholder: "Seleccione una opción",
                        allowClear: true,
                        width: '100%'
                    });
                });
            $('.select2-container--default .select2-selection--single').css({'height': '100%'});
        });

    function seleccionarTodasRespuestas(control, index) {
        const estado = control.checked;
        document.querySelectorAll('.respuestaCheck' + index).forEach((chk) => {
            chk.checked = estado;
        });
    }

    


    function agregarCabezalEvaluados() {
        var listaEvaluados = document.getElementById('lista-evaluados');
        if (!listaEvaluados.querySelector('.list-group-item-info')) {
            var cabezal = document.createElement('div');
            cabezal.className = 'list-group-item list-group-item-info d-flex justify-content-between align-items-center';
            cabezal.innerHTML = `
                <span class="col-1">#</span>
                <span class="col-3">Evaluado</span>
                <span class="col-3">Formulario</span>
                

                <span>Acciones</span>
            `;
            listaEvaluados.querySelector('.list-group').prepend(cabezal);
        }
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

// Esta función actualiza los índices de los evaluados cada vez que se realiza un cambio
function actualizarIndicesEvaluados() {
    var items = document.querySelectorAll('#lista-evaluados .list-group-item');
    items.forEach((item, index) => {
        // Ajusta para no modificar el cabezal si lo tienes como parte de la lista
        if (index > 0) { 
            var spanIndex = item.querySelector('.col-1');
            spanIndex.textContent = index; // Actualiza el índice visualmente
        }
    });
}

// Función para añadir un evaluado
function añadirEvaluado() {
    var selectEvaluados = document.getElementById('input-evaluados');
    var formularioSelect = document.getElementById('input-formulario');
    var evaluadorId = selectEvaluados.value;
    var evaluadorNombre = selectEvaluados.options[selectEvaluados.selectedIndex].text;
    var formularioId = formularioSelect.value;
    var formularioTexto = formularioSelect.options[formularioSelect.selectedIndex].text;

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
            <span class="col-1"></span> <!-- El índice se actualiza después de añadir -->
            <span class="col-3">${evaluadorNombre}</span>
            <span class="col-3">${formularioTexto}</span>
            <input type="hidden" name="evaluadosSeleccionados[]" value="${evaluadorId}">
            <input type="hidden" name="formulariosSeleccionados[]" value="${formularioId}">
            <span>
                <button class="btn btn-danger btn-sm quitar-evaluado" data-evaluado-id="${evaluadorId}"><i class="fas fa-trash-alt"></i></button>
                <button class="btn btn-info btn-sm abrirVinculosModal" type="button" data-evaluado-id="${evaluadorId}" data-evaluado-nombre="${evaluadorNombre}">Vínculos</button>
            </span>
        `;
        document.getElementById('lista-evaluados').querySelector('.list-group').appendChild(li);
        actualizarIndicesEvaluados(); // Actualiza los índices cada vez que se añade un evaluado
    } else {
        alert("Este evaluado ya ha sido seleccionado.");
    }
}

document.getElementById('lista-evaluados').addEventListener('click', function(event) {
    if (event.target.classList.contains('quitar-evaluado') || event.target.parentNode.classList.contains('quitar-evaluado')) {
        var li = event.target.closest('.list-group-item');
        li.remove();
        actualizarIndicesEvaluados();
    }
});

// Similar modification is needed for añadirTodosLosEvaluados()

    document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('lista-evaluados').addEventListener('click', function(event) {
        if (event.target.classList.contains('quitar-evaluado') || event.target.parentNode.classList.contains('quitar-evaluado')) {
            var li = event.target.closest('.list-group-item');
            li.remove();
            actualizarIndicesEvaluados();
        }
    });

});

    function toggleDropdown(collapseId) {
        var x = document.getElementById(collapseId);
        if (x.style.display === "none") {
            x.style.display = "block";
        } else {
            x.style.display = "none";
        }
    }var indicesPorPersona = {};

    function agregarVinculo(personaId) {
    var selectEvaluados = document.getElementById('input-evaluadores-' + personaId);
    var evaluadorId = selectEvaluados.value;
    var evaluadorNombre = selectEvaluados.options[selectEvaluados.selectedIndex].text;
    var tipoVinculo = document.getElementById('tipoVinculo-' + personaId);
    var vinculoId = tipoVinculo.value;
    var vinculoNombre = tipoVinculo.options[tipoVinculo.selectedIndex].text;

    // Incrementa el índice para la persona actual
    var listaEvaluadores = document.getElementById('lista-evaluadores-ul-' + personaId);
    var totalEvaluadores = listaEvaluadores.querySelectorAll('.list-group-item').length; // Cuenta todos los ítems, incluyendo el cabezal

    var li = document.createElement('div');
    li.className = 'list-group-item d-flex justify-content-between align-items-center';
    li.draggable = true;
    li.innerHTML = `
        <span class="col-1">${totalEvaluadores}</span>
        <span class="col-3">${evaluadorNombre}</span>
        <span class="col-3">${vinculoNombre}</span>
        <input type="hidden" name="evaluadoresSeleccionados[]" value="${evaluadorId}">
        <input type="hidden" name="evaluadoresVinculos[]" value="${vinculoId}">
        <button style="border-radius: 15%; width: 67px;" class="btn btn-danger btn-sm quitar-evaluador" data-evaluado-id="${evaluadorId}" onclick="quitarEvaluador(this)">
            <i class="fas fa-trash-alt" aria-hidden="true"></i>
        </button>
    `;
    listaEvaluadores.appendChild(li);

    // Actualizar el selector para eliminar la opción que fue añadida
    selectEvaluados.remove(selectEvaluados.selectedIndex);
}

function quitarEvaluador(element) {
    var evaluadorId = $(element).data('evaluado-id');
    var personaId = $(element).closest('[id^="lista-evaluadores-ul-"]').attr('id').split('-').pop();
    var nombreEvaluador = $(element).parent().find('.col-3:first').text();
    var selectEvaluadores = document.getElementById('input-evaluadores-' + personaId);

    // Crear nueva opción para el select
    var newOption = document.createElement('option');
    newOption.value = evaluadorId;
    newOption.text = nombreEvaluador;
    selectEvaluadores.appendChild(newOption);

    // Reordenar las opciones del select alfabéticamente, si necesario
    $(selectEvaluadores).html($('option', selectEvaluadores).sort(function(a, b) {
        return a.text == b.text ? 0 : a.text < b.text ? -1 : 1;
    }));

    // Remover el elemento de la lista
    $(element).closest('div.list-group-item').remove();

    // Actualizar los índices
    actualizarIndices(personaId);

    // Actualizar el componente select2 para reflejar el cambio
    $(selectEvaluadores).select2({
        placeholder: "Seleccione una opción",
        allowClear: true,
        width: '100%'
    });
}


function actualizarIndices(personaId) {
    var listaEvaluadores = document.getElementById('lista-evaluadores-ul-' + personaId);
    var items = listaEvaluadores.querySelectorAll('.list-group-item');
    items.forEach((item, index) => {
        if (index > 0) { // Ignora el cabezal
            item.querySelector('.col-1').textContent = index;
        }
    });
}

function guardarVinculos(index) {
    const evaluadoresSeleccionados = [];
    const evaluadoresVinculos = [];
    const listaEvaluadores = document.querySelectorAll(`#lista-evaluadores-ul-${index} input[name="evaluadoresSeleccionados[]"]`);

    listaEvaluadores.forEach(input => {
        evaluadoresSeleccionados.push(input.value);
    });

    const listaVinculos = document.querySelectorAll(`#lista-evaluadores-ul-${index} input[name="evaluadoresVinculos[]"]`);

    listaVinculos.forEach(input => {
        evaluadoresVinculos.push(input.value);
    });

    const empresaId = document.querySelector('input[name="empresa_id"]').value;

    const dataToSend = {
        evaluadores: evaluadoresSeleccionados,
        empresa_id: empresaId,
        personal_id: index,
        vinculos: evaluadoresVinculos
    };



    fetch('{{ route("agregar-vinculo") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(dataToSend)
    })
    .then(response => response.json())
    .then(data => {
        console.log('Respuesta del servidor:', data); // Más depuración
        alert('Vínculos guardados correctamente.');
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al guardar los vínculos: ' + error.message);
    });
}

function recuperarUltimosVinculos() {
    const empresaId = document.querySelector('input[name="empresa_id"]').value;

    fetch(`/recuperar-ultimos-vinculos?empresa_id=${empresaId}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        data.forEach(vinculo => {
            const evaluadorId = vinculo.evaluador_id;
            const vinculoId = vinculo.vinculo_id;
            const evaluadorNombre = vinculo.evaluador.nombre;
            const vinculoNombre = vinculo.vinculo.nombre;
            const personaId = vinculo.evaluado_id;
            const listaEvaluadores = document.getElementById(`lista-evaluadores-ul-${personaId}`);
            const selectEvaluados = document.getElementById(`input-evaluadores-${personaId}`);

            // Crear el elemento de la lista
            const li = document.createElement('div');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            li.draggable = true;
            li.innerHTML = `
                <span class="col-1">${personaId}</span>
                <span class="col-3">${evaluadorNombre}</span>
                <span class="col-3">${vinculoNombre}</span>
                <input type="hidden" name="evaluadoresSeleccionados[]" value="${evaluadorId}">
                <input type="hidden" name="evaluadoresVinculos[]" value="${vinculoId}">
                <button style="border-radius: 15%; width: 67px;" class="btn btn-danger btn-sm quitar-evaluador" data-evaluado-id="${evaluadorId}" onclick="quitarEvaluador(this)">  <i class="fas fa-trash-alt" aria-hidden="true"></i></button>
            `;
            listaEvaluadores.appendChild(li);

            // Eliminar la opción del selector si ya está en la lista
            for (let i = 0; i < selectEvaluados.options.length; i++) {
                if (selectEvaluados.options[i].value == evaluadorId) {
                    selectEvaluados.remove(i);
                    break;
                }
            }
        });
        alert('Últimos vínculos recuperados y agregados a las listas.');
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al recuperar los últimos vínculos: ' + error.message);
    });
}

function añadirTodosLosEvaluados() {
    var selectEvaluados = document.getElementById('input-evaluados');
    var evaluados = selectEvaluados.options;
    var formularioSelect = document.getElementById('input-formulario'); // Referencia al select de formulario

    for (var i = 0; i < evaluados.length; i++) {
        var evaluadorId = evaluados[i].value;
        var evaluadoNombre = evaluados[i].text;
        var formularioId = formularioSelect.value;
        var formularioTexto = formularioSelect.options[formularioSelect.selectedIndex].text;
        // Verificar si el evaluador ya ha sido añadido
        var evaluadorYaAgregado = false;
        document.querySelectorAll('#lista-evaluados input[type="hidden"]').forEach(function(input) {
            if (input.value === evaluadorId) {
                evaluadorYaAgregado = true;
            }
        });

        if (!evaluadorYaAgregado) {
            var totalFilas = document.querySelectorAll('#lista-evaluados .list-group-item').length;
            var indice = totalFilas + 1;
            agregarCabezalEvaluados();
            var li = document.createElement('div');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            li.draggable = true;
            li.innerHTML = `
                <span class="col-1">${indice}</span>
                <span class="col-3">${evaluadoNombre}</span>
                <span class="col-3">${formularioTexto}</span>  <!-- Muestra el nombre del formulario -->
                
                <input type="hidden" name="evaluadosSeleccionados[]" value="${evaluadorId}">
                <input type="hidden" name="formulariosSeleccionados[]" value="${formularioId}">  <!-- Guarda el ID del formulario -->

                <span >
                <button class="btn btn-danger btn-sm quitar-evaluado" data-evaluado-id="${evaluadorId}"><i class="fas fa-trash-alt"></i></button>
                <button class="btn btn-info btn-sm abrirVinculosModal" type="button" data-evaluado-id="${evaluadorId}" data-evaluado-nombre="${evaluadoNombre}">Vínculos</button>
                 </span>
            `;

            document.getElementById('lista-evaluados').querySelector('.list-group').appendChild(li);
            actualizarIndicesEvaluados(); // Actualiza los índices cada vez que se añade un evaluado

        } else {
            alert("Este evaluado ya ha sido seleccionado.");
        }
    }
}

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

        $("#crearEncuestaBtn").click(function() {
      
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
            $("#crearEncuestaLink").addClass('active');
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
            console.log(empresaId); // Agrega esta línea para depurar
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

@endisset


@endcomponent


