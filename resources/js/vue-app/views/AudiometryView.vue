<script setup>
import { ref, watch, computed } from 'vue'
import { useTinnitusStore } from '../stores/tinnitusStore'
import AudiogramCanvas from '../components/AudiogramCanvas.vue'
import Button from 'primevue/button'
import Select from 'primevue/select'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import ToggleSwitch from 'primevue/toggleswitch'
import { 
  Undo, 
  Trash2, 
  RotateCcw, 
  Circle, 
  X, 
  Save,
  HelpCircle,
  TrendingUp,
  Speaker
} from 'lucide-vue-next'

const store = useTinnitusStore()
const activeEar = ref('right')
const history = ref([])
const selectedHistory = ref(null)

const historyOptions = computed(() => {
  return store.patientHistory.map(h => ({
    ...h,
    label: `${h.date} - ${h.type}`
  }))
})

watch(selectedHistory, (newVal) => {
  if (newVal && newVal.data) {
    // Clone to local state for visualization
    audiometry.value = JSON.parse(JSON.stringify(newVal.data))
  }
})

const aidDialogVisible = ref(false)
const editingAid = ref(null)
const saveDialogVisible = ref(false)
const selectedType = ref('Evaluación de Seguimiento')

const audiometryTypes = ref([
  'Evaluación Inicial',
  'Evaluación de Seguimiento',
  'Control Anual',
  'Alta Intensidad / Profunda',
  'Screening / Tamizaje',
  'Otras'
])

const openEditAid = (aid) => {
  editingAid.value = { ...aid }
  aidDialogVisible.value = true
}

const createNewAid = (ear) => {
  editingAid.value = {
    id: 'ha_' + Date.now(),
    ear: ear,
    brand: '',
    model: '',
    type: 'RIC',
    technology_level: 'Premium',
    battery: 'Rechargeable',
    fitting_date: new Date().toISOString().split('T')[0],
    settings: { 
      gain_control: 80,
      channels: 16,
      programs: ['Automatic'],
      tinnitus_masker_active: false 
    },
    status: 'active'
  }
  aidDialogVisible.value = true
}

const saveAid = () => {
  const existing = store.hearingAids.find(a => a.id === editingAid.value.id)
  if (existing) {
    store.updateHearingAid(editingAid.value)
  } else {
    store.hearingAids.push({ ...editingAid.value })
  }
  aidDialogVisible.value = false
}

// Sync with store
const audiometry = ref(JSON.parse(JSON.stringify(store.audiometryData)))

watch(audiometry, (newVal) => {
  store.updateAudiometry(newVal)
}, { deep: true })

const addToHistory = () => {
  history.value.push(JSON.parse(JSON.stringify(audiometry.value)))
  if (history.value.length > 20) history.value.shift()
}

const handleUpdate = (newData) => {
  addToHistory()
  audiometry.value = newData
}

const undo = () => {
  if (history.value.length === 0) return
  audiometry.value = history.value.pop()
}

const clearEar = () => {
  addToHistory()
  audiometry.value[activeEar.value] = {}
}

const clearAll = () => {
  if (confirm('¿Eliminar todos los umbrales de ambos oídos?')) {
    addToHistory()
    audiometry.value = { right: {}, left: {} }
  }
}

const save = () => {
  saveDialogVisible.value = true
}

const confirmSave = () => {
  // En una app real, esto iría a la API. Aquí simulamos el guardado en el historial local.
  const newRecord = {
    id: 'aud_hist_' + Date.now(),
    date: new Date().toLocaleDateString('es-ES', { day: 'numeric', month: 'short', year: 'numeric' }),
    type: selectedType.value,
    data: JSON.parse(JSON.stringify(audiometry.value))
  }
  
  store.patientHistory.unshift(newRecord)
  store.updateAudiometry(audiometry.value)
  
  saveDialogVisible.value = false
  alert(`Audiometría "${selectedType.value}" guardada con éxito en el historial.`)
}
</script>

<template>
  <div class="h-[calc(100vh-160px)] min-h-[600px] overflow-hidden">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 h-full">
      <!-- Toolbar (Left) -->
      <aside class="lg:col-span-3 flex flex-col gap-3 h-full overflow-hidden">
        <!-- Patient Context (Compact) -->
        <div class="bg-primary-900 px-4 py-3 rounded-xl shadow-md shrink-0 border border-white/5">
           <div class="flex items-center justify-between gap-2">
             <div class="overflow-hidden">
               <p class="text-[8px] font-bold uppercase text-primary-400 tracking-widest leading-none mb-1">Paciente</p>
               <p class="text-xs font-bold text-white truncate">{{ store.selectedPatient?.name }}</p>
             </div>
             <TrendingUp :size="14" class="text-accent-blue" />
           </div>
        </div>

        <!-- Ear Selector (Compact) -->
        <div class="bg-white p-3 rounded-xl border border-primary-100 shadow-sm shrink-0">
          <div class="grid grid-cols-2 gap-2">
            <button 
              @click="activeEar = 'right'"
              :class="[
                'flex items-center justify-center gap-2 p-3 rounded-lg border-2 transition-all',
                activeEar === 'right' 
                  ? 'border-audio-right bg-audio-right/10 text-audio-right shadow-sm' 
                  : 'border-primary-50 bg-primary-50/30 hover:border-primary-100 opacity-60'
              ]"
            >
              <Circle :size="20" stroke-width="3" :fill="activeEar === 'right' ? 'currentColor' : 'none'" :fill-opacity="0.1" />
              <span class="text-[9px] font-bold uppercase tracking-widest">Derecho</span>
            </button>
            <button 
              @click="activeEar = 'left'"
              :class="[
                'flex items-center justify-center gap-2 p-3 rounded-lg border-2 transition-all',
                activeEar === 'left' 
                  ? 'border-audio-left bg-audio-left/10 text-audio-left shadow-sm' 
                  : 'border-primary-50 bg-primary-50/30 hover:border-primary-100 opacity-60'
              ]"
            >
              <X :size="20" stroke-width="3" />
              <span class="text-[9px] font-bold uppercase tracking-widest">Izquierdo</span>
            </button>
          </div>
        </div>

        <!-- Tools (Ultra Compact) -->
        <div class="bg-white px-2 py-1 rounded-xl border border-primary-100 shadow-sm shrink-0 flex items-center justify-around">
          <button 
            @click="undo" :disabled="history.length === 0"
            class="p-2 hover:bg-primary-50 text-primary-400 disabled:opacity-20 transition-all"
            v-tooltip.bottom="'Deshacer'"
          >
            <Undo :size="16" />
          </button>
          <div class="w-px h-4 bg-primary-100"></div>
          <button 
            @click="clearEar"
            class="p-2 hover:bg-red-50 text-primary-400 hover:text-red-500 transition-all"
            v-tooltip.bottom="'Limpiar Oído'"
          >
            <RotateCcw :size="16" />
          </button>
          <div class="w-px h-4 bg-primary-100"></div>
          <button 
            @click="clearAll"
            class="p-2 hover:bg-red-50 text-primary-400 hover:text-red-500 transition-all"
            v-tooltip.bottom="'Borrar Todo'"
          >
            <Trash2 :size="16" />
          </button>
        </div>

        <!-- Legend (Restored) -->
        <div class="bg-primary-900 border-none p-4 rounded-xl shadow-md shrink-0 border border-white/5">
          <p class="text-[8px] font-bold uppercase text-[#FDE047] mb-2 tracking-widest opacity-60">Información Clínica</p>
          <ul class="space-y-2">
            <li class="flex items-start gap-2">
              <div class="w-3 h-3 rounded-full bg-[#FDE047]/40 border border-[#FDE047] mt-0.5"></div>
              <p class="text-[10px] text-[#FDE047]/90 leading-tight">La "Banana del Habla" indica el rango donde se perciben los sonidos del lenguaje.</p>
            </li>
            <li class="flex items-start gap-2">
              <div class="w-3 h-0.5 bg-audio-right mt-1.5"></div>
              <p class="text-[10px] text-[#FDE047]/90 leading-tight">Los saltos de línea indican frecuencias no evaluadas o falta de audición.</p>
            </li>
          </ul>
        </div>

        <!-- Registro Histórico (Dropdown) -->
        <div class="bg-white p-3 rounded-xl border border-primary-100 shadow-sm shrink-0">
           <p class="text-[9px] font-bold uppercase text-primary-400 mb-2 tracking-widest">Registro Histórico</p>
           <Select 
             v-model="selectedHistory" 
             :options="historyOptions" 
             optionLabel="label" 
             placeholder="Ver evaluaciones previas" 
             class="w-full text-[10px]"
           >
              <template #option="slotProps">
                <div class="flex items-center justify-between w-full">
                  <div class="flex flex-col">
                    <span class="text-[10px] font-bold">{{ slotProps.option.date }}</span>
                    <span class="text-[8px] uppercase text-primary-400">{{ slotProps.option.type }}</span>
                  </div>
                  <TrendingUp :size="10" class="text-primary-300" />
                </div>
              </template>
           </Select>
        </div>

        <!-- Equipamiento (Audífonos) -->
        <div class="bg-white p-3 rounded-xl border border-primary-100 shadow-sm shrink-0">
          <p class="text-[9px] font-bold uppercase text-primary-400 mb-2 tracking-widest">Equipamiento</p>
          <div v-if="store.hearingAids.length > 0" class="grid grid-cols-2 gap-2">
            <div 
              v-for="aid in store.hearingAids" 
              :key="aid.id"
              @click="openEditAid(aid)"
              :class="[
                'p-2 rounded-lg border cursor-pointer transition-all hover:border-primary-300',
                aid.ear === 'right' ? 'border-audio-right/20 bg-audio-right/[0.02]' : 'border-audio-left/20 bg-audio-left/[0.02]'
              ]"
            >
              <div class="flex items-center gap-1.5 mb-1">
                <div :class="['w-1.5 h-1.5 rounded-full', aid.ear === 'right' ? 'bg-audio-right' : 'bg-audio-left']"></div>
                <span class="text-[8px] font-bold uppercase text-primary-500">{{ aid.ear === 'right' ? 'OD' : 'OI' }}</span>
              </div>
              <p class="text-[9px] font-bold text-primary-900 truncate">{{ aid.brand }}</p>
              <p class="text-[8px] text-primary-400 truncate">{{ aid.model }}</p>
            </div>
          </div>
          <div v-else class="flex flex-col gap-2">
            <button 
              @click="createNewAid('right')"
              class="w-full py-2 px-3 rounded-lg border border-dashed border-audio-right/30 text-audio-right bg-audio-right/[0.02] hover:bg-audio-right/[0.05] transition-all flex items-center justify-between group"
            >
              <span class="text-[8px] font-bold uppercase tracking-tight">Agregar Audífono OD</span>
              <Circle :size="10" stroke-width="3" />
            </button>
            <button 
              @click="createNewAid('left')"
              class="w-full py-2 px-3 rounded-lg border border-dashed border-audio-left/30 text-audio-left bg-audio-left/[0.02] hover:bg-audio-left/[0.05] transition-all flex items-center justify-between group"
            >
              <span class="text-[8px] font-bold uppercase tracking-tight">Agregar Audífono OI</span>
              <X :size="10" stroke-width="3" />
            </button>
          </div>
        </div>

        <!-- Session Actions (Icon only row) -->
        <div class="shrink-0 grid grid-cols-2 gap-2 pt-1 border-t border-primary-100">
          <Button 
            @click="save" 
            severity="danger" 
            class="bg-accent-red border-none py-3 shadow-lg shadow-red-500/10"
            v-tooltip.top="'Guardar Sesión Actual'"
          >
            <Save :size="18" />
          </Button>
          <button 
             @click="clearAll"
             class="flex items-center justify-center py-3 rounded-xl border-2 border-primary-100 text-primary-400 hover:border-primary-400 transition-all bg-white"
             v-tooltip.top="'Iniciar Nueva Audiometría'"
          >
            <RotateCcw :size="18" />
          </button>
        </div>
      </aside>

      <!-- Canvas (Right) -->
      <main class="lg:col-span-9 flex flex-col min-h-0 h-full">
        <div class="bg-white p-4 rounded-2xl border border-primary-100 shadow-lg relative overflow-hidden flex-1 flex flex-col">
          <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-audio-right via-audio-speech to-audio-left opacity-30"></div>
          
          <div class="mb-3 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
              <h2 class="text-[10px] font-bold uppercase tracking-widest text-primary-300">Audiograma Clínico</h2>
              <div v-if="selectedHistory" class="px-2 py-0.5 bg-accent-blue/10 text-accent-blue border border-accent-blue/20 rounded text-[8px] font-bold animate-pulse">
                MODO HISTORIAL ({{ selectedHistory.date }})
              </div>
            </div>
            <div class="flex items-center gap-3 text-[9px] font-bold text-primary-400">
              <span class="flex items-center gap-1.5"><Circle :size="10" class="text-audio-right" /> Derecho</span>
              <span class="flex items-center gap-1.5"><X :size="10" class="text-audio-left" /> Izquierdo</span>
            </div>
          </div>

          <div class="flex-1 min-h-0 flex items-center justify-center relative">
            <AudiogramCanvas 
              v-model="audiometry"
              :active-ear="activeEar"
              @update:model-value="handleUpdate"
            />
          </div>

          <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-3 shrink-0">
             <div class="px-3 py-2 bg-primary-50 rounded-lg border border-primary-100">
               <p class="text-[7px] uppercase font-bold text-primary-400">Sincronización</p>
               <p class="text-[9px] font-bold text-primary-800">En la nube</p>
             </div>
             <div class="px-3 py-2 bg-primary-50 rounded-lg border border-primary-100">
               <p class="text-[7px] uppercase font-bold text-primary-400">Dibujo</p>
               <p class="text-[9px] font-bold text-primary-800">Click en grilla</p>
             </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <!-- Edit Hearing Aid Dialog -->
  <Dialog v-model:visible="aidDialogVisible" modal header="Ficha Técnica de Audífono" :style="{ width: '380px' }" class="p-fluid">
    <div v-if="editingAid" class="space-y-4 pt-4 custom-scrollbar max-h-[70vh] overflow-y-auto px-1">
      <!-- Device Info -->
      <div class="grid grid-cols-2 gap-3">
        <div class="space-y-1">
          <label class="text-[9px] uppercase font-bold text-primary-400">Marca</label>
          <InputText v-model="editingAid.brand" class="text-xs p-2" />
        </div>
        <div class="space-y-1">
          <label class="text-[9px] uppercase font-bold text-primary-400">Modelo</label>
          <InputText v-model="editingAid.model" class="text-xs p-2" />
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div class="space-y-1">
          <label class="text-[9px] uppercase font-bold text-primary-400">Tipo / Formato</label>
          <InputText v-model="editingAid.type" placeholder="RIC, BTE, CIC..." class="text-xs p-2" />
        </div>
        <div class="space-y-1">
          <label class="text-[9px] uppercase font-bold text-primary-400">Nivel Tecnológico</label>
          <InputText v-model="editingAid.technology_level" placeholder="Premium, Entry..." class="text-xs p-2" />
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div class="space-y-1">
          <label class="text-[9px] uppercase font-bold text-primary-400">Batería</label>
          <InputText v-model="editingAid.battery" class="text-xs p-2" />
        </div>
        <div class="space-y-1">
          <label class="text-[9px] uppercase font-bold text-primary-400">Fecha Adaptación</label>
          <InputText v-model="editingAid.fitting_date" type="date" class="text-xs p-2" />
        </div>
      </div>

      <!-- Masker Toggle -->
      <div class="flex items-center justify-between p-3 bg-primary-50 rounded-xl border border-primary-100">
        <div class="flex items-center gap-2">
          <Speaker :size="16" class="text-accent-blue" />
          <div>
            <p class="text-[10px] font-bold text-primary-900 leading-none mb-0.5">Tinnitus Masker</p>
            <p class="text-[8px] text-primary-500 uppercase">Enmascarador Activo</p>
          </div>
        </div>
        <ToggleSwitch v-model="editingAid.settings.tinnitus_masker_active" />
      </div>

      <!-- Maintenance History (Read-only view in dialog) -->
      <div v-if="store.maintenanceHistory.length > 0" class="space-y-2">
        <p class="text-[9px] uppercase font-bold text-primary-400 tracking-widest border-b border-primary-100 pb-1">Historial de Mantenimiento</p>
        <div class="space-y-2">
          <div v-for="(item, idx) in store.maintenanceHistory" :key="idx" class="p-2 bg-primary-50/50 rounded-lg text-[9px] border border-primary-50">
            <div class="flex justify-between items-center mb-1">
              <span class="font-bold text-primary-900">{{ item.date }}</span>
              <span class="text-[8px] text-primary-400">{{ item.technician }}</span>
            </div>
            <p class="text-primary-600">{{ item.action }}</p>
          </div>
        </div>
      </div>

      <div class="pt-2 sticky bottom-0 bg-white">
        <Button @click="saveAid" label="Guardar Ficha Técnica" fluid severity="secondary" class="font-bold uppercase text-[10px] tracking-widest py-2.5 shadow-lg shadow-primary-200" />
      </div>
    </div>
  </Dialog>

  <!-- Save Audiometry Session Dialog -->
  <Dialog v-model:visible="saveDialogVisible" modal header="Guardar Sesión Clínica" :style="{ width: '350px' }" class="p-fluid">
    <div class="space-y-4 pt-4">
      <div class="space-y-2">
        <label class="text-[10px] uppercase font-bold text-primary-400 tracking-widest">Tipo de Audiometría</label>
        <Select 
          v-model="selectedType" 
          :options="audiometryTypes" 
          placeholder="Seleccionar tipo..." 
          class="w-full text-sm" 
        />
      </div>

      <div class="p-3 bg-primary-50 rounded-xl border border-primary-100 flex items-start gap-3">
        <TrendingUp :size="18" class="text-accent-blue mt-0.5" />
        <div>
          <p class="text-[10px] font-bold text-primary-900 leading-tight">Configuración del Registro</p>
          <p class="text-[8px] text-primary-500 uppercase leading-relaxed mt-1">
            Se guardarán {{ Object.keys(audiometry.right).length + Object.keys(audiometry.left).length }} umbrales para el paciente actual.
          </p>
        </div>
      </div>

      <div class="pt-2 flex gap-2">
        <Button label="Cancelar" severity="secondary" text @click="saveDialogVisible = false" class="text-[10px] uppercase font-bold tracking-widest" />
        <Button @click="confirmSave" label="Confirmar Guardado" severity="danger" class="bg-accent-red border-none font-bold uppercase text-[10px] tracking-widest py-3 shadow-lg shadow-red-500/10 flex-1" />
      </div>
    </div>
  </Dialog>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 3px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #dbdbd7;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #a1a09a;
}
</style>
