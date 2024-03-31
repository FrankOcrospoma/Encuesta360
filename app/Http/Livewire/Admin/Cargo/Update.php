<?php

namespace App\Http\Livewire\Admin\Cargo;

use App\Models\Cargo;
use Livewire\Component;
use Livewire\WithFileUploads;

class Update extends Component
{
    use WithFileUploads;

    public $cargo;

    public $nombre;
    
    protected $rules = [
        'nombre' => 'required|string|max:255',        
    ];

    public function mount(Cargo $Cargo){
        $this->cargo = $Cargo;
        $this->nombre = $this->cargo->nombre;        
    }

    public function updated($input)
    {
        $this->validateOnly($input);
    }

    public function update()
    {
        if($this->getRules())
            $this->validate();

        $this->dispatchBrowserEvent('show-message', ['type' => 'success', 'message' => __('UpdatedMessage', ['name' => __('Cargo') ]) ]);
        
        $this->cargo->update([
            'nombre' => $this->nombre,
            'user_id' => auth()->id(),
        ]);
    }

    public function render()
    {
        return view('livewire.admin.cargo.update', [
            'cargo' => $this->cargo
        ])->layout('admin::layouts.app', ['title' => __('UpdateTitle', ['name' => __('Cargo') ])]);
    }
}
