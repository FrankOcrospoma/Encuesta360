@if($personal->isNotEmpty())
<div id="personalList">

<h5 class="pb-2 border-bottom">Personas de la Empresa: {{ $empresa->nombre }}</h5>
<br>
@if(getCrudConfig('Personal')->create && hasPermission(getRouteName().'.personal.create', 1, 1))
<div class="col-md-4 right-0">
    <button id="btnCrearPersonal" onclick="abrirModalcrear()" class="btn btn-success">Crear {{ __('Personal')}}</button>
</div>
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
                <td class="">{{ $persona->Cargo }}</td>
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

                        @if(getCrudConfig('Personal')->update && hasPermission(getRouteName().'.personal.update', 1, 1, $persona))
                            <a href="@route(getRouteName().'.personal.update', $persona->id)" class="btn text-primary mt-1">
                                <i class="icon-pencil"></i>
                            </a>
                        @endif

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

</div>
@else
    <p>No hay personal registrado para esta empresa.</p>
@endif

<div id="crearPersonalForm" style="display: none;">
    <div class="card">
        <form class="form-horizontal" method="POST" action="{{ route('personals.create') }}" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
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
                    <label for='input-dni' class='col-sm-2 control-label'> {{ __('Dni') }} <span style="color: red" class="required">*</span></label>
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
                    <label for='input-telefono' class='col-sm-2 control-label'> {{ __('Teléfono') }} <span style="color: red" class="required">*</span></label>
                    <input type='number' name='telefono' id='input-telefono' class="form-control @error('telefono') is-invalid @enderror" placeholder='' autocomplete='on'>
                    @error('telefono') <div class='invalid-feedback'>{{ $message }}</div> @enderror
                </div>
                <!-- Cargo Input -->
                <div class='form-group'>
                    <label for='input-cargo' class='col-sm-2 control-label'> {{ __('Cargo') }} <span style="color: red" class="required">*</span></label>
                    <select id='input-cargo' name='cargo' class="form-control @error('cargo') is-invalid @enderror">
                        @foreach(getCrudConfig('Personal')->inputs()['cargo']['select'] as $key => $value)
                        <option value='{{ $key }}'>{{ $value }}</option>
                        @endforeach
                    </select>
                    @error('cargo') <div class='invalid-feedback'>{{ $message }}</div> @enderror
                </div>
                
                <!-- Estado Input -->
                <div class='form-group'>            
                    <div class='form-check mt-4 mb-3'>
                        <input name='estado' id='input-estado' class='form-check-input' type='checkbox' {{ old('estado') ? 'checked' : '' }}>
                        <label class='form-check-label' for='input-estado'>{{ __('Estado') }}</label>
                    </div>
                    @error('estado') <div class='invalid-feedback'>{{ $message }}</div> @enderror
                </div>
            </div>
        
            <div class="card-footer">
                <button type="button" class="btn btn-info ml-4" onclick="enviarDatos({{ $empresa->id }})">Crear</button>
                <a onclick="cerrarModalcrear()" class="btn btn-default float-left">{{ __('Cancel') }}</a>
            </div>
        </form>
        
    </div>

 
</div>




    