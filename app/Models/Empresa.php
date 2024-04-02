<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    public $timestamps = false;
    use HasFactory;
    protected $fillable = ['ruc', 'nombre', 'direccion', 'representante', 'estado'];
    public function __toString()
    {
        return $this->nombre; 
    }
}