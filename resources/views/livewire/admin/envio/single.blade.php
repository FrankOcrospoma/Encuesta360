<tr x-data="{ modalIsOpen : false }">
    <td class="">{{ $envio->Persona }}</td>
    <td class="">{{ $envio->Encuesta }}</td>
    <td class="">{{ $envio->estado }}</td>
    <td class="">{{ $envio->uuid }}</td>
    
    @if(getCrudConfig('Envio')->delete or getCrudConfig('Envio')->update)
        <td>

            @if(getCrudConfig('Envio')->update && hasPermission(getRouteName().'.envio.update', 1, 1, $envio))
                <a href="@route(getRouteName().'.envio.update', $envio->id)" class="btn text-primary mt-1">
                    <i class="icon-pencil"></i>
                </a>
            @endif

            @if(getCrudConfig('Envio')->delete && hasPermission(getRouteName().'.envio.delete', 1, 1, $envio))
                <button @click.prevent="modalIsOpen = true" class="btn text-danger mt-1">
                    <i class="icon-trash"></i>
                </button>
                <div x-show="modalIsOpen" class="cs-modal animate__animated animate__fadeIn">
                    <div class="bg-white shadow rounded p-5" @click.away="modalIsOpen = false" >
                        <h5 class="pb-2 border-bottom">{{ __('DeleteTitle', ['name' => __('Envio') ]) }}</h5>
                        <p>{{ __('DeleteMessage', ['name' => __('Envio') ]) }}</p>
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
