<?php

use Livewire\Volt\Component;
use App\Models\Patient;
use App\Models\PatientSession;
use App\Models\AudiometryValue;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public $patientId;
    public $noise_exposure = 'no';
    public $acufenos = false;
    public $vertigos = false;
    public $initialData = ['right' => [], 'left' => []];

    public function mount($patientId)
    {
        $this->patientId = $patientId;
        $patient = Patient::findOrFail($patientId);
        
        // Cargar últimos datos clínicos
        $this->noise_exposure = $patient->noise_exposure ? 'si' : 'no';
        $this->acufenos = (bool)$patient->tinnitus_symptom;
        $this->vertigos = (bool)$patient->vertigo_symptom;

        // Cargar datos del último audiograma para facilitar la edición
        $latestSession = $patient->sessions()
            ->where('type', 'doctor')
            ->latest()
            ->with(['audiometryValues' => function($q) {
                $q->where('type', 'air');
            }])
            ->first();

        if ($latestSession) {
            foreach ($latestSession->audiometryValues as $val) {
                $ear = ($val->ear === 'OD' || $val->ear === 'right') ? 'right' : 'left';
                $this->initialData[$ear][$val->frequency] = $val->db_level;
            }
        }
    }

    public function save($data)
    {
        $patient = Patient::findOrFail($this->patientId);

        // Actualizar perfil clínico del paciente
        $patient->update([
            'noise_exposure' => $this->noise_exposure == 'si',
            'tinnitus_symptom' => $this->acufenos,
            'vertigo_symptom' => $this->vertigos,
        ]);

        // Crear una nueva sesión (para archivado histórico)
        $session = $patient->sessions()->create([
            'type' => 'doctor',
            'initiated_by' => Auth::id(),
            'metadata' => [
                'interface' => 'audiogram-canvas-v1',
                'timestamp' => now()->toDateTimeString(),
            ],
        ]);

        // Guardar Oído Derecho (OD)
        if (isset($data['right'])) {
            foreach ($data['right'] as $freq => $db) {
                $session->audiometryValues()->create([
                    'ear' => 'OD', 
                    'frequency' => (int)$freq, 
                    'type' => 'air', 
                    'db_level' => (int)$db
                ]);
            }
        }

        // Guardar Oído Izquierdo (OI)
        if (isset($data['left'])) {
            foreach ($data['left'] as $freq => $db) {
                $session->audiometryValues()->create([
                    'ear' => 'OI', 
                    'frequency' => (int)$freq, 
                    'type' => 'air', 
                    'db_level' => (int)$db
                ]);
            }
        }

        $this->dispatch('audiometry-saved');
        flux()->toast(
            heading: 'Audiometría Guardada',
            text: 'Los resultados se han archivado en la ficha del paciente.',
            variant: 'success'
        );
    }
}; ?>

<div class="audiometry-entry-root">
    <div class="space-y-6">
        {{-- Header informativo --}}
        <div>
            <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Evaluación de Audiometría Tonal</h2>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Ingreso de umbrales por conducción aérea. Cada guardado genera un nuevo registro histórico.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Panel Izquierdo: Datos Clínicos --}}
            <div class="lg:col-span-3 space-y-4">
                <div class="bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800">
                    <h3 class="text-xs font-bold text-zinc-400 uppercase tracking-widest mb-4">Contexto Clínico</h3>
                    
                    <div class="space-y-4">
                        <flux:field>
                            <flux:label>Exposición a ruido</flux:label>
                            <flux:select wire:model="noise_exposure">
                                <option value="no">Sin exposición</option>
                                <option value="si">Exposición laboral/recreativa</option>
                            </flux:select>
                        </flux:field>

                        <div class="space-y-2">
                            <flux:label>Síntomas reportados</flux:label>
                            <flux:checkbox wire:model="acufenos" label="Acúfenos / Tinnitus" />
                            <flux:checkbox wire:model="vertigos" label="Vértigos / Inestabilidad" />
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl border border-blue-100 dark:border-blue-800/50">
                    <h3 class="text-xs font-bold text-blue-700 dark:text-blue-400 uppercase mb-2">Instrucciones</h3>
                    <ul class="text-xs text-blue-800 dark:text-blue-300 space-y-1.5 list-disc pl-4">
                        <li>Seleccioná el oído <b>Derecho</b> o <b>Izquierdo</b>.</li>
                        <li>Hacé clic en la grilla para marcar el umbral.</li>
                        <li>Clic en un punto existente para eliminarlo.</li>
                        <li>Los puntos se autoconectan por frecuencia.</li>
                    </ul>
                </div>
            </div>

            {{-- Panel Derecho: El Audiograma --}}
            <div class="lg:col-span-9">
                @include('partials.audiogram-canvas')
            </div>
        </div>
    </div>
</div>