<?php

namespace App\Http\Livewire\Admin\Personal;

use App\Models\Empresa;
use App\Models\Personal;
use Livewire\Component;

class Single extends Component
{

    public $personal;

    public function mount(Personal $Personal){
        $this->personal = $Personal;
    }

    public function delete()
    {
        $this->personal->delete();
        $this->dispatchBrowserEvent('show-message', ['type' => 'error', 'message' => __('DeletedMessage', ['name' => __('Personal') ]) ]);
        $this->emit('personalDeleted');
    }

    public function render()
    {
        $empresa = Empresa::findOrFail(auth()->user()->empresa_id); 

        return view('livewire.admin.personal.single',
            [ 'empresa' => $empresa ]        
        )
            ->layout('admin::layouts.app');
    }
}
