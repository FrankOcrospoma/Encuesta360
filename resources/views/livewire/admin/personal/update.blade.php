<div class="card">
    <div class="card-header p-0">
        <h3 class="card-title">{{ __('UpdateTitle', ['name' => __('Personal') ]) }}</h3>
        <div class="px-2 mt-4">
            <ul class="breadcrumb mt-3 py-3 px-4 rounded">
                <li class="breadcrumb-item"><a href="@route(getRouteName().'')" class="text-decoration-none">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="@route(getRouteName().'.personal.read')" class="text-decoration-none">{{ __(\Illuminate\Support\Str::plural('Personal')) }}</a></li>
                <li class="breadcrumb-item active">{{ __('Update') }}</li>
            </ul>
        </div>
    </div>

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
