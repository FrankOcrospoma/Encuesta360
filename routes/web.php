<?php

use Illuminate\Support\Facades\Route;
use App\Models\Encuesta;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Http\Controllers\EncuestaController;
use App\Http\Controllers\FormularioController;
use App\Http\Controllers\RespuestasController;
use App\Http\Controllers\ModelosController;
use App\Http\Controllers\PersonasEmpresaController;
use Inertia\Inertia;
use App\Http\Controllers\AdminController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/login');
});


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {


    Route::get('/admin', [AdminController::class, 'index'])->name('admin');

    Route::get('/encuestas', function () {
        return view('admin::encuestas');
    })->name('encuestas');

    Route::get('/encuestapdf/{encuesta}', function ($encuestaId) {
        // Buscar la encuesta por el ID proporcionado
        $encuesta = Encuesta::where('id', $encuestaId)->firstOrFail();

        // Cargar la vista de PDF con la encuesta específica
        $pdf = PDF::loadView('pdf.encuestaspdf', compact('encuesta'));

        // Retornar el PDF al navegador
        return $pdf->stream();
    })->name('encuestas.pdf');

    Route::get('/encuestapdf/{encuesta}', [EncuestaController::class, 'generarPDF'])->name('encuestas.pdf');


    Route::post('/encuestas', [EncuestaController::class, 'store'])->name('encuestas.store');



    Route::get('/enviar-encuesta/{id}', [EncuestaController::class, 'enviarEncuesta'])->name('enviar.encuesta');
    Route::post('/enviar-todas-encuestas', [EncuestaController::class, 'enviarTodasEncuestas'])->name('enviar-todas-encuestas');



    Route::post('/personal/updateEstado/{id}', [ModelosController::class, 'updateEstadoPersona'])->name('personal.updateEstado');
    Route::post('/empresa/updateEstado/{id}', [ModelosController::class, 'updateEstadoEmpresa'])->name('empresa.updateEstado');
    Route::get('/empresa/personal/{empresaId}', [ModelosController::class, 'personal'])->name('empresa.personal');
    Route::post('/empresa/personal/', [PersonasEmpresaController::class, 'store'])->name('personals.create');
    Route::get('/persona/delete/{id}/{empresaid}', [PersonasEmpresaController::class, 'eliminar'])->name('persona.delete');
    Route::get('/personal/editar/{id}', [PersonasEmpresaController::class, 'editar'])->name('personal.editar');
    Route::post('/importar-personas', [PersonasEmpresaController::class, 'importarPersonas'])->name('importar.personas');
    Route::post('/agregar-vin', [PersonasEmpresaController::class, 'agregarVin'])->name('agregar.vinculo');


    Route::get('/encuestas/{id}/edit', [EncuestaController::class, 'edit'])->name('encuestas.edit');
    Route::put('/encuestas', [EncuestaController::class, 'store'])->name('encuestas.update');
    Route::get('/encuestas/destroy/{id}', [EncuestaController::class, 'destroy'])->name('encuestas.destroy');
    Route::get('/usuarios-por-empresa/{empresaId}', [PersonasEmpresaController::class, 'usuariosPorEmpresa'])->name('usuarios.por.empresa');
    Route::get('/encuestas/ver/{persona_id}/{encuesta_id}', [EncuestaController::class, 'verRespuestas'])->name('encuestas.ver');
    Route::get('/personal/search', [PersonasEmpresaController::class, 'search'])->name('personals.search');
    Route::post('/agregar-vinculo', [PersonasEmpresaController::class, 'agregarVinculo'])->name('agregar-vinculo');
    Route::get('/recuperar-ultimos-vinculos', [PersonasEmpresaController::class, 'recuperarUltimosVinculos'])->name('recuperar-ultimos-vinculos');
    // Rutas para Formulario
    Route::post('/formularios', [FormularioController::class, 'store'])->name('formularios.store');
    Route::get('/admin/formulario/update/{formulario}', [FormularioController::class, 'edit'])->name('formularios.edit');
    Route::put('/formularios/{formulario}', [FormularioController::class, 'update'])->name('formularios.update');
    Route::get('/obtener-evaluados/{empresaId}/{perid}',  [EncuestaController::class, 'obtenerEvaluados'])->name('obtener-evaluados');
});

Route::get('/encuestas/responder/{uuid}', [EncuestaController::class, 'responder'])->name('encuestas.responder');
Route::post('/guardar-respuestas', [RespuestasController::class, 'store'])->name('guardar.respuestas');
Route::post('/continuar-encuesta/{uuid}',  [EncuestaController::class, 'continuarEncuesta'])->name('continuar.encuesta');
