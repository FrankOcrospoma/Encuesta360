<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detalle_pregunta extends Model
{
    public $timestamps = false;
    use HasFactory;
    protected $table = 'detalle_preguntas';
    protected $fillable = ['pregunta', 'respuesta'];
    public function __toString()
    {
        return $this->id; 
    }

    public function Pregunta()
    {
        return $this->belongsTo(Pregunta::class, 'pregunta', 'id');
    }

    // Definición de la relación con Respuesta
    public function Respuesta()
    {
        return $this->hasMany(Respuesta::class, 'id', 'respuesta');
    }

}
