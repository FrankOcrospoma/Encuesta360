<?php

namespace App\CRUD;

use EasyPanel\Contracts\CRUDComponent;
use App\Models\Respuesta;
use App\Models\Opcion;

class RespuestaComponent implements CRUDComponent
{
    // Manage actions in CRUD
    public $create = true;
    public $delete = true;
    public $update = true;

    // If you set it to true, it will automatically
    // add `user_id` to create and update actions
    public $with_user_id = true;

    public function getModel()
    {
        return Respuesta::class;
    }

    // Fields to be shown in the list page
    public function fields()
    {
        return [
            'id',
            'texto',
            'score',
            'estado'
        ];
    }

    // Searchable fields
    public function searchable()
    {
        return ['texto', 'score'];
    }

    // Input fields for create and update actions
    public function inputs()
    {
        return [
            'texto' => 'text',
            'score' =>  'number',
            'estado' => 'checkbox'
        ];
    }

    // Validation rules for update and create actions
    public function validationRules()
    {
        return [
            'texto' => 'required|string|max:255',
            'score' =>  'required|numeric'
        ];
    }

    // Store paths for inputs
    public function storePaths()
    {
        return [];
    }

   
}
