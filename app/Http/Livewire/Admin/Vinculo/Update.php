<?php

namespace App\Http\Livewire\Admin\Vinculo;

use App\Models\Vinculo;
use Livewire\Component;
use Livewire\WithFileUploads;

class Update extends Component
{
    use WithFileUploads;

    public $vinculo;

    public $nombre;
    
    protected $rules = [
        'nombre' => 'required|string|max:255',        
    ];

    public function mount(Vinculo $Vinculo){
        $this->vinculo = $Vinculo;
        $this->nombre = $this->vinculo->nombre;        
    }

    public function updated($input)
    {
        $this->validateOnly($input);
    }

    public function update()
    {
        if($this->getRules())
            $this->validate();

        $this->dispatchBrowserEvent('show-message', ['type' => 'success', 'message' => __('UpdatedMessage', ['name' => __('Vinculo') ]) ]);
        
        $this->vinculo->update([
            'nombre' => $this->nombre,
            'user_id' => auth()->id(),
        ]);
    }

    public function render()
    {
        return view('livewire.admin.vinculo.update', [
            'vinculo' => $this->vinculo
        ])->layout('admin::layouts.app', ['title' => __('UpdateTitle', ['name' => __('Vinculo') ])]);
    }
}
