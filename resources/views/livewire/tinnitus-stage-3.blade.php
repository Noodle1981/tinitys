<?php

use Livewire\Volt\Component;
use App\Models\CalibrationResult;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public $patientId;
    public $results = [];

    public function mount($patientId = null)
    {
        $this->patientId = $patientId;
    }

    public function saveResults($calibrationData)
    {
        if (!$this->patientId) return;

        $sessionId = $this->getLastSessionId();
        
        // Create a session if none exists for today
        if (!$sessionId) {
            $session = \App\Models\PatientSession::create([
                'patient_id' => $this->patientId,
                'status' => 'active',
                'notes' => 'Sesión automática de calibración'
            ]);
            $sessionId = $session->id;
        }

        foreach ($calibrationData as $id => $data) {
            CalibrationResult::updateOrCreate(
                ['patient_session_id' => $sessionId, 'layer_id' => $id],
                [
                    'frequency_hz' => $data['freqHz'],
                    'threshold_vol_pct' => $data['tVol'],
                    'match_vol_pct' => $data['mVol'],
                    'db_sl' => $data['db'],
                ]
            );
        }

        $this->dispatch('results-saved');
    }

    protected function getLastSessionId()
    {
        // Simple helper to get a session context
        return \App\Models\PatientSession::where('patient_id', $this->patientId)->latest()->first()?->id;
    }
}; ?>

<div class="tinnitus-stage-3" x-data="tinnitusCalibrator(@js($patientId))">
    <style>
        .stage-3-container { font-family: 'Inter', sans-serif; --color-background-primary: white; --color-background-secondary: #f8fafc; --color-border-tertiary: #e2e8f0; --color-text-primary: #1e293b; --color-text-secondary: #475569; --color-text-tertiary: #64748b; --border-radius-lg: 12px; --border-radius-md: 8px; }
        .card { background: var(--color-background-primary); border: 1px solid var(--color-border-tertiary); border-radius: var(--border-radius-lg); padding: 1rem 1.25rem; margin-bottom: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .lh { display: flex; align-items: center; gap: 10px; }
        .ldot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
        .lname { font-size: 14px; font-weight: 600; color: var(--color-text-primary); flex: 1; }
        .ldesc { font-size: 11px; color: var(--color-text-tertiary); }
        .badge { display: inline-block; font-size: 11px; padding: 2px 10px; border-radius: 20px; font-weight: 500; }
        .srow { display: flex; align-items: center; gap: 8px; margin-top: 10px; flex-wrap: wrap; }
        .ph-bar { display: flex; gap: 6px; margin-bottom: 16px; }
        .ph { height: 4px; flex: 1; border-radius: 2px; background: var(--color-border-tertiary); }
        .ph.act { background: #5DCAA5; }
        .ph.done { background: #1D9E75; }
        .instruct { font-size: 13px; color: var(--color-text-secondary); line-height: 1.65; background: var(--color-background-secondary); border-radius: var(--border-radius-md); padding: 12px 14px; margin-bottom: 16px; }
        .big-num { font-size: 42px; font-weight: 500; color: var(--color-text-primary); text-align: center; margin-bottom: 4px; }
        .rcards { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 12px; }
        .rcard { background: var(--color-background-secondary); border-radius: var(--border-radius-md); padding: 10px 12px; text-align: center; border: 1px solid var(--color-border-tertiary); }
        .rnum { font-size: 22px; font-weight: 600; }
        .rlabel { font-size: 11px; color: var(--color-text-tertiary); margin-top: 2px; }
        .threshold-mark { height: 18px; display: flex; align-items: center; margin-bottom: 6px; }
        .tmark-line { height: 2px; background: #cbd5e1; position: relative; flex: 1; }
        .tmark-label { font-size: 11px; color: var(--color-text-tertiary); white-space: nowrap; padding: 0 6px; }
    </style>

    <div class="stage-3-container">
        <p style="font-size:11px;color:var(--color-text-tertiary);margin:0 0 12px;text-transform:uppercase;letter-spacing:.06em">Etapa 3: Calibrador de Tinnitus dB SL</p>

        <div x-show="view === 'start'">
            <div style="text-align:center;padding:1.5rem 0.5rem">
                <p style="font-size:14px;font-weight:600;color:var(--color-text-primary);margin-bottom:6px">Calibrador de Tinnitus</p>
                <p style="font-size:13px;color:var(--color-text-tertiary);line-height:1.65;margin-bottom:8px">Usá auriculares. Cada capa se mide en 2 pasos:</p>
                <div style="display:flex;justify-content:center;gap:20px;margin-bottom:20px;font-size:12px;color:var(--color-text-secondary)">
                    <span style="display:flex;align-items:center;gap:6px"><span style="width:18px;height:18px;border-radius:50%;background:#E1F5EE;color:#0F6E56;font-size:10px;font-weight:600;display:flex;align-items:center;justify-content:center">1</span> Umbral</span>
                    <span style="display:flex;align-items:center;gap:6px"><span style="width:18px;height:18px;border-radius:50%;background:#E1F5EE;color:#0F6E56;font-size:10px;font-weight:600;display:flex;align-items:center;justify-content:center">2</span> Match</span>
                </div>
                <p style="font-size:12px;color:var(--color-text-tertiary);margin-bottom:20px">El resultado es en <strong>dB SL</strong> — medida de intensidad real.</p>
                <button @click="initApp()" class="px-8 py-3 bg-[#1D9E75] text-white rounded-lg font-bold">Iniciar Calibración</button>
            </div>
        </div>

        <div x-show="view === 'main'" style="display:none">
            <p style="font-size:11px;color:var(--color-text-tertiary);text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px">Capas por calibrar</p>
            <div class="layers-grid">
                <template x-for="l in layers" :key="l.id">
                    <div class="card">
                        <div class="lh">
                            <div class="ldot" :style="'background:'+l.color"></div>
                            <div class="lname" x-text="l.name"></div>
                            <div class="ldesc" x-text="l.desc"></div>
                        </div>
                        <div class="srow">
                            <template x-if="res[l.id]">
                                <span class="badge" :style="'background:'+l.color+'22;color:'+l.color" x-text="fmtF(res[l.id].freqHz) + ' — ' + res[l.id].db + ' dB SL'"></span>
                            </template>
                            <template x-if="!res[l.id]">
                                <span class="badge" style="background:#f1f5f9;color:#64748b">Sin calibrar</span>
                            </template>
                            <div style="flex:1"></div>
                            <button @click="startCalib(l.id)" class="px-4 py-1.5 rounded-full border text-xs font-semibold" :style="'border-color:'+l.color+';background:'+l.color+'11;color:'+l.color" x-text="res[l.id] ? 'Recalibrar' : 'Calibrar'"></button>
                        </div>
                    </div>
                </template>
            </div>
            <div class="grid grid-cols-2 gap-2 mt-4">
                <button @click="showSummary()" :disabled="Object.keys(res).length === 0" class="p-2.5 rounded-lg border text-sm font-semibold text-slate-700 disabled:opacity-40">Ver Resumen</button>
                <button @click="saveResults()" :disabled="Object.keys(res).length === 0" class="p-2.5 rounded-lg bg-[#1D9E75] text-white text-sm font-semibold disabled:opacity-40">Guardar Todo</button>
            </div>
        </div>

        <div x-show="view === 'calibrate'" style="display:none">
            <div class="flex items-center gap-2 mb-4">
                <div class="ldot" :style="'background:'+currentLayer?.color"></div>
                <span class="text-sm font-semibold text-slate-800 flex-1" x-text="currentLayer?.name"></span>
                <span class="text-xs text-slate-400" x-text="currentLayer ? fmtF(ff(currentLayer.freq)) : ''"></span>
                <button @click="cancelCalib()" class="text-xs text-slate-400">Cancelar</button>
            </div>
            <div class="ph-bar">
                <div class="ph" :class="cs.phase === 1 ? 'act' : 'done'"></div>
                <div class="ph" :class="cs.phase === 2 ? 'act' : ''"></div>
            </div>
            <div class="text-sm font-semibold text-slate-900 mb-2" x-text="cs.phase === 1 ? 'Fase 1 — Encontrá tu umbral' : 'Fase 2 — Igualá tu tinnitus'"></div>
            <div class="instruct" x-html="cs.phase === 1 ? 'El sonido está muy suave. Subí el slider despacio hasta escuchar el tono por primera vez. Ese volumen es tu <strong>umbral auditivo</strong>.' : 'Ahora subí el volumen hasta que el sonido sea igual a tu tinnitus.'"></div>
            
            <div x-show="cs.phase === 2" class="threshold-mark">
                <div class="tmark-label">Umbral</div>
                <div class="tmark-line"></div>
                <div class="tmark-label" x-text="cs.tVol + '%'"></div>
            </div>
            
            <div class="big-num"><span x-text="cs.cVol"></span><span class="text-lg text-slate-400 font-normal">%</span></div>
            <p class="text-center text-xs text-slate-500 mb-2" x-text="cs.phase === 1 ? 'Subí hasta apenas escuchar el tono' : dbHint(cs.tVol, cs.cVol)"></p>
            
            <input type="range" min="1" max="100" x-model="cs.cVol" @input="onSlider($event.target.value)" class="w-full mb-6 accent-[#1D9E75]">
            
            <button @click="cs.phase === 1 ? markThresh() : markMatch()" class="w-full py-3 rounded-lg bg-[#1D9E75] text-white font-semibold text-sm" x-text="cs.phase === 1 ? 'Marcar umbral — lo escucho' : 'Marcar intensidad — se igualó'"></button>
        </div>

        <div x-show="view === 'summary'" style="display:none">
            <div class="flex items-center gap-2 mb-4">
                <p class="text-sm font-semibold text-slate-800 flex-1">Resultados dB SL (Afinación)</p>
                <button @click="view = 'main'" class="text-xs text-slate-400">Volver</button>
            </div>
            <div class="rcards">
                <template x-for="l in layers.filter(x => res[x.id])" :key="l.id">
                    <div class="rcard" :style="'border-color:'+l.color+'55 text-balance'">
                        <div class="ldot mx-auto mb-2" :style="'background:'+l.color"></div>
                        <div class="text-xs font-semibold text-slate-800 mb-1" x-text="l.name"></div>
                        <div class="rnum" :style="'color:'+l.color"><span x-text="res[l.id].db"></span><span class="text-xs"> dB SL</span></div>
                        <div class="rlabel" x-text="fmtF(res[l.id].freqHz)"></div>
                    </div>
                </template>
            </div>
            <button @click="saveResults()" class="w-full py-2.5 rounded-lg bg-[#1D9E75] text-white text-sm font-semibold shadow-md">Guardar para Seguimiento</button>
        </div>
    </div>
</div>
