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
use App\Models\Persona_respuesta;
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
                    $detalles = Detalle_pregunta::where('pregunta', $preguntaId)->get();
                    foreach ($detalles as $detalle) {
                    Encuesta_pregunta::create([
                        'encuesta_id' => $encuesta->id,
                        'detalle_id' => $detalle->id,
                    ]);
                 }
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
                    $detalles = Detalle_pregunta::where('pregunta', $preguntaId)->get();
                    foreach ($detalles as $detalle) {
                    Encuesta_pregunta::create([
                        'encuesta_id' => $encuesta->id,
                        'detalle_id' => $detalle->id,
                    ]);
                 }
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
            $detalles = Detalle_pregunta::where('id', $encuesta_pregunta->detalle_id)->first();
            $pregunta = Pregunta::find($detalles->pregunta);
            if ($pregunta && !in_array($pregunta, $preguntasEncuesta)) {
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
        Persona_respuesta::where('encuesta_id', $encuesta->id)->delete();
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
            $encuesta = Encuesta::where('id',$id)->first();
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
            Mail::to($per->correo)->send(new EncuestaMailable($link, $encuesta->Empresa));

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
        $encuesta = Encuesta::find($encuestaId);
        $encuesta_pre = Encuesta_pregunta::where('encuesta_id', $encuestaId)
        ->first(); // Usamos first() en lugar de get() para obtener solo un objeto en lugar de una colección
        
        if ($encuesta_pre) {
            // Esto asume una estructura donde 'detalle_preguntas' conecta 'preguntas' y 'respuestas' con 'encuestas'
            $detallePreguntas = Detalle_pregunta::with(['pregunta', 'respuesta'])
                ->where('id', $encuesta_pre->detalle_id)
                ->get();
        }
        $evaluado = Evaluado::where('encuesta_id', $encuestaId)->firstOrFail();



        $empresa = Empresa::where('id', $encuesta->empresa)->firstOrFail();

        $preguntas = Pregunta::where('estado', true)->get();
        $preguntasAbiertas = Pregunta::where('preguntas.estado', false)
                ->join('detalle_preguntas', 'preguntas.id', '=', 'detalle_preguntas.pregunta')
                ->join('persona_respuestas', 'detalle_preguntas.id', '=', 'persona_respuestas.detalle')
                ->join('personals', 'persona_respuestas.persona', '=', 'personals.id')
                ->join('evaluados', 'personals.id', '=', 'evaluados.evaluador_id')
                ->join('vinculos', 'evaluados.vinculo_id', '=', 'vinculos.id')
                ->leftJoin('respuestas', function ($join) {
                    $join->on('detalle_preguntas.respuesta', '=', 'respuestas.id')
                        ->where('respuestas.estado', false);
                })
                ->where('persona_respuestas.encuesta_id', $encuestaId)
                ->where('evaluados.encuesta_id', $encuestaId)
                ->select('preguntas.texto as preguntaTexto', 'vinculos.nombre as nombreVinculos')
                ->selectRaw("GROUP_CONCAT(DISTINCT respuestas.texto ORDER BY respuestas.id SEPARATOR '\n') as respuestaTexto")
                ->groupBy('preguntas.texto', 'vinculos.nombre')
                ->get();

    
    
    
    
    
    
        $categorias = Categoria::all();

        $respuestas = Respuesta::where('estado', true)->get();

        $envios = Envio::with('persona')
            ->where('encuesta', $encuestaId)
            ->get();
        $vinculos = Vinculo::all();

        $results = DB::select('CALL ObtenerDatosResumen(?)', array($encuestaId));

        foreach ($categorias as $key => $cats) {
            $resultadosPorCategoria[$cats->id] = DB::select('CALL ObtenerResumenEnviosPorCategoria(?,?)', array($cats->id, $encuestaId));

        }
        foreach ($preguntas as $key => $pregs) {
            $resultadosPorPregunta[$pregs->id] = DB::select('CALL ObtenerResumenEnviosPorPregunta(?,?)', array( $encuestaId,$pregs->id));
        }
        foreach ($vinculos as $key => $vin) {
         
            $Top5[$vin->nombre]  = DB::select('CALL ObtenerTop5PorCargo(?,?)', array($encuestaId,$vin->id));
            $Bottom5[$vin->nombre] = DB::select('CALL ObtenerBottom5PorCargo(?,?)', array($encuestaId,$vin->id));

        }
        $Top5["Your Average"]  = DB::select('CALL ObtenerTop5PorCargo(?,0)', array($encuestaId));

        $Bottom5["Your Average"] = DB::select('CALL ObtenerBottom5PorCargo(?,0)', array($encuestaId));
        // Convertir los resultados a un array asociativo
        $enviosPorCargoscore = [];
        foreach ($results as $item) {
            $enviosPorCargoscore[] = [
                'cargo' => $item->nombre_vinculo,
                'promedio_rango' => number_format($item->promedio_score, 2), // Formatear a dos decimales
                'cantidad_envios' => $item->cantidad_respuestas,
                'cantidad_rango_1' => $item->Oportunidad_Crítica,
                'cantidad_rango_2' => $item->Debe_Mejorar,
                'cantidad_rango_3' => $item->Regular,
                'cantidad_rango_4' => $item->Hábil,
                'cantidad_rango_5' => $item->Destaca,
            ];
        }

        $datos = [
            'encuesta' => $encuesta,
            'detallePreguntas' => $detallePreguntas,
            'enviosPorCargo' => $enviosPorCargoscore,
            'respuestas' => $respuestas,
            'envios' => $envios,
            'preguntas' => $preguntas,
            'categorias' => $categorias,
            'empresa' => $empresa,
            'top5' => $Top5,
            'Bottom5' => $Bottom5,
            'preguntasAbiertas' => $preguntasAbiertas,
            'resultadosPorCategoria' => $resultadosPorCategoria,
            'evaluado' => $evaluado,
            'resultadosPorPregunta' => $resultadosPorPregunta


        ];

        // Cargar la vista de PDF con todos los datos recopilados
        $pdf = PDF::loadView('pdf.encuestaspdf', $datos);


        // Retornar el PDF al navegador
        return $pdf->stream();
    }
    public function verRespuestas($persona_id, $encuesta_id)
    {
        // Obtener las respuestas de la persona correspondiente al ID proporcionado
        $respuestas = Persona_Respuesta::where('persona', $persona_id)->where('encuesta_id', $encuesta_id)->get();
        
        $detallesp = [];
        
        foreach ($respuestas as $key => $rpt) {
            // Obtener las preguntas de la encuesta correspondiente al ID proporcionado y al detalle de respuesta
            $e_preguntas = Encuesta_Pregunta::where('encuesta_id', $encuesta_id)->where('detalle_id', $rpt->detalle)->get();
            
            foreach ($e_preguntas as $key => $ep) {
                // Obtener los detalles de la pregunta
                $detalle = Detalle_Pregunta::where('id', $ep->detalle_id)->first();
                
                if ($detalle) {
                    // Agregar el detalle de pregunta al array
                    $detallesp[] = $detalle;
                }
            }
        }
        
        // Devolver los datos en formato JSON
        return view('partials.partial_respuestas', compact('respuestas', 'detallesp'))->render();
    }
    
    
}
