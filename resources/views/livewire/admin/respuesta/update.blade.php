<div class="card">
    <div class="card-header p-0">
        <h3 class="card-title">{{ __('UpdateTitle', ['name' => __('Respuesta') ]) }}</h3>
        <div class="px-2 mt-4">
            <ul class="breadcrumb mt-3 py-3 px-4 rounded">
                <li class="breadcrumb-item"><a href="@route(getRouteName().'')" class="text-decoration-none">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="@route(getRouteName().'.respuesta.read')" class="text-decoration-none">{{ __(\Illuminate\Support\Str::plural('Respuesta')) }}</a></li>
                <li class="breadcrumb-item active">{{ __('Update') }}</li>
            </ul>
        </div>
    </div>

    <form class="form-horizontal" wire:submit.prevent="update" enctype="multipart/form-data">

        <div class="card-body">

                        <!-- Texto Input -->
            <div class='form-group'>
                <label for='input-texto' class='col-sm-2 control-label '> {{ __('Texto') }}</label>
                <input type='text' id='input-texto' wire:model.lazy='texto' class="form-control  @error('texto') is-invalid @enderror" placeholder='' autocomplete='on'>
                @error('texto') <div class='invalid-feedback'>{{ $message }}</div> @enderror
            </div>
            <!-- Score Input -->
            <div class='form-group'>
                <label for='input-score' class='col-sm-2 control-label '> {{ __('Score') }}</label>
                <input type='number' id='input-score' wire:model.lazy='score' class="form-control  @error('score') is-invalid @enderror" placeholder='' autocomplete='on'>
                @error('score') <div class='invalid-feedback'>{{ $message }}</div> @enderror
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
            <button type="submit" class="btn btn-info ml-4">{{ __('Update') }}</button>
            <a href="@route(getRouteName().'.respuesta.read')" class="btn btn-default float-left">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
