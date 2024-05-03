<?php

namespace App\Http\Livewire\Admin\Personal;

use App\Models\Personal;
use Livewire\Component;
use Livewire\WithFileUploads;

class Update extends Component
{
    use WithFileUploads;

    public $personal, $dni, $nombre, $correo, $telefono, $cargo, $estado;
    
    protected $rules = [
        'dni' => 'required|string|max:20',
        'nombre' => 'required|string|max:255',
        'correo' => 'required|email|max:255',
        'telefono' => 'required|max:15',
        'cargo' => 'required',        
    ];

    public function mount(Personal $personal){
        $this->personal = $personal;
        $this->dni = $personal->dni;
        $this->nombre = $personal->nombre;
        $this->correo = $personal->correo;
        $this->telefono = $personal->telefono;
        $this->cargo = $personal->cargo;
        $this->estado = $personal->estado;        
    }

    public function update()
    {
        $this->validate();  // Make sure to validate user input

        $this->personal->update([
            'dni' => $this->dni,
            'nombre' => $this->nombre,
            'correo' => $this->correo,
            'telefono' => $this->telefono,
            'cargo' => $this->cargo,
            'estado' => $this->estado,
            'user_id' => auth()->id(),  // Assuming you want to track which user made the change
        ]);

        $this->dispatchBrowserEvent('show-message', [
            'type' => 'success',
            'message' => __('UpdatedMessage', ['name' => __('Personal') ])
        ]);
    }

    public function render()
    {
        return view('livewire.admin.personal.update', [
            'personal' => $this->personal
        ])->layout('admin::layouts.app', ['title' => __('UpdateTitle', ['name' => __('Personal') ])]);
    }
}
