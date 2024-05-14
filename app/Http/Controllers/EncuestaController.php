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
use App\Models\Detalle_empresa;
use App\Models\Personal; // Asegúrate de que el namespace sea correcto para tu modelo.
use App\Models\Envio; // Asegúrate de que el namespace sea correcto para tu modelo.
use Illuminate\Support\Str; // Aquí se agrega la importación correcta
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Models\Persona_respuesta;
use App\Models\Empresa;

use App\Models\Evaluado;
use App\Models\Formulario;
use App\Models\Respuesta;
use App\Models\Vinculo;
use Illuminate\Support\Facades\Redirect;

class EncuestaController extends Controller
{
    public function store(Request $request)
    {
        // Obtenemos los datos necesarios del request
        $empresaId = $request->input('empresa');
        $fecha = $request->input('fecha');
        $proceso = $request->input('proceso');
    
        // Evaluadores seleccionados y sus vínculos vienen como array de IDs desde el formulario
        $evaluadosSeleccionados = $request->input('evaluadosSeleccionados', []);
        $formulariosSeleccionados = $_POST['formulariosSeleccionados'];
    
        // Verificar que todos los evaluados tengan al menos un vínculo
        foreach ($evaluadosSeleccionados as $evaluadoId) {
            // Consulta para obtener los vínculos de cada evaluado
            $vinculos = Evaluado::where('empresa_id', $empresaId)
                                ->where('evaluado_id', $evaluadoId)
                                ->whereNull('encuesta_id')
                                ->get();
    
            // Si no hay vínculos asociados, devuelve un mensaje de error
            if ($vinculos->isEmpty()) {
                $persona = Personal::find($evaluadoId);
                return response()->json([
                    'status' => 'error',
                    'message' => 'El evaluado ' . $persona->nombre . ' no tiene vínculos asociados. Por favor, añade vínculos antes de continuar.'
                ], 400);
            }
        }
    
        // Continuar con la creación de la encuesta si todos los evaluados tienen vínculos
        DB::beginTransaction();
        try {
            $encuestaId = $request->input('encuesta_id');
    
            foreach ($evaluadosSeleccionados as $key => $evsel) {
                $persona = Personal::find($evsel);
                $formularioId = $formulariosSeleccionados[$key];
    
                $encuesta = Encuesta::create([
                    'nombre' => 'Evaluación a ' . $persona->nombre,
                    'empresa' => $empresaId,
                    'fecha' => $fecha,
                    'proceso' => $proceso,
                    'formulario_id' => $formularioId
                ]);
    
                $evaluados = Evaluado::where('empresa_id', $empresaId)
                                    ->where('evaluado_id', $evsel)
                                    ->whereNull('encuesta_id')
                                    ->get();
    
                // Añadir los nuevos evaluadores seleccionados
                foreach ($evaluados as $evs) {
                    Evaluado::updateOrCreate(
                        ['id' => $evs->id], // clave(s) por las que buscar
                        ['encuesta_id' => $encuesta->id] // atributo a actualizar
                    );
                }
            }
    
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Encuesta creada exitosamente.'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Hubo un error al ' . ($encuestaId ? 'actualizar' : 'crear') . ' la encuesta: ' . $e->getMessage()
            ], 500);
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
            $formularios = Formulario::where('id', $encuesta->formulario_id)->get();
            $preguntasEncuesta = [];

            foreach ($formularios as $form) {
                $detalles = Detalle_pregunta::where('id', $form->detalle_id)->first();
                $pregunta = Pregunta::find($detalles->pregunta);
                if ($pregunta && !in_array($pregunta, $preguntasEncuesta)) {
                    $preguntasEncuesta[] = $pregunta;
                }
            }
            

        $usuarios = Personal::where('estado',1)->get();
        $vinculos = Vinculo::where('vigencia',1)->get();
        $empresas = Empresa::where('estado',1)->get();// Asegúrate de tener este modelo y consulta disponibles.

        return view('admin::encuestas', compact('encuesta', 'per', 'preguntasEncuesta', 'vinculos', 'empresas', 'evaluados','usuarios','id'));
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            // Encuentra la encuesta y sus relaciones para eliminar
            $encuesta = Encuesta::findOrFail($id);
            
            // Elimina las relaciones. Asegúrate de ajustar estos métodos a tus necesidades.
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
                    'estado' => 'P',
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
                Mail::to($per->correo)->send(new EncuestaMailable($link, $encuesta->empresa,  $per->nombre));

            }
     

        return Redirect::route('encuestas')->with('success', 'Encuesta enviada correctamente.');
        } catch (\Exception $e) {
            // Redirige hacia atrás con un mensaje de error
            return back()->with('error', 'Error al eliminar la encuesta: ' . $e->getMessage());
        }
    }

    public function enviarTodasEncuestas(Request $request)
    {
        $ids = $request->encuesta_ids;
        $errors = [];
        $successCount = 0;

        foreach ($ids as $id) {
            try {
                $encuesta = Encuesta::findOrFail($id);
                $evaluados = Evaluado::where('encuesta_id', $id)->get();
                foreach ($evaluados as $evaluado) {
                    $uuid = Str::uuid()->toString();
                    $envio = Envio::create([
                        'persona' => $evaluado->evaluador_id,
                        'encuesta' => $id,
                        'estado' => 'P',
                        'uuid' => $uuid,
                    ]);
                    $per = Personal::where('id', $evaluado->evaluador_id)->first();
                    if (!$per->correo) {
                        continue; // O maneja el error como prefieras
                    }
                    $link = route('encuestas.responder', ['uuid' => $uuid]);
                    Mail::to($per->correo)->send(new EncuestaMailable($link, $encuesta->empresa,  $per->nombre));
                }
                $successCount++;
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        if ($successCount > 0 && empty($errors)) {
            return redirect()->route('encuestas')->with('success', "Se enviaron $successCount encuestas correctamente.");
        } else {
            return redirect()->route('encuestas')->with('error', 'Errores encontrados: ' . implode(' ', $errors));
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

    public function continuarEncuesta(Request $request, $uuid) 
    {
        $envio = Envio::where('uuid', $uuid)->firstOrFail();
        if ($envio->estado == 'Borrador') {
            $envio->estado = 'P';
            $envio->save();
            return response()->json(['message' => 'Estado actualizado correctamente'], 200);
        }
        return response()->json(['message' => 'No se pudo actualizar el estado'], 400);
    }

    public function generarPDF($encuestaId)
        {
            // Buscar la encuesta por el ID proporcionado
                // Buscar la encuesta por el ID proporcionado
        $encuesta = Encuesta::find($encuestaId);

        if (!$encuesta) {
            return redirect()->back()->withErrors(['error' => 'La encuesta no existe.']);
        }

        $envios = Envio::where('encuesta', $encuestaId)->where('estado', 'F')->get();

        if ($envios->isEmpty()) {
            // Redirigir con un mensaje si no hay envíos en estado "True"
            return redirect()->back()->with('error', 'No hay envíos completados para esta encuesta.');
        }

            $encuesta_pre = Formulario::where('id', $encuesta->formulario_id)
            ->first(); // Usamos first() en lugar de get() para obtener solo un objeto en lugar de una colección
            
            if ($encuesta_pre) {
                // Esto asume una estructura donde 'detalle_preguntas' conecta 'preguntas' y 'respuestas' con 'encuestas'
                $detallePreguntas = Detalle_pregunta::with(['pregunta', 'respuesta'])
                    ->where('id', $encuesta_pre->detalle_id)
                    ->get();
            }
            $evaluado = Evaluado::where('encuesta_id', $encuestaId)->firstOrFail();



            $empresa = Empresa::where('id', $encuesta->empresa)->firstOrFail();

            $preguntas = Pregunta::select('preguntas.*')  // Selecciona todas las columnas de preguntas
                    ->distinct()  // Asegúrate de que las preguntas sean únicas
                    ->where('estado', true)
                    ->join('detalle_preguntas', 'preguntas.id', '=', 'detalle_preguntas.pregunta')
                    ->join('persona_respuestas', 'detalle_preguntas.id', '=', 'persona_respuestas.detalle')
                    ->where('persona_respuestas.encuesta_id', $encuestaId)
                    ->get();

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
        
            $categorias = Categoria::select('categorias.*')
                    ->distinct()
                    ->join('preguntas', 'preguntas.categoria', '=', 'categorias.id')
                    ->join('detalle_preguntas', 'preguntas.id', '=', 'detalle_preguntas.pregunta')
                    ->join('persona_respuestas', 'detalle_preguntas.id', '=', 'persona_respuestas.detalle')
                    ->join('formularios', 'formularios.detalle_id', '=', 'detalle_preguntas.id')
                    ->where('formularios.id', $encuesta->formulario_id)
                    ->where('persona_respuestas.encuesta_id', $encuestaId)
                    ->get();

            $respuestas = Respuesta::select('respuestas.*')
                    ->distinct()
                    ->join('detalle_preguntas', 'respuestas.id', '=', 'detalle_preguntas.respuesta')
                    ->join('formularios', 'formularios.detalle_id', '=', 'detalle_preguntas.id')
                    ->where('formularios.id', $encuesta->formulario_id)
                    ->where('respuestas.estado', true)->where('respuestas.score', '!=', 0)    
                    ->orderBy('respuestas.score')
                    ->get();


            $envios = Envio::with('persona')
                ->where('encuesta', $encuestaId)
                ->get();
            $vinculos = Vinculo::where('vigencia',1)->get();

            $results = DB::select('CALL ObtenerDatosResumen(?)', array($encuestaId));
            $enviosPorCargoscore = [];
            
            // Preparar nombres dinámicos de respuestas
            $responseNames = [];
            if (!empty($results)) {
                foreach ($results[0] as $key => $value) {
                    if (!in_array($key, ['nombre_vinculo', 'promedio_score', 'cantidad_respuestas'])) {
                        $responseNames[] = $key;
                    }
                }
            }
            
            foreach ($results as $item) {
                $entry = [
                    'cargo' => $item->nombre_vinculo,
                    'promedio_rango' => number_format($item->promedio_score, 2), // Formatear a dos decimales
                    'cantidad_envios' => $item->cantidad_respuestas,
                ];
            
                // Añadir dinámicamente las respuestas
                foreach ($responseNames as $responseName) {
                    $entry[$responseName] = $item->$responseName ?? 0;
                }
            
                $enviosPorCargoscore[] = $entry;
            }

            // Inicializar un array para almacenar los resultados categorizados
            $resultadosPorCategoria = [];

            // Iterar sobre cada categoría para obtener los datos
            foreach ($categorias as $key => $cats) {
                // Ejecutar el procedimiento almacenado con las categorías y la encuesta específica
                $resultados = DB::select('CALL ObtenerResumenEnviosPorCategoria(?, ?)', [$cats->id, $encuestaId]);

                // Verificar si hay resultados antes de procesar
                if (count($resultados) > 0) {
                    // Obtener los nombres de las columnas dinámicas
                    $columns = array_keys(get_object_vars($resultados[0]));
                    $responseFields = array_filter($columns, function ($col) {
                        return !in_array($col, ['nombre_vinculo', 'promedio_score', 'cantidad_respuestas']);
                    });

                    // Procesar los resultados para esta categoría
                    $processedResults = [];
                    foreach ($resultados as $item) {
                        $data = [
                            'nombre_vinculo' => $item->nombre_vinculo,
                            'promedio_score' => number_format($item->promedio_score, 2),
                            'cantidad_respuestas' => $item->cantidad_respuestas,
                            'respuestas' => []
                        ];

                        // Agregar todas las respuestas dinámicas al array de respuestas
                        foreach ($responseFields as $field) {
                            $data['respuestas'][$field] = $item->$field ?? 0;
                        }

                        $processedResults[] = $data;
                    }

                    // Asignar los resultados procesados a la categoría correspondiente
                    $resultadosPorCategoria[$cats->id] = $processedResults;
                }
            }
            $resultadosPorPregunta = [];

            foreach ($preguntas as $key => $preg) {
                $resultsp = DB::select('CALL ObtenerResumenEnviosPorPregunta(?, ?)', [$encuestaId, $preg->id]);
            
                if (count($resultsp) > 0) {
                    // Detectar columnas dinámicas de respuesta
                    $columns = array_keys(get_object_vars($resultsp[0]));
                    $responseFields = array_filter($columns, function ($col) {
                        return !in_array($col, ['nombre_vinculo', 'promedio_score', 'cantidad_respuestas']);
                    });
            
                    // Almacenar los datos
                    foreach ($resultsp as $item) {
                        $data = [
                            'nombre_vinculo' => $item->nombre_vinculo,
                            'promedio_score' => number_format($item->promedio_score, 2),
                            'cantidad_respuestas' => $item->cantidad_respuestas,
                            'respuestas' => []
                        ];
            
                        foreach ($responseFields as $field) {
                            $data['respuestas'][$field] = $item->$field ?? 0;
                        }
            
                        $resultadosPorPregunta[$preg->id][] = $data;
                    }
                } else {
                    $resultadosPorPregunta[$preg->id] = [];
                }
            }
            
            
            
            foreach ($vinculos as $key => $vin) {
            
                $Top5[$vin->nombre]  = DB::select('CALL ObtenerTop5PorCargo(?,?)', array($encuestaId,$vin->id));
                $Bottom5[$vin->nombre] = DB::select('CALL ObtenerBottom5PorCargo(?,?)', array($encuestaId,$vin->id));

            }
            $Top5["Your Average"]  = DB::select('CALL ObtenerTop5PorCargo(?,0)', array($encuestaId));

            $Bottom5["Your Average"] = DB::select('CALL ObtenerBottom5PorCargo(?,0)', array($encuestaId));
            // Convertir los resultados a un array asociativo
          

            // dd($resultadosPorCategoria);
            

            $datos = [
                'encuesta' => $encuesta,
                'detallePreguntas' => $detallePreguntas,
                'enviosPorCargo' => $enviosPorCargoscore,
                'responseNames' => $responseNames,
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
        // Obtener las respuestas únicas de la persona correspondiente al ID proporcionado
        $respuestas = Persona_Respuesta::where('persona', $persona_id)
                                        ->where('encuesta_id', $encuesta_id)
                                        ->get();
        
        $detallesp = [];
        
        foreach ($respuestas as $key => $rpt) {
            // Obtener la pregunta única de la encuesta correspondiente al ID proporcionado y al detalle de respuesta
            // Asegurarse de que el detalle_id sea distinto para evitar duplicados
            $e_preguntas = Formulario::where('detalle_id', $rpt->detalle)
                                      ->distinct()
                                      ->get(['detalle_id']);
            
            foreach ($e_preguntas as $key => $ep) {
                // Obtener los detalles de la pregunta
                $detalle = Detalle_Pregunta::where('id', $ep->detalle_id)->first();
                
                if ($detalle && !array_key_exists($detalle->id, $detallesp)) {
                    // Agregar el detalle de pregunta al array, asegurándose de que no esté ya incluido
                    $detallesp[$detalle->id] = $detalle;
                }
            }
        }
        
        // Devolver los datos en formato JSON
        return view('partials.partial_respuestas', compact('respuestas', 'detallesp'))->render();
    }
    
    public function obtenerEvaluados($empresaId, $perid) 
    {
        
        $vinculados = Evaluado::where('empresa_id', $empresaId)->where('encuesta_id', null)->where('evaluado_id', $perid)->get();

        $ultimosVin = Evaluado::with(['evaluador', 'vinculo'])
                                ->where('empresa_id', $empresaId)
                                ->whereNotNull('encuesta_id')
                                ->orderBy('encuesta_id', 'desc')
                                ->get();
        $vinculos = Vinculo::where('vigencia',1)->get();

        $detalle = Detalle_empresa::where('empresa_id', $empresaId)->get(); 
        $personalIds = $detalle->pluck('personal_id'); 
        $personals = Personal::whereIn('id', $personalIds)->get(); 
        return view('partials.partial_vinculos', compact('vinculados', 'ultimosVin', 'vinculos', 'personals','empresaId', 'perid'))->render();
    }
    
}
