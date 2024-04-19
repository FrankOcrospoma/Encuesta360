<?php

namespace App\Imports;

use App\Models\Personal;
use App\Models\Detalle_empresa;
use App\Models\Evaluado;
use App\Models\Vinculo;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;

class PersonasImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, WithEvents
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    use Importable;
    private $empresaId;
    private $evaluados; // La propiedad para almacenar información temporal para la inserción en el modelo Evaluado
    private $autoEvaluadoId; // Añadir esta propiedad

    public function __construct(int $empresaId)
    {
        $this->empresaId = $empresaId;
        $this->evaluados = [];

    }
    public function model(array $row)
    {
        if (empty($row['nombre']) || empty($row['apellido']) || empty($row['email'])) {
            return null;
        }
    
        $personal = Personal::create([
            'dni'       => $row['dni'] ?? null,
            'nombre'    => $row['nombre'] . ' ' . $row['apellido'],
            'correo'    => $row['email'],
            'telefono'  => $row['telefono'] ?? null,
            'cargo'     => $row['cargo'] ?? null,
        ]);
    
        Detalle_empresa::create([
            'personal_id' => $personal->id,
            'empresa_id' => $this->empresaId,
        ]);
    
        // Aquí asumimos que existe una función que obtiene el id del vinculo basado en el nombre.
        $vinculoId = $this->getVinculoIdByNombre($row['relationship_to_me']);
        // y que esta columna se llama 'auto_evaluado'.
        if (trim($row['relationship_to_me']) === "Auto Evaluación") {
            $this->autoEvaluadoId = $personal->id;
        }
    
        // El resto de inserciones para Evaluado deben hacerse después de procesar todas las filas,
        // por lo que se almacenarán en una propiedad de la clase para procesar después.
        $this->evaluados[] = [
            'evaluado_id' => $this->autoEvaluadoId, // Usar la propiedad de la clase.
            'personal_id' => $personal->id,
            'vinculo_id'  => $vinculoId,
            'empresa_id'  => $this->empresaId,
        ];


        return null;
    }
    public function registerEvents(): array
    {
        return [
            AfterImport::class => function(AfterImport $event) {
                $this->afterImport();
            },
        ];
    }
    
    private function getVinculoIdByNombre($nombre) {
        // Buscar el vínculo por nombre. Si no existe, crearlo.
        $vinculo = Vinculo::firstOrCreate(['nombre' => $nombre]);
        return $vinculo->id;
    }
    
    
    // Agregar este método para procesar las inserciones después de la importación.
    public function afterImport() {
        foreach ($this->evaluados as $evaluado) {
            Evaluado::create([
                'evaluado_id' => $evaluado['evaluado_id'],
                'evaluador_id' => $evaluado['personal_id'], // Suponiendo que el evaluador y evaluado son la misma persona.
                'vinculo_id'  => $evaluado['vinculo_id'],
                'empresa_id'  => $evaluado['empresa_id'],
            ]);
        }
    }
    
  
    
    
    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
