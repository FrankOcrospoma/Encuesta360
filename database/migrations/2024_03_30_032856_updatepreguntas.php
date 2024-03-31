<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Corrige los valores existentes en la columna 'estado'
        DB::table('preguntas')->whereNotIn('estado', [0, 1])->update(['estado' => 0]);
    }

    public function down()
    {
        // La lógica para revertir los cambios si es necesario
    }
};
