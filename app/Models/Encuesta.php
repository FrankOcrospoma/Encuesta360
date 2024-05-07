<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Encuesta extends Model
{
    public $timestamps = false;
    use HasFactory;
    protected $fillable = ['nombre', 'empresa', 'fecha', 'proceso', 'formulario_id'];
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
public function formularios()
{
    return $this->hasMany(Formulario::class);
}
public function preguntas()
{
    return $this->belongsToMany(Pregunta::class, 'formularios', 'detalle_id');
}

// Aquí agregamos la relación con Envios
public function envios()
{
    return $this->hasMany(Envio::class, 'encuesta');
}

}
