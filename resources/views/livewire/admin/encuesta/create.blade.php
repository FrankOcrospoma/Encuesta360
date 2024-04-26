<div class="card">
    <div class="card-header p-0">
        <h3 class="card-title">{{ __('CreateTitle', ['name' => __('Encuesta') ]) }}</h3>
        <div class="px-2 mt-4">
            <ul class="breadcrumb mt-3 py-3 px-4 rounded">
                <li class="breadcrumb-item"><a href="@route(getRouteName().'')" class="text-decoration-none">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="@route(getRouteName().'.encuesta.read')" class="text-decoration-none">{{ __(\Illuminate\Support\Str::plural('Encuesta')) }}</a></li>
                <li class="breadcrumb-item active">{{ __('Create') }}</li>
            </ul>
        </div>
    </div>

    <form class="form-horizontal" wire:submit.prevent="create" enctype="multipart/form-data">

        <div class="card-body">
                        <!-- Nombre Input -->
            <div class='form-group'>
                <label for='input-nombre' class='col-sm-2 control-label '> {{ __('Nombre') }}</label>
                <input type='text' id='input-nombre' wire:model.lazy='nombre' class="form-control  @error('nombre') is-invalid @enderror" placeholder='' autocomplete='on'>
                @error('nombre') <div class='invalid-feedback'>{{ $message }}</div> @enderror
            </div>
            <!-- Empresa Input -->
            <div class='form-group'>
                <label for='input-empresa' class='col-sm-2 control-label '> {{ __('Empresa') }}</label>
                <select id='input-empresa' wire:model.lazy='empresa' class="form-control  @error('empresa') is-invalid @enderror">
                    @foreach(getCrudConfig('Encuesta')->inputs()['empresa']['select'] as $key => $value)
                        <option value='{{ $key }}'>{{ $value }}</option>
                    @endforeach
                </select>
                @error('empresa') <div class='invalid-feedback'>{{ $message }}</div> @enderror
            </div>
            <!-- Fecha Input -->
            <div class='form-group'>
                <label for='input-fecha' class='col-sm-2 control-label '> {{ __('Fecha') }}</label>
                <input type='date' id='input-fecha' wire:model.lazy='fecha' class="form-control  @error('fecha') is-invalid @enderror" autocomplete='on'>
                @error('fecha') <div class='invalid-feedback'>{{ $message }}</div> @enderror
            </div>

        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-info ml-4">{{ __('Create') }}</button>
            <a href="@route(getRouteName().'.encuesta.read')" class="btn btn-default float-left">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
