<script setup>
import { ref, computed } from 'vue'
import { RouterView, RouterLink, useRoute, useRouter } from 'vue-router'
import { 
  BarChart3, 
  Users, 
  Settings, 
  LogOut, 
  Bell, 
  Search,
  Menu,
  X,
  ChevronLeft,
  Ear,
  ClipboardList,
  Waves,
  FileText,
  User,
  Layers,
  Activity
} from 'lucide-vue-next'
import { useTinnitusStore } from './stores/tinnitusStore'

const store = useTinnitusStore()
const route = useRoute()
const router = useRouter()
const isSidebarOpen = ref(true)

// Sincronizar paciente desde la URL si es necesario
const syncPatientFromRoute = async () => {
  const patientId = route.params.id
  if (patientId && (!store.selectedPatient || store.selectedPatient.id != patientId)) {
    // Si tenemos ID en la URL pero no coincide con el store, lo cargamos
    await store.selectPatient({ id: patientId })
  }
}

// Observar cambios en el ID de la URL
import { watch, onMounted } from 'vue'
watch(() => route.params.id, syncPatientFromRoute)
onMounted(syncPatientFromRoute)

const globalNavigation = [
  { name: 'Pacientes', icon: Users, path: '/' },
  { name: 'Configuración', icon: Settings, path: '/settings' },
]

const patientNavigation = computed(() => {
  const id = store.selectedPatient?.id || route.params.id
  if (!id) return []
  return [
    { name: 'Audiometría', icon: Ear, path: `/${id}/audiometry` },
    { name: 'Perfil Tinitus', icon: ClipboardList, path: `/${id}/profiling` },
    { name: 'Mapeo Sonoro', icon: Waves, path: `/${id}/mapping` },
    { name: 'Superposición', icon: Layers, path: `/${id}/spectral` },
    { name: 'Correlación', icon: Activity, path: `/${id}/correlation` },
  ]
})

const isEcosystem = computed(() => !!store.selectedPatient || !!route.params.id)

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value
}

const exitEcosystem = () => {
  store.unselectPatient()
  router.push('/')
}
</script>

<template>
  <div class="flex h-screen bg-primary-50 font-sans">
    <!-- Sidebar -->
    <aside 
      :class="[
        'bg-primary-900 text-white transition-all duration-300 ease-in-out flex flex-col',
        isSidebarOpen ? 'w-64' : 'w-20'
      ]"
    >
      <!-- Logo Section -->
      <div class="p-6 flex items-center gap-3">
        <div class="w-8 h-8 bg-accent-red rounded-lg flex items-center justify-center shrink-0">
          <BarChart3 class="w-5 h-5 text-white" />
        </div>
        <span v-if="isSidebarOpen" class="font-bold text-xl tracking-tight truncate">TinitusAI</span>
      </div>

      <!-- Patient Context Card -->
      <div v-if="isEcosystem && isSidebarOpen && store.selectedPatient" class="px-4 py-2 mx-4 mb-4 bg-white/5 rounded-2xl border border-white/10">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-accent-blue/20 flex items-center justify-center text-accent-blue">
            <User :size="20" />
          </div>
          <div class="overflow-hidden">
            <p class="text-xs font-bold truncate">{{ store.selectedPatient.name }}</p>
            <div class="flex items-center gap-2 mt-0.5">
               <p class="text-[10px] text-primary-400">DNI: {{ store.selectedPatient.dni }}</p>
               <div v-if="store.latestProfile" class="size-1.5 bg-emerald-500 rounded-full" title="Perfil completado"></div>
               <div v-if="store.latestMapping?.id" class="size-1.5 bg-accent-blue rounded-full" title="Mapeo completado"></div>
            </div>
          </div>
        </div>
        <button 
          @click="exitEcosystem"
          class="mt-3 w-full py-2 bg-white/10 hover:bg-white/20 rounded-lg text-[10px] font-bold uppercase tracking-wider flex items-center justify-center gap-2 transition-all"
        >
          <ChevronLeft :size="12" />
          Ver otro paciente
        </button>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-4 space-y-1 mt-2 overflow-y-auto">
        <template v-if="!isEcosystem">
          <RouterLink 
            v-for="item in globalNavigation" 
            :key="item.name" 
            :to="item.path"
            :class="[
              'flex items-center gap-3 px-3 py-3 rounded-xl transition-all group',
              route.path === item.path ? 'bg-white/10 text-white' : 'text-primary-400 hover:bg-white/5 hover:text-white'
            ]"
          >
            <component :is="item.icon" class="w-5 h-5 shrink-0" />
            <span v-if="isSidebarOpen" class="font-medium text-sm">{{ item.name }}</span>
          </RouterLink>
        </template>
        
        <template v-else>
          <div class="px-3 mb-4 mt-2">
            <p v-if="isSidebarOpen" class="text-[9px] font-black uppercase text-accent-blue tracking-[0.2em] opacity-80 mb-4">Ecosistema Clínico</p>
            <div v-else class="h-px bg-white/10 w-full mb-4"></div>
            
            <RouterLink 
              v-for="item in patientNavigation" 
              :key="item.name" 
              :to="item.path"
              :class="[
                'flex items-center gap-3 px-3 py-3 rounded-xl transition-all duration-300 group mb-1',
                route.path === item.path 
                  ? 'bg-accent-blue text-white shadow-lg shadow-blue-500/20' 
                  : 'text-primary-400 hover:bg-white/5 hover:text-white'
              ]"
            >
              <component :is="item.icon" class="w-5 h-5 shrink-0" :class="route.path === item.path ? 'animate-pulse' : ''" />
              <span v-if="isSidebarOpen" class="font-bold text-xs uppercase tracking-wider">{{ item.name }}</span>
            </RouterLink>
          </div>
        </template>
      </nav>

      <!-- Footer Action -->
      <div class="p-4 border-t border-white/10">
        <button class="flex items-center gap-3 px-3 py-3 w-full rounded-xl text-primary-400 hover:bg-white/5 hover:text-white transition-all group">
          <LogOut class="w-5 h-5 shrink-0" />
          <span v-if="isSidebarOpen" class="font-medium text-sm">Cerrar Sesión</span>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Header -->
      <header class="h-20 bg-white border-b border-primary-200 flex items-center justify-between px-8 shrink-0">
        <!-- Patient Context (Top Nav) -->
        <div v-if="isEcosystem && store.selectedPatient" class="flex items-center gap-4 animate-in slide-in-from-left duration-500">
           <div class="h-10 w-px bg-primary-100 hidden md:block"></div>
           <div class="flex flex-col">
              <h2 class="text-sm font-black uppercase text-primary-900 tracking-tight leading-none">{{ store.selectedPatient.name }}</h2>
              <div class="flex items-center gap-2 mt-1">
                 <span class="text-[9px] font-bold text-primary-400 uppercase tracking-widest">DNI: {{ store.selectedPatient.dni }}</span>
                 <span class="text-[10px] text-primary-200">|</span>
                 <span class="text-[9px] font-bold text-accent-blue uppercase tracking-widest">{{ store.selectedPatient.age }} Años</span>
              </div>
           </div>
        </div>
        <div v-else-if="isEcosystem" class="flex items-center gap-4 animate-pulse">
           <div class="h-10 w-px bg-primary-100 hidden md:block"></div>
           <div class="flex flex-col gap-2">
              <div class="h-3 w-32 bg-primary-100 rounded"></div>
              <div class="h-2 w-20 bg-primary-50 rounded"></div>
           </div>
        </div>
        <span v-else></span>

        <div class="flex items-center gap-6">
          <button class="relative p-2 text-primary-500 hover:bg-primary-50 rounded-lg transition-all">
            <Bell class="w-5 h-5" />
            <span class="absolute top-2 right-2 w-2 h-2 bg-accent-red rounded-full border-2 border-white"></span>
          </button>
          
          <div class="flex items-center gap-3 pl-6 border-l border-primary-200">
            <div class="text-right">
              <p class="text-sm font-semibold text-primary-900 leading-none">{{ store.userName }}</p>
              <p class="text-xs text-primary-500 mt-1 uppercase tracking-wider font-medium">{{ store.doctor.specialty }}</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-accent-orange/10 flex items-center justify-center text-accent-orange font-bold text-sm border border-accent-orange/20">
              DH
            </div>
          </div>
        </div>
      </header>

      <!-- View Content -->
      <main class="flex-1 overflow-y-auto p-8 bg-primary-50/50">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<style lang="postcss">
@reference "../../css/app.css";

/* Global Tailwind Adjustments */
body {
    @apply antialiased;
}
</style>
