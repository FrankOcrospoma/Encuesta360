<tr x-data="{ modalIsOpen : false }">
    <td class="">{{ $empresa->ruc }}</td>
    <td class="">{{ $empresa->nombre }}</td>
    <td class="">{{ $empresa->direccion }}</td>
    <td class="">{{ $empresa->representante }}</td>
    
    @if(getCrudConfig('Empresa')->delete or getCrudConfig('Empresa')->update)
        <td>

            @if(getCrudConfig('Empresa')->update && hasPermission(getRouteName().'.empresa.update', 1, 1, $empresa))
                <a href="@route(getRouteName().'.empresa.update', $empresa->id)" class="btn text-primary mt-1">
                    <i class="icon-pencil"></i>
                </a>
            @endif

            @if(getCrudConfig('Empresa')->delete && hasPermission(getRouteName().'.empresa.delete', 1, 1, $empresa))
                <button @click.prevent="modalIsOpen = true" class="btn text-danger mt-1">
                    <i class="icon-trash"></i>
                </button>
                <div x-show="modalIsOpen" class="cs-modal animate__animated animate__fadeIn">
                    <div class="bg-white shadow rounded p-5" @click.away="modalIsOpen = false" >
                        <h5 class="pb-2 border-bottom">{{ __('DeleteTitle', ['name' => __('Empresa') ]) }}</h5>
                        <p>{{ __('DeleteMessage', ['name' => __('Empresa') ]) }}</p>
                        <div class="mt-5 d-flex justify-content-between">
                            <a wire:click.prevent="delete" class="text-white btn btn-success shadow">{{ __('Yes, Delete it.') }}</a>
                            <a @click.prevent="modalIsOpen = false" class="text-white btn btn-danger shadow">{{ __('No, Cancel it.') }}</a>
                        </div>
                    </div>
                </div>
            @endif
        </td>
    @endif
</tr>
