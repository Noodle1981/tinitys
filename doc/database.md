# Auditoría de Base de Datos - Proyecto TINITUS

## 1. Estructura Actual
El proyecto utiliza **SQLite** como motor de persistencia, lo cual es ideal para el concepto de **Digital Twin** individual y portabilidad.

### Tablas Principales:
- `patients`: Almacena datos biográficos y clínicos base (DNI, nombre, lateralidad, comorbilidades).
- `tinnitus_profiles`: Captura el contexto subjetivo (estrés, sueño, alcohol) y percepción general.
- `tinnitus_mappings`: Almacena la configuración técnica del motor de audio (JSON).
- `audiometry_values`: Registro de umbrales auditivos por frecuencia.

## 2. Calidad de Datos

### Fortalezas:
- **Flexibilidad JSON**: El uso de `json` para `left_layers_config` y `right_layers_config` permite evolucionar el motor de audio sin migrar la base de datos constantemente.
- **Integridad Referencial**: Correcta implementación de `constrained()->onDelete('cascade')`.

### Debilidades Detectadas:
- **Tipado Subóptimo**: Campos como `left_ear_intensity` y `right_ear_intensity` en `tinnitus_profiles` están definidos como `string`. Para análisis clínico (promedios, tendencias), deberían ser `integer` o `float`.
- **Campos Volátiles**: `frequency_perception` es un `string` libre. Esto impedirá generar mapas de calor precisos de frecuencias predominantes.

## 3. Recomendaciones Técnicas

### Inmediatas:
1.  **Normalización de Intensidades**: Cambiar tipos de datos de `intensity` a numéricos.
2.  **Índices de Rendimiento**: Agregar índices explícitos en `(patient_id, created_at)` para todas las tablas de seguimiento longitudinal.

### A Largo Plazo:
1.  **Tablas de Metadatos**: Extraer `comorbidities` y `sound_type` a tablas relacionales o constantes estandarizadas para evitar "Data Drift" (ej: que un usuario escriba "Pitido" y otro "pitidó").
2.  **Versioning de JSON**: Incluir un campo `config_version` en `tinnitus_mappings` para saber qué versión del algoritmo de audio generó esos datos.
