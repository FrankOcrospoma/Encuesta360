<?php

namespace App\Http\Controllers;

use App\Models\Detalle_empresa;
use Illuminate\Http\Request;
use App\Models\Personal; 
use App\Models\Empresa; 
use Illuminate\Support\Facades\Log;
use App\Imports\PersonasImport;
use App\Models\Evaluado;
use Maatwebsite\Excel\Facades\Excel;


class PersonasEmpresaController extends Controller
{
    public function store(Request $request)
    {
        try {
            $personalId = $request->input('personal_id');
            $dni = $request->input('dni');
    
    
            $datos = [
                'dni' => $dni,
                'nombre' => $request->input('nombre'),
                'correo' => $request->input('correo'),
                'telefono' => $request->input('telefono'),
                'cargo' => $request->input('cargo'),
            ];
    
            if ($personalId) {
                // Actualizar
                $personal = Personal::findOrFail($personalId);
                $personal->update($datos);
            } else {
                // Crear nuevo registro
                $personal = Personal::create($datos);
                // Crear un detalle de empresa si es necesario
                Detalle_empresa::create([
                    'personal_id' => $personal->id,
                    'empresa_id' => $request->input('empresa'),
                ]);
            }
    
            return response()->json(['data' => $personal]);
    
        } catch (\Exception $e) {
            Log::error('Error al guardar el personal: ' . $e->getMessage());
            return response()->json(['error' => 'Hubo un error al procesar la solicitud.'], 500);
        }
    }
    
    
    

    public function eliminar($id)
    {
        $persona = Personal::find($id);
        if($persona) {
            $persona->delete();
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
        $usuarios = Detalle_empresa::select('personals.id','personals.dni', 'personals.nombre', 'personals.correo')  // Selecciona todos los campos de personal
        ->join('personals', 'personals.id', '=', 'detalle_empresas.personal_id')
        ->where('detalle_empresas.empresa_id', $empresaId)
        ->groupBy('personals.id','personals.dni', 'personals.nombre', 'personals.correo')  // Asegúrate de agrupar por el ID de personal para evitar duplicados
        ->get();


        return response()->json($usuarios);
    }
    
    public function importarPersonas(Request $request)
    {
        $file = $request->file('file');
        $empresaId = $request->input('empresa_id'); // Asegúrate de obtener el ID de la empresa correctamente
        Excel::import(new PersonasImport($empresaId), $file);
    
        return back()->with('success', 'Importación de personas completada con éxito para la empresa seleccionada.');
    }
    public function agregarVinculo(Request $request) {
        try {
            Evaluado::where('evaluado_id', $request->personal_id)->where('empresa_id', $request->empresa_id)->where('encuesta_id', null)->delete();
            foreach ($request->evaluadores as $index => $evaluadorId) {
                Evaluado::create([
                    'evaluado_id' => $request->personal_id,
                    'evaluador_id' => $evaluadorId,
                    'vinculo_id' => $request->vinculos[$index],
                    'empresa_id' => $request->empresa_id,
                ]);
            }
            
            return response()->json(['message' => 'Vínculos guardados correctamente']);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Error al guardar los vínculos: ' . $e->getMessage()], 500);
        }
        
    }

    public function recuperarUltimosVinculos(Request $request)
{
    $empresaId = $request->empresa_id;
    $ultimosVinculos = Evaluado::with(['evaluador', 'vinculo'])
                                ->where('empresa_id', $empresaId)
                                ->whereNotNull('encuesta_id')
                                ->orderBy('encuesta_id', 'desc')
                                ->get();

    return response()->json($ultimosVinculos);
}


}

