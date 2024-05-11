<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Respuesta extends Model
{
    public $timestamps = false;
    use HasFactory;
    protected $fillable = ['texto','score','estado', 'vigencia'];
    public function __toString()
    {
        return $this->texto; 
    }  
    public function preguntas()
{
    return $this->belongsToMany(Pregunta::class, 'detalle_preguntas', 'respuesta_id', 'pregunta_id');
}
public function detallesPregunta()
{
    return $this->hasMany(Detalle_pregunta::class, 'respuesta', 'id');
}
}
