<?php

namespace App\Http\Controllers;

use App\Exceptions\VinculoNotFoundException;
use App\Models\Detalle_empresa;
use Illuminate\Http\Request;
use App\Models\Personal; 
use App\Models\Empresa; 
use Illuminate\Support\Facades\Log;
use App\Imports\PersonasImport;
use App\Models\Encuesta;
use App\Models\Evaluado;
use App\Models\Vinculo;
use Maatwebsite\Excel\Facades\Excel;


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
            ];
    
            if ($personalId) {
                // Actualizar
                $personal = Personal::findOrFail($personalId);
                $personal->update($datos);
                $message = 'El personal ha sido actualizado con éxito.';
            } else {
                // Crear nuevo registro
                $personal = Personal::create($datos);
                // Crear un detalle de empresa si es necesario
                Detalle_empresa::create([
                    'personal_id' => $personal->id,
                    'empresa_id' => $request->input('empresa'),
                ]);
                $message = 'El personal ha sido creado con éxito.';
            }
    
            return response()->json(['success' => true, 'message' => $message, 'data' => $personal]);
    
        } catch (\Exception $e) {
            Log::error('Error al guardar el personal: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Hubo un error al procesar la solicitud.'], 500);
        }
    }
    
    
    

    public function eliminar($id, $empresaid)
    {
      
            Detalle_empresa::where('personal_id', $id)->where('empresa_id',$empresaid)->delete();

            return response()->json(['success' => true, 'message' => 'Persona eliminada correctamente.']);

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
        try {
            $file = $request->file('file');
    
            if (!$file->isValid()) {
                return response()->json(['success' => false, 'error' => 'Error al subir el archivo.'], 400);
            }
    
            $empresaId = $request->input('empresa_id');
    
            // Guardar el archivo temporalmente y almacenar la ruta en la sesión
            $filePath = $file->storeAs('imports', $file->getClientOriginalName());
            session(['filePath' => $filePath, 'empresaId' => $empresaId]);
    
            // Intentar la importación
            Excel::import(new PersonasImport($empresaId), storage_path('app/' . $filePath));
    
            return back()->with('success', 'Importación de personas completada con éxito para la empresa seleccionada.');
        } catch (\Exception $e) {
            // Extraer el nombre del vínculo de la excepción y almacenarlo en la sesión
            $vinculoNombre = $this->getVinculoNombreFromException($e);
            return redirect()->back()->with('error', $e->getMessage())->with('vinculo_nombre', $vinculoNombre)->with('empresa_id', $empresaId);
        }
    }
    

    
    private function getVinculoNombreFromException($e)
    {
        // Aquí deberías extraer el nombre del vínculo del mensaje de la excepción
        // Esto depende de cómo estés manejando la excepción en la clase PersonasImport
        if (preg_match("/El vínculo '(.*)' no existe./", $e->getMessage(), $matches)) {
            return $matches[1];
        }
        return null;
    }
    public function agregarVin(Request $request)
    {
        try {
            // Verificar que el nombre no sea null
            $nombre = $request->input('nombre');
            if (is_null($nombre)) {
                throw new \Exception("El nombre del vínculo no puede ser nulo.");
            }
    
            // Crear el nuevo vínculo
            Vinculo::create(['nombre' => $nombre]);
    
            // Reintentar la importación
            $filePath = session('filePath');
            $empresaId = session('empresaId');
    
            if ($filePath && $empresaId) {
                Excel::import(new PersonasImport($empresaId), storage_path('app/' . $filePath));
    
                // Limpiar la sesión después de la importación exitosa
                session()->forget(['filePath', 'empresaId']);
                
                return redirect()->back()->with('success', 'Vínculo agregado e importación realizada con éxito.');
            } else {
                return redirect()->back()->with('error', 'No se encontró el archivo para reintentar la importación.');
            }
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'Error al agregar el vínculo: ' . $e->getMessage());
        }
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
    
        // Identificar el nombre del último proceso basado en la fecha más reciente
        $ultimoProceso = Encuesta::where('empresa', $empresaId)
                                 ->latest('fecha')
                                 ->first();
    
        if (!$ultimoProceso) {
            return response()->json([]);  // Devuelve un arreglo vacío si no hay procesos
        }
    
        // Obtener todas las encuestas que están en ese último proceso
        $ultimasEncuestas = Encuesta::where('empresa', $empresaId)
                                    ->where('proceso', $ultimoProceso->proceso)
                                    ->get()
                                    ->pluck('id');  // Recolectar todos los ids de encuesta del último proceso
    
        // Obtener los vínculos de todos los evaluados que están en las últimas encuestas del último proceso
        $ultimosVinculos = Evaluado::with(['evaluador', 'vinculo', 'personal'])
                                   ->whereIn('encuesta_id', $ultimasEncuestas)
                                   ->get();
    
        return response()->json($ultimosVinculos);
    }
    

}

