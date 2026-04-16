<?php

use Livewire\Volt\Component;
use App\Models\TinnitusMapping;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

new class extends Component
{
    public $patientId;
    public $ear = 'ambos'; // 'OI', 'OD', 'ambos'
    public $leftLayersConfig = [];
    public $rightLayersConfig = [];
    public $masterVol = 100;

    protected function defaultLayers(): array
    {
        return [
            ['id' => 'ranita', 'name' => 'Tono Agudo',           'desc' => 'pulsante rítmico',    'type' => 'pulse', 'freq' => 78, 'vol' => 55, 'speed' => 52,   'color' => '#1D9E75'],
            ['id' => 'viento', 'name' => 'Ruido de Banda Ancha', 'desc' => 'ruido continuo',       'type' => 'noise', 'freq' => 38, 'vol' => 42, 'speed' => null,  'color' => '#378ADD'],
            ['id' => 'tono',   'name' => 'Tono Grave',           'desc' => 'tono puro constante',  'type' => 'pure',  'freq' => 55, 'vol' => 50, 'speed' => null,  'color' => '#7F77DD'],
            ['id' => 'sube',   'name' => 'Tono Oscilante',       'desc' => 'oscilante lento',      'type' => 'sweep', 'freq' => 62, 'vol' => 40, 'speed' => 28,   'color' => '#BA7517'],
        ];
    }

    public function mount($patientId = null)
    {
        $this->patientId = $patientId;
        $this->leftLayersConfig  = $this->defaultLayers();
        $this->rightLayersConfig = $this->defaultLayers();
    }

    public function setEar($ear)
    {
        $this->ear = $ear;
    }

    // Llamado desde Alpine.js — recibe (scope, leftLayers, rightLayers, masterVolume)
    public function save($scope, $leftConfig, $rightConfig, $masterVolume)
    {
        if (!$this->patientId) return;

        TinnitusMapping::create([
            'patient_id'          => $this->patientId,
            'initiated_by'        => Auth::id(),
            'ear'                 => $scope,
            'left_layers_config'  => ($this->ear !== 'OD') ? $leftConfig  : null,
            'right_layers_config' => ($this->ear !== 'OI') ? $rightConfig : null,
            'master_volume'       => $masterVolume / 100,
        ]);

        Flux::toast(
            heading: 'Mapeo Guardado',
            text: 'El mapeo de tinnitus ha sido archivado correctamente.',
            variant: 'success'
        );

        $this->dispatch('mapping-saved');
    }
}; ?>

<div class="space-y-3" wire:ignore x-data="tinnitusMapper(@js($leftLayersConfig), @js($rightLayersConfig), @js($masterVol))" x-init="activeEar = 'left'">
    @include('partials.tinnitus-scripts')
    
    {{-- Header del Etapa --}}
    <div class="flex items-center justify-between px-1">
        <div class="flex items-center gap-2">
            <flux:icon.musical-note class="size-5 text-emerald-500" />
            <div>
                <h2 class="text-lg font-bold text-zinc-900 dark:text-white leading-tight">{{ __('Mapeador de Tinnitus') }}</h2>
                <p class="text-[9px] uppercase font-bold text-zinc-400 tracking-widest">{{ __('Etapa 2: Calibración') }}</p>
            </div>
        </div>
        <div x-show="initialized" class="flex gap-2">
            <flux:button variant="ghost" size="xs" icon="arrow-path" @click="resetScope()">
                {{ __('Reiniciar') }}
            </flux:button>
        </div>
    </div>

    {{-- Pantalla de Inicio: Selección de Alcance (Compacta) --}}
    <div x-show="!initialized" class="py-10 px-6 bg-zinc-50 dark:bg-zinc-900/50 border-2 border-dashed border-zinc-200 dark:border-zinc-800 rounded-xl text-center">
        <flux:heading size="md" class="mb-1">{{ __('¿Dónde percibe el Acúfeno?') }}</flux:heading>
        <flux:subheading size="xs" class="mb-6">{{ __('Inicie la síntesis de frecuencia.') }}</flux:subheading>
        
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <flux:button variant="outline" class="flex-1 max-w-xs border-blue-200 hover:bg-blue-50 dark:border-blue-900 h-14" @click="initApp('OI')">
                <flux:icon.speaker-wave class="mr-2 text-blue-500" />
                {{ __('Izquierdo') }}
            </flux:button>
            <flux:button variant="outline" class="flex-1 max-w-xs border-red-200 hover:bg-red-50 dark:border-red-900 h-14" @click="initApp('OD')">
                <flux:icon.speaker-wave class="mr-2 text-red-500" />
                {{ __('Derecho') }}
            </flux:button>
            <flux:button variant="primary" class="flex-1 max-w-xs bg-emerald-600 hover:bg-emerald-700 h-14" @click="initApp('ambos')">
                <flux:icon.arrow-path class="mr-2" />
                {{ __('Bilateral') }}
            </flux:button>
        </div>
    </div>

    {{-- Panel de Mapeo Activo --}}
    <div x-show="initialized" class="space-y-3" style="display:none">
        {{-- Tabs de Oído (Solo si es Bilateral) --}}
        <div x-show="evaluationScope === 'ambos'" class="flex p-0.5 bg-zinc-100 dark:bg-zinc-800 rounded-lg w-fit mx-auto">
            <button type="button" @click="activeEar = 'left'" 
                class="px-5 py-1 text-[10px] font-bold rounded-md transition-all"
                :class="activeEar === 'left' ? 'bg-white dark:bg-zinc-700 text-emerald-600 shadow-sm' : 'text-zinc-500 hover:text-zinc-700'">
                {{ __('Izquierdo') }}
            </button>
            <button type="button" @click="activeEar = 'right'" 
                class="px-5 py-1 text-[10px] font-bold rounded-md transition-all"
                :class="activeEar === 'right' ? 'bg-white dark:bg-zinc-700 text-emerald-600 shadow-sm' : 'text-zinc-500 hover:text-zinc-700'">
                {{ __('Derecho') }}
            </button>
        </div>

        {{-- Grid de 4 Capas (Compacto) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <template x-for="l in (activeEar === 'left' ? leftLayers : rightLayers)" :key="activeEar + '-' + l.id">
                <div class="p-3 transition-all duration-300 border-2 rounded-xl bg-white dark:bg-zinc-900" 
                    :class="(activeEar === 'left' ? activeLeftNodes[l.id] : activeRightNodes[l.id]) ? 'border-emerald-500 shadow-lg shadow-emerald-500/5' : 'border-zinc-100 dark:border-zinc-800'">
                    
                    {{-- Cabecera de Tarjeta --}}
                    <div class="flex items-center gap-2 mb-3 p-1.5 -m-1.5 rounded-lg cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                        @click="toggleLayer(activeEar, l.id)">
                        <div class="size-2.5 rounded-full shrink-0" :style="'background:'+l.color"></div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-[13px] font-bold text-zinc-900 dark:text-white truncate" x-text="l.name"></h3>
                            <p class="text-[9px] text-zinc-400 truncate uppercase tracking-tighter" x-text="l.desc"></p>
                        </div>
                        <div class="px-1.5 py-0.5 rounded text-[8px] font-black uppercase transition-colors"
                            :class="(activeEar === 'left' ? activeLeftNodes[l.id] : activeRightNodes[l.id]) 
                                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' 
                                : 'bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-600'">
                            <span x-text="(activeEar === 'left' ? activeLeftNodes[l.id] : activeRightNodes[l.id]) ? 'ON' : 'OFF'"></span>
                        </div>
                    </div>

                    {{-- Controles de Capa --}}
                    <div class="space-y-3 px-0.5 py-1">
                        <div class="flex flex-col">
                            <label class="flex justify-between text-[10px] font-semibold text-zinc-400 dark:text-zinc-500 mb-1.5 uppercase tracking-wide">
                                {{ __('Freq') }}
                                <span class="font-bold text-zinc-800 dark:text-zinc-200" x-text="fmtFreq(freqFromSlider(l.freq))"></span>
                            </label>
                            <input type="range" min="0" max="100" x-model="l.freq" class="w-full h-1 bg-zinc-100 dark:bg-zinc-800 rounded-lg appearance-none cursor-pointer accent-emerald-500" @input="updateFreq(activeEar, l.id, $event.target.value)">
                        </div>

                        <div class="flex flex-col">
                            <label class="flex justify-between text-[10px] font-semibold text-zinc-400 dark:text-zinc-500 mb-1.5 uppercase tracking-wide">
                                {{ __('Vol') }}
                                <span class="font-bold text-zinc-800 dark:text-zinc-200" x-text="l.vol + '%'"></span>
                            </label>
                            <input type="range" min="0" max="100" x-model="l.vol" class="w-full h-1 bg-zinc-100 dark:bg-zinc-800 rounded-lg appearance-none cursor-pointer accent-emerald-500" @input="updateVol(activeEar, l.id, $event.target.value)">
                        </div>

                        <template x-if="l.speed !== null">
                            <div class="flex flex-col">
                                <label class="flex justify-between text-[10px] font-semibold text-zinc-400 dark:text-zinc-500 mb-1.5 uppercase tracking-wide">
                                    <span x-text="l.type === 'pulse' ? 'Pulsos' : 'Velocidad'"></span>
                                    <span class="font-bold text-zinc-800 dark:text-zinc-200" x-text="spdFromSlider(l.speed) + ' Hz'"></span>
                                </label>
                                <input type="range" min="0" max="100" x-model="l.speed" class="w-full h-1 bg-zinc-100 dark:bg-zinc-800 rounded-lg appearance-none cursor-pointer accent-indigo-500" @input="updateSpeed(activeEar, l.id, $event.target.value)">
                            </div>
                        </template>
                    </div>

                    {{-- Visualizador de Onda --}}
                    <div class="mt-3 pt-2 border-t border-zinc-50 dark:border-zinc-800/50">
                        <canvas class="w-full h-8 block rounded-sm bg-zinc-50/50 dark:bg-zinc-800/20"
                                x-effect="startWaveAnim(activeEar, l, $el)">
                        </canvas>
                    </div>
                </div>
            </template>
        </div>

        {{-- Footer de Acciones --}}
        <div class="mt-4 pt-3 border-t border-zinc-200 dark:border-zinc-800 flex justify-end">
            <flux:button variant="primary" class="w-full md:w-auto px-8 bg-emerald-600 hover:bg-emerald-700 h-10 text-xs shadow-none" icon="check-badge" @click="saveProfile()">
                {{ __('Guardar Mapeo') }}
            </flux:button>
        </div>
    </div>
</div>
</div>
</div>
</div>
