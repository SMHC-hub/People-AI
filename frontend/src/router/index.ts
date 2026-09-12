import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'dashboard',
      component: () => import('@/views/DashboardView.vue'),
    },
    {
      path: '/workforce',
      name: 'workforce',
      component: () => import('@/views/WorkforceView.vue'),
    },
    {
      path: '/employees',
      name: 'employees',
      component: () => import('@/views/EmployeeListView.vue'),
    },
    {
      path: '/employees/:id',
      name: 'employee-detail',
      component: () => import('@/views/EmployeeDetailView.vue'),
    },
    {
      path: '/attendance',
      name: 'attendance',
      component: () => import('@/views/AttendanceView.vue'),
    },
    {
      path: '/leaves',
      name: 'leaves',
      component: () => import('@/views/LeaveManagementView.vue'),
    },
    {
      path: '/payrolls',
      name: 'payrolls',
      component: () => import('@/views/PayrollView.vue'),
    },
    {
      path: '/performances',
      name: 'performances',
      component: () => import('@/views/PerformanceView.vue'),
    },
    {
      path: '/reports',
      name: 'reports',
      component: () => import('@/views/ReportView.vue'),
    },
    {
      path: '/chatbot',
      name: 'chatbot',
      component: () => import('@/views/ChatbotView.vue'),
    },
    {
      path: '/mlops',
      name: 'mlops',
      component: () => import('@/views/MLOpsView.vue'),
    },
    {
      path: '/settings',
      name: 'settings',
      component: () => import('@/views/SettingsView.vue'),
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/LoginView.vue'),
      meta: { public: true },
    },
  ],
})

router.beforeEach((to) => {
  const token = localStorage.getItem('auth_token')
  if (!to.meta.public && !token) {
    return { name: 'login' }
  }
})

export default router
