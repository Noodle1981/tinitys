# Auditoría de Muestreo Dinámico (Tinnitus Mapping)

## 1. Análisis del Mecanismo
El sistema utiliza un **Mapeador Multicapa** basado en la Web Audio API, capturando 4 dimensiones de sonido:
- **Pure Tone** (Tono puro)
- **Noise** (Ruido de banda)
- **Pulse** (Pulsaciones rítmicas)
- **Sweep** (Barridos de frecuencia)

## 2. Fiabilidad del Muestreo

### Proceso de Captura:
- La captura es **sincrónica** con Alpine.js/Web Audio API.
- Se almacenan valores de sliders (0-100) que se mapean a frecuencias logarítmicas en el frontend.

### Puntos de Mejora en Fiabilidad:
1.  **Calibración del "Master Volume"**: Actualmente el volumen maestro es subjetivo. No hay una referencia de "dB SPL" real, lo cual es comprensible en web, pero dificulta la comparación entre diferentes auriculares/dispositivos.
2.  **Frecuencias de Muestreo**: El mapeo depende de la resolución del slider (100 pasos). Para frecuencias altas (8kHz+), un paso en el slider puede saltar cientos de Hz, perdiendo precisión clínica.

## 3. Recomendaciones de Ingeniería

1.  **Upsampling de Precisión**: Permitir entrada numérica directa para la frecuencia o usar sliders logarítmicos compensados para las zonas de tinnitus (4kHz-12kHz).
2.  **Validación de Consistencia**: Implementar un "Test de Re-test". Pedir al paciente que identifique su tinnitus entre 3 muestras generadas (la suya y 2 aleatorias) para validar el `reliability_index`.
3.  **Captura de Metadatos de Hardware**: Registrar (vía JS) el tipo de salida de audio (si el navegador lo permite) para contextualizar la calidad del mapeo.
