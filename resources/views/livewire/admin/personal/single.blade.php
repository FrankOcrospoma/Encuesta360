<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.0/font/bootstrap-icons.css">
<!-- CSS de Bootstrap -->

<?php
use App\Models\Evaluado;
use App\Models\Detalle_empresa;
use App\Models\Personal;
use App\Models\Vinculo;

use App\Models\Empresa; 
$empresas = Empresa::where('estado',1)->get();
$vinculados = Evaluado::where('empresa_id', $empresa->id)->where('encuesta_id', null)->get();

$ultimosVin = Evaluado::with(['evaluador', 'vinculo'])
                        ->where('empresa_id', $empresa->id)
                        ->whereNotNull('encuesta_id')
                        ->orderBy('encuesta_id', 'desc')
                        ->get();
$vinculos = Vinculo::where('vigencia',1)->get();

$detalle = Detalle_empresa::where('empresa_id', $empresa->id)->get(); 
$personalIds = $detalle->pluck('personal_id'); 
$personals = Personal::whereIn('id', $personalIds)->get(); 
?>
<div id="vinculosSection" style="display: none;">
    <h5 class="pb-2 border-bottom">Relacionar Personas</h5>

    @if (!$ultimosVin->isEmpty())
    <div class="col-md-4">
        <button class="btn btn-info" onclick="recuperarUltimosVinculos()">Recuperar Últimos Vínculos</button>
    </div>
    @endif
    <br>
    
    <div class="accordion" id="accordionVinculos">
        
        @foreach($personals as $index => $persona)
        <input type="hidden" name="_token" value="{{ csrf_token() }}">

        <div class="card">
            <div class="card-header" id="heading{{ $index }}">
                <h2 class="mb-0">
                    <button onclick="toggleDropdown('collapse{{ $index }}')" class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse{{ $index }}" aria-expanded="false" aria-controls="collapse{{ $index }}">
                        {{ $persona->nombre }}
                    </button>
                </h2>
            </div>
            <div id="collapse{{ $index }}" class="collapse" aria-labelledby="heading{{ $index }}" data-parent="#accordionVinculos">
                <div class="card-body">
                    <input type="hidden" name="empresa_id" value="{{ $empresa->id }}">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-5">
                                <label for="input-evaluadores">Seleccionar persona:</label>
                                @php
                                    // Obtener IDs de personas ya vinculadas
                                    $vinculadosIds = $vinculados->where('evaluado_id', $persona->id)->pluck('evaluador_id')->toArray();
                                @endphp
                               <select class="form-control" id="input-evaluadores-{{ $persona->id }}">


                                    @foreach ($personals as $otraPersona)
                                        @if (!in_array($otraPersona->id, $vinculadosIds))
                                            <option value="{{ $otraPersona->id }}">{{ $otraPersona->nombre }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label>Seleccionar el vinculo:</label>
                                <select class="form-control" id="tipoVinculo-{{ $persona->id }}">
                                    @foreach ($vinculos as $vinculo)
                                        <option value="{{ $vinculo->id }}" >{{ $vinculo->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 align-self-end">
                                <label></label><br>
                                <button class="btn btn-outline-secondary ml-2" type="button" onclick="agregarVinculo({{ $persona->id }})">Añadir</button>
                            </div>
                        </div>
                    </div>
                        
                        
                    <div id="lista-evaluadores">
                        <ul class="list-group" id="lista-evaluadores-ul-{{ $persona->id }}">
                            <li class="list-group-item list-group-item-info d-flex justify-content-between align-items-center" >
                                <span class="col-1">#</span>
                                <span class="col-3">Evaluador</span>
                                <span class="col-3">Vínculo</span>
                
                                <span>Acciones</span>
                            </li>
                    @foreach ($vinculados as $index => $vinculado)
                        @if ($vinculado->evaluado_id == $persona->id)
                            <div  class="list-group-item d-flex justify-content-between align-items-center" draggable = true>
                                <span class="col-1">{{ $index + 1 }}</span> <!-- Índice incremental -->

                                <span class="col-3"> {{ $vinculado->evaluador->nombre }} </span> 
                                <span class="col-3"> {{ $vinculado->vinculo->nombre }} </span> 
                                <input type="hidden" name="evaluadoresSeleccionados[]" value="{{ $vinculado->evaluador_id }}">
                                <input type="hidden" name="evaluadoresVinculos[]" value="{{ $vinculado->vinculo_id }}">

                                <button style="border-radius: 15%; width: 67px;" class="btn btn-danger btn-sm quitar-evaluador" data-evaluado-id="{{ $vinculado->evaluador_id }}" onclick="quitarEvaluador(this)">
                                    <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                </button>
                                

                            </div>
                        @endif
                    @endforeach
                        </ul>
                    </div>
                    <button id="btnGuardarVinculos" class="btn btn-success mt-3" onclick="guardarVinculos({{ $persona->id }})">Guardar Vínculos</button>

                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
<div id="crearPersonalForm" style="display: none;">
    <div class="card">
        <form class="form-horizontal" method="POST" action="{{ route('personals.create') }}" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
               
                <!-- ID del personal, necesario solo para actualizar -->
                <input type="hidden" name="personal_id" id="input-personal-id">

        
                <!-- Empresa Input -->
                <div class='form-group'>
                    <label for='input-empresa' class='col-sm-2 control-label'>{{ __('Empresa') }} <span style="color: red" class="required" >*</span></label>
                    <select disabled id='input-empresa' name='empresa' class="form-control @error('empresa') is-invalid @enderror">
                        @foreach($empresas as $key => $value)
                            <option value='{{ $key }}' {{ $empresa->id == $value->id ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                    @error('empresa') <div class='invalid-feedback'>{{ $message }}</div> @enderror
                </div>
                <!-- Dni Input -->
                <div class='form-group'>
                    <label for='input-dni' class='col-sm-2 control-label'> {{ __('Dni') }} </label>
                    <div class="input-group mb-3">
                        <input type='text' name='dni' id='input-dni' class="form-control @error('dni') is-invalid @enderror" placeholder='' autocomplete='on'>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" onclick="consultaDNI()">Buscar</button>
                        </div>
                    </div>
                    @error('dni') <div class='invalid-feedback'>{{ $message }}</div> @enderror
                </div>
                <!-- Nombre Input -->
                <div class='form-group'>
                    <label for='input-nombre' class='col-sm-2 control-label'> {{ __('Nombre') }} <span style="color: red" class="required">*</span></label>
                    <input type='text' name='nombre' id='input-nombre' class="form-control @error('nombre') is-invalid @enderror" placeholder='' autocomplete='on'>
                    @error('nombre') <div class='invalid-feedback'>{{ $message }}</div> @enderror
                </div>
                <!-- Correo Input -->
                <div class='form-group'>
                    <label for='input-correo' class='col-sm-2 control-label'> {{ __('Correo') }} <span style="color: red" class="required">*</span></label>
                    <input type='email' name='correo' id='input-correo' class="form-control @error('correo') is-invalid @enderror" placeholder='' autocomplete='on'>
                    @error('correo') <div class='invalid-feedback'>{{ $message }}</div> @enderror
                </div>
                <!-- Teléfono Input -->
                <div class='form-group'>
                    <label for='input-telefono' class='col-sm-2 control-label'> {{ __('Teléfono') }} </label>
                    <input type='number' name='telefono' id='input-telefono' class="form-control @error('telefono') is-invalid @enderror" placeholder='' autocomplete='on'>
                    @error('telefono') <div class='invalid-feedback'>{{ $message }}</div> @enderror
                </div>
                <!-- Cargo Input -->

                <div class='form-group'>
                    <label for='input-cargo' class='col-sm-2 control-label'> {{ __('Cargo') }}</label>
                    <input type='text' name='cargo' id='input-cargo' class="form-control @error('cargo') is-invalid @enderror" placeholder='' autocomplete='on'>
                    @error('cargo') <div class='invalid-feedback'>{{ $message }}</div> @enderror
                </div>
                
            </div>
        
            <div class="card-footer">
                <button type="button" id="btnCrearActualizarPersonal" class="btn btn-info ml-4" onclick="enviarDatos({{ $empresa->id }})">Crear</button>
                <a onclick="cerrarModalcrear()" class="btn btn-default float-left">{{ __('Cancel') }}</a>
            </div>
        </form> 
        
    </div>

 
</div>


    <tr x-data="{ modalIsOpen : false }">
        <td class="">{{ $personal->dni }}</td>
        <td class="">{{ $personal->nombre }}</td>
        <td class="">{{ $personal->correo }}</td>
        <td class="">{{ $personal->telefono }}</td>
        <td class="">{{ $personal->cargo }}</td>
        <td class="">
            <label class="switch">
                <input type="checkbox" data-id="{{ $personal->id }}" {{ $personal->estado ? 'checked' : '' }} class="switch">
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

        </style>
        @if(getCrudConfig('Personal')->delete or getCrudConfig('Personal')->update)
            <td>


                <a class="btn text-primary mt-1" onclick="editarPersonal({{ $personal->id }})">
                    <i class="icon-pencil"></i>
                </a>
                
            

                <button class="btn text-danger mt-1" onclick="confirmDelete({{ $personal->id }}, {{ auth()->user()->empresa_id }}, this)">
                    <i class="icon-trash"></i>
                </button>
                
                
                
                <!-- Modal de Confirmación -->
                <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-sm" role="document" style=" width: 30%; display: flex; align-items: center; justify-content: center; height: 100%;">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalLabel">Confirmar Eliminación</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                ¿Estás seguro de que quieres eliminar a esta persona?
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-danger" id="deleteConfirmButton">Eliminar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </td>
        @endif
    </tr>

<meta name="csrf-token" content="{{ csrf_token() }}">
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
        document.getElementById('vinculosSection').style.display = 'none';
    }

        function cerrarModalcrear() {
            document.getElementById('crearPersonalForm').style.display = 'none';
            document.getElementById('personalList').style.display = '';
            document.getElementById('vinculosSection').style.display = 'none';
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
        console.log(data);
        alert('Personal creado/actualizado correctamente');
        cerrarModalcrear();
        location.reload(); // Recargar la página para mostrar los cambios actualizados
    })
    .catch(error => {
        console.error('Error:', error);
        alert('El Dni ya esta registrado.');
    });
}




    
</script>
<script>
    function confirmDelete(personaId, empid, element) {
    $('#deleteConfirmButton').off('click').on('click', function() {
        $('#confirmDeleteModal').modal('hide');

        $.ajax({
            url: "/persona/delete/" + personaId + "/" +empid ,
            type: "GET",
            success: function(response) {
                if(response.success) {
                    // Eliminar visualmente la fila de la persona
                    $(element).closest('tr').fadeOut(400, function() { 
                        $(this).remove();
                    });
                    alert('Persona eliminada con éxito');
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error en la solicitud AJAX: ", status, error);
                alert("No se pudo eliminar la persona. Por favor, inténtelo de nuevo.");
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
            personalList.style.display = "";
            vinculosSection.style.display = "none";
            document.getElementById('crearPersonalForm').style.display = 'none';
        } else {
            personalList.style.display = "none";
            vinculosSection.style.display = "";
            document.getElementById('crearPersonalForm').style.display = 'none';
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