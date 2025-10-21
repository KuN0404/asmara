<template>
  <div class="top-bar">
    <div style="display: flex; align-items: center; gap: 15px">
      <button class="mobile-menu-btn" @click="$emit('toggle-sidebar')">☰</button>
      <h1 class="page-title">{{ pageTitle }}</h1>
    </div>
    <div class="top-bar-actions">
      <div class="current-time">{{ currentDateTime }}</div>
      <div class="user-menu" v-if="authStore.user">
        <div class="user-avatar" @click="toggleDropdown">
          <img
            v-if="userPhoto"
            :src="userPhoto"
            :alt="authStore.user.name"
            class="avatar-image"
            @error="handleImageError"
          />
          <span v-else class="avatar-initial">{{ userInitial }}</span>
        </div>
        <transition name="dropdown">
          <div class="dropdown-menu" v-if="showDropdown" ref="dropdownRef">
            <div class="dropdown-header">
              <div class="dropdown-avatar">
                <img
                  v-if="userPhoto"
                  :src="userPhoto"
                  :alt="authStore.user.name"
                  class="avatar-image"
                  @error="handleImageError"
                />
                <span v-else class="avatar-initial">{{ userInitial }}</span>
              </div>
              <div class="dropdown-user-info">
                <div class="dropdown-user-name">{{ authStore.user.name }}</div>
                <div class="dropdown-user-email" v-if="authStore.user.email">
                  {{ authStore.user.email }}
                </div>
              </div>
            </div>
            <div class="dropdown-divider"></div>
            <button class="dropdown-item" @click="goToProfile">
              <span class="dropdown-icon">👤</span>
              Profil Saya
            </button>
            <button class="dropdown-item logout" @click="handleLogout">
              <span class="dropdown-icon">🚪</span>
              Keluar
            </button>
          </div>
        </transition>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

defineEmits(['toggle-sidebar'])

const authStore = useAuthStore()
const route = useRoute()
const router = useRouter()
const currentDateTime = ref('')
const showDropdown = ref(false)

// Tambahkan ref untuk dropdown element
const dropdownRef = ref(null)

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

const userInitial = computed(() => {
  if (!authStore.user?.name) return '?'
  return authStore.user.name.charAt(0).toUpperCase()
})

const photoError = ref(false)

const userPhoto = computed(() => {
  if (!authStore.user?.photo || photoError.value) return null

  const photo = authStore.user.photo

  if (photo.startsWith('http://') || photo.startsWith('https://')) {
    return photo
  }

  const baseURL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000'
  const cleanBaseURL = baseURL.replace('/api', '')

  let photoPath = photo
  if (!photoPath.startsWith('storage/') && !photoPath.startsWith('/storage/')) {
    photoPath = `storage/${photoPath}`
  }

  const cleanPhotoPath = photoPath.startsWith('/') ? photoPath : `/${photoPath}`

  return `${cleanBaseURL}${cleanPhotoPath}`
})

const handleImageError = () => {
  photoError.value = true
}

const updateDateTime = () => {
  const now = new Date()
  const options = {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
  }
  currentDateTime.value = now.toLocaleDateString('id-ID', options)
}

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value
}

const closeDropdown = () => {
  showDropdown.value = false
}

const goToProfile = () => {
  closeDropdown()
  router.push('/profile')
}

const handleLogout = async () => {
  closeDropdown()
  try {
    await authStore.logout()
    router.push('/login')
  } catch (error) {
    console.error('Logout error:', error)
    router.push('/login')
  }
}

// PERBAIKAN: Handle click outside dengan cara yang lebih sederhana
const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    // Cek juga apakah klik bukan di avatar
    const avatarElement = event.target.closest('.user-avatar')
    if (!avatarElement) {
      closeDropdown()
    }
  }
}

let interval

onMounted(() => {
  updateDateTime()
  interval = setInterval(updateDateTime, 1000)

  // PERBAIKAN: Tambahkan event listener saat mounted
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  if (interval) clearInterval(interval)

  // PERBAIKAN: Hapus event listener saat unmounted
  document.removeEventListener('click', handleClickOutside)
})

watch(
  () => authStore.user?.photo,
  () => {
    photoError.value = false
  },
)
</script>

<style scoped>
.top-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  background: white;
  border-bottom: 1px solid #e5e7eb;
  position: sticky;
  top: 0;
  z-index: 100;
}

.mobile-menu-btn {
  display: none;
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0.5rem;
  color: #374151;
}

.page-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #111827;
  margin: 0;
}

.top-bar-actions {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.current-time {
  font-size: 0.875rem;
  color: #6b7280;
  font-weight: 500;
}

.user-menu {
  position: relative;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition:
    transform 0.2s,
    box-shadow 0.2s;
  overflow: hidden;
  border: 2px solid #fff;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.user-avatar:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.avatar-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-initial {
  font-size: 1rem;
  font-weight: 600;
}

.dropdown-menu {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  background: white;
  border-radius: 8px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  min-width: 260px;
  overflow: hidden;
  z-index: 1000;
}

.dropdown-header {
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: #f9fafb;
}

.dropdown-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 1.25rem;
  flex-shrink: 0;
  overflow: hidden;
  border: 2px solid #fff;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.dropdown-avatar .avatar-initial {
  font-size: 1.25rem;
}

.dropdown-user-info {
  flex: 1;
  min-width: 0;
}

.dropdown-user-name {
  font-weight: 600;
  color: #111827;
  font-size: 0.95rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.dropdown-user-email {
  font-size: 0.8rem;
  color: #6b7280;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.dropdown-divider {
  height: 1px;
  background: #e5e7eb;
}

.dropdown-item {
  width: 100%;
  padding: 0.75rem 1rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  border: none;
  background: none;
  cursor: pointer;
  transition: background 0.2s;
  font-size: 0.9rem;
  color: #374151;
  text-align: left;
}

.dropdown-item:hover {
  background: #f3f4f6;
}

.dropdown-item.logout {
  color: #dc2626;
}

.dropdown-item.logout:hover {
  background: #fee2e2;
}

.dropdown-icon {
  font-size: 1.1rem;
}

/* Transition animations */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}

.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

@media (max-width: 768px) {
  .mobile-menu-btn {
    display: block;
  }

  .page-title {
    font-size: 1.25rem;
  }

  .current-time {
    display: none;
  }

  .dropdown-menu {
    right: -10px;
  }
}
</style>
