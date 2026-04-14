<?php

use Livewire\Volt\Component;
use App\Models\PatientSession;
use App\Models\CalibrationResult;

new class extends Component
{
    public $sessionId;
    public $results = [];

    public function mount($sessionId = null)
    {
        $this->sessionId = $sessionId;
    }

    public function saveResults($calibrationData)
    {
        if (!$this->sessionId) return;

        foreach ($calibrationData as $id => $data) {
            CalibrationResult::updateOrCreate(
                ['patient_session_id' => $this->sessionId, 'layer_id' => $id],
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
}; ?>

<div x-data="tinnitusCalibrator(@js($sessionId))" class="calibrator-container">
    <style>
        .calibrator-container { font-family: 'Inter', sans-serif; --color-background-primary: white; --color-background-secondary: #f8fafc; --color-border-tertiary: #e2e8f0; --color-text-primary: #1e293b; --color-text-secondary: #475569; --color-text-tertiary: #64748b; --border-radius-lg: 12px; --border-radius-md: 8px; }
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
        .tmark-line { height: 2px; background: #e2e8f0; position: relative; flex: 1; }
        .tmark-label { font-size: 11px; color: var(--color-text-tertiary); white-space: nowrap; padding: 0 6px; }
    </style>

    <div x-show="view === 'start'">
      <div style="text-align:center;padding:1.5rem 0.5rem">
        <p style="font-size:16px;font-weight:600;color:var(--color-text-primary);margin-bottom:6px text-balance">Calibrador de Tinnitus</p>
        <p style="font-size:14px;color:var(--color-text-tertiary);line-height:1.65;margin-bottom:8px">Usá auriculares. Cada capa se mide en 2 pasos:</p>
        <div style="display:flex;justify-content:center;gap:20px;margin-bottom:20px;font-size:12px;color:var(--color-text-secondary)">
          <span style="display:flex;align-items:center;gap:6px"><span style="width:18px;height:18px;border-radius:50%;background:#E1F5EE;color:#0F6E56;font-size:10px;font-weight:600;display:flex;align-items:center;justify-content:center">1</span> Umbral</span>
          <span style="display:flex;align-items:center;gap:6px"><span style="width:18px;height:18px;border-radius:50%;background:#E1F5EE;color:#0F6E56;font-size:10px;font-weight:600;display:flex;align-items:center;justify-content:center">2</span> Match</span>
        </div>
        <button @click="initApp()" class="btn-save !bg-[#1D9E75] !text-white !px-8 !py-3 rounded-lg shadow">Iniciar calibración</button>
      </div>
    </div>

    <div x-show="view === 'main'" style="display:none">
      <p style="font-size:11px;color:var(--color-text-tertiary);text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px">Capas de tinnitus</p>
      <div id="mainCards">
        <template x-for="l in layers" :key="l.id">
            <div class="card">
                <div class="lh">
                    <div class="ldot" :style="'background:'+l.color"></div>
                    <div class="lname" x-text="l.name"></div>
                    <div class="ldesc" x-text="l.desc"></div>
                </div>
                <div class="srow">
                    <span x-show="res[l.id]" class="badge" :style="'background:'+l.color+'22;color:'+l.color" x-text="fmtF(res[l.id]?.freqHz) + ' — ' + res[l.id]?.db + ' dB SL'"></span>
                    <span x-show="!res[l.id]" class="badge" style="background:#f1f5f9;color:#64748b">Sin calibrar</span>
                    <div style="flex:1"></div>
                    <button @click="startCalib(l.id)" class="px-4 py-1.5 rounded-full border text-xs font-semibold" :style="'border-color:'+l.color+';background:'+l.color+'11;color:'+l.color" x-text="res[l.id] ? 'Recalibrar' : 'Calibrar'"></button>
                </div>
            </div>
        </template>
      </div>
      <div class="grid grid-cols-2 gap-2 mt-4">
        <button @click="showSummary()" :disabled="Object.keys(res).length === 0" class="p-2.5 rounded-lg border text-sm font-semibold text-slate-700 disabled:opacity-40">Ver resumen</button>
        <button @click="saveAndExit()" :disabled="Object.keys(res).length === 0" class="p-2.5 rounded-lg bg-[#1D9E75] text-white text-sm font-semibold disabled:opacity-40">Guardar Resultados</button>
      </div>
    </div>

    <div x-show="view === 'calibrate'" style="display:none">
      <div class="flex items-center gap-2 mb-4">
        <div class="ldot" :style="'background:'+currentLayer?.color"></div>
        <span class="text-sm font-semibold text-slate-800 flex-1" x-text="currentLayer?.name"></span>
        <span class="text-xs text-slate-400" x-text="fmtF(ff(currentLayer?.freq))"></span>
        <button @click="cancelCalib()" class="text-xs text-slate-400 hover:text-slate-600">Cancelar</button>
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
        <p class="text-sm font-semibold text-slate-800 flex-1">Perfil de tinnitus</p>
        <button @click="view = 'main'" class="text-xs text-slate-400">Volver</button>
      </div>
      <div class="rcards">
        <template x-for="l in layers.filter(x => res[x.id])" :key="l.id">
            <div class="rcard" :style="'border-color:'+l.color+'55'">
                <div class="ldot mx-auto mb-2" :style="'background:'+l.color"></div>
                <div class="text-xs font-semibold text-slate-800 mb-1" x-text="l.name"></div>
                <div class="rnum" :style="'color:'+l.color"><span x-text="res[l.id].db"></span><span class="text-xs"> dB SL</span></div>
                <div class="rlabel" x-text="fmtF(res[l.id].freqHz)"></div>
                <div class="rlabel" x-text="dbLabel(res[l.id].db)"></div>
            </div>
        </template>
      </div>
      <div class="instruct !bg-slate-50 text-xs" x-html="summaryNote()"></div>
      <button @click="saveAndExit()" class="w-full py-2.5 rounded-lg bg-[#1D9E75] text-white text-sm font-semibold">Guardar para audiólogo</button>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('tinnitusCalibrator', (sessionId) => ({
        view: 'start',
        ac: null,
        mg: null,
        ca: { nodes: [], g: null },
        cs: { id: null, phase: 1, tVol: 5, cVol: 5 },
        res: {},
        layers: [
            {id:'ranita', name:'Ranita / grillo', desc:'pulsante', type:'pulse', freq:78, speed:52, color:'#1D9E75'},
            {id:'viento', name:'Viento', desc:'ruido filtrado', type:'noise', freq:38, speed:null, color:'#378ADD'},
            {id:'tono', name:'Uuuuuuu', desc:'tono puro', type:'pure', freq:55, speed:null, color:'#7F77DD'},
            {id:'sube', name:'Sube y baja', desc:'oscilante', type:'sweep', freq:62, speed:28, color:'#BA7517'},
        ],
        currentLayer: null,

        ff(v) { return Math.round(250 * Math.pow(32, v / 100)); },
        fmtF(f) { return f >= 1000 ? (f / 1000).toFixed(1) + ' kHz' : f + ' Hz'; },
        spd(v) { return parseFloat((0.3 * Math.pow(40, v / 100)).toFixed(1)); },
        dbSL(t, m) { if (!t || t <= 0 || m <= t) return 0; return Math.round(20 * Math.log10(m / t) * 10) / 10; },
        dbLabel(d) {
            if (d <= 3) return 'al umbral';
            if (d <= 7) return 'leve';
            if (d <= 12) return 'moderado';
            if (d <= 20) return 'intenso';
            return 'muy intenso';
        },
        dbHint(t, m) {
            let d = this.dbSL(t, m);
            return d === 0 ? 'igual al umbral' : '+' + d + ' dB SL sobre umbral — ' + this.dbLabel(d);
        },

        initApp() {
            this.ac = new(window.AudioContext || window.webkitAudioContext)();
            this.mg = this.ac.createGain();
            this.mg.gain.value = 1;
            this.mg.connect(this.ac.destination);
            this.view = 'main';
        },

        startCalib(id) {
            if (this.ac.state === 'suspended') this.ac.resume();
            this.currentLayer = this.layers.find(l => l.id === id);
            this.cs = { id: id, phase: 1, tVol: 5, cVol: 5 };
            this.view = 'calibrate';
            setTimeout(() => { this.playCA(id, 5); }, 80);
        },

        stopCA() {
            this.ca.nodes.forEach(n => { try { n.stop && n.stop(0); } catch (e) {} try { n.disconnect(); } catch (e) {} });
            if (this.ca.g) try { this.ca.g.disconnect(); } catch (e) {}
            this.ca = { nodes: [], g: null };
        },

        playCA(id, vol) {
            this.stopCA();
            let l = this.layers.find(x => x.id === id);
            if (!l) return;
            let fr = this.ff(l.freq);
            let g = this.ac.createGain();
            g.gain.value = vol / 100 * 0.5;
            g.connect(this.mg);
            let ns = [];
            if (l.type === 'pure') {
                let o = this.ac.createOscillator(); o.type = 'sine'; o.frequency.value = fr; o.connect(g); o.start(); ns.push(o);
            } else if (l.type === 'noise') {
                let sz = this.ac.sampleRate * 4, buf = this.ac.createBuffer(1, sz, this.ac.sampleRate), d = buf.getChannelData(0);
                for (let i = 0; i < sz; i++) d[i] = Math.random() * 2 - 1;
                let src = this.ac.createBufferSource(); src.buffer = buf; src.loop = true;
                let flt = this.ac.createBiquadFilter(); flt.type = 'bandpass'; flt.frequency.value = fr; flt.Q.value = 2;
                src.connect(flt); flt.connect(g); src.start(); ns.push(src, flt);
            } else if (l.type === 'pulse') {
                let o = this.ac.createOscillator(); o.type = 'sine'; o.frequency.value = fr;
                let pg = this.ac.createGain(); pg.gain.value = 0.5;
                let lfo = this.ac.createOscillator(); lfo.type = 'sine'; lfo.frequency.value = this.spd(l.speed);
                let lg = this.ac.createGain(); lg.gain.value = 0.5;
                lfo.connect(lg); lg.connect(pg.gain); o.connect(pg); pg.connect(g);
                o.start(); lfo.start(); ns.push(o, lfo, lg, pg);
            } else if (l.type === 'sweep') {
                let o = this.ac.createOscillator(); o.type = 'sine'; o.frequency.value = fr;
                let fl = this.ac.createOscillator(); fl.type = 'sine'; fl.frequency.value = this.spd(l.speed);
                let fg = this.ac.createGain(); fg.gain.value = fr * 0.45;
                fl.connect(fg); fg.connect(o.frequency); o.connect(g); o.start(); fl.start(); ns.push(o, fl, fg);
            }
            this.ca = { nodes: ns, g: g };
        },

        onSlider(v) {
            this.cs.cVol = parseInt(v);
            if (this.ca.g) this.ca.g.gain.setValueAtTime(this.cs.cVol / 100 * 0.5, this.ac.currentTime);
        },

        markThresh() {
            this.cs.tVol = this.cs.cVol;
            this.cs.phase = 2;
            this.onSlider(this.cs.tVol);
        },

        markMatch() {
            let db = this.dbSL(this.cs.tVol, this.cs.cVol);
            this.res[this.cs.id] = { name: this.currentLayer.name, freqHz: this.ff(this.currentLayer.freq), tVol: this.cs.tVol, mVol: this.cs.cVol, db: db };
            this.stopCA();
            this.view = 'main';
        },

        cancelCalib() {
            this.stopCA();
            this.view = 'main';
        },

        showSummary() {
            this.view = 'summary';
        },

        summaryNote() {
            let count = Object.keys(this.res).length;
            let total = Object.values(this.res).reduce((a, b) => a + b.db, 0);
            let avg = count ? (total / count).toFixed(1) : 0;
            return `<strong>${count} capa${count !== 1 ? 's' : ''} mapeada${count !== 1 ? 's' : ''}.</strong> Promedio: ${avg} dB SL. Estos valores le indican al audiólogo la intensidad real de tu tinnitus.`;
        },

        saveAndExit() {
            this.$wire.saveResults(this.res);
        }
    }));
});
</script>