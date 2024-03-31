<?php

namespace App\Http\Livewire\Admin\Encuesta;

use App\Models\Encuesta;
use Livewire\Component;

class Single extends Component
{

    public $encuesta;

    public function mount(Encuesta $Encuesta){
        $this->encuesta = $Encuesta;
    }

    public function delete()
    {
        $this->encuesta->delete();
        $this->dispatchBrowserEvent('show-message', ['type' => 'error', 'message' => __('DeletedMessage', ['name' => __('Encuesta') ]) ]);
        $this->emit('encuestaDeleted');
    }

    public function render()
    {
        return view('livewire.admin.encuesta.single')
            ->layout('admin::layouts.app');
    }
}
