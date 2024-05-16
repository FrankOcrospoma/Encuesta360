<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header p-0">
                <h3 class="card-title">{{ __('ListTitle', ['name' => __(\Illuminate\Support\Str::plural('Personal')) ]) }}</h3>

                <div class="px-2 mt-4">

                    <ul class="breadcrumb mt-3 py-3 px-4 rounded">
                        <li class="breadcrumb-item"><a href="@route(getRouteName().'')" class="text-decoration-none">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __(\Illuminate\Support\Str::plural('Personal')) }}</li>
                    </ul>

                   
                        <!-- First Row for Crear and Importar -->
                        <div class="row justify-content-between mt-4 mb-4">
                            <div class="col-md-6 d-flex justify-content-start align-items-center">
                                @if(getCrudConfig('Personal')->create && hasPermission(getRouteName().'.personal.create', 1, 1))
                                    <button id="btnCrearPersonal" onclick="abrirModalcrear()" class="btn btn-success">Crear {{ __('Personal') }}</button>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <form action="{{ route('importar.personas') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="empresa_id"  value="{{ auth()->user()->empresa_id }}">
                            
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="file" class="custom-file-input" id="inputGroupFile" required>
                                            <label class="custom-file-label" for="inputGroupFile">Elegir archivo</label>
                                        </div>
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary"><i class="bi bi-file-earmark-spreadsheet"></i> Importar Personas</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    
                        <!-- Second Row for Vinculos and Search -->
                        <div class="row justify-content-between mt-4 mb-4">
                            <div class="col-md-6 d-flex justify-content-start align-items-center" style="margin-top:-20px ">
                                @if(getCrudConfig('Personal')->create && hasPermission(getRouteName().'.personal.create', 1, 1))
                                    <button id="btnVinculos" onclick="mostrarVinculos()" class="btn btn-primary mt-3">Vínculos</button>
                                @endif
                            </div>
                            <div class="col-md-6">
                                @if(getCrudConfig('Personal')->searchable())
                                    <div class="input-group">
                                        <input type="text" class="form-control" @if(config('easy_panel.lazy_mode')) wire:model.lazy="search" @else wire:model="search" @endif placeholder="{{ __('Search') }}" value="{{ request('search') }}">
                                        <div class="input-group-append">
                                            <button class="btn btn-default">
                                                <a wire:target="search" wire:loading.remove><i class="fa fa-search"></i></a>
                                                <a wire:loading wire:target="search"><i class="fas fa-spinner fa-spin"></i></a>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    
                        @if(session('error'))
                        <!-- Modal de Error -->
                        <div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="errorModalLabel">Error de Importación</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        {{ session('error') }}
                                        <form id="addVinculoForm" action="{{ route('agregar.vinculo') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="nombre" id="vinculoNombre" value="{{ session('vinculo_nombre') }}">
                                            <input type="hidden" name="empresa_id" value="{{ session('empresa_id') }}">
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                        <button type="button" class="btn btn-primary" id="addVinculoButton">Agregar Vínculo y Continuar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @if(session()->has('success'))
                            <div class="alert alert-success">
                                {{ session()->get('success') }}
                            </div>
                            {{ session()->forget('success') }}
                        @endif
                        
                 
                    
                </div>
            </div>

            <div  class="card-body table-responsive p-0">
                <table id="personalList" class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th scope="col" style='cursor: pointer' wire:click="sort('dni')"> <i class='fa @if($sortType == 'desc' and $sortColumn == 'dni') fa-sort-amount-down ml-2 @elseif($sortType == 'asc' and $sortColumn == 'dni') fa-sort-amount-up ml-2 @endif'></i> {{ __('Dni') }} </th>
                            <th scope="col" style='cursor: pointer' wire:click="sort('nombre')"> <i class='fa @if($sortType == 'desc' and $sortColumn == 'nombre') fa-sort-amount-down ml-2 @elseif($sortType == 'asc' and $sortColumn == 'nombre') fa-sort-amount-up ml-2 @endif'></i> {{ __('Nombre') }} </th>
                            <th scope="col" style='cursor: pointer' wire:click="sort('correo')"> <i class='fa @if($sortType == 'desc' and $sortColumn == 'correo') fa-sort-amount-down ml-2 @elseif($sortType == 'asc' and $sortColumn == 'correo') fa-sort-amount-up ml-2 @endif'></i> {{ __('Correo') }} </th>
                            <th scope="col" style='cursor: pointer' wire:click="sort('telefono')"> <i class='fa @if($sortType == 'desc' and $sortColumn == 'telefono') fa-sort-amount-down ml-2 @elseif($sortType == 'asc' and $sortColumn == 'telefono') fa-sort-amount-up ml-2 @endif'></i> {{ __('Telefono') }} </th>
                            <th scope="col" style='cursor: pointer' wire:click="sort('cargo')"> <i class='fa @if($sortType == 'desc' and $sortColumn == 'cargo') fa-sort-amount-down ml-2 @elseif($sortType == 'asc' and $sortColumn == 'cargo') fa-sort-amount-up ml-2 @endif'></i> {{ __('Cargo') }} </th>
                            <th scope="col" style='cursor: pointer' wire:click="sort('estado')"> <i class='fa @if($sortType == 'desc' and $sortColumn == 'estado') fa-sort-amount-down ml-2 @elseif($sortType == 'asc' and $sortColumn == 'estado') fa-sort-amount-up ml-2 @endif'></i> {{ __('Estado') }} </th>
                            
                            @if(getCrudConfig('Personal')->delete or getCrudConfig('Personal')->update)
                                <th scope="col">{{ __('Action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($personals as $personal)
                            @livewire('admin.personal.single', [$personal], key($personal->id))
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="m-auto pt-3 pr-3">
                {{ $personals->appends(request()->query())->links() }}
            </div>

            <div wire:loading wire:target="nextPage,gotoPage,previousPage" class="loader-page"></div>

        </div>
    </div>
</div>
<!-- Incluir los scripts de jQuery y Bootstrap -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        @if(session('error'))
            $('#errorModal').modal('show');
        @endif

        $('#addVinculoButton').click(function() {
            $('#addVinculoForm').submit();
        });
    });
</script>