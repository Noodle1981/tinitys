<div class="space-y-6">
    {{-- Barra de Herramientas: Búsqueda y Botón Nuevo --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <flux:input 
            wire:model.live="search" 
            icon="magnifying-glass" 
            placeholder="{{ __('Buscar por nombre o DNI...') }}" 
            class="max-w-xs" 
        />
        <flux:button wire:click="create" variant="primary" icon="plus">
            {{ __('Nuevo Paciente') }}
        </flux:button>
    </div>

    {{-- Listado de Pacientes --}}
    <div class="overflow-hidden">
        @include('livewire.patients.partials.table')
        
        <div class="px-6 py-4 border-t border-zinc-100 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50">
            {{ $patients->links() }}
        </div>
    </div>

    {{-- Modal de Edición/Creación --}}
    @include('livewire.patients.partials.modal')
</div>
