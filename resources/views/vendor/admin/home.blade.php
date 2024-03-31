@component('admin::layouts.app')
<?php

use App\Models\Encuesta;

$encuestas = Encuesta::with('empresa')->get(); // Asegúrate de cargar la relación con la empresa

?>
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.0/font/bootstrap-icons.css">
<style>
    .gradient-card-header {
        background: rgb(0,123,255);
        background: linear-gradient(to right,#8971ea,#7f72ea,#7574ea,#6a75e9,#5f76e8);
    }

    .card-hover:hover {
        transform: translateY(-5px);
        transition: all 0.3s ease-in-out;
        box-shadow: 0 10px 20px -10px rgba(0, 0, 0, 0.25);
    }

    .card {
        border: none;
        border-radius: 10px;
    }

    .export-btn {
        background-color: #0069d9;
        border: none;
        border-radius: 5px;
    }

    .export-btn:hover {
        background-color: #0056b3;
        box-shadow: 0 5px 15px -5px rgba(0, 0, 0, 0.2);
        color: white;
        text-decoration: none;
    }
    .pdf-export-btn {
        background-color: #d9534f; /* Rojo Bootstrap para el botón de PDF */
        color: white;
        border: none;
        border-radius: 5px;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        line-height: 1.5;
        transition: background-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .pdf-export-btn:hover {
        background-color: #c9302c; /* Rojo más oscuro para hover */
        color: white;
        text-decoration: none;
        box-shadow: 0 0 0 0.2rem rgba(217, 83, 79, 0.5);
    }

    .pdf-export-btn i {
        margin-right: 5px;
    }
</style>

<div class="container py-5">
    <h2 class="mb-4 text-center">Listado de Encuestas</h2>
    <div class="row g-4">
        @foreach ($encuestas as $encuesta)
        <div class="col-md-4">
            <div class="card h-100 card-hover">
                <div class="gradient-card-header py-3">
                    <h5 class="card-title text-center">{{ $encuesta->nombre }}</h5>
                </div>
                <div class="card-body">
                <p class="text-muted">
                        Empresa: {{ $encuesta->Empresa }}<br>
                        Representante: {{ $encuesta->Empresa->representante }}
                    </p>
                    <a href="{{ route('encuestas.pdf', ['encuesta' => $encuesta->id]) }}" class="mt-auto btn pdf-export-btn d-inline-flex align-items-center" target="_blank">
            <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
        </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endcomponent