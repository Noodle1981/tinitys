<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{
    /**
     * Devuelve el listado de pacientes para el profesional logueado.
     */
    public function index()
    {
        // En un escenario real, filtraríamos por doctor_id
        // $patients = Patient::where('doctor_id', Auth::user()->doctor_id)->get();
        
        $patients = Patient::with(['clinicalHistory', 'exposure', 'habits'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $patients
        ]);
    }

    /**
     * Guarda un nuevo paciente básico.
     */
    /**
     * Registra un nuevo paciente con su ficha técnica inicial completa.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'dni' => 'required|string|unique:patients,dni',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|string',
        ]);

        $patient = DB::transaction(function () use ($request, $validated) {
            $patient = Patient::create(array_merge($validated, [
                'user_id' => Auth::id(),
                'country' => $request->input('country', 'Argentina'),
                'civil_status' => $request->input('civil_status'),
                'educational_level' => $request->input('educational_level'),
                'occupation' => $request->input('occupation'),
            ]));

            // Persistir todas las sub-pestañas
            $patient->exposure()->create($request->input('exposure', []));
            $patient->habits()->create($request->input('habits', []));
            $patient->clinicalHistory()->create($request->input('clinical_history', []));
            
            // Pestaña 4: Tinnitus
            $patient->tinnitusProfile()->create($request->input('tinnitus_profile', []));

            return $patient;
        });

        return response()->json([
            'success' => true,
            'message' => 'Paciente registrado con éxito',
            'data' => $patient->load(['clinicalHistory', 'exposure', 'habits', 'tinnitusProfile'])
        ]);
    }

    /**
     * Obtiene toda la ficha técnica (5 pestañas) de un paciente.
     */
    public function show($id)
    {
        $patient = Patient::with([
            'clinicalHistory', 
            'exposure', 
            'habits', 
            'tinnitusProfile',
            'sessions'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $patient
        ]);
    }

    /**
     * Actualiza la ficha técnica completa.
     */
    public function update(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);

        DB::transaction(function () use ($request, $patient) {
            // 1. Datos Socio-demográficos (Pestaña 1)
            $patient->update($request->only([
                'name', 'dni', 'birth_date', 'gender', 'civil_status', 
                'educational_level', 'children_count', 'children_ages', 
                'province', 'city', 'address', 'postal_code', 'country', 'occupation'
            ]));

            // 2. Entorno Laboral (Pestaña 2)
            $patient->exposure()->updateOrCreate(
                ['patient_id' => $patient->id],
                $request->input('exposure', [])
            );

            // 3. Ocio y Hábitos (Pestaña 3)
            $patient->habits()->updateOrCreate(
                ['patient_id' => $patient->id],
                $request->input('habits', [])
            );

            // 4. Historia Clínica (Pestaña 5)
            $patient->clinicalHistory()->updateOrCreate(
                ['patient_id' => $patient->id],
                $request->input('clinical_history', [])
            );

            // 5. Perfil de Tinnitus (Pestaña 4)
            $patient->tinnitusProfile()->updateOrCreate(
                ['patient_id' => $patient->id],
                $request->input('tinnitus_profile', [])
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Ficha técnica actualizada correctamente',
            'data' => $patient->load(['clinicalHistory', 'exposure', 'habits', 'tinnitusProfile'])
        ]);
    }

    /**
     * Eliminar paciente.
     */
    public function destroy($id)
    {
        $patient = Patient::findOrFail($id);
        $patient->delete();

        return response()->json([
            'success' => true,
            'message' => 'Paciente eliminado correctamente'
        ]);
    }
}
