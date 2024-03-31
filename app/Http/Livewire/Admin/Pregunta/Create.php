<?php

namespace App\Http\Livewire\Admin\Pregunta;

use App\Models\Pregunta;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public $texto;
    public $categoria;
    public $estado = false;
    
    protected $rules = [
        'texto' => 'required|string|max:255',        
    ];

    public function updated($input)
    {
        $this->validateOnly($input);
    }

    public function create()
    {
        if($this->getRules())
            $this->validate();

        $this->dispatchBrowserEvent('show-message', ['type' => 'success', 'message' => __('CreatedMessage', ['name' => __('Pregunta') ])]);
        
        Pregunta::create([
            'texto' => $this->texto,
            'categoria' => $this->categoria,
            'estado' => $this->estado,
            'user_id' => auth()->id(),
        ]);

        $this->reset();
    }

    public function render()
    {
        return view('livewire.admin.pregunta.create')
            ->layout('admin::layouts.app', ['title' => __('CreateTitle', ['name' => __('Pregunta') ])]);
    }
}
