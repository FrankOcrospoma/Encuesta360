<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Persona_Respuesta; 
use App\Models\Envio; 
use App\Models\Respuesta; 

use App\Models\Detalle_Pregunta;
use App\Models\Encuesta_pregunta;
use Illuminate\Support\Facades\DB; // Importar Facade para las transacciones

class RespuestasController extends Controller
{
    public function store(Request $request)
    {
        DB::beginTransaction();
    
        try {
            $uuid = $request->input('uuid');
            $envio = Envio::where('uuid', $uuid)->firstOrFail();
    
            $personaId = $envio->persona;
            $respuestas = $request->input('detalle', []);
            $respuestasAbiertas = $request->input('respuestaAbierta', []);
            $totalScore = 0;
    
            // Primero procesar respuestas predefinidas
            foreach ($respuestas as $preguntaId => $detallePreguntaId) {
                if (!is_null($detallePreguntaId)) {
                    $detalle = Detalle_Pregunta::find($detallePreguntaId);
                    if ($detalle) {
                        $respuesta = Respuesta::find($detalle->respuesta);
                        $totalScore += $respuesta->score;
    
                        // Registrar en persona_respuestas
                        Persona_Respuesta::create([
                            'persona' => $personaId,
                            'detalle' => $detallePreguntaId,
                            'encuesta_id' => $envio->encuesta,
                        ]);
                    }
                }
            }
    
            // Procesar respuestas abiertas
            foreach ($respuestasAbiertas as $preguntaId => $textoRespuesta) {
                if (!empty($textoRespuesta)) {
                    $nuevaRespuesta = Respuesta::create([
                        'texto' => $textoRespuesta,
                        'estado' => false,
                    ]);
    
                    $detalle =  Detalle_Pregunta::create([
                        'pregunta' => $preguntaId,
                        'respuesta' => $nuevaRespuesta->id,
                    ]);
                                    // Crear un nuevo registro en encuesta_preguntas

    
                    if ($detalle) {
                        $detalle->respuesta = $nuevaRespuesta->id;
                        $detalle->save();
                        Encuesta_pregunta::create([
                            'encuesta_id' =>  $envio->encuesta,
                            'detalle_id' => $detalle->id,
                        ]);
                        // También registrar esta respuesta en persona_respuestas
                        Persona_Respuesta::create([
                            'persona' => $personaId,
                            'detalle' => $detalle->id, 
                            'encuesta_id' => $envio->encuesta,
                        ]);
                    } else {
                        // Manejar el caso donde no existe el detalle (opcional)
                    }
                }
            }
    
            // Actualizar estado de envío
            $countRespuestas = count($respuestas);

            // Calcular el promedio de los scores
            $promedio = $countRespuestas > 0 ? $totalScore / $countRespuestas : 0;

            // Convertir el promedio a formato decimal
            $promedioDecimal =(float) number_format($promedio, 2, '.', '');


            // Actualizar el campo rango del envío con el promedio decimal
            $envio->estado = true; // Establecer el estado en true
            $envio->rango = $promedioDecimal; // Establecer el nuevo promedio como el valor de rango
            $envio->save(); // Guardar los cambios en la base de datos
    
            DB::commit();
            return redirect()->back()->with('success', 'Enviado correctamente');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Hubo un error al guardar las respuestas: ' . $e->getMessage());
        }
    }
    
    
    
}
