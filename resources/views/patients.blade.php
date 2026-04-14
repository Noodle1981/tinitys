<x-layouts::app title="Gestión de Pacientes">
    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Pacientes</h1>
                    <p class="text-sm text-slate-500">Administra la base de datos clínica y vincula los perfiles del Gemelo Digital.</p>
                </div>

                <livewire:patient-management />
            </div>
        </div>
    </div>
</x-layouts::app>
