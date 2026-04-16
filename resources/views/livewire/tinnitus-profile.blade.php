<?php

use Livewire\Volt\Component;
use App\Models\TinnitusProfile;
use App\Models\TinnitusMapping;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

new class extends Component
{
    public $patientId;

    // Oídos afectados en esta sesión
    public $affectedEars = 'ambos'; // 'OI', 'OD', 'ambos'

    // Oído activo en la UI (cuál está siendo configurado)
    public $activeEar = 'left';

    // Sliders globales (sistémicos, aplican a ambos oídos)
    public $sleep = 1;
    public $stress = 3;
    public $noise = 4;
    public $health = 1;
    
    // NUEVO: Cansancio físico
    public $fatigue = 1; 

    // NUEVO: Síntomas físicos (Checkboxes)
    public $alc = false;
    public $puna = false;
    public $cold = false;
    public $throat = false;

    // Frecuencia percibida por oído
    public $leftFreq = 'Medio ~2kHz';
    public $rightFreq = 'Medio ~2kHz';

    // Valores calculados
    public $index = 0;       // índice global
    public $leftIndex = 0;
    public $rightIndex = 0;
    public $leftEarVal = 'Moderado';
    public $rightEarVal = 'Leve';
    public $statusBadge = [];
    public $recommendations = [];

    // Último mapping por oído (para mostrar correlación)
    public $lastLeftMapping = null;
    public $lastRightMapping = null;

    public $patient = null;

    public function mount($patientId = null)
    {
        $this->patient_id = $patientId;
        if ($patientId) {
            $this->patient = \App\Models\Patient::find($patientId);
            
            // Cargar último mapping para correlación
            $leftM = TinnitusMapping::where('patient_id', $patientId)
                ->whereIn('ear', ['OI', 'ambos'])->latest()->first();
            $this->lastLeftMapping = $leftM ? $leftM->created_at->diffForHumans() : null;

            $rightM = TinnitusMapping::where('patient_id', $patientId)
                ->whereIn('ear', ['OD', 'ambos'])->latest()->first();
            $this->lastRightMapping = $rightM ? $rightM->created_at->diffForHumans() : null;
        }

        $this->calculate();
    }

    public function updated($property)
    {
        $this->calculate();
    }

    public function setAffectedEars($val) // 'OI', 'OD', 'ambos'
    {
        $this->affectedEars = $val;
        $this->activeEar = ($val === 'OD') ? 'right' : 'left';
        $this->calculate();
    }

    public function setActiveEar($ear) // 'left' o 'right'
    {
        $this->activeEar = $ear;
    }

    public function setFreq($ear, $val)
    {
        if ($ear === 'left') $this->leftFreq = $val;
        else $this->rightFreq = $val;
        $this->calculate();
    }

    protected function calculate()
    {
        $intensities = ['Mínimo', 'Leve', 'Moderado', 'Intenso', 'Muy intenso'];

        // 1. Factores Sistémicos (Sliders 1-5): Cada uno aporta hasta 10 pts. (Max: 50)
        $slidersPts = (($this->stress - 1) * 2.5)
                    + (($this->sleep - 1) * 2.5)
                    + (($this->noise - 1) * 2.5)
                    + (($this->health - 1) * 2.5)
                    + (($this->fatigue - 1) * 2.5);

        // 2. Síntomas Físicos (Checkboxes): Pesos específicos (Max: 50)
        $symptomsPts = ($this->alc ? 10 : 0)
                     + ($this->puna ? 10 : 0)
                     + ($this->throat ? 10 : 0)
                     + ($this->cold ? 20 : 0);

        $this->index = round($slidersPts + $symptomsPts);

        // Índice por oído (mismo cálculo base + ajuste por frecuencia percibida)
        $freqBoost = ['Grave ~500Hz' => 0, 'Medio ~2kHz' => 4, 'Agudo ~4kHz' => 8, 'Muy agudo ~8kHz' => 10];
        $leftBoost = $freqBoost[$this->leftFreq] ?? 0;
        $rightBoost = $freqBoost[$this->rightFreq] ?? 0;

        $this->leftIndex  = min(100, $this->index + $leftBoost);
        $this->rightIndex = min(100, $this->index + $rightBoost);

        $this->rightEarVal = $intensities[min(4, max(0, round(($this->stress + $this->sleep) / 2) - 1))];
    }

    public function save()
    {
        if (!$this->patient_id) return;

        TinnitusProfile::create([
            'patient_id'          => $this->patient_id,
            'initiated_by'        => Auth::id(),
            'affected_ears'       => $this->affectedEars,
            'sleep_quality'       => $this->sleep,
            'stress_level'        => $this->stress,
            'noise_exposure'      => $this->noise,
            'health_state'        => $this->health,
            'alcohol_intake'      => $this->alc,
            'fatigue_level'       => $this->fatigue,
            'has_puna'            => $this->puna,
            'has_cold'            => $this->cold,
            'has_throat_pain'     => $this->throat,
            'reliability_index'   => $this->index,
            'frequency_perception'=> $this->leftFreq, // global fallback
            'left_freq_selected'  => $this->leftFreq,
            'right_freq_selected' => $this->rightFreq,
            'left_index'          => $this->leftIndex,
            'right_index'         => $this->rightIndex,
            'left_ear_intensity'  => $this->leftEarVal,
            'right_ear_intensity' => $this->rightEarVal,
        ]);

        Flux::toast(
            heading: 'Perfil Guardado',
            text: 'El perfil de tinnitus de hoy ha sido archivado.',
            variant: 'success'
        );

        $this->dispatch('profile-saved');
    }
}; ?>

<div class="space-y-6">
    {{-- Header de Contexto --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-zinc-900 dark:text-white">{{ __('Perfil Diagnóstico de Tinnitus') }}</h2>
            <p class="text-xs text-zinc-500 mt-1">
                {{ __('Evaluación de confiabilidad para') }}: <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $patient?->name }}</span>
            </p>
        </div>
        <flux:badge color="zinc" variant="outline" size="sm" icon="clock">
            {{ now()->format('d/m/Y') }}
        </flux:badge>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start overflow-hidden">
        {{-- Columna Izquierda: Oídos y Resultado (lg:col-span-5) --}}
        <div class="lg:col-span-5 space-y-6">
            <flux:card class="p-5">
                <flux:heading size="sm" class="mb-4">{{ __('Configuración por Oído') }}</flux:heading>
                
                {{-- Selector de Oído --}}
                <div class="flex p-1 bg-zinc-100 dark:bg-zinc-800 rounded-lg mb-6">
                    <button type="button" wire:click="setActiveEar('left')" 
                        class="flex-1 py-1.5 text-xs font-bold rounded-md transition-all {{ $activeEar === 'left' ? 'bg-white dark:bg-zinc-700 text-indigo-600 shadow-sm' : 'text-zinc-500 hover:text-zinc-700' }}">
                        {{ __('Oído Izquierdo') }}
                    </button>
                    <button type="button" wire:click="setActiveEar('right')" 
                        class="flex-1 py-1.5 text-xs font-bold rounded-md transition-all {{ $activeEar === 'right' ? 'bg-white dark:bg-zinc-700 text-indigo-600 shadow-sm' : 'text-zinc-500 hover:text-zinc-700' }}">
                        {{ __('Oído Derecho') }}
                    </button>
                </div>

                {{-- Frecuencia Percibida --}}
                <div class="space-y-4">
                    <p class="text-[10px] uppercase font-bold text-zinc-400 tracking-widest">{{ __('Frecuencia Percibida') }}</p>
                    <flux:radio.group 
                        wire:model.live="{{ $activeEar === 'left' ? 'leftFreq' : 'rightFreq' }}" 
                        wire:key="freq-config-{{ $activeEar }}"
                        variant="segmented" 
                        size="sm"
                    >
                        <flux:radio value="Grave ~500Hz" label="Grave" />
                        <flux:radio value="Medio ~2kHz" label="Medio" />
                        <flux:radio value="Agudo ~4kHz" label="Agudo" />
                        <flux:radio value="Muy agudo ~8kHz" label="Extremo" />
                    </flux:radio.group>
                    
                    @php 
                        $currentLastMapping = $activeEar === 'left' ? $lastLeftMapping : $lastRightMapping;
                    @endphp
                    @if($currentLastMapping)
                        <div class="flex items-center gap-1.5 text-[10px] text-zinc-400 italic mt-2">
                            <flux:icon.clock variant="mini" class="size-3" />
                            {{ __('Último mapeo') }}: {{ $currentLastMapping }}
                        </div>
                    @endif
                </div>
            </flux:card>

            {{-- Resultado: Índice de Confiabilidad --}}
            <flux:card class="p-6 flex flex-col items-center justify-center text-center bg-zinc-50/50 dark:bg-zinc-900/50 border-dashed border-zinc-200 dark:border-zinc-700">
                <p class="text-[10px] uppercase font-bold text-zinc-400 tracking-widest mb-4">{{ __('Confiabilidad del Examen') }}</p>
                
                {{-- Gauge Circular con SVG --}}
                <div class="relative flex items-center justify-center mb-4" x-data>
                    <svg class="size-32 transform -rotate-90">
                        <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="8" fill="transparent" class="text-zinc-200 dark:text-zinc-800" />
                        <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="8" fill="transparent" 
                            stroke-dasharray="364.4" 
                            :style="{ strokeDashoffset: 364.4 - (364.4 * $wire.index / 100) + 'px' }"
                            class="transition-all duration-500 ease-out {{ $index >= 70 ? 'text-red-500' : ($index >= 45 ? 'text-orange-500' : ($index >= 25 ? 'text-amber-500' : 'text-emerald-500')) }}" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-3xl font-black text-zinc-900 dark:text-white leading-none">{{ $index }}</span>
                        <span class="text-[9px] font-bold text-zinc-400 mt-1">/ 100</span>
                    </div>
                </div>

                <flux:badge :color="$index >= 70 ? 'red' : ($index >= 45 ? 'orange' : ($index >= 25 ? 'amber' : 'green'))" size="lg" variant="subtle" class="font-bold">
                    {{ $index >= 70 ? 'Riesgo Crítico' : ($index >= 45 ? 'Desfavorable' : ($index >= 25 ? 'Aceptable' : 'Óptimo')) }}
                </flux:badge>
                
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-3 px-4 leading-relaxed">
                    {{ $index >= 45 ? __('Interferencia clínica probable. Proceder con extrema precaución.') : __('Condiciones favorables para una audiometría confiable.') }}
                </p>
            </flux:card>
        </div>

        {{-- Columna Derecha: Factores Sistémicos (lg:col-span-7) --}}
        <div class="lg:col-span-7 space-y-6">
            <flux:card class="p-6">
                <flux:heading size="sm" class="mb-6">{{ __('Factores Sistémicos y Estilo de Vida') }}</flux:heading>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">
                    <flux:field>
                        <flux:label class="flex justify-between">
                            {{ __('Calidad de Sueño') }}
                            <span class="text-xs font-bold text-indigo-600">{{ $sleep }}/5</span>
                        </flux:label>
                        <flux:input type="range" min="1" max="5" step="1" wire:model.live="sleep" class="mt-2" />
                    </flux:field>

                    <flux:field>
                        <flux:label class="flex justify-between">
                            {{ __('Nivel de Estrés') }}
                            <span class="text-xs font-bold text-indigo-600">{{ $stress }}/5</span>
                        </flux:label>
                        <flux:input type="range" min="1" max="5" step="1" wire:model.live="stress" class="mt-2" />
                    </flux:field>

                    <flux:field>
                        <flux:label class="flex justify-between">
                            {{ __('Exposición a Ruido') }}
                            <span class="text-xs font-bold text-indigo-600">{{ $noise }}/5</span>
                        </flux:label>
                        <flux:input type="range" min="1" max="5" step="1" wire:model.live="noise" class="mt-2" />
                    </flux:field>

                    <flux:field>
                        <flux:label class="flex justify-between">
                            {{ __('Cansancio Físico') }}
                            <span class="text-xs font-bold text-indigo-600">{{ $fatigue }}/5</span>
                        </flux:label>
                        <flux:input type="range" min="1" max="5" step="1" wire:model.live="fatigue" class="mt-2" />
                    </flux:field>
                    
                    <flux:field class="md:col-span-2">
                        <flux:label class="flex justify-between">
                            {{ __('Estado General de Salud') }}
                            <span class="text-xs font-bold text-indigo-600">{{ $health }}/5</span>
                        </flux:label>
                        <flux:input type="range" min="1" max="5" step="1" wire:model.live="health" class="mt-2" />
                    </flux:field>
                </div>

                <flux:separator class="my-8" />

                {{-- Síntomas Físicos --}}
                <p class="text-[10px] uppercase font-bold text-zinc-400 tracking-widest mb-4">{{ __('Sintomatología Física Actual') }}</p>
                <div class="grid grid-cols-2 gap-4">
                    <flux:checkbox wire:model.live="alc" label="Consumo de Alcohol" />
                    <flux:checkbox wire:model.live="puna" label="Sensación de Puna" />
                    <flux:checkbox wire:model.live="cold" label="Resfrío / Congestión" />
                    <flux:checkbox wire:model.live="throat" label="Dolor de Garganta" />
                </div>
            </flux:card>

            {{-- Botón de Guardado --}}
            <div class="flex justify-end gap-3 pt-4">
                <flux:button variant="ghost" wire:click="$refresh">{{ __('Restablecer') }}</flux:button>
                <flux:button variant="primary" icon="check-badge" class="px-8" 
                    x-data x-on:click="$flux.modal('confirm-save-profile').show()">
                    {{ __('Archivar Perfil Clínico') }}
                </flux:button>
            </div>
        </div>
    </div>

    {{-- Modal de Confirmación --}}
    <flux:modal name="confirm-save-profile" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Guardar Perfil de Tinnitus') }}</flux:heading>
                <flux:subheading>
                    {{ __('¿Deseas guardar definitivamente el perfil clínico de hoy en la base de datos del paciente?') }}
                </flux:subheading>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="save" x-on:click="$flux.modal('confirm-save-profile').close()">
                    {{ __('Sí, Guardar Perfil') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
