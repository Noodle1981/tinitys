<script setup>
import { ref, watch, onMounted } from 'vue'
import Tabs from 'primevue/tabs'
import TabList from 'primevue/tablist'
import Tab from 'primevue/tab'
import TabPanels from 'primevue/tabpanels'
import TabPanel from 'primevue/tabpanel'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import DatePicker from 'primevue/datepicker'
import Select from 'primevue/select'
import Checkbox from 'primevue/checkbox'
import Textarea from 'primevue/textarea'
import Button from 'primevue/button'
import { useTinnitusStore } from '../stores/tinnitusStore'
import { 
  User, 
  Briefcase, 
  Heart, 
  Waves, 
  ClipboardList, 
  Save, 
  X 
} from 'lucide-vue-next'

const props = defineProps({
  patient: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'saved'])
const store = useTinnitusStore()
const loading = ref(false)

const localPatient = ref({
  name: '',
  dni: '',
  birth_date: null,
  gender: '',
  civil_status: '',
  educational_level: '',
  children_count: 0,
  children_ages: '',
  province: '',
  city: '',
  address: '',
  postal_code: '',
  country: 'Argentina',
  occupation: '',
  exposure: {},
  habits: {},
  clinical_history: {},
  tinnitus_profile: {}
})

// Inicializar datos locales al abrir
watch(() => props.patient, (newVal) => {
  if (newVal) {
    localPatient.value = { 
      ...newVal,
      exposure: newVal.exposure || {},
      habits: newVal.habits || {},
      clinical_history: newVal.clinical_history || {},
      tinnitus_profile: newVal.tinnitus_profile || {}
    }
  }
}, { immediate: true })

// Lógica reactiva para campos dependientes
watch(() => localPatient.value.exposure.protection_frequency, (newVal) => {
  if (newVal === 'Nunca') {
    localPatient.value.exposure.protection_type = 'ninguno'
  }
})

// Lógica de exclusión mutua para "Ninguna actividad"
watch(() => localPatient.value.habits.risk_none, (newVal) => {
  if (newVal) {
    activityOptions.forEach(opt => {
      if (opt.key !== 'risk_none') {
        localPatient.value.habits[opt.key] = false
      }
    })
  }
})

// Si se marca cualquier otra actividad, desmarcar "Ninguna"
const otherRisks = [
  'risk_discotecas', 'risk_musica_fuerte', 'risk_caza', 
  'risk_tiro', 'risk_militar', 'risk_motociclismo', 
  'risk_automovilismo', 'risk_submarinismo'
]

otherRisks.forEach(key => {
  watch(() => localPatient.value.habits[key], (newVal) => {
    if (newVal) {
      localPatient.value.habits.risk_none = false
    }
  })
})

const saveFicha = async () => {
  loading.value = true
  try {
    if (localPatient.value.id) {
      await store.updatePatient(localPatient.value)
    } else {
      await store.addPatient(localPatient.value)
    }
    emit('saved')
    emit('close')
  } catch (error) {
    console.error('Error al guardar ficha:', error)
  } finally {
    loading.value = false
  }
}

const genderOptions = [
  { label: 'Masculino', value: 'M' },
  { label: 'Femenino', value: 'F' },
  { label: 'Otro', value: 'O' }
]

const civilStatusOptions = [
  { label: 'Soltero/a', value: 'soltero' },
  { label: 'Casado/a', value: 'casado' },
  { label: 'Divorciado/a', value: 'divorciado' },
  { label: 'Viudo/a', value: 'viudo' }
]

const activityOptions = [
  { label: 'Discotecas', key: 'risk_discotecas' },
  { label: 'Música Fuerte', key: 'risk_musica_fuerte' },
  { label: 'Caza', key: 'risk_caza' },
  { label: 'Tiro', key: 'risk_tiro' },
  { label: 'Militar', key: 'risk_militar' },
  { label: 'Motociclismo', key: 'risk_motociclismo' },
  { label: 'Automovilismo', key: 'risk_automovilismo' },
  { label: 'Submarinismo', key: 'risk_submarinismo' },
  { label: 'Ninguna actividad', key: 'risk_none' }
]

const clinicalGroups = {
  family: [
    { label: 'Padre', key: 'fam_father' },
    { label: 'Madre', key: 'fam_mother' },
    { label: 'Abuelos', key: 'fam_grandparents' },
    { label: 'Tíos', key: 'fam_uncles' },
    { label: 'Hermanos', key: 'fam_siblings' }
  ],
  otologic: [
    { label: 'Vértigo', key: 'has_vertigo' },
    { label: 'Otalgia', key: 'has_otalgia' },
    { label: 'Otorrea', key: 'has_otorrhea' },
    { label: 'Otitis Serosa', key: 'has_serous_otitis' },
    { label: 'Enf. Meniere', key: 'has_meniere' }
  ],
  infectious: [
    { label: 'Traumas Craneales', key: 'has_trauma' },
    { label: 'Meningitis', key: 'has_meningitis' },
    { label: 'Parálisis Facial', key: 'has_facial_paralysis' },
    { label: 'Herpes Zoster', key: 'has_herpes_zoster' },
    { label: 'Parotiditis', key: 'has_mumps' },
    { label: 'Rubeola', key: 'has_rubella' },
    { label: 'Sarampión', key: 'has_measles' },
    { label: 'Fiebre Tifoidea', key: 'has_typhoid' },
    { label: 'Tifus Exantem.', key: 'has_typhus' }
  ],
  ototoxic: [
    { label: 'Estreptomicina', key: 'use_streptomycin' },
    { label: 'Gentamicina', key: 'use_gentamicin' },
    { label: 'Salicilatos', key: 'uses_salicylates' },
    { label: 'Quininas', key: 'uses_quinine' },
    { label: 'Kanamicina', key: 'use_kanamycin' },
    { label: 'Tobramicina', key: 'use_tobramycin' },
    { label: 'Furosemida', key: 'use_furosemide' },
    { label: 'Ac. Etacrínico', key: 'use_ethacrynic_acid' },
    { label: 'Vancomicina', key: 'use_vancomycin' }
  ],
  comorbidities: [
    { label: 'Paludismo', key: 'has_malaria' },
    { label: 'Reumatismo', key: 'has_rheumatism' },
    { label: 'Tuberculosis', key: 'has_tuberculosis' },
    { label: 'Cefaleas', key: 'has_headaches' },
    { label: 'Insuf. Cardíaca', key: 'has_heart_failure' },
    { label: 'Hipertensión', key: 'has_hypertension' }
  ]
}
</script>

<template>
  <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-primary-100 max-w-5xl w-full mx-auto">
    <!-- Header -->
    <div class="px-8 py-6 bg-primary-900 flex justify-between items-center text-white">
      <div class="flex items-center gap-4">
        <div class="size-12 bg-accent-blue/20 rounded-2xl flex items-center justify-center text-accent-blue border border-accent-blue/30">
          <User :size="24" />
        </div>
        <div>
          <h2 class="text-xl font-black uppercase tracking-widest">Ficha Técnica Clínica</h2>
          <p class="text-xs text-white/50 font-bold uppercase">{{ localPatient.name || 'Nuevo Paciente' }} | {{ localPatient.dni }}</p>
        </div>
      </div>
      <button @click="$emit('close')" class="p-2 hover:bg-white/10 rounded-full transition-colors">
        <X :size="24" />
      </button>
    </div>

    <!-- Tabs Content -->
    <div class="p-0">
      <Tabs value="0">
        <TabList class="bg-primary-50/50 border-b border-primary-100 flex p-2 gap-2">
          <Tab value="0" class="flex-1">
            <div class="flex items-center justify-center gap-2 py-2">
              <User :size="16" />
              <span class="text-[10px] font-black uppercase tracking-tight">1. Identificación</span>
            </div>
          </Tab>
          <Tab value="1" class="flex-1">
            <div class="flex items-center justify-center gap-2 py-2">
              <Briefcase :size="16" />
              <span class="text-[10px] font-black uppercase tracking-tight">2. Laboral</span>
            </div>
          </Tab>
          <Tab value="2" class="flex-1">
            <div class="flex items-center justify-center gap-2 py-2">
              <Heart :size="16" />
              <span class="text-[10px] font-black uppercase tracking-tight">3. Ocio/Hábitos</span>
            </div>
          </Tab>
          <Tab value="3" class="flex-1">
            <div class="flex items-center justify-center gap-2 py-2">
              <Waves :size="16" />
              <span class="text-[10px] font-black uppercase tracking-tight">4. Tinnitus</span>
            </div>
          </Tab>
          <Tab value="4" class="flex-1">
            <div class="flex items-center justify-center gap-2 py-2">
              <ClipboardList :size="16" />
              <span class="text-[10px] font-black uppercase tracking-tight">5. Clínica</span>
            </div>
          </Tab>
        </TabList>

        <TabPanels class="p-8 min-h-[500px] max-h-[70vh] overflow-y-auto custom-scrollbar">
          <!-- Pestaña 1: Socio-demográfico -->
          <TabPanel value="0">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div class="space-y-4">
                <div class="flex flex-col gap-2">
                  <label class="text-[10px] font-black uppercase text-primary-400">Nombre Completo</label>
                  <InputText v-model="localPatient.name" class="w-full text-sm rounded-xl border-primary-100" />
                </div>
                <div class="flex flex-col gap-2">
                  <label class="text-[10px] font-black uppercase text-primary-400">DNI / ID</label>
                  <InputText v-model="localPatient.dni" class="w-full text-sm rounded-xl border-primary-100" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                  <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-black uppercase text-primary-400">Fecha Nacimiento</label>
                    <DatePicker v-model="localPatient.birth_date" class="w-full text-sm " />
                  </div>
                  <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-black uppercase text-primary-400">Sexo</label>
                    <Select v-model="localPatient.gender" :options="genderOptions" optionLabel="label" optionValue="value" class="w-full text-sm rounded-xl border-primary-100" />
                  </div>
                </div>
              </div>

              <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                  <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-black uppercase text-primary-400">Estado Civil</label>
                    <Select v-model="localPatient.civil_status" :options="civilStatusOptions" optionLabel="label" optionValue="value" class="w-full text-sm rounded-xl border-primary-100" />
                  </div>
                  <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-black uppercase text-primary-400">Nivel Educativo</label>
                    <Select v-model="localPatient.educational_level" :options="store.options.educationalLevels" optionLabel="label" optionValue="value" placeholder="Seleccionar..." class="w-full text-sm rounded-xl border-primary-100" />
                  </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                  <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-black uppercase text-primary-400">N° Hijos</label>
                    <InputNumber v-model="localPatient.children_count" class="w-full text-sm" />
                  </div>
                  <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-black uppercase text-primary-400">Provincia / Zona</label>
                    <InputText v-model="localPatient.province" class="w-full text-sm rounded-xl border-primary-100" />
                  </div>
                  <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-black uppercase text-primary-400">País</label>
                    <InputText v-model="localPatient.country" class="w-full text-sm rounded-xl border-primary-100" />
                  </div>
                </div>
                <div class="flex flex-col gap-2">
                  <label class="text-[10px] font-black uppercase text-primary-400">Domicilio</label>
                  <InputText v-model="localPatient.address" class="w-full text-sm rounded-xl border-primary-100" />
                </div>
              </div>
            </div>
          </TabPanel>

          <!-- Pestaña 2: Laboral -->
          <TabPanel value="1">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div class="space-y-4">
                <div class="flex flex-col gap-2">
                  <label class="text-[10px] font-black uppercase text-primary-400">Sector de la Empresa</label>
                  <Select v-model="localPatient.exposure.sector" :options="store.options.workSectors" optionLabel="label" optionValue="value" placeholder="Seleccionar Sector..." class="w-full text-sm rounded-xl border-primary-100" />
                </div>
                <div class="flex flex-col gap-2">
                  <label class="text-[10px] font-black uppercase text-primary-400">Puesto de Trabajo</label>
                  <Select v-model="localPatient.occupation" :options="store.options.jobPositions" optionLabel="label" optionValue="value" placeholder="Seleccionar Puesto..." class="w-full text-sm rounded-xl border-primary-100" />
                </div>
                <div class="flex flex-col gap-2">
                  <label class="text-[10px] font-black uppercase text-primary-400">Perfil del Ruido (Cualitativo)</label>
                  <Select v-model="localPatient.exposure.noise_profile" :options="store.options.noiseProfiles" optionLabel="label" optionValue="value" placeholder="Seleccionar Perfil..." class="w-full text-sm rounded-xl border-primary-100" />
                </div>
              </div>
              <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                  <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-black uppercase text-primary-400">Exposición diaria (Hrs)</label>
                    <InputNumber v-model="localPatient.work_hours" class="w-full text-sm" />
                  </div>
                  <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-black uppercase text-primary-400">Protección</label>
                    <Select v-model="localPatient.exposure.protection_frequency" :options="['Siempre', 'A veces', 'Nunca']" class="w-full text-sm" />
                  </div>
                </div>
                <div class="flex flex-col gap-2">
                  <label class="text-[10px] font-black uppercase text-primary-400">Tipo de Protección</label>
                  <Select v-model="localPatient.exposure.protection_type" :options="store.options.protectionTypes" optionLabel="label" optionValue="value" placeholder="Tipo de Protector..." class="w-full text-sm rounded-xl border-primary-100" />
                </div>
                <div class="flex flex-col gap-2">
                  <label class="text-[10px] font-black uppercase text-primary-400">Horas de descanso auditivo</label>
                  <Select v-model="localPatient.exposure.recovery_hours_daily" :options="store.options.recoveryHoursOptions" optionLabel="label" optionValue="value" placeholder="Nivel de Descanso..." class="w-full text-sm rounded-xl border-primary-100" />
                </div>
              </div>
            </div>
          </TabPanel>

          <!-- Pestaña 3: Ocio y Hábitos -->
          <TabPanel value="2">
            <div class="space-y-8">
              <div class="bg-primary-50 p-6 rounded-2xl border border-primary-100">
                <h4 class="text-xs font-black uppercase text-primary-900 mb-4">Actividades de Riesgo Acústico</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                  <div v-for="act in activityOptions" :key="act.key" class="flex items-center gap-2">
                    <Checkbox :inputId="act.key" v-model="localPatient.habits[act.key]" binary />
                    <label :for="act.key" class="text-xs text-primary-600 font-bold">{{ act.label }}</label>
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-8">
                <div class="bg-primary-50 p-6 rounded-2xl border border-primary-100">
                  <h4 class="text-xs font-black uppercase text-primary-900 mb-4">Exposición Reciente (24h)</h4>
                  <div class="flex items-center gap-3">
                    <Checkbox v-model="localPatient.habits.last_24h_exposure" binary />
                    <label class="text-xs text-primary-600 font-bold italic">¿Ha estado expuesto a ruido elevado en las últimas 24h?</label>
                  </div>
                </div>
                <div class="bg-primary-50 p-6 rounded-2xl border border-primary-100">
                  <h4 class="text-xs font-black uppercase text-primary-900 mb-4">Consumo de Sustancias</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                      <label class="text-[9px] font-bold text-primary-400 uppercase">Tabaco</label>
                      <Select v-model="localPatient.habits.tobacco_usage" :options="store.options.tobaccoLevels" optionLabel="label" optionValue="value" placeholder="Nivel..." class="w-full text-sm rounded-xl border-primary-100" />
                    </div>
                    <div class="flex flex-col gap-2">
                      <label class="text-[9px] font-bold text-primary-400 uppercase">Alcohol</label>
                      <Select v-model="localPatient.habits.alcohol_usage" :options="store.options.alcoholLevels" optionLabel="label" optionValue="value" placeholder="Nivel..." class="w-full text-sm rounded-xl border-primary-100" />
                    </div>
                    <div class="flex flex-col gap-2">
                      <label class="text-[9px] font-bold text-primary-400 uppercase">Café / Té</label>
                      <Select v-model="localPatient.habits.coffee_usage" :options="store.options.coffeeLevels" optionLabel="label" optionValue="value" placeholder="Nivel..." class="w-full text-sm rounded-xl border-primary-100" />
                    </div>
                    <div class="flex flex-col gap-2">
                      <label class="text-[9px] font-bold text-primary-400 uppercase">Energizantes</label>
                      <Select v-model="localPatient.habits.energy_usage" :options="store.options.energyLevels" optionLabel="label" optionValue="value" placeholder="Nivel..." class="w-full text-sm rounded-xl border-primary-100" />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </TabPanel>

          <!-- Pestaña 4: Tinnitus -->
          <TabPanel value="3">
            <div class="space-y-8">
                <!-- Nueva sección de Localización -->
                <div class="bg-primary-50 p-6 rounded-2xl border border-primary-100 flex flex-col md:flex-row md:items-center gap-8">
                    <div class="flex flex-col gap-2 flex-1">
                        <label class="text-[10px] font-black uppercase text-primary-400">Oído(s) Afectado(s)</label>
                        <Select v-model="localPatient.tinnitus_profile.affected_ears" :options="['Derecho', 'Izquierdo', 'Ambos']" placeholder="Localización..." class="w-full text-sm" />
                    </div>
                    <div class="flex-1 text-xs text-primary-400 italic font-medium">
                        Indique en qué oído el paciente percibe el acúfeno con mayor frecuencia o intensidad.
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3 p-4 bg-primary-50 rounded-2xl border border-primary-100">
                            <Checkbox v-model="localPatient.tinnitus_profile.discrimination_difficulty" binary />
                            <label class="text-xs font-bold text-primary-700">¿Dificulta la discriminación de sonidos?</label>
                        </div>
                        <div class="flex items-center gap-3 p-4 bg-primary-50 rounded-2xl border border-primary-100">
                            <Checkbox v-model="localPatient.tinnitus_profile.warble_tone_preference" binary />
                            <label class="text-xs font-bold text-primary-700">
                                ¿Reconoce mejor los "Tonos Warble"? 
                                <span class="block text-[9px] text-primary-400 font-medium">(Tonos de frecuencia cambiante)</span>
                            </label>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-center gap-3 p-4 bg-primary-50 rounded-2xl border border-primary-100">
                            <Checkbox v-model="localPatient.tinnitus_profile.loud_noise_exacerbation" binary />
                            <label class="text-xs font-bold text-primary-700">¿Se exacerba ante ruidos fuertes?</label>
                        </div>
                        <div class="flex flex-col gap-2 p-4 bg-primary-50 rounded-2xl border border-primary-100">
                            <label class="text-[9px] font-black uppercase text-primary-400">Inhibición Residual</label>
                            <Select v-model="localPatient.tinnitus_profile.residual_inhibition" :options="store.options.residualInhibitionOptions" optionLabel="label" optionValue="value" placeholder="Seleccionar resultado..." class="w-full text-sm rounded-xl border-primary-100" />
                        </div>
                    </div>
                </div>
            </div>
          </TabPanel>

          <!-- Pestaña 5: Historia Clínica -->
          <TabPanel value="4">
            <div class="space-y-8">
                <!-- Autopercepción y Equipamiento -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-primary-50 p-6 rounded-2xl border border-primary-100 flex flex-col gap-4">
                        <h4 class="text-xs font-black uppercase text-primary-900 flex items-center gap-2">
                            <Waves :size="14" class="text-accent-blue" /> Autopercepción Auditiva
                        </h4>
                        <div class="flex flex-col gap-4">
                            <div class="flex flex-col gap-2">
                                <label class="text-[9px] font-black uppercase text-primary-400">Lado</label>
                                <Select v-model="localPatient.clinical_history.hearing_loss_type" :options="['Derecho', 'Izquierdo', 'Ambos', 'Normal']" placeholder="Lado..." class="w-full text-sm" />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-[9px] font-black uppercase text-primary-400">Grado de Hipoacusia</label>
                                <Select v-model="localPatient.clinical_history.hearing_loss_degree" :options="store.options.hearingLossDegrees" optionLabel="label" optionValue="value" placeholder="Grado..." class="w-full text-sm" />
                            </div>
                            <div class="flex items-center gap-3 bg-white p-3 rounded-xl border border-primary-100 shadow-sm">
                                <Checkbox v-model="localPatient.clinical_history.ambient_noise_improvement" binary />
                                <label class="text-xs font-bold text-primary-700 italic">¿Oye mejor con ruido ambiental?</label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-primary-50 p-6 rounded-2xl border border-primary-100 flex flex-col gap-4">
                        <h4 class="text-xs font-black uppercase text-primary-900 flex items-center gap-2">
                            <Briefcase :size="14" class="text-accent-red" /> Equipamiento Auditivo
                        </h4>
                        <div class="space-y-4">
                            <div class="p-4 bg-white rounded-xl border border-primary-100 shadow-sm space-y-3">
                                <div class="flex items-center gap-3">
                                    <Checkbox v-model="localPatient.clinical_history.has_hearing_aid" binary />
                                    <label class="text-xs font-bold text-primary-700">Equipado con Audífono</label>
                                </div>
                                <div v-if="localPatient.clinical_history.has_hearing_aid" class="flex flex-col gap-2 pl-7 animate-in fade-in slide-in-from-top-1 duration-200">
                                    <label class="text-[9px] font-black uppercase text-primary-400 italic">Lado(s)</label>
                                    <Select v-model="localPatient.clinical_history.hearing_aid_side" :options="['Derecho', 'Izquierdo', 'Ambos']" placeholder="Seleccionar..." class="w-full text-xs" />
                                </div>
                            </div>

                            <div class="p-4 bg-white rounded-xl border border-primary-100 shadow-sm space-y-3">
                                <div class="flex items-center gap-3">
                                    <Checkbox v-model="localPatient.clinical_history.has_cochlear_implant" binary />
                                    <label class="text-xs font-bold text-primary-700">Implante Coclear</label>
                                </div>
                                <div v-if="localPatient.clinical_history.has_cochlear_implant" class="flex flex-col gap-2 pl-7 animate-in fade-in slide-in-from-top-1 duration-200">
                                    <label class="text-[9px] font-black uppercase text-primary-400 italic">Lado(s)</label>
                                    <Select v-model="localPatient.clinical_history.cochlear_implant_side" :options="['Derecho', 'Izquierdo', 'Ambos']" placeholder="Seleccionar..." class="w-full text-xs" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grupos Clínicos -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Familia y Otología -->
                    <div class="space-y-6">
                        <div class="bg-primary-50 p-6 rounded-2xl border border-primary-100">
                            <h4 class="text-xs font-black uppercase text-primary-900 mb-4 flex items-center gap-2">
                                <Heart :size="14" class="text-accent-red" /> Antecedentes Familiares
                            </h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div v-for="item in clinicalGroups.family" :key="item.key" class="flex items-center gap-2">
                                    <Checkbox :inputId="item.key" v-model="localPatient.clinical_history[item.key]" binary />
                                    <label :for="item.key" class="text-[11px] text-primary-600 font-bold uppercase tracking-tighter">{{ item.label }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="bg-primary-50 p-6 rounded-2xl border border-primary-100">
                            <h4 class="text-xs font-black uppercase text-primary-900 mb-4 flex items-center gap-2">
                                <Waves :size="14" class="text-accent-blue" /> Afecciones Otológicas
                            </h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div v-for="item in clinicalGroups.otologic" :key="item.key" class="flex items-center gap-2">
                                    <Checkbox :inputId="item.key" v-model="localPatient.clinical_history[item.key]" binary />
                                    <label :for="item.key" class="text-[11px] text-primary-600 font-bold uppercase tracking-tighter">{{ item.label }}</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Infecciosas -->
                    <div class="bg-primary-50 p-6 rounded-2xl border border-primary-100 h-full">
                        <h4 class="text-xs font-black uppercase text-primary-900 mb-4 flex items-center gap-2">
                           <ClipboardList :size="14" class="text-amber-500" /> Enf. Generales e Infecciosas
                        </h4>
                        <div class="grid grid-cols-2 gap-3">
                            <div v-for="item in clinicalGroups.infectious" :key="item.key" class="flex items-center gap-2">
                                <Checkbox :inputId="item.key" v-model="localPatient.clinical_history[item.key]" binary />
                                <label :for="item.key" class="text-[11px] text-primary-600 font-bold uppercase tracking-tighter">{{ item.label }}</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ototóxicos y Comorbilidades -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-primary-50 p-6 rounded-2xl border border-primary-100">
                        <h4 class="text-xs font-black uppercase text-primary-900 mb-4 flex items-center gap-2">
                            <Briefcase :size="14" class="text-accent-red" /> Tratamientos Ototóxicos
                        </h4>
                        <div class="grid grid-cols-2 gap-3">
                            <div v-for="item in clinicalGroups.ototoxic" :key="item.key" class="flex items-center gap-2">
                                <Checkbox :inputId="item.key" v-model="localPatient.clinical_history[item.key]" binary />
                                <label :for="item.key" class="text-[11px] text-primary-600 font-bold uppercase tracking-tighter">{{ item.label }}</label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-primary-50 p-6 rounded-2xl border border-primary-100">
                        <h4 class="text-xs font-black uppercase text-primary-900 mb-4 flex items-center gap-2">
                            <User :size="14" class="text-indigo-500" /> Comorbilidades
                        </h4>
                        <div class="grid grid-cols-2 gap-3">
                            <div v-for="item in clinicalGroups.comorbidities" :key="item.key" class="flex items-center gap-2">
                                <Checkbox :inputId="item.key" v-model="localPatient.clinical_history[item.key]" binary />
                                <label :for="item.key" class="text-[11px] text-primary-600 font-bold uppercase tracking-tighter">{{ item.label }}</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Exploración Física -->
                <div class="flex flex-col gap-2 bg-primary-900 p-8 rounded-3xl text-white shadow-xl shadow-primary-900/40">
                    <label class="text-[10px] font-black uppercase text-white/50 tracking-widest flex items-center gap-2">
                        <Save :size="14" /> Hallazgos de Exploración Física (Actual)
                    </label>
                    <Textarea v-model="localPatient.clinical_history.physical_exam_notes" rows="3" placeholder="Detalle aquí hallazgos de otoscopia, exploración craneal, etc..." class="w-full text-sm rounded-2xl border-none bg-white/10 text-white placeholder:text-white/20 focus:bg-white/20 transition-all font-medium" />
                </div>
            </div>
          </TabPanel>
        </TabPanels>
      </Tabs>
    </div>

    <!-- Footer Actions -->
    <div class="px-8 py-6 bg-primary-50 border-t border-primary-100 flex justify-end gap-3">
      <Button 
        @click="$emit('close')" 
        severity="secondary" 
        text 
        label="Cancelar" 
        class="text-xs uppercase font-black tracking-widest px-6"
      />
      <Button 
        @click="saveFicha" 
        severity="danger" 
        :loading="loading"
        class="bg-accent-red border-none px-8 py-3 rounded-xl shadow-lg shadow-red-500/20 text-xs font-black uppercase tracking-widest text-white flex items-center gap-2"
      >
        <Save :size="16" />
        Guardar Ficha Técnica
      </Button>
    </div>
  </div>
</template>

<style scoped>
:deep(.p-tablist-tab-list) {
  border-bottom: none !important;
}
:deep(.p-tab) {
  border-radius: 12px;
  transition: all 0.2s;
  color: #64748b;
  border: none !important;
}
:deep(.p-tab-active) {
  background: white;
  color: #1e293b;
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
}
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
