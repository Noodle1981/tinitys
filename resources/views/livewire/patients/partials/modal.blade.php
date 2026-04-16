<flux:modal wire:model="showModal" class="min-w-fit md:min-w-180">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ $form->patient ? __('Editar Paciente') : __('Nuevo Paciente') }}</flux:heading>
            <flux:subheading>{{ __('Completa la ficha clínica profesional del paciente.') }}</flux:subheading>
        </div>

        <div x-data="{ tab: 'basic' }" class="space-y-6">
            {{-- Pestañas de Navegación --}}
            <div class="flex flex-wrap gap-2 p-1 bg-zinc-100 dark:bg-zinc-800 rounded-lg w-fit">
                <button @click="tab = 'basic'" :class="tab === 'basic' ? 'bg-white dark:bg-zinc-700 shadow-sm text-zinc-900 dark:text-white' : 'text-zinc-500 hover:text-zinc-700'" class="px-4 py-1.5 text-xs font-bold rounded-md transition-all uppercase tracking-wide">
                    {{ __('Identificación') }}
                </button>
                <button @click="tab = 'socio'" :class="tab === 'socio' ? 'bg-white dark:bg-zinc-700 shadow-sm text-zinc-900 dark:text-white' : 'text-zinc-500 hover:text-zinc-700'" class="px-4 py-1.5 text-xs font-bold rounded-md transition-all uppercase tracking-wide">
                    {{ __('Socio-Demog.') }}
                </button>
                <button @click="tab = 'environment'" :class="tab === 'environment' ? 'bg-white dark:bg-zinc-700 shadow-sm text-zinc-900 dark:text-white' : 'text-zinc-500 hover:text-zinc-700'" class="px-4 py-1.5 text-xs font-bold rounded-md transition-all uppercase tracking-wide">
                    {{ __('Hábitos y Entorno') }}
                </button>
                <button @click="tab = 'tinnitus'" :class="tab === 'tinnitus' ? 'bg-white dark:bg-zinc-700 shadow-sm text-zinc-900 dark:text-white' : 'text-zinc-500 hover:text-zinc-700'" class="px-4 py-1.5 text-xs font-bold rounded-md transition-all uppercase tracking-wide">
                    {{ __('Perfil Tinnitus') }}
                </button>
                <button @click="tab = 'history'" :class="tab === 'history' ? 'bg-white dark:bg-zinc-700 shadow-sm text-zinc-900 dark:text-white' : 'text-zinc-500 hover:text-zinc-700'" class="px-4 py-1.5 text-xs font-bold rounded-md transition-all uppercase tracking-wide">
                    {{ __('Clínica') }}
                </button>
            </div>

            <div class="mt-4">
                @include('livewire.patients.tabs.identification')
                @include('livewire.patients.tabs.socio')
                @include('livewire.patients.tabs.environment')
                @include('livewire.patients.tabs.tinnitus')
                @include('livewire.patients.tabs.clinic')
            </div>
        </div>

        <div class="flex gap-2 justify-end border-t border-zinc-100 dark:border-zinc-800 pt-6">
            <flux:modal.close>
                <flux:button variant="ghost">{{ __('Cancelar') }}</flux:button>
            </flux:modal.close>
            <flux:button wire:click="save" variant="primary" class="px-8">
                {{ __('Guardar Paciente') }}
            </flux:button>
        </div>
    </div>
</flux:modal>
