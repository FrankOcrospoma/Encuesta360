<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Personal; 
use App\Models\Empresa; 



class ModelosController extends Controller
{
    public function updateEstadoPersona(Request $request, $id)
    {
        try {
            $personal = Personal::findOrFail($id); // Esto lanzará una excepción si no se encuentra el registro
            $personal->estado = filter_var($request->estado, FILTER_VALIDATE_BOOLEAN);
            $personal->save();
    
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar el estado'], 500);
        }
    }
    public function updateEstadoEmpresa(Request $request, $id)
    {
        try {
            $empresa = Empresa::findOrFail($id); // Esto lanzará una excepción si no se encuentra el registro
            $empresa->estado = filter_var($request->estado, FILTER_VALIDATE_BOOLEAN);
            $empresa->save();
    
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar el estado'], 500);
        }
    }
    
    
    
    
    
}
