<!-- Chart.js via CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
window.initConsultationChart = function(el, data) {
    const ctx = document.getElementById('consultationChart').getContext('2d');
    
    // Configuración base de datasets
    const datasets = [];

    // 1. Agregar el Heatmap Histórico (Gris muy tenue)
    // Al superponer muchas líneas con opacidad 0.05, las zonas donde coinciden se ven más oscuras
    data.history.forEach((session, index) => {
        datasets.push({
            label: 'Historial',
            data: Object.keys(session).map(f => ({ x: f, y: session[f] })),
            borderColor: 'rgba(148, 163, 184, 0.08)', // Slate-400 con 8% opacidad
            borderWidth: 2,
            pointRadius: 0,
            tension: 0.3,
            fill: false,
            order: 2
        });
    });

    // 2. Agregar Tinnitus Histórico (Puntos de calor azules)
    datasets.push({
        label: 'Tinnitus',
        data: data.tinnitus.map(t => ({ x: t.f, y: t.intensity })),
        backgroundColor: 'rgba(59, 130, 246, 0.4)', // Blue-500
        borderColor: 'rgba(59, 130, 246, 0.8)',
        pointRadius: 6,
        showLine: false,
        order: 1
    });

    // 3. Agregar Sesión Actual (Amarillo Potente)
    datasets.push({
        label: 'Sesión Hoy',
        data: Object.keys(data.current).map(f => ({ x: f, y: data.current[f] })),
        borderColor: '#EAB308', // Yellow-500
        backgroundColor: '#EAB308',
        borderWidth: 4,
        pointRadius: 6,
        pointBackgroundColor: '#FFF',
        pointBorderWidth: 3,
        tension: 0.3,
        fill: false,
        order: 0 // Siempre encima
    });

    new Chart(ctx, {
        type: 'line',
        data: { datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    type: 'category',
                    labels: [250, 500, 1000, 2000, 3000, 4000, 6000, 8000],
                    title: { display: true, text: 'Frecuencia (Hz)', font: { weight: 'bold' } },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                y: {
                    reverse: true, // Audiometría clásica: dB HL invertido
                    min: -10,
                    max: 120,
                    title: { display: true, text: 'Nivel de Audición (dB HL)', font: { weight: 'bold' } },
                    ticks: { stepSize: 10 },
                    grid: { color: 'rgba(0,0,0,0.1)' }
                }
            },
            plugins: {
                legend: { display: false }, // Usamos nuestra leyenda custom en HTML
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            if (context.dataset.label === 'Historial') return null;
                            return `${context.dataset.label}: ${context.raw.y} dB HL`;
                        }
                    }
                }
            }
        }
    });
};
</script>
