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
        'empresa',
        'estado'
    ];
    public function __toString()
    {
        return $this->nombre; 
    }
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa');
    }

    public function cargo()
    {
        return $this->belongsTo(Cargo::class, 'cargo');
    }
}
