
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
    window.togglePersonalModal = function(open, empresaId) {
        const modal = document.getElementById('personalModal');
        if (modal) {
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
        } else {
            console.error('El modal con ID "personalModal" no se encontró en el DOM.');
        }
    };
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
    formData.append('empresa', empresaId);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

    let personalId = document.getElementById('input-personal-id').value;
    if (personalId) {
        formData.append('personal_id', personalId);
    }

    fetch('{{ route("personals.create") }}', {
        method: 'POST',
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            agregarPersonalATabla(data.personal);
            cerrarModalcrear();
        } else {
            throw new Error(data.message || "Error al crear el personal.");
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error: ' + error.message);
    });
}

function agregarPersonalATabla(personal) {
    const tabla = document.getElementById("tablaPersonal");
    if (tabla) { // Asegúrate de que el elemento exista
        const fila = document.createElement("tr");
        fila.innerHTML = `
            <td>${personal.dni}</td>
            <td>${personal.nombre}</td>
            <td>${personal.correo}</td>
            <td>${personal.telefono}</td>
            <td>${personal.cargo}</td>
            <td>
                <button class="btn text-primary mt-1" onclick="editarPersonal(${personal.id})">
                    <i class="icon-pencil"></i>
                </button>
                <button class="btn text-danger mt-1" onclick="confirmDelete(${personal.id}, this)">
                    <i class="icon-trash"></i>
                </button>
            </td>
        `;
        tabla.appendChild(fila);
    } else {
        console.error('No se encontró la tabla de personal en el DOM.');
    }
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
        function quitarEvaluador(element) {
        var evaluadoId = $(element).data('evaluado-id');
        console.log("Eliminando evaluado con ID: ", evaluadoId);
        // Subir hasta el ancestro correcto que sea un <li>
        $(element).closest('div.list-group-item').remove();
    }
    function toggleDropdown(collapseId) {
        var x = document.getElementById(collapseId);
        if (x.style.display === "none") {
            x.style.display = "block";
        } else {
            x.style.display = "none";
        }
    }
  // Variable global para mantener el conteo de los índices por persona
var indicesPorPersona = {};

function agregarVinculo(personaId) {
    var selectEvaluados = document.getElementById('input-evaluadores-' + personaId);
    var evaluadorId = selectEvaluados.value;
    var evaluadorNombre = selectEvaluados.options[selectEvaluados.selectedIndex].text;
    var tipoVinculo = document.getElementById('tipoVinculo-' + personaId);
    var vinculoId = tipoVinculo.value;
    var vinculoNombre = tipoVinculo.options[tipoVinculo.selectedIndex].text;

    // Verifica si la personaId ya tiene un contador, si no, lo inicializa a 0
    if (!indicesPorPersona[personaId]) {
        indicesPorPersona[personaId] = 0;
    }

    // Incrementa el índice para la persona actual
    indicesPorPersona[personaId]++;

    var listaEvaluadores = document.getElementById('lista-evaluadores-ul-' + personaId);
    var li = document.createElement('div');
    li.className = 'list-group-item d-flex justify-content-between align-items-center';
    li.draggable = true;
    li.innerHTML = `
        <span class="col-1">${indicesPorPersona[personaId]}</span>
        <span class="col-3">${evaluadorNombre}</span>
        <span class="col-3">${vinculoNombre}</span>
        <input type="hidden" name="evaluadoresSeleccionados[]" value="${evaluadorId}">
        <input type="hidden" name="evaluadoresVinculos[]" value="${vinculoId}">
        <button style="border-radius: 15%; width: 67px;" class="btn btn-danger btn-sm quitar-evaluador" data-evaluado-id="${evaluadorId}" onclick="quitarEvaluador(this)">
            <i class="fas fa-trash-alt" aria-hidden="true"></i>
        </button>
    `;
    listaEvaluadores.appendChild(li);

    // Lógica para actualizar el select y remover la opción añadida
    selectEvaluados.remove(selectEvaluados.selectedIndex);
}


function guardarVinculos(index) {
    const evaluadoresSeleccionados = [];
    const evaluadoresVinculos = [];
    const listaEvaluadores = document.querySelectorAll(`#lista-evaluadores-ul-${index} input[name="evaluadoresSeleccionados[]"]`);

    listaEvaluadores.forEach(input => {
        evaluadoresSeleccionados.push(input.value);
    });

    const listaVinculos = document.querySelectorAll(`#lista-evaluadores-ul-${index} input[name="evaluadoresVinculos[]"]`);

    listaVinculos.forEach(input => {
        evaluadoresVinculos.push(input.value);
    });

    const empresaId = document.querySelector('input[name="empresa_id"]').value;

    const dataToSend = {
        evaluadores: evaluadoresSeleccionados,
        empresa_id: empresaId,
        personal_id: index,
        vinculos: evaluadoresVinculos
    };

    console.log(dataToSend); // Depuración para ver los datos que se enviarán

    fetch('{{ route("agregar-vinculo") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(dataToSend)
    })
    .then(response => response.json())
    .then(data => {
        console.log('Respuesta del servidor:', data); // Más depuración
        alert('Vínculos guardados correctamente.');
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al guardar los vínculos: ' + error.message);
    });
}

function recuperarUltimosVinculos() {
    const empresaId = document.querySelector('input[name="empresa_id"]').value;

    fetch(`/recuperar-ultimos-vinculos?empresa_id=${empresaId}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        data.forEach(vinculo => {
            const evaluadorId = vinculo.evaluador_id;
            const vinculoId = vinculo.vinculo_id;
            const evaluadorNombre = vinculo.evaluador.nombre;
            const vinculoNombre = vinculo.vinculo.nombre;
            const personaId = vinculo.evaluado_id;
            const listaEvaluadores = document.getElementById(`lista-evaluadores-ul-${personaId}`);
            const selectEvaluados = document.getElementById(`input-evaluadores-${personaId}`);

            // Crear el elemento de la lista
            const li = document.createElement('div');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            li.draggable = true;
            li.innerHTML = `
                <span class="col-1">${personaId}</span>
                <span class="col-3">${evaluadorNombre}</span>
                <span class="col-3">${vinculoNombre}</span>
                <input type="hidden" name="evaluadoresSeleccionados[]" value="${evaluadorId}">
                <input type="hidden" name="evaluadoresVinculos[]" value="${vinculoId}">
                <button style="border-radius: 15%; width: 67px;" class="btn btn-danger btn-sm quitar-evaluador" data-evaluado-id="${evaluadorId}" onclick="quitarEvaluador(this)">  <i class="fas fa-trash-alt" aria-hidden="true"></i></button>
            `;
            listaEvaluadores.appendChild(li);

            // Eliminar la opción del selector si ya está en la lista
            for (let i = 0; i < selectEvaluados.options.length; i++) {
                if (selectEvaluados.options[i].value == evaluadorId) {
                    selectEvaluados.remove(i);
                    break;
                }
            }
        });
        alert('Últimos vínculos recuperados y agregados a las listas.');
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al recuperar los últimos vínculos: ' + error.message);
    });
}


</script>