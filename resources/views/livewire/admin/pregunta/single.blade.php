<tr x-data="{ modalIsOpen : false }">
    <td class="">{{ $pregunta->texto }}</td>
    <td class="">{{ $pregunta->Categoria }}</td>
    <td class="">
        @if($pregunta->estado == 1)
        Para Marcar

        @else
            Abierta
        @endif
    </td>    
    @if(getCrudConfig('Pregunta')->delete or getCrudConfig('Pregunta')->update)
        <td>

            @if(getCrudConfig('Pregunta')->update && hasPermission(getRouteName().'.pregunta.update', 1, 1))
                <a href="@route(getRouteName().'.pregunta.update', $pregunta->id)" class="btn text-primary mt-1">
                    <i class="icon-pencil"></i>
                </a>
            @endif

            @if(getCrudConfig('Pregunta')->delete && hasPermission(getRouteName().'.pregunta.delete', 1, 1))
                <button @click.prevent="modalIsOpen = true" class="btn text-danger mt-1">
                    <i class="icon-trash"></i>
                </button>
                <div x-show="modalIsOpen" class="cs-modal animate__animated animate__fadeIn">
                    <div class="bg-white shadow rounded p-5" @click.away="modalIsOpen = false" >
                        <h5 class="pb-2 border-bottom">{{ __('DeleteTitle', ['name' => __('Pregunta') ]) }}</h5>
                        <p>{{ __('DeleteMessage', ['name' => __('Pregunta') ]) }}</p>
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
