@once
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endonce

@once
@script
<script>
window.initConsultationChart = function(container, payload) {
    if (typeof Chart === 'undefined') {
        setTimeout(() => window.initConsultationChart(container, payload), 50);
        return;
    }

    const canvas = container.querySelector('canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    // Limpiar instancia previa si existe
    const existingChart = Chart.getChart(canvas);
    if (existingChart) {
        existingChart.destroy();
    }

    // 1. Preparar datos de Audiometría con "saltos" clínicos
    const parseAudioData = (dataArray) => {
        const standardFreqs = [125, 250, 500, 750, 1000, 1500, 2000, 3000, 4000, 6000, 8000];
        const dataMap = {};
        (dataArray || []).forEach(item => {
            if (item.umbral_db !== null) dataMap[item.frecuencia_hz] = item.umbral_db;
        });

        const measuredFreqs = Object.keys(dataMap).map(Number).sort((a, b) => a - b);
        if (measuredFreqs.length === 0) return [];

        const minMeasured = measuredFreqs[0];
        const maxMeasured = measuredFreqs[measuredFreqs.length - 1];

        return standardFreqs
            .filter(f => f >= minMeasured && f <= maxMeasured)
            .map(f => ({
                x: f,
                y: dataMap[f] !== undefined ? dataMap[f] : null
            }));
    };

    const dataOD = parseAudioData(payload.audiometry.OD);
    const dataOI = parseAudioData(payload.audiometry.OI);

    // 2. Extraer frecuencias de Tinnitus
    const getTinnitusHz = (zonesArray) => (zonesArray || []).map(z => z.clinical_hz);
    const tinnitusOD = getTinnitusHz(payload.tinnitus_zones.OD);
    const tinnitusOI = getTinnitusHz(payload.tinnitus_zones.OI);

    // 3. Plugin para la Banana del Habla
    const speechBananaPlugin = {
        id: 'speechBanana',
        beforeDatasetsDraw(chart) {
            const { ctx, scales: { x, y } } = chart;
            const soundsData = [
                { hz: 250, min: 30, max: 50, sounds: ["u", "o", "m", "z"] },
                { hz: 500, min: 25, max: 45, sounds: ["a", "i", "e", "j"] },
                { hz: 1000, min: 20, max: 45, sounds: ["b", "d", "g", "r"] },
                { hz: 2000, min: 20, max: 50, sounds: ["ch", "sh", "k", "t"] },
                { hz: 4000, min: 25, max: 55, sounds: ["s", "f", "th"] },
                { hz: 6000, min: 35, max: 60, sounds: ["agudos"] }
            ];

            ctx.save();
            ctx.beginPath();
            soundsData.forEach((d, i) => {
                const px = x.getPixelForValue(d.hz);
                const py = y.getPixelForValue(d.min);
                i === 0 ? ctx.moveTo(px, py) : ctx.lineTo(px, py);
            });
            [...soundsData].reverse().forEach((d) => {
                ctx.lineTo(x.getPixelForValue(d.hz), y.getPixelForValue(d.max));
            });
            ctx.closePath();
            ctx.fillStyle = 'rgba(251, 191, 36, 0.12)';
            ctx.fill();
            ctx.strokeStyle = 'rgba(251, 191, 36, 0.3)';
            ctx.lineWidth = 1;
            ctx.stroke();

            ctx.fillStyle = 'rgba(180, 130, 20, 0.6)';
            ctx.font = '500 10px system-ui';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            soundsData.forEach(d => {
                const px = x.getPixelForValue(d.hz);
                const py = y.getPixelForValue((d.min + d.max) / 2);
                ctx.fillText(d.sounds.join(' '), px, py);
            });
            ctx.restore();
        }
    };

    // 4. Plugin para Tinnitus (Sombras)
    const tinnitusZonesPlugin = {
        id: 'tinnitusZones',
        beforeDatasetsDraw(chart) {
            const { ctx, chartArea: { top, bottom, left, right }, scales: { x } } = chart;
            const drawZone = (hz, color) => {
                const xPos = x.getPixelForValue(hz);
                if (xPos >= left && xPos <= right) {
                    ctx.save();
                    ctx.fillStyle = color;
                    ctx.fillRect(xPos - 15, top, 30, bottom - top);
                    ctx.restore();
                }
            };
            tinnitusOD.forEach(hz => drawZone(hz, 'rgba(239, 68, 68, 0.1)'));
            tinnitusOI.forEach(hz => drawZone(hz, 'rgba(59, 130, 246, 0.1)'));
        }
    };

    // 5. Configuración final
    new Chart(ctx, {
        type: 'line',
        plugins: [speechBananaPlugin, tinnitusZonesPlugin],
        data: {
            datasets: [
                {
                    label: 'OD',
                    data: dataOD,
                    borderColor: '#EF4444',
                    backgroundColor: '#EF4444',
                    pointStyle: 'circle',
                    pointRadius: 6,
                    borderWidth: 2,
                    tension: 0.1,
                    spanGaps: false
                },
                {
                    label: 'OI',
                    data: dataOI,
                    borderColor: '#3B82F6',
                    backgroundColor: '#3B82F6',
                    pointStyle: 'crossRot',
                    pointRadius: 6,
                    borderWidth: 2,
                    tension: 0.1,
                    spanGaps: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    type: 'logarithmic',
                    min: 125,
                    max: 8000,
                    title: { display: true, text: 'Hz' },
                    ticks: {
                        callback: function(v) {
                            const val = [125, 250, 500, 1000, 2000, 4000, 8000];
                            return val.includes(v) ? (v >= 1000 ? (v/1000)+'k' : v) : '';
                        }
                    },
                    grid: { color: '#f4f4f5' }
                },
                y: {
                    reverse: true,
                    min: -10,
                    max: 120,
                    title: { display: true, text: 'dB HL' },
                    ticks: { stepSize: 10 },
                    grid: { color: '#f4f4f5' }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        title: (i) => `${i[0].raw.x} Hz`,
                        label: (i) => `${i.raw.y} dB`
                    }
                }
            }
        }
    });
};
</script>
@endscript
@endonce
