<div x-show="tab === 'tinnitus'" class="space-y-6" style="display: none;">
    {{-- Sección 1: Temporalidad y El "Big Bang" (Origen) --}}
    <div class="bg-indigo-50/50 dark:bg-indigo-900/10 p-6 rounded-xl border border-indigo-100 dark:border-indigo-800 space-y-6">
        <div class="flex items-center gap-2 mb-2 font-bold text-xs uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
            <flux:icon.clock class="size-4" />
            {{ __('El Origen (Causas de Inicio)') }}
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <flux:input label="{{ __('Fecha de Inicio (Aprox.)') }}" type="date" wire:model="form.onset_date" />
                <flux:select label="{{ __('Tipo de Inicio') }}" wire:model="form.onset_type" placeholder="{{ __('Seleccionar...') }}">
                    <flux:select.option value="Súbito">{{ __('Súbito (Trauma/Ruido)') }}</flux:select.option>
                    <flux:select.option value="Gradual">{{ __('Gradual (Progresivo)') }}</flux:select.option>
                </flux:select>
            </div>

            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <flux:label>{{ __('Intensidad Inicial (EVA)') }}</flux:label>
                    <span class="text-sm font-bold text-indigo-600">{{ $form->initial_intensity_eva }} / 10</span>
                </div>
                <input type="range" wire:model.live="form.initial_intensity_eva" min="0" max="10" step="1" class="w-full h-2 bg-zinc-200 dark:bg-zinc-700 rounded-lg appearance-none cursor-pointer accent-indigo-600" />
            </div>
        </div>

        <div class="space-y-3 pt-2">
            <flux:label>{{ __('Sonido al Comienzo') }}</flux:label>
            <div class="flex flex-wrap gap-2">
                @foreach (['Pitido', 'Siseo', 'Rugido', 'Zumbido'] as $sound)
                    <flux:checkbox label="{{ $sound }}" wire:model="form.initial_sound_types" value="{{ $sound }}" class="text-xs" />
                @endforeach
            </div>
        </div>
    </div>

    {{-- Sección 2: Estado Actual --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 py-4 px-2 border-y border-zinc-100 dark:border-zinc-800">
        {{-- Lateralidad --}}
        <div class="space-y-4">
            <flux:select label="{{ __('Lateralidad Actual') }}" wire:model="form.laterality" placeholder="{{ __('Seleccionar...') }}">
                <flux:select.option value="Derecho">{{ __('Oído Derecho') }}</flux:select.option>
                <flux:select.option value="Izquierdo">{{ __('Oído Izquierdo') }}</flux:select.option>
                <flux:select.option value="Bilateral">{{ __('Bilateral') }}</flux:select.option>
            </flux:select>
            
            <div class="space-y-2">
                <flux:label>{{ __('Sonido Actual') }}</flux:label>
                <div class="flex flex-wrap gap-2">
                    @foreach (['Pitido', 'Siseo', 'Rugido', 'Pulsátil', 'Zumbido', 'Otros'] as $sound)
                        <flux:checkbox label="{{ $sound }}" wire:model="form.sound_type" value="{{ $sound }}" class="text-xs" />
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Intensidad Actual --}}
        <div class="bg-zinc-50 dark:bg-zinc-800/20 p-4 rounded-xl space-y-4">
            <div class="space-y-3">
                <flux:label>{{ __('Intensidad Percibida AHORA (EVA)') }}</flux:label>
                <div class="flex items-center gap-4">
                    <input type="range" wire:model.live="form.intensity_eva" min="0" max="10" step="1" class="flex-1 h-2 bg-zinc-200 dark:bg-zinc-700 rounded-lg appearance-none cursor-pointer accent-indigo-600" />
                    <span class="text-xl font-bold text-indigo-600 w-12 text-center">{{ $form->intensity_eva }}</span>
                </div>
            </div>

            <div class="space-y-3">
                <flux:label>{{ __('Nivel de Distrés / Molestia') }}</flux:label>
                <div class="flex items-center gap-4">
                    <input type="range" wire:model.live="form.distress_eva" min="0" max="10" step="1" class="flex-1 h-2 bg-zinc-200 dark:bg-zinc-700 rounded-lg appearance-none cursor-pointer accent-rose-600" />
                    <span class="text-xl font-bold text-rose-600 w-12 text-center">{{ $form->distress_eva }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
