@script
<script>
(function() {
    // Stage 2: Tinnitus Mapping (Per-Ear Support)
    Alpine.data('tinnitusMapper', (initialLeftLayers, initialRightLayers, initialMasterVol) => ({
        initialized: false,
        ctx: null,
        masterGain: null,
        activeEar: 'left',
        activeLeftNodes: {},
        activeRightNodes: {},
        waveAnimFrames: {},
        leftLayers: initialLeftLayers,
        rightLayers: initialRightLayers,
        masterVol: initialMasterVol,
        evaluationScope: 'ambos',

        freqFromSlider(v) {
            const minLog = Math.log(125);
            const scale = (Math.log(8000) - minLog) / 100;
            return Math.round(Math.exp(minLog + scale * v));
        },
        fmtFreq(f) { return f >= 1000 ? (f / 1000).toFixed(1) + ' kHz' : f + ' Hz'; },
        spdFromSlider(v) { return parseFloat((0.3 * Math.pow(40, v / 100)).toFixed(1)); },

        initApp(scope = 'ambos') {
            this.evaluationScope = scope;
            if (scope === 'OD') this.activeEar = 'right';
            if (scope === 'OI') this.activeEar = 'left';
            this.initialized = true;
            
            if (!this.ctx) {
                this.ctx = new (window.AudioContext || window.webkitAudioContext)();
                this.masterGain = this.ctx.createGain();
                this.masterGain.gain.value = this.masterVol / 100;
                this.masterGain.connect(this.ctx.destination);
            } else {
                if (this.ctx.state === 'suspended') this.ctx.resume();
            }
        },

        resetScope() {
            this.initialized = false;
            // Detener temporalmente los ruidos
            ['ranita', 'viento', 'tono', 'sube'].forEach(id => {
                this.stopLayer('left', id);
                this.stopLayer('right', id);
            });
            if (this.ctx && this.ctx.state === 'running') this.ctx.suspend();
        },

        makeNoiseBuf() {
            let sz = this.ctx.sampleRate * 4;
            let buf = this.ctx.createBuffer(1, sz, this.ctx.sampleRate);
            let d = buf.getChannelData(0);
            for (let i = 0; i < sz; i++) d[i] = Math.random() * 2 - 1;
            return buf;
        },

        stopLayer(ear, id) {
            let nodesMap = ear === 'left' ? this.activeLeftNodes : this.activeRightNodes;
            let n = nodesMap[id];
            if (!n) return;
            n.nodes.forEach(node => { 
                try { node.stop && node.stop(0); } catch(e){} 
                try { node.disconnect(); } catch(e){} 
            });
            try { n.gainNode.disconnect(); } catch(e){}
            delete nodesMap[id];
        },

        startLayer(ear, id) {
            this.stopLayer(ear, id);
            let layersList = ear === 'left' ? this.leftLayers : this.rightLayers;
            let nodesMap = ear === 'left' ? this.activeLeftNodes : this.activeRightNodes;
            
            let l = layersList.find(x => x.id === id);
            if (!l) return;
            
            let freq = this.freqFromSlider(l.freq);
            let vol = l.vol / 100 * 0.28;
            let lgain = this.ctx.createGain();
            lgain.gain.value = vol;
            
            // Stereo Panning
            let panner = this.ctx.createStereoPanner ? this.ctx.createStereoPanner() : null;
            if (panner) {
                panner.pan.value = (ear === 'left') ? -1 : 1;
                lgain.connect(panner);
                panner.connect(this.masterGain);
            } else {
                lgain.connect(this.masterGain);
            }

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
            nodesMap[id] = { nodes, gainNode: lgain, refs, panner };
        },

        toggleLayer(ear, id) {
            if (this.ctx.state === 'suspended') this.ctx.resume();
            let nodesMap = ear === 'left' ? this.activeLeftNodes : this.activeRightNodes;
            if (nodesMap[id]) {
                this.stopLayer(ear, id);
            } else {
                this.startLayer(ear, id);
            }
        },

        updateFreq(ear, id, val) {
            let freq = this.freqFromSlider(val);
            let nodesMap = ear === 'left' ? this.activeLeftNodes : this.activeRightNodes;
            let n = nodesMap[id];
            if (!n) return;
            let r = n.refs;
            if (r.osc) r.osc.frequency.setValueAtTime(freq, this.ctx.currentTime);
            if (r.filter) r.filter.frequency.setValueAtTime(freq, this.ctx.currentTime);
            if (r.flfog) r.flfog.gain.setValueAtTime(freq * 0.45, this.ctx.currentTime);
        },

        updateVol(ear, id, val) {
            let nodesMap = ear === 'left' ? this.activeLeftNodes : this.activeRightNodes;
            let n = nodesMap[id];
            if (n) n.gainNode.gain.setValueAtTime(val / 100 * 0.28, this.ctx.currentTime);
        },

        updateSpeed(ear, id, val) {
            let spd = this.spdFromSlider(val);
            let nodesMap = ear === 'left' ? this.activeLeftNodes : this.activeRightNodes;
            let n = nodesMap[id];
            if (!n) return;
            let r = n.refs;
            if (r.lfo) r.lfo.frequency.setValueAtTime(spd, this.ctx.currentTime);
            if (r.flfo) r.flfo.frequency.setValueAtTime(spd, this.ctx.currentTime);
        },

        setMasterVol(val) {
            if (this.masterGain) this.masterGain.gain.setValueAtTime(val / 100, this.ctx.currentTime);
        },

        saveProfile() {
            let enrich = (l) => {
                let realFreq = this.freqFromSlider(l.freq);
                return {
                    ...l,
                    // Agrega el valor clínico real para los Gemelos Digitales
                    clinical_hz: realFreq,
                    clinical_hz_str: this.fmtFreq(realFreq),
                    clinical_speed_hz: l.speed !== null ? this.spdFromSlider(l.speed) : null
                };
            };
            
            this.$wire.save(this.evaluationScope, this.leftLayers.map(enrich), this.rightLayers.map(enrich), this.masterVol);
        },

        startWaveAnim(ear, layer, canvasEl) {
            // No animar hasta que el usuario haya iniciado el mapeador
            if (!this.initialized) return;

            const key = ear + '-' + layer.id;
            // Cancelar animación previa si existe
            if (this.waveAnimFrames[key]) {
                cancelAnimationFrame(this.waveAnimFrames[key]);
                delete this.waveAnimFrames[key];
            }
            if (!canvasEl) return;

            const ctx2d = canvasEl.getContext('2d');
            canvasEl.width = canvasEl.offsetWidth || 200;
            canvasEl.height = 48;

            const nodesMap = ear === 'left' ? this.activeLeftNodes : this.activeRightNodes;
            let t = 0;

            const draw = () => {
                // Obtener estado actual de la capa (puede cambiar con sliders)
                const layersList = ear === 'left' ? this.leftLayers : this.rightLayers;
                const l = layersList.find(x => x.id === layer.id);
                if (!l) return;

                const isOn = !!nodesMap[layer.id];
                const W = canvasEl.width;
                const H = canvasEl.height;
                const freq = this.freqFromSlider(l.freq);
                const vol = l.vol / 100;
                const spd = l.speed !== null ? this.spdFromSlider(l.speed) : 1;
                const color = l.color;
                
                // Volvemos al renderizado limpio — sin trails ni perspectiva
                ctx2d.clearRect(0, 0, W, H);
                
                const alpha = isOn ? 1.0 : 0.15;
                const pulse = isOn ? (Math.sin(t * 10) * 0.05 + 1) : 1;
                const amp = (H / 2 - 2) * vol * pulse;

                ctx2d.globalAlpha = alpha;
                ctx2d.strokeStyle = color;
                ctx2d.lineWidth = isOn ? 2.5 : 1;
                
                if (isOn) {
                    ctx2d.shadowBlur = 10;
                    ctx2d.shadowColor = color;
                }

                ctx2d.beginPath();

                if (l.type === 'pure') {
                    const cycles = Math.max(1.5, Math.min(15, freq / 1000 * 5));
                    for (let x = 0; x <= W; x++) {
                        const angle = (x / W) * cycles * Math.PI * 2 - t * 4;
                        const y = H / 2 + Math.sin(angle) * amp;
                        x === 0 ? ctx2d.moveTo(x, y) : ctx2d.lineTo(x, y);
                    }
                    t += 0.06;

                } else if (l.type === 'noise') {
                    for (let x = 0; x <= W; x++) {
                        const nx = x / W;
                        const n = Math.sin(nx * 12 * Math.PI - t * 15) * 0.5
                                + Math.sin(nx * freq/200 * Math.PI - t * 8) * 0.3
                                + (Math.random() - 0.5) * 0.4;
                        const y = H / 2 + n * amp;
                        x === 0 ? ctx2d.moveTo(x, y) : ctx2d.lineTo(x, y);
                    }
                    t += 0.04;

                } else if (l.type === 'pulse') {
                    const pulseEnv = Math.pow((Math.sin(t * spd * Math.PI) + 1) / 2, 3);
                    const cycles = 4 + (freq / 1500);
                    
                    if (isOn) {
                        ctx2d.shadowBlur = 5 + (pulseEnv * 15);
                        ctx2d.lineWidth = 1 + (pulseEnv * 3);
                    }

                    for (let x = 0; x <= W; x++) {
                        const nx = x / W;
                        const angle = nx * cycles * Math.PI * 2 - t * (10 + pulseEnv * 20);
                        const y = H / 2 + Math.sin(angle) * amp * (0.2 + pulseEnv * 0.8);
                        x === 0 ? ctx2d.moveTo(x, y) : ctx2d.lineTo(x, y);
                    }
                    t += 0.01 + (pulseEnv * 0.04);

                } else if (l.type === 'sweep') {
                    const sweepPhase = Math.sin(t * spd * 0.5);
                    const cycles = 4 + sweepPhase * 3.5;
                    for (let x = 0; x <= W; x++) {
                        const angle = (x / W) * cycles * Math.PI * 2 - t * 5;
                        const y = H / 2 + Math.sin(angle) * amp;
                        x === 0 ? ctx2d.moveTo(x, y) : ctx2d.lineTo(x, y);
                    }
                    t += 0.02;
                }

                ctx2d.stroke();
                ctx2d.globalAlpha = 1.0;
                ctx2d.shadowBlur = 0;

                this.waveAnimFrames[key] = requestAnimationFrame(draw);
            };

            draw();
        },

        stopWaveAnim(ear, layerId) {
            const key = ear + '-' + layerId;
            if (this.waveAnimFrames[key]) {
                cancelAnimationFrame(this.waveAnimFrames[key]);
                delete this.waveAnimFrames[key];
            }
        },

        stopAllWaveAnims() {
            Object.values(this.waveAnimFrames).forEach(id => cancelAnimationFrame(id));
            this.waveAnimFrames = {};
        },

        destroy() {
            this.stopAllWaveAnims();
        }
    }));
})();
</script>
@endscript
