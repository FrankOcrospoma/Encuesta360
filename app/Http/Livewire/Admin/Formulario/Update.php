<?php

namespace App\Http\Livewire\Admin\Formulario;

use App\Models\Detalle_pregunta;
use App\Models\Formulario;
use App\Models\Pregunta;
use Livewire\Component;
use Livewire\WithFileUploads;

class Update extends Component
{
    use WithFileUploads;

    public $formulario;

    public $nombre;
    
    protected $rules = [
        
    ];

    public function mount(Formulario $Formulario){
        $this->formulario = $Formulario;
        $this->nombre = $this->formulario->nombre;        
    }

    public function updated($input)
    {
        $this->validateOnly($input);
    }

    public function update()
    {
        if($this->getRules())
            $this->validate();

        $this->dispatchBrowserEvent('show-message', ['type' => 'success', 'message' => __('UpdatedMessage', ['name' => __('Formulario') ]) ]);
        
        $this->formulario->update([
            'nombre' => $this->nombre,
            'user_id' => auth()->id(),
        ]);
    }

    public function render()
    {
        $preguntas = Pregunta::all();
        $idDelFormulario = $this->formulario->id;
        $forms = Formulario::where('id', $idDelFormulario)->get();
        $detalles = [];
        $respuestasSeleccionadas = [];
    
        foreach ($forms as $form) {
            $detalle = Detalle_pregunta::where('id', $form->detalle_id)->first();
            if ($detalle) {
                $detalles[] = $detalle;
                // Suponiendo que tienes una relación para obtener las respuestas desde detalle
                $respuestasSeleccionadas = array_merge($respuestasSeleccionadas, $detalle->Respuesta->pluck('id')->toArray());
            }
        }
        $preguntasEncuesta = [];
    
        foreach ($detalles as $det) {
            $preguntaEncuesta = Pregunta::where('id', $det->pregunta)->first();
            if ($preguntaEncuesta && !isset($preguntasEncuesta[$preguntaEncuesta->id])) {
                $preguntasEncuesta[$preguntaEncuesta->id] = $preguntaEncuesta;
            }
        }
        $preguntasEncuesta = array_values($preguntasEncuesta);
    
        return view('livewire.admin.formulario.update', [
            'formulario' => $this->formulario,
            'preguntas' => $preguntas,
            'preguntasEncuesta' => $preguntasEncuesta,
            'respuestasSeleccionadas' => $respuestasSeleccionadas
        ])->layout('admin::layouts.app', ['title' => __('UpdateTitle', ['name' => __('Formulario')])]);
    }
    
}
