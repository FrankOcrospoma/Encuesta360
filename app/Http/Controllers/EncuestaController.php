<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;

use Illuminate\Http\Request;
use App\Models\Detalle_pregunta;
use App\Models\Pregunta;
use App\Models\Categoria;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\EncuestaMailable;
use App\Models\Personal; // Asegúrate de que el namespace sea correcto para tu modelo.
use App\Models\Envio; // Asegúrate de que el namespace sea correcto para tu modelo.
use Illuminate\Support\Str; // Aquí se agrega la importación correcta
use Barryvdh\DomPDF\Facade\PDF;

use App\Models\Empresa;
use App\Models\Encuesta_pregunta;
use App\Models\Evaluado;
use App\Models\Respuesta;
use App\Models\Vinculo;
use Illuminate\Support\Facades\Redirect;

class EncuestaController extends Controller
{
    public function store(Request $request)
    {
        // Obtenemos los datos necesarios del request
        $nombre = $request->input('nombre');
        $empresaId = $request->input('empresa');
        $fecha = $request->input('fecha');
        $evaluado = $request->input('evaluado');
        // Preguntas seleccionadas vienen como un array de IDs desde el formulario
        $preguntasSeleccionadas = $request->input('preguntasSeleccionadas', []);
    
        // Evaluadores seleccionados y sus vínculos vienen como array de IDs y Vínculos desde el formulario
        $evaluadoresSeleccionados = $request->input('evaluadoresSeleccionados', []);
        $vinculosSeleccionados = $request->input('vinculosSeleccionados', []);
    
        DB::beginTransaction();

        try {
            $encuestaId = $request->input('encuesta_id');
    
            if ($encuestaId) {
                $encuesta = Encuesta::findOrFail($encuestaId);
                $encuesta->update([
                    'nombre' => $nombre,
                    'empresa' => $empresaId,
                    'fecha' => $fecha,
                ]);
    
                // Eliminamos las preguntas existentes asociadas con la encuesta
                Encuesta_pregunta::where('encuesta_id', $encuesta->id)->delete();

                // Insertamos las nuevas preguntas seleccionadas
                foreach ($preguntasSeleccionadas as $preguntaId) {
                    $detalle = Detalle_pregunta::where('pregunta', $preguntaId)->get();
                    Encuesta_pregunta::create([
                        'encuesta_id' => $encuesta->id,
                        'detalle_id' => $detalle->id,
                    ]);
                }
    
                // Eliminamos los evaluadores existentes
                Evaluado::where('encuesta_id', $encuestaId)->delete();
    
                // Añadimos los nuevos evaluadores seleccionados
                foreach ($evaluadoresSeleccionados as $index => $evaluadorId) {
                    Evaluado::create([
                        'evaluado_id' => $evaluado,
                        'evaluador_id' => $evaluadorId,
                        'encuesta_id' => $encuesta->id,
                        'vinculo_id' => $vinculosSeleccionados[$index],
                    ]);
                }
            } else {
                // Si no existe, estamos creando una nueva encuesta
                $encuesta = Encuesta::create([
                    'nombre' => $nombre,
                    'empresa' => $empresaId,
                    'fecha' => $fecha,
                ]);
                // Para cada pregunta seleccionada, creamos una entrada en la tabla 'encuesta_preguntas'
                foreach ($preguntasSeleccionadas as $preguntaId) {
                Encuesta_pregunta::create([
                    'encuesta_id' => $encuesta->id,
                    'pregunta_id' => $preguntaId,
                ]);
                }
                
                // Creamos los registros de evaluadores asociados a la encuesta
                foreach ($evaluadoresSeleccionados as $index => $evaluadorId) {
                    Evaluado::create([
                        'evaluado_id' => $evaluado,
                        'evaluador_id' => $evaluadorId,
                        'encuesta_id' => $encuesta->id,
                        'vinculo_id' => $vinculosSeleccionados[$index],
                    ]);
                }
            }
    


    
            DB::commit();
            return view('admin::encuestas');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput($request->input())->with('error', 'Hubo un error al ' . ($encuestaId ? 'actualizar' : 'crear') . ' la encuesta: ' . $e->getMessage());
        }
    }
    
    
    public function edit($id)
{
    $encuesta = Encuesta::where('id',$id)->first();

    $evaluados = Evaluado::where('encuesta_id',$id)->get();
    $evaluado = Evaluado::where('encuesta_id', $id)->first(); // Obtén el primer evaluado para la encuesta
    $per = null;
    if ($evaluado) {
        $per = Personal::where('id', $evaluado->evaluado_id)->first(); // Encuentra el Personal asociado
    }
        $encuesta_preguntas = Encuesta_pregunta::where('encuesta_id', $id)->get();
    $preguntasEncuesta = [];
    
    foreach ($encuesta_preguntas as $encuesta_pregunta) {
        $pregunta = Pregunta::find($encuesta_pregunta->pregunta_id);
        if ($pregunta) {
            $preguntasEncuesta[] = $pregunta;
        }
    }
    $usuarios = Personal::all();
    $vinculos = Vinculo::all();
    $empresas = Empresa::all(); // Asegúrate de tener este modelo y consulta disponibles.

    return view('admin::encuestas', compact('encuesta', 'per', 'preguntasEncuesta', 'vinculos', 'empresas', 'evaluados','usuarios','id'));
}
public function destroy($id)
{
    DB::beginTransaction();
    
    try {
        // Encuentra la encuesta y sus relaciones para eliminar
        $encuesta = Encuesta::findOrFail($id);
        
        // Elimina las relaciones. Asegúrate de ajustar estos métodos a tus necesidades.
        Encuesta_pregunta::where('encuesta_id', $encuesta->id)->delete();
        Evaluado::where('encuesta_id', $encuesta->id)->delete();
        Envio::where('encuesta', $encuesta->id)->delete();

        // Finalmente, elimina la encuesta
        $encuesta->delete();

        DB::commit();

        // Redirige a una ruta deseada con un mensaje de éxito
        return Redirect::route('encuestas')->with('success', 'Encuesta eliminada correctamente.');
    } catch (\Exception $e) {
        // En caso de error, revierte la transacción
        DB::rollback();
        
        // Redirige hacia atrás con un mensaje de error
        return back()->with('error', 'Error al eliminar la encuesta: ' . $e->getMessage());
    }
}


    public function enviarEncuesta($id)
    {
        try {
        $evaluados = Evaluado::where('encuesta_id',$id)->get();
        foreach($evaluados as $evaluado) {
            $uuid = Str::uuid()->toString();
            // Guarda la relación en la base de datos
            $envio = Envio::create([
                'persona' => $evaluado->evaluador_id, 
                'encuesta' => $id,
                'estado' => false,
                'uuid' => $uuid,
            ]);
            $per = Personal::where('id', $evaluado->evaluador_id)->first();
            // Verifica si el correo del destinatario está presente
            if (empty($per->correo)) {
                return redirect()->back()->with('error', 'No se ha especificado un correo electrónico válido.');
            }


            if (!$envio) {
                return redirect()->back()->with('error', 'No se pudo guardar el envío de la encuesta.');
            }

            // Genera el enlace a la encuesta con el UUID
            $link = route('encuestas.responder', ['uuid' => $uuid]); // Asegúrate de que la ruta y sus parámetros sean correctos.

            // Envía el correo electrónico con el enlace
            Mail::to($per->correo)->send(new EncuestaMailable($link));

        }
     

        return Redirect::route('encuestas')->with('success', 'Encuesta enviada correctamente.');
    } catch (\Exception $e) {
        // Redirige hacia atrás con un mensaje de error
        return back()->with('error', 'Error al eliminar la encuesta: ' . $e->getMessage());
    }
    }
    public function responder($uuid)
    {

        $envio = Envio::where('uuid', $uuid)->firstOrFail();

        if ($envio->estado) {
            return view('encuestas.responder');
        }

        return view('encuestas.responder');
    }

    public function generarPDF($encuestaId)
    {
        // Buscar la encuesta por el ID proporcionado
        $encuesta = Encuesta::with('empresa') // Asumiendo una relación con 'empresas'
            ->findOrFail($encuestaId);

        // Esto asume una estructura donde 'detalle_preguntas' conecta 'preguntas' y 'respuestas' con 'encuestas'
        $detallePreguntas = Detalle_pregunta::with(['pregunta', 'respuesta'])
            ->where('encuesta', $encuestaId)
            ->get();


        $empresa = Empresa::where('id', $encuesta->empresa)->firstOrFail();

        $preguntas = Pregunta::where('estado', true)->get();
        $preguntasAbiertas = Pregunta::where('preguntas.estado', false)
        ->join('detalle_preguntas', 'preguntas.id', '=', 'detalle_preguntas.pregunta')
        ->join('persona_respuestas', 'detalle_preguntas.id', '=', 'persona_respuestas.detalle_pregunta')
        ->join('personals', 'persona_respuestas.persona', '=', 'personals.id')
        ->join('cargos', 'personals.cargo', '=', 'cargos.id')
        ->leftJoin('respuestas', function ($join) {
            $join->on('detalle_preguntas.respuesta', '=', 'respuestas.id')
                 ->where('respuestas.estado', false);
        })
        ->select('preguntas.texto as preguntaTexto', 'cargos.nombre as cargoNombre')
        ->selectRaw('GROUP_CONCAT(DISTINCT respuestas.texto ORDER BY respuestas.id SEPARATOR ", ") as respuestaTexto')
        ->groupBy('preguntas.texto', 'cargos.nombre', 'cargos.id')
        ->get();
    
    
    
        
        $categorias = Categoria::all();


        $respuestas = Respuesta::where('estado', true)->get();
        $respuestasAbiertas = Respuesta::where('estado', false)->get();

        $envios = Envio::with(['persona' => function ($query) {
            $query->with('cargo', 'empresa'); // Asumiendo relaciones 'cargo' y 'empresa' en el modelo 'personal'
        }])
            ->where('encuesta', $encuestaId)
            ->get();

        // Preparar todos los datos para pasar a la vista
        // Obtener los envíos de la encuesta

        $results = DB::select('CALL ObtenerDatosCargo(?)', array($encuestaId));
        $Top5 = DB::select('CALL ObtenerTop5PorCargo(?)', array($encuestaId));
        $categoria_1 = DB::select('CALL ObtenerResumenEnviosPorCategoria(1,?)', array($encuestaId));
        $categoria_2 = DB::select('CALL ObtenerResumenEnviosPorCategoria(2,?)', array($encuestaId));
        $categoria_3 = DB::select('CALL ObtenerResumenEnviosPorCategoria(3,?)', array($encuestaId));
        $categoria_4 = DB::select('CALL ObtenerResumenEnviosPorCategoria(4,?)', array($encuestaId));
        $categoria_5 = DB::select('CALL ObtenerResumenEnviosPorCategoria(5,?)', array($encuestaId));

        $categoria_1Array = [];
        foreach ($categoria_1 as $item) {
            $categoria_1Array[] = [
                'nombre' => $item->nombre,
                'promedio_rango' => number_format($item->promedio_rango, 2), // Formatear a dos decimales
                'cantidad_envios' => $item->cantidad_envios,
                'cantidad_rango_1' => $item->cantidad_rango_1,
                'cantidad_rango_2' => $item->cantidad_rango_2,
                'cantidad_rango_3' => $item->cantidad_rango_3,
                'cantidad_rango_4' => $item->cantidad_rango_4,
                'cantidad_rango_5' => $item->cantidad_rango_5,
            ];
        }
        $categoria_2Array = [];
        foreach ($categoria_2 as $item) {
            $categoria_2Array[] = (array)$item;
        }
        $categoria_3Array = [];
        foreach ($categoria_3 as $item) {
            $categoria_3Array[] = (array)$item;
        }
        $categoria_4Array = [];
        foreach ($categoria_4 as $item) {
            $categoria_4Array[] = (array)$item;
        }
        $categoria_5Array = [];
        foreach ($categoria_5 as $item) {
            $categoria_5Array[] = (array)$item;
        }

        $top5Array = [];
        foreach ($Top5 as $item) {
            $top5Array[] = (array)$item;
        }
        $Bottom5 = DB::select('CALL ObtenerBottom5PorCargo(?)', array($encuestaId));
        $Bottom5Array = [];
        foreach ($Bottom5 as $item) {
            $Bottom5Array[] = (array)$item;
        }
        // Convertir los resultados a un array asociativo
        $enviosPorCargoscore = [];
        foreach ($results as $item) {
            $enviosPorCargoscore[] = [
                'cargo' => $item->nombre,
                'promedio_rango' => number_format($item->promedio_rango, 2), // Formatear a dos decimales
                'cantidad_envios' => $item->cantidad_envios,
                'cantidad_rango_1' => $item->cantidad_rango_1,
                'cantidad_rango_2' => $item->cantidad_rango_2,
                'cantidad_rango_3' => $item->cantidad_rango_3,
                'cantidad_rango_4' => $item->cantidad_rango_4,
                'cantidad_rango_5' => $item->cantidad_rango_5,
            ];
        }

        // Preparar todos los datos para pasar a la vista
        $datos = [
            'encuesta' => $encuesta,
            'detallePreguntas' => $detallePreguntas,
            'enviosPorCargo' => $enviosPorCargoscore,
            'respuestas' => $respuestas,
            'envios' => $envios,
            'preguntas' => $preguntas,
            'categorias' => $categorias,
            'empresa' => $empresa,
            'top5' => $top5Array,
            'Bottom5' => $Bottom5Array,
            'respuestasAbiertas' => $respuestasAbiertas,
            'preguntasAbiertas' => $preguntasAbiertas,
            'categoria_1Array' => $categoria_1Array,
            'categoria_2Array' => $categoria_2Array,
            'categoria_3Array' => $categoria_3Array,
            'categoria_4Array' => $categoria_4Array,
            'categoria_5Array' => $categoria_5Array,



        ];

        // Cargar la vista de PDF con todos los datos recopilados
        $pdf = PDF::loadView('pdf.encuestaspdf', $datos);


        // Retornar el PDF al navegador
        return $pdf->stream();
    }
}
