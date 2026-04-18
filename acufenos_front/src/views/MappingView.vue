<script setup>
import { ref, reactive, onUnmounted, nextTick, watch } from 'vue'
import { useTinnitusStore } from '../stores/tinnitusStore'
import { useTinnitusAudio } from '../composables/useTinnitusAudio'
import Slider from 'primevue/slider'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import { 
  Waves, 
  Volume2, 
  Settings2, 
  Activity, 
  Zap, 
  ArrowLeft,
  Ear,
  CloudLightning,
  CheckCircle2,
  Stethoscope,
  ShieldCheck,
  AlertCircle,
  Save
} from 'lucide-vue-next'

const store = useTinnitusStore()
const audio = useTinnitusAudio()

// State
const activeEar = ref('left')
const earStatus = reactive({
  left: 'symptomatic', // 'symptomatic' | 'healthy'
  right: 'symptomatic'
})

const defaultLayers = () => [
  { id: 'tono_pulsatil', name: 'Tono Pulsátil', desc: 'Sincronizado o rítmico', type: 'pulse', freq: 50, vol: 0, speed: 50, color: '#10B981' },
  { id: 'ruido_banda', name: 'Ruido de Banda', desc: 'Multiespectral continuo', type: 'noise', freq: 40, vol: 0, speed: null, color: '#3B82F6' },
  { id: 'tono_puro', name: 'Tono Puro', desc: 'Monofrecuencial estable', type: 'pure', freq: 55, vol: 0, speed: null, color: '#8B5CF6' },
  { id: 'tono_modulado', name: 'Tono Modulado', desc: 'Pulsación de frecuencia', type: 'sweep', freq: 62, vol: 0, speed: 30, color: '#F59E0B' }
]

const leftLayers = ref(store.latestMapping.left?.layers.length ? JSON.parse(JSON.stringify(store.latestMapping.left.layers)) : defaultLayers())
const rightLayers = ref(store.latestMapping.right?.layers.length ? JSON.parse(JSON.stringify(store.latestMapping.right.layers)) : defaultLayers())

// Sync ear status
if (store.latestMapping.left?.status) earStatus.left = store.latestMapping.left.status
if (store.latestMapping.right?.status) earStatus.right = store.latestMapping.right.status

// Animation Frames
const waveAnimFrames = {}

const toggleLayer = (layerId) => {
  const ear = activeEar.value
  if (earStatus[ear] === 'healthy') return

  const nodes = audio.activeNodes[ear]
  
  if (nodes[layerId]) {
    audio.stopLayer(ear, layerId)
  } else {
    const layers = ear === 'left' ? leftLayers.value : rightLayers.value
    const layer = layers.find(l => l.id === layerId)
    audio.startLayer(ear, layer)
    nextTick(() => {
      startWaveAnim(ear, layerId)
    })
  }
}

const getLayer = (ear, id) => {
  const list = ear === 'left' ? leftLayers.value : rightLayers.value
  return list.find(l => l.id === id)
}

const updateFreq = (id, val) => {
  audio.updateFreq(activeEar.value, id, val)
}

const updateVol = (id, val) => {
  audio.updateVol(activeEar.value, id, val)
}

const updateSpeed = (id, val) => {
  audio.updateSpeed(activeEar.value, id, val)
}

const fmtFreq = (f) => {
  const hz = audio.freqFromSlider(f)
  return hz >= 1000 ? (hz / 1000).toFixed(1) + ' kHz' : hz + ' Hz'
}

const fmtSpeed = (v) => {
  return audio.speedFromSlider(v) + ' Hz'
}

const handleVolSlideEnd = (layer) => {
  if (!audio.activeNodes[activeEar.value][layer.id] && layer.vol > 0) {
    toggleLayer(layer.id)
  }
}

const setEarHealthy = (ear) => {
  earStatus[ear] = 'healthy'
  // Stop all sounds for this ear
  const layers = ear === 'left' ? leftLayers.value : rightLayers.value
  layers.forEach(l => {
    audio.stopLayer(ear, l.id)
    l.vol = 0 // Reset visual volume
  })
}

const setEarSymptomatic = (ear) => {
  earStatus[ear] = 'symptomatic'
}

// Visualizer Logic
const startWaveAnim = (ear, layerId) => {
  const canvasId = `wave-${ear}-${layerId}`
  const canvas = document.getElementById(canvasId)
  if (!canvas) return

  const ctx2d = canvas.getContext('2d')
  const layer = getLayer(ear, layerId)
  
  const draw = () => {
    if (!audio.activeNodes[ear][layerId]) return
    
    const W = canvas.width = canvas.offsetWidth
    const H = canvas.height = canvas.offsetHeight
    const freq = audio.freqFromSlider(layer.freq)
    const vol = layer.vol / 100
    const spd = layer.speed !== null ? audio.speedFromSlider(layer.speed) : 1
    const color = layer.color
    
    ctx2d.clearRect(0, 0, W, H)
    
    const time = performance.now() / 1000
    const amp = (H / 2 - 4) * vol
    
    ctx2d.strokeStyle = color
    ctx2d.lineWidth = 3
    ctx2d.lineJoin = 'round'
    ctx2d.beginPath()
    
    if (layer.type === 'pure' || layer.type === 'pulse' || layer.type === 'sweep') {
      const cycles = 2 + (freq / 1000)
      const pulse = layer.type === 'pulse' ? Math.pow(Math.sin(time * spd * Math.PI), 2) : 1
      
      for (let x = 0; x <= W; x++) {
        const nx = x / W
        const angle = nx * cycles * Math.PI * 2 - time * 10
        const y = H / 2 + Math.sin(angle) * amp * (layer.type === 'pulse' ? (0.2 + pulse * 0.8) : 1)
        x === 0 ? ctx2d.moveTo(x, y) : ctx2d.lineTo(x, y)
      }
    } else {
      for (let x = 0; x <= W; x++) {
        const n = (Math.random() - 0.5) * 2
        const y = H / 2 + n * amp
        x === 0 ? ctx2d.moveTo(x, y) : ctx2d.lineTo(x, y)
      }
    }
    
    ctx2d.stroke()
    waveAnimFrames[canvasId] = requestAnimationFrame(draw)
  }
  
  draw()
}

onUnmounted(() => {
  ['left', 'right'].forEach(ear => {
    ['tono_pulsatil', 'ruido_banda', 'tono_puro', 'tono_modulado'].forEach(id => {
      audio.stopLayer(ear, id)
    })
  })
  Object.values(waveAnimFrames).forEach(id => cancelAnimationFrame(id))
})

const showSaveDialog = ref(false)

const saveMapping = () => {
  showSaveDialog.value = true
}

const confirmSave = () => {
  // Persistence to Store
  store.latestMapping = {
    left: { status: earStatus.left, layers: [...leftLayers.value] },
    right: { status: earStatus.right, layers: [...rightLayers.value] }
  }

  store.patientHistory.unshift({
    id: 'map_' + Date.now(),
    date: new Date().toLocaleDateString('es-ES', { day: 'numeric', month: 'short', year: 'numeric' }),
    type: 'Mapeo Tinnitus',
    data: JSON.parse(JSON.stringify(store.latestMapping))
  })

  showSaveDialog.value = false
  alert('Configuración de Mapeo guardada correctamente en el Gemelo Digital.')
}

// Ensure audio context starts on first interaction
const ensureAudioCtx = () => {
  audio.initContext()
}
</script>

<template>
  <div class="h-[calc(100vh-160px)] min-h-[600px] flex flex-col gap-6 overflow-hidden" @mousedown="ensureAudioCtx">
    <!-- Header & Clinical Console -->
    <div class="flex items-center justify-between shrink-0 px-2 py-4 bg-primary-900 rounded-3xl shadow-xl border border-white/5">
      <div class="flex items-center gap-6">
        <!-- Title & Icon -->
        <div class="flex items-center gap-3 pl-4">
          <div class="size-10 bg-accent-blue/10 rounded-xl flex items-center justify-center text-accent-blue border border-accent-blue/20">
            <Waves :size="20" />
          </div>
          <div class="hidden xl:block">
            <h1 class="text-xs font-black text-white uppercase tracking-widest">Mapeador</h1>
            <p class="text-[9px] text-white/40 uppercase font-bold">Consola Clínica</p>
          </div>
        </div>

        <!-- Integrated Ear Selector -->
        <div class="flex p-1 bg-black/40 rounded-2xl border border-white/5 gap-1">
          <button 
            @click="activeEar = 'left'"
            :class="[
              'px-6 py-2.5 text-[9px] font-black uppercase tracking-widest rounded-xl transition-all border-2 relative', 
              activeEar === 'left' ? 'text-white border-white/20' : 'bg-transparent border-transparent text-white/30 hover:text-white/60'
            ]"
            :style="activeEar === 'left' ? { backgroundColor: '#3B82F6' } : {}"
          >
            Izquierdo
            <div v-if="earStatus.left === 'healthy'" class="absolute -top-1 -right-1 size-3.5 bg-emerald-500 rounded-full border-2 border-primary-900 flex items-center justify-center">
              <CheckCircle2 :size="8" class="text-white" />
            </div>
          </button>
          <button 
            @click="activeEar = 'right'"
            :class="[
              'px-6 py-2.5 text-[9px] font-black uppercase tracking-widest rounded-xl transition-all border-2 relative', 
              activeEar === 'right' ? 'text-white border-white/20' : 'bg-transparent border-transparent text-white/30 hover:text-white/60'
            ]"
            :style="activeEar === 'right' ? { backgroundColor: '#EF4444' } : {}"
          >
            Derecho
            <div v-if="earStatus.right === 'healthy'" class="absolute -top-1 -right-1 size-3.5 bg-emerald-500 rounded-full border-2 border-primary-900 flex items-center justify-center">
              <CheckCircle2 :size="8" class="text-white" />
            </div>
          </button>
        </div>

        <!-- Healthy / Symptomatic Toggle (Integrated) -->
        <button 
          @click="earStatus[activeEar] === 'healthy' ? setEarSymptomatic(activeEar) : setEarHealthy(activeEar)"
          class="px-5 py-2.5 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all border flex items-center gap-2"
          :class="earStatus[activeEar] === 'healthy' 
            ? 'bg-emerald-500 border-emerald-500 text-white shadow-lg' 
            : 'bg-white/5 border-white/10 text-white/60 hover:text-white hover:bg-white/10'"
        >
          <ShieldCheck v-if="earStatus[activeEar] === 'healthy'" :size="14" />
          <Activity v-else :size="14" />
          {{ earStatus[activeEar] === 'healthy' ? 'Oído Sano' : 'Marcar Oído Sano' }}
        </button>
      </div>
      
      <!-- Action Diskette -->
      <div class="pr-4">
        <Button 
          @click="saveMapping" 
          v-tooltip.bottom="'Guardar en Gemelo Digital'"
          class="p-button p-component bg-emerald-500 border-none py-3 px-5 shadow-lg shadow-emerald-500/10 text-white rounded-xl transition-all duration-300 flex items-center gap-2 hover:bg-emerald-600" 
        >
          <Save :size="18" />
          <span class="text-[10px] font-black uppercase tracking-widest hidden md:block">Guardar Mapeo</span>
        </Button>
      </div>
    </div>

    <!-- Main Mapper Panel -->
    <div class="flex-1 flex flex-col gap-6 min-h-0">
      <!-- Synthesizer Grid or Healthy State View -->
      <div class="flex-1 min-h-0 overflow-y-auto pr-2 custom-scrollbar">
        <!-- Case A: Symptomatic (Synthesizer) -->
        <div 
          v-if="earStatus[activeEar] === 'symptomatic'"
          class="grid grid-cols-1 md:grid-cols-2 gap-4"
        >
          <div 
            v-for="layer in (activeEar === 'left' ? leftLayers : rightLayers)" 
            :key="activeEar + '-' + layer.id"
            class="bg-white p-6 rounded-3xl border transition-all duration-300 flex flex-col gap-6"
            :class="audio.activeNodes[activeEar][layer.id] ? 'border-primary-900 shadow-xl' : 'border-primary-100 shadow-sm'"
          >
            <!-- Layer Header & Visualizer -->
            <div class="flex flex-col gap-4">
              <div 
                @click="toggleLayer(layer.id)"
                class="flex items-center gap-4 cursor-pointer group"
              >
                <div 
                  class="size-12 rounded-2xl flex items-center justify-center transition-all duration-500"
                  :style="{ backgroundColor: audio.activeNodes[activeEar][layer.id] ? layer.color : layer.color + '10', color: audio.activeNodes[activeEar][layer.id] ? 'white' : layer.color }"
                >
                  <Zap v-if="layer.type === 'pulse'" :size="20" />
                  <Settings2 v-else-if="layer.type === 'noise'" :size="20" />
                  <Activity v-else-if="layer.type === 'pure'" :size="20" />
                  <Waves v-else :size="20" />
                </div>
                <div class="flex-1 min-w-0">
                  <h3 class="text-sm font-black text-primary-900 uppercase tracking-widest truncate">{{ layer.name }}</h3>
                  <p class="text-[10px] text-primary-400 uppercase tracking-tighter">{{ layer.desc }}</p>
                </div>
                <div 
                  class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase transition-all duration-500"
                  :class="audio.activeNodes[activeEar][layer.id] ? 'bg-green-500 text-white' : 'bg-primary-50 text-primary-300'"
                >
                  {{ audio.activeNodes[activeEar][layer.id] ? 'Activo' : 'OFF' }}
                </div>
              </div>

              <!-- Kinetic Wave Visualizer -->
              <div 
                class="h-16 w-full rounded-2xl overflow-hidden relative transition-all duration-500"
                :class="audio.activeNodes[activeEar][layer.id] ? 'bg-primary-950 shadow-inner' : 'bg-primary-50/50'"
              >
                <canvas :id="`wave-${activeEar}-${layer.id}`" class="w-full h-full opacity-80"></canvas>
                <div v-if="!audio.activeNodes[activeEar][layer.id]" class="absolute inset-0 flex items-center justify-center">
                  <div class="w-1/2 h-[1px] bg-primary-200"></div>
                </div>
              </div>
            </div>

            <!-- Controls Section -->
            <div class="grid grid-cols-1 gap-6 px-1">
              <div class="space-y-3">
                <div class="flex justify-between items-end">
                  <label class="text-[10px] font-black text-primary-500 uppercase tracking-widest">Frecuencia</label>
                  <span class="text-xs font-black text-primary-900">{{ fmtFreq(layer.freq) }}</span>
                </div>
                <Slider 
                  v-model="layer.freq" 
                  :min="0" :max="100" 
                  class="custom-slider" 
                  @change="updateFreq(layer.id, $event)" 
                />
              </div>

              <div class="grid grid-cols-2 gap-6">
                <div class="space-y-3">
                  <div class="flex justify-between items-end">
                    <label class="text-[10px] font-black text-primary-500 uppercase tracking-widest">Intensidad</label>
                    <span class="text-xs font-black text-primary-900">{{ layer.vol }}%</span>
                  </div>
                  <Slider 
                    v-model="layer.vol" 
                    :min="0" :max="100" 
                    class="custom-slider-vol" 
                    @change="updateVol(layer.id, $event)" 
                    @slideend="handleVolSlideEnd(layer)"
                  />
                </div>

                <div v-if="layer.speed !== null" class="space-y-3">
                  <div class="flex justify-between items-end">
                    <label class="text-[10px] font-black text-primary-500 uppercase tracking-widest">Velocidad</label>
                    <span class="text-xs font-black text-primary-900">{{ fmtSpeed(layer.speed) }}</span>
                  </div>
                  <Slider 
                    v-model="layer.speed" 
                    :min="0" :max="100" 
                    class="custom-slider-speed" 
                    @change="updateSpeed(layer.id, $event)" 
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Case B: Healthy (Message) -->
        <div 
          v-else
          class="h-full flex flex-col items-center justify-center text-center p-12 bg-white rounded-3xl border border-emerald-100"
        >
          <div class="size-20 bg-emerald-50 rounded-full flex items-center justify-center mb-6 text-emerald-500 border border-emerald-200 animate-bounce">
            <ShieldCheck :size="40" />
          </div>
          <h2 class="text-2xl font-black text-primary-900 mb-2">Oído Clínicamente Sano</h2>
          <p class="text-sm text-primary-500 max-w-sm">
            Este oído ha sido marcado como libre de síntomas de acúfeno. No se aplicará síntesis sonora en este canal.
          </p>
          <div class="mt-8 flex gap-2">
            <div class="px-4 py-1.5 bg-emerald-100 text-emerald-700 rounded-full text-[10px] font-black uppercase tracking-widest">Sin Percepción</div>
            <div class="px-4 py-1.5 bg-emerald-100 text-emerald-700 rounded-full text-[10px] font-black uppercase tracking-widest">Canal Protegido</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Save Confirmation Modal -->
  <Dialog v-model:visible="showSaveDialog" modal header="Confirmar Guardado" :style="{ width: '450px' }">
    <div class="space-y-6 pt-4">
      <div class="flex items-center gap-4 p-4 bg-primary-50 rounded-2xl border border-primary-100">
        <div class="size-12 bg-emerald-500 rounded-xl flex items-center justify-center text-white shadow-lg shadow-emerald-500/20">
          <CheckCircle2 :size="24" />
        </div>
        <div>
          <p class="text-sm font-black text-primary-900 uppercase">Resumen del Mapeo</p>
          <p class="text-[10px] text-primary-500 uppercase">Se guardarán los siguientes estados clínicos</p>
        </div>
      </div>

      <div class="space-y-3">
        <!-- Izquierdo Summary -->
        <div class="flex items-center justify-between p-3 rounded-xl border" :class="earStatus.left === 'healthy' ? 'bg-emerald-50/50 border-emerald-100' : 'bg-white border-primary-100'">
          <div class="flex items-center gap-3">
            <div class="size-2 rounded-full" :style="{ backgroundColor: '#3B82F6' }"></div>
            <span class="text-xs font-bold text-primary-900">Oído Izquierdo</span>
          </div>
          <span class="text-[10px] font-black uppercase tracking-widest" :class="earStatus.left === 'healthy' ? 'text-emerald-600' : 'text-primary-400'">
            {{ earStatus.left === 'healthy' ? 'Sano' : 'Mapeado' }}
          </span>
        </div>

        <!-- Derecho Summary -->
        <div class="flex items-center justify-between p-3 rounded-xl border" :class="earStatus.right === 'healthy' ? 'bg-emerald-50/50 border-emerald-100' : 'bg-white border-primary-100'">
          <div class="flex items-center gap-3">
            <div class="size-2 rounded-full" :style="{ backgroundColor: '#EF4444' }"></div>
            <span class="text-xs font-bold text-primary-900">Oído Derecho</span>
          </div>
          <span class="text-[10px] font-black uppercase tracking-widest" :class="earStatus.right === 'healthy' ? 'text-emerald-600' : 'text-primary-400'">
            {{ earStatus.right === 'healthy' ? 'Sano' : 'Mapeado' }}
          </span>
        </div>
      </div>

      <p class="text-xs text-primary-500 text-center leading-relaxed">
        ¿Deseas finalizar la sesión y guardar estos parámetros en el Gemelo Digital del paciente?
      </p>

      <div class="flex gap-3 pt-2">
        <Button label="Revisar" severity="secondary" text @click="showSaveDialog = false" class="text-xs uppercase font-bold flex-1" />
        <Button 
          @click="confirmSave" 
          label="Guardar y Finalizar" 
          severity="success" 
          class="bg-emerald-500 border-none text-xs uppercase font-bold flex-1 py-4 shadow-xl shadow-emerald-500/20" 
        />
      </div>
    </div>
  </Dialog>
</template>

<style>
.custom-slider .p-slider-handle, .custom-slider-vol .p-slider-handle, .custom-slider-speed .p-slider-handle {
  @apply border-none size-3 shadow-lg !important;
}

.custom-slider .p-slider-handle { background-color: #3b82f6 !important; }
.custom-slider .p-slider-range { background-color: rgba(59, 130, 246, 0.2) !important; }

.custom-slider-vol .p-slider-handle { background-color: #10B981 !important; }
.custom-slider-vol .p-slider-range { background-color: rgba(16, 185, 129, 0.2) !important; }

.custom-slider-speed .p-slider-handle { background-color: #F59E0B !important; }
.custom-slider-speed .p-slider-range { background-color: rgba(245, 158, 11, 0.2) !important; }

.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(0,0,0,0.1);
  border-radius: 10px;
}
</style>
