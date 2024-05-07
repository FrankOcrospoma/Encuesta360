<?php

namespace App\CRUD;

use App\Models\Empresa;
use EasyPanel\Contracts\CRUDComponent;
use EasyPanel\Parsers\Fields\Field;
use App\Models\User;

class UserComponent implements CRUDComponent
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
        return User::class;
    }

    // which kind of data should be showed in list page
    public function fields()
    {
        return ['name', 'email', 'empresa'];
    }

    // Searchable fields, if you dont want search feature, remove it
    public function searchable()
    {
        return ['name', 'email', 'empresa.nombre'];
    }

    // Write every fields in your db which you want to have a input
    // Available types : "ckeditor", "checkbox", "text", "select", "file", "textarea"
    // "password", "number", "email", "select", "date", "datetime", "time"
    public function inputs()
    {
        $options = $this->options(); // Obtenemos las opciones del método options()
        $optionsWithDefault = [
            'empresa' => ['Selecciona una opción'] + $options['empresa'],
        ];
        return [
            'name' => 'text',
            'email' => 'email',
            'password' => 'password',
            'empresa_id' => [
                'select' => $optionsWithDefault['empresa'], // Usamos las opciones con el primer ítem
            ],
        ];
    }
    public function options()
    {
        // Obtener todas las categorías disponibles
        $empresas = Empresa::pluck('nombre', 'id')->toArray();
    
    
        return [
            'empresa' => $empresas,
        ];
    }
    // Validation in update and create actions
    // It uses Laravel validation system
    public function validationRules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ];
    }

    // Where files will store for inputs
    public function storePaths()
    {
        return [];
    }
    
}
    