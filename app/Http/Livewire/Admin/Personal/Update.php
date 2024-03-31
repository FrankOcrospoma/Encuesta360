<?php

namespace App\Http\Livewire\Admin\Personal;

use App\Models\Personal;
use Livewire\Component;
use Livewire\WithFileUploads;

class Update extends Component
{
    use WithFileUploads;

    public $personal;

    public $dni;
    public $nombre;
    public $correo;
    public $cargo;
    public $empresa;
    
    protected $rules = [
        'dni' => 'required|string|max:20',
        'nombre' => 'required|string|max:255',
        'correo' => 'required|email|max:255',
        'cargo' => 'required|integer|exists:cargos,id',
        'empresa' => 'required|integer|exists:empresas,id',        
    ];

    public function mount(Personal $Personal){
        $this->personal = $Personal;
        $this->dni = $this->personal->dni;
        $this->nombre = $this->personal->nombre;
        $this->correo = $this->personal->correo;
        $this->cargo = $this->personal->cargo;
        $this->empresa = $this->personal->empresa;        
    }

    public function updated($input)
    {
        $this->validateOnly($input);
    }

    public function update()
    {
        if($this->getRules())
            $this->validate();

        $this->dispatchBrowserEvent('show-message', ['type' => 'success', 'message' => __('UpdatedMessage', ['name' => __('Personal') ]) ]);
        
        $this->personal->update([
            'dni' => $this->dni,
            'nombre' => $this->nombre,
            'correo' => $this->correo,
            'cargo' => $this->cargo,
            'empresa' => $this->empresa,
            'user_id' => auth()->id(),
        ]);
    }

    public function render()
    {
        return view('livewire.admin.personal.update', [
            'personal' => $this->personal
        ])->layout('admin::layouts.app', ['title' => __('UpdateTitle', ['name' => __('Personal') ])]);
    }
}
