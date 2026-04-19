# Futura Implementación: Mapa de Densidad Espectral Histórico

Este documento detalla la hoja de ruta para evolucionar la visualización diagnóstica de TinitusAI hacia un modelo de probabilidad y consistencia temporal.

## 1. Concepto: Mapa de Densidad de Consistencia
Transformar la gráfica de Superposición de una "foto fija" a una "intervención temporal". Al superponer múltiples sesiones de mapeo, las áreas donde el paciente identifica consistentemente su acúfeno aparecerán más intensas, mientras que las variaciones esporádicas serán más tenues.

## 2. Cambios Requeridos

### A. Store & Data Handling
- **Getter `mappingHistory`**: Filtrar `patientHistory` en Pinia para extraer únicamente las sesiones de tipo `Mapeo Tinnitus`.
- **Análisis de Coincidencia**: Comparar frecuencias (Hz) entre sesiones para generar un índice de confiabilidad diagnóstica.

### B. Visualización de Densidad (Frontend)
- **Modo de Mezcla Aditivo**: Implementar en el plugin de Chart.js un renderizado con opacidad acumulativa (0.1 por sesión). 
- **Suavizado de Espectro (Smoothing)**: Cambiar las franjas sólidas por gradientes radiales que simulen una "nube de probabilidad" clínica.
- **Selector de Modo**: Switch en la UI para alternar entre "Última Sesión" (Foco actual) y "Mapa de Densidad" (Foco histórico).

## 3. Valor Clínico (Puntos de Defensa)
- **Validación del Paciente**: Permite saber si el paciente es consistente en su percepción o si hay factores externos que desplazan el acúfeno.
- **Diagnóstico basado en Evidencia Temporal**: Pasar de un dibujo a un mapa estadístico de la "huella sonora" del individuo.

---

> [!TIP]
> Esta implementación posicionará a TinitusAI a la vanguardia de la audiología digital, permitiendo ver no solo "qué escucha" el paciente, sino "qué tan seguro está" de lo que escucha.
