<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    public $timestamps = false;
    use HasFactory;
    protected $fillable = ['nombre' ];
    public function __toString()
    {
        return $this->nombre; 
    }
}
