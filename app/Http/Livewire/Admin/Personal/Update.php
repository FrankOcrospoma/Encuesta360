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
    public $telefono;
    public $cargo;
    public $empresa;
    public $estado;
    
    protected $rules = [
        'dni' => 'required|string|max:20',
        'nombre' => 'required|string|max:255',
        'correo' => 'required|email|max:255',
        'telefono' => 'required|max:15',
        'cargo' => 'required|integer|exists:cargos,id',
        'empresa' => 'required|integer|exists:empresas,id',        
    ];

    public function mount(Personal $Personal){
        $this->personal = $Personal;
        $this->dni = $this->personal->dni;
        $this->nombre = $this->personal->nombre;
        $this->correo = $this->personal->correo;
        $this->telefono = $this->personal->telefono;
        $this->cargo = $this->personal->cargo;
        $this->empresa = $this->personal->empresa;
        $this->estado = $this->personal->estado;        
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
            'telefono' => $this->telefono,
            'cargo' => $this->cargo,
            'empresa' => $this->empresa,
            'estado' => $this->estado,
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
