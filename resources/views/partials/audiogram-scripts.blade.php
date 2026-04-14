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
    CH: 400,
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
        window.addEventListener('resize', () => { this.doResize(); this.draw(); });
        
        // Listener para carga de datos históricos
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
    fX(f) { return this.PAD.l + Math.log(f / 125) / Math.log(8000 / 125) * this.pW(); },
    dY(db) { return this.PAD.t + (db - this.DB_MIN) / (this.DB_MAX - this.DB_MIN) * this.pH(); },

    doResize() {
        const w = this.canvas.parentNode.getBoundingClientRect().width || 640;
        this.CW = Math.floor(w);
        this.CH = Math.round(this.CW * 0.62);
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
        return cx >= this.PAD.l - 15 && cx <= this.CW - this.PAD.r + 10
            && cy >= this.PAD.t - 8 && cy <= this.CH - this.PAD.b + 8;
    },

    snapFreq(cx) {
        let bf = null, bd = Infinity;
        const lim = this.pW() / this.FREQS.length * 0.72;
        this.FREQS.forEach(f => {
            const d = Math.abs(this.fX(f) - cx);
            if (d < bd) { bd = d; bf = f; }
        });
        return bd <= lim ? bf : null;
    },

    snapDb(cy) {
        let db = this.DB_MIN + (cy - this.PAD.t) / this.pH() * (this.DB_MAX - this.DB_MIN);
        return Math.max(this.DB_MIN, Math.min(this.DB_MAX, Math.round(db / 5) * 5));
    },

    onClick(e) {
        if (this.isReadOnly) return; // Bloquear edición

        const [cx, cy] = this.evCoords(e);
        if (!this.inPlot(cx, cy)) return;
        const freq = this.snapFreq(cx);
        if (!freq) return;
        const db = this.snapDb(cy);

        this.hist.push(JSON.parse(JSON.stringify(this.audioData)));
        if (this.hist.length > 60) this.hist.shift();

        if (this.audioData[this.activeEar][freq] !== undefined) {
            delete this.audioData[this.activeEar][freq];
        } else {
            this.audioData[this.activeEar][freq] = db;
        }
        this.draw();
        this.$dispatch('audiogram-updated', this.audioData);
    },

    onMove(e) {
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
        const gc = 'rgba(0,0,0,0.08)';
        const gs = 'rgba(0,0,0,0.16)';
        const tm = '#999';
        const tl = '#334155';

        this.ctx.fillStyle = '#ffffff';
        this.ctx.fillRect(0, 0, this.CW, this.CH);

        // Speech banana
        const soundsData = [
            { hz: 250, min: 30, max: 50, sounds: ["u", "o", "m", "z"] },
            { hz: 500, min: 25, max: 45, sounds: ["a", "i", "e", "j"] },
            { hz: 1000, min: 20, max: 45, sounds: ["b", "d", "g", "r"] },
            { hz: 2000, min: 20, max: 50, sounds: ["ch", "sh", "k", "t"] },
            { hz: 4000, min: 25, max: 55, sounds: ["s", "f", "th"] },
            { hz: 6000, min: 35, max: 60, sounds: ["agudos"] }
        ];

        this.ctx.beginPath();
        // Upper bounds (min_db)
        soundsData.forEach((d, i) => {
            const x = this.fX(d.hz), y = this.dY(d.min);
            i === 0 ? this.ctx.moveTo(x, y) : this.ctx.lineTo(x, y);
        });
        // Lower bounds (max_db) en reversa para cerrar la curva
        [...soundsData].reverse().forEach((d) => this.ctx.lineTo(this.fX(d.hz), this.dY(d.max)));
        this.ctx.closePath();
        
        this.ctx.fillStyle = 'rgba(255, 235, 59, 0.3)';
        this.ctx.fill();
        this.ctx.strokeStyle = 'rgba(255, 235, 59, 0.6)';
        this.ctx.lineWidth = 1;
        this.ctx.stroke();

        // Render phonemes texts
        this.ctx.fillStyle = 'rgba(102, 92, 0, 0.75)'; // Dark yellow/brown for legibility
        this.ctx.font = '500 11px system-ui';
        this.ctx.textAlign = 'center';
        soundsData.forEach((d) => {
            const x = this.fX(d.hz);
            const y = this.dY((d.min + d.max) / 2);
            this.ctx.fillText(d.sounds.join(', '), x, y + 4);
        });

        // Horizontal dB grid + labels
        for (let db = this.DB_MIN; db <= this.DB_MAX; db += 10) {
            const y = this.dY(db);
            this.ctx.beginPath();
            this.ctx.moveTo(this.PAD.l, y);
            this.ctx.lineTo(this.CW - this.PAD.r, y);
            this.ctx.strokeStyle = db === 0 ? gs : gc;
            this.ctx.lineWidth = db === 0 ? 1 : 0.5;
            this.ctx.stroke();
            this.ctx.fillStyle = tm;
            this.ctx.font = '11px system-ui';
            this.ctx.textAlign = 'right';
            this.ctx.fillText(db, this.PAD.l - 8, y + 4);
        }

        // Vertical freq grid + labels
        this.FREQS.forEach(freq => {
            const x = this.fX(freq);
            const show = this.FREQ_SHOW.has(freq);
            this.ctx.beginPath();
            this.ctx.moveTo(x, this.PAD.t);
            this.ctx.lineTo(x, this.CH - this.PAD.b);
            this.ctx.strokeStyle = show ? gs : gc;
            this.ctx.lineWidth = 0.5;
            this.ctx.stroke();
            if (show) {
                this.ctx.fillStyle = tl;
                this.ctx.font = '500 12px system-ui';
                this.ctx.textAlign = 'center';
                this.ctx.fillText(this.FREQ_LABELS[freq], x, this.CH - this.PAD.b + 18);
            }
        });

        // Axis labels
        this.ctx.fillStyle = '#64748b';
        this.ctx.font = '12px system-ui';
        this.ctx.textAlign = 'center';
        this.ctx.fillText('Frecuencia (Hz)', this.PAD.l + this.pW() / 2, this.CH - 6);

        this.ctx.save();
        this.ctx.translate(13, this.PAD.t + this.pH() / 2);
        this.ctx.rotate(-Math.PI / 2);
        this.ctx.fillText('Umbral (dB HL)', 0, 0);
        this.ctx.restore();

        // Plot border
        this.ctx.strokeStyle = 'rgba(0,0,0,0.20)';
        this.ctx.lineWidth = 0.75;
        this.ctx.strokeRect(this.PAD.l, this.PAD.t, this.pW(), this.pH());

        // Data
        this.drawEar('right', this.RC, this.drawO.bind(this));
        this.drawEar('left', this.LC, this.drawXSym.bind(this));

        // Hover ghost
        if (this.hov) {
            const x = this.fX(this.hov.freq), y = this.dY(this.hov.db);
            const col = this.hov.ear === 'right' ? this.RC : this.LC;
            this.ctx.globalAlpha = 0.35;
            if (this.hov.ear === 'right') this.drawO(x, y, col); else this.drawXSym(x, y, col);
            this.ctx.globalAlpha = 1;

            const lbl = `${this.hov.freq >= 1000 ? (this.hov.freq / 1000) + 'k' : this.hov.freq} Hz — ${this.hov.db} dB HL`;
            this.ctx.font = '11px system-ui';
            this.ctx.textAlign = 'left';
            const tw = this.ctx.measureText(lbl).width + 18;
            let tx = x + 12, ty = y - 26;
            if (tx + tw > this.CW - this.PAD.r) tx = x - tw - 8;
            if (ty < this.PAD.t + 4) ty = y + 10;

            this.ctx.fillStyle = 'rgba(248,250,252,0.96)';
            this.roundRect(this.ctx, tx, ty, tw, 21, 4);
            this.ctx.fill();
            this.ctx.strokeStyle = 'rgba(0,0,0,0.12)';
            this.ctx.lineWidth = 0.5;
            this.roundRect(this.ctx, tx, ty, tw, 21, 4);
            this.ctx.stroke();
            this.ctx.fillStyle = '#1e293b';
            this.ctx.fillText(lbl, tx + 9, ty + 14);
        }
    },

    roundRect(ctx, x, y, w, h, r) {
        ctx.beginPath();
        ctx.moveTo(x + r, y);
        ctx.lineTo(x + w - r, y); ctx.arcTo(x + w, y, x + w, y + r, r);
        ctx.lineTo(x + w, y + h - r); ctx.arcTo(x + w, y + h, x + w - r, y + h, r);
        ctx.lineTo(x + r, y + h); ctx.arcTo(x, y + h, x, y + h - r, r);
        ctx.lineTo(x, y + r); ctx.arcTo(x, y, x + r, y, r);
        ctx.closePath();
    },

    drawEar(ear, col, symFn) {
        const pts = this.audioData[ear];
        const fs = Object.keys(pts).map(Number).sort((a, b) => a - b);
        if (!fs.length) return;

        this.ctx.beginPath();
        fs.forEach((f, i) => {
            const x = this.fX(f), y = this.dY(pts[f]);
            i === 0 ? this.ctx.moveTo(x, y) : this.ctx.lineTo(x, y);
        });
        this.ctx.strokeStyle = col;
        this.ctx.lineWidth = 1.5;
        this.ctx.setLineDash([]);
        this.ctx.stroke();

        fs.forEach(f => symFn(this.fX(f), this.dY(pts[f]), col));
    },

    drawO(x, y, col) {
        this.ctx.beginPath(); this.ctx.arc(x, y, 7, 0, Math.PI * 2);
        this.ctx.fillStyle = 'rgba(255,255,255,0.92)'; this.ctx.fill();
        this.ctx.strokeStyle = col; this.ctx.lineWidth = 2; this.ctx.stroke();
    },

    drawXSym(x, y, col) {
        const s = 6.5;
        this.ctx.beginPath(); this.ctx.arc(x, y, 7, 0, Math.PI * 2); // Círculo de fondo para legibilidad
        this.ctx.fillStyle = 'rgba(255,255,255,0.92)'; this.ctx.fill();
        this.ctx.beginPath(); this.ctx.moveTo(x - s, y - s); this.ctx.lineTo(x + s, y + s);
        this.ctx.moveTo(x + s, y - s); this.ctx.lineTo(x - s, y + s);
        this.ctx.strokeStyle = col; this.ctx.lineWidth = 2.5; this.ctx.stroke();
    },

    setEar(ear) {
        this.activeEar = ear;
    },

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
