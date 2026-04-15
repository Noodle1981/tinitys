<?php

use Livewire\Volt\Component;
use App\Models\Patient;
use App\Models\PatientSession;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public $patientId;
    public $noise_exposure = 'no';
    public $acufenos = false;
    public $vertigos = false;
    public $selectedSessionId = 'new';
    public $initialData = [];

    public function mount($patientId)
    {
        $this->patientId = $patientId;
        $patient = Patient::findOrFail($patientId);
        
        // Cargar últimos datos clínicos básicos
        $this->noise_exposure = $patient->noise_exposure ? 'si' : 'no';
        $this->acufenos = (bool)$patient->tinnitus_symptom;
        $this->vertigos = (bool)$patient->vertigo_symptom;

        // Por requerimiento, las nuevas evaluaciones comienzan vacías
        $this->initialData = ['right' => (object)[], 'left' => (object)[]];
    }

    public function with()
    {
        return [
            'sessions' => PatientSession::where('patient_id', $this->patientId)
                ->where('type', 'doctor')
                ->latest()
                ->get()
        ];
    }

    public function updatedSelectedSessionId($id)
    {
        if ($id === 'new') {
            $this->dispatch('load-audiogram', data: ['right' => (object)[], 'left' => (object)[]], readOnly: false);
            return;
        }

        $session = PatientSession::findOrFail($id);
        $data = ['right' => (object)[], 'left' => (object)[]];

        if ($session->audiometry_data) {
            $validFreqs = [125, 250, 500, 750, 1000, 1500, 2000, 3000, 4000, 6000, 8000];
            
            $od = $session->audiometry_data['oido_derecho'] ?? [];
            $rightData = [];
            foreach ($od as $item) {
                if (in_array((int)$item['frecuencia_hz'], $validFreqs) && $item['umbral_db'] !== null) {
                    $rightData[(string)$item['frecuencia_hz']] = $item['umbral_db'];
                }
            }
            if (!empty($rightData)) {
                $data['right'] = $rightData;
            }

            $oi = $session->audiometry_data['oido_izquierdo'] ?? [];
            $leftData = [];
            foreach ($oi as $item) {
                if (in_array((int)$item['frecuencia_hz'], $validFreqs) && $item['umbral_db'] !== null) {
                    $leftData[(string)$item['frecuencia_hz']] = $item['umbral_db'];
                }
            }
            if (!empty($leftData)) {
                $data['left'] = $leftData;
            }
        }

        $this->dispatch('load-audiogram', data: $data, readOnly: true);
    }

    public function save($data)
    {
        // Solo permitir guardado si estamos en modo "Nueva"
        if ($this->selectedSessionId !== 'new') return;

        $patient = Patient::findOrFail($this->patientId);

        // Actualizar perfil clínico del paciente
        $patient->update([
            'noise_exposure' => $this->noise_exposure == 'si',
            'tinnitus_symptom' => $this->acufenos,
            'vertigo_symptom' => $this->vertigos,
        ]);

        // Formatear datos al esquema JSON solicitado
        $formattedData = [
            'oido_derecho' => [],
            'oido_izquierdo' => []
        ];
        
        $validFreqs = [125, 250, 500, 750, 1000, 1500, 2000, 3000, 4000, 6000, 8000];

        if (isset($data['right']) && is_iterable($data['right'])) {
            foreach ($data['right'] as $freq => $db) {
                if (in_array((int)$freq, $validFreqs) && $db !== null) {
                    $formattedData['oido_derecho'][] = [
                        'frecuencia_hz' => (int)$freq,
                        'umbral_db' => (int)$db
                    ];
                }
            }
        }

        if (isset($data['left']) && is_iterable($data['left'])) {
            foreach ($data['left'] as $freq => $db) {
                if (in_array((int)$freq, $validFreqs) && $db !== null) {
                    $formattedData['oido_izquierdo'][] = [
                        'frecuencia_hz' => (int)$freq,
                        'umbral_db' => (int)$db
                    ];
                }
            }
        }

        // Crear una nueva sesión con el JSON consolidado
        $session = $patient->sessions()->create([
            'type' => 'doctor',
            'audiometry_data' => $formattedData,
            'metadata' => [
                'initiated_by' => Auth::id(),
                'interface' => 'audiogram-canvas-v1',
                'timestamp' => now()->toDateTimeString(),
            ],
        ]);

        $this->selectedSessionId = $session->id;
        $this->dispatch('audiometry-saved');
        
        $this->updatedSelectedSessionId($session->id);

        Flux::toast(
            heading: 'Audiometría Guardada',
            text: 'Los resultados se han archivado correctamente en formato JSON.',
            variant: 'success'
        );
    }
}; ?>

<div class="audiometry-entry-root">
    <div class="space-y-6">
        {{-- Header informativo --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Evaluación de Audiometría Tonal</h2>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Ingreso de umbrales por conducción aérea.</p>
            </div>
            
            {{-- Selector de Historial --}}
            <div class="w-full md:w-72">
                <flux:field>
                    <flux:label icon="clock">Historial de Evaluaciones</flux:label>
                    <flux:select wire:model.live="selectedSessionId" placeholder="Seleccionar registro...">
                        <option value="new">➕ Nueva Evaluación (Vacía)</option>
                        <hr class="my-1 border-zinc-200">
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}">
                                📅 {{ $session->created_at->format('d/m/Y H:i') }}
                            </option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Panel Izquierdo: Datos Clínicos --}}
            <div class="lg:col-span-3 space-y-4">
                <div class="bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800">
                    <h3 class="text-xs font-bold text-zinc-400 uppercase tracking-widest mb-4">Contexto Clínico</h3>
                    
                    <div class="space-y-4">
                        <flux:field>
                            <flux:label>Exposición a ruido</flux:label>
                            <flux:select wire:model="noise_exposure" :disabled="$selectedSessionId !== 'new'">
                                <option value="no">Sin exposición</option>
                                <option value="si">Exposición laboral/recreativa</option>
                            </flux:select>
                        </flux:field>

                        <div class="space-y-2">
                            <flux:label>Síntomas reportados</flux:label>
                            <flux:checkbox wire:model="acufenos" label="Acúfenos / Tinnitus" :disabled="$selectedSessionId !== 'new'" />
                            <flux:checkbox wire:model="vertigos" label="Vértigos / Inestabilidad" :disabled="$selectedSessionId !== 'new'" />
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl border border-blue-100 dark:border-blue-800/50">
                    <h3 class="text-xs font-bold text-blue-700 dark:text-blue-400 uppercase mb-2">Instrucciones</h3>
                    <div class="text-xs text-blue-800 dark:text-blue-300 space-y-2">
                        @if($selectedSessionId === 'new')
                            <p>Estás en modo <b>Edición</b>:</p>
                            <ul class="list-disc pl-4 space-y-1">
                                <li>Seleccioná el oído.</li>
                                <li>Hacé clic para marcar umbral.</li>
                                <li>Clic de nuevo para borrar.</li>
                            </ul>
                        @else
                            <p>Estás viendo un <b>Registro Histórico</b>:</p>
                            <ul class="list-disc pl-4 space-y-1">
                                <li>El gráfico es de solo lectura.</li>
                                <li>Para una nueva evaluación, selecciona "Nueva Evaluación" en el desplegable de arriba.</li>
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Panel Derecho: El Audiograma --}}
            <div class="lg:col-span-9">
                @include('partials.audiogram-canvas')
            </div>
        </div>
    </div>
</div>