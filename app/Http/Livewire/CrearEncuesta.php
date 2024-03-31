<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

// Importa tus modelos
use App\Models\Encuesta;
use App\Models\Detalle_Pregunta;

class CrearEncuesta extends Component
{
    public $nombre, $empresa, $fecha, $preguntasSeleccionadas = [];

    public function crearEncuesta()
    {
        DB::beginTransaction();
        try {
            $primerDetalleId = null;
    
            // Suponiendo que preguntasSeleccionadas tiene la estructura adecuada.
            foreach ($this->preguntasSeleccionadas as $preguntaId => $respuestas) {
                foreach ($respuestas as $respuestaId) {
                    $detalle = Detalle_Pregunta::create([
                        'pregunta' => $preguntaId,
                        'respuesta' => $respuestaId,
                        // No se establece encuesta_id aquí porque sigue un diseño inusual
                    ]);
    
                    if (!$primerDetalleId) {
                        $primerDetalleId = $detalle->id;
                    }
                }
            }
    
            // Verifica si se creó al menos un detalle
            if ($primerDetalleId) {
                Encuesta::create([
                    'nombre' => $this->nombre,
                    'empresa' => $this->empresa,
                    'fecha' => $this->fecha,
                    'detalle' => $primerDetalleId, // Usa el ID del primer detalle creado
                ]);
            } else {
                throw new \Exception("No se pudo crear ningún detalle para la encuesta.");
            }
    
            DB::commit();
            session()->flash('message', 'Encuesta creada con éxito.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', "Error al crear la encuesta: {$e->getMessage()}");
        }
    }
    
    
    public function render()
    {
        return view('livewire.crear-encuesta');
    }
}
