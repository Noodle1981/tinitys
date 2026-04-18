import { createRouter, createWebHistory } from 'vue-router'
import DashboardView from '../views/DashboardView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'dashboard',
      component: DashboardView
    },
    {
      path: '/patients',
      name: 'patients',
      component: () => import('../views/PatientsView.vue')
    },
    {
      path: '/patients/:id/audiometry',
      name: 'audiometry',
      component: () => import('../views/AudiometryView.vue')
    },
    {
      path: '/patients/:id/profiling',
      name: 'profiling',
      component: () => import('../views/ProfilingView.vue')
    },
    {
      path: '/patients/:id/mapping',
      name: 'mapping',
      component: () => import('../views/MappingView.vue')
    },
    {
      path: '/patients/:id/report',
      name: 'report',
      component: () => import('../views/ReportView.vue')
    },
    {
      path: '/patients/:id/spectral',
      name: 'spectral',
      component: () => import('../views/SpectralView.vue')
    },
    {
      path: '/patients/:id/correlation',
      name: 'correlation',
      component: () => import('../views/CorrelationView.vue')
    },
    {
      path: '/settings',
      name: 'settings',
      component: () => import('../views/SettingsView.vue')
    }
  ]
})

export default router
