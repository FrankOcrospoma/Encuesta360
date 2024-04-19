<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.0/font/bootstrap-icons.css">
<!-- CSS de Bootstrap -->




<div id="personalList">
    @if($personal->isNotEmpty())

<h5 class="pb-2 border-bottom">Personas de la Empresa: {{ $empresa->nombre }}</h5>
<br>
@if(getCrudConfig('Personal')->create && hasPermission(getRouteName().'.personal.create', 1, 1))
<div class="col-md-4 d-flex justify-content-between align-items-center">
    <button id="btnCrearPersonal" onclick="abrirModalcrear()" class="btn btn-success">Crear {{ __('Personal')}}</button>
    <button  id="btnVinculos" onclick="mostrarVinculos()" class="btn btn-primary mt-3">Vínculos</button>
    @if(getCrudConfig('Personal')->searchable())
    <div class="col-md-4">
        <div class="input-group">
            <input type="text" class="form-control" @if(config('easy_panel.lazy_mode')) wire:model.lazy="search" @else wire:model="search" @endif placeholder="{{ __('Search') }}" value="{{ request('search') }}">
            <div class="input-group-append">
                <button class="btn btn-default">
                    <a wire:target="search" wire:loading.remove><i class="fa fa-search"></i></a>
                    <a wire:loading wire:target="search"><i class="fas fa-spinner fa-spin" ></i></a>
                </button>
            </div>
        </div>
    </div>
    @endif
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
            <tr >
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
    <div class="accordion" id="accordionVinculos">
        @foreach($personal as $index => $persona)
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
                    <h6>Agregar nuevo vínculo:</h6>
                    <form id="form{{ $persona->id }}" onsubmit="event.preventDefault(); agregarVinculo({{ $persona->id }}, {{ $empresa->id }});">
                        <div class="form-group">
                            <label for="nuevoVinculo{{ $persona->id }}">Seleccionar persona:</label>
                            @php
                                // Obtener IDs de personas ya vinculadas
                                $vinculadosIds = $vinculados->where('evaluado_id', $persona->id)->pluck('evaluador_id')->toArray();
                            @endphp
                            <select class="form-control" id="nuevoVinculo{{ $persona->id }}">
                                @foreach ($personal as $otraPersona)
                                    @if (!in_array($otraPersona->id, $vinculadosIds))
                                        <option value="{{ $otraPersona->id }}">{{ $otraPersona->nombre }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <select class="form-control" id="tipoVinculo{{ $persona->id }}">
                                @foreach ($vinculos as $vinculo)
                                    <option value="{{ $vinculo->id }}">{{ $vinculo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </form>
                    <ul id="vinculosLista{{ $persona->id }}">
                        @foreach ($vinculados as $vinculado)
                            @if ($vinculado->evaluado_id == $persona->id)
                                <li>{{ $vinculado->evaluador->nombre }} - {{ $vinculado->vinculo->nombre }}</li>
                            @endif
                        @endforeach
                    </ul>
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
                <div class='form-group'>
                    <label for='input-empresa' class='col-sm-2 control-label'>{{ __('Empresa') }} <span style="color: red" class="required" >*</span></label>
                    <select disabled id='input-empresa' name='empresa' class="form-control @error('empresa') is-invalid @enderror">
                        @foreach(getCrudConfig('Personal')->inputs()['empresa']['select'] as $key => $value)
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



    