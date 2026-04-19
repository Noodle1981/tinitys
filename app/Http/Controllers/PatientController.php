<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\TinnitusMapping;

class PatientController extends Controller
{
    /**
     * Devuelve el listado de pacientes para el profesional logueado.
     */
    public function index()
    {
        // En un escenario real, filtraríamos por doctor_id
        // $patients = Patient::where('doctor_id', Auth::user()->doctor_id)->get();
        
        $patients = Patient::with(['clinicalHistory', 'exposure', 'habits', 'tinnitusProfile', 'tinnitusMapping'])->get();
        
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
            'data' => $patient->load(['clinicalHistory', 'exposure', 'habits', 'tinnitusProfile', 'tinnitusMapping'])
        ]);
    }

    /**
     * Obtiene toda la ficha técnica (5 pestañas) de un paciente, incluyendo sesiones de audiometría y equipamiento.
     */
    public function show($id)
    {
        $patient = Patient::with([
            'clinicalHistory', 
            'exposure', 
            'habits', 
            'tinnitusProfile',
            'tinnitusMapping',
            'sessions' => function($q) {
                $q->orderBy('created_at', 'desc');
            },
            'hearingAids'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $patient
        ]);
    }

    /**
     * Guarda una nueva sesión de audiometría.
     */
    public function saveAudiometry(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);

        $session = $patient->sessions()->create([
            'type' => $request->input('type', 'Seguimiento'),
            'audiometry_data' => $request->input('audiometry_data'),
            'metadata' => $request->input('metadata', []),
            'notes' => $request->input('notes'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sesión de audiometría guardada correctamente',
            'data' => $session
        ]);
    }

    /**
     * Guarda o actualiza el perfilado de tinnitus específico de un paciente.
     */
    public function saveProfiling(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);
        
        $profile = $patient->tinnitusProfile()->updateOrCreate(
            ['patient_id' => $id],
            [
                'initiated_by' => Auth::id() ?? 1, // Fallback para desarrollo
                'reliability_index' => $request->input('reliability_index'),
                'sleep_quality' => $request->input('factors.sleep'),
                'stress_level' => $request->input('factors.stress'),
                'noise_exposure' => $request->input('factors.noise'),
                'health_state' => $request->input('factors.health'),
                'fatigue_level' => $request->input('factors.fatigue'),
                'has_cold' => $request->input('symptoms.cold'),
                'has_puna' => $request->input('symptoms.puna'),
                'has_throat_pain' => $request->input('symptoms.throat'),
                'alcohol_intake' => $request->input('symptoms.alcohol') ? 1 : 0,
                'frequency_perception' => $request->input('perceptions.left'), // Simplificación
                'left_freq_selected' => $request->input('perceptions.left'),
                'right_freq_selected' => $request->input('perceptions.right'),
                'recommendations' => [
                    'observations' => $request->input('observations')
                ],
                // Agregar otros campos si se expanden en la vista
            ]
        );

        // Opcional: Crear una sesión clínica para el historial
        $patient->sessions()->create([
            'type' => 'Perfilado Tinnitus',
            'audiometry_data' => $request->all(), // Guardamos el snapshot completo
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Perfil de tinnitus guardado correctamente',
            'data' => $profile
        ]);
    }

    /**
     * Guarda la configuración de mapeo sonoro de un paciente.
     */
    public function saveMapping(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);

        $mapping = $patient->tinnitusMapping()->updateOrCreate(
            ['patient_id' => $id],
            [
                'initiated_by' => Auth::id() ?? 1,
                'left_layers_config' => $request->input('left.layers'),
                'right_layers_config' => $request->input('right.layers'),
                'config_version' => '1.0'
            ]
        );

        // Crear registro en historial
        $patient->sessions()->create([
            'type' => 'Mapeo Tinnitus',
            'audiometry_data' => $request->all(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mapeo sonoro archivado correctamente',
            'data' => $mapping
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
