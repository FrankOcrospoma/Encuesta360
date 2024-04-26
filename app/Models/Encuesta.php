<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Encuesta extends Model
{
    public $timestamps = false;
    use HasFactory;
    protected $fillable = ['nombre', 'empresa', 'fecha', 'proceso'];
    protected $dates = ['fecha'];
    public function __toString()
    {
        return $this->nombre; 
    }
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa');
    }
// En tu modelo Encuesta
public function evaluados()
{
    return $this->hasMany(Evaluado::class);
}
public function preguntas()
{
    return $this->belongsToMany(Pregunta::class, 'encuesta_preguntas', 'encuesta_id', 'detalle_id');
}
public function encuesta_preguntas()
{
    return $this->hasMany(Encuesta_pregunta::class);
}
// Aquí agregamos la relación con Envios
public function envios()
{
    return $this->hasMany(Envio::class, 'encuesta');
}
}
