<div class="card">
    <div class="card-header p-0">
        <h3 class="card-title">Crear Preguntas</h3>
        <div class="px-2 mt-4">
            <ul class="breadcrumb mt-3 py-3 px-4 rounded">
                <li class="breadcrumb-item"><a href="@route(getRouteName().'.home')" class="text-decoration-none">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="@route(getRouteName().'.pregunta.read')" class="text-decoration-none">{{ __(\Illuminate\Support\Str::plural('Pregunta')) }}</a></li>
                <li class="breadcrumb-item active">{{ __('Create') }}</li>
            </ul>
        </div>
    </div>

    <form class="form-horizontal" wire:submit.prevent="create" enctype="multipart/form-data">

        <div class="card-body">
            <!-- Texto Input -->
            <div class='form-group'>
                <label for='input-texto' class='col-sm-2 control-label'> {{ __('Texto') }}</label>
                <input type='text' id='input-texto' wire:model.lazy='texto' class="form-control @error('texto') is-invalid @enderror" placeholder='' autocomplete='on'>
                @error('texto') <div class='invalid-feedback'>{{ $message }}</div> @enderror
            </div>
            
            <!-- Estado Input -->
            <div class='form-group'>
                <div class='form-check mt-4 mb-3'>
                    <input wire:model.lazy='estado' id='input-estado' class='form-check-input' type='checkbox' autocomplete='on'>
                    <label class='form-check-label' for='input-estado'>{{ __('(Con Respuestas)') }}</label>
                </div>
                @error('estado') <div class='invalid-feedback'>{{ $message }}</div> @enderror
            </div>

            <!-- Categoria Input -->
            @if($estado) <!-- Asume que 'estado' es un booleano que indica si el checkbox está marcado -->
            <div class='form-group'>
                <label for='input-categoria' class='col-sm-2 control-label'> {{ __('Categoria') }}</label>
                <select id='input-categoria' wire:model.lazy='categoria' class="form-control @error('categoria') is-invalid @enderror">
                    @foreach(getCrudConfig('Pregunta')->inputs()['categoria']['select'] as $key => $value)
                        <option value='{{ $key }}'>{{ $value }}</option>
                    @endforeach
                </select>
                @error('categoria') <div class='invalid-feedback'>{{ $message }}</div> @enderror
            </div>
            @endif

        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-info ml-4">{{ __('Create') }}</button>
            <a href="@route(getRouteName().'.pregunta.read')" class="btn btn-default float-left">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
 