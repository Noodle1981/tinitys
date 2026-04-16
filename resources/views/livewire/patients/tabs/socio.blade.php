<div x-show="tab === 'socio'" class="space-y-4" style="display: none;">
    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl border border-blue-100 dark:border-blue-800 text-xs text-blue-800 dark:text-blue-300">
        <div class="flex gap-3">
            <flux:icon.information-circle variant="mini" class="size-5 shrink-0" />
            <div>
                <strong class="block mb-1 font-bold uppercase tracking-wide">{{ __('Datos para Gemelo Digital') }}</strong>
                {{ __('Estos datos permiten correlacionar el estrés social y laboral del entorno con la percepción del Tinnitus.') }}
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <flux:select label="{{ __('Estado Civil') }}" wire:model="form.civil_status" placeholder="{{ __('Seleccionar...') }}">
            <flux:select.option value="Soltero/a">{{ __('Soltero/a') }}</flux:select.option>
            <flux:select.option value="Casado/a">{{ __('Casado/a o Concubinato') }}</flux:select.option>
            <flux:select.option value="Divorciado/a">{{ __('Divorciado/a') }}</flux:select.option>
            <flux:select.option value="Viudo/a">{{ __('Viudo/a') }}</flux:select.option>
        </flux:select>
        
        <div class="flex items-center md:pt-6">
            <flux:checkbox label="{{ __('Tiene hijos/as a cargo') }}" wire:model="form.has_children" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-t border-zinc-100 dark:border-zinc-800 pt-6">
        <flux:select label="{{ __('Actividad') }}" wire:model="form.work_status" placeholder="{{ __('Seleccionar...') }}">
            <flux:select.option value="Trabaja">{{ __('Trabaja') }}</flux:select.option>
            <flux:select.option value="Estudia">{{ __('Estudia') }}</flux:select.option>
            <flux:select.option value="Trabaja y Estudia">{{ __('Trabaja y Estudia') }}</flux:select.option>
            <flux:select.option value="Desocupado">{{ __('Desocupado/a') }}</flux:select.option>
            <flux:select.option value="Jubilado">{{ __('Jubilado/a') }}</flux:select.option>
        </flux:select>
        
        <flux:input label="{{ __('Horas Semanales') }}" type="number" wire:model="form.work_hours" placeholder="Ej: 40" />
        <flux:input label="{{ __('Profesión / Oficio') }}" wire:model="form.occupation" placeholder="Ej: Operario industrial" />
    </div>
</div>
