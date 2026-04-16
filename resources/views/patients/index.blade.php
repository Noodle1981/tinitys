<x-layouts::app.full-width title="Gestión de Pacientes">
    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Gestión de Pacientes</h1>
                <p class="text-sm text-slate-500">Base de datos clínica y administración de perfiles para la plataforma de Tinnitus.</p>
            </div>

            <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm sm:rounded-lg p-6 border border-zinc-200 dark:border-zinc-700">
                <livewire:patients.patient-management />
            </div>
        </div>
    </div>
</x-layouts::app.full-width>
