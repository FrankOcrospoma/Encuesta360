<?php

namespace App\Http\Livewire\Admin\User;

use App\Models\Panel_admin;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;

class Create extends Component
{
    use WithFileUploads;

    public $name;
    public $email;
    public $password;
    public $empresa_id;
    
    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8',        
    ];

    public function updated($input)
    {
        $this->validateOnly($input);
    }

    public function create()
    {
        if($this->getRules())
            $this->validate();
        
        $this->dispatchBrowserEvent('show-message', [
            'type' => 'success', 
            'message' => __('CreatedMessage', ['name' => __('User') ])
        ]);
        
        // Inicio de la transacción
        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password), // Aquí encriptas la contraseña
                'empresa_id' => $this->empresa_id,
                'user_id' => auth()->id(), // Esto parece incorrecto, usualmente user_id no es necesario aquí si estás creando un usuario nuevo
            ]);
        
            // Ahora insertamos en la tabla panel_admins
            Panel_admin::create([
                'user_id' => $user->id, // Usamos el ID del usuario recién creado
                'is_superuser' => false, // O el valor que necesites
                // 'created_at' y 'updated_at' se establecerán automáticamente si estás usando timestamps
            ]);
    
            DB::commit(); // Confirma las operaciones si todo está bien
            $this->reset();
        } catch (\Exception $e) {
            DB::rollback(); // Revierte las operaciones en caso de error
            // Manejo del error
            // Por ejemplo: $this->dispatchBrowserEvent('show-message', ['type' => 'error', 'message' => 'Error creating user']);
        }
    }
    
    

    public function render()
    {
        return view('livewire.admin.user.create')
            ->layout('admin::layouts.app', ['title' => __('CreateTitle', ['name' => __('User') ])]);
    }
}
