# Guía de Implementación: Suite Tinitus AI (Vue 3 + Laravel API)

Este documento define la arquitectura técnica y el contrato de integración entre el frontend de alta fidelidad (Vue 3) y el backend de servicios (Laravel API). **Se elimina cualquier rastro de Livewire**, adoptando un modelo de aplicación desacoplada.

## 1. Arquitectura del Sistema
- **Frontend**: Vue 3 (Composition API) + Pinia (State Management) + Chart.js (Analytics).
- **Backend**: Laravel API (RESTful) + MySQL.
- **Comunicación**: Axios (HTTP Client) con autenticación vía Laravel Sanctum (Stateful/Token).

---

## 2. Estado de Implementación (Etapa por Etapa)

### Etapa 4: Perfilado de Tinnitus (Finalizado)
**Función**: Captura factores biopsicosociales y estilo de vida.
- **Mock**: `latest_profile` en `tinnitus_mock_data.json`.
- **Lo que proveemos al Back**: Objeto con niveles de estrés (1-5), sueño, fatiga, ruido, salud, e intensidad EVA por oído.
- **Lo que necesitamos del Back**: El historial previo del perfil para calcular tendencias y correlaciones actuales.

### Etapa 5: Mapeador Sonoro (Finalizado)
**Función**: Síntesis de audio en tiempo real y mapeo de percepción.
- **Mock**: `latest_mapping` en `tinnitus_mock_data.json`.
- **Lo que proveemos al Back**: Configuración multi-capa (Volumen, Frecuencia, Velocidad) por canal y estado del oído (Sano/Sintomático).
- **Lo que necesitamos del Back**: Guardado persistente de la firma sónica del paciente (JSON estandarizado de capas).

### Etapa 6: Diagnóstico Avanzado (Finalizado)
**Función**: Superposición Sonora (Audiometría vs Mapeo) y Correlación de Vida.
- **Mock**: Integración de `audiometry_history`, `latest_mapping` y `latest_profile`.
- **Lo que proveemos al Back**: Es una vista de consulta (Output).
- **Lo que necesitamos del Back**: Endpoints que entreguen la audiometría y el mapeo en un mismo payload para análisis cruzado espectral.

---

## 3. Especificaciones del Backend (Laravel Side)

### 3.1. Controladores API (Puros)
Se deben evitar los controladores de Livewire. Laravel debe actuar como un proveedor de recursos JSON. Se recomienda el uso de **API Resources** de Laravel.

**Ejemplo de Controller Sugerido:**
```php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;

class TinnitusMappingController extends Controller {
    public function show($patientId) {
        $mapping = TinnitusMapping::where('patient_id', $patientId)->latest()->first();
        return response()->json($mapping); // Mantener estructura del Mock
    }

    public function store(Request $request, $patientId) {
        // Lógica de persistencia de las capas de audio
        return response()->json(['message' => 'Mapeo guardado con éxito'], 201);
    }
}
```

### 3.2. Seguridad y CORS
- **Autenticación**: Laravel Sanctum. El frontend enviará el token `Bearer` en el header `Authorization`.
- **CORS**: Configurar `config/cors.php` para permitir el origen `http://localhost:5173` (Vite).
- **Headers**:
    - `Accept: application/json`
    - `Content-Type: application/json`

### 3.3. Rutas (`api.php`)
```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/patients/{id}/profile', [ProfileController::class, 'show']);
    Route::post('/patients/{id}/profile', [ProfileController::class, 'store']);
    Route::get('/patients/{id}/mapping', [MappingController::class, 'show']);
    Route::post('/patients/{id}/mapping', [MappingController::class, 'store']);
});
```

---

## 4. Contrato de Datos (Basado en Mock)

### Datos de Perfilado (Frontend -> Backend)
```json
{
  "stress": 4,
  "sleep": 2,
  "intensity_eva": 8,
  "reliability_index": 82
}
```

### Datos de Mapeo (Frontend -> Backend)
```json
{
  "left": { "status": "symptomatic", "layers": [ { "id": "tono_puro", "vol": 40, "freq": 60 } ] },
  "right": { "status": "healthy", "layers": [] }
}
```

---

## 5. Estrategia de Pruebas
1.  **Validación de Endpoints**: Usar Postman o Insomnia para verificar que el JSON de Laravel coincida con la estructura del `tinnitus_mock_data.json`.
2.  **Integración Axios**: Migrar el `tinnitusStore.js` para usar llamadas asíncronas en lugar del import estático.

> [!CAUTION]
> **Livewire No Recomendado**: El motor de síntesis sonora construido en Vue requiere acceso directo al DOM y a la Web Audio API, algo que el ciclo de vida de Livewire no puede garantizar de forma eficiente.
