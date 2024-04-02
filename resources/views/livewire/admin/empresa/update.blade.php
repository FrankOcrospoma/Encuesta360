<div class="card">
    <div class="card-header p-0">
        <h3 class="card-title">{{ __('UpdateTitle', ['name' => __('Empresa') ]) }}</h3>
        <div class="px-2 mt-4">
            <ul class="breadcrumb mt-3 py-3 px-4 rounded">
                <li class="breadcrumb-item"><a href="@route(getRouteName().'.home')" class="text-decoration-none">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="@route(getRouteName().'.empresa.read')" class="text-decoration-none">{{ __(\Illuminate\Support\Str::plural('Empresa')) }}</a></li>
                <li class="breadcrumb-item active">{{ __('Update') }}</li>
            </ul>
        </div>
    </div>

    <form class="form-horizontal" wire:submit.prevent="update" enctype="multipart/form-data">

        <div class="card-body">

                        <!-- Ruc Input -->
            <div class='form-group'>
                <label for='input-ruc' class='col-sm-2 control-label '> {{ __('Ruc') }}</label>
                <input type='text' id='input-ruc' wire:model.lazy='ruc' class="form-control  @error('ruc') is-invalid @enderror" placeholder='' autocomplete='on'>
                @error('ruc') <div class='invalid-feedback'>{{ $message }}</div> @enderror
            </div>
            <!-- Nombre Input -->
            <div class='form-group'>
                <label for='input-nombre' class='col-sm-2 control-label '> {{ __('Nombre') }}</label>
                <input type='text' id='input-nombre' wire:model.lazy='nombre' class="form-control  @error('nombre') is-invalid @enderror" placeholder='' autocomplete='on'>
                @error('nombre') <div class='invalid-feedback'>{{ $message }}</div> @enderror
            </div>
            <!-- Direccion Input -->
            <div class='form-group'>
                <label for='input-direccion' class='col-sm-2 control-label '> {{ __('Direccion') }}</label>
                <input type='text' id='input-direccion' wire:model.lazy='direccion' class="form-control  @error('direccion') is-invalid @enderror" placeholder='' autocomplete='on'>
                @error('direccion') <div class='invalid-feedback'>{{ $message }}</div> @enderror
            </div>
              <!-- Dni Input -->
              <div class='form-group'>
                <label class='col-sm-2 control-label'> {{ __('Dni') }}</label>
                <div class="input-group mb-3">
                    <input type='text' id='input-dni' class="form-control" placeholder='' autocomplete='on'>
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" onclick="consultaDNI()">Buscar</button>
                    </div>
                </div>
            
            </div>
            <!-- Representante Input -->
            <div class='form-group'>
                <label for='input-representante' class='col-sm-2 control-label '> {{ __('Representante') }}</label>
                <input type='text' id='input-representante' wire:model.lazy='representante' class="form-control  @error('representante') is-invalid @enderror" placeholder='' autocomplete='on'>
                @error('representante') <div class='invalid-feedback'>{{ $message }}</div> @enderror
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
            <a href="@route(getRouteName().'.empresa.read')" class="btn btn-default float-left">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
<script>
    function consultaRUC() {
        var ruc = $("#input-ruc").val();
        var url = "https://comprobante-e.com/facturacion/buscaCliente/BuscaClienteRuc.php";
        if (location.protocol !== "https:") {
            url = "http://comprobante-e.com/facturacion/buscaCliente/BuscaClienteRuc.php";
        }
        $.ajax({
            type: 'GET',
            url: url,
            data: "fe=N&token=qusEj_w7aHEpX&ruc=" + ruc,
            success: function(data) {
                data = JSON.parse(data);
                @this.set('nombre', data.RazonSocial);
                @this.set('direccion', data.Direccion);
            },
            error: function(xhr, status, error) {
                console.error("Error al obtener datos: ", error);
            }
        });
    }

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
                @this.set('representante', nombreCompleto);
            },
            error: function(xhr, status, error) {
                console.error("Error al obtener datos: ", error);
            }
        });
    }
</script>