<?php

namespace App\Http\Livewire\Admin\Empresa;

use App\Models\Empresa;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public $ruc;
    public $nombre;
    public $direccion;
    public $representante;
    public $estado=true;
    
    protected $rules = [
        'ruc' => 'required|string|max:11',
        'nombre' => 'max:100',
        'direccion' => 'max:200',
        'representante' => 'max:200',        
    ];

    public function updated($input)
    {
        $this->validateOnly($input);
    }

    public function create()
    {
        if($this->getRules())
            $this->validate();

        $this->dispatchBrowserEvent('show-message', ['type' => 'success', 'message' => __('CreatedMessage', ['name' => __('Empresa') ])]);
        
        Empresa::create([
            'ruc' => $this->ruc,
            'nombre' => $this->nombre,
            'direccion' => $this->direccion,
            'representante' => $this->representante,
            'estado' => $this->estado,
            'user_id' => auth()->id(),
        ]);

        $this->reset();
    }

    public function render()
    {
        return view('livewire.admin.empresa.create')
            ->layout('admin::layouts.app', ['title' => __('CreateTitle', ['name' => __('Empresa') ])]);
    }
}
