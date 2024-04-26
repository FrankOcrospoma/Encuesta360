<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header p-0">
                <h3 class="card-title">{{ __('ListTitle', ['name' => __(\Illuminate\Support\Str::plural('Pregunta')) ]) }}</h3>

                <div class="px-2 mt-4">

                    <ul class="breadcrumb mt-3 py-3 px-4 rounded">
                        <li class="breadcrumb-item"><a href="@route(getRouteName().'')" class="text-decoration-none">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __(\Illuminate\Support\Str::plural('Pregunta')) }}</li>
                    </ul>

                    <div class="row justify-content-between mt-4 mb-4">
                        @if(getCrudConfig('Pregunta')->create && hasPermission(getRouteName().'.pregunta.create', 1, 1))
                        <div class="col-md-4 right-0">
                            <a href="@route(getRouteName().'.pregunta.create')" class="btn btn-success">{{ __('CreateTitle', ['name' => __('Pregunta') ]) }}</a>
                        </div>
                        @endif
                        @if(getCrudConfig('Pregunta')->searchable())
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
                            <th scope="col" style='cursor: pointer' wire:click="sort('texto')"> <i class='fa @if($sortType == 'desc' and $sortColumn == 'texto') fa-sort-amount-down ml-2 @elseif($sortType == 'asc' and $sortColumn == 'texto') fa-sort-amount-up ml-2 @endif'></i> {{ __('Texto') }} </th>
                            <th scope="col" style='cursor: pointer' wire:click="sort('Categoria')"> <i class='fa @if($sortType == 'desc' and $sortColumn == 'Categoria') fa-sort-amount-down ml-2 @elseif($sortType == 'asc' and $sortColumn == 'Categoria') fa-sort-amount-up ml-2 @endif'></i> {{ __('Categoria') }} </th>
                            <th scope="col" style='cursor: pointer' wire:click="sort('estado')"> <i class='fa @if($sortType == 'desc' and $sortColumn == 'estado') fa-sort-amount-down ml-2 @elseif($sortType == 'asc' and $sortColumn == 'estado') fa-sort-amount-up ml-2 @endif'></i> {{ __('Estado') }} </th>
                            
                            @if(getCrudConfig('Pregunta')->delete or getCrudConfig('Pregunta')->update)
                                <th scope="col">{{ __('Action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($preguntas as $pregunta)
                            @livewire('admin.pregunta.single', [$pregunta], key($pregunta->id))
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="m-auto pt-3 pr-3">
                {{ $preguntas->appends(request()->query())->links() }}
            </div>

            <div wire:loading wire:target="nextPage,gotoPage,previousPage" class="loader-page"></div>

        </div>
    </div>
</div>
