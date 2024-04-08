<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personal extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        
        'dni',
        'nombre',
        'correo',
        'telefono',
        'cargo',
        'estado'
    ];
    public function __toString()
    {
        return $this->nombre; 
    }


    public function cargo()
    {
        return $this->belongsTo(Cargo::class, 'cargo');
    }
}
