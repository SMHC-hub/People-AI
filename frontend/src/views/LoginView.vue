<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import NeumorphicButton from '@/components/NeumorphicButton.vue'
import NeumorphicBadge from '@/components/NeumorphicBadge.vue'

const router = useRouter()
const authStore = useAuthStore()

const email = ref('admin@hranalytics.com')
const password = ref('password')
const error = ref('')
const loading = ref(false)

async function handleLogin() {
  error.value = ''
  loading.value = true
  try {
    await authStore.login(email.value.trim(), password.value)
    await router.push('/')
  } catch (err: any) {
    error.value =
      err.response?.data?.message ||
      err.response?.data?.detail ||
      err.message ||
      'Invalid credentials. Please verify your email and password.'
  } finally {
    loading.value = false
  }
}

function fillCredentials(e: string, p: string) {
  email.value = e
  password.value = p
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-neu-base px-4 py-12 select-none">
    <div class="w-full max-w-md">
      <!-- Neumorphic Login Card -->
      <div class="rounded-3xl bg-neu-surface shadow-neu-raised border border-white/70 p-8 sm:p-10 space-y-6">
        <!-- Logo & Header -->
        <div class="text-center space-y-2">
          <div class="inline-flex items-center justify-center w-16 h-16 rounded-3xl bg-gradient-to-br from-neu-primary to-blue-600 shadow-neu-flat text-white font-black text-2xl mx-auto">
            P
          </div>
          <h1 class="text-2xl font-black text-neu-text tracking-tight mt-2">
            People<span class="text-neu-primary">AI</span>
          </h1>
          <p class="text-xs text-neu-muted font-medium">
            Intelligent Workforce & Attrition SaaS Platform
          </p>
          <div class="pt-1 flex justify-center">
            <NeumorphicBadge variant="primary" size="sm">
              Acme Global Technologies &bull; Enterprise
            </NeumorphicBadge>
          </div>
        </div>

        <!-- Error Alert -->
        <div
          v-if="error"
          class="p-3.5 rounded-2xl bg-rose-50 text-rose-700 text-xs font-semibold shadow-neu-inset border border-rose-200"
        >
          {{ error }}
        </div>

        <!-- Form -->
        <form @submit.prevent="handleLogin" class="space-y-4">
          <div class="space-y-1">
            <label for="email" class="block text-xs font-bold uppercase tracking-wider text-neu-muted">
              Corporate Email
            </label>
            <input
              id="email"
              v-model="email"
              type="email"
              required
              autocomplete="email"
              placeholder="admin@hranalytics.com"
              class="w-full px-4 py-3 rounded-2xl bg-neu-base shadow-neu-inset text-xs font-semibold text-neu-text border border-white/40 focus:outline-none"
            />
          </div>

          <div class="space-y-1">
            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-neu-muted">
              Password
            </label>
            <input
              id="password"
              v-model="password"
              type="password"
              required
              autocomplete="current-password"
              placeholder="••••••••"
              class="w-full px-4 py-3 rounded-2xl bg-neu-base shadow-neu-inset text-xs font-semibold text-neu-text border border-white/40 focus:outline-none"
            />
          </div>

          <div class="pt-2">
            <NeumorphicButton
              variant="primary"
              size="lg"
              type="submit"
              :loading="loading"
              class="w-full text-center justify-center font-black"
            >
              Sign In to Platform
            </NeumorphicButton>
          </div>
        </form>

        <!-- Quick Demo Switcher -->
        <div class="pt-4 border-t border-neu-border/50 space-y-2">
          <div class="text-[11px] font-bold uppercase tracking-wider text-neu-muted text-center">
            Quick Persona Switcher (Demo)
          </div>
          <div class="grid grid-cols-2 gap-2 text-xs">
            <button
              type="button"
              @click="fillCredentials('admin@hranalytics.com', 'password')"
              class="p-2 rounded-xl bg-neu-base shadow-neu-flat hover:shadow-neu-pressed text-[11px] font-bold text-neu-text border border-white/40 transition-all text-left truncate"
            >
              👑 Admin (Full Access)
            </button>
            <button
              type="button"
              @click="fillCredentials('hrmanager@hranalytics.com', 'password')"
              class="p-2 rounded-xl bg-neu-base shadow-neu-flat hover:shadow-neu-pressed text-[11px] font-bold text-neu-text border border-white/40 transition-all text-left truncate"
            >
              💼 HR Manager
            </button>
            <button
              type="button"
              @click="fillCredentials('hranalyst@hranalytics.com', 'password')"
              class="p-2 rounded-xl bg-neu-base shadow-neu-flat hover:shadow-neu-pressed text-[11px] font-bold text-neu-text border border-white/40 transition-all text-left truncate"
            >
              📊 HR Analyst
            </button>
            <button
              type="button"
              @click="fillCredentials('employee@hranalytics.com', 'password')"
              class="p-2 rounded-xl bg-neu-base shadow-neu-flat hover:shadow-neu-pressed text-[11px] font-bold text-neu-text border border-white/40 transition-all text-left truncate"
            >
              👤 Employee
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
