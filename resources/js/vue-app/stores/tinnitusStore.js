import { defineStore } from 'pinia'
import mockData from '../../tinnitus_mock_data.json'

export const useTinnitusStore = defineStore('tinnitus', {
  state: () => ({
    auth: mockData.auth,
    doctor: mockData.doctor,
    patients: mockData.patients || [],
    options: mockData.options || {},
    latestProfile: mockData.latest_profile,
    endpoints: mockData.api_endpoints_preview,
    selectedPatient: null,
    audiometryData: {
      right: {}, // format: { 1000: 20, 2000: 45, ... }
      left: {}
    },
    hearingAids: [],
    maintenanceHistory: [],
    patientHistory: [],
    latestMapping: mockData.latest_mapping || { left: { status: 'healthy', layers: [] }, right: { status: 'healthy', layers: [] } },
    latestProfile: mockData.latest_profile || null
  }),
  getters: {
    userName: (state) => state.auth.user.name,
    patientList: (state) => state.patients,
    provinces: (state) => state.options.provinces || [],
    cities: (state) => state.options.cities || [],
    lateralityOptions: (state) => state.options.laterality || []
  },
  actions: {
    selectPatient(patient) {
      this.selectedPatient = patient
      this.hearingAids = patient.hearing_aids_data?.current_devices || []
      this.maintenanceHistory = patient.hearing_aids_data?.maintenance_history || []
      this.patientHistory = patient.hearing_aids_data?.audiometry_history || []
      
      // Auto-cargar la última audiometría si existe en el historial
      if (this.patientHistory.length > 0 && !Object.keys(this.audiometryData.right).length) {
        this.audiometryData = JSON.parse(JSON.stringify(this.patientHistory[0].data))
      }
    },
    unselectPatient() {
      this.selectedPatient = null
      this.audiometryData = { right: {}, left: {} }
      this.hearingAids = []
      this.maintenanceHistory = []
      this.patientHistory = []
    },
    updateAudiometry(data) {
      this.audiometryData = { ...data }
    },
    updateHearingAid(updatedAid) {
      const index = this.hearingAids.findIndex(a => a.id === updatedAid.id)
      if (index !== -1) {
        this.hearingAids[index] = { ...updatedAid }
      }
    },
    addPatient(patient) {
      const newId = Math.max(...this.patients.map(p => p.id), 0) + 1
      this.patients.push({ ...patient, id: newId })
    },
    updatePatient(updatedPatient) {
      const index = this.patients.findIndex(p => p.id === updatedPatient.id)
      if (index !== -1) {
        this.patients[index] = { ...updatedPatient }
      }
    },
    deletePatient(id) {
      this.patients = this.patients.filter(p => p.id !== id)
    }
  }
})
