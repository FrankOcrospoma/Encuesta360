<?php

namespace App\Http\Livewire\Admin\Formulario;

use App\Models\Formulario;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public $nombre;
    
    protected $rules = [
        
    ];

    public function updated($input)
    {
        $this->validateOnly($input);
    }

    public function create()
    {
        if($this->getRules())
            $this->validate();

        $this->dispatchBrowserEvent('show-message', ['type' => 'success', 'message' => __('CreatedMessage', ['name' => __('Formulario') ])]);
        
        Formulario::create([
            'nombre' => $this->nombre,
            'user_id' => auth()->id(),
        ]);

        $this->reset();
    }

    public function render()
    {
        return view('livewire.admin.formulario.create')
            ->layout('admin::layouts.app', ['title' => __('CreateTitle', ['name' => __('Formulario') ])]);
    }
}
