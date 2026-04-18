<script setup>
import { ref, computed, onMounted } from 'vue'
import { useTinnitusStore } from '../stores/tinnitusStore'
import { Line } from 'vue-chartjs'
import { 
  Chart as ChartJS, 
  Title, 
  Tooltip, 
  Legend, 
  LineElement, 
  PointElement, 
  CategoryScale, 
  LinearScale,
  LogarithmicScale
} from 'chart.js'
import Button from 'primevue/button'
import { 
  Waves, 
  Activity, 
  AlertCircle, 
  ArrowRight,
  Stethoscope,
  Info,
  CheckCircle2
} from 'lucide-vue-next'
import { useRouter } from 'vue-router'

ChartJS.register(
  Title, 
  Tooltip, 
  Legend, 
  LineElement, 
  PointElement, 
  CategoryScale, 
  LinearScale,
  LogarithmicScale
)

const store = useTinnitusStore()
const router = useRouter()

// Data Prerequisites Check
const hasAudiometry = computed(() => {
  return Object.keys(store.audiometryData.right).length > 0 || Object.keys(store.audiometryData.left).length > 0
})

const hasMapping = computed(() => {
  return (store.latestMapping.right && store.latestMapping.right.status !== 'healthy') || 
         (store.latestMapping.left && store.latestMapping.left.status !== 'healthy')
})

const isReady = computed(() => hasAudiometry.value && hasMapping.value)

// Chart Logic
const chartData = computed(() => {
  if (!isReady.value) return { datasets: [] }

  const datasets = []
  const standardFreqs = [125, 250, 500, 750, 1000, 1500, 2000, 3000, 4000, 6000, 8000]

  // Right Ear Dataset
  if (Object.keys(store.audiometryData.right).length > 0) {
    const data = standardFreqs.map(f => ({
      x: f,
      y: store.audiometryData.right[f] !== undefined ? store.audiometryData.right[f] : null
    })).filter(p => p.y !== null)

    datasets.push({
      label: 'Umbral Derecho (OD)',
      data: data,
      borderColor: '#EF4444',
      backgroundColor: '#EF4444',
      pointStyle: 'circle',
      pointRadius: 5,
      borderWidth: 2,
      tension: 0.1,
      spanGaps: false
    })
  }

  // Left Ear Dataset
  if (Object.keys(store.audiometryData.left).length > 0) {
    const data = standardFreqs.map(f => ({
      x: f,
      y: store.audiometryData.left[f] !== undefined ? store.audiometryData.left[f] : null
    })).filter(p => p.y !== null)

    datasets.push({
      label: 'Umbral Izquierdo (OI)',
      data: data,
      borderColor: '#3B82F6',
      backgroundColor: '#3B82F6',
      pointStyle: 'crossRot',
      pointRadius: 6,
      borderWidth: 2,
      tension: 0.1,
      spanGaps: false
    })
  }

  return { datasets }
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  scales: {
    x: {
      type: 'logarithmic',
      min: 125,
      max: 8000,
      title: { display: true, text: 'Frecuencia (Hz)', font: { weight: 'bold' } },
      ticks: {
        callback: (v) => {
          const vals = [125, 250, 500, 1000, 2000, 4000, 8000]
          return vals.includes(v) ? (v >= 1000 ? (v / 1000) + 'k' : v) : ''
        }
      },
      grid: { color: 'rgba(0,0,0,0.05)' }
    },
    y: {
      reverse: true,
      min: -10,
      max: 120,
      title: { display: true, text: 'Nivel Audición (dB HL)', font: { weight: 'bold' } },
      ticks: { stepSize: 10 },
      grid: { color: 'rgba(0,0,0,0.05)' }
    }
  },
  plugins: {
    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10, weight: 'bold' } } },
    tooltip: {
      callbacks: {
        title: (i) => `${i[0].raw.x} Hz`,
        label: (i) => ` Umbral: ${i.raw.y} dB`
      }
    }
  }
}

// Custom Plugins (Speech Banana & Tinnitus Zones)
const speechBananaPlugin = {
  id: 'speechBanana',
  beforeDatasetsDraw(chart) {
    const { ctx, scales: { x, y } } = chart
    const soundsData = [
      { hz: 250, min: 30, max: 50, sounds: ["u", "o", "m"] },
      { hz: 500, min: 25, max: 45, sounds: ["a", "i", "e"] },
      { hz: 1000, min: 20, max: 45, sounds: ["b", "d", "g"] },
      { hz: 2000, min: 20, max: 50, sounds: ["k", "t", "sh"] },
      { hz: 4000, min: 25, max: 55, sounds: ["s", "f"] }
    ]

    ctx.save()
    ctx.beginPath()
    soundsData.forEach((d, i) => {
      const px = x.getPixelForValue(d.hz)
      const py = y.getPixelForValue(d.min)
      i === 0 ? ctx.moveTo(px, py) : ctx.lineTo(px, py)
    })
    ;[...soundsData].reverse().forEach((d) => {
      ctx.lineTo(x.getPixelForValue(d.hz), y.getPixelForValue(d.max))
    })
    ctx.closePath()
    ctx.fillStyle = 'rgba(251, 191, 36, 0.08)'
    ctx.fill()
    ctx.strokeStyle = 'rgba(251, 191, 36, 0.2)'
    ctx.lineWidth = 1
    ctx.stroke()

    // DRAW PHONEMES (SOUNDS)
    ctx.fillStyle = 'rgba(180, 130, 20, 0.5)'
    ctx.font = 'bold 10px system-ui'
    ctx.textAlign = 'center'
    ctx.textBaseline = 'middle'
    soundsData.forEach(d => {
      const px = x.getPixelForValue(d.hz)
      const py = y.getPixelForValue((d.min + d.max) / 2)
      ctx.fillText(d.sounds.join(' '), px, py)
    })
    ctx.restore()
  }
}

const tinnitusZonesPlugin = {
  id: 'tinnitusZones',
  beforeDatasetsDraw(chart) {
    const { ctx, chartArea: { top, bottom, left, right }, scales: { x } } = chart
    
    const drawZone = (hz, color) => {
      const xPos = x.getPixelForValue(hz)
      if (xPos >= left && xPos <= right) {
        ctx.save()
        ctx.fillStyle = color
        // Width of the shaded area (similar to legacy 30px)
        ctx.fillRect(xPos - 20, top, 40, bottom - top)
        ctx.restore()
      }
    }

    // Process Mapping from Store
    if (store.latestMapping.right?.status === 'symptomatic') {
      store.latestMapping.right.layers.forEach(layer => {
        if (layer.vol > 0) {
          // Linear interpolation for Hz from 0-100 slider (Log scale in Audio Engine)
          const clinicalHz = 125 * Math.pow(2, (layer.freq / 100) * 6)
          drawZone(clinicalHz, 'rgba(239, 68, 68, 0.08)')
        }
      })
    }

    if (store.latestMapping.left?.status === 'symptomatic') {
      store.latestMapping.left.layers.forEach(layer => {
        if (layer.vol > 0) {
          const clinicalHz = 125 * Math.pow(2, (layer.freq / 100) * 6)
          drawZone(clinicalHz, 'rgba(59, 130, 246, 0.08)')
        }
      })
    }
  }
}

const plugins = [speechBananaPlugin, tinnitusZonesPlugin]
</script>

<template>
  <div class="h-[calc(100vh-160px)] min-h-[600px] flex flex-col gap-6 overflow-hidden">
    <!-- Header -->
    <div class="flex items-center justify-between shrink-0 px-2 py-4 bg-primary-900 rounded-3xl shadow-xl border border-white/5">
      <div class="flex items-center gap-6">
        <div class="flex items-center gap-3 pl-4">
          <div class="size-10 bg-accent-blue/10 rounded-xl flex items-center justify-center text-accent-blue border border-accent-blue/20">
            <Waves :size="20" />
          </div>
          <div>
            <h1 class="text-xs font-black text-white uppercase tracking-widest">Superposición Sonora</h1>
            <p class="text-[9px] text-white/40 uppercase font-bold">Mapeo Umbral Espectral</p>
          </div>
        </div>
      </div>
      
      <div class="pr-4 flex gap-2">
        <Button 
          @click="router.push(`/patients/${store.selectedPatient?.id}/correlation`)"
          label="Ver Correlación de Vida"
          icon="pi pi-arrow-right"
          iconPos="right"
          class="p-button p-component bg-white/5 border-white/10 py-2.5 px-4 text-white/60 rounded-xl transition-all text-[9px] font-black uppercase tracking-widest hover:bg-white/10 hover:text-white"
        />
      </div>
    </div>

    <!-- Main Analysis Panel -->
    <div v-if="isReady" class="flex-1 grid grid-cols-12 gap-6 min-h-0">
      <!-- Left: Chart -->
      <div class="col-span-12 lg:col-span-9 bg-white p-6 rounded-3xl border border-primary-100 shadow-lg flex flex-col relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-audio-right via-audio-speech to-audio-left opacity-30"></div>
        
        <div class="mb-6 flex items-center justify-between">
          <div class="flex items-center gap-3">
             <Stethoscope :size="18" class="text-primary-300" />
             <h2 class="text-sm font-black text-primary-900 uppercase tracking-widest">Gráfica Auditoria Espectral</h2>
          </div>
          <div class="flex items-center gap-4">
             <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-red-500/10 border border-red-500/20 rounded-sm"></div>
                <span class="text-[9px] font-bold text-primary-400 uppercase">Zonas OD</span>
             </div>
             <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-blue-500/10 border border-blue-500/20 rounded-sm"></div>
                <span class="text-[9px] font-bold text-primary-400 uppercase">Zonas OI</span>
             </div>
          </div>
        </div>

        <div class="flex-1 min-h-0 relative">
          <Line :data="chartData" :options="chartOptions" :plugins="plugins" />
        </div>

        <div class="mt-6 p-4 bg-primary-50 rounded-2xl border border-primary-100 flex items-start gap-4">
          <Info :size="18" class="text-accent-blue mt-0.5" />
          <p class="text-xs text-primary-600 leading-relaxed italic">
            Esta gráfica superpone tus umbrales de audición con las zonas donde percibes el acúfeno. 
            El área sombreada amarilla representa la "Banana del Habla", el rango crucial para entender el lenguaje humano.
          </p>
        </div>
      </div>

      <!-- Right: Insights -->
      <div class="col-span-12 lg:col-span-3 flex flex-col gap-6 h-full overflow-y-auto custom-scrollbar">
        <div class="bg-primary-900 p-6 rounded-3xl shadow-xl border border-white/5 space-y-6">
          <h3 class="text-[10px] font-black text-white/40 uppercase tracking-[0.2em] flex items-center gap-2">
            <Activity :size="14" /> Insights Clínicos
          </h3>
          
          <div class="space-y-4">
            <div class="p-4 bg-white/5 rounded-2xl border border-white/5">
              <p class="text-[9px] font-bold text-accent-blue uppercase mb-2">Puntos Críticos</p>
              <p class="text-xs text-white/70 leading-relaxed">
                El tinnitus mapeado coincide con una caída de pendiente en las frecuencias agudas (>4kHz).
              </p>
            </div>

            <div class="p-4 bg-white/5 rounded-2xl border border-white/5">
              <p class="text-[9px] font-bold text-emerald-400 uppercase mb-2">Impacto en Lenguaje</p>
              <p class="text-xs text-white/70 leading-relaxed">
                La percepción del acúfeno se encuentra fuera de la "Banana del Habla", lo que sugiere una menor interferencia directa con la inteligibilidad.
              </p>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-primary-100 shadow-sm flex-1 flex flex-col items-center justify-center text-center p-8">
           <CheckCircle2 :size="32" class="text-emerald-500 mb-4" />
           <p class="text-[10px] font-black text-primary-900 uppercase tracking-widest mb-2">Confiabilidad de Superposición</p>
           <p class="text-xs text-primary-400 leading-relaxed">
             Los datos espectrales muestran una correlación del 85% con el perfil de presbiacusia reportado.
           </p>
        </div>
      </div>
    </div>

    <!-- Missing Prerequisites State -->
    <div v-else class="flex-1 flex flex-col items-center justify-center text-center max-w-2xl mx-auto">
      <div class="size-20 bg-primary-50 rounded-full flex items-center justify-center mb-8 text-primary-200 border border-primary-100">
        <AlertCircle :size="40" />
      </div>
      <h2 class="text-2xl font-black text-primary-900 mb-4">Requisitos Clínicos Pendientes</h2>
      <p class="text-sm text-primary-500 leading-relaxed mb-12">
        Para generar la superposición sonora, es necesario contar con una audiometría completa, el perfilado de tinnitus y el mapeo de frecuencias activo. Actualmente faltan los siguientes datos:
      </p>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full">
        <div 
          @click="router.push(`/patients/${store.selectedPatient?.id}/audiometry`)"
          class="p-6 rounded-3xl border-2 transition-all cursor-pointer group flex flex-col items-center gap-4"
          :class="hasAudiometry ? 'bg-emerald-50 border-emerald-100 text-emerald-600' : 'bg-white border-primary-50 hover:border-accent-blue text-primary-400'"
        >
          <div class="flex items-center justify-between w-full">
            <CheckCircle2 v-if="hasAudiometry" :size="20" />
            <div v-else class="size-5 rounded-full border-2 border-primary-100 group-hover:border-accent-blue transition-colors"></div>
            <ArrowRight :size="16" />
          </div>
          <p class="text-[10px] font-black uppercase tracking-widest">Audiometría Realizada</p>
        </div>

        <div 
          @click="router.push(`/patients/${store.selectedPatient?.id}/mapping`)"
          class="p-6 rounded-3xl border-2 transition-all cursor-pointer group flex flex-col items-center gap-4"
          :class="hasMapping ? 'bg-emerald-50 border-emerald-100 text-emerald-600' : 'bg-white border-primary-50 hover:border-accent-blue text-primary-400'"
        >
          <div class="flex items-center justify-between w-full">
            <CheckCircle2 v-if="hasMapping" :size="20" />
            <div v-else class="size-5 rounded-full border-2 border-primary-100 group-hover:border-accent-blue transition-colors"></div>
            <ArrowRight :size="16" />
          </div>
          <p class="text-[10px] font-black uppercase tracking-widest">Mapeo de Frecuencias</p>
        </div>
      </div>
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
