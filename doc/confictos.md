# Informe de Conflictos y Deuda Técnica

## 1. Conflictos Identificados

### Dualidad de Datos Clínicos:
Existe una superposición de campos entre la tabla `patients` (laterality, history) y `tinnitus_profiles`. 
- **Riesgo**: Que la lateralidad cambie en un perfil pero no se actualice en la ficha maestra del paciente, generando inconsistencia en los informes.

### Acoplamiento Frontend-Backend:
La estructura del JSON `layers_config` está "hardcoded" tanto en `tinnitus-mapping.blade.php` (método `defaultLayers`) como en los scripts de audio de Alpine.js.
- **Conflicto**: Si se agrega una 5ta capa de sonido, hay que modificar múltiples archivos y el riesgo de romper registros históricos es alto.

## 2. Deuda Técnica

1.  **Archivos Extensos**: `patient-management` viola el Principio de Responsabilidad Única (SRP).
2.  **Estilos Inline**: Presencia de bloques `<style>` y CSS inline abundantes dentro de los componentes Volt. Deberían migrarse a clases de Tailwind o componentes Flux personalizados.
3.  **Falta de Testing**: No se detectaron tests unitarios para la lógica de cálculo del `reliability_index` ni para la persistencia de configuraciones JSON complejas.

## 3. Hoja de Ruta de Resolución

- **Prioridad Alta**: Sincronizar campos maestros de pacientes con perfiles dinámicos.
- **Prioridad Media**: Implementar un `LayerProvider` en PHP que inyecte la configuración de capas tanto al componente Livewire como a Alpine.js desde una única fuente de verdad.
- **Prioridad Baja**: Migración de lógica de base de datos a `Actions` (Laravel/Spatie) para limpiar los componentes Volt.
