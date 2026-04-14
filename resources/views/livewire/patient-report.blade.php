<?php

use Livewire\Volt\Component;
use App\Models\Patient;
use App\Models\PatientSession;

new class extends Component
{
    public $patientId;
    public $sessions;
    public $latestSessionData = [];

    public function mount($patientId = null)
    {
        $this->sessions = collect();
        $this->patientId = $patientId;
        if ($this->patientId) {
            $this->loadData();
        }
    }

    public function loadData()
    {
        $patient = Patient::with(['sessions.audiometryValues', 'sessions.calibrationResults'])->find($this->patientId);
        if (!$patient) return;

        $this->sessions = $patient->sessions->sortByDesc('created_at');
        $firstSession = $this->sessions->first();
        
        // Prepare data for Chart.js
        if ($firstSession) {
            $this->latestSessionData = [
                'od_air' => $this->formatForChart($firstSession, 'OD', 'air'),
                'oi_air' => $this->formatForChart($firstSession, 'OI', 'air'),
                'tinnitus' => $this->formatTinnitusForChart($firstSession),
            ];
        }
    }

    protected function formatForChart($session, $ear, $type)
    {
        if (!$session) return [];
        return $session->audiometryValues
            ->where('ear', $ear)
            ->where('type', $type)
            ->map(fn($v) => ['x' => (int)$v->frequency, 'y' => (int)$v->db_level])
            ->values()
            ->toArray();
    }

    protected function formatTinnitusForChart($session)
    {
        if (!$session) return [];
        return $session->calibrationResults
            ->map(fn($v) => ['x' => (int)$v->frequency_hz, 'y' => (int)$v->db_sl, 'label' => $v->layer_id])
            ->values()
            ->toArray();
    }
}; ?>

<div class="report-container p-6 bg-white rounded-xl shadow-sm border border-slate-200">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-slate-800">Audiograma y Gemelo Digital</h2>
        <div class="text-sm text-slate-500">Historial de Calibraciones</div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-slate-50 p-4 rounded-lg border border-slate-100">
                <canvas id="audiogramChart" height="400"></canvas>
            </div>
        </div>

        <div class="space-y-4">
            <h3 class="font-semibold text-slate-700">Sesiones Recientes</h3>
            <div class="divide-y divide-slate-100">
                @foreach($sessions as $session)
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-slate-800">{{ $session->created_at->format('d/m/Y H:i') }}</div>
                            <div class="text-xs text-slate-500 uppercase">{{ $session->type }} session</div>
                        </div>
                        <span class="text-xs px-2 py-1 rounded bg-slate-100 text-slate-600">Ver</span>
                    </div>
                @endforeach
            </div>
            
            <div class="p-4 bg-teal-50 border border-teal-100 rounded-lg mt-6">
                <h4 class="text-sm font-bold text-teal-800 mb-1">Nota Profesional</h4>
                <p class="text-xs text-teal-700 leading-relaxed">
                    La comparación longitudinal permite observar cómo el umbral del tinnitus (gemelo digital) varía respecto al umbral auditivo clínico.
                </p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            const ctx = document.getElementById('audiogramChart').getContext('2d');
            const data = @js($latestSessionData);

            new Chart(ctx, {
                type: 'line',
                data: {
                    datasets: [
                        {
                            label: 'Oído Derecho (OD)',
                            data: data.od_air,
                            borderColor: '#ef4444',
                            backgroundColor: '#ef4444',
                            borderWidth: 2,
                            pointStyle: 'circle',
                            pointRadius: 6,
                            showLine: true,
                            tension: 0
                        },
                        {
                            label: 'Oído Izquierdo (OI)',
                            data: data.oi_air,
                            borderColor: '#3b82f6',
                            backgroundColor: '#3b82f6',
                            borderWidth: 2,
                            pointStyle: 'crossRot',
                            pointRadius: 8,
                            showLine: true,
                            tension: 0
                        },
                        {
                            label: 'Intensidad Tinnitus (dB SL)',
                            data: data.tinnitus,
                            backgroundColor: '#10b981',
                            borderColor: '#10b981',
                            pointStyle: 'star',
                            pointRadius: 8,
                            showLine: false,
                            type: 'scatter'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            type: 'logarithmic',
                            title: { display: true, text: 'Frecuencia (Hz)' },
                            min: 125,
                            max: 8000,
                            ticks: {
                                callback: function(value) {
                                    return [125, 250, 500, 1000, 2000, 4000, 8000].includes(value) ? value : null;
                                }
                            }
                        },
                        y: {
                            reverse: true,
                            min: -10,
                            max: 120,
                            title: { display: true, text: 'Nivel de Audición (dB HL / SL)' },
                            ticks: { stepSize: 10 }
                        }
                    },
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.raw.y + ' dB at ' + context.raw.x + ' Hz';
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</div>