<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import SidebarNav from '@/components/SidebarNav.vue'

const route = useRoute()

const showSidebar = computed(() => route.name !== 'login')
</script>

<template>
  <div class="flex min-h-screen bg-gray-50">
    <SidebarNav v-if="showSidebar" />
    <main class="flex-1 overflow-auto">
      <router-view />
    </main>
  </div>
</template>
