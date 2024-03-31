<?php

namespace App\Http\Livewire\Admin\Respuesta;

use App\Models\Respuesta;
use Livewire\Component;

class Single extends Component
{

    public $respuesta;

    public function mount(Respuesta $Respuesta){
        $this->respuesta = $Respuesta;
    }

    public function delete()
    {
        $this->respuesta->delete();
        $this->dispatchBrowserEvent('show-message', ['type' => 'error', 'message' => __('DeletedMessage', ['name' => __('Respuesta') ]) ]);
        $this->emit('respuestaDeleted');
    }

    public function render()
    {
        return view('livewire.admin.respuesta.single')
            ->layout('admin::layouts.app');
    }
}
