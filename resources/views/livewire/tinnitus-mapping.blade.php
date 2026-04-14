<?php

use Livewire\Volt\Component;
use App\Models\TinnitusMapping;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public $patientId;
    public $layersConfig = [];
    public $masterVol = 65;

    public function mount($patientId = null)
    {
        $this->patientId = $patientId;
        // Initialize default layers if none provided
        $this->layersConfig = [
            ['id' => 'ranita', 'name' => 'Tono Agudo', 'desc' => 'pulsante rítmico', 'type' => 'pulse', 'freq' => 78, 'vol' => 55, 'speed' => 52, 'color' => '#1D9E75'],
            ['id' => 'viento', 'name' => 'Ruido de Banda Ancha', 'desc' => 'ruido continuo', 'type' => 'noise', 'freq' => 38, 'vol' => 42, 'speed' => null, 'color' => '#378ADD'],
            ['id' => 'tono', 'name' => 'Tono Grave', 'desc' => 'tono puro constante', 'type' => 'pure', 'freq' => 55, 'vol' => 50, 'speed' => null, 'color' => '#7F77DD'],
            ['id' => 'sube', 'name' => 'Tono Oscilante', 'desc' => 'oscilante lento', 'type' => 'sweep', 'freq' => 62, 'vol' => 40, 'speed' => 28, 'color' => '#BA7517'],
        ];
    }

    public function save($config, $masterVolume)
    {
        if (!$this->patientId) return;

        TinnitusMapping::create([
            'patient_id' => $this->patientId,
            'initiated_by' => Auth::id(),
            'layers_config' => $config,
            'master_volume' => $masterVolume / 100,
        ]);

        $this->dispatch('mapping-saved');
    }
}; ?>
<div class="tinnitus-stage-2" wire:ignore x-data="tinnitusMapper(@js($layersConfig), @js($masterVol))">
    @include('partials.tinnitus-scripts')
    <style>
        .stage-2-container { font-family: 'Inter', sans-serif; --color-background-primary: white; --color-background-secondary: #f8fafc; --color-border-tertiary: #e2e8f0; --color-text-primary: #1e293b; --color-text-secondary: #475569; --color-text-tertiary: #64748b; --border-radius-lg: 12px; --border-radius-md: 8px; }
        .card { background: var(--color-background-primary); border: 1px solid var(--color-border-tertiary); border-radius: var(--border-radius-lg); padding: 1rem 1.25rem; margin-bottom: 10px; transition: border-color .2s; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .card.is-on { border-color: #1D9E75; border-width: 2px; }
        .lh { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
        .ldot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
        .lname { font-size: 14px; font-weight: 600; color: var(--color-text-primary); flex: 1; display: flex; align-items: center; gap: 6px; }
        .ldesc { font-size: 11px; color: var(--color-text-tertiary); }
        .cr { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; }
        .cr label { font-size: 12px; color: var(--color-text-secondary); width: 85px; flex-shrink: 0; }
        .cr input[type=range] { flex: 1; accent-color: #1D9E75; }
        .cr .v { font-size: 12px; font-weight: 500; color: var(--color-text-primary); width: 65px; text-align: right; white-space: nowrap; }
        .onbtn { padding: 4px 14px; border-radius: 20px; border: 1px solid var(--color-border-tertiary); background: transparent; font-size: 12px; cursor: pointer; color: var(--color-text-tertiary); transition: all .15s; font-weight: 600; }
        .hint { font-size: 12px; color: var(--color-text-tertiary); line-height: 1.5; background: var(--color-background-secondary); border-radius: var(--border-radius-md); padding: 8px 12px; margin-bottom: 12px; border: 1px solid var(--color-border-tertiary); }
    </style>

    <div class="stage-2-container">
        <p style="font-size:11px;color:var(--color-text-tertiary);margin:0 0 12px;text-transform:uppercase;letter-spacing:.06em">Etapa 2: Mapeador de Tinnitus Multicapa</p>

        <div x-show="!initialized" class="text-center p-8 bg-slate-50 rounded-xl border-2 border-dashed border-slate-200">
            <p class="text-slate-600 font-medium mb-4">Usá auriculares. Activá cada capa y ajustá hasta que coincida con lo que escuchás.</p>
            <button @click="initApp()" class="px-8 py-3 bg-[#1D9E75] text-white rounded-lg font-bold shadow-sm">Iniciar Mapeador</button>
        </div>

        <div x-show="initialized" style="display:none">
            <div class="hint">Activá cada capa con el botón ON/OFF. Ajustá frecuencia y volumen hasta que suene igual a tu tinnitus.</div>
            
            <div class="layers-list">
                <template x-for="(l, index) in layers" :key="l.id">
                    <div class="card" :class="activeNodes[l.id] ? 'is-on' : ''">
                        <div class="lh">
                            <div class="ldot" :style="'background:'+l.color"></div>
                            <div class="lname">
                                <template x-if="l.id === 'ranita'"><flux:icon.sparkles variant="mini" class="size-4 opacity-70" /></template>
                                <template x-if="l.id === 'viento'"><flux:icon.cloud variant="mini" class="size-4 opacity-70" /></template>
                                <template x-if="l.id === 'tono'"><flux:icon.bolt variant="mini" class="size-4 opacity-70" /></template>
                                <template x-if="l.id === 'sube'"><flux:icon.presentation-chart-line variant="mini" class="size-4 opacity-70" /></template>
                                <span x-text="l.name"></span>
                            </div>
                            <div class="ldesc" x-text="l.desc"></div>
                            <button @click="toggleLayer(l.id)" class="onbtn" 
                                    :style="activeNodes[l.id] ? 'background:'+l.color+'11; color:'+l.color+'; border-color:'+l.color : ''"
                                    x-text="activeNodes[l.id] ? 'ON' : 'OFF'"></button>
                        </div>
                        
                        <div class="cr">
                            <label>Frecuencia</label>
                            <input type="range" min="0" max="100" x-model="l.freq" @input="updateFreq(l.id, $event.target.value)">
                            <span class="v" x-text="fmtFreq(freqFromSlider(l.freq))"></span>
                        </div>

                        <div class="cr">
                            <label>Volumen</label>
                            <input type="range" min="0" max="100" x-model="l.vol" @input="updateVol(l.id, $event.target.value)">
                            <span class="v" x-text="l.vol + '%'"></span>
                        </div>

                        <template x-if="l.speed !== null">
                            <div class="cr">
                                <label x-text="l.type === 'pulse' ? 'Pulsos/seg' : 'Velocidad'"></label>
                                <input type="range" min="0" max="100" x-model="l.speed" @input="updateSpeed(l.id, $event.target.value)">
                                <span class="v" x-text="spdFromSlider(l.speed) + ' Hz'"></span>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <div class="bg-slate-100 p-4 rounded-lg flex items-center gap-4 mb-4">
                <span class="text-xs font-bold text-slate-500 uppercase w-20">Master</span>
                <input type="range" min="10" max="100" x-model="masterVol" @input="setMasterVol($event.target.value)" class="flex-1 accent-slate-800">
                <span class="text-sm font-bold w-12 text-right" x-text="masterVol + '%'"></span>
            </div>

            <button @click="saveProfile()" class="w-full py-3 bg-[#1D9E75] text-white rounded-lg font-bold shadow-md hover:bg-[#158060] transition-colors">
                Guardar Configuración y Ver Audiometría ↗
            </button>
        </div>
    </div>
</div>
