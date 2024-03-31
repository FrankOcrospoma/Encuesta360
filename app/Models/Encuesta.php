<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Encuesta extends Model
{
    public $timestamps = false;
    use HasFactory;
    protected $fillable = ['nombre', 'empresa', 'fecha'];
    public function __toString()
    {
        return $this->nombre; 
    }
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa');
    }

}
