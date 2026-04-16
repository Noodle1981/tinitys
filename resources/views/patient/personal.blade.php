<x-layouts::patient :patientId="$patientId" title="Perfil del Paciente">
    <div class="space-y-6">
        {{-- Header Seccional --}}
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">{{ __('Ficha Clínica Detallada') }}</h2>
            <div class="flex gap-2">
                <flux:badge color="zinc" size="sm" variant="outline">{{ __('ID: ' . $patient->id) }}</flux:badge>
                <flux:badge color="indigo" size="sm">{{ __('Estado: Activo') }}</flux:badge>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Columna Izquierda: Identificación y Hábitos --}}
            <div class="lg:col-span-1 space-y-6">
                {{-- Identificación --}}
                <flux:card class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <flux:icon.user variant="mini" class="text-zinc-400" />
                        <h3 class="font-bold text-zinc-800 dark:text-zinc-200">{{ __('Identificación Básica') }}</h3>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <p class="text-[10px] uppercase font-bold text-zinc-400 tracking-wider">{{ __('DNI / Documento') }}</p>
                            <p class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $patient->dni }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-[10px] uppercase font-bold text-zinc-400 tracking-wider">{{ __('Nacimiento') }}</p>
                                <p class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $patient->birth_date ? $patient->birth_date->format('d/m/Y') : 'N/D' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-bold text-zinc-400 tracking-wider">{{ __('Género') }}</p>
                                <p class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $patient->gender ?? 'N/D' }}</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-bold text-zinc-400 tracking-wider">{{ __('Ocupación') }}</p>
                            <p class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $patient->occupation ?? 'No registrada' }}</p>
                        </div>
                    </div>
                </flux:card>

                {{-- Hábitos y Calidad de Vida --}}
                <flux:card class="p-6 bg-zinc-50/50 dark:bg-zinc-900/50">
                    <div class="flex items-center gap-3 mb-4">
                        <flux:icon.bolt variant="mini" class="text-amber-500" />
                        <h3 class="font-bold text-zinc-800 dark:text-zinc-200">{{ __('Hábitos y Calidad de Vida') }}</h3>
                    </div>

                    <div class="space-y-5">
                        <x-clinical.level-indicator label="Tabaquismo" :value="$patient->habits?->smoking_level" color="orange" />
                        <x-clinical.level-indicator label="Alcohol" :value="$patient->habits?->alcohol_level" color="blue" />
                        <x-clinical.level-indicator label="Cafeína" :value="$patient->habits?->coffee_level" color="amber" />
                        
                        <flux:separator class="my-2" />
                        
                        <div class="grid grid-cols-2 gap-4">
                            <x-clinical.stat-box label="Sueño (0-10)" :value="$patient->clinicalHistory?->sleep_quality ?? 0" icon="moon" color="indigo" />
                            <x-clinical.stat-box label="Estrés (0-10)" :value="$patient->clinicalHistory?->stress_level ?? 0" icon="fire" color="red" />
                        </div>
                    </div>
                </flux:card>
            </div>

            {{-- Columna Central/Derecha: Datos Clínicos Profundos --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Origen y Sintomatología --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:card class="p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <flux:icon.clock variant="mini" class="text-indigo-500" />
                            <h3 class="font-bold text-zinc-800 dark:text-zinc-200">{{ __('Origen del Cuadro') }}</h3>
                        </div>

                        <div class="space-y-4 text-sm">
                            <div class="flex justify-between">
                                <span class="text-zinc-500">{{ __('Fecha de Inicio') }}</span>
                                <span class="font-semibold">{{ $patient->onsetCause?->onset_date ? $patient->onsetCause?->onset_date->format('M Y') : 'N/D' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500">{{ __('Tipo de Inicio') }}</span>
                                <flux:badge size="sm" variant="subtle" color="zinc">{{ $patient->onsetCause?->onset_type ?? 'N/D' }}</flux:badge>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500">{{ __('Intensidad Inicial (EVA)') }}</span>
                                <span class="font-bold text-indigo-600">{{ $patient->onsetCause?->initial_intensity_eva ?? 0 }}/10</span>
                            </div>
                            
                            <div class="mt-4 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                                <p class="text-[10px] uppercase font-bold text-zinc-400 mb-2">{{ __('Gatillos Identificados') }}</p>
                                <div class="flex flex-wrap gap-1">
                                    @php 
                                        $triggers = [
                                            'Trauma Craneal' => $patient->onsetCause?->has_head_trauma,
                                            'Meningitis' => $patient->onsetCause?->has_meningitis,
                                            'Meniere' => $patient->onsetCause?->has_meniere,
                                            'Parálisis Facial' => $patient->onsetCause?->has_facial_paralysis,
                                            'Vértigo' => $patient->onsetCause?->has_vertigo,
                                        ];
                                    @endphp
                                    @forelse(array_filter($triggers) as $label => $active)
                                        <flux:badge color="red" size="sm" variant="subtle">{{ $label }}</flux:badge>
                                    @empty
                                        <span class="text-xs text-zinc-400 italic">{{ __('Ningún gatillo severo reportado') }}</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </flux:card>

                    <flux:card class="p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <flux:icon.speaker-wave variant="mini" class="text-blue-500" />
                            <h3 class="font-bold text-zinc-800 dark:text-zinc-200">{{ __('Exposición y Entorno') }}</h3>
                        </div>

                        <div class="space-y-4 text-sm">
                            <x-clinical.level-indicator label="Ruido Laboral" :value="$patient->exposure?->occupational_noise_level" color="blue" />
                            <x-clinical.level-indicator label="Ruido Recreativo" :value="$patient->exposure?->leisure_noise_level" color="indigo" />
                            
                            <flux:separator class="my-2" />
                            
                            <div class="grid grid-cols-2 gap-4 text-center">
                                <div>
                                    <p class="text-[9px] uppercase font-bold text-zinc-400 mb-1">{{ __('Exposición') }}</p>
                                    <p class="text-sm font-bold text-zinc-700 dark:text-zinc-300">{{ $patient->exposure?->noise_duration_years ?? 0 }} años</p>
                                </div>
                                <div>
                                    <p class="text-[9px] uppercase font-bold text-zinc-400 mb-1">{{ __('Protección') }}</p>
                                    <flux:badge size="sm" :color="$patient->exposure?->protection_used ? 'green' : 'red'">
                                        {{ $patient->exposure?->protection_used ? 'Sí' : 'No' }}
                                    </flux:badge>
                                </div>
                            </div>
                        </div>
                    </flux:card>
                </div>

                {{-- Historia Médica y Ototoxicidad --}}
                <flux:card class="p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <flux:icon.beaker variant="mini" class="text-emerald-500" />
                        <h3 class="font-bold text-zinc-800 dark:text-zinc-200">{{ __('Análisis de Comorbilidades y Ototoxicidad') }}</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <p class="text-[10px] uppercase font-bold text-zinc-400 mb-3 tracking-widest">{{ __('Antecedentes Médicos') }}</p>
                            <div class="space-y-2">
                                @php
                                    $medical = [
                                        'Hipertensión' => $patient->clinicalHistory?->has_hypertension,
                                        'Insuf. Cardíaca' => $patient->clinicalHistory?->has_heart_failure,
                                        'Reumatismo' => $patient->clinicalHistory?->has_rheumatism,
                                        'Antecedente Familiar' => $patient->clinicalHistory?->family_hearing_loss,
                                    ];
                                @endphp
                                @foreach($medical as $label => $val)
                                    <div class="flex items-center gap-2">
                                        <flux:icon.check-circle variant="mini" class="{{ $val ? 'text-emerald-500' : 'text-zinc-200 dark:text-zinc-800' }}" />
                                        <span class="text-xs {{ $val ? 'text-zinc-800 dark:text-zinc-200 font-medium' : 'text-zinc-400' }}">{{ $label }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <p class="text-[10px] uppercase font-bold text-zinc-400 mb-3 tracking-widest">{{ __('Potencial Ototóxico') }}</p>
                            <div class="space-y-2">
                                @php
                                    $ototoxic = [
                                        'Aminoglucósidos' => $patient->clinicalHistory?->uses_aminoglycosides,
                                        'Salicilatos (Aspirina)' => $patient->clinicalHistory?->uses_salicylates,
                                        'Diuréticos de asa' => $patient->clinicalHistory?->uses_loop_diuretics,
                                        'Quinina' => $patient->clinicalHistory?->uses_quinine,
                                    ];
                                @endphp
                                @foreach($ototoxic as $label => $val)
                                    <div class="flex items-center gap-2">
                                        <flux:icon.exclamation-circle variant="mini" class="{{ $val ? 'text-red-500 animate-pulse' : 'text-zinc-200 dark:text-zinc-800' }}" />
                                        <span class="text-xs {{ $val ? 'text-red-600 font-bold' : 'text-zinc-400' }}">{{ $label }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-4 border-t border-zinc-100 dark:border-zinc-800 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-[10px] uppercase font-bold text-zinc-400 mb-1">{{ __('Medicación Actual') }}</p>
                            <p class="text-xs text-zinc-600 dark:text-zinc-400 italic">
                                {{ $patient->clinicalHistory?->medications ?: 'Ninguna registrada' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-bold text-zinc-400 mb-1">{{ __('Contexto Salud Mental') }}</p>
                            <p class="text-xs text-zinc-600 dark:text-zinc-400 italic">
                                {{ $patient->clinicalHistory?->mental_health_context ?: 'Sin observaciones' }}
                            </p>
                        </div>
                    </div>
                </flux:card>
            </div>
        </div>
    </div>
</x-layouts::patient>
