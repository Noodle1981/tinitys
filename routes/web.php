<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Patient Context Group
    Route::prefix('patient/{patientId}')->name('patients.')->group(function () {
        // Redirect root patient URL to default clinical view
        Route::get('/', function ($patientId) {
            return redirect()->route('patients.audiometry', $patientId);
        });

        // 1. Perfil Personal y Clínico
        Route::get('/personal', function ($patientId) {
            $patient = \App\Models\Patient::findOrFail($patientId);
            return view('patient.personal', ['patientId' => $patientId, 'patient' => $patient]);
        })->name('personal');

        // 2. Audiometría Clínica (Predeterminado)
        Route::get('/audiometry', function ($patientId) {
            return view('audiometry', ['patientId' => $patientId]);
        })->name('audiometry');

        // 3. Mapeador Multicapa
        Route::get('/mapeador', function ($patientId) {
            return view('stages.stage-2', ['patientId' => $patientId]);
        })->name('mapeador');

        // 4. Calibrador Tinnitus
        Route::get('/calibrador', function ($patientId) {
            return view('stages.stage-3', ['patientId' => $patientId]);
        })->name('calibrador');

        // 5. Perfil Tinnitus (Stage 1)
        Route::get('/perfil-tinnitus', function ($patientId) {
            return view('stages.stage-1', ['patientId' => $patientId]);
        })->name('perfil-tinnitus');
    });

    Route::get('/report/{patientId}', function ($patientId) {
        return view('report', ['patientId' => $patientId]);
    })->name('report');

    // Patient Self-Refinement (The "Digital Twin" end-user view)
    Route::get('/my-tinnitus', function () {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $patient = $user?->patient; 
        if (!$patient) return redirect()->route('dashboard');
        return view('patient.my-profile', ['patientId' => $patient->id]);
    })->name('my-tinnitus');
});

require __DIR__.'/settings.php';
