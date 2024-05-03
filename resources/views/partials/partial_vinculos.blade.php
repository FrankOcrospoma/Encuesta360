<div class="card-body">
    <input type="hidden" name="empresa_id" value="{{ $empresaId }}">
    <div class="form-group">
        <div class="row">
            <div class="col-md-5">
                <label for="input-evaluadores">Seleccionar persona:</label>
                @php
                    // Obtener IDs de personas ya vinculadas
                    $vinculadosIds = $vinculados->where('evaluado_id', $perid)->pluck('evaluador_id')->toArray();
                @endphp
               <select class="form-control" id="input-evaluadores-{{ $perid }}">


                    @foreach ($personals as $otraPersona)
                        @if (!in_array($otraPersona->id, $vinculadosIds))
                            <option value="{{ $otraPersona->id }}">{{ $otraPersona->nombre }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label>Seleccionar el vinculo:</label>
                <select class="form-control" id="tipoVinculo-{{ $perid }}">
                    @foreach ($vinculos as $vinculo)
                        <option value="{{ $vinculo->id }}" >{{ $vinculo->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 align-self-end">
                <label></label><br>
                <button class="btn btn-outline-secondary ml-2" type="button" onclick="agregarVinculo({{ $perid }})">Añadir</button>
            </div>
        </div>
    </div>
        
        
    <div id="lista-evaluadores">
        <ul class="list-group" id="lista-evaluadores-ul-{{ $perid }}">
            <li class="list-group-item list-group-item-info d-flex justify-content-between align-items-center" >
                <span class="col-1">#</span>
                <span class="col-3">Evaluador</span>
                <span class="col-3">Vínculo</span>

                <span>Acciones</span>
            </li>
    @foreach ($vinculados as $index => $vinculado)
        @if ($vinculado->evaluado_id == $perid)
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
    <button id="btnGuardarVinculos" class="btn btn-success mt-3" onclick="guardarVinculos({{ $perid }})">Guardar Vínculos</button>

</div>