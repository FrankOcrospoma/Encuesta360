<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pregunta extends Model
{
    public $timestamps = false;
    use HasFactory;
    protected $fillable = ['texto', 'categoria', 'estado'];
    public function __toString()
    {
        return $this->texto; 
    }  
    public function preguntas()
{
    return $this->belongsToMany(Pregunta::class, 'detalle_preguntas', 'respuesta_id', 'pregunta_id');
}
public function categoria()
{
    return $this->belongsTo(Categoria::class, 'categoria');
}
protected $attributes = [
    'estado' => false,
];

}
