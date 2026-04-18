import { createRouter, createWebHistory } from 'vue-router'
import PatientsView from '../views/PatientsView.vue'
import DashboardView from '../views/DashboardView.vue'

const router = createRouter({
  history: createWebHistory('/patients'),
  routes: [
    {
      path: '/',
      name: 'patients',
      component: PatientsView
    },
    {
      path: '/:id/summary',
      name: 'summary',
      component: DashboardView
    },
    {
      path: '/:id/audiometry',
      name: 'audiometry',
      component: () => import('../views/AudiometryView.vue')
    },
    {
      path: '/:id/profiling',
      name: 'profiling',
      component: () => import('../views/ProfilingView.vue')
    },
    {
      path: '/:id/mapping',
      name: 'mapping',
      component: () => import('../views/MappingView.vue')
    },
    {
      path: '/:id/report',
      name: 'report',
      component: () => import('../views/ReportView.vue')
    },
    {
      path: '/:id/spectral',
      name: 'spectral',
      component: () => import('../views/SpectralView.vue')
    },
    {
      path: '/:id/correlation',
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
