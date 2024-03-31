<?php

namespace App\Http\Livewire\Admin\Personal;

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
        return view('livewire.admin.personal.single')
            ->layout('admin::layouts.app');
    }
}
