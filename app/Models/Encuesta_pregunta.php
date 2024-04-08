<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Encuesta_pregunta extends Model
{
    public $timestamps = false;
    use HasFactory;
    protected $fillable = ['encuesta_id', 'pregunta_id'];
    public function __toString()
    {
        return $this->pregunta_id; 
    }
    public function pregunta()
{
    return $this->belongsToMany(Pregunta::class, 'encuesta_preguntas', 'encuesta_id', 'pregunta_id');
}
}
