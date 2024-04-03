
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
/* Estilos del modal para centrado vertical y horizontal */
.cs-modal {
    display: none; 
    position: fixed;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow-y: auto; /* Permite desplazamiento vertical si el contenido excede la altura */
    background-color: rgba(0,0,0,0.4); /* Fondo semitransparente */
    display: flex;
    align-items: center; /* Centrado vertical */
    justify-content: center; /* Centrado horizontal */
    z-index: 9999; /* Asegura que el modal esté sobre otros elementos */
}

.cs-modal .bg-white {
    margin: auto;
    width: 50%; /* Se ajusta al contenido */
    height: 80%;
    max-width: 190%; /* Un máximo que asegura margen con los bordes */
    padding: 20px; /* Espacio dentro del modal */
    background: #fff; /* Fondo blanco del modal */
    border-radius: 5px; /* Bordes redondeados del modal */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Sombra sutil */
    overflow-y: auto; /* Asegura desplazamiento vertical dentro del modal si es necesario */
}
.modal-body {
    width: 100%; /* Ocupa todo el ancho disponible */
}

.cs-modal div {
     max-width: 100%;
    z-index: 30;
}
/* Estilos actualizados para el modal para incluir la barra inferior */
.cs-modal .bg-white {
    display: flex;
    flex-direction: column; /* Organiza los hijos del modal en columna */
    justify-content: space-between; /* Separa el contenido del pie */
}

.modal-body {
    flex-grow: 1; /* Permite que el contenido crezca para llenar el espacio */
    overflow-y: auto; /* Mantiene el desplazamiento si el contenido excede la altura */
}

/* Estilos para la barra inferior del modal */
.modal-footer {
    width: 100%; /* Ocupa todo el ancho disponible del modal */
    padding: 10px 20px; /* Espaciado dentro de la barra */
    background: #fff; /* Fondo blanco */
    border-top: 1px solid #eee; /* Borde superior para separar del contenido */
    display: flex; /* Permite alinear los elementos dentro fácilmente */
    justify-content: flex-end; /* Alinea el botón a la derecha */
}
    </style>        
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

            @if(getCrudConfig('Empresa')->create)
            <button onclick="togglePersonalModal(true, {{ $empresa->id }})" class="btn text-success mt-1">
                <i class="icon-user"></i> Personal
            </button>
            <!-- Modal de Personal -->
            <div style="display: none;" id="personalModal" class="cs-modal animate__animated animate__fadeIn">
                <div class="bg-white shadow rounded p-5" onclick="event.stopPropagation()">
                    <h5 class="pb-2 border-bottom">Personas de la Empresa: {{ $empresa->nombre }}</h5>
                    <!-- Aquí se insertará la vista parcial cargada vía AJAX -->
                    <div class="modal-body">
                        <!-- El contenido del personal se cargará aquí -->
                    </div>
                    <div class="modal-footer">
                        <button onclick="togglePersonalModal(false)" class="btn btn-danger">Cerrar</button>
                    </div>
                </div>
            </div>
            @endif
            

        </td>
    @endif
</tr>

<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.switch input').forEach(item => {
            item.addEventListener('change', function () {
                let estado = this.checked;
                let empresaId = this.getAttribute('data-id');
                fetch(`/empresa/updateEstado/${empresaId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ estado })
                })
                .catch(error => console.error('Error:', error));
            });
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Lógica para el switch omitida para brevedad
        window.togglePersonalModal = function(open, empresaId) {
    const modal = document.getElementById('personalModal');
    if (open) {
        fetch(`/empresa/personal/${empresaId}`)
            .then(response => response.text())
            .then(html => {
                modal.querySelector('.modal-body').innerHTML = html;
                modal.style.display = 'flex';
            })
            .catch(error => console.error('Error cargando el personal:', error));
    } else {
        modal.style.display = 'none';
    }
};

        // Cerrar el modal si se hace clic fuera de él
        document.getElementById('personalModal').addEventListener('click', function(event) {
            if (event.target === this) {
                togglePersonalModal(false);
            }
        });
    });
    </script>