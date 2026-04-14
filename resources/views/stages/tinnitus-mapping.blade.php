<x-layouts::patient :patientId="$patientId" title="Mapeador Multicapa">
    <div class="bg-white dark:bg-zinc-900 shadow-sm sm:rounded-lg p-6 border border-zinc-200 dark:border-zinc-700">
        <livewire:tinnitus-mapping :patientId="$patientId" />
    </div>
</x-layouts::patient>
