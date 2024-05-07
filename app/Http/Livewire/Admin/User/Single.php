<?php

namespace App\Http\Livewire\Admin\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Single extends Component
{

    public $user;

    public function mount(User $User){
        $this->user = $User;
    }

    public function delete()
    {
        // Iniciar transacción para asegurar la integridad de la data
        DB::beginTransaction();
        try {
            // Primero eliminamos las referencias en panel_admins
            DB::table('panel_admins')->where('user_id', $this->user->id)->delete();
    
            // Ahora eliminamos el usuario
            $this->user->delete();
    
            DB::commit(); // Confirma las operaciones si todo está correcto
    
            $this->dispatchBrowserEvent('show-message', ['type' => 'success', 'message' => __('DeletedMessage', ['name' => __('User') ]) ]);
            $this->emit('userDeleted');
        } catch (\Exception $e) {
            DB::rollback(); // Si algo sale mal, revierte toda la operación
            $this->dispatchBrowserEvent('show-message', ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    

    public function render()
    {
        return view('livewire.admin.user.single')
            ->layout('admin::layouts.app');
    }
}
