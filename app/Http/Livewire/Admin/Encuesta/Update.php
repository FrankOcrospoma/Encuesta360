<?php

namespace App\Http\Livewire\Admin\Encuesta;

use App\Models\Encuesta;
use Livewire\Component;
use Livewire\WithFileUploads;

class Update extends Component
{
    use WithFileUploads;

    public $encuesta;

    public $nombre;
    public $empresa;
    public $fecha;
    
    protected $rules = [
        'nombre' => 'required|string',
        'empresa' => 'required|string',
        'fecha' => 'required|date',        
    ];

    public function mount(Encuesta $Encuesta){
        $this->encuesta = $Encuesta;
        $this->nombre = $this->encuesta->nombre;
        $this->empresa = $this->encuesta->empresa;
        $this->fecha = $this->encuesta->fecha;        
    }

    public function updated($input)
    {
        $this->validateOnly($input);
    }

    public function update()
    {
        if($this->getRules())
            $this->validate();

        $this->dispatchBrowserEvent('show-message', ['type' => 'success', 'message' => __('UpdatedMessage', ['name' => __('Encuesta') ]) ]);
        
        $this->encuesta->update([
            'nombre' => $this->nombre,
            'empresa' => $this->empresa,
            'fecha' => $this->fecha,
            'user_id' => auth()->id(),
        ]);
    }

    public function render()
    {
        return view('livewire.admin.encuesta.update', [
            'encuesta' => $this->encuesta
        ])->layout('admin::layouts.app', ['title' => __('UpdateTitle', ['name' => __('Encuesta') ])]);
    }
}
