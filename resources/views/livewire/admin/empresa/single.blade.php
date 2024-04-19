
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
            width: 80%; /* Se ajusta al contenido */
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

            <button onclick="togglePersonalModal(true, {{ $empresa->id }})" class="btn text-success mt-1">
                <i class="icon-user"></i> Personal
            </button>
            <!-- Modal de Personal -->
            <div style="display: none;" id="personalModal" class="cs-modal animate__animated animate__fadeIn">
                <div class="bg-white shadow rounded p-5" onclick="event.stopPropagation()">

                    <!-- Aquí se insertará la vista parcial cargada vía AJAX -->
                    <div class="modal-body">
                     
                        
               
                    </div>
                    <div class="modal-footer">
                        <button onclick="togglePersonalModal(false)" class="btn btn-danger">Cerrar</button>
                    </div>
                </div>
            </div>
     
      
  

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

                // Imprimir en consola
                console.log("Estado:", estado);
                console.log("Empresa ID:", empresaId);

                fetch(`/empresa/updateEstado/${empresaId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ estado })
                    
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json(); // Aquí puedes continuar manejando la respuesta
                })
                .then(data => console.log(data)) // Aquí puedes imprimir la respuesta del servidor
                .catch(error => console.error('Error:', error));
            });
        });
    });
</script>
<script>
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
                document.getElementById('input-nombre').value = nombreCompleto;
            },
            error: function(xhr, status, error) {
                console.error("Error al obtener datos: ", error);
            }
        });
    }
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

<script>
        document.addEventListener('DOMContentLoaded', function () {
    // Usar document o un selector más específico que ya exista en el DOM
    document.addEventListener('change', function (e) {
        if (e.target.matches('.switch2 input')) { // Verifica si el elemento que cambió es uno de tus inputs
            let estado = e.target.checked;
            let personalId = e.target.getAttribute('data-id');

            fetch(`/personal/updateEstado/${personalId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ estado })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Respuesta del servidor:', data);
            })
            .catch(error => console.error('Error:', error));
        }
    });
    });

</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
    bsCustomFileInput.init(); // Si estás usando Bootstrap 4, esto mejora los input de archivo.
    });

    function abrirModalcrear() {
        let personalId = document.getElementById('input-personal-id').value;
        let botonTexto = personalId ? 'Actualizar' : 'Crear';

        document.getElementById('btnCrearActualizarPersonal').textContent = botonTexto + ' Personal';

        document.getElementById('crearPersonalForm').style.display = '';
        document.getElementById('personalList').style.display = 'none';
    }

        function cerrarModalcrear() {
            document.getElementById('crearPersonalForm').style.display = 'none';
            document.getElementById('personalList').style.display = '';
        }

</script>

<script>
    function enviarDatos(empresaId) {
        let formData = new FormData();
        formData.append('dni', document.getElementById('input-dni').value);
        formData.append('nombre', document.getElementById('input-nombre').value);
        formData.append('correo', document.getElementById('input-correo').value);
        formData.append('telefono', document.getElementById('input-telefono').value);
        formData.append('cargo', document.getElementById('input-cargo').value);
        formData.append('empresa', empresaId); // Utiliza el id de la empresa pasado como argumento
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        // Añadir el ID del personal si está presente
        let personalId = document.getElementById('input-personal-id').value;
        if (personalId) {
            formData.append('personal_id', personalId);
        }

        fetch('{{ route("personals.create") }}', {
            method: 'POST',
            body: formData,
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Respuesta de red fallida');
            }
            return response.json();
        })
        .then(data => {
            // Aquí puedes agregar lógica adicional para manejar la respuesta,
            // como actualizar la interfaz de usuario o mostrar un mensaje de éxito/error.
            console.log(data);
            togglePersonalModal(true, empresaId); 
            cerrarModalcrear(); 
        })
        .catch(error => {
            console.error('Error:', error);
            alert('El Dni ya esta registrado.');
        });
    }



    
</script>
<script>
    function confirmDelete(personaId, empresaId) {
        $('#deleteConfirmButton').off('click').on('click', function() {
            $('#confirmDeleteModal').modal('hide');
            
            $.ajax({
                url: "/persona/delete/" + personaId,
                type: "GET",
                success: function(response) {
                    if(response.success) {
                        togglePersonalModal(true, empresaId);
                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error en la solicitud AJAX: ", status, error);
                }
            });
        });

        $('#confirmDeleteModal').modal();
    }

</script>
<script>

    function editarPersonal(personalId) {
        fetch(`/personal/editar/${personalId}`) // Asegúrate de tener esta ruta en tu controlador
        .then(response => response.json())
        .then(data => {
            // Suponiendo que 'data' es el objeto con los datos del personal
            document.getElementById('input-dni').value = data.dni;
            document.getElementById('input-nombre').value = data.nombre;
            document.getElementById('input-correo').value = data.correo;
            document.getElementById('input-telefono').value = data.telefono;
            document.getElementById('input-cargo').value = data.cargo;
            document.getElementById('input-personal-id').value = personalId; // Para el campo oculto
            console.log(personalId)
            // Mostrar el formulario
            abrirModalcrear();
        })
        .catch(error => console.error('Error:', error));
        
    }
        
</script>
 
<script>
    function mostrarVinculos() {
        var personalList = document.getElementById("personalList");
        var vinculosSection = document.getElementById("vinculosSection");
        
        if (personalList.style.display === "none") {
            personalList.style.display = "block";
            vinculosSection.style.display = "none";
        } else {
            personalList.style.display = "none";
            vinculosSection.style.display = "block";
        }
    }
  
</script>

<script>
    function toggleDropdown(collapseId) {
        var x = document.getElementById(collapseId);
        if (x.style.display === "none") {
            x.style.display = "block";
        } else {
            x.style.display = "none";
        }
    }
function agregarVinculo(personaId, empresaId) {
    var evaluadoId = document.getElementById("nuevoVinculo" + personaId).value;
    var tipoVinculo = document.getElementById("tipoVinculo" + personaId).value;
    $.ajax({
        url: '/agregar-vinculo',
        type: 'POST',
        data: {
            _token: "{{ csrf_token() }}",
            persona_id: personaId,
            evaluado_id: evaluadoId,
            tipo_vinculo: tipoVinculo,
            empresa_id: empresaId
        },
        success: function(response) {
            // Añadir el nuevo elemento a la lista sin recargar la página
            var ul = document.getElementById("vinculosLista" + personaId);
            var li = document.createElement("li");
            li.textContent = response.nombre + " - " + response.vinculo;
            ul.appendChild(li);

            // Actualizar el select, eliminando la opción añadida
            var select = document.getElementById("nuevoVinculo" + personaId);
            for (var i = 0; i < select.options.length; i++) {
                if (select.options[i].value == evaluadoId) {
                    select.remove(i);
                    break;
                }
            }

            // Opcional: limpiar el formulario
            document.getElementById("tipoVinculo" + personaId).value = '';
        },
        error: function(xhr) {
            var errorMessage = 'Error al guardar el vínculo.';
            if(xhr.responseJSON && xhr.responseJSON.error) {
                errorMessage += " Detalle: " + xhr.responseJSON.error;
            } else if(xhr.responseText) {
                errorMessage += " Detalle: " + xhr.responseText;
            }
            alert(errorMessage);
        }
    });
}


</script>
    
