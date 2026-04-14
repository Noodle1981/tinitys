<?php

use Livewire\Volt\Component;
use App\Models\Patient;
use App\Models\PatientSession;
use App\Models\AudiometryValue;
use App\Models\TinnitusProfile;
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

    public function getMockData()
    {
        // Simulamos 10 sesiones históricas con una curva base de presbiacusia leve
        $history = [];
        $frequencies = [250, 500, 1000, 2000, 3000, 4000, 6000, 8000];
        
        for ($i = 0; $i < 12; $i++) {
            $session = [];
            foreach ($frequencies as $f) {
                // Curva base + ruido aleatorio de +- 10dB para el mapa de calor
                $base = ($f / 1000) * 10; // Cae con la frecuencia
                $session[$f] = round($base + (rand(-10, 10)));
            }
            $history[] = $session;
        }

        // Sesión "Actual" destacada en Amarillo
        $current = [];
        foreach ($frequencies as $f) {
            $base = ($f / 1000) * 12; // Un poco peor que la historia
            $current[$f] = round($base + 5);
        }

        return [
            'history' => $history,
            'current' => $current,
            'tinnitus' => [
                ['f' => 4000, 'intensity' => 60],
                ['f' => 4000, 'intensity' => 55],
                ['f' => 3800, 'intensity' => 65],
                ['f' => 4200, 'intensity' => 50],
            ] // Mock de tinnitus histórico concentrado en 4kHz
        ];
    }
}; ?>

<div class="consultation-root" x-data="{ view: 'both' }">
    @include('partials.consultation-scripts')

    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 italic">Gemelo Digital: Perfil Biográfico</h2>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Comparativa de Sesión Actual vs. Densidad Histórica (Mapa de Calor)</p>
        </div>
        
        <div class="flex bg-zinc-100 dark:bg-zinc-800 p-1 rounded-lg">
            <button @click="view = 'OD'" :class="view === 'OD' ? 'bg-white shadow text-blue-600' : 'text-zinc-500'" class="px-3 py-1.5 text-xs font-bold rounded-md transition-all">Oído Derecho</button>
            <button @click="view = 'both'" :class="view === 'both' ? 'bg-white shadow text-zinc-800' : 'text-zinc-500'" class="px-3 py-1.5 text-xs font-bold rounded-md transition-all">Ambos</button>
            <button @click="view = 'OI'" :class="view === 'OI' ? 'bg-white shadow text-red-600' : 'text-zinc-500'" class="px-3 py-1.5 text-xs font-bold rounded-md transition-all">Oído Izquierdo</button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Panel de Insights -->
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-amber-50 border border-amber-200 p-4 rounded-xl">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-3 h-3 rounded-full bg-[#EAB308]"></div>
                    <span class="text-xs font-bold text-amber-800 uppercase">Estado Actual (Hoy)</span>
                </div>
                <p class="text-sm text-amber-900 font-medium">El umbral de hoy presenta una caída de 5dB respecto al promedio histórico en 4kHz.</p>
            </div>

            <div class="bg-zinc-50 border border-zinc-200 p-4 rounded-xl">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-3 h-3 rounded-full bg-zinc-400"></div>
                    <span class="text-xs font-bold text-zinc-600 uppercase">Firma Histórica (Heatmap)</span>
                </div>
                <p class="text-xs text-zinc-500">La "mancha" gris muestra dónde se ubica tu audición el 80% del tiempo. Es tu zona de estabilidad clínica.</p>
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
                    <p class="text-[10px] text-zinc-400 italic">Alta recurrencia en frecuencias agudas coincidente con el drop-off de hoy.</p>
                </div>
            </div>
        </div>

        <!-- El Gran Gráfico -->
        <div class="lg:col-span-3 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm relative overflow-hidden">
            {{-- Fondo de diseño --}}
            <div class="absolute top-0 right-0 p-8 opacity-[0.03] pointer-events-none">
                <svg width="200" height="200" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" stroke="currentColor" stroke-width="0.5" fill="none"/></svg>
            </div>

            <div class="relative h-[500px]" 
                 x-init="initConsultationChart($el, @js($this->getMockData()))">
                <canvas id="consultationChart"></canvas>
            </div>
            
            <div class="mt-6 flex justify-center gap-6">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-1 bg-[#EAB308] rounded-full"></div>
                    <span class="text-[10px] font-bold text-zinc-500 uppercase">Sesión Hoy</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-3 bg-zinc-200 rounded-sm opacity-60"></div>
                    <span class="text-[10px] font-bold text-zinc-500 uppercase">Densidad Histórica</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg width="12" height="12" viewBox="0 0 12 12" class="text-blue-500"><circle cx="6" cy="6" r="4" fill="currentColor"/></svg>
                    <span class="text-[10px] font-bold text-zinc-500 uppercase">Tinnitus Histórico</span>
                </div>
            </div>
        </div>
    </div>
</div>
