<?php

namespace App\Http\Livewire\Admin\Formulario;

use App\Models\Formulario;
use Livewire\Component;

class Single extends Component
{

    public $formulario;

    public function mount(Formulario $Formulario){
        $this->formulario = $Formulario;
    }

    public function delete()
    {
        $this->formulario->estado = false;
        $this->formulario->save();
        $this->dispatchBrowserEvent('show-message', ['type' => 'error', 'message' => __('DeletedMessage', ['name' => __('Formulario') ]) ]);
        $this->emit('formularioDeleted');
    }

    public function render()
    {
        return view('livewire.admin.formulario.single')
            ->layout('admin::layouts.app');
    }
}
