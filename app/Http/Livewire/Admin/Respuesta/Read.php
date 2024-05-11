<?php

namespace App\Http\Livewire\Admin\Respuesta;

use App\Models\Respuesta;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class Read extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;

    protected $queryString = ['search'];

    protected $listeners = ['respuestaDeleted'];

    public $sortType;
    public $sortColumn;

    public function respuestaDeleted()
    {
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
        // Inicia la consulta filtrando por estado true
        $data = Respuesta::where('estado', true)->where('vigencia', true);

        $instance = getCrudConfig('respuesta');
        if($instance->searchable()){
            $array = (array) $instance->searchable();
            $data->where(function (Builder $query) use ($array){
                foreach ($array as $item) {
                    if(!\Str::contains($item, '.')) {
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

        if($this->sortColumn) {
            $data->orderBy($this->sortColumn, $this->sortType);
        } else {
            // Ordena por defecto por la columna 'id' de forma descendente si no hay una columna de ordenación especificada
            $data->latest('id');
        }

        // Pagina el resultado final
        $data = $data->paginate(config('easy_panel.pagination_count', 15));

        return view('livewire.admin.respuesta.read', [
            'respuestas' => $data
        ])->layout('admin::layouts.app', ['title' => __(\Str::plural('Respuesta')) ]);
    }
}
