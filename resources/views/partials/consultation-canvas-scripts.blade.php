@once
@script
<script>
Alpine.data('consultationViewer', (payload) => ({
    payload: payload,
    audioData: { right: {}, left: {} },
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
    PAD: { l: 60, r: 20, t: 40, b: 50 },
    RC: '#e11d48', // Red-600 (Clinico)
    LC: '#2563eb', // Blue-600 (Clinico)

    init() {
        this.canvas = this.$refs.consultationCanvas;
        this.ctx = this.canvas.getContext('2d');
        
        // Transformar data del payload (DB) a objeto de auditoría local
        this.transformData();

        this.doResize();
        window.addEventListener('resize', () => { 
            this.doResize(); 
            this.draw(); 
        });

        this.draw();
    },

    transformData() {
        const mapArr = (arr) => {
            const out = {};
            (arr || []).forEach(item => {
                if (item.umbral_db !== null) out[item.frecuencia_hz] = item.umbral_db;
            });
            return out;
        };
        this.audioData.right = mapArr(this.payload.audiometry.OD);
        this.audioData.left = mapArr(this.payload.audiometry.OI);
    },

    pW() { return this.CW - this.PAD.l - this.PAD.r; },
    pH() { return this.CH - this.PAD.t - this.PAD.b; },

    getX(hz) {
        hz = Number(hz);
        // Si es una frecuencia exacta de la grilla
        const index = this.FREQS.indexOf(hz);
        if (index !== -1) {
            return this.PAD.l + (index / (this.FREQS.length - 1)) * this.pW();
        }

        // Si es una frecuencia intermedia (interpolación para Tinnitus)
        if (hz < this.FREQS[0]) return this.PAD.l;
        if (hz > this.FREQS[this.FREQS.length - 1]) return this.CW - this.PAD.r;

        for (let i = 0; i < this.FREQS.length - 1; i++) {
            if (hz >= this.FREQS[i] && hz <= this.FREQS[i+1]) {
                const f1 = this.FREQS[i], f2 = this.FREQS[i+1];
                const x1 = this.PAD.l + (i / (this.FREQS.length - 1)) * this.pW();
                const x2 = this.PAD.l + ((i + 1) / (this.FREQS.length - 1)) * this.pW();
                
                // Interpolación logarítmica para mayor precisión visual
                const ratio = (Math.log(hz) - Math.log(f1)) / (Math.log(f2) - Math.log(f1));
                return x1 + ratio * (x2 - x1);
            }
        }
        return this.PAD.l;
    },

    getY(db) { 
        return this.PAD.t + (db - this.DB_MIN) / (this.DB_MAX - this.DB_MIN) * this.pH(); 
    },

    doResize() {
        const w = this.canvas.parentNode.getBoundingClientRect().width || 640;
        this.CW = Math.floor(w);
        this.CH = 480; 
        this.dpr = window.devicePixelRatio || 1;
        this.canvas.width = this.CW * this.dpr;
        this.canvas.height = this.CH * this.dpr;
        this.canvas.style.height = this.CH + 'px';
        this.ctx.setTransform(this.dpr, 0, 0, this.dpr, 0, 0);
    },

    draw() {
        const ctx = this.ctx;
        ctx.clearRect(0, 0, this.CW, this.CH);
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, this.CW, this.CH);

        // 1. Zonas de Tinnitus (Sombras Verticales)
        this.drawTinnitusZones();

        // 2. Banana del habla
        this.drawSpeechBanana();

        // 3. Grilla
        this.drawGrid();

        // 4. Datos de Audiometría
        this.drawEarData(this.audioData.left, this.LC, 'left');
        this.drawEarData(this.audioData.right, this.RC, 'right');
    },

    drawTinnitusZones() {
        const zones = this.payload.tinnitus_zones;
        if (!zones) return;

        const drawCol = (hz, color, label) => {
            const x = this.getX(hz);
            this.ctx.save();
            this.ctx.fillStyle = color;
            // 32px de ancho para las columnas de tinnitus
            this.ctx.fillRect(x - 16, this.PAD.t, 32, this.pH());
            this.ctx.restore();
        };

        (zones.OD || []).forEach(z => drawCol(z.clinical_hz, 'rgba(225, 29, 72, 0.08)', 'Tinitus OD'));
        (zones.OI || []).forEach(z => drawCol(z.clinical_hz, 'rgba(37, 99, 235, 0.08)', 'Tinitus OI'));
    },

    drawSpeechBanana() {
        const soundsData = [
            { hz: 250, min: 30, max: 50, sounds: ["u", "o", "m", "z"] },
            { hz: 500, min: 25, max: 45, sounds: ["a", "i", "e", "j"] },
            { hz: 1000, min: 20, max: 45, sounds: ["b", "d", "g", "r"] },
            { hz: 2000, min: 20, max: 50, sounds: ["ch", "sh", "k", "t"] },
            { hz: 4000, min: 25, max: 55, sounds: ["s", "f", "th"] },
            { hz: 6000, min: 35, max: 60, sounds: ["agudos"] }
        ];

        this.ctx.beginPath();
        soundsData.forEach((d, i) => {
            const x = this.getX(d.hz), y = this.getY(d.min);
            i === 0 ? this.ctx.moveTo(x, y) : this.ctx.lineTo(x, y);
        });
        [...soundsData].reverse().forEach((d) => this.ctx.lineTo(this.getX(d.hz), this.getY(d.max)));
        this.ctx.closePath();
        
        this.ctx.fillStyle = 'rgba(245, 158, 11, 0.12)';
        this.ctx.fill();
        this.ctx.strokeStyle = 'rgba(245, 158, 11, 0.2)';
        this.ctx.lineWidth = 1;
        this.ctx.stroke();

        // 2.1 Fonemas/Letras dentro de la banana
        this.ctx.fillStyle = 'rgba(180, 130, 20, 0.7)';
        this.ctx.font = '500 11px system-ui';
        this.ctx.textAlign = 'center';
        this.ctx.textBaseline = 'middle';
        soundsData.forEach(d => {
            const x = this.getX(d.hz);
            const y = this.getY((d.min + d.max) / 2);
            this.ctx.fillText(d.sounds.join(' '), x, y);
        });
    },

    drawGrid() {
        const ctx = this.ctx;
        const gc = 'rgba(0,0,0,0.06)';
        const gs = 'rgba(0,0,0,0.12)';

        // Horizontales
        for (let db = this.DB_MIN; db <= this.DB_MAX; db += 10) {
            const y = this.getY(db);
            ctx.beginPath();
            ctx.moveTo(this.PAD.l, y);
            ctx.lineTo(this.CW - this.PAD.r, y);
            ctx.strokeStyle = db === 0 ? gs : gc;
            ctx.lineWidth = db === 0 ? 1 : 0.5;
            ctx.stroke();
            
            ctx.fillStyle = '#64748b';
            ctx.font = '10px system-ui';
            ctx.textAlign = 'right';
            ctx.fillText(db, this.PAD.l - 12, y + 3);
        }

        // Verticales
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
                ctx.fillStyle = '#334155';
                ctx.font = 'bold 11px system-ui';
                ctx.textAlign = 'center';
                ctx.fillText(this.FREQ_LABELS[hz], x, this.CH - this.PAD.b + 20);
            }
        });
    },

    drawEarData(earData, color, earType) {
        const freqs = Object.keys(earData)
            .map(Number)
            .filter(f => this.FREQS.includes(f))
            .sort((a, b) => a - b);
            
        if (freqs.length === 0) return;

        const ctx = this.ctx;
        ctx.beginPath();
        ctx.strokeStyle = color;
        ctx.lineWidth = 2.5;

        for (let i = 0; i < freqs.length; i++) {
            const hz = freqs[i];
            const x = this.getX(hz);
            const y = this.getY(earData[hz]);
            
            if (i === 0) {
                ctx.moveTo(x, y); 
            } else {
                const prevHz = freqs[i - 1];
                let hasGap = false;
                for (let f of this.FREQ_SHOW) {
                    if (f > prevHz && f < hz) { hasGap = true; break; }
                }

                if (hasGap) {
                    ctx.moveTo(x, y);
                } else {
                    ctx.lineTo(x, y); 
                }
            }
        }
        ctx.stroke();

        freqs.forEach(hz => {
            const x = this.getX(hz), y = this.getY(earData[hz]);
            if (earType === 'right') this.drawO(x, y, color); else this.drawXSym(x, y, color);
        });
    },

    drawO(x, y, col) {
        this.ctx.beginPath(); 
        this.ctx.arc(x, y, 5, 0, Math.PI * 2);
        this.ctx.fillStyle = '#fff'; this.ctx.fill();
        this.ctx.strokeStyle = col; this.ctx.lineWidth = 2; this.ctx.stroke();
    },

    drawXSym(x, y, col) {
        const s = 4.5;
        this.ctx.beginPath(); 
        this.ctx.arc(x, y, 5, 0, Math.PI * 2); 
        this.ctx.fillStyle = '#fff'; this.ctx.fill();
        this.ctx.beginPath(); 
        this.ctx.moveTo(x - s, y - s); this.ctx.lineTo(x + s, y + s);
        this.ctx.moveTo(x + s, y - s); this.ctx.lineTo(x - s, y + s);
        this.ctx.strokeStyle = col; this.ctx.lineWidth = 2; this.ctx.stroke();
    }
}));
</script>
@endscript
@endonce
