<div class="card">
    <div class="card-header p-0">
        <h3 class="card-title">{{ __('CreateTitle', ['name' => __('Personal') ]) }}</h3>
        <div class="px-2 mt-4">
            <ul class="breadcrumb mt-3 py-3 px-4 rounded">
                <li class="breadcrumb-item"><a href="@route(getRouteName().'')" class="text-decoration-none">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="@route(getRouteName().'.personal.read')" class="text-decoration-none">{{ __(\Illuminate\Support\Str::plural('Personal')) }}</a></li>
                <li class="breadcrumb-item active">{{ __('Create') }}</li>
            </ul>
        </div>
    </div>

    <form class="form-horizontal" wire:submit.prevent="create" enctype="multipart/form-data">

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
            <!-- Correo Input -->
            <div class='form-group'>
                <label for='input-correo' class='col-sm-2 control-label '> {{ __('Correo') }}</label>
                <input type='email' id='input-correo' wire:model.lazy='correo' class="form-control  @error('correo') is-invalid @enderror" placeholder='' autocomplete='on'>
                @error('correo') <div class='invalid-feedback'>{{ $message }}</div> @enderror
            </div>
            <!-- Telefono Input -->
            <div class='form-group'>
                <label for='input-telefono' class='col-sm-2 control-label '> {{ __('Telefono') }}</label>
                <input type='number' id='input-telefono' wire:model.lazy='telefono' class="form-control  @error('telefono') is-invalid @enderror" placeholder='' autocomplete='on'>
                @error('telefono') <div class='invalid-feedback'>{{ $message }}</div> @enderror
            </div>
            <!-- Cargo Input -->
            <div class='form-group'>
                <label for='input-cargo' class='col-sm-2 control-label '> {{ __('Cargo') }}</label>
                <input type='text' id='input-cargo' wire:model.lazy='cargo' class="form-control  @error('cargo') is-invalid @enderror" placeholder='' autocomplete='on'>
                @error('cargo') <div class='invalid-feedback'>{{ $message }}</div> @enderror
            </div>
            <!-- Estado Input -->
            <div class='form-group'>
                <div class='form-check mt-4 mb-3'>
                    <input wire:model.lazy='estado' id='input-estado' class='form-check-input ' type='checkbox' autocomplete='on'>
                    <label class='form-check-label ' for='input-estado'>{{ __('Estado') }}</label>
                </div>
                @error('estado') <div class='invalid-feedback'>{{ $message }}</div> @enderror
            </div>

        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-info ml-4">{{ __('Create') }}</button>
            <a href="@route(getRouteName().'.personal.read')" class="btn btn-default float-left">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
