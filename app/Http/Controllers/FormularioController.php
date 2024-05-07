<?php

namespace App\Http\Controllers;

use App\Models\Detalle_pregunta;
use App\Models\Formulario;
use App\Models\Pregunta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormularioController extends Controller
{


    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
    
            // Obtener el máximo ID actual y aumentarlo en uno para la nueva transacción
            $maxId = Formulario::max('id') + 1;
    
            $detalles = Detalle_pregunta::all();
            $preguntasSeleccionadas = $request->input('preguntasSeleccionadas', []);
            $respuestasSeleccionadas = $request->input('respuestas', []); // Nueva línea para capturar las respuestas
            // dd($preguntasSeleccionadas);
            foreach ($preguntasSeleccionadas as $pregunta_id) {
                foreach ($detalles as $detalle) {
                    if ($detalle->pregunta == $pregunta_id) {
                        if ($detalle->respuesta == null ) {
                            Formulario::create([
                                'id' => $maxId,  // Usar el ID calculado para la transacción
                                'detalle_id' => $detalle->id,
                                'nombre' => $request->nombre,
                            ]);
                          }
                        foreach ($respuestasSeleccionadas as $rptId) {
                          if ($rptId == $detalle->respuesta ) {
                            Formulario::create([
                                'id' => $maxId,  // Usar el ID calculado para la transacción
                                'detalle_id' => $detalle->id,
                                'nombre' => $request->nombre,
                            ]);
                          }  
                        }
                       
                    }
                }
            }
    
            DB::commit();
            return back();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Error al crear el formulario: ' . $e->getMessage());
        }
    }
    public function edit(Formulario $formulario)
    {
        $preguntas = Pregunta::all(); // Asumiendo que tienes un modelo Pregunta
        return view('livewire.admin.formulario.update', compact('formulario', 'preguntas'));
    }

    public function update(Request $request, Formulario $formulario)
    {
        try {
            DB::beginTransaction();

            Formulario::where('id', $formulario->id)->delete();

            $detalles = Detalle_pregunta::all();
            $preguntasSeleccionadas = $request->input('preguntasSeleccionadas', []);
            $respuestasSeleccionadas = $request->input('respuestas', []); // Nueva línea para capturar las respuestas
            foreach ($preguntasSeleccionadas as $pregunta_id) {
                foreach ($detalles as $detalle) {
                    if ($detalle->pregunta == $pregunta_id) {
                        if ($detalle->respuesta == null ) {
                            Formulario::create([
                                'id' => $formulario->id,  // Usar el ID calculado para la transacción
                                'detalle_id' => $detalle->id,
                                'nombre' => $request->nombre,
                            ]);
                          }
                        foreach ($respuestasSeleccionadas as $rptId) {
                          if ($rptId == $detalle->respuesta ) {
                            Formulario::create([
                                'id' => $formulario->id,  // Usar el ID calculado para la transacción
                                'detalle_id' => $detalle->id,
                                'nombre' => $request->nombre,
                            ]);
                          }
                        }
                       
                    }
                }
            }
    

            DB::commit();
            return back();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Error al actualizar el formulario: ' . $e->getMessage());
        }
    }
    
    
}
