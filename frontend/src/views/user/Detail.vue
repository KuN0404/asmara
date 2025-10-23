<template>
  <AdminLayout>
    <div class="page-header">
      <h2 class="page-title">Detail User</h2>
      <router-link to="/users" class="btn btn-secondary">← Kembali</router-link>
    </div>

    <!-- Loading -->
    <LoadingSpinner v-if="loading" text="Memuat detail user..." />

    <!-- Error State -->
    <div v-else-if="error" class="error-state">
      <p>{{ error }}</p>
      <router-link to="/users" class="btn btn-primary">Kembali ke Daftar User</router-link>
    </div>

    <!-- Content -->
    <div v-else-if="user" class="detail-wrapper">
      <!-- Profile Card -->
      <div class="profile-card">
        <div class="profile-header">
          <div class="avatar-section">
            <img
              v-if="user.photo && !photoError"
              :src="getPhotoUrl(user.photo)"
              alt="Profile Photo"
              class="avatar-large"
              @error="handleImageError"
            />
            <div v-else class="avatar-large avatar-placeholder">
              {{ getInitials(user.name) }}
            </div>
          </div>

          <div class="profile-info">
            <h1 class="user-name">{{ user.name }}</h1>
            <p class="user-username">@{{ user.username }}</p>
            <div class="roles-badges">
              <span
                v-for="role in user.roles"
                :key="role.id"
                class="role-badge"
                :class="getRoleClass(role.name)"
              >
                {{ formatRoleName(role.name) }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Details Grid -->
      <div class="details-grid">
        <!-- Contact Information -->
        <div class="detail-card">
          <h3 class="card-title">📧 Informasi Kontak</h3>
          <div class="detail-list">
            <div class="detail-item">
              <span class="detail-label">Email</span>
              <span class="detail-value">{{ user.email || '-' }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">WhatsApp</span>
              <span class="detail-value">{{ user.whatsapp_number || '-' }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Alamat</span>
              <span class="detail-value">{{ user.address || '-' }}</span>
            </div>
          </div>
        </div>

        <!-- Account Information -->
        <div class="detail-card">
          <h3 class="card-title">👤 Informasi Akun</h3>
          <div class="detail-list">
            <div class="detail-item">
              <span class="detail-label">Username</span>
              <span class="detail-value">{{ user.username }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Bergabung</span>
              <span class="detail-value">{{ formatDateTime(user.created_at) }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Terakhir Diperbarui</span>
              <span class="detail-value">{{ formatDateTime(user.updated_at) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="actions" v-if="canEdit">
        <router-link :to="`/users/${user.id}/edit`" class="btn btn-edit">
          ✏️ Edit User
        </router-link>
        <button @click="handleDelete" class="btn btn-delete">🗑️ Hapus User</button>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AdminLayout from '@/layouts/AdminLayout.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import userService from '@/services/userService.js'
import { formatDateTime, handleError } from '@/utils/helpers'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const notificationStore = useNotificationStore()

const loading = ref(true)
const error = ref(null)
const user = ref(null)
const photoError = ref(false)
const baseURL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000'

const canEdit = computed(() => authStore.hasRole('super_admin'))

const handleImageError = () => {
  console.error('Failed to load user photo')
  photoError.value = true
}

onMounted(async () => {
  try {
    const id = route.params.id
    console.log('🔍 Loading user ID:', id)

    if (!id) {
      throw new Error('ID user tidak ditemukan')
    }

    const data = await userService.getById(id)
    console.log('✅ Data loaded:', data)

    user.value = data
  } catch (err) {
    console.error('❌ Error loading user:', err)
    error.value = err.response?.data?.message || err.message || 'Gagal memuat data user'
    notificationStore.error(error.value)
  } finally {
    loading.value = false
  }
})

const getPhotoUrl = (photo) => {
  if (!photo || photoError.value) return null

  // Jika sudah URL lengkap
  if (photo.startsWith('http://') || photo.startsWith('https://')) {
    return photo
  }

  // Bersihkan baseURL dari '/api'
  const cleanBaseURL = baseURL.replace('/api', '')

  // Pastikan path dimulai dengan 'storage/'
  let photoPath = photo
  if (!photoPath.startsWith('storage/') && !photoPath.startsWith('/storage/')) {
    photoPath = `storage/${photoPath}`
  }

  // Pastikan ada slash di depan path
  const cleanPhotoPath = photoPath.startsWith('/') ? photoPath : `/${photoPath}`

  return `${cleanBaseURL}${cleanPhotoPath}`
}

const getInitials = (name) => {
  if (!name) return 'U'
  const words = name.trim().split(/\s+/)
  if (words.length === 1) {
    return words[0].substring(0, 2).toUpperCase()
  }
  return (words[0][0] + words[words.length - 1][0]).toUpperCase()
}

const formatRoleName = (roleName) => {
  const roleMap = {
    super_admin: 'Super Admin',
    admin: 'Admin',
    user: 'User',
  }
  return roleMap[roleName] || roleName
}

const getRoleClass = (roleName) => {
  const classMap = {
    super_admin: 'role-super-admin',
    admin: 'role-admin',
    user: 'role-user',
  }
  return classMap[roleName] || 'role-default'
}

const handleDelete = async () => {
  if (!confirm(`Yakin ingin menghapus user "${user.value.name}"?`)) return

  try {
    await userService.delete(user.value.id)
    notificationStore.success('User berhasil dihapus ✅')
    router.push('/users')
  } catch (err) {
    notificationStore.error(handleError(err))
  }
}
</script>

<style scoped>
.error-state {
  background: #ffebee;
  color: #c62828;
  padding: 24px;
  border-radius: 12px;
  text-align: center;
  margin-top: 1rem;
}

.error-state p {
  margin-bottom: 16px;
  font-size: 16px;
}

.detail-wrapper {
  margin-top: 1rem;
}

/* Profile Card */
.profile-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 16px;
  padding: 40px;
  margin-bottom: 24px;
  box-shadow: 0 8px 24px rgba(102, 126, 234, 0.25);
}

.profile-header {
  display: flex;
  align-items: center;
  gap: 32px;
}

.avatar-section {
  flex-shrink: 0;
}

.avatar-large {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.avatar-placeholder {
  background: rgba(255, 255, 255, 0.2);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 48px;
  font-weight: 700;
  backdrop-filter: blur(10px);
}

.profile-info {
  flex: 1;
  color: #ffffff;
}

.user-name {
  font-size: 32px;
  font-weight: 700;
  margin: 0 0 8px 0;
  color: #ffffff;
}

.user-username {
  font-size: 18px;
  margin: 0 0 16px 0;
  opacity: 0.9;
}

.roles-badges {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.role-badge {
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
  backdrop-filter: blur(10px);
}

.role-super-admin {
  background: rgba(255, 215, 0, 0.3);
  color: #ffd700;
  border: 1px solid rgba(255, 215, 0, 0.5);
}

.role-admin {
  background: rgba(76, 175, 80, 0.3);
  color: #a5d6a7;
  border: 1px solid rgba(76, 175, 80, 0.5);
}

.role-user {
  background: rgba(255, 255, 255, 0.2);
  color: #ffffff;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

/* Details Grid */
.details-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 24px;
  margin-bottom: 24px;
}

.detail-card {
  background: #ffffff;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.card-title {
  font-size: 18px;
  font-weight: 700;
  color: #222;
  margin: 0 0 20px 0;
  padding-bottom: 12px;
  border-bottom: 2px solid #f0f0f0;
}

.detail-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.detail-label {
  font-size: 13px;
  font-weight: 600;
  color: #666;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.detail-value {
  font-size: 15px;
  color: #222;
  word-break: break-word;
}

/* Actions */
.actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  padding: 20px;
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  font-size: 14px;
  font-weight: 600;
  border-radius: 8px;
  cursor: pointer;
  text-decoration: none;
  border: none;
  transition: all 0.2s ease;
}

.btn-primary {
  background-color: #1976d2;
  color: #fff;
}

.btn-primary:hover {
  background-color: #125ea8;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(25, 118, 210, 0.3);
}

.btn-edit {
  background-color: #fff3e0;
  color: #f57c00;
}

.btn-edit:hover {
  background-color: #ffe0b2;
  transform: translateY(-1px);
}

.btn-delete {
  background-color: #ffebee;
  color: #d32f2f;
}

.btn-delete:hover {
  background-color: #ffcdd2;
  transform: translateY(-1px);
}

.btn-secondary {
  background-color: #f5f5f5;
  color: #444;
}

.btn-secondary:hover {
  background-color: #e0e0e0;
}

/* Responsive */
@media (max-width: 768px) {
  .profile-header {
    flex-direction: column;
    text-align: center;
  }

  .user-name {
    font-size: 24px;
  }

  .user-username {
    font-size: 16px;
  }

  .details-grid {
    grid-template-columns: 1fr;
  }

  .actions {
    flex-direction: column;
  }

  .btn {
    width: 100%;
    justify-content: center;
  }
}
</style>
