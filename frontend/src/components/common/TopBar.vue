<template>
  <div class="top-bar">
    <div style="display: flex; align-items: center; gap: 15px">
      <button class="mobile-menu-btn" @click="$emit('toggle-sidebar')">☰</button>
      <h1 class="page-title">{{ pageTitle }}</h1>
    </div>
    <div class="top-bar-actions">
      <div class="current-time">{{ currentDateTime }}</div>
      <div class="user-avatar" v-if="authStore.user">
        {{ authStore.user.name.charAt(0).toUpperCase() }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

defineEmits(['toggle-sidebar'])

const authStore = useAuthStore()
const route = useRoute()
const currentDateTime = ref('')

const pageTitle = computed(() => {
  const titles = {
    '/': 'Dashboard',
    '/office-agenda': 'Agenda Kantor',
    '/my-agenda': 'Agenda Saya',
    '/announcements': 'Pengumuman',
    '/users': 'Kelola Pengguna',
    '/rooms': 'Kelola Ruangan',
    '/participants': 'Kelola Partisipan Luar',
  }
  return titles[route.path] || 'BPS Admin'
})

const updateDateTime = () => {
  const now = new Date()
  const options = {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }
  currentDateTime.value = now.toLocaleDateString('id-ID', options)
}

let interval

onMounted(() => {
  updateDateTime()
  interval = setInterval(updateDateTime, 60000)
})

onUnmounted(() => {
  if (interval) clearInterval(interval)
})
</script>
