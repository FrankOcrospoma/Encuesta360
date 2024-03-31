<?php

namespace App\Http\Livewire\Admin\Envio;

use App\Models\Envio;
use Livewire\Component;

class Single extends Component
{

    public $envio;

    public function mount(Envio $Envio){
        $this->envio = $Envio;
    }

    public function delete()
    {
        $this->envio->delete();
        $this->dispatchBrowserEvent('show-message', ['type' => 'error', 'message' => __('DeletedMessage', ['name' => __('Envio') ]) ]);
        $this->emit('envioDeleted');
    }

    public function render()
    {
        return view('livewire.admin.envio.single')
            ->layout('admin::layouts.app');
    }
}
