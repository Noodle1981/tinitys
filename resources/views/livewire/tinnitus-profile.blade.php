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
    public $sleep = 2;
    public $stress = 3;
    public $noise = 4;
    public $health = 3;
    public $alc = 1;

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

    public function mount($patientId = null)
    {
        $this->patientId = $patientId;

        // Cargar último mapping para correlación
        if ($patientId) {
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

        // Índice global (refleja condición general del paciente hoy)
        $raw = ($this->stress * 20) + ((5 - $this->sleep) * 20) + ($this->noise * 12) + ((5 - $this->health) * 12) + ($this->alc * 16);
        $this->index = min(100, round($raw * 100 / 300));

        // Índice por oído (mismo cálculo base + ajuste por frecuencia percibida)
        $freqBoost = ['Grave ~500Hz' => 0, 'Medio ~2kHz' => 4, 'Agudo ~4kHz' => 8, 'Muy agudo ~8kHz' => 10];
        $leftBoost = $freqBoost[$this->leftFreq] ?? 0;
        $rightBoost = $freqBoost[$this->rightFreq] ?? 0;

        $this->leftIndex  = min(100, $this->index + $leftBoost);
        $this->rightIndex = min(100, $this->index + $rightBoost);

        $this->leftEarVal  = $intensities[min(4, max(0, round(($this->stress + $this->noise) / 2) - 1))];
        $this->rightEarVal = $intensities[min(4, max(0, round(($this->stress + $this->sleep) / 2) - 2))];

        // Badge y recomendaciones (basadas en índice global)
        if ($this->index >= 70) {
            $this->statusBadge = ['color' => '#E24B4A', 'bg' => '#FCEBEB', 'text' => '#A32D2D', 'label' => 'Condición muy desfavorable', 'desc' => 'Alta probabilidad de resultados no confiables. Se recomienda reprogramar.'];
            $this->recommendations = [
                ['c' => '#E24B4A', 't' => 'Considerar reprogramar la audiometría para un día de mejor estado.'],
                ['c' => '#E24B4A', 't' => 'Si se procede, marcar todas las frecuencias como de baja confiabilidad.'],
                ['c' => '#BA7517', 't' => 'Iniciar con acufenometría antes de cualquier medición de umbral.'],
            ];
        } elseif ($this->index >= 45) {
            $this->statusBadge = ['color' => '#D85A30', 'bg' => '#FAECE7', 'text' => '#993C1D', 'label' => 'Condición desfavorable', 'desc' => 'El tinnitus puede interferir en las frecuencias seleccionadas.'];
            $this->recommendations = [
                ['c' => '#BA7517', 't' => 'Realizar acufenometría al inicio: mapear frecuencia e intensidad exacta del tinnitus.'],
                ['c' => '#BA7517', 't' => 'Usar tonos pulsados o modulados en lugar de tono continuo en las zonas afectadas.'],
                ['c' => '#639922', 't' => 'Registrar nivel de confianza diferenciado por frecuencia.'],
            ];
        } elseif ($this->index >= 25) {
            $this->statusBadge = ['color' => '#BA7517', 'bg' => '#FAEEDA', 'text' => '#854F0B', 'label' => 'Condición aceptable', 'desc' => 'Proceder con precaución. Documentar frecuencias de riesgo.'];
            $this->recommendations = [
                ['c' => '#639922', 't' => 'Proceder con protocolo estándar con atención extra en zonas afectadas.'],
                ['c' => '#639922', 't' => 'Ofrecer pausa entre frecuencias si el paciente siente que el tono se queda pegado.'],
            ];
        } else {
            $this->statusBadge = ['color' => '#1D9E75', 'bg' => '#E1F5EE', 'text' => '#0F6E56', 'label' => 'Condición favorable', 'desc' => 'Buenas condiciones para una audiometría confiable hoy.'];
            $this->recommendations = [
                ['c' => '#1D9E75', 't' => 'Condiciones óptimas. Proceder con protocolo estándar.'],
                ['c' => '#1D9E75', 't' => 'Registrar el perfil de tinnitus como línea de base para próximas sesiones.'],
            ];
        }
    }

    public function save()
    {
        if (!$this->patientId) return;

        TinnitusProfile::create([
            'patient_id'          => $this->patientId,
            'initiated_by'        => Auth::id(),
            'affected_ears'       => $this->affectedEars,
            'sleep_quality'       => $this->sleep,
            'stress_level'        => $this->stress,
            'noise_exposure'      => $this->noise,
            'health_state'        => $this->health,
            'alcohol_intake'      => $this->alc,
            'reliability_index'   => $this->index,
            'frequency_perception'=> $this->leftFreq, // global fallback
            'left_freq_selected'  => $this->leftFreq,
            'right_freq_selected' => $this->rightFreq,
            'left_index'          => $this->leftIndex,
            'right_index'         => $this->rightIndex,
            'left_ear_intensity'  => $this->leftEarVal,
            'right_ear_intensity' => $this->rightEarVal,
            'recommendations'     => $this->recommendations,
        ]);

        Flux::toast(
            heading: 'Perfil Guardado',
            text: 'El perfil de tinnitus de hoy ha sido archivado.',
            variant: 'success'
        );

        $this->dispatch('profile-saved');
    }
}; ?>

<div class="tinnitus-stage-1">
    <style>
        .stage-1-container { font-family: 'Inter', sans-serif; --color-background-primary: white; --color-background-secondary: #f8fafc; --color-border-tertiary: #e2e8f0; --color-text-primary: #1e293b; --color-text-secondary: #475569; --color-text-tertiary: #64748b; --border-radius-lg: 12px; --border-radius-md: 8px; }
        .screen { background: var(--color-background-secondary); border-radius: var(--border-radius-lg); padding: 1.25rem; margin-bottom: 1rem; border: 1px solid var(--color-border-tertiary); }
        .section-title { font-size: 11px; font-weight: 500; color: var(--color-text-tertiary); text-transform: uppercase; letter-spacing: 0.08em; margin: 0 0 10px; }
        .row { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .row label { font-size: 13px; color: var(--color-text-secondary); min-width: 140px; }
        .row input[type=range] { flex: 1; accent-color: #1D9E75; }
        .row .val { font-size: 13px; font-weight: 500; color: var(--color-text-primary); min-width: 28px; text-align: right; }
        .index-box { background: var(--color-background-primary); border: 1px solid var(--color-border-tertiary); border-radius: var(--border-radius-lg); padding: 1rem 1.25rem; display: flex; align-items: center; gap: 16px; margin-bottom: 1rem; }
        .index-num { font-size: 36px; font-weight: 500; min-width: 56px; text-align: center; }
        .index-label { font-size: 13px; color: var(--color-text-secondary); line-height: 1.5; }
        .badge { display: inline-block; font-size: 11px; padding: 2px 10px; border-radius: 20px; font-weight: 500; margin-bottom: 4px; }
        .freq-row { display: flex; gap: 8px; margin-bottom: 10px; }
        .freq-btn { flex: 1; font-size: 12px; padding: 6px 4px; border-radius: var(--border-radius-md); border: 1px solid var(--color-border-tertiary); background: var(--color-background-primary); color: var(--color-text-secondary); cursor: pointer; text-align: center; transition: all .15s; }
        .freq-btn.active { border-color: #5DCAA5; color: #0F6E56; background: #E1F5EE; font-weight: 500; }
        .ear-row { display: flex; gap: 8px; margin-bottom: 14px; }
        .ear-card { flex: 1; background: var(--color-background-primary); border: 1px solid var(--color-border-tertiary); border-radius: var(--border-radius-md); padding: 10px 12px; cursor: pointer; transition: all .2s; }
        .ear-card.active { border-color: #1D9E75; border-width: 2px; box-shadow: 0 0 0 1px #1D9E75; }
        .ear-label { font-size: 11px; color: var(--color-text-tertiary); margin-bottom: 4px; }
        .ear-val { font-size: 16px; font-weight: 500; color: var(--color-text-primary); }
        .ear-sub { font-size: 10px; color: var(--color-text-tertiary); margin-top: 4px; }
        .recommendation { background: var(--color-background-primary); border: 1px solid var(--color-border-tertiary); border-radius: var(--border-radius-lg); padding: 1rem 1.25rem; }
        .rec-item { display: flex; gap: 10px; align-items: flex-start; margin-bottom: 8px; font-size: 13px; color: var(--color-text-secondary); line-height: 1.5; }
        .rec-dot { width: 6px; height: 6px; border-radius: 50%; margin-top: 6px; flex-shrink: 0; }
        hr { border: none; border-top: 1px solid var(--color-border-tertiary); margin: 14px 0; }
        button.cta { width: 100%; padding: 12px; border-radius: var(--border-radius-md); border: none; font-size: 14px; font-weight: 600; cursor: pointer; margin-top: 12px; transition: opacity .2s; }
        .ear-selector { display: flex; gap: 4px; margin-bottom: 12px; border-bottom: 1px solid var(--color-border-tertiary); padding-bottom: 12px; }
        .ear-selector button { flex: 1; padding: 6px; font-size: 12px; border-radius: 6px; border: 1px solid var(--color-border-tertiary); background: #f1f5f9; color: #64748b; font-weight: 500; }
        .ear-selector button.active { background: #1D9E75; color: white; border-color: #1D9E75; }
    </style>

    <div class="stage-1-container">
        <p style="font-size:11px;color:var(--color-text-tertiary);margin:0 0 12px;text-transform:uppercase;letter-spacing:.06em">Etapa 1: Perfil Tinnitus Pre-audiometría</p>

        <div class="ear-selector">
            <button type="button" wire:click="setActiveEar('left')" class="{{ $activeEar === 'left' ? 'active' : '' }}">Oído Izquierdo</button>
            <button type="button" wire:click="setActiveEar('right')" class="{{ $activeEar === 'right' ? 'active' : '' }}">Oído Derecho</button>
        </div>

        <div class="screen">
            <p class="section-title">Resumen por oído (clic para configurar)</p>
            
            <div class="ear-row">
                <div class="ear-card {{ $activeEar === 'left' ? 'active' : '' }}" wire:click="setActiveEar('left')">
                    <div class="flex justify-between items-start">
                        <div class="ear-label">Oído izquierdo</div>
                        <div class="text-[10px] font-bold {{ $leftIndex >= 45 ? 'text-red-500' : 'text-green-600' }}">{{ $leftIndex }}/100</div>
                    </div>
                    <div class="ear-val">{{ $leftEarVal }}</div>
                    <div class="ear-sub">{{ $leftFreq }}</div>
                    @if($lastLeftMapping)
                        <div class="ear-sub italic text-[9px]">Último mapeo: {{ $lastLeftMapping }}</div>
                    @endif
                </div>

                <div class="ear-card {{ $activeEar === 'right' ? 'active' : '' }}" wire:click="setActiveEar('right')">
                    <div class="flex justify-between items-start">
                        <div class="ear-label">Oído derecho</div>
                        <div class="text-[10px] font-bold {{ $rightIndex >= 45 ? 'text-red-500' : 'text-green-600' }}">{{ $rightIndex }}/100</div>
                    </div>
                    <div class="ear-val">{{ $rightEarVal }}</div>
                    <div class="ear-sub">{{ $rightFreq }}</div>
                    @if($lastRightMapping)
                        <div class="ear-sub italic text-[9px]">Último mapeo: {{ $lastRightMapping }}</div>
                    @endif
                </div>
            </div>

            <hr>

            <p class="section-title">Configuración: {{ $activeEar === 'left' ? 'Oído Izquierdo' : 'Oído Derecho' }}</p>

            <p class="section-title" style="text-transform:none; font-size:12px; margin-top:10px">Frecuencia percibida en este oído</p>
            <div class="freq-row">
                <button type="button" wire:click="setFreq('{{ $activeEar }}', 'Grave ~500Hz')" class="freq-btn {{ ($activeEar === 'left' ? $leftFreq : $rightFreq) === 'Grave ~500Hz' ? 'active' : '' }}">Grave</button>
                <button type="button" wire:click="setFreq('{{ $activeEar }}', 'Medio ~2kHz')" class="freq-btn {{ ($activeEar === 'left' ? $leftFreq : $rightFreq) === 'Medio ~2kHz' ? 'active' : '' }}">Medio</button>
                <button type="button" wire:click="setFreq('{{ $activeEar }}', 'Agudo ~4kHz')" class="freq-btn {{ ($activeEar === 'left' ? $leftFreq : $rightFreq) === 'Agudo ~4kHz' ? 'active' : '' }}">Agudo</button>
                <button type="button" wire:click="setFreq('{{ $activeEar }}', 'Muy agudo ~8kHz')" class="freq-btn {{ ($activeEar === 'left' ? $leftFreq : $rightFreq) === 'Muy agudo ~8kHz' ? 'active' : '' }}">Muy agudo</button>
            </div>

            <p class="section-title" style="text-transform:none; font-size:12px; margin-top:20px">Factores sistémicos (aplica a ambos)</p>

            <div class="row">
                <label>Calidad de sueño</label>
                <input type="range" min="1" max="5" step="1" wire:model.live="sleep">
                <span class="val">{{ $sleep }}/5</span>
            </div>
            <div class="row">
                <label>Nivel de estrés</label>
                <input type="range" min="1" max="5" step="1" wire:model.live="stress">
                <span class="val">{{ $stress }}/5</span>
            </div>
            <div class="row">
                <label>Exposición a ruido</label>
                <input type="range" min="1" max="5" step="1" wire:model.live="noise">
                <span class="val">{{ $noise }}/5</span>
            </div>
            <div class="row">
                <label>Estado de salud</label>
                <input type="range" min="1" max="5" step="1" wire:model.live="health">
                <span class="val">{{ $health }}/5</span>
            </div>
            <div class="row">
                <label>Alcohol últimas 24hs</label>
                <input type="range" min="1" max="5" step="1" wire:model.live="alc">
                <span class="val">{{ $alc }}/5</span>
            </div>
        </div>

        <div class="index-box">
            <div class="index-num" @style(['color: ' . $statusBadge['color']])>{{ $index }}</div>
            <div class="index-label">
                <div class="badge" @style(['background: ' . $statusBadge['bg'], 'color: ' . $statusBadge['text']])>{{ $statusBadge['label'] }}</div>
                <div style="font-size:12px;margin-top:2px">{{ $statusBadge['desc'] }}</div>
            </div>
        </div>

        <div class="recommendation">
            <p class="section-title" style="margin-bottom:10px">Recomendaciones para el audiólogo</p>
            <div class="space-y-2">
                @foreach($recommendations as $rec)
                    <div class="rec-item">
                        <div class="rec-dot" @style(['background: ' . $rec['c']])></div>
                        <span>{{ $rec['t'] }}</span>
                    </div>
                @endforeach
            </div>
            
            <button wire:click="save" class="cta" style="background:#1D9E75; color:white">
                Guardar Perfil de Hoy
            </button>
        </div>
    </div>
</div>
