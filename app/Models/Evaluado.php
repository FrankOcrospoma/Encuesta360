<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluado extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [

        'evaluado_id',
        'evaluador_id',
        'encuesta_id',
        'vinculo_id',
    ];
    public function __toString()
    {
        return $this->evaluado_id; 
    }



    public function evaluador()
    {
        return $this->belongsTo(Personal::class, 'evaluador_id');
    }
    public function encuesta_id()
    {
        return $this->belongsTo(Encuesta::class, 'encuesta_id');
    }
    public function Vinculo()
    {
        return $this->belongsTo(Vinculo::class, 'vinculo_id');
    }
    // En tu modelo Evaluado
    public function personal()
    {
        return $this->belongsTo(Personal::class, 'evaluado_id');
    }
       

}
