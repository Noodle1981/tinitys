<div x-show="tab === 'environment'" class="space-y-6" style="display: none;">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Sección Cuantificación de Hábitos --}}
        <div class="bg-zinc-50 dark:bg-zinc-800/50 p-6 rounded-xl border border-zinc-200 dark:border-zinc-700 space-y-6">
            <div class="flex items-center gap-2 mb-2 font-bold text-xs uppercase tracking-wider text-zinc-500">
                <flux:icon.sparkles class="size-4" />
                {{ __('Cuantificación de Hábitos') }}
            </div>
            
            {{-- Tabaco --}}
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <flux:label>{{ __('Consumo de Tabaco') }}</flux:label>
                    <span class="text-sm font-bold text-indigo-600">{{ $form->smoking_level }} / 10</span>
                </div>
                <input type="range" wire:model.live="form.smoking_level" min="0" max="10" step="1" class="w-full h-2 bg-zinc-200 dark:bg-zinc-700 rounded-lg appearance-none cursor-pointer accent-indigo-600" />
            </div>

            {{-- Alcohol --}}
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <flux:label>{{ __('Consumo de Alcohol') }}</flux:label>
                    <span class="text-sm font-bold text-indigo-600">{{ $form->alcohol_level }} / 10</span>
                </div>
                <input type="range" wire:model.live="form.alcohol_level" min="0" max="10" step="1" class="w-full h-2 bg-zinc-200 dark:bg-zinc-700 rounded-lg appearance-none cursor-pointer accent-indigo-600" />
            </div>

            {{-- Café --}}
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <flux:label>{{ __('Consumo de Café / Cafeína') }}</flux:label>
                    <span class="text-sm font-bold text-indigo-600">{{ $form->coffee_level }} / 10</span>
                </div>
                <input type="range" wire:model.live="form.coffee_level" min="0" max="10" step="1" class="w-full h-2 bg-zinc-200 dark:bg-zinc-700 rounded-lg appearance-none cursor-pointer accent-indigo-600" />
            </div>
        </div>

        {{-- Sección Entorno y Ruido --}}
        <div class="bg-zinc-50 dark:bg-zinc-800/50 p-6 rounded-xl border border-zinc-200 dark:border-zinc-700 space-y-6">
            <div class="flex items-center gap-2 mb-2 font-bold text-xs uppercase tracking-wider text-zinc-500">
                <flux:icon.information-circle class="size-4" />
                {{ __('Niveles de Exposición al Ruido') }}
            </div>
            
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <flux:label>{{ __('Ruido Laboral') }}</flux:label>
                    <span class="text-sm font-bold text-orange-600">{{ $form->occupational_noise_level }} / 10</span>
                </div>
                <input type="range" wire:model.live="form.occupational_noise_level" min="0" max="10" step="1" class="w-full h-2 bg-zinc-200 dark:bg-zinc-700 rounded-lg appearance-none cursor-pointer accent-orange-500" />
            </div>

            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <flux:label>{{ __('Ruido Ocio / Música') }}</flux:label>
                    <span class="text-sm font-bold text-orange-600">{{ $form->leisure_noise_level }} / 10</span>
                </div>
                <input type="range" wire:model.live="form.leisure_noise_level" min="0" max="10" step="1" class="w-full h-2 bg-zinc-200 dark:bg-zinc-700 rounded-lg appearance-none cursor-pointer accent-orange-500" />
            </div>

            <div class="grid grid-cols-2 gap-4 pt-2">
                <flux:checkbox label="{{ __('Protección Auditiva') }}" wire:model.live="form.protection_used" />
                <flux:checkbox label="{{ __('Ruido últimas 24h') }}" wire:model.live="form.recent_exposure" />
            </div>

            <div x-show="$wire.form.protection_used || $wire.form.recent_exposure" x-transition>
                <flux:input label="{{ __('Años de Exposición') }}" type="number" wire:model="form.noise_duration_years" min="0" placeholder="Ej: 15" />
            </div>
        </div>
    </div>
</div>
