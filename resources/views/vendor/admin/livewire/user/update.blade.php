<div class="card">
    <div class="card-header p-0">
        <h3 class="card-title">{{ __('UpdateTitle', ['name' => __('User') ]) }}</h3>
        <div class="px-2 mt-4">
            <ul class="breadcrumb mt-3 py-3 px-4 rounded">
                <li class="breadcrumb-item"><a href="@route(getRouteName().'')" class="text-decoration-none">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="@route(getRouteName().'.users.read')" class="text-decoration-none">{{ __(\Illuminate\Support\Str::plural('User')) }}</a></li>
                <li class="breadcrumb-item active">{{ __('Update') }}</li>
            </ul>
        </div>
    </div>

    <form class="form-horizontal" wire:submit.prevent="update" enctype="multipart/form-data">

        <div class="card-body">

                        <!-- Name Input -->
            <div class='form-group'>
                <label for='input-name' class='col-sm-2 control-label '> {{ __('Name') }}</label>
                <input type='text' id='input-name' wire:model.lazy='name' class="form-control  @error('name') is-invalid @enderror" placeholder='' autocomplete='on'>
                @error('name') <div class='invalid-feedback'>{{ $message }}</div> @enderror
            </div>
            <!-- Email Input -->
            <div class='form-group'>
                <label for='input-email' class='col-sm-2 control-label '> {{ __('Email') }}</label>
                <input type='email' id='input-email' wire:model.lazy='email' class="form-control  @error('email') is-invalid @enderror" placeholder='' autocomplete='on'>
                @error('email') <div class='invalid-feedback'>{{ $message }}</div> @enderror
            </div>
            <!-- Password Input -->
            <div class='form-group'>
                <label for='inputpassword' class='col-sm-2 control-label '> {{ __('Password') }}</label>
                <input type='password' id='input-password' wire:model.lazy='password' class="form-control  @error('password') is-invalid @enderror" placeholder=''>
                @error('password') <div class='invalid-feedback'>{{ $message }}</div> @enderror
            </div>
            <!-- Roles Radio Buttons -->
            <div class='form-group'>
                <label for='roles' class='col-sm-2 control-label'>Roles</label>
                <div id='roles'>
                    <div class="form-check form-check-inline">
                        <input type='radio' id='role-admin_empresa' name='role' value='admin_empresa' class="form-check-input">
                        <label for='role-admin_empresa' class='form-check-label'>Admin Empresa</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input type='radio' id='role-super_admin' name='role' value='super_admin' class="form-check-input">
                        <label for='role-super_admin' class='form-check-label'>Super Admin</label>
                    </div>
                </div>
            </div>

            <!-- Empresa_id Input -->
            <div class='form-group' id='empresa_id_group'>
                <label for='input-empresa_id' class='col-sm-2 control-label'>{{ __('Empresa_id') }}</label>
                <select id='input-empresa_id' wire:model.lazy='empresa_id' class="form-control">
                    @foreach(getCrudConfig('User')->inputs()['empresa_id']['select'] as $key => $value)
                        <option value='{{ $key }}'>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-info ml-4">{{ __('Update') }}</button>
            <a href="@route(getRouteName().'.users.read')" class="btn btn-default float-left">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const empresaIdSelect = document.getElementById('input-empresa_id');
        const adminEmpresaRadio = document.getElementById('role-admin_empresa');
        const superAdminRadio = document.getElementById('role-super_admin');
        const empresaIdGroup = document.getElementById('empresa_id_group');

        function updateRoleVisibility() {
            // Usa parseInt para asegurarte de que se maneja un número
            const empresaIdValue = parseInt(empresaIdSelect.value, 10);
            if (empresaIdValue > 0) {
                adminEmpresaRadio.checked = true;
                empresaIdGroup.style.display = '';
            } else {
                superAdminRadio.checked = true;
                empresaIdGroup.style.display = 'none';
            }
        }

        // Escucha eventos de cambio en el select para ajustar la visibilidad basada en la selección actual
        empresaIdSelect.addEventListener('change', updateRoleVisibility);

        // Asegura que se revisa el estado inicial después de que Livewire pueda haber actualizado los valores
        setTimeout(updateRoleVisibility, 50);

        // Actualiza cuando se cambian las opciones de los roles
        document.querySelectorAll('input[name="role"]').forEach(radio => {
            radio.addEventListener('change', () => {
                if (radio.value === 'admin_empresa') {
                    empresaIdGroup.style.display = '';
                } else {
                    empresaIdGroup.style.display = 'none';
                    empresaIdSelect.value = '';
                }
            });
        });
    });
</script>



