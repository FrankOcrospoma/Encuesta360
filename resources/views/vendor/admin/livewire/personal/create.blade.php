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
                <label for='input-dni' class='col-sm-2 control-label'> {{ __('Dni') }}</label>
                <div class="input-group mb-3">
                    <input type='text' id='input-dni' wire:model.lazy='dni' class="form-control  @error('dni') is-invalid @enderror" placeholder='' autocomplete='on'>
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" onclick="consultaDNI()">Buscar</button>
                    </div>
                </div>
                @error('dni') <div class='invalid-feedback'>{{ $message }}</div> @enderror
            </div>
            <!-- Nombre Input -->
            <div class='form-group'>
                <label for='input-nombre' class='col-sm-2 control-label '> {{ __('Nombre') }}</label>
                <input type='text' id='input-nombre' wire:model.defer='nombre' class="form-control  @error('nombre') is-invalid @enderror" placeholder='' autocomplete='on'>
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
            <button type="submit" class="btn btn-info ml-4">{{ __('Create') }}</button>
            <a href="@route(getRouteName().'.personal.read')" class="btn btn-default float-left">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>

<script>
function consultaDNI() {
    var dni = $("#input-dni").val();
    var url = "https://facturae-garzasoft.com/facturacion/buscaCliente/BuscaCliente2.php";
    if (location.protocol !== "https:") {
        url = "http://facturae-garzasoft.com/facturacion/buscaCliente/BuscaCliente2.php";
    }
    $.ajax({
        type: 'GET',
        url: url,
        data: "dni=" + dni + "&fe=N&token=qusEj_w7aHEpX",
        success: function(data) {
            data = JSON.parse(data);
            var nombreCompleto = data.apepat + " " + data.apemat + " " + data.nombres;
            @this.set('nombre', nombreCompleto);
        },
        error: function(xhr, status, error) {
            console.error("Error al obtener datos: ", error);
        }
    });
}

</script>
