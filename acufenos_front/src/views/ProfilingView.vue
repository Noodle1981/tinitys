<script setup>
import { ref, computed } from 'vue'
import { useTinnitusStore } from '../stores/tinnitusStore'
import Slider from 'primevue/slider'
import Checkbox from 'primevue/checkbox'
import SelectButton from 'primevue/selectbutton'
import Textarea from 'primevue/textarea'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import { 
  Activity, 
  Moon, 
  Zap, 
  Volume2, 
  Stethoscope, 
  AlertTriangle,
  CheckCircle2,
  Clock,
  ClipboardList,
  Save
} from 'lucide-vue-next'

const store = useTinnitusStore()

// State
const activeEar = ref('left')
const factors = ref({
  sleep: 3,
  stress: 2,
  noise: 1,
  fatigue: 2,
  health: 4
})

const symptoms = ref({
  alcohol: false,
  puna: false,
  cold: false,
  throat: false
})

const perceptions = ref({
  left: 'Medio ~2kHz',
  right: 'Medio ~2kHz'
})

const observations = ref(store.latestProfile?.observations || '')
const saveDialogVisible = ref(false)

// Sync from store if exists
if (store.latestProfile) {
  factors.value = { ...store.latestProfile.factors }
  symptoms.value = { ...store.latestProfile.symptoms }
  if (store.latestProfile.perceptions) {
    perceptions.value = { ...store.latestProfile.perceptions }
  }
}

const freqOptions = [
  { label: 'Grave', value: 'Grave ~500Hz' },
  { label: 'Medio', value: 'Medio ~2kHz' },
  { label: 'Agudo', value: 'Agudo ~4kHz' },
  { label: 'Extremo', value: 'Muy agudo ~8kHz' }
]

// Reliability Index Logic (Legacy)
const reliabilityIndex = computed(() => {
  // 1. Factors (Max 50)
  const factorPts = ((factors.value.stress - 1) * 2.5) +
                    ((factors.value.sleep - 1) * 2.5) +
                    ((factors.value.noise - 1) * 2.5) +
                    ((factors.value.health - 1) * 2.5) +
                    ((factors.value.fatigue - 1) * 2.5)

  // 2. Symptoms (Max 50)
  const symptomPts = (symptoms.value.alcohol ? 10 : 0) +
                     (symptoms.value.puna ? 10 : 0) +
                     (symptoms.value.throat ? 10 : 0) +
                     (symptoms.value.cold ? 20 : 0)

  return Math.round(factorPts + symptomPts)
})

const getStatusColor = (val) => {
  if (val >= 70) return '#EF4444' // Red - Critical Risk
  if (val >= 45) return '#F97316' // Orange - Unfavorable
  if (val >= 25) return '#F59E0B' // Amber - Acceptable
  return '#10B981' // Green - Optimal
}

const getStatusLabel = (val) => {
  if (val >= 70) return 'Riesgo Crítico'
  if (val >= 45) return 'Desfavorable'
  if (val >= 25) return 'Aceptable'
  return 'Óptimo'
}

const saveProfile = () => {
  saveDialogVisible.value = true
}

const confirmSave = () => {
  const profile = {
    date: new Date().toISOString(),
    reliability_index: reliabilityIndex.value,
    factors: { ...factors.value },
    symptoms: { ...symptoms.value },
    observations: observations.value,
    perceptions: { ...perceptions.value }
  }
  
  store.latestProfile = profile
  store.patientHistory.unshift({
    id: 'prof_' + Date.now(),
    date: new Date().toLocaleDateString('es-ES', { day: 'numeric', month: 'short', year: 'numeric' }),
    type: 'Perfilado Tinnitus',
    data: profile
  })

  saveDialogVisible.value = false
  alert('Perfil Clínico archivado correctamente en el Gemelo Digital.')
}
</script>

<template>
  <div class="h-[calc(100vh-160px)] min-h-[600px] flex flex-col gap-6 overflow-hidden">
    <!-- Header & Clinical Console -->
    <div class="flex items-center justify-between shrink-0 px-2 py-4 bg-primary-900 rounded-3xl shadow-xl border border-white/5">
      <div class="flex items-center gap-6">
        <!-- Title & Icon -->
        <div class="flex items-center gap-3 pl-4">
          <div class="size-10 bg-accent-blue/10 rounded-xl flex items-center justify-center text-accent-blue border border-accent-blue/20">
            <Activity :size="20" />
          </div>
          <div>
            <h1 class="text-xs font-black text-white uppercase tracking-widest">Perfilado</h1>
            <p class="text-[9px] text-white/40 uppercase font-bold">Diagnóstico de Confiabilidad</p>
          </div>
        </div>

        <div class="hidden md:flex items-center gap-2 px-3 py-1.5 bg-white/5 border border-white/10 rounded-xl text-[9px] font-black text-white/40 uppercase tracking-widest">
          <Clock :size="12" />
          {{ new Date().toLocaleDateString() }}
        </div>
      </div>
      
      <!-- Action Diskette -->
      <div class="pr-4">
        <Button 
          @click="saveProfile" 
          v-tooltip.bottom="'Archivar Perfil Clínico'"
          class="p-button p-component bg-emerald-500 border-none py-3 px-5 shadow-lg shadow-emerald-500/10 text-white rounded-xl transition-all duration-300 flex items-center gap-2 hover:bg-emerald-600" 
        >
          <Save :size="18" />
          <span class="text-[10px] font-black uppercase tracking-widest hidden md:block">Archivar Perfil</span>
        </Button>
      </div>
    </div>

    <div class="grid grid-cols-12 gap-6 flex-1 min-h-0">
      <!-- Left Column: Gauge & Frequency -->
      <div class="col-span-12 lg:col-span-4 flex flex-col gap-6 h-full">
        <!-- Reliability Gauge Card -->
        <div class="bg-white p-6 rounded-2xl border border-primary-100 shadow-sm flex flex-col items-center justify-center text-center relative overflow-hidden flex-1">
          <div class="absolute top-0 left-0 w-full h-1" :style="{ backgroundColor: getStatusColor(reliabilityIndex) }"></div>
          
          <p class="text-[10px] uppercase font-black text-primary-400 tracking-[0.2em] mb-6">Índice de Riesgo Clínico</p>
          
          <div class="relative flex items-center justify-center mb-6">
            <svg class="size-48 transform -rotate-90">
              <circle cx="96" cy="96" r="88" stroke="currentColor" stroke-width="12" fill="transparent" class="text-primary-50" />
              <circle cx="96" cy="96" r="88" stroke="currentColor" stroke-width="12" fill="transparent" 
                stroke-dasharray="552.9" 
                :style="{ strokeDashoffset: 552.9 - (552.9 * reliabilityIndex / 100), stroke: getStatusColor(reliabilityIndex) }"
                class="transition-all duration-1000 ease-out" />
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center">
              <span class="text-5xl font-black text-primary-900 leading-none">{{ reliabilityIndex }}</span>
              <span class="text-[10px] font-bold text-primary-400 mt-2 uppercase tracking-widest">/ 100 pts</span>
            </div>
          </div>

          <div 
            class="px-6 py-2 rounded-full font-black text-xs uppercase tracking-widest transition-colors duration-500"
            :style="{ backgroundColor: getStatusColor(reliabilityIndex) + '15', color: getStatusColor(reliabilityIndex), border: `2px solid ${getStatusColor(reliabilityIndex)}30` }"
          >
            {{ getStatusLabel(reliabilityIndex) }}
          </div>

          <p class="text-xs text-primary-500 mt-6 leading-relaxed px-4">
            {{ reliabilityIndex >= 45 
                ? 'Se detecta interferencia clínica probable. Los resultados de la audiometría podrían verse afectados.' 
                : 'Las condiciones sistémicas son óptimas para un diagnóstico de alta confiabilidad.' }}
          </p>
        </div>

        <!-- Ear & Frequency Context -->
        <div class="bg-primary-900 p-6 rounded-2xl shadow-xl border border-white/5 space-y-6 shrink-0">
          <div class="flex p-1 bg-white/5 rounded-xl gap-1">
            <button 
              @click="activeEar = 'left'"
              :class="[
                'flex-1 py-2 text-[10px] font-black uppercase tracking-widest rounded-lg transition-all border-2', 
                activeEar === 'left' ? 'text-white border-white/20' : 'bg-transparent border-transparent text-white/40 hover:text-white/60'
              ]"
              :style="activeEar === 'left' ? { backgroundColor: '#3B82F6' } : {}"
            >
              Oído Izquierdo
            </button>
            <button 
              @click="activeEar = 'right'"
              :class="[
                'flex-1 py-2 text-[10px] font-black uppercase tracking-widest rounded-lg transition-all border-2', 
                activeEar === 'right' ? 'text-white border-white/20' : 'bg-transparent border-transparent text-white/40 hover:text-white/60'
              ]"
              :style="activeEar === 'right' ? { backgroundColor: '#EF4444' } : {}"
            >
              Oído Derecho
            </button>
          </div>

          <div class="space-y-4">
            <p class="text-[10px] uppercase font-black text-white/40 tracking-[0.2em]">Frecuencia Percibida</p>
            <div class="grid grid-cols-2 gap-2">
              <button 
                v-for="opt in freqOptions" 
                :key="opt.value"
                @click="perceptions[activeEar] = opt.value"
                :class="[
                  'py-3 px-2 text-[9px] font-black uppercase tracking-tight rounded-xl transition-all text-center border-2 flex items-center justify-center gap-2',
                  perceptions[activeEar] === opt.value 
                    ? 'text-white border-white/20 shadow-lg'
                    : 'bg-white/5 border-white/5 text-white/30 hover:bg-white/10'
                ]"
                :style="perceptions[activeEar] === opt.value ? { backgroundColor: activeEar === 'left' ? '#3B82F6' : '#EF4444' } : {}"
              >
                <div 
                  v-if="perceptions[activeEar] === opt.value"
                  class="size-1.5 rounded-full bg-white animate-pulse"
                ></div>
                {{ opt.label }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Factors & Symptoms -->
      <div class="col-span-12 lg:col-span-8 flex flex-col gap-6 min-h-0 h-full">
        <div class="bg-white p-8 rounded-2xl border border-primary-100 shadow-sm flex-1 flex flex-col min-h-0 overflow-y-auto custom-scrollbar">
          <h2 class="text-sm font-black text-primary-900 uppercase tracking-widest mb-8 flex items-center gap-3">
            <Activity :size="18" class="text-accent-blue" />
            Factores Sistémicos y Estilo de Vida
          </h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-10 mb-10">
            <!-- Sliders -->
            <div class="space-y-3">
              <div class="flex justify-between items-end mb-2">
                <label class="text-[10px] font-black text-primary-700 uppercase tracking-widest flex items-center gap-2">
                  <Moon :size="14" class="text-primary-400" /> Calidad de Sueño
                </label>
                <span class="text-xs font-black text-accent-blue">{{ factors.sleep }}/5</span>
              </div>
              <Slider v-model="factors.sleep" :min="1" :max="5" class="custom-slider" />
            </div>

            <div class="space-y-3">
              <div class="flex justify-between items-end mb-2">
                <label class="text-[10px] font-black text-primary-700 uppercase tracking-widest flex items-center gap-2">
                  <Zap :size="14" class="text-primary-400" /> Nivel de Estrés
                </label>
                <span class="text-xs font-black text-accent-blue">{{ factors.stress }}/5</span>
              </div>
              <Slider v-model="factors.stress" :min="1" :max="5" class="custom-slider" />
            </div>

            <div class="space-y-3">
              <div class="flex justify-between items-end mb-2">
                <label class="text-[10px] font-black text-primary-700 uppercase tracking-widest flex items-center gap-2">
                  <Volume2 :size="14" class="text-primary-400" /> Exposición a Ruido
                </label>
                <span class="text-xs font-black text-accent-blue">{{ factors.noise }}/5</span>
              </div>
              <Slider v-model="factors.noise" :min="1" :max="5" class="custom-slider" />
            </div>

            <div class="space-y-3">
              <div class="flex justify-between items-end mb-2">
                <label class="text-[10px] font-black text-primary-700 uppercase tracking-widest flex items-center gap-2">
                  <Activity :size="14" class="text-primary-400" /> Cansancio Físico
                </label>
                <span class="text-xs font-black text-accent-blue">{{ factors.fatigue }}/5</span>
              </div>
              <Slider v-model="factors.fatigue" :min="1" :max="5" class="custom-slider" />
            </div>

            <div class="md:col-span-2 space-y-3 pt-4">
              <div class="flex justify-between items-end mb-2">
                <label class="text-[10px] font-black text-primary-700 uppercase tracking-widest flex items-center gap-2">
                  <Stethoscope :size="14" class="text-primary-400" /> Estado General de Salud
                </label>
                <span class="text-xs font-black text-accent-blue">{{ factors.health }}/5</span>
              </div>
              <Slider v-model="factors.health" :min="1" :max="5" class="custom-slider" />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
            <!-- Symptoms Checkboxes -->
            <div class="space-y-6">
              <p class="text-[10px] uppercase font-black text-primary-300 tracking-[0.2em] flex items-center gap-2">
                <ClipboardList :size="14" /> Sintomatología Física
              </p>
              <div class="grid grid-cols-1 gap-4">
                <div class="flex items-center gap-3 group cursor-pointer">
                  <Checkbox v-model="symptoms.alcohol" binary inputId="s_alc" />
                  <label for="s_alc" class="text-[11px] font-bold text-primary-600 group-hover:text-primary-800 transition-colors">Consumo de Alcohol</label>
                </div>
                <div class="flex items-center gap-3 group cursor-pointer">
                  <Checkbox v-model="symptoms.puna" binary inputId="s_puna" />
                  <label for="s_puna" class="text-[11px] font-bold text-primary-600 group-hover:text-primary-800 transition-colors">Sensación de Puna / Altura</label>
                </div>
                <div class="flex items-center gap-3 group cursor-pointer">
                  <Checkbox v-model="symptoms.throat" binary inputId="s_throat" />
                  <label for="s_throat" class="text-[11px] font-bold text-primary-600 group-hover:text-primary-800 transition-colors">Dolor de Garganta</label>
                </div>
                <div class="flex items-center gap-3 group cursor-pointer p-3 bg-red-50 rounded-xl border border-red-100/50">
                  <Checkbox v-model="symptoms.cold" binary inputId="s_cold" />
                  <label for="s_cold" class="text-[11px] font-bold text-red-600">Resfrío / Congestión (+20 pts)</label>
                </div>
              </div>
            </div>

            <!-- Observations -->
            <div class="space-y-3">
              <p class="text-[10px] uppercase font-black text-primary-300 tracking-[0.2em]">Observaciones Clínicas</p>
              <Textarea 
                v-model="observations" 
                rows="5" 
                placeholder="Escriba aquí cualquier observación relevante sobre el estado del paciente..."
                class="w-full text-xs p-4 bg-primary-50 border-none rounded-xl focus:ring-2 ring-primary-100 transition-all resize-none"
              />
            </div>
          </div>
        </div>

        <!-- Float Actions REMOVED and MOVED to Header -->
        <div class="flex justify-end gap-3 pt-2 shrink-0">
          <Button label="Restablecer Datos" severity="secondary" text class="text-[10px] uppercase font-black tracking-widest opacity-40 hover:opacity-100 transition-opacity" />
        </div>
      </div>
    </div>
  </div>

  <!-- Confirm Save Modal -->
  <Dialog v-model:visible="saveDialogVisible" modal header="Confirmar Archivo" :style="{ width: '450px' }">
    <div class="space-y-6 pt-4">
      <div class="p-6 bg-primary-50 rounded-2xl border border-primary-100 flex items-center gap-4">
        <div class="size-16 rounded-2xl flex items-center justify-center font-black text-xl shadow-lg" :style="{ backgroundColor: getStatusColor(reliabilityIndex), color: 'white' }">
          {{ reliabilityIndex }}
        </div>
        <div>
          <p class="text-[10px] font-black text-primary-400 uppercase tracking-widest">Riesgo Calculado</p>
          <p class="text-sm font-bold text-primary-900">{{ getStatusLabel(reliabilityIndex) }}</p>
        </div>
      </div>

      <p class="text-xs text-primary-500 leading-relaxed text-center px-4">
        ¿Deseas archivar este perfil clínico en el registro histórico del paciente? Estos datos alimentarán la evolución del Gemelo Digital.
      </p>

      <div class="flex gap-3">
        <Button label="Revisar" severity="secondary" text @click="saveDialogVisible = false" class="text-xs uppercase font-bold flex-1" />
        <Button @click="confirmSave" label="Archivar Perfil" class="bg-emerald-500 border-none text-xs uppercase font-black flex-1 py-4 shadow-xl shadow-emerald-500/20 text-white" />
      </div>
    </div>
  </Dialog>
</template>

<style>
.custom-select-button .p-button {
  @apply bg-white/5 border-none text-white/50 text-[9px] font-black uppercase tracking-widest py-3 transition-all !important;
}
.custom-select-button .p-button.p-highlight {
  @apply bg-white text-primary-900 !important;
}
.custom-slider .p-slider-handle {
  background-color: #3b82f6 !important;
  border: none !important;
  width: 1rem !important;
  height: 1rem !important;
  box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3) !important;
}
.custom-slider .p-slider-range {
  background-color: rgba(59, 130, 246, 0.2) !important;
}
.custom-header-gauge {
  background: conic-gradient(from 180deg, #10B981 0%, #F59E0B 45%, #EF4444 80%);
}
</style>
