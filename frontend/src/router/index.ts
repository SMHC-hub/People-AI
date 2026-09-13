import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(),
  scrollBehavior(_to, _from, savedPosition) {
    if (savedPosition) return savedPosition
    return { top: 0 }
  },
  routes: [
    {
      path: '/',
      alias: ['/dashboard'],
      name: 'dashboard',
      component: () => import('@/views/DashboardView.vue'),
    },
    {
      path: '/workforce',
      alias: ['/workforce-risk'],
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
      alias: ['/payroll'],
      name: 'payrolls',
      component: () => import('@/views/PayrollView.vue'),
    },
    {
      path: '/performances',
      alias: ['/performance'],
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
      alias: ['/ai-assistant', '/chat'],
      name: 'chatbot',
      component: () => import('@/views/ChatbotView.vue'),
    },
    {
      path: '/mlops',
      alias: ['/models'],
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
    // Employee Portal Routes
    {
      path: '/portal',
      alias: ['/my-dashboard'],
      name: 'portal-dashboard',
      component: () => import('@/views/portal/EmployeeDashboardView.vue'),
    },
    {
      path: '/portal/attendance',
      alias: ['/my-attendance'],
      name: 'portal-attendance',
      component: () => import('@/views/portal/EmployeeAttendanceView.vue'),
    },
    {
      path: '/portal/leaves',
      alias: ['/my-leaves'],
      name: 'portal-leaves',
      component: () => import('@/views/portal/EmployeeLeavesView.vue'),
    },
    {
      path: '/portal/payrolls',
      alias: ['/my-payrolls', '/my-payslips'],
      name: 'portal-payrolls',
      component: () => import('@/views/portal/EmployeePayrollsView.vue'),
    },
    {
      path: '/portal/performance',
      alias: ['/my-performance'],
      name: 'portal-performance',
      component: () => import('@/views/portal/EmployeePerformanceView.vue'),
    },
    {
      path: '/portal/profile',
      alias: ['/my-profile'],
      name: 'portal-profile',
      component: () => import('@/views/portal/EmployeeProfileView.vue'),
    },
    {
      path: '/:pathMatch(.*)*',
      redirect: '/',
    },
  ],
})

router.beforeEach(async (to) => {
  const token = localStorage.getItem('auth_token')
  if (!to.meta.public && !token) {
    return { name: 'login' }
  }

  if (token) {
    const { useAuthStore } = await import('@/stores/auth')
    const authStore = useAuthStore()
    if (!authStore.user) {
      await authStore.fetchUser()
    }

    // Role-based routing:
    const isEmployee = authStore.user?.role === 'employee'
    if (isEmployee) {
      const adminOnlyPaths = [
        '/',
        '/dashboard',
        '/workforce',
        '/workforce-risk',
        '/employees',
        '/attendance',
        '/leaves',
        '/payrolls',
        '/payroll',
        '/performances',
        '/performance',
        '/reports',
        '/mlops',
        '/models',
        '/settings',
      ]
      if (adminOnlyPaths.includes(to.path) || to.path.startsWith('/employees/')) {
        return { path: '/portal' }
      }
    } else {
      // Admin trying to access employee portal -> redirect to executive dashboard
      if (to.path.startsWith('/portal')) {
        return { path: '/' }
      }
    }
  }
})

router.onError((error) => {
  console.error('Router navigation error caught:', error)
})

export default router
