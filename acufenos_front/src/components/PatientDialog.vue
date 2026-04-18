<script setup>
import { ref, watch, computed } from 'vue'
import { useTinnitusStore } from '../stores/tinnitusStore'
import Dialog from 'primevue/dialog'
import Tabs from 'primevue/tabs'
import TabList from 'primevue/tablist'
import Tab from 'primevue/tab'
import TabPanels from 'primevue/tabpanels'
import TabPanel from 'primevue/tabpanel'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import DatePicker from 'primevue/datepicker'
import InputNumber from 'primevue/inputnumber'
import Checkbox from 'primevue/checkbox'
import Button from 'primevue/button'
import Textarea from 'primevue/textarea'

const props = defineProps({
  visible: Boolean,
  patient: Object
})

const emit = defineEmits(['update:visible', 'saved'])

const store = useTinnitusStore()
const form = ref({
  name: '',
  dni: '',
  email: '',
  gender: '',
  birth_date: null,
  occupation: '',
  province: '',
  city: '',
  laterality: '',
  evolution_years: 0,
  diagnosis: '',
  noise_exposure: false,
  habits: '',
  clinic_history: ''
})

const isEdit = computed(() => !!props.patient?.id)

watch(() => props.patient, (newVal) => {
  if (newVal) {
    form.value = { ...newVal, birth_date: newVal.birth_date ? new Date(newVal.birth_date) : null }
  } else {
    form.value = {
      name: '', dni: '', email: '', gender: '', birth_date: null,
      occupation: '', province: '', city: '', laterality: '',
      evolution_years: 0, diagnosis: '', noise_exposure: false,
      habits: '', clinic_history: ''
    }
  }
}, { immediate: true })

const close = () => emit('update:visible', false)

const save = () => {
  const data = { ...form.value }
  if (isEdit.value) {
    store.updatePatient(data)
  } else {
    store.addPatient(data)
  }
  emit('saved')
  close()
}

const filteredCities = computed(() => {
  if (!form.value.province) return []
  return store.cities.filter(c => c.province === form.value.province)
})
</script>

<template>
  <Dialog 
    :visible="visible" 
    @update:visible="emit('update:visible', $event)"
    :modal="true" 
    :header="isEdit ? 'Editar Ficha Clínica' : 'Nueva Ficha de Paciente'" 
    class="w-full max-w-4xl mx-4"
    :breakpoints="{'960px': '75vw', '640px': '90vw'}"
  >
    <template #header>
        <div class="flex flex-col">
            <h2 class="text-xl font-bold text-primary-900">{{ isEdit ? 'Editar Ficha Clínica' : 'Nueva Ficha de Paciente' }}</h2>
            <p class="text-xs text-primary-500 font-medium">Completa la información detallada para el gemelo digital.</p>
        </div>
    </template>

    <div class="mt-4">
      <Tabs value="0">
        <TabList>
          <Tab value="0">Identificación</Tab>
          <Tab value="1">Socio-Demog.</Tab>
          <Tab value="2">Entorno</Tab>
          <Tab value="3">Tinitus</Tab>
          <Tab value="4">Clínica</Tab>
        </TabList>
        <TabPanels class="pt-6">
          <!-- Tab 0: Identificación -->
          <TabPanel value="0">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="flex flex-col gap-2">
                <label for="name" class="text-xs font-bold uppercase text-primary-400">Nombre Completo</label>
                <InputText id="name" v-model="form.name" placeholder="Ej. Juan Pérez" />
              </div>
              <div class="flex flex-col gap-2">
                <label for="dni" class="text-xs font-bold uppercase text-primary-400">DNI / Documento</label>
                <InputText id="dni" v-model="form.dni" placeholder="Sin puntos" />
              </div>
              <div class="flex flex-col gap-2">
                <label for="email" class="text-xs font-bold uppercase text-primary-400">Email</label>
                <InputText id="email" v-model="form.email" placeholder="paciente@ejemplo.com" />
              </div>
              <div class="flex flex-col gap-2">
                <label for="birth_date" class="text-xs font-bold uppercase text-primary-400">Fecha de Nacimiento</label>
                <DatePicker id="birth_date" v-model="form.birth_date" dateFormat="dd/mm/yy" />
              </div>
            </div>
          </TabPanel>

          <!-- Tab 1: Socio-Demog -->
          <TabPanel value="1">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="flex flex-col gap-2">
                <label class="text-xs font-bold uppercase text-primary-400">Provincia</label>
                <Select v-model="form.province" :options="store.provinces" optionLabel="label" optionValue="value" placeholder="Seleccionar..." />
              </div>
              <div class="flex flex-col gap-2">
                <label class="text-xs font-bold uppercase text-primary-400">Ciudad</label>
                <Select v-model="form.city" :options="filteredCities" optionLabel="label" optionValue="value" placeholder="Seleccionar..." :disabled="!form.province" />
              </div>
              <div class="flex flex-col gap-2 md:col-span-2">
                <label for="occupation" class="text-xs font-bold uppercase text-primary-400">Ocupación / Profesión</label>
                <InputText id="occupation" v-model="form.occupation" />
              </div>
            </div>
          </TabPanel>

          <!-- Tab 2: Entorno -->
          <TabPanel value="2">
            <div class="space-y-6">
              <div class="flex items-center gap-3 p-4 bg-primary-50 rounded-xl border border-primary-100">
                <Checkbox v-model="form.noise_exposure" :binary="true" inputId="noise" />
                <label for="noise" class="text-sm font-semibold text-primary-700">Exposición a ruido laboral o recreativo</label>
              </div>
              <div class="flex flex-col gap-2">
                <label for="habits" class="text-xs font-bold uppercase text-primary-400">Hábitos (Fumador, Alcohol, Actividad Física)</label>
                <Textarea id="habits" v-model="form.habits" rows="3" autoResize />
              </div>
            </div>
          </TabPanel>

          <!-- Tab 3: Tinitus -->
          <TabPanel value="3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="flex flex-col gap-2">
                <label class="text-xs font-bold uppercase text-primary-400">Lateralidad Percibida</label>
                <Select v-model="form.laterality" :options="store.lateralityOptions" optionLabel="label" optionValue="value" placeholder="Seleccionar..." />
              </div>
              <div class="flex flex-col gap-2">
                <label for="evolution" class="text-xs font-bold uppercase text-primary-400">Años de Evolución</label>
                <InputNumber id="evolution" v-model="form.evolution_years" showButtons :min="0" :max="100" />
              </div>
              <div class="flex flex-col gap-2 md:col-span-2">
                <label for="diagnosis" class="text-xs font-bold uppercase text-primary-400">Diagnóstico Presuntivo</label>
                <InputText id="diagnosis" v-model="form.diagnosis" />
              </div>
            </div>
          </TabPanel>

          <!-- Tab 4: Clínica -->
          <TabPanel value="4">
            <div class="flex flex-col gap-2">
              <label for="history" class="text-xs font-bold uppercase text-primary-400">Antecedentes Clínicos / Observaciones</label>
              <Textarea id="history" v-model="form.clinic_history" rows="8" placeholder="Ototóxicos, cirugías previas, etc." />
            </div>
          </TabPanel>
        </TabPanels>
      </Tabs>
    </div>

    <template #footer>
      <div class="flex gap-3 justify-end mt-4">
        <Button label="Cancelar" icon="pi pi-times" severity="secondary" text @click="close" />
        <Button label="Guardar Paciente" icon="pi pi-check" severity="danger" class="bg-accent-red border-none px-6" @click="save" />
      </div>
    </template>
  </Dialog>
</template>

<style scoped>
:deep(.p-tablist-tab-list) {
    background: transparent;
    border: none;
}
:deep(.p-tab) {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.75rem 1.25rem;
}
:deep(.p-tab panels) {
    background: transparent;
    padding: 1.5rem 0 0 0;
}
</style>
