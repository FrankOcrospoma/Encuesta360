<tr x-data="{ modalIsOpen : false }">
    <td class="">{{ $respuesta->id }}</td>
    <td class="">{{ $respuesta->texto }}</td>
    <td class="">{{ $respuesta->score }}</td>
    <td class="">{{ $respuesta->estado }}</td>
    
    @if(getCrudConfig('Respuesta')->delete or getCrudConfig('Respuesta')->update)
        <td>

            @if(getCrudConfig('Respuesta')->update && hasPermission(getRouteName().'.respuesta.update', 1, 1, $respuesta))
                <a href="@route(getRouteName().'.respuesta.update', $respuesta->id)" class="btn text-primary mt-1">
                    <i class="icon-pencil"></i>
                </a>
            @endif

            @if(getCrudConfig('Respuesta')->delete && hasPermission(getRouteName().'.respuesta.delete', 1, 1, $respuesta))
                <button @click.prevent="modalIsOpen = true" class="btn text-danger mt-1">
                    <i class="icon-trash"></i>
                </button>
                <div x-show="modalIsOpen" class="cs-modal animate__animated animate__fadeIn">
                    <div class="bg-white shadow rounded p-5" @click.away="modalIsOpen = false" >
                        <h5 class="pb-2 border-bottom">{{ __('DeleteTitle', ['name' => __('Respuesta') ]) }}</h5>
                        <p>{{ __('DeleteMessage', ['name' => __('Respuesta') ]) }}</p>
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
