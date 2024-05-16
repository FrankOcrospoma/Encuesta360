<?php

namespace App\Imports;

use App\Models\Personal;
use App\Models\Detalle_empresa;
use App\Models\Evaluado;
use App\Models\Vinculo;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;

class PersonasImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, WithEvents
{
    use Importable;

    private $empresaId;
    private $evaluados;
    private $autoEvaluadoId;

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

        return DB::transaction(function () use ($row) {
            $personal = Personal::where('correo', $row['email'])->first();

            if ($personal) {
                $detalleEmpresa = Detalle_empresa::where('personal_id', $personal->id)
                    ->where('empresa_id', $this->empresaId)
                    ->first();

                if (!$detalleEmpresa) {
                    Detalle_empresa::create([
                        'personal_id' => $personal->id,
                        'empresa_id' => $this->empresaId,
                    ]);
                }
            } else {
                $personal = Personal::create([
                    'dni' => $row['dni'] ?? null,
                    'nombre' => $row['nombre'] . ' ' . $row['apellido'],
                    'correo' => $row['email'],
                    'telefono' => $row['telefono'] ?? null,
                    'cargo' => $row['cargo'] ?? null,
                ]);

                Detalle_empresa::create([
                    'personal_id' => $personal->id,
                    'empresa_id' => $this->empresaId,
                ]);
            }

            $vinculoId = $this->getVinculoIdByNombre($row['relationship_to_me']);
            if (trim($row['relationship_to_me']) === "Auto Evaluación") {
                $this->autoEvaluadoId = $personal->id;
            }

            $this->evaluados[] = [
                'evaluado_id' => $this->autoEvaluadoId,
                'personal_id' => $personal->id,
                'vinculo_id' => $vinculoId,
                'empresa_id' => $this->empresaId,
            ];

            return null;
        });
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                $this->afterImport();
            },
        ];
    }

    private function getVinculoIdByNombre($nombre)
    {
        $vinculo = Vinculo::where('nombre', $nombre)->first();

        if (!$vinculo) {
            throw new \Exception("El vínculo '{$nombre}' no existe.");
        }

        return $vinculo->id;
    }

    public function afterImport()
    {
        DB::transaction(function () {
            foreach ($this->evaluados as $evaluado) {
                $exists = Evaluado::where('evaluado_id', $evaluado['evaluado_id'])
                    ->where('evaluador_id', $evaluado['personal_id'])
                    ->where('encuesta_id', null)
                    ->where('empresa_id', $evaluado['empresa_id'])
                    ->exists();

                if (!$exists) {
                    Evaluado::create([
                        'evaluado_id' => $evaluado['evaluado_id'],
                        'evaluador_id' => $evaluado['personal_id'],
                        'vinculo_id' => $evaluado['vinculo_id'],
                        'empresa_id' => $evaluado['empresa_id'],
                    ]);
                }
            }
        });
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
