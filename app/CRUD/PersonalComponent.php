<?php

namespace App\CRUD;

use EasyPanel\Contracts\CRUDComponent;
use App\Models\Personal;
use App\Models\Cargo;
use App\Models\Empresa;

class PersonalComponent implements CRUDComponent
{
    // Manage actions in crud
    public $create = true;
    public $delete = true;
    public $update = true;

    // If you will set it true it will automatically
    // add `user_id` to create and update action
    public $with_user_id = true;

    public function getModel()
    {
        return Personal::class;
    }

    // which kind of data should be showed in list page
    public function fields()
    {
        return ['dni', 'nombre', 'correo', 'Cargo', 'Empresa'];
    }
    
    // Searchable fields, if you dont want search feature, remove it
    public function searchable()
    {
        return ['dni', 'nombre','correo','cargo.nombre', 'empresa.nombre'];
    }

    // Write every fields in your db which you want to have a input
    // Available types : "ckeditor", "checkbox", "text", "select", "file", "textarea"
    // "password", "number", "email", "select", "date", "datetime", "time"
    public function inputs()
    {
        $options = $this->options();
    
        return [
            'dni' => 'text',
            'nombre' => 'text',
            'correo' => 'email', // Campo de tipo correo electrónico
            'cargo' => [
                'select' => $options['cargos'],
            ],
            'empresa' => [
                'select' => $options['empresas'],
            ],
        ];
    }
    
    // Validation in update and create actions
    // It uses Laravel validation system
    public function validationRules()
    {
        return [
            'dni' => 'required|string|max:20',
            'nombre' => 'required|string|max:255',
            'correo' => 'required|email|max:255', // Validación de correo electrónico único
            'cargo' => 'required|integer|exists:cargos,id',
            'empresa' => 'required|integer|exists:empresas,id',
        ];
    }
    // Where files will store for inputs
    public function storePaths()
    {
        return [];
    }

    public function options()
    {
        $cargos = Cargo::pluck('nombre', 'id')->toArray();
        $empresas = Empresa::pluck('nombre', 'id')->toArray();
    
        // Agregar la opción por defecto en los arreglos de cargos y empresas
        $cargos = ['' => 'Selecciona una opción'] + $cargos;
        $empresas = ['' => 'Selecciona una opción'] + $empresas;
    
        return [
            'cargos' => $cargos,
            'empresas' => $empresas,
        ];
    }
    
}
