<?php

namespace App\Livewire\Forms;

use App\Models\Patient;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PatientForm extends Form
{
    public ?Patient $patient = null;

    #[Validate('required|string|min:3')]
    public $name = '';

    #[Validate('required|string')]
    public $dni = '';

    #[Validate('required|date')]
    public $birth_date = '';

    #[Validate('required|string')]
    public $gender = '';

    // Socio-Demográfico
    public $civil_status = '';
    public $has_children = false;
    public $work_status = '';
    public $work_hours = null;
    public $occupation = '';

    // Contacto
    public $address = '';
    public $city = '';
    public $province = '';
    public $phone = '';

    // Clínico General (En la tabla patients)
    public $laterality = '';
    public $sound_type = [];
    public $other_disabilities = '';
    public $user_id = null;

    // --- RELACIONES ---

    // Hábitos (PatientHabit)
    public $smoking_level = 0;
    public $alcohol_level = 0;
    public $coffee_level = 0;

    // Exposiciones (PatientExposure)
    public $occupational_noise_level = 0;
    public $leisure_noise_level = 0;
    #[Validate('nullable|integer|min:0')]
    public $noise_duration_years = null;
    public $protection_used = false;
    public $recent_exposure = false;

    // Causas de Inicio (PatientOnsetCause)
    public $onset_date = '';
    public $onset_type = '';
    public $initial_intensity_eva = 5;
    public $initial_sound_types = [];
    public $intensity_eva = 5;
    public $distress_eva = 5;
    public $has_head_trauma = false;
    public $has_meningitis = false;
    public $has_meniere = false;
    public $has_facial_paralysis = false;
    public $has_herpes_zoster = false;
    public $has_mumps = false;
    public $has_measles = false;
    public $has_rubella = false;
    public $has_typhoid = false;
    public $has_vertigo = false;

    // Historia Clínica (PatientClinicalHistory)
    public $uses_aminoglycosides = false;
    public $uses_salicylates = false;
    public $uses_loop_diuretics = false;
    public $uses_quinine = false;
    public $has_malaria = false;
    public $has_rheumatism = false;
    public $has_tuberculosis = false;
    public $has_heart_failure = false;
    public $has_hypertension = false;
    public $has_otalgia = false;
    public $has_otorrhea = false;
    public $has_serous_otitis = false;
    public $has_ear_obstruction = false;
    public $has_ossicular_chain_lesion = false;
    public $family_hearing_loss = false;
    public $sleep_quality = 5;
    public $avg_sleep_hours = null;
    public $has_night_shifts = false;
    public $mental_health_context = '';
    public $medications = '';

    public function setPatient(Patient $patient)
    {
        $this->patient = $patient;

        // Carga de datos base
        $this->name = $patient->name;
        $this->dni = $patient->dni;
        $this->birth_date = $patient->birth_date ? $patient->birth_date->format('Y-m-d') : '';
        $this->gender = $patient->gender;
        $this->civil_status = $patient->civil_status;
        $this->has_children = (bool)$patient->has_children;
        $this->work_status = $patient->work_status;
        $this->work_hours = $patient->work_hours;
        $this->occupation = $patient->occupation;
        $this->address = $patient->address;
        $this->city = $patient->city;
        $this->province = $patient->province;
        $this->phone = $patient->phone;
        $this->laterality = $patient->laterality;
        $this->sound_type = $patient->sound_type ?? [];
        $this->other_disabilities = $patient->other_disabilities;
        $this->user_id = $patient->user_id;

        // Carga de Hábitos
        if ($habit = $patient->habits) {
            $this->smoking_level = $habit->smoking_level;
            $this->alcohol_level = $habit->alcohol_level;
            $this->coffee_level = $habit->coffee_level;
        }

        // Carga de Exposiciones
        if ($exp = $patient->exposure) {
            $this->occupational_noise_level = $exp->occupational_noise_level;
            $this->leisure_noise_level = $exp->leisure_noise_level;
            $this->noise_duration_years = $exp->noise_duration_years;
            $this->protection_used = (bool)$exp->protection_used;
            $this->recent_exposure = (bool)$exp->recent_exposure;
        }

        // Carga de Causas de Inicio
        if ($onset = $patient->onsetCause) {
            $this->onset_date = $onset->onset_date ? $onset->onset_date->format('Y-m-d') : '';
            $this->onset_type = $onset->onset_type;
            $this->initial_intensity_eva = $onset->initial_intensity_eva;
            $this->initial_sound_types = $onset->initial_sound_types ?? [];
            $this->has_head_trauma = (bool)$onset->has_head_trauma;
            $this->has_meningitis = (bool)$onset->has_meningitis;
            $this->has_meniere = (bool)$onset->has_meniere;
            $this->has_facial_paralysis = (bool)$onset->has_facial_paralysis;
            $this->has_herpes_zoster = (bool)$onset->has_herpes_zoster;
            $this->has_mumps = (bool)$onset->has_mumps;
            $this->has_measles = (bool)$onset->has_measles;
            $this->has_rubella = (bool)$onset->has_rubella;
            $this->has_typhoid = (bool)$onset->has_typhoid;
            $this->has_vertigo = (bool)$onset->has_vertigo;
        }

        // Carga de Historia Clínica
        if ($history = $patient->clinicalHistory) {
            $this->uses_aminoglycosides = (bool)$history->uses_aminoglycosides;
            $this->uses_salicylates = (bool)$history->uses_salicylates;
            $this->uses_loop_diuretics = (bool)$history->uses_loop_diuretics;
            $this->uses_quinine = (bool)$history->uses_quinine;
            $this->has_malaria = (bool)$history->has_malaria;
            $this->has_rheumatism = (bool)$history->has_rheumatism;
            $this->has_tuberculosis = (bool)$history->has_tuberculosis;
            $this->has_heart_failure = (bool)$history->has_heart_failure;
            $this->has_hypertension = (bool)$history->has_hypertension;
            $this->has_otalgia = (bool)$history->has_otalgia;
            $this->has_otorrhea = (bool)$history->has_otorrhea;
            $this->has_serous_otitis = (bool)$history->has_serous_otitis;
            $this->has_ear_obstruction = (bool)$history->has_ear_obstruction;
            $this->has_ossicular_chain_lesion = (bool)$history->has_ossicular_chain_lesion;
            $this->family_hearing_loss = (bool)$history->family_hearing_loss;
            $this->sleep_quality = $history->sleep_quality ?? 5;
            $this->avg_sleep_hours = $history->avg_sleep_hours;
            $this->has_night_shifts = (bool)$history->has_night_shifts;
            $this->intensity_eva = $history->intensity_eva ?? 5;
            $this->distress_eva = $history->distress_eva ?? 5;
            $this->mental_health_context = $history->mental_health_context;
            $this->medications = $history->medications;
        }
    }

    public function store()
    {
        $this->validate();

        DB::transaction(function () {
            // 1. Guardar/Actualizar Paciente base
            $patientData = [
                'name' => $this->name,
                'dni' => $this->dni,
                'birth_date' => $this->birth_date,
                'gender' => $this->gender,
                'occupation' => $this->occupation,
                'address' => $this->address,
                'city' => $this->city,
                'province' => $this->province,
                'phone' => $this->phone,
                'civil_status' => $this->civil_status,
                'has_children' => $this->has_children,
                'work_status' => $this->work_status,
                'work_hours' => $this->work_hours,
                'other_disabilities' => $this->other_disabilities,
                'laterality' => $this->laterality,
                'sound_type' => $this->sound_type,
                'user_id' => $this->user_id,
                'doctor_id' => Auth::id(),
            ];

            if ($this->patient) {
                $this->patient->update($patientData);
            } else {
                $this->patient = Patient::create($patientData);
            }

            // 2. Guardar Hábitos
            $this->patient->habits()->updateOrCreate([], [
                'smoking_level' => $this->smoking_level,
                'alcohol_level' => $this->alcohol_level,
                'coffee_level' => $this->coffee_level,
            ]);

            // 3. Guardar Exposiciones
            $this->patient->exposure()->updateOrCreate([], [
                'occupational_noise_level' => $this->occupational_noise_level,
                'leisure_noise_level' => $this->leisure_noise_level,
                'noise_duration_years' => $this->noise_duration_years,
                'protection_used' => $this->protection_used,
                'recent_exposure' => $this->recent_exposure,
            ]);

            // 4. Guardar Causas de Inicio
            $this->patient->onsetCause()->updateOrCreate([], [
                'onset_date' => $this->onset_date ?: null,
                'onset_type' => $this->onset_type,
                'initial_intensity_eva' => $this->initial_intensity_eva,
                'initial_sound_types' => $this->initial_sound_types,
                'has_head_trauma' => $this->has_head_trauma,
                'has_meningitis' => $this->has_meningitis,
                'has_meniere' => $this->has_meniere,
                'has_facial_paralysis' => $this->has_facial_paralysis,
                'has_herpes_zoster' => $this->has_herpes_zoster,
                'has_mumps' => $this->has_mumps,
                'has_measles' => $this->has_measles,
                'has_rubella' => $this->has_rubella,
                'has_typhoid' => $this->has_typhoid,
                'has_vertigo' => $this->has_vertigo,
            ]);

            // 5. Guardar Historia Clínica
            $this->patient->clinicalHistory()->updateOrCreate([], [
                'uses_aminoglycosides' => $this->uses_aminoglycosides,
                'uses_salicylates' => $this->uses_salicylates,
                'uses_loop_diuretics' => $this->uses_loop_diuretics,
                'uses_quinine' => $this->uses_quinine,
                'has_malaria' => $this->has_malaria,
                'has_rheumatism' => $this->has_rheumatism,
                'has_tuberculosis' => $this->has_tuberculosis,
                'has_heart_failure' => $this->has_heart_failure,
                'has_hypertension' => $this->has_hypertension,
                'has_otalgia' => $this->has_otalgia,
                'has_otorrhea' => $this->has_otorrhea,
                'has_serous_otitis' => $this->has_serous_otitis,
                'has_ear_obstruction' => $this->has_ear_obstruction,
                'has_ossicular_chain_lesion' => $this->has_ossicular_chain_lesion,
                'family_hearing_loss' => $this->family_hearing_loss,
                'sleep_quality' => $this->sleep_quality,
                'avg_sleep_hours' => $this->avg_sleep_hours ?: null,
                'has_night_shifts' => $this->has_night_shifts,
                'intensity_eva' => $this->intensity_eva,
                'distress_eva' => $this->distress_eva,
                'mental_health_context' => $this->mental_health_context,
                'medications' => $this->medications,
            ]);
        });

        $this->reset();
    }
}
