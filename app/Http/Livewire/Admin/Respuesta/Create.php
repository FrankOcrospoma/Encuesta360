<?php

namespace App\Http\Livewire\Admin\Respuesta;

use App\Models\Respuesta;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public $texto;
    public $score;
    public $estado;
    
    protected $rules = [
        'texto' => 'required|string|max:255',
        'score' => 'required|numeric',        
    ];

    public function updated($input)
    {
        $this->validateOnly($input);
    }

    public function create()
    {
        if($this->getRules())
            $this->validate();

        $this->dispatchBrowserEvent('show-message', ['type' => 'success', 'message' => __('CreatedMessage', ['name' => __('Respuesta') ])]);
        
        Respuesta::create([
            'texto' => $this->texto,
            'score' => $this->score,
            'estado' => $this->estado,
            'user_id' => auth()->id(),
        ]);

        $this->reset();
    }

    public function render()
    {
        return view('livewire.admin.respuesta.create')
            ->layout('admin::layouts.app', ['title' => __('CreateTitle', ['name' => __('Respuesta') ])]);
    }
}
