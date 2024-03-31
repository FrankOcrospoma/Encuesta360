<?php

namespace App\Http\Livewire\Admin\Cargo;

use App\Models\Cargo;
use Livewire\Component;

class Single extends Component
{

    public $cargo;

    public function mount(Cargo $Cargo){
        $this->cargo = $Cargo;
    }

    public function delete()
    {
        $this->cargo->delete();
        $this->dispatchBrowserEvent('show-message', ['type' => 'error', 'message' => __('DeletedMessage', ['name' => __('Cargo') ]) ]);
        $this->emit('cargoDeleted');
    }

    public function render()
    {
        return view('livewire.admin.cargo.single')
            ->layout('admin::layouts.app');
    }
}
