@script
<script>
(function() {
    // Stage 2: Tinnitus Mapping
    Alpine.data('tinnitusMapper', (initialLayers, initialMasterVol) => ({
        initialized: false,
        ctx: null,
        masterGain: null,
        activeNodes: {},
        layers: initialLayers,
        masterVol: initialMasterVol,

        freqFromSlider(v) { return Math.round(250 * Math.pow(32, v / 100)); },
        fmtFreq(f) { return f >= 1000 ? (f / 1000).toFixed(1) + ' kHz' : f + ' Hz'; },
        spdFromSlider(v) { return parseFloat((0.3 * Math.pow(40, v / 100)).toFixed(1)); },

        initApp() {
            this.initialized = true;
            this.ctx = new (window.AudioContext || window.webkitAudioContext)();
            this.masterGain = this.ctx.createGain();
            this.masterGain.gain.value = this.masterVol / 100;
            this.masterGain.connect(this.ctx.destination);
        },

        makeNoiseBuf() {
            let sz = this.ctx.sampleRate * 4;
            let buf = this.ctx.createBuffer(1, sz, this.ctx.sampleRate);
            let d = buf.getChannelData(0);
            for (let i = 0; i < sz; i++) d[i] = Math.random() * 2 - 1;
            return buf;
        },

        stopLayer(id) {
            let n = this.activeNodes[id];
            if (!n) return;
            n.nodes.forEach(node => { 
                try { node.stop && node.stop(0); } catch(e){} 
                try { node.disconnect(); } catch(e){} 
            });
            try { n.gainNode.disconnect(); } catch(e){}
            delete this.activeNodes[id];
        },

        startLayer(id) {
            this.stopLayer(id);
            let l = this.layers.find(x => x.id === id);
            if (!l) return;
            let freq = this.freqFromSlider(l.freq);
            let vol = l.vol / 100 * 0.28;
            let lgain = this.ctx.createGain();
            lgain.gain.value = vol;
            lgain.connect(this.masterGain);
            let nodes = [];
            let refs = {};

            if (l.type === 'pure') {
                let osc = this.ctx.createOscillator(); osc.type = 'sine'; osc.frequency.value = freq;
                osc.connect(lgain); osc.start(); nodes.push(osc); refs.osc = osc;
            } else if (l.type === 'noise') {
                let src = this.ctx.createBufferSource(); src.buffer = this.makeNoiseBuf(); src.loop = true;
                let filt = this.ctx.createBiquadFilter(); filt.type = 'bandpass'; filt.frequency.value = freq; filt.Q.value = 2.0;
                src.connect(filt); filt.connect(lgain); src.start(); nodes.push(src, filt); refs.filter = filt;
            } else if (l.type === 'pulse') {
                let osc = this.ctx.createOscillator(); osc.type = 'sine'; osc.frequency.value = freq;
                let pg = this.ctx.createGain(); pg.gain.value = 0.5;
                let lfo = this.ctx.createOscillator(); lfo.type = 'sine'; lfo.frequency.value = this.spdFromSlider(l.speed);
                let lfog = this.ctx.createGain(); lfog.gain.value = 0.5;
                lfo.connect(lfog); lfog.connect(pg.gain); osc.connect(pg); pg.connect(lgain);
                osc.start(); lfo.start(); nodes.push(osc, lfo, lfog, pg); refs.osc = osc; refs.lfo = lfo;
            } else if (l.type === 'sweep') {
                let osc = this.ctx.createOscillator(); osc.type = 'sine'; osc.frequency.value = freq;
                let flfo = this.ctx.createOscillator(); flfo.type = 'sine'; flfo.frequency.value = this.spdFromSlider(l.speed);
                let flfog = this.ctx.createGain(); flfog.gain.value = freq * 0.45;
                flfo.connect(flfog); flfog.connect(osc.frequency); osc.connect(lgain);
                osc.start(); flfo.start(); nodes.push(osc, flfo, flfog); refs.osc = osc; refs.flfo = flfo; refs.flfog = flfog;
            }
            this.activeNodes[id] = { nodes, gainNode: lgain, refs };
        },

        toggleLayer(id) {
            if (this.ctx.state === 'suspended') this.ctx.resume();
            if (this.activeNodes[id]) {
                this.stopLayer(id);
            } else {
                this.startLayer(id);
            }
        },

        updateFreq(id, val) {
            let freq = this.freqFromSlider(val);
            let n = this.activeNodes[id];
            if (!n) return;
            let r = n.refs;
            if (r.osc) r.osc.frequency.setValueAtTime(freq, this.ctx.currentTime);
            if (r.filter) r.filter.frequency.setValueAtTime(freq, this.ctx.currentTime);
            if (r.flfog) r.flfog.gain.setValueAtTime(freq * 0.45, this.ctx.currentTime);
        },

        updateVol(id, val) {
            let n = this.activeNodes[id];
            if (n) n.gainNode.gain.setValueAtTime(val / 100 * 0.28, this.ctx.currentTime);
        },

        updateSpeed(id, val) {
            let spd = this.spdFromSlider(val);
            let n = this.activeNodes[id];
            if (!n) return;
            let r = n.refs;
            if (r.lfo) r.lfo.frequency.setValueAtTime(spd, this.ctx.currentTime);
            if (r.flfo) r.flfo.frequency.setValueAtTime(spd, this.ctx.currentTime);
        },

        setMasterVol(val) {
            if (this.masterGain) this.masterGain.gain.setValueAtTime(val / 100, this.ctx.currentTime);
        },

        saveProfile() {
            this.$wire.save(this.layers, this.masterVol);
        }
    }));

    // Stage 3: Tinnitus Calibrator
    Alpine.data('tinnitusCalibrator', (patientId) => ({
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
            let refs = {};

            if (l.type === 'pure') {
                let o = this.ac.createOscillator(); o.type = 'sine'; o.frequency.value = fr;
                o.connect(g); o.start(); ns.push(o); refs.osc = o;
            } else if (l.type === 'noise') {
                let sz = this.ac.sampleRate * 4, buf = this.ac.createBuffer(1, sz, this.ac.sampleRate), d = buf.getChannelData(0);
                for (let i = 0; i < sz; i++) d[i] = Math.random() * 2 - 1;
                let src = this.ac.createBufferSource(); src.buffer = buf; src.loop = true;
                let filt = this.ac.createBiquadFilter(); filt.type = 'bandpass'; filt.frequency.value = fr; filt.Q.value = 2.0;
                src.connect(filt); filt.connect(g); src.start(); ns.push(src, filt); refs.filter = filt;
            } else if (l.type === 'pulse') {
                let o = this.ac.createOscillator(); o.type = 'sine'; o.frequency.value = fr;
                let pg = this.ac.createGain(); pg.gain.value = 0.5;
                let lfo = this.ac.createOscillator(); lfo.type = 'sine'; lfo.frequency.value = this.spd(l.speed);
                let lg = this.ac.createGain(); lg.gain.value = 0.5;
                lfo.connect(lg); lg.connect(pg.gain); o.connect(pg); pg.connect(g);
                o.start(); lfo.start(); ns.push(o, lfo, lg, pg); refs.osc = o; refs.lfo = lfo;
            } else if (l.type === 'sweep') {
                let o = this.ac.createOscillator(); o.type = 'sine'; o.frequency.value = fr;
                let fl = this.ac.createOscillator(); fl.type = 'sine'; fl.frequency.value = this.spd(l.speed);
                let fg = this.ac.createGain(); fg.gain.value = fr * 0.45;
                fl.connect(fg); fg.connect(o.frequency); o.connect(g);
                o.start(); fl.start(); ns.push(o, fl, fg); refs.osc = o; refs.flfo = fl; refs.flfog = fg;
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

        saveResults() {
            this.$wire.saveResults(this.res);
            this.view = 'main';
        }
    }));
})();
</script>
@endscript
