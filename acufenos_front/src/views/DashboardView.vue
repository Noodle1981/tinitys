<script setup>
import { useTinnitusStore } from '../stores/tinnitusStore'
import { 
  Activity, 
  Calendar, 
  TrendingUp, 
  AlertCircle,
  Ear,
  Brain,
  Zap,
  CheckCircle2
} from 'lucide-vue-next'
import Card from 'primevue/card'
import ProgressBar from 'primevue/progressbar'

const store = useTinnitusStore()

const getIntensityColor = (value) => {
  if (value >= 80) return 'text-accent-red'
  if (value >= 50) return 'text-accent-orange'
  return 'text-green-500'
}

const getBgColor = (type) => {
  switch (type) {
    case 'precaution': return 'bg-red-50 border-red-100 text-red-700'
    case 'therapy': return 'bg-blue-50 border-blue-100 text-blue-700'
    case 'lifestyle': return 'bg-amber-50 border-amber-100 text-amber-700'
    default: return 'bg-primary-50 border-primary-100 text-primary-700'
  }
}
</script>

<template>
  <div class="space-y-8 animate-in fade-in duration-500">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
      <div>
        <h1 class="text-3xl font-bold text-primary-900 tracking-tight">Resumen Clínico</h1>
        <p class="text-primary-500 mt-2 flex items-center gap-2">
          <Calendar class="w-4 h-4" />
          Paciente: <span class="font-semibold text-primary-700">{{ store.patientName }}</span> ({{ store.patient.age }} años)
        </p>
      </div>
      <div class="flex items-center gap-3">
        <div class="px-4 py-2 bg-white border border-primary-200 rounded-xl shadow-sm">
          <p class="text-[10px] uppercase tracking-wider font-bold text-primary-400">Última Evaluación</p>
          <p class="text-sm font-semibold text-primary-700">16 Abr, 2026</p>
        </div>
        <button class="px-6 py-2.5 bg-accent-red text-white font-bold rounded-xl shadow-lg shadow-accent-red/20 hover:bg-accent-red/90 transition-all flex items-center gap-2">
          <Activity class="w-4 h-4" />
          Nueva Sesión
        </button>
      </div>
    </div>

    <!-- Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Reliability Index -->
      <Card class="border-none shadow-sm overflow-hidden">
        <template #content>
          <div class="flex items-center justify-between mb-4">
            <div class="p-2 bg-blue-50 rounded-lg">
              <TrendingUp class="w-5 h-5 text-blue-500" />
            </div>
            <span class="text-xs font-bold text-blue-500 px-2 py-1 bg-blue-50 rounded-full">Óptima</span>
          </div>
          <h3 class="text-sm font-bold text-primary-400 uppercase tracking-wider text-[11px]">Índice de Confiabilidad</h3>
          <p class="text-4xl font-bold text-primary-900 mt-2">{{ store.latestProfile.reliability_index }}%</p>
          <div class="mt-4">
            <ProgressBar :value="store.latestProfile.reliability_index" :showValue="false" class="h-2 rounded-full" />
            <p class="text-[11px] text-primary-500 mt-2">Basado en la consistencia de respuestas del paciente.</p>
          </div>
        </template>
      </Card>

      <!-- Intensity Right Ear -->
      <Card class="border-none shadow-sm overflow-hidden">
        <template #content>
          <div class="flex items-center justify-between mb-4">
            <div class="p-2 bg-red-50 rounded-lg">
              <Ear class="w-5 h-5 text-accent-red" />
            </div>
            <span class="text-[10px] font-bold text-accent-red px-2 py-1 bg-red-50 rounded-full border border-red-100 uppercase">Oído Derecho</span>
          </div>
          <h3 class="text-sm font-bold text-primary-400 uppercase tracking-wider text-[11px]">Intensidad Percibida (EVA)</h3>
          <p class="text-4xl font-bold text-primary-900 mt-2">{{ store.intensityEva.right_ear.value }}/100</p>
          <p :class="['text-sm font-bold mt-2', getIntensityColor(store.intensityEva.right_ear.value)]">
            Nivel {{ store.intensityEva.right_ear.label }}
          </p>
        </template>
      </Card>

      <!-- Intensity Left Ear -->
      <Card class="border-none shadow-sm overflow-hidden">
        <template #content>
          <div class="flex items-center justify-between mb-4">
            <div class="p-2 bg-accent-orange/10 rounded-lg">
              <Ear class="w-5 h-5 text-accent-orange" />
            </div>
            <span class="text-[10px] font-bold text-accent-orange px-2 py-1 bg-accent-orange/10 rounded-full border border-accent-orange/20 uppercase">Oído Izquierdo</span>
          </div>
          <h3 class="text-sm font-bold text-primary-400 uppercase tracking-wider text-[11px]">Intensidad Percibida (EVA)</h3>
          <p class="text-4xl font-bold text-primary-900 mt-2">{{ store.intensityEva.left_ear.value }}/100</p>
          <p :class="['text-sm font-bold mt-2', getIntensityColor(store.intensityEva.left_ear.value)]">
            Nivel {{ store.intensityEva.left_ear.label }}
          </p>
        </template>
      </Card>
    </div>

    <!-- Recommendations & Profile Details -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Clinical Recommendations -->
      <div class="space-y-6">
        <h2 class="text-xl font-bold text-primary-900 flex items-center gap-2">
          <Brain class="w-5 h-5 text-accent-red" />
          Recomendaciones Clínicas
        </h2>
        <div 
          v-for="rec in store.recommendations" 
          :key="rec.id"
          :class="['p-4 rounded-2xl border flex gap-4 transition-all hover:shadow-md cursor-default', getBgColor(rec.type)]"
        >
          <div class="shrink-0 mt-1">
            <Zap v-if="rec.type === 'therapy'" class="w-5 h-5" />
            <AlertCircle v-else-if="rec.type === 'precaution'" class="w-5 h-5" />
            <Activity v-else class="w-5 h-5" />
          </div>
          <p class="text-sm font-medium leading-relaxed">{{ rec.text }}</p>
        </div>
      </div>

      <!-- Psycho-Acoustic Factors -->
      <div class="bg-white p-6 rounded-3xl border border-primary-200 shadow-sm">
        <h2 class="text-xl font-bold text-primary-900 mb-6 flex items-center gap-2">
          <CheckCircle2 class="w-5 h-5 text-green-500" />
          Factores Psico-acústicos
        </h2>
        <div class="space-y-6">
          <div v-for="(value, key) in store.latestProfile.factors" :key="key">
            <div class="flex justify-between text-xs font-bold uppercase tracking-widest text-primary-400 mb-2">
              <span>{{ key.replace('_', ' ') }}</span>
              <span class="text-primary-900">{{ value * 20 }}%</span>
            </div>
            <div class="h-1.5 w-full bg-primary-100 rounded-full overflow-hidden">
              <div 
                class="h-full bg-primary-900 rounded-full" 
                :style="{ width: `${value * 20}%` }"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
:deep(.p-progressbar) {
  background: #f1f5f9;
}
:deep(.p-progressbar-value) {
  background: #3b82f6;
  border-radius: 9999px;
}
:deep(.p-card) {
  border-radius: 1.5rem;
}
</style>
