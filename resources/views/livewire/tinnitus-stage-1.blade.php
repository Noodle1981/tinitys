<?php

use Livewire\Volt\Component;
use App\Models\TinnitusProfile;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public $patientId;
    public $sleep = 2;
    public $stress = 3;
    public $noise = 4;
    public $health = 3;
    public $alc = 1;
    public $freqSelected = 'Medio ~2kHz';

    public $index = 0;
    public $statusBadge = [
        'color' => '#1D9E75',
        'bg' => '#E1F5EE',
        'text' => '#0F6E56',
        'label' => 'Cargando...',
        'desc' => 'Calculando índice...'
    ];
    public $recommendations = [];
    public $leftEarVal = 'Moderado';
    public $rightEarVal = 'Leve';

    public function mount($patientId = null)
    {
        $this->patientId = $patientId;
        $this->calculate();
    }

    public function updated($property)
    {
        $this->calculate();
    }

    public function setFreq($val)
    {
        $this->freqSelected = $val;
        $this->calculate();
    }

    protected function calculate()
    {
        $raw = ($this->stress * 20) + ((5 - $this->sleep) * 20) + ($this->noise * 12) + ((5 - $this->health) * 12) + ($this->alc * 16);
        $this->index = min(100, round($raw * 100 / 300));

        $intensities = ['Mínimo', 'Leve', 'Moderado', 'Intenso', 'Muy intenso'];
        $this->leftEarVal = $intensities[min(4, max(0, round(($this->stress + $this->noise) / 2) - 1))];
        $this->rightEarVal = $intensities[min(4, max(0, round(($this->stress + $this->sleep) / 2) - 2))];

        if ($this->index >= 70) {
            $this->statusBadge = ['color' => '#E24B4A', 'bg' => '#FCEBEB', 'text' => '#A32D2D', 'label' => 'Condición muy desfavorable', 'desc' => 'Alta probabilidad de resultados no confiables. Se recomienda reprogramar.'];
            $this->recommendations = [
                ['c' => '#E24B4A', 't' => 'Considerar reprogramar la audiometría para un día de mejor estado.'],
                ['c' => '#E24B4A', 't' => 'Si se procede, marcar todas las frecuencias como de baja confiabilidad.'],
                ['c' => '#BA7517', 't' => 'Iniciar con acufenometría antes de cualquier medición de umbral.'],
                ['c' => '#BA7517', 't' => 'Usar protocolo descendente (de audible hacia abajo) en lugar del ascendente estándar.'],
            ];
        } else if ($this->index >= 45) {
            $this->statusBadge = ['color' => '#D85A30', 'bg' => '#FAECE7', 'text' => '#993C1D', 'label' => 'Condición desfavorable', 'desc' => 'El tinnitus puede interferir en frecuencias cercanas a ' . $this->freqSelected . '.'];
            $this->recommendations = [
                ['c' => '#BA7517', 't' => 'Realizar acufenometría al inicio: mapear frecuencia e intensidad exacta del tinnitus.'],
                ['c' => '#BA7517', 't' => 'En zona de frecuencia ' . $this->freqSelected . ': usar tonos pulsados o modulados en lugar de tono continuo.'],
                ['c' => '#BA7517', 't' => 'Protocolo descendente en las frecuencias cercanas al tinnitus reportado.'],
                ['c' => '#639922', 't' => 'Registrar las mediciones con nivel de confianza diferenciado por frecuencia.'],
            ];
        } else if ($this->index >= 25) {
            $this->statusBadge = ['color' => '#BA7517', 'bg' => '#FAEEDA', 'text' => '#854F0B', 'label' => 'Condición aceptable', 'desc' => 'Proceder con precaución. Documentar las frecuencias de riesgo.'];
            $this->recommendations = [
                ['c' => '#639922', 't' => 'Proceder con protocolo estándar pero con atención extra en zona ' . $this->freqSelected . '.'],
                ['c' => '#639922', 't' => 'Ofrecer pausa entre frecuencias si el paciente siente que el tono "se queda pegado".'],
                ['c' => '#639922', 't' => 'Documentar en el audiograma las frecuencias potencialmente afectadas.'],
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
            'patient_id' => $this->patientId,
            'initiated_by' => Auth::id(),
            'sleep_quality' => $this->sleep,
            'stress_level' => $this->stress,
            'noise_exposure' => $this->noise,
            'health_state' => $this->health,
            'alcohol_intake' => $this->alc,
            'reliability_index' => $this->index,
            'frequency_perception' => $this->freqSelected,
            'left_ear_intensity' => $this->leftEarVal,
            'right_ear_intensity' => $this->rightEarVal,
            'recommendations' => $this->recommendations,
        ]);

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
        .ear-card { flex: 1; background: var(--color-background-primary); border: 1px solid var(--color-border-tertiary); border-radius: var(--border-radius-md); padding: 10px 12px; }
        .ear-label { font-size: 11px; color: var(--color-text-tertiary); margin-bottom: 4px; }
        .ear-val { font-size: 16px; font-weight: 500; color: var(--color-text-primary); }
        .recommendation { background: var(--color-background-primary); border: 1px solid var(--color-border-tertiary); border-radius: var(--border-radius-lg); padding: 1rem 1.25rem; }
        .rec-item { display: flex; gap: 10px; align-items: flex-start; margin-bottom: 8px; font-size: 13px; color: var(--color-text-secondary); line-height: 1.5; }
        .rec-dot { width: 6px; height: 6px; border-radius: 50%; margin-top: 6px; flex-shrink: 0; }
        hr { border: none; border-top: 1px solid var(--color-border-tertiary); margin: 14px 0; }
        button.cta { width: 100%; padding: 12px; border-radius: var(--border-radius-md); border: none; font-size: 14px; font-weight: 600; cursor: pointer; margin-top: 12px; transition: opacity .2s; }
    </style>

    <div class="stage-1-container">
        <p style="font-size:11px;color:var(--color-text-tertiary);margin:0 0 12px;text-transform:uppercase;letter-spacing:.06em">Etapa 1: Perfil Tinnitus Pre-audiometría</p>

        <div class="screen">
            <p class="section-title">Estado del tinnitus hoy</p>
            
            <div class="ear-row">
                <div class="ear-card">
                    <div class="ear-label">Oído izquierdo</div>
                    <div class="ear-val">{{ $leftEarVal }}</div>
                </div>
                <div class="ear-card">
                    <div class="ear-label">Oído derecho</div>
                    <div class="ear-val">{{ $rightEarVal }}</div>
                </div>
            </div>

            <p class="section-title">Frecuencia percibida</p>
            <div class="freq-row">
                <button type="button" wire:click="setFreq('Grave ~500Hz')" class="freq-btn {{ $freqSelected === 'Grave ~500Hz' ? 'active' : '' }}">Grave</button>
                <button type="button" wire:click="setFreq('Medio ~2kHz')" class="freq-btn {{ $freqSelected === 'Medio ~2kHz' ? 'active' : '' }}">Medio</button>
                <button type="button" wire:click="setFreq('Agudo ~4kHz')" class="freq-btn {{ $freqSelected === 'Agudo ~4kHz' ? 'active' : '' }}">Agudo</button>
                <button type="button" wire:click="setFreq('Muy agudo ~8kHz')" class="freq-btn {{ $freqSelected === 'Muy agudo ~8kHz' ? 'active' : '' }}">Muy agudo</button>
            </div>

            <hr>
            <p class="section-title">Factores de contexto</p>

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
