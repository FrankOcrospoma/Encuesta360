
<div class="card">
    <div class="card-header p-0">
        <h3 class="card-title">{{ __('CreateTitle', ['name' => __('User') ]) }}</h3>
        <div class="px-2 mt-4">
            <ul class="breadcrumb mt-3 py-3 px-4 rounded">
                <li class="breadcrumb-item"><a href="@route(getRouteName().'')" class="text-decoration-none">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="@route(getRouteName().'.users.read')" class="text-decoration-none">{{ __(\Illuminate\Support\Str::plural('User')) }}</a></li>
                <li class="breadcrumb-item active">{{ __('Create') }}</li>
            </ul>
        </div>
    </div>

    <form class="form-horizontal" wire:submit.prevent="create" enctype="multipart/form-data">
        <div class="card-body">
            <!-- Name Input -->
            <div class='form-group'>
                <label for='input-name' class='col-sm-2 control-label '> {{ __('Name') }}</label>
                <input type='text' id='input-name' wire:model.lazy='name' class="form-control @error('name') is-invalid @enderror" placeholder='' autocomplete='on'>
                @error('name') <div class='invalid-feedback'>{{ $message }}</div> @enderror
            </div>
            <!-- Email Input -->
            <div class='form-group'>
                <label for='input-email' class='col-sm-2 control-label '> {{ __('Email') }}</label>
                <input type='email' id='input-email' wire:model.lazy='email' class="form-control @error('email') is-invalid @enderror" placeholder='' autocomplete='on'>
                @error('email') <div class='invalid-feedback'>{{ $message }}</div> @enderror
            </div>
            <!-- Password Input -->
            <div class='form-group'>
                <label for='input-password' class='col-sm-2 control-label '> {{ __('Password') }}</label>
                <input type='password' id='input-password' wire:model.lazy='password' class="form-control @error('password') is-invalid @enderror" placeholder=''>
                @error('password') <div class='invalid-feedback'>{{ $message }}</div> @enderror
            </div>
              <!-- Roles Radio Buttons -->
            <div class='form-group'>
                <label for='roles' class='col-sm-2 control-label'>Roles</label>
                <div id='roles'>
                    <div class="form-check form-check-inline">
                        <input type='radio' id='role-admin_empresa' name='role' value='admin_empresa' class="form-check-input" wire:model="selectedRole">
                        <label for='role-admin_empresa' class='form-check-label'>Admin Empresa</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input type='radio' id='role-super_admin' name='role' value='super_admin' class="form-check-input" wire:model="selectedRole">
                        <label for='role-super_admin' class='form-check-label'>Super Admin</label>
                    </div>
                    
                </div>
            </div>

            <!-- Empresa_id Input -->
            <div class='form-group' style='display: {{ $selectedRole == 'admin_empresa' ? 'block' : 'none' }};'>
                <label for='input-empresa' class='control-label'>{{ __('Empresa') }} <span style="color: red" class="required">*</span></label>
                <select name='empresa_id' wire:model.lazy='empresa_id' id='input-empresa' class="form-control @error('empresa') is-invalid @enderror">
                    @foreach(getCrudConfig('User')->inputs()['empresa_id']['select'] as $key => $value)
                        <option value='{{ $key }}'>{{ $value }}</option>
                    @endforeach
                </select>
                @error('empresa') <div class='invalid-feedback'>{{ $message }}</div> @enderror
            </div>
            
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-info ml-4">{{ __('Create') }}</button>
            <a href="@route(getRouteName().'.users.read')" class="btn btn-default float-left">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
<script>
    function toggleEmpresaSelect(role) {
        var empresaSelect = document.getElementById('empresa_id_group');
        if (role === 'admin_empresa') {
            empresaSelect.style.display = 'block';
        } else {
            empresaSelect.style.display = 'none';
        }
    }
    </script>
