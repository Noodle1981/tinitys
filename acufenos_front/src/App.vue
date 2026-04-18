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

const globalNavigation = [
  { name: 'Dashboard', icon: BarChart3, path: '/' },
  { name: 'Pacientes', icon: Users, path: '/patients' },
  { name: 'Configuración', icon: Settings, path: '/settings' },
]

const patientNavigation = computed(() => {
  if (!store.selectedPatient) return []
  const id = store.selectedPatient.id
  return [
    { name: 'Audiometría', icon: Ear, path: `/patients/${id}/audiometry` },
    { name: 'Perfil Tinitus', icon: ClipboardList, path: `/patients/${id}/profiling` },
    { name: 'Mapeo Sonoro', icon: Waves, path: `/patients/${id}/mapping` },
    { name: 'Superposición', icon: Layers, path: `/patients/${id}/spectral` },
    { name: 'Correlación', icon: Activity, path: `/patients/${id}/correlation` },
  ]
})

const isEcosystem = computed(() => !!store.selectedPatient)

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value
}

const exitEcosystem = () => {
  store.unselectPatient()
  router.push('/patients')
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
      <div v-if="isEcosystem && isSidebarOpen" class="px-4 py-2 mx-4 mb-4 bg-white/5 rounded-2xl border border-white/10">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-accent-blue/20 flex items-center justify-center text-accent-blue">
            <User :size="20" />
          </div>
          <div class="overflow-hidden">
            <p class="text-xs font-bold truncate">{{ store.selectedPatient.name }}</p>
            <p class="text-[10px] text-primary-400">DNI: {{ store.selectedPatient.dni }}</p>
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
          <p v-if="isSidebarOpen" class="px-3 mb-2 text-[10px] font-bold uppercase text-primary-500 tracking-widest">Ecosistema Clínico</p>
          <RouterLink 
            v-for="item in patientNavigation" 
            :key="item.name" 
            :to="item.path"
            :class="[
              'flex items-center gap-3 px-3 py-3 rounded-xl transition-all group',
              route.path === item.path ? 'bg-accent-red text-white' : 'text-primary-400 hover:bg-white/5 hover:text-white'
            ]"
          >
            <component :is="item.icon" class="w-5 h-5 shrink-0" />
            <span v-if="isSidebarOpen" class="font-medium text-sm">{{ item.name }}</span>
          </RouterLink>
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
        <div class="flex items-center gap-4">
          <button @click="toggleSidebar" class="p-2 hover:bg-primary-50 rounded-lg text-primary-500">
            <Menu v-if="!isSidebarOpen" class="w-5 h-5" />
            <X v-else class="w-5 h-5" />
          </button>
          <div class="relative hidden md:block">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-primary-400" />
            <input 
              type="text" 
              placeholder="Buscar..." 
              class="pl-10 pr-4 py-2 bg-primary-50 border border-primary-200 rounded-xl w-64 focus:outline-none focus:ring-2 focus:ring-accent-red/20 focus:border-accent-red transition-all"
            />
          </div>
        </div>

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

<style>
/* Global Tailwind Adjustments */
body {
    @apply antialiased;
}
</style>
