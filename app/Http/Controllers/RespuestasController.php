<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Persona_respuesta; 
use App\Models\Envio; 
use App\Models\Respuesta; 

use App\Models\Detalle_pregunta;
use Illuminate\Support\Facades\DB; // Importar Facade para las transacciones

class RespuestasController extends Controller
{
    public function store(Request $request)
    {
        DB::beginTransaction();
    
        try {
            $uuid = $request->input('uuid');
            $envio = Envio::where('uuid', $uuid)->firstOrFail();
            $accion = $request->input('accion', 'definitivo');  // Recoger la acción del formulario
    
            $personaId = $envio->persona;
            $respuestas = $request->input('detalle', []);
            $respuestasAbiertas = $request->input('respuestaAbierta', []);
            $totalScore = 0;
            // dd($respuestasAbiertas);
            // Eliminar respuestas anteriores para evitar duplicados
            Persona_respuesta::where('persona', $personaId)
                             ->where('encuesta_id', $envio->encuesta)
                             ->delete();
    
            // Primero procesar respuestas predefinidas
            foreach ($respuestas as $preguntaId => $detallePreguntaId) {
                if (!is_null($detallePreguntaId)) {
                    $detalle = Detalle_pregunta::find($detallePreguntaId);
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
    
                    $detalle = Detalle_pregunta::create([
                        'pregunta' => $preguntaId,
                        'respuesta' => $nuevaRespuesta->id,
                    ]);
    
                    if ($detalle) {
                        $detalle->respuesta = $nuevaRespuesta->id;
                        $detalle->save();
    
                        // También registrar esta respuesta en persona_respuestas
                        Persona_respuesta::create([
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
    
            if ($accion === 'borrador') {
                $envio->estado = 'B';
            } else {
                // Código para finalizar y calcular promedios
                $envio->estado = 'F';
                $envio->rango = $promedioDecimal;
            }
            $envio->save(); // Guardar los cambios en la base de datos
    
            DB::commit();
            return redirect()->back()->with('success', 'Enviado correctamente');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Hubo un error al guardar las respuestas: ' . $e->getMessage());
        }
    }
    
    
}
