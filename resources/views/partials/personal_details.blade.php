<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.0/font/bootstrap-icons.css">
<!-- CSS de Bootstrap -->

<?php
use App\Models\Evaluado;

$ultimosVin = Evaluado::with(['evaluador', 'vinculo'])
                        ->where('empresa_id', $empresa->id)
                        ->whereNotNull('encuesta_id')
                        ->orderBy('encuesta_id', 'desc')
                        ->get();
?>

<div id="personalList">
    @if($personal->isNotEmpty())

    <h5 class="pb-2 border-bottom">Personas de la Empresa: {{ $empresa->nombre }}</h5>
    <br>
    @if(getCrudConfig('Personal')->create && hasPermission(getRouteName().'.personal.create', 1, 1))
    <div class="col-md-4 d-flex justify-content-between align-items-center">
        <button id="btnCrearPersonal" onclick="abrirModalcrear()" class="btn btn-success">Crear {{ __('Personal')}}</button>
        <button  id="btnVinculos" onclick="mostrarVinculos()" class="btn btn-primary mt-3">Vínculos</button>
        <div class="col-md-4 input-group">
            <input type="text" id="searchInputPersonal" class="form-control" placeholder="Buscar Personas..." onkeyup="filterPeople()">
            <span class="input-group-text"><i class="fa fa-search"></i></span>
        </div>

    </div>

    <br>
    
    <form action="{{ route('importar.personas') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="empresa_id" value="{{ $empresa->id }}">

        <div class="input-group">
            <div class="custom-file">
                <input type="file" name="file" class="custom-file-input" id="inputGroupFile" required>
                <label class="custom-file-label" for="inputGroupFile">Elegir archivo</label>
            </div>
            <div class="input-group-append">
                <button type="submit" class="btn btn-primary"><i class="bi bi-file-earmark-spreadsheet"></i> Importar Personas</button>
            </div>
        </div>
    </form>


    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @endif

    <br>
        <table class="table">
            <thead>
                <tr>
                    <th>DNI</th>
                    <th>Nombre</th>
                    <th>Cargo</th>
                    <th>Email</th>
                    <th>Telefono</th>
                    <th>Estado</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($personal as $persona)
                <tr id="persona_{{ $persona->id }}"> <!-- Agrega este ID único -->
                    <td class="">{{ $persona->dni }}</td>
                    <td class="">{{ $persona->nombre }}</td>
                    <td class="">{{ $persona->cargo }}</td>
                    <td class="">{{ $persona->correo }}</td>
                    <td class="">{{ $persona->telefono }}</td>
                    <td class="">
                        <label class="switch2">
                            <input type="checkbox" data-id="{{ $persona->id }}" {{ $persona->estado ? 'checked' : '' }} class="switch2">
                            <span class="slider round"></span>
                        </label>
                    </td>
                    <style>
                        /* El contenedor del switch2 - hazlo como quieras */
                        .switch2 {
                        position: relative;
                        display: inline-block;
                        width: 60px;
                        height: 34px;
                        }

                        /* Esconde el input */
                        .switch2 input { 
                        opacity: 0;
                        width: 0;
                        height: 0;
                        }

                        /* El deslizador */
                        .slider {
                        position: absolute;
                        cursor: pointer;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        background-color: #ff4f70;
                        -webkit-transition: .4s;
                        transition: .4s;
                        }

                        .slider:before {
                        position: absolute;
                        content: "";
                        height: 26px;
                        width: 26px;
                        left: 4px;
                        bottom: 4px;
                        background-color: white;
                        -webkit-transition: .4s;
                        transition: .4s;
                        }

                        input:checked + .slider {
                        background-color: #22ca80;
                        }

                        input:focus + .slider {
                        box-shadow: 0 0 1px #22ca80;
                        }

                        input:checked + .slider:before {
                        -webkit-transform: translateX(26px);
                        -ms-transform: translateX(26px);
                        transform: translateX(26px);
                        }

                        /* Forma redonda */
                        .slider.round {
                        border-radius: 34px;
                        }

                        .slider.round:before {
                        border-radius: 50%;
                        }

                    </style>
                    @if(getCrudConfig('Personal')->delete or getCrudConfig('Personal')->update)
                        <td>

                            <a class="btn text-primary mt-1" onclick="editarPersonal({{ $persona->id }})">
                                <i class="icon-pencil"></i>
                            </a>
                            

                            <button class="btn text-danger mt-1" onclick="confirmDelete('{{ $persona->id }}', '{{ $empresa->id }}')">
                                <i class="icon-trash"></i>
                            </button>
                            
                            
                            <!-- Modal de Confirmación -->
                            <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-sm" role="document" style=" width: 30%; display: flex; align-items: center; justify-content: center; height: 100%;">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalLabel">Confirmar Eliminación</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            ¿Estás seguro de que quieres eliminar a esta persona?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                            <button type="button" class="btn btn-danger" id="deleteConfirmButton">Eliminar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            
                        </td>
                    @endif
                </tr>
                
                @endforeach
            </tbody>
        </table>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @else
        <p>No hay personal registrado para esta empresa.</p>
        <div class="col-md-4 right-0">
            <button id="btnCrearPersonal" onclick="abrirModalcrear()" class="btn btn-success">Crear {{ __('Personal')}}</button>
        </div>
        <br>
        <form action="{{ route('importar.personas') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="empresa_id" value="{{ $empresa->id }}">
        
            <div class="input-group">
                <div class="custom-file">
                    <input type="file" name="file" class="custom-file-input" id="inputGroupFile" required>
                    <label class="custom-file-label" for="inputGroupFile">Elegir archivo</label>
                </div>
                <div class="input-group-append">
                    <button type="submit" class="btn btn-primary">    <i class="bi bi-file-earmark-spreadsheet"></i> Importar Personas
                    </button>
                </div>
            </div>
        </form>
        
    @endif
</div>

<div id="vinculosSection" style="display: none;">
    <h5 class="pb-2 border-bottom">Relacionar Personas</h5>

    @if (!$ultimosVin->isEmpty())
    <div class="col-md-4">
        <button id="btnRecuperarVinculos" class="btn btn-info" onclick="recuperarUltimosVinculos()">Recuperar Últimos Vínculos</button>
    </div>
    @endif
    <br>
    
    <div class="accordion" id="accordionVinculos">
        
        @foreach($personal as $index => $persona)
        <input type="hidden" name="_token" value="{{ csrf_token() }}">

        <div class="card">
            <div class="card-header" id="heading{{ $index }}">
                <h2 class="mb-0">
                    <button onclick="toggleDropdown('collapse{{ $index }}')" class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse{{ $index }}" aria-expanded="false" aria-controls="collapse{{ $index }}">
                        {{ $persona->nombre }}
                    </button>
                </h2>
            </div>
            <div id="collapse{{ $index }}" class="collapse" aria-labelledby="heading{{ $index }}" data-parent="#accordionVinculos">
                <div class="card-body">
                    <input type="hidden" name="empresa_id" value="{{ $empresa->id }}">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-5">
                                <label for="input-evaluadores">Seleccionar persona:</label>
                                @php
                                    // Obtener IDs de personas ya vinculadas
                                    $vinculadosIds = $vinculados->where('evaluado_id', $persona->id)->pluck('evaluador_id')->toArray();
                                @endphp
                               <select class="form-control input-evaluadores" id="input-evaluadores-{{ $persona->id }}">


                                    @foreach ($personal as $otraPersona)
                                        @if (!in_array($otraPersona->id, $vinculadosIds))
                                            <option value="{{ $otraPersona->id }}">{{ $otraPersona->nombre }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label>Seleccionar el vinculo:</label>
                                <select class="form-control" id="tipoVinculo-{{ $persona->id }}">
                                    @foreach ($vinculos as $vinculo)
                                        <option value="{{ $vinculo->id }}" >{{ $vinculo->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 align-self-end">
                                <label></label><br>
                                <button class="btn btn-outline-secondary ml-2" type="button" onclick="agregarVinculo({{ $persona->id }})">Añadir</button>
                            </div>
                        </div>
                    </div>
                        
                        
                    <div id="lista-evaluadores">
                        <ul class="list-group" id="lista-evaluadores-ul-{{ $persona->id }}">
                            <li class="list-group-item list-group-item-info d-flex justify-content-between align-items-center" >
                                <span class="col-1">#</span>
                                <span class="col-3">Evaluador</span>
                                <span class="col-3">Vínculo</span>
                
                                <span>Acciones</span>
                            </li>
                    @foreach ($vinculados as $index => $vinculado)
                        @if ($vinculado->evaluado_id == $persona->id)
                            <div  class="list-group-item d-flex justify-content-between align-items-center" draggable = true>
                                <span class="col-1">{{ $index + 1 }}</span> <!-- Índice incremental -->

                                <span class="col-3"> {{ $vinculado->evaluador->nombre }} </span> 
                                <span class="col-3"> {{ $vinculado->vinculo->nombre }} </span> 
                                <input type="hidden" name="evaluadoresSeleccionados[]" value="{{ $vinculado->evaluador_id }}">
                                <input type="hidden" name="evaluadoresVinculos[]" value="{{ $vinculado->vinculo_id }}">

                                <button style="border-radius: 15%; width: 67px;" class="btn btn-danger btn-sm quitar-evaluador" data-evaluado-id="{{ $vinculado->evaluador_id }}" onclick="quitarEvaluador(this)">
                                    <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                </button>
                                

                            </div>
                        @endif
                    @endforeach
                        </ul>
                    </div>
                    <button id="btnGuardarVinculos" class="btn btn-success mt-3" onclick="guardarVinculos({{ $persona->id }})">Guardar Vínculos</button>

                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>





<div id="crearPersonalForm" style="display: none;">
    <div class="card">
        <form class="form-horizontal" method="POST" action="{{ route('personals.create') }}" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
               
                <!-- ID del personal, necesario solo para actualizar -->
                <input type="hidden" name="personal_id" id="input-personal-id">
                

                <!-- Empresa Input -->
                @php
                use App\Models\Empresa; 
                    $empresas = Empresa::all();
                @endphp
                <div class='form-group'>
                    <label for='input-empresa' class='col-sm-2 control-label'>{{ __('Empresa') }} <span style="color: red" class="required" >*</span></label>
                    <select disabled id='input-empresa' name='empresa' class="form-control @error('empresa') is-invalid @enderror">
                        @foreach($empresas as $key => $value)
                            <option value='{{ $key }}' {{ $empresa->id == $key ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                    @error('empresa') <div class='invalid-feedback'>{{ $message }}</div> @enderror
                </div>

                <!-- Dni Input -->
                <div class='form-group'>
                    <label for='input-dni' class='col-sm-2 control-label'> {{ __('Dni') }} </label>
                    <div class="input-group mb-3">
                        <input type='text' name='dni' id='input-dni' class="form-control @error('dni') is-invalid @enderror" placeholder='' autocomplete='on'>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" onclick="consultaDNI()">Buscar</button>
                        </div>
                    </div>
                    @error('dni') <div class='invalid-feedback'>{{ $message }}</div> @enderror
                </div>
                <!-- Nombre Input -->
                <div class='form-group'>
                    <label for='input-nombre' class='col-sm-2 control-label'> {{ __('Nombre') }} <span style="color: red" class="required">*</span></label>
                    <input type='text' name='nombre' id='input-nombre' class="form-control @error('nombre') is-invalid @enderror" placeholder='' autocomplete='on'>
                    @error('nombre') <div class='invalid-feedback'>{{ $message }}</div> @enderror
                </div>
                <!-- Correo Input -->
                <div class='form-group'>
                    <label for='input-correo' class='col-sm-2 control-label'> {{ __('Correo') }} <span style="color: red" class="required">*</span></label>
                    <input type='email' name='correo' id='input-correo' class="form-control @error('correo') is-invalid @enderror" placeholder='' autocomplete='on'>
                    @error('correo') <div class='invalid-feedback'>{{ $message }}</div> @enderror
                </div>
                <!-- Teléfono Input -->
                <div class='form-group'>
                    <label for='input-telefono' class='col-sm-2 control-label'> {{ __('Teléfono') }} </label>
                    <input type='number' name='telefono' id='input-telefono' class="form-control @error('telefono') is-invalid @enderror" placeholder='' autocomplete='on'>
                    @error('telefono') <div class='invalid-feedback'>{{ $message }}</div> @enderror
                </div>
                <!-- Cargo Input -->

                <div class='form-group'>
                    <label for='input-cargo' class='col-sm-2 control-label'> {{ __('Cargo') }}</label>
                    <input type='text' name='cargo' id='input-cargo' class="form-control @error('cargo') is-invalid @enderror" placeholder='' autocomplete='on'>
                    @error('cargo') <div class='invalid-feedback'>{{ $message }}</div> @enderror
                </div>
                
            </div>
        
            <div class="card-footer">
                <button type="button" id="btnCrearActualizarPersonal" class="btn btn-info ml-4" onclick="enviarDatos({{ $empresa->id }})">Crear</button>
                <a onclick="cerrarModalcrear()" class="btn btn-default float-left">{{ __('Cancel') }}</a>
            </div>
        </form> 
        
    </div>

 
</div>
<meta name="csrf-token" content="{{ csrf_token() }}">



<script>
    $(document).ready(function() {
    
            $('[id^="input-evaluadores-"]').each(function() {
                $(this).select2({
                    placeholder: "Seleccione una opción",
                    allowClear: true,
                    width: '100%'
                });
            });
        $('.select2-container--default .select2-selection--single').css({'height': '100%'});
    });

    $('[id^="tipoVinculo-"]').each(function() {
        $(this).select2({
            placeholder: "Seleccione una opción",
            allowClear: true,
            width: '100%'
        });
    });


</script>
