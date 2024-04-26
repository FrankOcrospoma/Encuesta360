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

    <form class="form-horizontal" wire:submit.prevent="update" enctype="multipart/form-data">

        <div class="card-body">

                        <!-- Dni Input -->
            <div class='form-group'>
                <label for='input-dni' class='col-sm-2 control-label '> {{ __('Dni') }}</label>
                <input type='text' id='input-dni' wire:model.lazy='dni' class="form-control  @error('dni') is-invalid @enderror" placeholder='' autocomplete='on'>
                @error('dni') <div class='invalid-feedback'>{{ $message }}</div> @enderror
            </div>
            <!-- Nombre Input -->
            <div class='form-group'>
                <label for='input-nombre' class='col-sm-2 control-label '> {{ __('Nombre') }}</label>
                <input type='text' id='input-nombre' wire:model.lazy='nombre' class="form-control  @error('nombre') is-invalid @enderror" placeholder='' autocomplete='on'>
                @error('nombre') <div class='invalid-feedback'>{{ $message }}</div> @enderror
            </div>
            <!-- Cargo Input -->
            <div class='form-group'>
                <label for='input-cargo' class='col-sm-2 control-label '> {{ __('Cargo') }}</label>
                <select id='input-cargo' wire:model.lazy='cargo' class="form-control  @error('cargo') is-invalid @enderror">
                    @foreach(getCrudConfig('Personal')->inputs()['cargo']['select'] as $key => $value)
                        <option value='{{ $key }}'>{{ $value }}</option>
                    @endforeach
                </select>
                @error('cargo') <div class='invalid-feedback'>{{ $message }}</div> @enderror
            </div>
            <!-- Empresa Input -->
            <div class='form-group'>
                <label for='input-empresa' class='col-sm-2 control-label '> {{ __('Empresa') }}</label>
                <select id='input-empresa' wire:model.lazy='empresa' class="form-control  @error('empresa') is-invalid @enderror">
                    @foreach(getCrudConfig('Personal')->inputs()['empresa']['select'] as $key => $value)
                        <option value='{{ $key }}'>{{ $value }}</option>
                    @endforeach
                </select>
                @error('empresa') <div class='invalid-feedback'>{{ $message }}</div> @enderror
            </div>


        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-info ml-4">{{ __('Update') }}</button>
            <a href="@route(getRouteName().'.personal.read')" class="btn btn-default float-left">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
