<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Personal; 
use App\Models\Empresa; 
use Illuminate\Support\Facades\Log;



class PersonasEmpresaController extends Controller
{
    public function store(Request $request)
    {
        try {
        // Aquí puedes crear una nueva instancia del modelo y guardar los datos.
        // En tu método store en PersonasEmpresaController

        $personal = Personal::create([
            'dni' => $request->input('dni'),
            'nombre' => $request->input('nombre'),
            'correo' => $request->input('correo'),
            'telefono' => $request->input('telefono'),
            'cargo' => $request->input('cargo'),
            'empresa' => $request->input('empresa'),
            'estado' => $request->input('estado'),
        ]);

        return response()->json(['success' => 'Registro completado con éxito', 'data' => $personal]);

    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Hubo un error al crear la encuesta.');
    }
    }

    public function eliminar($id)
    {
        $persona = Personal::find($id);
        if($persona) {
            $persona->delete();
            // Retornar una respuesta JSON en lugar de redirigir
            return response()->json(['success' => true, 'message' => 'Persona eliminada correctamente.']);
        } else {
            return response()->json(['success' => false, 'message' => 'Persona no encontrada.']);
        }
    }
    
    
  
    
}
