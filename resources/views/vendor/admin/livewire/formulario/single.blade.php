<tr x-data="{ modalIsOpen : false }">

    <td class="">{{ $formulario->nombre }}</td>
    
    @if(getCrudConfig('Formulario')->delete or getCrudConfig('Formulario')->update)
        <td>

            @if(getCrudConfig('Formulario')->update && hasPermission(getRouteName().'.formulario.update', 1, 1, $formulario))
                <a href="@route(getRouteName().'.formulario.update', $formulario->id)" class="btn text-primary mt-1">
                    <i class="icon-pencil"></i>
                </a>
            @endif

            @if(getCrudConfig('Formulario')->delete && hasPermission(getRouteName().'.formulario.delete', 1, 1, $formulario))
                <button onclick="openModal()" class="btn text-danger mt-1">
                    <i class="icon-trash"></i>
                </button>
                <!-- Modal con estilo display none inicialmente -->
                <div id="deleteModal" style="display: none;" class="cs-modal animate__animated animate__fadeIn">
                    <div class="bg-white shadow rounded p-5" onclick="closeModal()">
                        <h5 class="pb-2 border-bottom">{{ __('DeleteTitle', ['name' => __('Formulario') ]) }}</h5>
                        <p>{{ __('DeleteMessage', ['name' => __('Formulario') ]) }}</p>
                        <div class="mt-5 d-flex justify-content-between">
                            <a wire:click.prevent="delete" class="text-white btn btn-success shadow">{{ __('Yes, Delete it.') }}</a>
                            <a onclick="closeModal()" class="text-white btn btn-danger shadow">{{ __('No, Cancel it.') }}</a>
                        </div>
                    </div>
                </div>
            @endif
        </td>
    @endif
</tr>

<script>
function openModal() {
    document.getElementById('deleteModal').style.display = '';
}

function closeModal() {
    document.getElementById('deleteModal').style.display = 'none';
}
</script>
