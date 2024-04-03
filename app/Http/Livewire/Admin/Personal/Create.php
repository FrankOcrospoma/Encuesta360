<?php

namespace App\Http\Livewire\Admin\Personal;

use App\Models\Personal;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public $dni;
    public $nombre;
    public $correo;
    public $telefono;
    public $cargo;
    public $empresa;
    public $estado = false;
    
    protected $rules = [
        'dni' => 'required|string|max:20',
        'nombre' => 'required|string|max:255',
        'correo' => 'required|email|max:255',
        'telefono' => 'required|max:15',
        'cargo' => 'required|integer|exists:cargos,id',
        'empresa' => 'required|integer|exists:empresas,id',        
    ];

    public function updated($input)
    {
        $this->validateOnly($input);
    }

    public function create()
    {
        if($this->getRules())
            $this->validate();

        $this->dispatchBrowserEvent('show-message', ['type' => 'success', 'message' => __('CreatedMessage', ['name' => __('Personal') ])]);
        
        Personal::create([
            'dni' => $this->dni,
            'nombre' => $this->nombre,
            'correo' => $this->correo,
            'telefono' => $this->telefono,
            'cargo' => $this->cargo,
            'empresa' => $this->empresa,
            'estado' => $this->estado,
            'user_id' => auth()->id(),
        ]);

        $this->reset();
    }

    public function render()
    {
        return view('livewire.admin.personal.create')
            ->layout('admin::layouts.app', ['title' => __('CreateTitle', ['name' => __('Personal') ])]);
    }
}
