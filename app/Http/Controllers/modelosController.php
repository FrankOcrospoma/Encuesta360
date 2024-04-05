<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Personal; 
use App\Models\Empresa; 



class ModelosController extends Controller
{
    public function store(Request $request)
    {

        // Aquí puedes crear una nueva instancia del modelo y guardar los datos.
         Personal::create([
            'empresa' => $request->input('empresa'),
            'dni' => $request->input('dni'),
            'nombre' => $request->input('nombre'),
            'correo' => $request->input('correo'),
            'telefono' => $request->input('telefono'),
            'cargo' => $request->input('cargo'),
            'estado' => $request->input('estado', false), // Usando false como valor predeterminado si 'estado' no está presente
        ]);
        
        // Añade un mensaje flash a la sesión.
        session()->flash('success', 'Guardado correctamente');
    
        // Redirige a donde necesites después de guardar los datos.
        return redirect()->route('algunaRutaDeÉxito')->with('success', 'Encuesta creada con éxito.');
    }

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
    public function personal($empresaId)
    {
        $personal = Personal::where('empresa', $empresaId)->get(); // Asegúrate que la columna se llame 'empresa_id' o ajusta según tu esquema de BD.
        $empresa = Empresa::findOrFail($empresaId); // Esto lanzará una excepción si no se encuentra el registro
        return view('partials.personal_details', compact('personal','empresa'));
    }
    
    
  
    
}
