<?php

namespace App\Http\Livewire\Admin\Vinculo;

use App\Models\Vinculo;
use Livewire\Component;

class Single extends Component
{

    public $vinculo;

    public function mount(Vinculo $Vinculo){
        $this->vinculo = $Vinculo;
    }

    public function delete()
    {
        $this->vinculo->vigencia = false;
        $this->vinculo->save();
        $this->dispatchBrowserEvent('show-message', ['type' => 'error', 'message' => __('DeletedMessage', ['name' => __('Vinculo') ]) ]);
        $this->emit('vinculoDeleted');
    }

    public function render()
    {
        return view('livewire.admin.vinculo.single')
            ->layout('admin::layouts.app');
    }
}
