<?php

namespace App\Http\Livewire\Admin\Empresa;

use App\Models\Empresa;
use Livewire\Component;

class Single extends Component
{

    public $empresa;

    public function mount(Empresa $Empresa){
        $this->empresa = $Empresa;
    }

    public function delete()
    {
        $this->empresa->delete();
        $this->dispatchBrowserEvent('show-message', ['type' => 'error', 'message' => __('DeletedMessage', ['name' => __('Empresa') ]) ]);
        $this->emit('empresaDeleted');
    }

    public function render()
    {
        return view('livewire.admin.empresa.single')
            ->layout('admin::layouts.app');
    }
}
