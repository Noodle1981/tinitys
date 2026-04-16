<flux:table>
    <flux:table.columns>
        <flux:table.column class="font-bold tracking-tight uppercase text-[10px] text-zinc-400 pl-6!">DNI</flux:table.column>
        <flux:table.column class="font-bold tracking-tight uppercase text-[10px] text-zinc-400">Nombre</flux:table.column>
        <flux:table.column class="font-bold tracking-tight uppercase text-[10px] text-zinc-400">Edad</flux:table.column>
        <flux:table.column class="font-bold tracking-tight uppercase text-[10px] text-zinc-400">Provincia</flux:table.column>
        <flux:table.column class="font-bold tracking-tight uppercase text-[10px] text-zinc-400">Lateralidad</flux:table.column>
        <flux:table.column class="font-bold tracking-tight uppercase text-[10px] text-zinc-400 text-right shrink-0 pr-6!">{{ __('Acciones') }}</flux:table.column>
    </flux:table.columns>

    <flux:table.rows>
        @forelse ($patients as $patient)
            <flux:table.row :key="$patient->id">
                <flux:table.cell class="pl-6! font-semibold text-slate-900 dark:text-zinc-300">{{ $patient->dni }}</flux:table.cell>
                <flux:table.cell class="font-medium">{{ $patient->name }}</flux:table.cell>
                <flux:table.cell>{{ $patient->getAge() }} años</flux:table.cell>
                <flux:table.cell>
                    <span class="text-xs text-zinc-500">{{ $patient->province ?? '-' }}</span>
                </flux:table.cell>
                <flux:table.cell>
                    <flux:badge size="sm" :color="$patient->laterality === 'Bilateral' ? 'zinc' : 'blue'">
                        {{ $patient->laterality ?? 'N/D' }}
                    </flux:badge>
                </flux:table.cell>
                <flux:table.cell class="pr-6! shrink-0 w-0 whitespace-nowrap">
                    <div class="flex items-center justify-end gap-2">
                        <flux:button wire:click="edit({{ $patient->id }})" variant="ghost" icon="pencil-square" size="sm" />
                        <flux:button 
                            wire:click="delete({{ $patient->id }})" 
                            variant="ghost" icon="trash" size="sm" color="red" 
                            confirm="{{ __('¿Estás seguro de eliminar este paciente?') }}" 
                        />
                        <a 
                            href="{{ route('patients.audiometry', $patient->id) }}" 
                            class="flex items-center gap-1.5 p-1 px-3 text-[10px] bg-indigo-600 rounded hover:bg-indigo-700 transition-colors font-bold text-white uppercase tracking-wider"
                        >
                            <flux:icon.arrow-right-end-on-rectangle class="size-3" />
                            {{ __('Abrir Ficha') }}
                        </a>
                    </div>
                </flux:table.cell>
            </flux:table.row>
        @empty
            <flux:table.row>
                <flux:table.cell colspan="6" class="text-center py-12 text-zinc-400 italic">
                    {{ __('No se encontraron pacientes que coincidan con la búsqueda.') }}
                </flux:table.cell>
            </flux:table.row>
        @endforelse
    </flux:table.rows>
</flux:table>
