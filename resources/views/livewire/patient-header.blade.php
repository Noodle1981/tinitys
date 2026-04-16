<?php

use Livewire\Volt\Component;
use App\Models\Patient;

new class extends Component
{
    public $patientId;
    public $patient;

    public function mount($patientId)
    {
        $this->patientId = $patientId;
        $this->patient = Patient::findOrFail($patientId);
    }
}; ?>

<div class="patient-header bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700 shadow-sm">
    <div class="px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-700 dark:text-indigo-300 font-bold text-lg border border-indigo-100 dark:border-indigo-800">
                    {{ substr($patient->name, 0, 1) }}
                </div>
                <div>
                    <h1 class="text-base font-bold text-zinc-900 dark:text-white leading-tight">{{ $patient->name }}</h1>
                    <div class="flex items-center gap-2 text-[11px] text-zinc-500 mt-0.5">
                        <span class="font-medium">DNI: {{ $patient->dni }}</span>
                        <span class="text-zinc-300">|</span>
                        <span>{{ $patient->getAge() }} años</span>
                        <span class="text-zinc-300">|</span>
                        <span class="text-indigo-600 dark:text-indigo-400 font-semibold uppercase">{{ $patient->laterality ?? 'N/D' }}</span>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('patients.index') }}" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded shadow-sm hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors">
                    <flux:icon.arrow-left-start-on-rectangle class="size-3" />
                    Volver a Gestión
                </a>
            </div>
        </div>
        </div>
    </div>
</div>
