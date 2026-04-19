<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/patients/{any?}', function () {
        return view('layouts.vue-app');
    })->where('any', '.*')->name('patients.index');

    Route::redirect('/dashboard', '/patients');

    // Data Routes (JSON)
    Route::prefix('api/data')->group(function () {
        Route::get('patients', [\App\Http\Controllers\PatientController::class, 'index'])->name('api.patients.index');
        Route::post('patients', [\App\Http\Controllers\PatientController::class, 'store'])->name('api.patients.store');
        Route::get('patients/{id}', [\App\Http\Controllers\PatientController::class, 'show'])->name('api.patients.show');
        Route::put('patients/{id}', [\App\Http\Controllers\PatientController::class, 'update'])->name('api.patients.update');
        Route::delete('patients/{id}', [\App\Http\Controllers\PatientController::class, 'destroy'])->name('api.patients.destroy');
    });

    // Patient Context Group
    Route::prefix('patient/{patientId}')->name('patients.')->group(function () {
        // Redirect root patient URL to default clinical view
        Route::get('/', function ($patientId) {
            return redirect()->route('patients.audiometry', $patientId);
        });

        // 1. Perfil Personal y Clínico
        Route::get('/personal', function ($patientId) {
            $patient = \App\Models\Patient::with(['habits', 'exposure', 'onsetCause', 'clinicalHistory'])
                ->findOrFail($patientId);
            return view('patient.personal', ['patientId' => $patientId, 'patient' => $patient]);
        })->name('personal');

        // 2. Audiometría Clínica (Predeterminado)
        Route::get('/audiometry', function ($patientId) {
            return view('audiometry', ['patientId' => $patientId]);
        })->name('audiometry');

        // 3. Tinnitus Mapping
        Route::get('/tinnitus-mapping', function ($patientId) {
            return view('stages.tinnitus-mapping', ['patientId' => $patientId]);
        })->name('tinnitus-mapping');

        // 5. Tinnitus Profile
        Route::get('/tinnitus-profile', function ($patientId) {
            return view('stages.tinnitus-profile', ['patientId' => $patientId]);
        })->name('tinnitus-profile');
        // 6. Digital Twin / Consultation Dashboard
        Route::get('/consultation', function ($patientId) {
            return view('stages.consultation', ['patientId' => $patientId]);
        })->name('consultation');

        // 7. Indicadores Clínicos
        Route::get('/indicators', function ($patientId) {
            return view('indicators', ['patientId' => $patientId]);
        })->name('indicators');
    });

    Route::get('/report/{patientId}', function ($patientId) {
        return view('report', ['patientId' => $patientId]);
    })->name('report');

    // Patient Self-Refinement (The "Digital Twin" end-user view)
    Route::get('/my-tinnitus', function () {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $patient = $user?->patient; 
        if (!$patient) return redirect()->route('patients.index');
        return view('patient.my-profile', ['patientId' => $patient->id]);
    })->name('my-tinnitus');
});

require __DIR__.'/settings.php';
