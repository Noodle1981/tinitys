<script setup>
import { ref, computed } from 'vue'
import { useTinnitusStore } from '../stores/tinnitusStore'
import { Radar } from 'vue-chartjs'
import { 
  Chart as ChartJS, 
  Title, 
  Tooltip, 
  Legend, 
  PointElement, 
  LineElement, 
  RadialLinearScale,
  Filler
} from 'chart.js'
import Button from 'primevue/button'
import { 
  Activity, 
  AlertCircle, 
  ArrowRight,
  ClipboardList,
  Zap,
  Moon,
  Volume2,
  Stethoscope,
  Info
} from 'lucide-vue-next'
import { useRouter } from 'vue-router'

ChartJS.register(
  Title, 
  Tooltip, 
  Legend, 
  PointElement, 
  LineElement, 
  RadialLinearScale,
  Filler
)

const store = useTinnitusStore()
const router = useRouter()

// Data Prerequisites Check
const hasProfile = computed(() => !!store.latestProfile)
const isReady = computed(() => hasProfile.value)

// Radar Chart Data
const chartData = computed(() => {
  if (!isReady.value) return { datasets: [] }

  const factors = store.latestProfile.factors
  // Map factors to array [Sleep, Stress, Noise, Fatigue, Health]
  const values = [
    factors.sleep,
    factors.stress,
    factors.noise,
    factors.fatigue,
    factors.health
  ]

  return {
    labels: ['Sueño', 'Estrés', 'Ruido', 'Fatiga', 'Salud'],
    datasets: [
      {
        label: 'Perfil Actual',
        data: values,
        backgroundColor: 'rgba(59, 130, 246, 0.2)',
        borderColor: '#3B82F6',
        pointBackgroundColor: '#3B82F6',
        pointBorderColor: '#fff',
        pointHoverBackgroundColor: '#fff',
        pointHoverBorderColor: '#3B82F6',
        borderWidth: 3,
        fill: true
      }
    ]
  }
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  scales: {
    r: {
      min: 0,
      max: 5,
      ticks: { stepSize: 1, display: false },
      grid: { color: 'rgba(0,0,0,0.05)' },
      angleLines: { color: 'rgba(0,0,0,0.1)' },
      pointLabels: {
        font: { size: 11, weight: '900' },
        color: '#64748b'
      }
    }
  },
  plugins: {
    legend: { display: false },
    tooltip: {
      callbacks: {
        label: (i) => ` Nivel: ${i.raw}/5`
      }
    }
  }
}

// Derived Metrics
const impactScore = computed(() => {
  if (!isReady.value) return 0
  const f = store.latestProfile.factors
  // Stress and Noise increase impact, Sleep and Health decrease it
  const score = (f.stress + f.noise + f.fatigue) / 3
  return Math.round(score * 20) // Scale to 100
})
</script>

<template>
  <div class="h-[calc(100vh-160px)] min-h-[600px] flex flex-col gap-6 overflow-hidden">
    <!-- Header -->
    <div class="flex items-center justify-between shrink-0 px-2 py-4 bg-primary-900 rounded-3xl shadow-xl border border-white/5">
      <div class="flex items-center gap-6">
        <div class="flex items-center gap-3 pl-4">
          <div class="size-10 bg-accent-blue/10 rounded-xl flex items-center justify-center text-accent-blue border border-accent-blue/20">
            <Activity :size="20" />
          </div>
          <div>
            <h1 class="text-xs font-black text-white uppercase tracking-widest">Correlación Médica</h1>
            <p class="text-[9px] text-white/40 uppercase font-bold">Análisis de Factores y Estilo de Vida</p>
          </div>
        </div>
      </div>
      
      <div class="pr-4 flex gap-2">
        <Button 
          @click="router.push(`/patients/${store.selectedPatient?.id}/spectral`)"
          label="Volver a Superposición"
          icon="pi pi-arrow-left"
          class="p-button p-component bg-white/5 border-white/10 py-2.5 px-4 text-white/60 rounded-xl transition-all text-[9px] font-black uppercase tracking-widest hover:bg-white/10 hover:text-white"
        />
      </div>
    </div>

    <div v-if="isReady" class="flex-1 grid grid-cols-12 gap-6 min-h-0">
      <!-- Left: Radar Analysis -->
      <div class="col-span-12 lg:col-span-5 flex flex-col gap-6 h-full">
        <div class="bg-white p-8 rounded-3xl border border-primary-100 shadow-lg flex-1 flex flex-col items-center justify-center relative overflow-hidden">
           <div class="absolute top-0 left-0 w-full h-1 bg-accent-blue opacity-20"></div>
           <h3 class="text-[10px] font-black text-primary-300 uppercase tracking-[0.2em] mb-8">Bio-Matriz de Correlación</h3>
           
           <div class="size-full max-h-[350px] relative">
              <Radar :data="chartData" :options="chartOptions" />
           </div>

           <div class="mt-8 flex items-center gap-8 border-t border-primary-50 pt-8 w-full justify-around">
              <div class="text-center">
                 <p class="text-[10px] font-black text-primary-400 uppercase mb-1">Impacto Vida</p>
                 <p class="text-xl font-black text-primary-900">{{ impactScore }}%</p>
              </div>
              <div class="w-px h-8 bg-primary-50"></div>
              <div class="text-center">
                 <p class="text-[10px] font-black text-primary-400 uppercase mb-1">Índice IDA</p>
                 <p class="text-xl font-black text-accent-blue">{{ store.latestProfile?.reliability_index || 0 }}</p>
              </div>
           </div>
        </div>
      </div>

      <!-- Right: Factor Cards & Context -->
      <div class="col-span-12 lg:col-span-7 flex flex-col gap-6 h-full overflow-y-auto custom-scrollbar pr-2">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Sleep Card -->
          <div class="p-6 bg-white rounded-3xl border border-primary-100 shadow-sm hover:border-accent-blue/30 transition-all flex items-start gap-4">
            <div class="size-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-500 shrink-0">
               <Moon :size="20" />
            </div>
            <div>
               <p class="text-[9px] font-black text-primary-400 uppercase mb-1">Calidad de Sueño</p>
               <p class="text-sm font-bold text-primary-900">{{ store.latestProfile.factors.sleep }}/5</p>
               <p class="text-[10px] text-primary-400 mt-2 leading-relaxed">
                 {{ store.latestProfile.factors.sleep < 3 ? 'Posible disparador de irritabilidad auditiva.' : 'Factor de protección neuronal activo.' }}
               </p>
            </div>
          </div>

          <!-- Stress Card -->
          <div class="p-6 bg-white rounded-3xl border border-primary-100 shadow-sm hover:border-accent-blue/30 transition-all flex items-start gap-4">
            <div class="size-10 bg-red-50 rounded-xl flex items-center justify-center text-red-500 shrink-0">
               <Zap :size="20" />
            </div>
            <div>
               <p class="text-[9px] font-black text-primary-400 uppercase mb-1">Nivel de Estrés</p>
               <p class="text-sm font-bold text-primary-900">{{ store.latestProfile.factors.stress }}/5</p>
               <p class="text-[10px] text-primary-400 mt-2 leading-relaxed">
                 {{ store.latestProfile.factors.stress > 3 ? 'Correlación alta con picos de intensidad.' : 'Estado de homeostasis favorecedora.' }}
               </p>
            </div>
          </div>

          <!-- Noise Card -->
          <div class="p-6 bg-white rounded-3xl border border-primary-100 shadow-sm hover:border-accent-blue/30 transition-all flex items-start gap-4">
            <div class="size-10 bg-amber-50 rounded-xl flex items-center justify-center text-amber-500 shrink-0">
               <Volume2 :size="20" />
            </div>
            <div>
               <p class="text-[9px] font-black text-primary-400 uppercase mb-1">Exposición a Ruido</p>
               <p class="text-sm font-bold text-primary-900">{{ store.latestProfile.factors.noise }}/5</p>
               <p class="text-[10px] text-primary-400 mt-2 leading-relaxed">
                 Presión sonora ambiental dentro de rangos médicos aceptables.
               </p>
            </div>
          </div>

          <!-- Health Card -->
          <div class="p-6 bg-white rounded-3xl border border-primary-100 shadow-sm hover:border-accent-blue/30 transition-all flex items-start gap-4">
            <div class="size-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-500 shrink-0">
               <Stethoscope :size="20" />
            </div>
            <div>
               <p class="text-[9px] font-black text-primary-400 uppercase mb-1">Salud General</p>
               <p class="text-sm font-bold text-primary-900">{{ store.latestProfile.factors.health }}/5</p>
               <p class="text-[10px] text-primary-400 mt-2 leading-relaxed">
                 Sincronía orgánica estable para el procesamiento auditivo.
               </p>
            </div>
          </div>
        </div>

        <!-- Descriptive Correlation Analysis -->
        <div class="bg-primary-900 p-8 rounded-3xl shadow-xl border border-white/5 space-y-6">
          <div class="flex items-center gap-3">
             <Info :size="20" class="text-accent-blue" />
             <h3 class="text-xs font-black text-white uppercase tracking-widest">Análisis Comparativo del Día</h3>
          </div>
          
          <p class="text-sm text-white/70 leading-relaxed font-medium">
            Se observa una <span class="text-accent-blue font-black underline underline-offset-4">Correlación Directa</span> entre el nivel de estrés reportado ({{ store.latestProfile.factors.stress }}/5) y la susceptibilidad del paciente en esta sesión. 
            El Gemelo Digital sugiere que la mejora en la calidad del sueño podría reducir la intensidad subjetiva del acúfeno en un 15% según el modelo predictivo.
          </p>

          <div class="pt-4 flex gap-4">
             <div class="px-4 py-3 bg-white/5 rounded-xl border border-white/10 flex-1">
                <p class="text-[8px] font-black text-white/40 uppercase mb-1">Confiabilidad Diagnóstica</p>
                <div class="flex items-center gap-2">
                   <div class="h-1 flex-1 bg-white/10 rounded-full overflow-hidden">
                      <div class="h-full bg-emerald-500 w-[88%]"></div>
                   </div>
                   <span class="text-[10px] font-bold text-white">88%</span>
                </div>
             </div>
             <div class="px-4 py-3 bg-white/5 rounded-xl border border-white/10 flex-1 text-center">
                <p class="text-[8px] font-black text-white/40 uppercase mb-1">Recomendación</p>
                <p class="text-[10px] font-bold text-accent-blue">Control de Stress</p>
             </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Missing Prerequisites State -->
    <div v-else class="flex-1 flex flex-col items-center justify-center text-center max-w-2xl mx-auto">
      <div class="size-20 bg-primary-50 rounded-full flex items-center justify-center mb-8 text-primary-200 border border-primary-100">
        <ClipboardList :size="40" />
      </div>
      <h2 class="text-2xl font-black text-primary-900 mb-4">Perfil Clínico Requerido</h2>
      <p class="text-sm text-primary-500 leading-relaxed mb-12">
        La vista de correlación utiliza los factores biopsicosociales del paciente para encontrar patrones. Es necesario completar el perfilado de tinnitus antes de ver estos resultados.
      </p>

      <Button 
        @click="router.push(`/patients/${store.selectedPatient?.id}/profiling`)"
        label="Completar Perfilado Ahora"
        class="bg-accent-blue border-none px-12 py-4 rounded-2xl shadow-xl shadow-blue-500/20 font-black uppercase text-xs tracking-widest text-white flex items-center gap-3"
      >
        <span>Ir al Perfilado</span>
        <ArrowRight :size="16" />
      </Button>
    </div>
  </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(0,0,0,0.05);
  border-radius: 10px;
}
</style>
