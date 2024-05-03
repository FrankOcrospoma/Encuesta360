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
        'estado',
      
    ];
    public function __toString()
    {
        return $this->nombre; 
    }
    public function envios()
    {
        // Asumiendo que 'persona_id' es la clave foránea en la tabla 'envio'
        // y 'id' es la clave primaria de 'personal'
        return $this->hasMany(Envio::class, 'persona', 'id');
    }
    public function detallesEmpresa()
{
    return $this->hasMany(Detalle_empresa::class, 'persona_id', 'id');
}

}
