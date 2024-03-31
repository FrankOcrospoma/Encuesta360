<?php

namespace App\Http\Livewire\Admin\Respuesta;

use App\Models\Respuesta;
use Livewire\Component;
use Livewire\WithFileUploads;

class Update extends Component
{
    use WithFileUploads;

    public $respuesta;

    public $texto;
    public $score;
    public $estado;
    
    protected $rules = [
        'texto' => 'required|string|max:255',
        'score' => 'required|numeric',        
    ];

    public function mount(Respuesta $Respuesta){
        $this->respuesta = $Respuesta;
        $this->texto = $this->respuesta->texto;
        $this->score = $this->respuesta->score;
        $this->estado = $this->respuesta->estado;        
    }

    public function updated($input)
    {
        $this->validateOnly($input);
    }

    public function update()
    {
        if($this->getRules())
            $this->validate();

        $this->dispatchBrowserEvent('show-message', ['type' => 'success', 'message' => __('UpdatedMessage', ['name' => __('Respuesta') ]) ]);
        
        $this->respuesta->update([
            'texto' => $this->texto,
            'score' => $this->score,
            'estado' => $this->estado,
            'user_id' => auth()->id(),
        ]);
    }

    public function render()
    {
        return view('livewire.admin.respuesta.update', [
            'respuesta' => $this->respuesta
        ])->layout('admin::layouts.app', ['title' => __('UpdateTitle', ['name' => __('Respuesta') ])]);
    }
}
