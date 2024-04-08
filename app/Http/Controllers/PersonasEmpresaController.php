<?php

namespace App\Http\Controllers;

use App\Models\Detalle_empresa;
use Illuminate\Http\Request;
use App\Models\Personal; 
use App\Models\Empresa; 
use Illuminate\Support\Facades\Log;



class PersonasEmpresaController extends Controller
{
    public function store(Request $request)
    {
        try {
            $personalId = $request->input('personal_id');
    
            $datos = [
                'dni' => $request->input('dni'),
                'nombre' => $request->input('nombre'),
                'correo' => $request->input('correo'),
                'telefono' => $request->input('telefono'),
                'cargo' => $request->input('cargo'),
                'estado' => $request->input('estado') ? 1 : 0, // Manejar el checkbox correctamente
            ];
    
            if ($personalId) {
                // Actualizar
                $personal = Personal::findOrFail($personalId);
                $personal->update($datos);
            } else {
                // Crear
                $personal = Personal::create($datos);
                // Crear un detalle de empresa si necesario
                Detalle_empresa::create([
                    'personal_id' => $personal->id,
                    'empresa_id' => $request->input('empresa'),
                ]);
            }
    
            // Retornar solo los datos del personal sin mensaje
            return response()->json(['data' => $personal]);
    
        } catch (\Exception $e) {
            Log::error('Error al guardar el personal: ' . $e->getMessage());
            // Retornar una respuesta con código de error y sin redireccionar
            return response()->json(['error' => 'Hubo un error al procesar la solicitud.'], 500);
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
   
    public function editar($id)
    {
        $personal = Personal::findOrFail($id);
        return response()->json($personal);
    }
    
    public function usuariosPorEmpresa($empresaId)
    {
        $detalle = Detalle_empresa::where('empresa_id', $empresaId)->get(); // Asegúrate que la columna se llame 'empresa_id' o ajusta según tu esquema de BD.
    
        // Si estás buscando múltiples registros en Detalle_empresa, debes iterar sobre cada uno para obtener los respectivos Personal
        $personalIds = $detalle->pluck('personal_id'); // Esto obtendrá una colección de todos los personal_id encontrados
        $usuarios = Personal::whereIn('id', $personalIds)->get(); // Esto buscará todos los Personal que coincidan con los IDs
        return response()->json($usuarios);
    }
    
    
}
