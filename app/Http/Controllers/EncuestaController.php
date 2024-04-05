<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;

use Illuminate\Http\Request;
use App\Models\Detalle_pregunta;
use App\Models\Pregunta;
use App\Models\Categoria;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\EncuestaMailable;
use App\Models\Personal; // Asegúrate de que el namespace sea correcto para tu modelo.
use App\Models\Envio; // Asegúrate de que el namespace sea correcto para tu modelo.
use Illuminate\Support\Str; // Aquí se agrega la importación correcta
use Barryvdh\DomPDF\Facade\PDF;

use App\Models\Empresa;
use App\Models\Respuesta;

class EncuestaController extends Controller
{
    public function store(Request $request)
    {
        $preguntasSeleccionadas = $request->input('preguntasSeleccionadas', []);
        $preguntasConRespuesta = array_keys($preguntasSeleccionadas);
    
        DB::beginTransaction();
        try {
            // Crear la encuesta
            $encuesta = Encuesta::create([
                'nombre' => $request->input('nombre'),
                'empresa' => $request->input('empresa'),
                'fecha' => $request->input('fecha'),
            ]);
            // Registrar preguntas con respuestas seleccionadas
            foreach ($preguntasSeleccionadas as $preguntaId => $respuestas) {
                foreach ($respuestas as $respuestaId) {
                    if ($respuestaId !== 'todas') {
                        Detalle_pregunta::create([
                            'pregunta' => $preguntaId,
                            'respuesta' => $respuestaId,
                            'encuesta' => $encuesta->id,
                        ]); 
                    }
                }
            }
    
            // Identificar y registrar preguntas sin respuestas
            $todasLasPreguntas = request('todasLasPreguntas', []); // Asume que recibes un array con los IDs de todas las preguntas mostradas en el formulario
            $preguntasSinRespuesta = array_diff($todasLasPreguntas, $preguntasConRespuesta);
    
            foreach ($preguntasSinRespuesta as $preguntaId) {
                Detalle_pregunta::create([
                    'pregunta' => $preguntaId,
                    'respuesta' => null, // Asume que aceptas null en tu esquema de base de datos
                    'encuesta' => $encuesta->id,
                ]);
            }
    
            DB::commit();
            return redirect()->route('algunaRutaDeÉxito')->with('success', 'Encuesta creada con éxito.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Hubo un error al crear la encuesta.');
        }
    }
    
    


    public function enviarEncuesta(Request $request)
    {
        $correoDestinatario = $request->destinatarioCorreo;
        $encuestaId = $request->encuesta; // Asumiendo que también pasas el ID de la encuesta desde tu formulario.
        $persona = Personal::where('correo', $correoDestinatario)->first();
        $personaid = $persona->id;
        // Verifica si el correo del destinatario está presente
        if (empty($correoDestinatario)) {
            return redirect()->back()->with('error', 'No se ha especificado un correo electrónico válido.');
        }

        // Genera un UUID único
        $uuid = Str::uuid()->toString();
        // Guarda la relación en la base de datos
        $envio = Envio::create([
            'persona' => $personaid, // Asegúrate de que los nombres de columna sean correctos
            'encuesta' => $encuestaId,
            'estado' => false,
            'uuid' => $uuid, // Asegúrate de que tu modelo Envio tenga un campo 'uuid'
        ]);

        if (!$envio) {
            return redirect()->back()->with('error', 'No se pudo guardar el envío de la encuesta.');
        }

        // Genera el enlace a la encuesta con el UUID
        $link = route('encuestas.responder', ['uuid' => $uuid]); // Asegúrate de que la ruta y sus parámetros sean correctos.

        // Envía el correo electrónico con el enlace
        Mail::to($correoDestinatario)->send(new EncuestaMailable($link));

        return redirect()->back()->with('success', 'Encuesta enviada correctamente.');
    }
    public function responder($uuid)
    {

        $envio = Envio::where('uuid', $uuid)->firstOrFail();

        if ($envio->estado) {
            return view('encuestas.responder');
        }

        return view('encuestas.responder');
    }

    public function generarPDF($encuestaId)
    {
        // Buscar la encuesta por el ID proporcionado
        $encuesta = Encuesta::with('empresa') // Asumiendo una relación con 'empresas'
            ->findOrFail($encuestaId);

        // Esto asume una estructura donde 'detalle_preguntas' conecta 'preguntas' y 'respuestas' con 'encuestas'
        $detallePreguntas = Detalle_pregunta::with(['pregunta', 'respuesta'])
            ->where('encuesta', $encuestaId)
            ->get();


        $empresa = Empresa::where('id', $encuesta->empresa)->firstOrFail();

        $preguntas = Pregunta::where('estado', true)->get();
        $preguntasAbiertas = Pregunta::where('preguntas.estado', false)
        ->join('detalle_preguntas', 'preguntas.id', '=', 'detalle_preguntas.pregunta')
        ->join('persona_respuestas', 'detalle_preguntas.id', '=', 'persona_respuestas.detalle_pregunta')
        ->join('personals', 'persona_respuestas.persona', '=', 'personals.id')
        ->join('cargos', 'personals.cargo', '=', 'cargos.id')
        ->leftJoin('respuestas', function ($join) {
            $join->on('detalle_preguntas.respuesta', '=', 'respuestas.id')
                 ->where('respuestas.estado', false);
        })
        ->select('preguntas.texto as preguntaTexto', 'cargos.nombre as cargoNombre')
        ->selectRaw('GROUP_CONCAT(DISTINCT respuestas.texto ORDER BY respuestas.id SEPARATOR ", ") as respuestaTexto')
        ->groupBy('preguntas.texto', 'cargos.nombre', 'cargos.id')
        ->get();
    
    
    
        
        $categorias = Categoria::all();


        $respuestas = Respuesta::where('estado', true)->get();
        $respuestasAbiertas = Respuesta::where('estado', false)->get();

        $envios = Envio::with(['persona' => function ($query) {
            $query->with('cargo', 'empresa'); // Asumiendo relaciones 'cargo' y 'empresa' en el modelo 'personal'
        }])
            ->where('encuesta', $encuestaId)
            ->get();

        // Preparar todos los datos para pasar a la vista
        // Obtener los envíos de la encuesta

        $results = DB::select('CALL ObtenerDatosCargo(?)', array($encuestaId));
        $Top5 = DB::select('CALL ObtenerTop5PorCargo(?)', array($encuestaId));
        $categoria_1 = DB::select('CALL ObtenerResumenEnviosPorCategoria(1,?)', array($encuestaId));
        $categoria_2 = DB::select('CALL ObtenerResumenEnviosPorCategoria(2,?)', array($encuestaId));
        $categoria_3 = DB::select('CALL ObtenerResumenEnviosPorCategoria(3,?)', array($encuestaId));
        $categoria_4 = DB::select('CALL ObtenerResumenEnviosPorCategoria(4,?)', array($encuestaId));
        $categoria_5 = DB::select('CALL ObtenerResumenEnviosPorCategoria(5,?)', array($encuestaId));

        $categoria_1Array = [];
        foreach ($categoria_1 as $item) {
            $categoria_1Array[] = [
                'nombre' => $item->nombre,
                'promedio_rango' => number_format($item->promedio_rango, 2), // Formatear a dos decimales
                'cantidad_envios' => $item->cantidad_envios,
                'cantidad_rango_1' => $item->cantidad_rango_1,
                'cantidad_rango_2' => $item->cantidad_rango_2,
                'cantidad_rango_3' => $item->cantidad_rango_3,
                'cantidad_rango_4' => $item->cantidad_rango_4,
                'cantidad_rango_5' => $item->cantidad_rango_5,
            ];
        }
        $categoria_2Array = [];
        foreach ($categoria_2 as $item) {
            $categoria_2Array[] = (array)$item;
        }
        $categoria_3Array = [];
        foreach ($categoria_3 as $item) {
            $categoria_3Array[] = (array)$item;
        }
        $categoria_4Array = [];
        foreach ($categoria_4 as $item) {
            $categoria_4Array[] = (array)$item;
        }
        $categoria_5Array = [];
        foreach ($categoria_5 as $item) {
            $categoria_5Array[] = (array)$item;
        }

        $top5Array = [];
        foreach ($Top5 as $item) {
            $top5Array[] = (array)$item;
        }
        $Bottom5 = DB::select('CALL ObtenerBottom5PorCargo(?)', array($encuestaId));
        $Bottom5Array = [];
        foreach ($Bottom5 as $item) {
            $Bottom5Array[] = (array)$item;
        }
        // Convertir los resultados a un array asociativo
        $enviosPorCargoscore = [];
        foreach ($results as $item) {
            $enviosPorCargoscore[] = [
                'cargo' => $item->nombre,
                'promedio_rango' => number_format($item->promedio_rango, 2), // Formatear a dos decimales
                'cantidad_envios' => $item->cantidad_envios,
                'cantidad_rango_1' => $item->cantidad_rango_1,
                'cantidad_rango_2' => $item->cantidad_rango_2,
                'cantidad_rango_3' => $item->cantidad_rango_3,
                'cantidad_rango_4' => $item->cantidad_rango_4,
                'cantidad_rango_5' => $item->cantidad_rango_5,
            ];
        }

        // Preparar todos los datos para pasar a la vista
        $datos = [
            'encuesta' => $encuesta,
            'detallePreguntas' => $detallePreguntas,
            'enviosPorCargo' => $enviosPorCargoscore,
            'respuestas' => $respuestas,
            'envios' => $envios,
            'preguntas' => $preguntas,
            'categorias' => $categorias,
            'empresa' => $empresa,
            'top5' => $top5Array,
            'Bottom5' => $Bottom5Array,
            'respuestasAbiertas' => $respuestasAbiertas,
            'preguntasAbiertas' => $preguntasAbiertas,
            'categoria_1Array' => $categoria_1Array,
            'categoria_2Array' => $categoria_2Array,
            'categoria_3Array' => $categoria_3Array,
            'categoria_4Array' => $categoria_4Array,
            'categoria_5Array' => $categoria_5Array,



        ];

        // Cargar la vista de PDF con todos los datos recopilados
        $pdf = PDF::loadView('pdf.encuestaspdf', $datos);


        // Retornar el PDF al navegador
        return $pdf->stream();
    }
}
