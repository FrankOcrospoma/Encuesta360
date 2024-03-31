<?php

namespace App\Http\Livewire\Admin\Empresa;

use App\Models\Empresa;
use Livewire\Component;
use Livewire\WithFileUploads;

class Update extends Component
{
    use WithFileUploads;

    public $empresa;

    public $ruc;
    public $nombre;
    public $direccion;
    public $representante;
    
    protected $rules = [
        'ruc' => 'required|string|max:11',
        'nombre' => 'max:100',
        'direccion' => 'max:200',
        'representante' => 'max:200',        
    ];

    public function mount(Empresa $Empresa){
        $this->empresa = $Empresa;
        $this->ruc = $this->empresa->ruc;
        $this->nombre = $this->empresa->nombre;
        $this->direccion = $this->empresa->direccion;
        $this->representante = $this->empresa->representante;        
    }

    public function updated($input)
    {
        $this->validateOnly($input);
    }

    public function update()
    {
        if($this->getRules())
            $this->validate();

        $this->dispatchBrowserEvent('show-message', ['type' => 'success', 'message' => __('UpdatedMessage', ['name' => __('Empresa') ]) ]);
        
        $this->empresa->update([
            'ruc' => $this->ruc,
            'nombre' => $this->nombre,
            'direccion' => $this->direccion,
            'representante' => $this->representante,
            'user_id' => auth()->id(),
        ]);
    }

    public function render()
    {
        return view('livewire.admin.empresa.update', [
            'empresa' => $this->empresa
        ])->layout('admin::layouts.app', ['title' => __('UpdateTitle', ['name' => __('Empresa') ])]);
    }
}
