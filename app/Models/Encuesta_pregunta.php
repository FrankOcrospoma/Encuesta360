<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Encuesta_pregunta extends Model
{
    public $timestamps = false;
    use HasFactory;
    protected $fillable = ['encuesta_id', 'detalle_id'];
    public function __toString()
    {
        return $this->encuesta_id; 
    }
    public function detalle()
{
    return $this->belongsToMany(Detalle_pregunta::class, 'encuesta_preguntas', 'encuesta_id', 'detalle_id');
}
}
