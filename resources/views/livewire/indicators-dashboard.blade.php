<?php

use Livewire\Volt\Component;
use App\Models\TinnitusProfile;

new class extends Component
{
    public $patientId;
    public $profile;

    public function mount($patientId = 1)
    {
        $this->patientId = $patientId;
        $this->profile = TinnitusProfile::where('patient_id', $this->patientId)->latest()->first();
    }

    public function getIntensityNumber($text) {
        $map = ['Mínimo' => 20, 'Leve' => 40, 'Moderado' => 60, 'Intenso' => 80, 'Muy intenso' => 100];
        return $map[$text] ?? 0;
    }
}; ?>

<div class="indicators-dashboard space-y-8" x-data>
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 italic">Indicadores Clínicos</h2>
            <p class="text-sm text-zinc-500">Estado biopsicosocial del Gemelo Digital.</p>
        </div>
        
        <div></div>
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
                            stroke-dashoffset="{{ 314 - (314 * ($profile->reliability_index ?? 0) / 100) }}" 
                            class="{{ ($profile->reliability_index ?? 0) >= 70 ? 'text-emerald-500' : 'text-amber-500' }}"
                            stroke-linecap="round" />
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-2xl font-black text-zinc-900 dark:text-white">{{ $profile->reliability_index ?? 0 }}%</span>
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
                        <span class="text-zinc-900 dark:text-white">{{ $profile->sleep_quality ?? 0 }}/5</span>
                    </div>
                    <div class="h-1.5 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                        @php
                            $sleepPercent = [1 => 'w-[20%]', 2 => 'w-[40%]', 3 => 'w-[60%]', 4 => 'w-[80%]', 5 => 'w-full'];
                            $sleepWidth = $sleepPercent[$profile->sleep_quality ?? 0] ?? 'w-0';
                        @endphp
                        <div class="h-full bg-blue-500 {{ $sleepWidth }}"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-[10px] font-bold mb-1">
                        <span class="text-zinc-500 uppercase">Estrés</span>
                        <span class="text-zinc-900 dark:text-white">{{ $profile->stress_level ?? 0 }}/5</span>
                    </div>
                    <div class="h-1.5 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                        @php
                            $stressPercent = [1 => 'w-[20%]', 2 => 'w-[40%]', 3 => 'w-[60%]', 4 => 'w-[80%]', 5 => 'w-full'];
                            $stressWidth = $stressPercent[$profile->stress_level ?? 0] ?? 'w-0';
                        @endphp
                        <div class="h-full bg-amber-500 {{ $stressWidth }}"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Percepción EVA -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-6">
            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">Intensidad EVA</span>
            <div class="flex flex-col gap-4">
                @if($profile)
                <div>
                    <div class="flex justify-between text-[9px] font-bold mb-1 uppercase tracking-tighter">
                        <span class="text-red-600">Oído Derecho</span>
                        <span class="text-zinc-900 dark:text-white">{{ $profile->right_ear_intensity }}</span>
                    </div>
                    <div class="flex gap-0.5 h-2">
                        @php $rightNum = $this->getIntensityNumber($profile->right_ear_intensity); @endphp
                        @for ($i = 1; $i <= 10; $i++)
                            <div class="flex-1 rounded-px {{ $rightNum >= ($i * 10) ? 'bg-red-500' : 'bg-red-100 dark:bg-red-900/10' }}"></div>
                        @endfor
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-[9px] font-bold mb-1 uppercase tracking-tighter">
                        <span class="text-blue-600">Oído Izquierdo</span>
                        <span class="text-zinc-900 dark:text-white">{{ $profile->left_ear_intensity }}</span>
                    </div>
                    <div class="flex gap-0.5 h-2">
                        @php $leftNum = $this->getIntensityNumber($profile->left_ear_intensity); @endphp
                        @for ($i = 1; $i <= 10; $i++)
                            <div class="flex-1 rounded-px {{ $leftNum >= ($i * 10) ? 'bg-blue-500' : 'bg-blue-100 dark:bg-blue-900/10' }}"></div>
                        @endfor
                    </div>
                </div>
                @else
                <p class="text-[10px] text-zinc-400 italic">No hay datos de intensidad.</p>
                @endif
            </div>
        </div>

        <!-- 4. Contexto -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest mb-4 block">Contexto Clínico</span>
            <div class="flex flex-wrap gap-1.5">
                <span class="px-1.5 py-0.5 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded text-[9px] font-bold text-zinc-600 dark:text-zinc-400">Perfil Activo</span>
                @if($profile->affected_ears ?? false)
                    <span class="px-1.5 py-0.5 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded text-[9px] font-bold text-zinc-600 dark:text-zinc-400">{{ $profile->affected_ears === 'ambos' ? 'Bilateral' : ($profile->affected_ears === 'OD' ? 'Derecho' : 'Izquierdo') }}</span>
                @endif
            </div>
            <div class="mt-4 pt-4 border-t border-zinc-100 dark:border-zinc-800 text-[10px] text-zinc-500 space-y-1">
                <p>Última actualización: {{ $profile->created_at->format('d/m/Y') }}</p>
                <p>{{ $profile->created_at->diffForHumans() }}</p>
            </div>
        </div>
    </div>

    <!-- Recomendaciones -->
    <div class="p-8 bg-indigo-600 rounded-3xl text-white">
        <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
            <flux:icon.sparkles variant="mini" />
            Intervención Sugerida
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            @forelse($profile->recommendations ?? [] as $rec)
                <div class="flex gap-3 text-sm">
                    <!-- Toma el color que guardamos, o usa indigo por defecto -->
                    <div class="w-2 h-2 rounded-full mt-1.5 shrink-0" :style="{ backgroundColor: '{{ $rec['c'] ?? '#818cf8' }}' }"></div>
                    <!-- Imprime el texto de la recomendación -->
                    <p>{{ $rec['t'] ?? $rec }}</p>
                </div>
            @empty
                <p class="text-indigo-200">No hay recomendaciones disponibles para este perfil.</p>
            @endforelse
        </div>
    </div>
</div>
