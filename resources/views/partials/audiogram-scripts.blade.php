@once
@script
<script>
Alpine.data('audiogramEntry', (initialData = null) => ({
    activeEar: 'right',
    audioData: initialData || { right: {}, left: {} },
    isReadOnly: false,
    hist: [],
    hov: null,
    canvas: null,
    ctx: null,
    dpr: 1, 
    CW: 640, 
    CH: 500,
    FREQS: [125, 250, 500, 750, 1000, 1500, 2000, 3000, 4000, 6000, 8000],
    FREQ_SHOW: new Set([125, 250, 500, 1000, 2000, 4000, 8000]),
    FREQ_LABELS: { 125: '125', 250: '250', 500: '500', 750: '750', 1000: '1k', 1500: '1.5k', 2000: '2k', 3000: '3k', 4000: '4k', 6000: '6k', 8000: '8k' },
    DB_MIN: -10, 
    DB_MAX: 120,
    PAD: { l: 54, r: 18, t: 38, b: 50 },
    RC: '#c0392b', 
    LC: '#2471a3',

    init() {
        this.canvas = this.$refs.audiogramCanvas;
        this.ctx = this.canvas.getContext('2d');
        this.doResize();
        
        window.addEventListener('resize', () => { 
            this.doResize(); 
            this.draw(); 
        });
        
        window.addEventListener('load-audiogram', (e) => {
            this.audioData = e.detail.data || { right: {}, left: {} };
            this.isReadOnly = e.detail.readOnly || false;
            this.hist = [];
            this.draw();
        });

        this.draw();
    },

    pW() { return this.CW - this.PAD.l - this.PAD.r; },
    pH() { return this.CH - this.PAD.t - this.PAD.b; },

    getX(f) {
        const index = this.FREQS.indexOf(Number(f));
        if (index === -1) return this.PAD.l;
        return this.PAD.l + (index / (this.FREQS.length - 1)) * this.pW();
    },

    getY(db) { 
        return this.PAD.t + (db - this.DB_MIN) / (this.DB_MAX - this.DB_MIN) * this.pH(); 
    },

    doResize() {
        const w = this.canvas.parentNode.getBoundingClientRect().width || 640;
        this.CW = Math.floor(w);
        this.CH = 500; 
        this.dpr = window.devicePixelRatio || 1;
        this.canvas.width = this.CW * this.dpr;
        this.canvas.height = this.CH * this.dpr;
        this.canvas.style.height = this.CH + 'px';
        this.ctx.setTransform(this.dpr, 0, 0, this.dpr, 0, 0);
    },

    evCoords(e) {
        const r = this.canvas.getBoundingClientRect();
        return [
            (e.clientX - r.left) * this.CW / r.width,
            (e.clientY - r.top) * this.CH / r.height
        ];
    },

    inPlot(cx, cy) {
        return cx >= this.PAD.l - 20 && cx <= this.CW - this.PAD.r + 20
            && cy >= this.PAD.t - 10 && cy <= this.CH - this.PAD.b + 10;
    },

    snapFreq(cx) {
        let bf = null, bd = Infinity;
        const lim = 30;
        this.FREQS.forEach(f => {
            const d = Math.abs(this.getX(f) - cx);
            if (d < bd) { bd = d; bf = f; }
        });
        return bd <= lim ? bf : null;
    },

    snapDb(cy) {
        let db = this.DB_MIN + (cy - this.PAD.t) / this.pH() * (this.DB_MAX - this.DB_MIN);
        return Math.max(this.DB_MIN, Math.min(this.DB_MAX, Math.round(db / 5) * 5));
    },

    onClick(e) {
        if (this.isReadOnly) return;
        const [cx, cy] = this.evCoords(e);
        if (!this.inPlot(cx, cy)) return;
        const freq = this.snapFreq(cx);
        if (!freq) return;
        const db = this.snapDb(cy);

        this.hist.push(JSON.parse(JSON.stringify(this.audioData)));
        if (this.hist.length > 50) this.hist.shift();

        if (this.audioData[this.activeEar][freq] === db) {
            delete this.audioData[this.activeEar][freq];
        } else {
            this.audioData[this.activeEar][freq] = db;
        }
        
        this.draw();
        this.$dispatch('audiogram-updated', this.audioData);
    },

    onMove(e) {
        if (this.isReadOnly) return;
        const [cx, cy] = this.evCoords(e);
        if (!this.inPlot(cx, cy)) {
            if (this.hov) { this.hov = null; this.draw(); }
            return;
        }
        const freq = this.snapFreq(cx);
        if (!freq) {
            if (this.hov) { this.hov = null; this.draw(); }
            return;
        }
        const db = this.snapDb(cy);
        const nh = { freq, db, ear: this.activeEar };
        if (!this.hov || this.hov.freq !== nh.freq || this.hov.db !== nh.db || this.hov.ear !== nh.ear) {
            this.hov = nh;
            this.draw();
        }
    },

    draw() {
        const ctx = this.ctx;
        const gc = 'rgba(0,0,0,0.08)';
        const gs = 'rgba(0,0,0,0.16)';
        const tm = '#999';
        const tl = '#334155';

        ctx.clearRect(0, 0, this.CW, this.CH);
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, this.CW, this.CH);

        // 1. Speech banana
        const soundsData = [
            { hz: 250, min: 30, max: 50, sounds: ["u", "o", "m", "z"] },
            { hz: 500, min: 25, max: 45, sounds: ["a", "i", "e", "j"] },
            { hz: 1000, min: 20, max: 45, sounds: ["b", "d", "g", "r"] },
            { hz: 2000, min: 20, max: 50, sounds: ["ch", "sh", "k", "t"] },
            { hz: 4000, min: 25, max: 55, sounds: ["s", "f", "th"] },
            { hz: 6000, min: 35, max: 60, sounds: ["agudos"] }
        ];

        ctx.beginPath();
        soundsData.forEach((d, i) => {
            const x = this.getX(d.hz), y = this.getY(d.min);
            i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
        });
        [...soundsData].reverse().forEach((d) => ctx.lineTo(this.getX(d.hz), this.getY(d.max)));
        ctx.closePath();
        
        ctx.fillStyle = 'rgba(251, 191, 36, 0.15)';
        ctx.fill();
        ctx.strokeStyle = 'rgba(251, 191, 36, 0.3)';
        ctx.lineWidth = 1;
        ctx.stroke();

        // 1.1 Letters / Sounds inside the banana
        ctx.fillStyle = 'rgba(180, 130, 20, 0.7)'; // Slightly darker text for readability
        ctx.font = '500 11px system-ui';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        soundsData.forEach(d => {
            const x = this.getX(d.hz);
            const y = this.getY((d.min + d.max) / 2);
            ctx.fillText(d.sounds.join(' '), x, y);
        });

        // 2. Grid - Horizontal (dB)
        ctx.setLineDash([]);
        for (let db = this.DB_MIN; db <= this.DB_MAX; db += 10) {
            const y = this.getY(db);
            ctx.beginPath();
            ctx.moveTo(this.PAD.l, y);
            ctx.lineTo(this.CW - this.PAD.r, y);
            ctx.strokeStyle = db === 0 ? gs : gc;
            ctx.lineWidth = db === 0 ? 1 : 0.5;
            ctx.stroke();
            
            ctx.fillStyle = tm;
            ctx.font = '11px system-ui';
            ctx.textAlign = 'right';
            ctx.fillText(db, this.PAD.l - 12, y + 4);
        }

        // 3. Grid - Vertical (Hz)
        this.FREQS.forEach(hz => {
            const x = this.getX(hz);
            const show = this.FREQ_SHOW.has(hz);
            ctx.beginPath();
            ctx.moveTo(x, this.PAD.t);
            ctx.lineTo(x, this.CH - this.PAD.b);
            ctx.strokeStyle = show ? gs : gc;
            ctx.lineWidth = 0.5;
            ctx.stroke();
            
            if (show) {
                ctx.fillStyle = tl;
                ctx.font = '500 12px system-ui';
                ctx.textAlign = 'center';
                ctx.fillText(this.FREQ_LABELS[hz], x, this.CH - this.PAD.b + 20);
            }
        });

        // 4. Data (Ears)
        this.drawEarData(ctx, this.audioData.left, this.LC, 'left');
        this.drawEarData(ctx, this.audioData.right, this.RC, 'right');

        // 5. Hover ghost
        if (this.hov && !this.isReadOnly) {
            const x = this.getX(this.hov.freq), y = this.getY(this.hov.db);
            const col = this.hov.ear === 'right' ? this.RC : this.LC;
            ctx.globalAlpha = 0.4;
            if (this.hov.ear === 'right') this.drawO(x, y, col); else this.drawXSym(x, y, col);
            ctx.globalAlpha = 1;

            const lbl = `${this.hov.freq >= 1000 ? (this.hov.freq / 1000) + 'k' : this.hov.freq} Hz — ${this.hov.db} dB`;
            ctx.fillStyle = '#1e293b';
            ctx.font = 'bold 11px system-ui';
            ctx.textAlign = 'center';
            ctx.fillText(lbl, x, y - 15);
        }
    },

    drawEarData(ctx, earData, color, earType) {
        const freqs = Object.keys(earData)
            .map(Number)
            .filter(f => this.FREQS.includes(f))
            .sort((a, b) => a - b);
            
        if (freqs.length === 0) return;

        // FASE 1: DIBUJAR LÍNEAS (CON DETECCIÓN DE SALTOS/ISLAS DE AUDICIÓN)
        ctx.beginPath();
        ctx.strokeStyle = color;
        ctx.lineWidth = 2.5;

        for (let i = 0; i < freqs.length; i++) {
            const hz = freqs[i];
            const x = this.getX(hz);
            const y = this.getY(earData[hz]);
            
            if (i === 0) {
                // Primer punto: apoyamos el lápiz
                ctx.moveTo(x, y); 
            } else {
                const prevHz = freqs[i - 1];
                
                // Verificamos si hay algún "hueco" de frecuencia principal que se haya saltado
                let hasGap = false;
                for (let f of this.FREQ_SHOW) {
                    if (f > prevHz && f < hz) {
                        hasGap = true;
                        break;
                    }
                }

                if (hasGap) {
                    // Si se saltó una frecuencia principal (ej. de 500 a 2k falta 1000)
                    // LEVANTAMOS EL LÁPIZ y lo ponemos en la nueva coordenada sin trazar línea
                    ctx.moveTo(x, y);
                } else {
                    // Si son frecuencias continuas en nuestra evaluación, trazamos la línea normal
                    ctx.lineTo(x, y); 
                }
            }
        }
        ctx.stroke();

        // FASE 2: DIBUJAR LOS SÍMBOLOS (X / O)
        for (let i = 0; i < freqs.length; i++) {
            const hz = freqs[i];
            const x = this.getX(hz);
            const y = this.getY(earData[hz]);
            if (earType === 'right') this.drawO(x, y, color); else this.drawXSym(x, y, color);
        }
    },

    drawO(x, y, col) {
        this.ctx.beginPath(); 
        this.ctx.arc(x, y, 6, 0, Math.PI * 2);
        this.ctx.fillStyle = '#fff'; this.ctx.fill();
        this.ctx.strokeStyle = col; this.ctx.lineWidth = 2; this.ctx.stroke();
    },

    drawXSym(x, y, col) {
        const s = 5;
        this.ctx.beginPath(); 
        this.ctx.arc(x, y, 6, 0, Math.PI * 2); 
        this.ctx.fillStyle = '#fff'; this.ctx.fill();
        this.ctx.beginPath(); 
        this.ctx.moveTo(x - s, y - s); this.ctx.lineTo(x + s, y + s);
        this.ctx.moveTo(x + s, y - s); this.ctx.lineTo(x - s, y + s);
        this.ctx.strokeStyle = col; this.ctx.lineWidth = 2.5; this.ctx.stroke();
    },

    setEar(ear) { this.activeEar = ear; },
    undoLast() {
        if (this.isReadOnly || !this.hist.length) return;
        this.audioData = this.hist.pop();
        this.draw();
        this.$dispatch('audiogram-updated', this.audioData);
    },
    clearEar() {
        if (this.isReadOnly) return;
        this.hist.push(JSON.parse(JSON.stringify(this.audioData)));
        this.audioData[this.activeEar] = {};
        this.draw();
        this.$dispatch('audiogram-updated', this.audioData);
    },
    clearAll() {
        if (this.isReadOnly) return;
        this.hist.push(JSON.parse(JSON.stringify(this.audioData)));
        this.audioData = { right: {}, left: {} };
        this.draw();
        this.$dispatch('audiogram-updated', this.audioData);
    },
    save() {
        this.$wire.save(this.audioData);
    }
}));
</script>
@endscript
@endonce
