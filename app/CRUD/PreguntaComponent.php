<?php

namespace App\CRUD;

use EasyPanel\Contracts\CRUDComponent;
use EasyPanel\Parsers\Fields\Field;
use App\Models\Pregunta;
use App\Models\Categoria; // Importa el modelo de Categoría

class PreguntaComponent implements CRUDComponent
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
        return Pregunta::class;
    }

    // Fields to be shown in the list page
    public function fields()
    {
        return [
    
            'texto',
            'Categoria'
            , 'estado'
        ];
    }

    // Searchable fields
    public function searchable()
    {
        return ['texto'];
    }

    // Input fields for create and update actions
    public function inputs()
    {
        $options = $this->options(); // Obtenemos las opciones del método options()
              $optionsWithDefault = [
                'categoria' => ['Selecciona una opción'] + $options['categoria'],
            ];
            
            return [
                'texto' => 'text',
                'categoria' => [
                    'select' => $optionsWithDefault['categoria'], // Usamos las opciones con el primer ítem
                ],
                'estado' =>
                    'checkbox' 
            
            ];
            
            
    }
     // Define las opciones para el menú desplegable
     public function options()
     {
         // Obtener todas las categorías disponibles
         $categorias = Categoria::pluck('nombre', 'id')->toArray();
     
     
         return [
             'categoria' => $categorias,
         ];
     }
    // Validation rules for update and create actions
    public function validationRules()
    {
        return [
            'texto' => 'required|string|max:255',
        ];
    }

    // Store paths for inputs
    public function storePaths()
    {
        return [];
    }

   
}