<div x-show="tab === 'history'" class="space-y-6" style="display: none;">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Sección: Infecciones y Enfermedades de Inicio --}}
        <div class="space-y-3">
            <flux:label class="font-bold text-zinc-900 dark:text-zinc-200 uppercase text-[10px] tracking-widest">{{ __('Enfermedades Infecciosas / Trauma') }}</flux:label>
            <div class="grid grid-cols-1 gap-1 p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200 dark:border-zinc-700">
                <flux:checkbox label="{{ __('Traumatismo Craneal') }}" wire:model="form.has_head_trauma" />
                <flux:checkbox label="{{ __('Meningitis') }}" wire:model="form.has_meningitis" />
                <flux:checkbox label="{{ __('Enfermedad de Meniere') }}" wire:model="form.has_meniere" />
                <flux:checkbox label="{{ __('Parálisis Facial') }}" wire:model="form.has_facial_paralysis" />
                <flux:checkbox label="{{ __('Herpes Zoster') }}" wire:model="form.has_herpes_zoster" />
                <flux:checkbox label="{{ __('Parotiditis (Paperas)') }}" wire:model="form.has_mumps" />
                <flux:checkbox label="{{ __('Sarampión / Rubeola / Tifus') }}" wire:model="form.has_measles" />
                <flux:checkbox label="{{ __('Vértigo recurrente') }}" wire:model="form.has_vertigo" />
            </div>
        </div>

        {{-- Sección: Antecedentes Otológicos --}}
        <div class="space-y-3">
            <flux:label class="font-bold text-zinc-900 dark:text-zinc-200 uppercase text-[10px] tracking-widest">{{ __('Antecedentes Otológicos') }}</flux:label>
            <div class="grid grid-cols-1 gap-1 p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200 dark:border-zinc-700">
                <flux:checkbox label="{{ __('Otalgia (Dolor)') }}" wire:model="form.has_otalgia" />
                <flux:checkbox label="{{ __('Otorrea (Líquido)') }}" wire:model="form.has_otorrhea" />
                <flux:checkbox label="{{ __('Otitis Serosa') }}" wire:model="form.has_serous_otitis" />
                <flux:checkbox label="{{ __('Obstrucción / Tapones') }}" wire:model="form.has_ear_obstruction" />
                <flux:checkbox label="{{ __('Lesión Cadena Huesecillos') }}" wire:model="form.has_ossicular_chain_lesion" />
                <flux:checkbox label="{{ __('Problemas de Audición Familiar') }}" wire:model="form.family_hearing_loss" />
            </div>
        </div>
    </div>

    {{-- Sección Especial: Ototóxicos y Condiciones Crónicas --}}
    <div class="bg-rose-50/50 dark:bg-rose-900/10 p-6 rounded-2xl border border-rose-100 dark:border-rose-900/40">
        <div class="flex items-center gap-2 mb-4 font-bold text-xs uppercase tracking-wider text-rose-600 dark:text-rose-400">
            <flux:icon.beaker class="size-4" />
            {{ __('Indicadores de Ototoxicidad (Fármacos y Proxies)') }}
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            {{-- Fármacos --}}
            <div class="space-y-3">
                <p class="text-[10px] font-bold text-rose-800 dark:text-rose-300 uppercase tracking-tighter">{{ __('Fármacos (Tóxicos Laberínticos)') }}</p>
                <div class="space-y-2">
                    <flux:checkbox label="{{ __('Antibióticos (Aminoglucósidos)') }}" wire:model="form.uses_aminoglycosides" />
                    <flux:checkbox label="{{ __('Salicilatos (Dosis altas Aspirina)') }}" wire:model="form.uses_salicylates" />
                    <flux:checkbox label="{{ __('Diuréticos de asa (Furosemida)') }}" wire:model="form.uses_loop_diuretics" />
                    <flux:checkbox label="{{ __('Quininas (Antipalúdicos)') }}" wire:model="form.uses_quinine" />
                </div>
            </div>

            {{-- Condiciones --}}
            <div class="space-y-3">
                <p class="text-[10px] font-bold text-rose-800 dark:text-rose-300 uppercase tracking-tighter">{{ __('Condiciones Tratadas / Antecedentes') }}</p>
                <div class="space-y-2">
                    <flux:checkbox label="{{ __('Paludismo / Malaria') }}" wire:model="form.has_malaria" />
                    <flux:checkbox label="{{ __('Reumatismo / Cefaleas') }}" wire:model="form.has_rheumatism" />
                    <flux:checkbox label="{{ __('Tuberculosis') }}" wire:model="form.has_tuberculosis" />
                    <flux:checkbox label="{{ __('Insuficiencia Cardíaca / HT') }}" wire:model="form.has_heart_failure" />
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Sección: Bienestar y Sueño --}}
        <div class="space-y-3">
            <flux:label class="font-bold text-zinc-900 dark:text-zinc-200 uppercase text-[10px] tracking-widest">{{ __('Estructura de Sueño y Bienestar') }}</flux:label>
            <div class="p-4 bg-indigo-50/30 dark:bg-indigo-900/10 rounded-xl border border-indigo-100 dark:border-indigo-800 space-y-4">
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <flux:label class="text-xs">{{ __('Calidad del Sueño') }}</flux:label>
                        <span class="text-xs font-bold text-indigo-600">{{ $form->sleep_quality }} / 10</span>
                    </div>
                    <input type="range" wire:model.live="form.sleep_quality" min="0" max="10" step="1" class="w-full h-1.5 bg-zinc-200 dark:bg-zinc-700 rounded-lg appearance-none cursor-pointer accent-indigo-600" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <flux:input label="{{ __('Horas Sueño Prom.') }}" type="number" wire:model="form.avg_sleep_hours" placeholder="Ej: 7" size="sm" />
                    <div class="flex items-end pb-2">
                        <flux:checkbox label="{{ __('Hace Guardias/Turnos') }}" wire:model="form.has_night_shifts" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Salud Mental Contexto --}}
        <div class="space-y-3">
            <flux:label class="font-bold text-zinc-900 dark:text-zinc-200 uppercase text-[10px] tracking-widest">{{ __('Contexto Psico-emocional') }}</flux:label>
            <flux:textarea label="{{ __('Notas / Diagnósticos') }}" wire:model="form.mental_health_context" rows="3" placeholder="{{ __('Ej: Ansiedad generalizada, estrés laboral alto...') }}" />
        </div>
    </div>

    <div class="pt-2">
        <flux:textarea label="{{ __('Notas sobre Medicación Especial') }}" wire:model="form.medications" rows="2" />
    </div>

    <hr class="border-zinc-100 dark:border-zinc-800" />

    {{-- Acceso al Sistema (Gemelo Digital) --}}
    <div class="space-y-4">
        <flux:heading size="sm">{{ __('Configuración Acceso del Paciente') }}</flux:heading>
        
        @if(!$form->user_id)
            <div class="p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl border border-indigo-100 dark:border-indigo-800">
                <flux:checkbox label="{{ __('Crear nueva cuenta para el paciente') }}" wire:model.live="create_user" />
                @if($create_user)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <flux:input label="{{ __('Email de Acceso') }}" wire:model="user_email" placeholder="paciente@ejemplo.com" />
                        <flux:input label="{{ __('Contraseña Temporal') }}" type="password" wire:model="user_password" />
                    </div>
                @endif
            </div>
        @else
            <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl border border-emerald-100 dark:border-emerald-800 flex items-start gap-3">
                <flux:icon.check-circle variant="mini" class="size-5 text-emerald-500 mt-0.5" />
                <div>
                    <p class="text-xs font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-wide">{{ __('Cuenta Vinculada Activa') }}</p>
                </div>
            </div>
        @endif
    </div>
</div>
