<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import SidebarNav from '@/components/SidebarNav.vue'
import TopHeader from '@/components/TopHeader.vue'
import CommandPalette from '@/components/CommandPalette.vue'

const route = useRoute()
const showPalette = ref(false)

const showLayout = computed(() => route.name !== 'login')

function handleKeyDown(e: KeyboardEvent) {
  if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
    e.preventDefault()
    showPalette.value = !showPalette.value
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown)
})
</script>

<template>
  <div class="flex min-h-screen bg-neu-base text-neu-text antialiased">
    <SidebarNav v-if="showLayout" />
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <TopHeader v-if="showLayout" @open-command-palette="showPalette = true" />
      <main class="flex-1 overflow-y-auto p-6 lg:p-8">
        <router-view />
      </main>
    </div>

    <!-- Global Command Palette (Ctrl+K) -->
    <CommandPalette :isOpen="showPalette" @close="showPalette = false" />
  </div>
</template>
