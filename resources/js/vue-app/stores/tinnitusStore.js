import { defineStore } from 'pinia'
import axios from 'axios'

export const useTinnitusStore = defineStore('tinnitus', {
  state: () => ({
    auth: { user: { name: 'Doctor' } }, // Se cargará de la sesión
    doctor: { specialty: 'Audiología' },
    patients: [],
    options: {
      provinces: [
        { label: "Buenos Aires", value: "buenos_aires" },
        { label: "Córdoba", value: "cordoba" },
        { label: "Santa Fe", value: "santa_fe" },
        { label: "Mendoza", value: "mendoza" },
        { label: "San Juan", value: "san_juan" }
      ],
      cities: [
        { label: "CABA", value: "caba", province: "buenos_aires" },
        { label: "Rosario", value: "rosario", province: "santa_fe" },
        { label: "Villa María", value: "villa_maria", province: "cordoba" },
        { label: "San Juan (Capital)", value: "sj_capital", province: "san_juan" }
      ],
      laterality: [
        { label: "Bilateral", value: "Bilateral" },
        { label: "Izquierdo (OI)", value: "OI" },
        { label: "Derecho (OD)", value: "OD" }
      ],
      educationalLevels: [
        { label: "Sin estudios", value: "sin_estudios" },
        { label: "Primario incompleto", value: "primario_incompleto" },
        { label: "Primario completo", value: "primario_completo" },
        { label: "Secundario incompleto", value: "secundario_incompleto" },
        { label: "Secundario completo", value: "secundario_completo" },
        { label: "Terciario/Universitario incompleto", value: "universitario_incompleto" },
        { label: "Terciario/Universitario completo", value: "universitario_completo" },
        { label: "Posgrado/Doctorado", value: "posgrado" }
      ],
      workSectors: [
        { label: "Industria Metalúrgica", value: "metalurgica" },
        { label: "Construcción", value: "construccion" },
        { label: "Transporte / Logística", value: "transporte" },
        { label: "Minería / Petróleo", value: "mineria" },
        { label: "Servicios / Oficina", value: "servicios" },
        { label: "Salud / Educación", value: "salud_educacion" },
        { label: "Entretenimiento / Música", value: "entretenimiento" },
        { label: "Militar / Seguridad", value: "militar" },
        { label: "Agricultura / Ganadería", value: "agricultura" },
        { label: "Otro", value: "otro" }
      ],
      jobPositions: [
        { label: "Operador de Maquinaria", value: "operador_maquina" },
        { label: "Técnico / Especialista", value: "tecnico" },
        { label: "Administrativo / Gerencial", value: "administrativo" },
        { label: "Conductor / Transportista", value: "conductor" },
        { label: "Mantenimiento / Maestranza", value: "mantenimiento" },
        { label: "Vendedor / Atención al público", value: "ventas" },
        { label: "Muro / Estibador", value: "estibador" },
        { label: "Docente / Académico", value: "docente" },
        { label: "Militar / Policía", value: "militar_policia" },
        { label: "Músico / DJ", value: "musico" },
        { label: "Otro", value: "otro" }
      ],
      protectionTypes: [
        { label: "Tapones (Endoaurales)", value: "tapones" },
        { label: "Orejeras (Supraaurales)", value: "orejeras" },
        { label: "Protección Dual (Ambos)", value: "dual" },
        { label: "Tapones a Medida", value: "medida" },
        { label: "Ninguno / Inexistente", value: "ninguno" }
      ],
      noiseProfiles: [
        { label: "Continuo / Estable", value: "continuo" },
        { label: "Intermitente", value: "intermitente" },
        { label: "Impacto / Impulsivo", value: "impacto" },
        { label: "Fluctuante", value: "fluctuante" }
      ],
      recoveryHoursOptions: [
        { label: "Sin descanso (<2h)", value: "ninguno" },
        { label: "Escaso (2-4h)", value: "escaso" },
        { label: "Moderado (4-8h)", value: "moderado" },
        { label: "Suficiente (>8h)", value: "suficiente" },
        { label: "Nocturno Completo", value: "nocturno" }
      ],
      residualInhibitionOptions: [
        { label: "Positiva (Completa)", value: "positiva_completa" },
        { label: "Positiva (Parcial)", value: "positiva_parcial" },
        { label: "Negativa (Nula)", value: "negativa" },
        { label: "Rebotada / Exacerbante", value: "rebotada" }
      ],
      hearingLossDegrees: [
        { label: "Normal / Sin pérdida", value: "normal" },
        { label: "Leve", value: "leve" },
        { label: "Moderada", value: "moderada" },
        { label: "Severa", value: "severa" },
        { label: "Profunda", value: "profunda" }
      ],
      tobaccoLevels: [
        { label: "No fumador", value: "no_fumador" },
        { label: "Fumador Pasivo", value: "pasivo" },
        { label: "Fumador Social", value: "social" },
        { label: "Nivel Bajo (Ocasional)", value: "bajo" },
        { label: "Moderado", value: "moderado" },
        { label: "Alto", value: "alto" }
      ],
      alcoholLevels: [
        { label: "No consume", value: "no_consume" },
        { label: "Consumo Ocasional", value: "ocasional" },
        { label: "Moderado", value: "moderado" },
        { label: "Alto / Frecuente", value: "alto" }
      ],
      coffeeLevels: [
        { label: "Sin consumo", value: "no_frecuente" },
        { label: "Bajo (1-2 tazas/día)", value: "bajo" },
        { label: "Moderado (3-5 tazas/día)", value: "moderado" },
        { label: "Alto (>5 tazas/día)", value: "alto" }
      ],
      energyLevels: [
        { label: "Sin consumo", value: "no_consume" },
        { label: "Ocasional", value: "ocasional" },
        { label: "Frecuente (1-3/semana)", value: "frecuente" },
        { label: "Alto / Diario", value: "alto" }
      ]
    },
    latestProfile: null,
    endpoints: {},
    selectedPatient: null,
    audiometryData: {
      right: {}, // format: { 1000: 20, 2000: 45, ... }
      left: {}
    },
    hearingAids: [],
    maintenanceHistory: [],
    patientHistory: [],
    latestMapping: { left: { status: 'healthy', layers: [] }, right: { status: 'healthy', layers: [] } },
  }),
  getters: {
    userName: (state) => state.auth.user.name,
    patientList: (state) => state.patients,
    provinces: (state) => state.options.provinces || [],
    cities: (state) => state.options.cities || [],
    lateralityOptions: (state) => state.options.laterality || []
  },
  actions: {
    async fetchPatients() {
      try {
        const response = await axios.get('/api/data/patients')
        if (response.data.success) {
          this.patients = response.data.data
        }
      } catch (error) {
        console.error('Error fetching patients:', error)
      }
    },
    async selectPatient(patient) {
      try {
        const response = await axios.get(`/api/data/patients/${patient.id}`)
        if (response.data.success) {
          this.selectedPatient = response.data.data
          // Sincronizar sub-objetos si es necesario
          this.hearingAids = this.selectedPatient.hearing_aids_data?.current_devices || []
        }
      } catch (error) {
        console.error('Error fetching patient details:', error)
      }
    },
    unselectPatient() {
      this.selectedPatient = null
      this.audiometryData = { right: {}, left: {} }
      this.hearingAids = []
    },
    async addPatient(patient) {
      try {
        const response = await axios.post('/api/data/patients', patient)
        if (response.data.success) {
          this.patients.push(response.data.data)
          return response.data.data
        }
      } catch (error) {
        console.error('Error adding patient:', error)
        throw error
      }
    },
    async updatePatient(updatedPatient) {
      try {
        const response = await axios.put(`/api/data/patients/${updatedPatient.id}`, updatedPatient)
        if (response.data.success) {
          const index = this.patients.findIndex(p => p.id === updatedPatient.id)
          if (index !== -1) {
            this.patients[index] = response.data.data
          }
          this.selectedPatient = response.data.data
        }
      } catch (error) {
        console.error('Error updating patient:', error)
        throw error
      }
    },
    async deletePatient(id) {
      if (confirm('¿Estás seguro de eliminar este paciente?')) {
        try {
          const response = await axios.delete(`/api/data/patients/${id}`)
          if (response.data.success) {
            this.patients = this.patients.filter(p => p.id !== id)
          }
        } catch (error) {
          console.error('Error deleting patient:', error)
        }
      }
    }
  }
})
