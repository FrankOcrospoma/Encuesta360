<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Formulario extends Model
{
    public $timestamps = false;
    use HasFactory;
    protected $fillable = ['id','detalle_id', 'nombre', 'estado'];
    public function __toString()
    {
        return $this->nombre; 
    }
    public function detalles()
{
    return $this->hasMany(Detalle_Pregunta::class,  'id', 'detalle_id');
}
}
