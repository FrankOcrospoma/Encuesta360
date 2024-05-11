<?php

namespace App\Http\Controllers;

use App\Models\Detalle_empresa;
use Illuminate\Http\Request;
use App\Models\Personal; 
use App\Models\Empresa;
use App\Models\Evaluado;
use App\Models\Vinculo;

class ModelosController extends Controller
{
    public function store(Request $request)
    {

         Personal::create([
            'dni' => $request->input('dni'),
            'nombre' => $request->input('nombre'),
            'correo' => $request->input('correo'),
            'telefono' => $request->input('telefono'),
            'cargo' => $request->input('cargo'),
            'estado' => $request->input('estado', false), // Usando false como valor predeterminado si 'estado' no está presente
        ]);
        
        // Añade un mensaje flash a la sesión.
        session()->flash('success', 'Guardado correctamente');
    
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
            $empresa = Empresa::findOrFail($id);
            $empresa->estado = filter_var($request->estado, FILTER_VALIDATE_BOOLEAN);
            $empresa->save();
    
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar el estado'], 500);
        }
    }
    public function personal($empresaId)
    {
        try {
            $detalle = Detalle_empresa::where('empresa_id', $empresaId)->get(); 
            $personalIds = $detalle->pluck('personal_id'); 
            $personal = Personal::whereIn('id', $personalIds)->get(); 
            $vinculos = Vinculo::where('vigencia',1)->get();
            $empresa = Empresa::findOrFail($empresaId); 
            $vinculados = Evaluado::where('empresa_id', $empresaId)->where('encuesta_id', null)->get();

        
            return view('partials.personal_details', compact('personal', 'empresa', 'vinculados', 'vinculos'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al buscar los detalles del personal: ' . $e->getMessage()]);
        }
    }
        
    
  
    
}
