
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* El contenedor del switch - hazlo como quieras */
        .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
        }

        /* Esconde el input */
        .switch input { 
        opacity: 0;
        width: 0;
        height: 0;
        }

        /* El deslizador */
        .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ff4f70;
        -webkit-transition: .4s;
        transition: .4s;
        }

        .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
        }

        input:checked + .slider {
        background-color: #22ca80;
        }

        input:focus + .slider {
        box-shadow: 0 0 1px #22ca80;
        }

        input:checked + .slider:before {
        -webkit-transform: translateX(26px);
        -ms-transform: translateX(26px);
        transform: translateX(26px);
        }

        /* Forma redonda */
        .slider.round {
        border-radius: 34px;
        }

        .slider.round:before {
        border-radius: 50%;
        }

    </style>
<body>

<tr x-data="{ modalIsOpen : false }">
    <td class="">{{ $empresa->ruc }}</td>
    <td class="">{{ $empresa->nombre }}</td>
    <td class="">{{ $empresa->direccion }}</td>
    <td class="">{{ $empresa->representante }}</td>
    <td class="">
        <label class="switch">
            <input type="checkbox" data-id="{{ $empresa->id }}" {{ $empresa->estado ? 'checked' : '' }} class="switch">
            <span class="slider round"></span>
        </label>
    </td>    
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.switch input').forEach(item => {
            item.addEventListener('change', function () {
                let estado = this.checked;
                let empresalId = this.getAttribute('data-id'); // Asegúrate de añadir el atributo data-id al input
                fetch(`/empresa/updateEstado/${empresalId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // CSRF token
                    },
                    body: JSON.stringify({ estado })
                })

                .catch(error => console.error('Error:', error));
            });
        });
    });
    </script>
    
    
    
</body>
