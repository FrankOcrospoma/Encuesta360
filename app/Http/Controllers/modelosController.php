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

        // Aquí puedes crear una nueva instancia del modelo y guardar los datos.
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
        try {
            $detalle = Detalle_empresa::where('empresa_id', $empresaId)->get(); // Asegúrate que la columna se llame 'empresa_id' o ajusta según tu esquema de BD.
            // Si estás buscando múltiples registros en Detalle_empresa, debes iterar sobre cada uno para obtener los respectivos Personal
            $personalIds = $detalle->pluck('personal_id'); // Esto obtendrá una colección de todos los personal_id encontrados
            $personal = Personal::whereIn('id', $personalIds)->get(); // Esto buscará todos los Personal que coincidan con los IDs
            $vinculos = Vinculo::all();
            $empresa = Empresa::findOrFail($empresaId); // Esto lanzará una excepción si no se encuentra el registro
            $vinculados = Evaluado::where('empresa_id', $empresaId)->get();
            return view('partials.personal_details', compact('personal', 'empresa', 'vinculados', 'vinculos'));
        } catch (\Exception $e) {
            // Aquí manejas lo que sucede si hay un error, por ejemplo, redirigir al usuario a otra página o mostrar un mensaje de error
            return back()->withErrors(['error' => 'Error al buscar los detalles del personal: ' . $e->getMessage()]);
        }
    }
        
    
  
    
}
