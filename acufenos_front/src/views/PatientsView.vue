<script setup>
import { ref, computed } from 'vue'
import { useTinnitusStore } from '../stores/tinnitusStore'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import PatientDialog from '../components/PatientDialog.vue'
import { Plus, Search, Pencil, Trash2, FileText } from 'lucide-vue-next'
import { useRouter } from 'vue-router'

const store = useTinnitusStore()
const router = useRouter()
const globalFilter = ref('')
const displayDialog = ref(false)
const editingPatient = ref(null)

const filteredPatients = computed(() => {
  if (!globalFilter.value) return store.patients
  const search = globalFilter.value.toLowerCase()
  return store.patients.filter(p => 
    p.name.toLowerCase().includes(search) || 
    p.dni.toLowerCase().includes(search)
  )
})

const openNew = () => {
  editingPatient.value = null
  displayDialog.value = true
}

const editPatient = (patient) => {
  editingPatient.value = { ...patient }
  displayDialog.value = true
}

const deletePatient = (id) => {
  if (confirm('¿Estás seguro de eliminar este paciente?')) {
    store.deletePatient(id)
  }
}

const getLateralityLabel = (val) => {
  const option = store.lateralityOptions.find(o => o.value === val)
  return option ? option.label : val
}

const getProvinceLabel = (val) => {
  const option = store.provinces.find(o => o.value === val)
  return option ? option.label : val
}

const openPatientEcosystem = (patient) => {
  store.selectPatient(patient)
  router.push(`/patients/${patient.id}/audiometry`)
}

const getLateralitySeverity = (lat) => {
  switch (lat) {
    case 'Bilateral': return 'secondary'
    case 'OI': return 'info'
    case 'OD': return 'danger'
    default: return 'contrast'
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-primary-900 border-l-4 border-accent-blue pl-4">Gestión de Pacientes</h1>
        <p class="text-sm text-primary-500 mt-1 ml-4">Base de datos clínica y administración de gemelos digitales.</p>
      </div>
      <Button 
        @click="openNew"
        severity="danger" 
        class="bg-accent-red border-none px-6 py-2.5 shadow-lg shadow-red-500/20"
      >
        <Plus :size="18" class="mr-2" />
        Nuevo Paciente
      </Button>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-primary-100 shadow-sm overflow-hidden">
      <div class="p-4 border-b border-primary-50 bg-primary-50/30 flex justify-between items-center">
        <span class="text-xs font-bold text-primary-400 uppercase tracking-widest">Listado Maestro</span>
        <div class="relative w-72">
          <Search :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-primary-400" />
          <InputText 
            v-model="globalFilter" 
            placeholder="Buscar por nombre o DNI..." 
            class="pl-10 w-full text-sm rounded-xl border-primary-100"
          />
        </div>
      </div>

      <DataTable 
        :value="filteredPatients" 
        dataKey="id"
        class="p-datatable-sm"
        :rows="10"
        paginator
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords}"
        responsiveLayout="stack"
      >
        <Column field="dni" header="DNI" headerClass="pl-6" />
        <Column field="name" header="Nombre" />
        <Column field="age" header="Edad" />
        <Column field="province" header="Provincia">
          <template #body="slotProps">
            {{ getProvinceLabel(slotProps.data.province) }}
          </template>
        </Column>
        <Column field="laterality" header="Lateralidad">
          <template #body="slotProps">
            <Tag :value="getLateralityLabel(slotProps.data.laterality)" :severity="getLateralitySeverity(slotProps.data.laterality)" class="text-[10px] font-bold px-2" />
          </template>
        </Column>

        <Column header="Acciones" headerClass="text-[10px] uppercase font-bold text-primary-400 tracking-tight text-right pr-6" bodyClass="text-right pr-4">
          <template #body="slotProps">
            <div class="flex items-center justify-end gap-1">
              <Button @click="editPatient(slotProps.data)" severity="secondary" text rounded size="small">
                <Pencil :size="16" />
              </Button>
              <Button @click="deletePatient(slotProps.data.id)" severity="danger" text rounded size="small">
                <Trash2 :size="16" />
              </Button>
              <div class="h-4 w-px bg-primary-100 mx-1"></div>
              <Button 
                label="Abrir Ficha" 
                severity="primary" 
                size="small" 
                @click="openPatientEcosystem(slotProps.data)" 
                class="text-[10px] font-bold uppercase"
              >
                <template #icon>
                  <FileText :size="12" class="mr-1.5" />
                </template>
              </Button>
            </div>
          </template>
        </Column>

        <template #empty>
          <div class="text-center py-12">
            <Search :size="48" class="mx-auto text-primary-100 mb-4" />
            <p class="text-primary-400 italic">No se encontraron pacientes para tu búsqueda.</p>
          </div>
        </template>
      </DataTable>
    </div>

    <!-- Modal Form -->
    <PatientDialog 
      v-model:visible="displayDialog" 
      :patient="editingPatient"
      @saved="() => {}"
    />
  </div>
</template>

<style scoped>
:deep(.p-datatable-thead > tr > th) {
  background: transparent;
  padding-top: 1.5rem;
  padding-bottom: 0.75rem;
  border-bottom: 2px solid #f1f5f9;
}
:deep(.p-datatable-tbody > tr) {
  transition: all 0.2s;
}
:deep(.p-datatable-tbody > tr:hover) {
  background-color: #f8fafc !important;
}
:deep(.p-datatable-tbody > tr > td) {
  padding-top: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #f1f5f9;
}
</style>
