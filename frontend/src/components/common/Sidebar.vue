<template>
  <aside class="sidebar" :class="{ 'mobile-hidden': !visible }">
    <div class="sidebar-header">
      <div class="logo">
        <div class="logo-icon">BPS</div>
        <div class="logo-text">
          <h1>BPS Admin</h1>
          <p>Sistem Agenda Kantor</p>
        </div>
      </div>
      <div class="admin-info" v-if="authStore.user">
        <div class="admin-name">{{ authStore.user.name }}</div>
        <div class="admin-role">{{ getRoleName(authStore.user.roles[0]?.name) }}</div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-section">
        <div class="nav-section-title">Menu Utama</div>
        <router-link to="/" class="nav-item" active-class="active">
          <span class="nav-icon">📊</span>
          <span>Dashboard</span>
        </router-link>
        <router-link to="/announcements" class="nav-item" active-class="active">
          <span class="nav-icon">📋</span>
          <span>Pengumuman</span>
        </router-link>
        <router-link to="/office-agenda" class="nav-item" active-class="active">
          <span class="nav-icon">📅</span>
          <span>Agenda Kantor</span>
        </router-link>
        <router-link to="/my-agenda" class="nav-item" active-class="active">
          <span class="nav-icon">📅</span>
          <span>Agenda Saya</span>
        </router-link>
      </div>

      <div class="nav-section" v-if="canAccessAdmin">
        <div class="nav-section-title">Master Data</div>
        <router-link to="/users" class="nav-item" active-class="active">
          <span class="nav-icon">👥</span>
          <span>Pengguna</span>
        </router-link>
        <router-link to="/rooms" class="nav-item" active-class="active">
          <span class="nav-icon">🏢</span>
          <span>Ruangan</span>
        </router-link>
        <router-link to="/participants" class="nav-item" active-class="active">
          <span class="nav-icon">👤</span>
          <span>Partisipan</span>
        </router-link>
      </div>

      <div class="nav-section">
        <div class="nav-section-title">Akun</div>
        <router-link to="/profile" class="nav-item" active-class="active">
          <span class="nav-icon">👤</span>
          <span>Profil Saya</span>
        </router-link>
      </div>
    </nav>
  </aside>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

defineProps({
  visible: {
    type: Boolean,
    default: true,
  },
})

const authStore = useAuthStore()
const router = useRouter()

const canAccessAdmin = computed(() => {
  return authStore.hasRole('super_admin') || authStore.hasRole('admin')
})

const getRoleName = (role) => {
  const roles = {
    super_admin: 'Super Admin',
    admin: 'Admin',
    staff: 'Staff',
  }
  return roles[role] || role
}
</script>
