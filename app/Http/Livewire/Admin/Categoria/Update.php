<?php

namespace App\Http\Livewire\Admin\Categoria;

use App\Models\Categoria;
use Livewire\Component;
use Livewire\WithFileUploads;

class Update extends Component
{
    use WithFileUploads;

    public $categoria;

    public $nombre;
    public $descripcion;
    
    protected $rules = [
        'nombre' => 'required|string|max:255',        
    ];

    public function mount(Categoria $Categoria){
        $this->categoria = $Categoria;
        $this->nombre = $this->categoria->nombre;
        $this->descripcion = $this->categoria->descripcion;        
    }

    public function updated($input)
    {
        $this->validateOnly($input);
    }

    public function update()
    {
        if($this->getRules())
            $this->validate();

        $this->dispatchBrowserEvent('show-message', ['type' => 'success', 'message' => __('UpdatedMessage', ['name' => __('Categoria') ]) ]);
        
        $this->categoria->update([
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'user_id' => auth()->id(),
        ]);
    }

    public function render()
    {
        return view('livewire.admin.categoria.update', [
            'categoria' => $this->categoria
        ])->layout('admin::layouts.app', ['title' => __('UpdateTitle', ['name' => __('Categoria') ])]);
    }
}
