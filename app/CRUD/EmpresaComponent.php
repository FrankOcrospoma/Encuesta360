<?php

namespace App\CRUD;

use EasyPanel\Contracts\CRUDComponent;
use EasyPanel\Parsers\Fields\Field;
use App\Models\Empresa;

class EmpresaComponent implements CRUDComponent
{
    public $create = true;
    public $delete = true;
    public $update = true;
    public $with_user_id = true;

    public function getModel()
    {
        return Empresa::class;
    }

    public function fields()
    {
        return [
            'ruc',
            'nombre',
            'direccion',
            'representante',
            'estado'
        ];
         
    }
    
    public function searchable()
    {
        return [
            'ruc',
            'nombre',
            'estado'
        ];
    }

    public function inputs()
    {
    
        return [
            'ruc' => 'text',
            'nombre' => 'text',
            'direccion' => 'text',
            'representante' => 'text',
            'estado' => 'checkbox'

        ];
    }
    public function setField($field, $value)
    {
        $this->{$field} = $value;
    }
    
    public function validationRules()
    {
        return [
            'ruc' => 'required|string|max:11',
            'nombre' => 'max:100',
            'direccion' => 'max:200',
            'representante' => 'max:200'
          
        ];
    }

    public function storePaths()
    {
        return [];
    }
}
