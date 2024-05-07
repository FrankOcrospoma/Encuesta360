<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Envio extends Model
{
    public $timestamps = false;
    use HasFactory;
    protected $fillable = ['persona', 'encuesta', 'estado', 'uuid'];
    public function persona()
    {
        return $this->belongsTo(Personal::class, 'persona');
    }

    public function encuesta()
    {
        return $this->belongsTo(Encuesta::class, 'encuesta');
    }
    public function getEstadoAttribute($value)
    {
        $estadoOptions = [
           'F' => 'Finalizado',
           'P'=> 'Pendiente',
           'B' => 'Borrador'
       
        ];

        return $estadoOptions[$value] ?? null;
    }
 
}
