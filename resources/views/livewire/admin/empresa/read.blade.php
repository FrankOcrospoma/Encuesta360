<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header p-0">
                <h3 class="card-title">{{ __('ListTitle', ['name' => __(\Illuminate\Support\Str::plural('Empresa')) ]) }}</h3>

                <div class="px-2 mt-4">

                    <ul class="breadcrumb mt-3 py-3 px-4 rounded">
                        <li class="breadcrumb-item"><a href="@route(getRouteName().'.home')" class="text-decoration-none">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __(\Illuminate\Support\Str::plural('Empresa')) }}</li>
                    </ul>

                    <div class="row justify-content-between mt-4 mb-4">
                        @if(getCrudConfig('Empresa')->create && hasPermission(getRouteName().'.empresa.create', 1, 1))
                        <div class="col-md-4 right-0">
                            <a href="@route(getRouteName().'.empresa.create')" class="btn btn-success">{{ __('CreateTitle', ['name' => __('Empresa') ]) }}</a>
                        </div>
                        @endif
                        @if(getCrudConfig('Empresa')->searchable())
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" class="form-control" @if(config('easy_panel.lazy_mode')) wire:model.lazy="search" @else wire:model="search" @endif placeholder="{{ __('Search') }}" value="{{ request('search') }}">
                                <div class="input-group-append">
                                    <button class="btn btn-default">
                                        <a wire:target="search" wire:loading.remove><i class="fa fa-search"></i></a>
                                        <a wire:loading wire:target="search"><i class="fas fa-spinner fa-spin" ></i></a>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th scope="col" style='cursor: pointer' wire:click="sort('ruc')"> <i class='fa @if($sortType == 'desc' and $sortColumn == 'ruc') fa-sort-amount-down ml-2 @elseif($sortType == 'asc' and $sortColumn == 'ruc') fa-sort-amount-up ml-2 @endif'></i> {{ __('Ruc') }} </th>
                            <th scope="col" style='cursor: pointer' wire:click="sort('nombre')"> <i class='fa @if($sortType == 'desc' and $sortColumn == 'nombre') fa-sort-amount-down ml-2 @elseif($sortType == 'asc' and $sortColumn == 'nombre') fa-sort-amount-up ml-2 @endif'></i> {{ __('Nombre') }} </th>
                            <th scope="col" style='cursor: pointer' wire:click="sort('direccion')"> <i class='fa @if($sortType == 'desc' and $sortColumn == 'direccion') fa-sort-amount-down ml-2 @elseif($sortType == 'asc' and $sortColumn == 'direccion') fa-sort-amount-up ml-2 @endif'></i> {{ __('Direccion') }} </th>
                            <th scope="col" style='cursor: pointer' wire:click="sort('representante')"> <i class='fa @if($sortType == 'desc' and $sortColumn == 'representante') fa-sort-amount-down ml-2 @elseif($sortType == 'asc' and $sortColumn == 'representante') fa-sort-amount-up ml-2 @endif'></i> {{ __('Representante') }} </th>
                            
                            @if(getCrudConfig('Empresa')->delete or getCrudConfig('Empresa')->update)
                                <th scope="col">{{ __('Action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($empresas as $empresa)
                            @livewire('admin.empresa.single', [$empresa], key($empresa->id))
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="m-auto pt-3 pr-3">
                {{ $empresas->appends(request()->query())->links() }}
            </div>

            <div wire:loading wire:target="nextPage,gotoPage,previousPage" class="loader-page"></div>

        </div>
    </div>
</div>
