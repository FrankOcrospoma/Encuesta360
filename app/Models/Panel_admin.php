<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Panel_admin extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'is_superuser'];
}
