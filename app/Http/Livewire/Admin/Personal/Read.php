<?php

namespace App\Http\Livewire\Admin\Personal;

use App\Models\Detalle_empresa;
use App\Models\Empresa;
use App\Models\Personal;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class Read extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;

    protected $queryString = ['search'];

    protected $listeners = ['personalDeleted'];

    public $sortType;
    public $sortColumn;

    public function personalDeleted(){
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
        $detalle = Detalle_empresa::where('empresa_id', auth()->user()->empresa_id)->get(); 
        $personalIds = $detalle->pluck('personal_id'); 
        $query = Personal::whereIn('id', $personalIds);
    
        $instance = getCrudConfig('personal');
        if ($instance->searchable()) {
            $searchableFields = (array) $instance->searchable();
            $query->where(function (Builder $query) use ($searchableFields) {
                foreach ($searchableFields as $field) {
                    if (!\Str::contains($field, '.')) {
                        $query->orWhere($field, 'like', '%' . $this->search . '%');
                    } else {
                        $fieldParts = explode('.', $field);
                        $query->orWhereHas($fieldParts[0], function (Builder $subQuery) use ($fieldParts) {
                            $subQuery->where($fieldParts[1], 'like', '%' . $this->search . '%');
                        });
                    }
                }
            });
        }
    
        if ($this->sortColumn) {
            $query->orderBy($this->sortColumn, $this->sortType);
        } else {
            $query->latest('id');
        }
    
        $data = $query->paginate(config('easy_panel.pagination_count', 15));
    
        return view('livewire.admin.personal.read', [
            'personals' => $data
        ])->layout('admin::layouts.app', ['title' => __(\Str::plural('Personal'))]);
    }
    
    
}
