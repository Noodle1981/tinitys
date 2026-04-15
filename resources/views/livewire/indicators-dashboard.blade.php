<?php

use Livewire\Volt\Component;

new class extends Component
{
    public $patientId;

    public function mount($patientId = 1)
    {
        $this->patientId = $patientId;
    }
}; ?>

<div class="indicators-dashboard space-y-8" x-data="{ reliability: 85 }">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 italic">Indicadores Clínicos (BETA Estático)</h2>
            <p class="text-sm text-zinc-500">Estado biopsicosocial del Gemelo Digital (Datos de Control).</p>
        </div>
        
        <div class="px-3 py-1 bg-amber-100 dark:bg-amber-900/40 rounded-full text-[10px] font-bold text-amber-600">
            MODO DE ESTABILIDAD: VALORES DE PRUEBA
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- 1. Confiabilidad -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm flex flex-col items-center">
            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest mb-6">Confiabilidad</span>
            <div class="relative size-28 flex items-center justify-center">
                <svg class="size-full -rotate-90">
                    <circle cx="56" cy="56" r="50" stroke="currentColor" stroke-width="8" fill="transparent" class="text-zinc-100 dark:text-zinc-800" />
                    <circle cx="56" cy="56" r="50" stroke="currentColor" stroke-width="8" fill="transparent" 
                            stroke-dasharray="314" 
                            :stroke-dashoffset="314 - (314 * reliability / 100)" 
                            class="text-emerald-500"
                            stroke-linecap="round" />
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-2xl font-black text-zinc-900 dark:text-white">85%</span>
                </div>
            </div>
        </div>

        <!-- 2. Psicosocial -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4">
            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">Impacto</span>
            
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-[10px] font-bold mb-1">
                        <span class="text-zinc-500 uppercase">Sueño</span>
                        <span class="text-zinc-900 dark:text-white">7/10</span>
                    </div>
                    <div class="h-1.5 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-500" style="width: 70%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-[10px] font-bold mb-1">
                        <span class="text-zinc-500 uppercase">Estrés</span>
                        <span class="text-amber-500 uppercase">Moderado</span>
                    </div>
                    <div class="h-1.5 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                        <div class="h-full bg-amber-500" style="width: 40%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Percepción EVA -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-6">
            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">Intensidad EVA</span>
            <div class="flex flex-col gap-4">
                <div>
                    <div class="flex justify-between text-[9px] font-bold mb-1 uppercase tracking-tighter">
                        <span class="text-red-600">Oído Derecho</span>
                        <span class="text-zinc-900 dark:text-white">65 dB</span>
                    </div>
                    <div class="flex gap-0.5 h-2">
                        @for ($i = 1; $i <= 10; $i++)
                            <div class="flex-1 rounded-px {{ 6.5 >= $i ? 'bg-red-500' : 'bg-red-100 dark:bg-red-900/10' }}"></div>
                        @endfor
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-[9px] font-bold mb-1 uppercase tracking-tighter">
                        <span class="text-blue-600">Oído Izquierdo</span>
                        <span class="text-zinc-900 dark:text-white">42 dB</span>
                    </div>
                    <div class="flex gap-0.5 h-2">
                        @for ($i = 1; $i <= 10; $i++)
                            <div class="flex-1 rounded-px {{ 4.2 >= $i ? 'bg-blue-500' : 'bg-blue-100 dark:bg-blue-900/10' }}"></div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Contexto -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest mb-4 block">Contexto Clínico</span>
            <div class="flex flex-wrap gap-1.5">
                <span class="px-1.5 py-0.5 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded text-[9px] font-bold text-zinc-600 dark:text-zinc-400">Pérdida Auditiva</span>
                <span class="px-1.5 py-0.5 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded text-[9px] font-bold text-zinc-600 dark:text-zinc-400">Bruxismo</span>
            </div>
            <div class="mt-4 pt-4 border-t border-zinc-100 dark:border-zinc-800 text-[10px] text-zinc-500 space-y-1">
                <p>Paciente de Control</p>
                <p>3 años de evolución</p>
            </div>
        </div>
    </div>

    <!-- Recomendaciones -->
    <div class="p-8 bg-indigo-600 rounded-3xl text-white">
        <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
            <flux:icon.sparkles variant="mini" />
            Intervención Sugerida (Demo)
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div class="flex gap-3">
                <div class="w-1.5 h-1.5 rounded-full bg-indigo-300 mt-1.5 shrink-0"></div>
                <p>Protocolo de Enmascaramiento en zona de caída frecuencial.</p>
            </div>
            <div class="flex gap-3">
                <div class="w-1.5 h-1.5 rounded-full bg-indigo-300 mt-1.5 shrink-0"></div>
                <p>Monitoreo trimestral de indicadores biopsicosociales.</p>
            </div>
        </div>
    </div>
</div>
