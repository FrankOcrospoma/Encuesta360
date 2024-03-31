<?php

namespace App\Http\Livewire\Admin\Pregunta;

use App\Models\Pregunta;
use Livewire\Component;
use Livewire\WithFileUploads;

class Update extends Component
{
    use WithFileUploads;

    public $pregunta;

    public $texto;
    public $categoria;
    public $estado;
    
    protected $rules = [
        'texto' => 'required|string|max:255',        
    ];

    public function mount(Pregunta $Pregunta){
        $this->pregunta = $Pregunta;
        $this->texto = $this->pregunta->texto;
        $this->categoria = $this->pregunta->categoria;
        $this->estado = $this->pregunta->estado;        
    }

    public function updated($input)
    {
        $this->validateOnly($input);
    }

    public function update()
    {
        if($this->getRules())
            $this->validate();

        $this->dispatchBrowserEvent('show-message', ['type' => 'success', 'message' => __('UpdatedMessage', ['name' => __('Pregunta') ]) ]);
        
        $this->pregunta->update([
            'texto' => $this->texto,
            'categoria' => $this->categoria,
            'estado' => $this->estado,
            'user_id' => auth()->id(),
        ]);
    }

    public function render()
    {
        return view('livewire.admin.pregunta.update', [
            'pregunta' => $this->pregunta
        ])->layout('admin::layouts.app', ['title' => __('UpdateTitle', ['name' => __('Pregunta') ])]);
    }
}
