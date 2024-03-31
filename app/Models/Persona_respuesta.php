<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona_respuesta extends Model
{
    public $timestamps = false;
    use HasFactory;
    protected $fillable = [ 'persona', 'detalle_pregunta',];
    public function __toString()
    {
        return $this->id; 
    }
}
