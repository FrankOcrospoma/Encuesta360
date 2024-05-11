<?php

namespace App\Http\Livewire\Admin\Formulario;

use App\Models\Formulario;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class Read extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;

    protected $queryString = ['search'];

    protected $listeners = ['formularioDeleted'];

    public $sortType;
    public $sortColumn;

    public function formularioDeleted(){
        // Nothing ..
    }

    public function sort($column)
    {
        $sort = $this->sortType == 'desc' ? 'asc' : 'desc';

        $this->sortColumn = $column;
        $this->sortType = $sort;
    }

    public function render()
    {
        $data = Formulario::where('estado', true);  // Filtra solo los formularios activos
    
        $instance = getCrudConfig('formulario');
        if ($instance->searchable()) {
            $array = (array) $instance->searchable();
            $data->where(function (Builder $query) use ($array) {
                foreach ($array as $item) {
                    if (!\Str::contains($item, '.')) {
                        $query->orWhere($item, 'like', '%' . $this->search . '%');
                    } else {
                        $array = explode('.', $item);
                        $query->orWhereHas($array[0], function (Builder $query) use ($array) {
                            $query->where($array[1], 'like', '%' . $this->search . '%');
                        });
                    }
                }
            });
        }
    
        if ($this->sortColumn) {
            $data->orderBy($this->sortColumn, $this->sortType);
        } else {
            $data->latest('id');
        }
    
        // Obtenemos los formularios sin duplicados basados en el ID
        $data = $data->get()->unique('id');
    
        return view('livewire.admin.formulario.read', [
            'formularios' => $data
        ])->layout('admin::layouts.app', ['title' => __(\Str::plural('Formulario'))]);
    }
    
    
}
