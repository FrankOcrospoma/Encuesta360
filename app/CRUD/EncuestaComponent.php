<?php

namespace App\CRUD;

use EasyPanel\Contracts\CRUDComponent;
use App\Models\Encuesta;
use App\Models\Empresa;


class EncuestaComponent implements CRUDComponent
{
    // Manage actions in CRUD
    public $create = true;
    public $delete = true;
    public $update = true;

    // If you set it true, it will automatically
    // add `user_id` to create and update actions
    public $with_user_id = true;

    public function getModel()
    {
        return Encuesta::class;
    }

    // Define the fields to be displayed in the list page
    public function fields()
    {
        return ['nombre', 'Empresa', 'fecha'];
    }

    // Define the searchable fields for search feature
    public function searchable()
    {
        return ['nombre', 'empresa', 'fecha'];
    }

    // Define the inputs for create and update actions
    public function inputs()
    {
        $options = $this->options(); // Obtenemos las opciones del método options()
        $optionsWithDefault = [
          'empresa' => ['Selecciona una opción'] + $options['empresa'],
      ];
        return [
            'nombre' => 'text',
            'empresa' => [ 'select' => $optionsWithDefault['empresa']], // Usamos las opciones con el primer ítem,
            'fecha' => 'date',
          
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
    // Define validation rules for update and create actions
    // It uses Laravel validation system
    public function validationRules()
    {
        return [
            'nombre' => 'required|string',
            'empresa' => 'required|string',
            'fecha' => 'required|date',
        ];
    }

    // Define where files will be stored for inputs
    public function storePaths()
    {
        return [];
    }
}
