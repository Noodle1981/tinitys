# Proyecto TINITUS - Contexto Maestros

Este documento sirve como ancla de contexto para que cualquier agente de IA entienda la visión, la arquitectura y el estado actual del proyecto.

## 1. Visión y Propósito
**TINITUS** no es solo una app de salud; es una plataforma de **Digital Twin (Gemelo Digital)** enfocada en la audiología. El objetivo es permitir que pacientes con condiciones fluctuantes (como el **Síndrome de Ménière** o **Hipoacusia progresiva**) registren datos periódicos que las audiometrías clínicas tradicionales (espaciadas en meses) no capturan.

### Conceptos Clave:
- **PGHD (Patient-Generated Health Data):** Datos generados por el paciente para seguimiento longitudinal.
- **Cruce de Datos:** Correlacionar el umbral auditivo (Audiometría) con la percepción del Tinnitus (Mapeo).
- **Validación Médica:** Diseñado para ser validado por ORLs y especialistas, con potencial de inversión en el sector (ej. Sonova/Phonak).

## 2. Stack Tecnológico
- **Core:** Laravel 13 + PHP 8.3+.
- **Frontend:** Livewire Volt (Arquitectura funcional de Livewire).
- **UI:** Flux UI (Componentes premium) + Tailwind CSS.
- **Interactividad:** Alpine.js para lógica de tiempo real (Audio/Visuales).
- **Audio:** Web Audio API (Osciladores, Filtros, Panning estéreo).
- **Base de Datos:** SQLite.

## 3. Módulos Implementados

### Etapa 1: Perfil de Tinnitus (`tinnitus-profile`)
- Seguimiento independiente por oído (OI/OD).
- Factores sistémicos: Sueño, Estrés, Ruido, Salud, Alcohol.
- Índice de confiabilidad calculado por oído.

### Etapa 2: Mapeador Multicapa (`tinnitus-mapping`)
- Generación de audio en tiempo real con 4 capas:
    - **Pure:** Tono puro (Sinusoidal).
    - **Noise:** Ruido de banda (Pseudo-ruido inarmónico).
    - **Pulse:** Tonos pulsantes (Frecuencia + Ritmo).
    - **Sweep:** Tonos oscilantes (Barrido de frecuencia).
- **Visuales:** Canvas 2D con animaciones reactivas que scrollean y muestran la forma de onda según los sliders.
- **Panning:** Control estéreo independiente por oído.

## 4. Estructura de Datos (Tablas Clave)
- `patients`: Datos maestros del paciente.
- `tinnitus_profiles`: Contexto clínico y percepción subjetiva.
- `tinnitus_mappings`: Configuración técnica de audio (JSON `left_layers_config` / `right_layers_config`).
- `audiometry_values`: Valores dB HL por frecuencia y oído.

## 5. Próximos Pasos (Roadmap)
1. **Historial Longitudinal:** Vista para que el paciente vea su evolución día a día.
2. **Gráfico de Cruce:** Superposición de Audiometría vs Tinnitus Mapper para identificar zonas de pérdida.
3. **Dashboard para Profesionales:** Vista simplificada para que el ORL valide los datos generados por el paciente.

---
*Este proyecto es liderado por un usuario que es paciente activo (Ménière/Hipoacusia), lo que garantiza que la UX esté validada por la necesidad real.*
