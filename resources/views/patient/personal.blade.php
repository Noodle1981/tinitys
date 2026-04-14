<x-layouts::patient :patientId="$patientId" title="Perfil del Paciente">
    <div class="space-y-6">
        <div class="bg-white dark:bg-zinc-900 shadow-sm sm:rounded-lg p-8 border border-zinc-200 dark:border-zinc-700">
            <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-6">Datos Personales e Identificación</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <p class="text-xs font-semibold text-zinc-500 uppercase tracking-tighter">DNI / Documento</p>
                    <p class="text-lg font-medium text-zinc-900 dark:text-white mt-1">{{ $patient->dni }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-zinc-500 uppercase tracking-tighter">Fecha de Nacimiento</p>
                    <p class="text-lg font-medium text-zinc-900 dark:text-white mt-1">{{ $patient->birth_date ? $patient->birth_date->format('d/m/Y') : 'No registrado' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-zinc-500 uppercase tracking-tighter">Género</p>
                    <p class="text-lg font-medium text-zinc-900 dark:text-white mt-1">{{ $patient->gender }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-zinc-500 uppercase tracking-tighter">Ocupación</p>
                    <p class="text-lg font-medium text-zinc-900 dark:text-white mt-1">{{ $patient->occupation ?? 'No registrada' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-zinc-500 uppercase tracking-tighter">Edad Actual</p>
                    <p class="text-lg font-medium text-zinc-900 dark:text-white mt-1">{{ $patient->getAge() }} años</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-zinc-900 shadow-sm sm:rounded-lg p-8 border border-zinc-200 dark:border-zinc-700">
                <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-6">Caracterización del Tinnitus</h2>
                <div class="space-y-4">
                    <div class="flex justify-between border-b border-zinc-100 dark:border-zinc-800 pb-2">
                        <span class="text-sm text-zinc-500">Lateralidad</span>
                        <span class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $patient->laterality }}</span>
                    </div>
                    <div class="flex justify-between border-b border-zinc-100 dark:border-zinc-800 pb-2">
                        <span class="text-sm text-zinc-500">Tipos de Sonido</span>
                        <div class="flex flex-wrap gap-1 justify-end">
                            @if(is_array($patient->sound_type))
                                @foreach($patient->sound_type as $s)
                                    <span class="px-2 py-0.5 bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-[10px] rounded font-semibold uppercase">{{ $s }}</span>
                                @endforeach
                            @else
                                <span class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $patient->sound_type ?? 'N/D' }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex justify-between border-b border-zinc-100 dark:border-zinc-800 pb-2">
                        <span class="text-sm text-zinc-500">Tiempo de Evolución</span>
                        <span class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $patient->evolution_time }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-900 shadow-sm sm:rounded-lg p-8 border border-zinc-200 dark:border-zinc-700">
                <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-6">Antecedentes Médicos</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs font-semibold text-zinc-500 uppercase tracking-tighter mb-2">Comorbilidades</p>
                        <div class="flex flex-wrap gap-2">
                            @if($patient->comorbidities)
                                @foreach($patient->comorbidities as $c)
                                    <span class="px-2 py-1 bg-indigo-50 text-indigo-700 text-xs rounded-full font-medium">{{ $c }}</span>
                                @endforeach
                            @else
                                <span class="text-xs text-zinc-400">Ninguna registrada</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-zinc-500 uppercase tracking-tighter mb-1">Medicamentos</p>
                        <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $patient->medications ?? 'Sin registro' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-zinc-500 uppercase tracking-tighter mb-1">Salud Mental / Sueño</p>
                        <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $patient->mental_health_context ?? 'Sin registro' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-indigo-600 rounded-lg p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold">Estado del Gemelo Digital</h3>
                    <p class="text-sm text-indigo-100 mt-1">
                        {{ $patient->user_id ? 'Vinculado correctamente con la cuenta de ' . $patient->name : 'Este paciente aún no tiene una cuenta vinculada para refinamiento remoto.' }}
                    </p>
                </div>
                @if(!$patient->user_id)
                    <button class="px-4 py-2 bg-white text-indigo-600 rounded font-bold text-sm shadow hover:bg-indigo-50 transition-colors">Vincular Cuenta</button>
                @endif
            </div>
        </div>
    </div>
</x-layouts::patient>
