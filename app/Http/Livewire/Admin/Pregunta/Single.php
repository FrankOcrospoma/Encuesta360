<?php

namespace App\Http\Livewire\Admin\Pregunta;

use App\Models\Pregunta;
use Livewire\Component;

class Single extends Component
{

    public $pregunta;

    public function mount(Pregunta $Pregunta){
        $this->pregunta = $Pregunta;
    }

    public function delete()
    {
        $this->pregunta->vigencia = false;
        $this->pregunta->save();
        $this->dispatchBrowserEvent('show-message', ['type' => 'error', 'message' => __('DeletedMessage', ['name' => __('Pregunta') ]) ]);
        $this->emit('preguntaDeleted');
    }

    public function render()
    {
        return view('livewire.admin.pregunta.single')
            ->layout('admin::layouts.app');
    }
}
