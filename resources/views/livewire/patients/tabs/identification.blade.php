<div x-show="tab === 'basic'" class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <flux:input label="{{ __('DNI / ID Único') }}" wire:model="form.dni" placeholder="Ej: 12345678" />
        <flux:input label="{{ __('Nombre Completo') }}" wire:model="form.name" />
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <flux:input label="{{ __('Fecha de Nacimiento') }}" type="date" wire:model="form.birth_date" />
        <flux:select label="{{ __('Género') }}" wire:model="form.gender" placeholder="{{ __('Seleccionar...') }}">
            <flux:select.option value="Masculino">{{ __('Masculino') }}</flux:select.option>
            <flux:select.option value="Femenino">{{ __('Femenino') }}</flux:select.option>
            <flux:select.option value="Otro">{{ __('Otro') }}</flux:select.option>
        </flux:select>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
        <flux:input label="{{ __('Domicilio') }}" wire:model="form.address" placeholder="Ej: Calle Falsa 123" />
        <flux:input label="{{ __('Ciudad') }}" wire:model="form.city" placeholder="Ej: Buenos Aires" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <flux:input label="{{ __('Provincia') }}" wire:model="form.province" placeholder="Ej: Buenos Aires" />
        <flux:input label="{{ __('Teléfono') }}" wire:model="form.phone" placeholder="Ej: +54 9 11 1234 5678" />
    </div>

    <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800">
        <flux:checkbox label="{{ __('Problemas de audición en familiares directos (padre, madre, hermanos)') }}" wire:model="form.family_hearing_loss" />
    </div>
</div>
