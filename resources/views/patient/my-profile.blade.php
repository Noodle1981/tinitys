<x-layouts::app title="Mi Refinamiento de Tinnitus">
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Quick Navigation to Stages for Patient -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-800 mb-2">1. Factores</h3>
                    <p class="text-xs text-slate-500 mb-4">¿Cómo dormiste? ¿Estrés? Alimentá tu perfil diario.</p>
                    <a href="{{ route('patients.tinnitus-profile', $patientId) }}" class="inline-block px-4 py-2 bg-slate-100 text-slate-700 rounded-md text-sm font-semibold">Refinar Perfil</a>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-800 mb-2">2. Mapeador</h3>
                    <p class="text-xs text-slate-500 mb-4">Afiná las capas de sonido según lo que escuchás ahora.</p>
                    <a href="{{ route('patients.tinnitus-mapping', $patientId) }}" class="inline-block px-4 py-2 bg-slate-100 text-slate-700 rounded-md text-sm font-semibold">Refinar Capas</a>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-800 mb-2">3. Calibrador</h3>
                    <p class="text-xs text-slate-500 mb-4">Calibrá la intensidad dB SL sobre tu umbral actual.</p>
                    <a href="{{ route('patients.tinnitus-calibrator', $patientId) }}" class="inline-block px-4 py-2 bg-slate-100 text-slate-700 rounded-md text-sm font-semibold">Refinar Umbral</a>
                </div>
                <div class="dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-amber-200 bg-amber-50/20">
                    <h3 class="text-lg font-bold text-amber-800 mb-2">4. Digital Twin</h3>
                    <p class="text-xs text-slate-500 mb-4">Mirá tu evolución histórica y compará con tu audimetría.</p>
                    <a href="{{ route('patients.consultation', $patientId) }}" class="inline-block px-4 py-2 bg-amber-100 text-amber-800 rounded-md text-sm font-semibold">Ver Gráfica Histórica</a>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h2 class="text-xl font-bold text-slate-900 mb-6">Último Reporte Audiométrico (Médico)</h2>
                <div class="opacity-75 pointer-events-none">
                    <livewire:patient-report :patientId="$patientId" />
                </div>
                <p class="mt-4 text-xs text-slate-500 italic">Esta información es de solo lectura. Solo el médico puede modificar los valores clínicos.</p>
            </div>
        </div>
    </div>
</x-layouts::app>
