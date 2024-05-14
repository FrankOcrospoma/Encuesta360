<?php

namespace App\CRUD;

use EasyPanel\Contracts\CRUDComponent;
use EasyPanel\Parsers\Fields\Field;
use App\Models\Categoria;

class CategoriaComponent implements CRUDComponent
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
        return Categoria::class;
    }

    // which kind of data should be showed in list page
    public function fields()
    {
        return ['nombre', 'descripcion'];
    }

    // Searchable fields, if you dont want search feature, remove it
    public function searchable()
    {
        return ['nombre'];
    }


    public function inputs()
    {
        return [
            'nombre' => 'text',
            'descripcion' => 'textarea',
        ];
    }

    // Validation in update and create actions
    // It uses Laravel validation system
    public function validationRules()
    {
        return [
            'nombre' => 'required|string|max:255',
        ];
    }

    // Where files will store for inputs
    public function storePaths()
    {
        return [];
    }
}
