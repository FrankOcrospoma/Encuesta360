<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detalle_empresa extends Model
{
    public $timestamps = false;
    use HasFactory;
    protected $fillable = ['personal_id', 'empresa_id'];
    public function __toString()
    {
        return $this->personal_id; 
    }


}
