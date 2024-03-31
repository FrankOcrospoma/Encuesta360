<?php

namespace App\Http\Livewire\Admin\Encuesta;

use App\Models\Encuesta;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public $nombre;
    public $empresa;
    public $fecha;
    
    protected $rules = [
        'nombre' => 'required|string',
        'empresa' => 'required|string',
        'fecha' => 'required|date',        
    ];

    public function updated($input)
    {
        $this->validateOnly($input);
    }

    public function create()
    {
        if($this->getRules())
            $this->validate();

        $this->dispatchBrowserEvent('show-message', ['type' => 'success', 'message' => __('CreatedMessage', ['name' => __('Encuesta') ])]);
        
        Encuesta::create([
            'nombre' => $this->nombre,
            'empresa' => $this->empresa,
            'fecha' => $this->fecha,
            'user_id' => auth()->id(),
        ]);

        $this->reset();
    }

    public function render()
    {
        return view('livewire.admin.encuesta.create')
            ->layout('admin::layouts.app', ['title' => __('CreateTitle', ['name' => __('Encuesta') ])]);
    }
}
