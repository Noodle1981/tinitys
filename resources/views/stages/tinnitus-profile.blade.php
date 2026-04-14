<x-layouts::patient :patientId="$patientId" title="Perfil Tinnitus">
    <div class="bg-white dark:bg-zinc-900 shadow-sm sm:rounded-lg p-6 border border-zinc-200 dark:border-zinc-700">
        <livewire:tinnitus-profile :patientId="$patientId" />
    </div>
</x-layouts::patient>
