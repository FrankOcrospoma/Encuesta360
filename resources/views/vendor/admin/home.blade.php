@component('admin::layouts.app')

<?php

use App\Models\Encuesta;

$encuestas = Encuesta::with('empresa')->get(); // Asegúrate de cargar la relación con la empresa

// Initialize counts
$process1Count = 0;
$process2Count = 0;
$process3Count = 0;

// Calculate the counts based on some attribute
foreach ($encuestas as $encuesta) {
    switch ($encuesta->proceso) {
        case 'Proceso 1':
            $process1Count++;
            break;
        case 'Proceso 2':
            $process2Count++;
            break;
        case 'Proceso 3':
            $process3Count++;
            break;
    }
}

// Initialize variables to store scores and counts for averaging
$scores1 = [];
$scores2 = [];
$scores3 = [];

foreach ($encuestas as $encuesta) {
    switch ($encuesta->empresa) { // Assuming 'name' is the identifier for the company
        case 'Empresa 1':
            $scores1[] = $encuesta->score; // Assuming 'score' is the attribute for scores
            break;
        case 'Empresa 2':
            $scores2[] = $encuesta->score;
            break;
        case 'Empresa 3':
            $scores3[] = $encuesta->score;
            break;
    }
}

// Calculate average scores, ensure no division by zero
$averageScore1 = !empty($scores1) ? array_sum($scores1) / count($scores1) : 0;
$averageScore2 = !empty($scores2) ? array_sum($scores2) / count($scores2) : 0;
$averageScore3 = !empty($scores3) ? array_sum($scores3) / count($scores3) : 0;

?>

<style>
    /* Additional CSS for styling the dashboard */
    .card-header { background: linear-gradient(60deg, #66a6ff, #89f7fe); color: white; }
    .card { margin-bottom: 20px; }
</style>

<div class="container py-5">
    <h2 class="mb-4 text-center">Dashboard de Encuestas</h2>

    <div class="row">
        <!-- Survey distribution chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Distribución de Encuestas por Proceso</div>
                <div class="card-body">
                    EN PROCESO...

                    <canvas id="surveyProcessChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Performance metrics chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Métricas de Rendimiento por Empresa</div>
                <div class="card-body">
                    EN PROCESO...

                    <canvas id="performanceMetricsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed surveys table -->
    <h3 class="mt-5 text-center">Detalles de las Encuestas</h3>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    EN PROCESO...

                    {{-- <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Empresa</th>
                                <th>Proceso</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($encuestas as $encuesta)
                            <tr>
                                <td>{{ $encuesta->nombre }}</td>
                                <td>{{ $encuesta->empresa }}</td>
                                <td>{{ $encuesta->proceso }}</td>
                                <td>{{ $encuesta->fecha}}</td>
                                <td>
                                    <a href="{{ route('encuestas.pdf', ['encuesta' => $encuesta->id]) }}" class="btn btn-danger" target="_blank">
                                        <i class="bi bi-file-earmark-pdf"></i> PDF
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table> --}}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctxProcess = document.getElementById('surveyProcessChart').getContext('2d');
    var surveyProcessChart = new Chart(ctxProcess, {
        type: 'pie',
        data: {
            labels: ['Proceso 1', 'Proceso 2', 'Proceso 3'],
            datasets: [{
                data: [{{ $process1Count }}, {{ $process2Count }}, {{ $process3Count }}],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Distribución de Encuestas por Proceso'
                }
            }
        }
    });

    var ctxPerformance = document.getElementById('performanceMetricsChart').getContext('2d');
    var performanceMetricsChart = new Chart(ctxPerformance, {
        type: 'bar',
        data: {
            labels: ['Empresa 1', 'Empresa 2', 'Empresa 3'],
            datasets: [{
                label: 'Score Promedio',
                data: [{{ $averageScore1 }}, {{ $averageScore2 }}, {{ $averageScore3 }}],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(75, 192, 192, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Métricas de Rendimiento por Empresa'
                }
            }
        }
    });
});

</script>
<script>

</script>

@endcomponent
