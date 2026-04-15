<?php

use Livewire\Volt\Component;
use App\Models\Patient;
use App\Models\PatientSession;
use App\Models\TinnitusMapping;

new class extends Component
{
    public $patientId;
    public $patient;

    public function mount($patientId)
    {
        $this->patientId = $patientId;
        $this->patient = Patient::findOrFail($patientId);
    }

    public function getConsultationData()
    {
        $payload = [
            'audiometry' => ['OD' => [], 'OI' => []],
            'tinnitus_zones' => ['OD' => [], 'OI' => []]
        ];

        // 1. Extraer Última Audiometría Real
        $lastAudio = PatientSession::where('patient_id', $this->patientId)
                        ->whereNotNull('audiometry_data')
                        ->latest()
                        ->first();
        
        if ($lastAudio) {
            $data = $lastAudio->audiometry_data ?? [];
            $payload['audiometry']['OD'] = $data['oido_derecho'] ?? [];
            $payload['audiometry']['OI'] = $data['oido_izquierdo'] ?? [];
        }

        // 2. Extraer Último Mapeo de Tinnitus Analítico
        $lastMapping = TinnitusMapping::where('patient_id', $this->patientId)->latest()->first();
        if ($lastMapping) {
            $left = $lastMapping->left_layers_config ?? [];
            $right = $lastMapping->right_layers_config ?? [];

            // Asegurar que se procesen como arrays si vienen como JSON string
            $leftArr = is_string($left) ? json_decode($left, true) : $left;
            $rightArr = is_string($right) ? json_decode($right, true) : $right;

            // Solo enviar los que contengan la variable clinical_hz
            $payload['tinnitus_zones']['OI'] = array_values(array_filter($leftArr ?? [], fn($l) => isset($l['clinical_hz'])));
            $payload['tinnitus_zones']['OD'] = array_values(array_filter($rightArr ?? [], fn($r) => isset($r['clinical_hz'])));
        }

        return $payload;
    }
}; ?>

<div class="consultation-root" x-data="{ view: 'both' }">
    @include('partials.consultation-scripts')

    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4 px-2">
        <div>
            <h2 class="text-3xl font-black text-zinc-900 dark:text-zinc-100 italic tracking-tight uppercase">Gráfica de Tinitus</h2>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Comparativa de Sesión Actual vs. Datos Históricos</p>
        </div>
    </div>


    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Panel de Insights -->
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-amber-50 border border-amber-200 p-4 rounded-xl shadow-sm">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-[#EAB308]"></div>
                    <span class="text-xs font-bold text-amber-800 uppercase tracking-tighter">Estado Actual (Hoy)</span>
                </div>
                <p class="text-sm text-amber-900 font-medium">El umbral de hoy presenta una caída de 5dB respecto al promedio histórico en 4kHz. (modo prueba)</p>
            </div>

            <div class="bg-zinc-50 border border-zinc-200 p-4 rounded-xl shadow-sm">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-zinc-400"></div>
                    <span class="text-xs font-bold text-zinc-600 uppercase tracking-tighter">Áreas de Tinnitus</span>
                </div>
                <p class="text-xs text-zinc-500 leading-relaxed">Las columnas sombreadas en <span class="font-bold text-red-600/70">rojo (TD)</span> y <span class="font-bold text-blue-600/70">azul (TI)</span> indican la ubicación frecuencial del zumbido según tu perfil biográfico.</p>
            </div>


            <div class="bg-white border border-zinc-200 p-4 rounded-xl shadow-sm">
                <h3 class="text-xs font-bold text-zinc-400 uppercase mb-3">Zonas de Tinnitus</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-zinc-600">Frecuencia Habitual</span>
                        <span class="text-xs font-bold">~4.0 kHz</span>
                    </div>
                    <div class="w-full bg-zinc-100 h-2 rounded-full overflow-hidden">
                        <div class="bg-blue-500 h-full" style="width: 85%"></div>
                    </div>
                    <p class="text-[10px] text-zinc-400 italic font-medium">Alta recurrencia en frecuencias agudas coincidente con el drop-off de hoy.</p>
                </div>
            </div>
        </div>

        <!-- El Gran Gráfico -->
        <div class="lg:col-span-3 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm relative overflow-hidden min-h-[500px]">
            {{-- Fondo de diseño --}}
            <div class="absolute top-0 right-0 p-8 opacity-[0.03] pointer-events-none">
                <svg width="200" height="200" viewBox="0 0 100 100" class="text-zinc-900 dark:text-white"><circle cx="50" cy="50" r="40" stroke="currentColor" stroke-width="0.5" fill="none"/></svg>
            </div>

            <div class="relative h-[480px]" 
                 x-init="$nextTick(() => initConsultationChart($el, @js($this->getConsultationData())))">

                <canvas id="consultationChart"></canvas>
            </div>
            
            <div class="mt-8 flex flex-wrap justify-center gap-8 border-t border-zinc-50 dark:border-zinc-800/50 pt-6">
            <div class="flex items-center gap-2.5">
                <div class="w-3 h-3 rounded-full border-2 border-red-500 bg-white shadow-sm"></div>
                <span class="text-[10px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest">OD</span>
            </div>
            <div class="flex items-center gap-2.5">
                <div class="relative w-3 h-3 flex items-center justify-center">
                    <div class="absolute w-full h-0.5 bg-blue-500 rotate-45 rounded-full"></div>
                    <div class="absolute w-full h-0.5 bg-blue-500 -rotate-45 rounded-full"></div>
                </div>
                <span class="text-[10px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest">OI</span>
            </div>
            <div class="flex items-center gap-2.5">
                <div class="w-3.5 h-3.5 bg-red-500/20 border border-red-500/30 rounded-sm shadow-inner"></div>
                <span class="text-[10px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest">TD</span>
            </div>
            <div class="flex items-center gap-2.5">
                <div class="w-3.5 h-3.5 bg-blue-500/20 border border-blue-500/30 rounded-sm shadow-inner"></div>
                <span class="text-[10px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest">TI</span>
            </div>
        </div>

        </div>
    </div>
</div>
