<?php

use Livewire\Volt\Component;
use App\Models\Patient;
use App\Models\PatientSession;
use App\Models\AudiometryValue;

new class extends Component
{
    public $name = '';
    public $age = '';
    public $noise_exposure = 'no';
    public $acufenos = false;
    public $vertigos = false;

    public $od_aerea = [125 => null, 250 => null, 500 => null, 1000 => null, 2000 => null, 3000 => null, 4000 => null, 6000 => null, 8000 => null];
    public $od_osea = [500 => null, 1000 => null, 2000 => null, 4000 => null];
    public $enmascaramiento_od = 'no';

    public $oi_aerea = [125 => null, 250 => null, 500 => null, 1000 => null, 2000 => null, 3000 => null, 4000 => null, 6000 => null, 8000 => null];
    public $oi_osea = [500 => null, 1000 => null, 2000 => null, 4000 => null];
    public $enmascaramiento_oi = 'no';

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'age' => 'required|integer',
        ]);

        $patient = Patient::create([
            'name' => $this->name,
            'age' => $this->age,
            'noise_exposure' => $this->noise_exposure == 'si',
            'tinnitus_symptom' => $this->acufenos,
            'vertigo_symptom' => $this->vertigos,
        ]);

        $session = $patient->sessions()->create([
            'type' => 'doctor',
            'metadata' => [
                'enmascaramiento_od' => $this->enmascaramiento_od,
                'enmascaramiento_oi' => $this->enmascaramiento_oi,
            ],
        ]);

        // Save OD Air
        foreach ($this->od_aerea as $freq => $val) {
            if ($val !== null) {
                $session->audiometryValues()->create([
                    'ear' => 'OD', 'frequency' => $freq, 'type' => 'air', 'db_level' => $val
                ]);
            }
        }
        // Save OD Bone
        foreach ($this->od_osea as $freq => $val) {
            if ($val !== null) {
                $session->audiometryValues()->create([
                    'ear' => 'OD', 'frequency' => $freq, 'type' => 'bone', 'db_level' => $val
                ]);
            }
        }
        // Save OI Air
        foreach ($this->oi_aerea as $freq => $val) {
            if ($val !== null) {
                $session->audiometryValues()->create([
                    'ear' => 'OI', 'frequency' => $freq, 'type' => 'air', 'db_level' => $val
                ]);
            }
        }
        // Save OI Bone
        foreach ($this->oi_osea as $freq => $val) {
            if ($val !== null) {
                $session->audiometryValues()->create([
                    'ear' => 'OI', 'frequency' => $freq, 'type' => 'bone', 'db_level' => $val
                ]);
            }
        }

        session()->flash('message', 'Audiometría guardada correctamente.');
        $this->redirectRoute('dashboard');
    }
}; ?>

<div class="audiometry-container">
    <style>
        .audiometry-container { font-family: 'Inter', sans-serif; }
        .seccion { margin-bottom: 20px; padding: 15px; border: 1px solid var(--color-border-secondary); border-radius: 8px; background: white; }
        .oido-derecho { border-left: 5px solid #ef4444; background-color: #fef2f2; }
        .oido-izquierdo { border-left: 5px solid #3b82f6; background-color: #eff6ff; }
        .title-h1 { font-size: 1.5rem; font-weight: bold; margin-bottom: 20px; color: var(--color-text-primary); }
        .title-h2 { font-size: 1.25rem; font-weight: 600; margin-bottom: 10px; color: var(--color-text-primary); }
        label { display: inline-block; width: 150px; font-weight: 600; font-size: 0.875rem; color: var(--color-text-secondary); }
        .freq-group { margin-bottom: 10px; display: flex; align-items: center; }
        .input-num { padding: 4px 8px; border-radius: 4px; border: 1px solid #ccc; width: 80px; }
        .btn-save { padding: 10px 24px; background: #1D9E75; color: white; border-radius: 6px; font-weight: 600; cursor: pointer; border: none; transition: background 0.2s; }
        .btn-save:hover { background: #15805d; }
    </style>

    <form wire:submit.prevent="save">
        <!-- Datos Clínicos y Laborales -->
        <div class="seccion">
            <h2 class="title-h2">Datos del Paciente e Historia Clínica</h2>
            <div class="mb-4">
                <label>Nombre completo:</label>
                <input type="text" wire:model="name" class="input-num !w-64" required>
            </div>
            <div class="mb-4">
                <label>Edad:</label>
                <input type="number" wire:model="age" class="input-num" required>
            </div>
            <div class="mb-4">
                <label>Exposición a ruido:</label>
                <select wire:model="noise_exposure" class="input-num !w-32">
                    <option value="no">No</option>
                    <option value="si">Sí</option>
                </select>
            </div>
            <div class="mb-4">
                <label>Síntomas óticos:</label>
                <label class="!w-auto mr-4 font-normal"><input type="checkbox" wire:model="acufenos" class="mr-1"> Acúfenos</label>
                <label class="!w-auto font-normal"><input type="checkbox" wire:model="vertigos" class="mr-1"> Vértigos</label>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Umbrales Oído Derecho -->
            <div class="seccion oido-derecho">
                <h2 class="title-h2">Oído Derecho (Rojo)</h2>
                <p class="font-bold mb-2 text-sm">Conducción Aérea (dB)</p>
                @foreach([125, 250, 500, 1000, 2000, 3000, 4000, 6000, 8000] as $freq)
                    <div class="freq-group">
                        <label>{{ $freq }} Hz:</label>
                        <input type="number" wire:model="od_aerea.{{ $freq }}" min="-10" max="120" class="input-num">
                        <span class="ml-2 text-xs text-gray-500">dB</span>
                    </div>
                @endforeach

                <p class="font-bold mt-4 mb-2 text-sm">Conducción Ósea (dB)</p>
                @foreach([500, 1000, 2000, 4000] as $freq)
                    <div class="freq-group">
                        <label>{{ $freq }} Hz:</label>
                        <input type="number" wire:model="od_osea.{{ $freq }}" min="-10" max="120" class="input-num">
                        <span class="ml-2 text-xs text-gray-500">dB</span>
                    </div>
                @endforeach

                <div class="mt-4">
                    <label>¿Enmascaramiento?</label>
                    <label class="!w-auto mr-4 font-normal"><input type="radio" wire:model="enmascaramiento_od" value="si"> Sí</label>
                    <label class="!w-auto font-normal"><input type="radio" wire:model="enmascaramiento_od" value="no"> No</label>
                </div>
            </div>

            <!-- Umbrales Oído Izquierdo -->
            <div class="seccion oido-izquierdo">
                <h2 class="title-h2">Oído Izquierdo (Azul)</h2>
                <p class="font-bold mb-2 text-sm">Conducción Aérea (dB)</p>
                @foreach([125, 250, 500, 1000, 2000, 3000, 4000, 6000, 8000] as $freq)
                    <div class="freq-group">
                        <label>{{ $freq }} Hz:</label>
                        <input type="number" wire:model="oi_aerea.{{ $freq }}" min="-10" max="120" class="input-num">
                        <span class="ml-2 text-xs text-gray-500">dB</span>
                    </div>
                @endforeach

                <p class="font-bold mt-4 mb-2 text-sm">Conducción Ósea (dB)</p>
                @foreach([500, 1000, 2000, 4000] as $freq)
                    <div class="freq-group">
                        <label>{{ $freq }} Hz:</label>
                        <input type="number" wire:model="oi_osea.{{ $freq }}" min="-10" max="120" class="input-num">
                        <span class="ml-2 text-xs text-gray-500">dB</span>
                    </div>
                @endforeach

                <div class="mt-4">
                    <label>¿Enmascaramiento?</label>
                    <label class="!w-auto mr-4 font-normal"><input type="radio" wire:model="enmascaramiento_oi" value="si"> Sí</label>
                    <label class="!w-auto font-normal"><input type="radio" wire:model="enmascaramiento_oi" value="no"> No</label>
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit" class="btn-save shadow-lg">Guardar Resultados de Audiometría</button>
        </div>
    </form>
</div>