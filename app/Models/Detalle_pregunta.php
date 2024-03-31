<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detalle_pregunta extends Model
{
    public $timestamps = false;
    use HasFactory;
    protected $fillable = ['pregunta', 'respuesta', 'encuesta'];
    public function __toString()
    {
        return $this->id; 
    }

    public function pregunta()
    {
        return $this->belongsTo(Pregunta::class, 'pregunta', 'id');
    }

    // Definición de la relación con Respuesta
    public function respuesta()
    {
        return $this->belongsTo(Respuesta::class, 'respuesta', 'id');
    }

}
