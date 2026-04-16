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

<div class="px-2 py-4 mb-2 border-b border-zinc-200/60 dark:border-zinc-700/60">
    <div class="flex items-center gap-3 mb-3">
        <div class="relative">
            <div class="w-11 h-11 rounded-full bg-linear-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg shadow-md ring-2 ring-white dark:ring-zinc-900">
                {{ substr($patient->name, 0, 1) }}
            </div>
            <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 border-2 border-white dark:border-zinc-900" title="Paciente Activo"></div>
        </div>
        
        <div class="flex-1 min-w-0">
            <h2 class="text-sm font-bold text-zinc-900 dark:text-white truncate leading-none mb-1" title="{{ $patient->name }}">
                {{ $patient->name }}
            </h2>
            <p class="text-[10px] text-zinc-400 font-medium tracking-wider uppercase">DNI {{ $patient->dni }}</p>
        </div>
    </div>

    <div class="flex items-center gap-2 text-[11px] text-zinc-500 dark:text-zinc-400 mb-4 px-1">
        <span class="bg-zinc-100 dark:bg-zinc-800 px-1.5 py-0.5 rounded text-zinc-600 dark:text-zinc-300">{{ $patient->getAge() }} años</span>
        <span class="text-zinc-300 dark:text-zinc-700">•</span>
        <span class="font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-tighter">{{ $patient->laterality ?? 'N/D' }}</span>
    </div>

    <flux:navlist.item :href="route('patients.index')" icon="arrow-uturn-left" size="sm" class="text-[11px]! py-1! text-zinc-400 hover:text-zinc-900 dark:hover:text-white" wire:navigate>
        {{ __('Cambiar Paciente') }}
    </flux:navlist.item>
</div>

