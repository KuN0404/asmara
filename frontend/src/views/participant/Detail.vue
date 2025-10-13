<template>
  <AdminLayout>
    <div class="page-header">
      <h2 class="page-title">Detail Partisipan Luar</h2>
      <router-link to="/participants" class="btn btn-secondary">← Kembali</router-link>
    </div>

    <!-- Loading -->
    <LoadingSpinner v-if="loading" text="Memuat detail pengumuman..." />

    <!-- Error State -->
    <div v-else-if="error" class="error-state">
      <p>{{ error }}</p>
      <router-link to="/participants" class="btn btn-primary">Kembali ke Daftar</router-link>
    </div>

    <!-- Content -->
    <div v-else-if="participant" class="detail-card">
      <!-- Header -->
      <div class="detail-header">
        <h1 class="title">{{ participant.name }}</h1>
        <div class="meta">
          <span>👤 {{ participant?.email || 'Tidak dicantumkan' }}</span>
          <span>👤 {{ participant?.phone || 'Tidak dicantumkan' }}</span>
          <span>👤 {{ participant?.organization || 'Tidak dicantumkan' }}</span>
        </div>
      </div>
      <!-- Actions -->
      <div class="actions" v-if="canEdit">
        <router-link :to="`/participants/${participant.id}/edit`" class="btn btn-edit">
          ✏️ Edit
        </router-link>
        <button @click="handleDelete" class="btn btn-delete">🗑️ Hapus</button>
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
import participantService from '@/services/participantService'
import { formatDateTime, handleError } from '@/utils/helpers'
import '@vueup/vue-quill/dist/vue-quill.snow.css'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const notificationStore = useNotificationStore()

const loading = ref(true)
const error = ref(null)
const participant = ref(null)
const baseURL = import.meta.env.VITE_API_BASE_URL

const canEdit = computed(() => authStore.hasRole('super_admin') || authStore.hasRole('admin'))

onMounted(async () => {
  try {
    const id = route.params.id
    console.log('🔍 Loading participant ID:', id)

    if (!id) {
      throw new Error('ID pengumuman tidak ditemukan')
    }

    const data = await participantService.getById(id)
    console.log('✅ Data loaded:', data)

    participant.value = data
  } catch (err) {
    console.error('❌ Error loading participant:', err)
    error.value = err.response?.data?.message || err.message || 'Gagal memuat data pengumuman'
    notificationStore.error(error.value)
  } finally {
    loading.value = false
  }
})

const formatFileSize = (bytes) => {
  if (!bytes) return '0 B'
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / 1048576).toFixed(1) + ' MB'
}

const getFileIcon = (mimeType) => {
  if (!mimeType) return '📎'
  if (mimeType.includes('pdf')) return '📄'
  if (mimeType.includes('image')) return '🖼️'
  if (mimeType.includes('word') || mimeType.includes('document')) return '📝'
  return '📎'
}

const handleDelete = async () => {
  if (!confirm('Yakin hapus pengumuman ini?')) return

  try {
    await participantService.delete(participant.value.id)
    notificationStore.success('Partisipan Luar berhasil dihapus ✅')
    router.push('/participants')
  } catch (err) {
    notificationStore.error(handleError(err))
  }
}
</script>

<style scoped>
.detail-content :deep(pre) {
  background: #f5f5f5;
  padding: 12px;
  border-radius: 6px;
  overflow-x: auto;
}

.detail-content :deep(blockquote) {
  border-left: 4px solid #ddd;
  padding-left: 16px;
  margin: 16px 0;
  color: #666;
  font-style: italic;
}

.detail-content :deep(code) {
  background: #f5f5f5;
  padding: 2px 6px;
  border-radius: 4px;
  font-family: 'Courier New', monospace;
}

.detail-content :deep(ul),
.detail-content :deep(ol) {
  padding-left: 24px;
  margin: 12px 0;
}

.detail-content :deep(h1),
.detail-content :deep(h2),
.detail-content :deep(h3) {
  margin-top: 20px;
  margin-bottom: 12px;
  font-weight: 600;
}

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

.detail-card {
  background: #ffffff;
  border-radius: 12px;
  padding: 32px;
  margin-top: 1rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.detail-header {
  margin-bottom: 24px;
}

.title {
  font-size: 28px;
  font-weight: 700;
  color: #222;
  margin-bottom: 12px;
}

.meta {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
  font-size: 14px;
  color: #666;
}

.badge-active {
  background: #4caf50;
  color: #fff;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 13px;
}

.badge-inactive {
  background: #ccc;
  color: #fff;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 13px;
}

.detail-content {
  font-size: 16px;
  line-height: 1.8;
  color: #333;
  margin-bottom: 32px;
  min-height: 100px;
}

.detail-content :deep(img) {
  max-width: 100%;
  height: auto;
  border-radius: 8px;
  margin: 16px 0;
}

.detail-content :deep(p) {
  margin-bottom: 12px;
}

.attachments {
  background: #f9f9f9;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 24px;
}

.attachments h3 {
  font-size: 18px;
  margin-bottom: 12px;
  color: #444;
}

.attachment-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.attachment-list li {
  margin-bottom: 8px;
}

.attachment-link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: #1976d2;
  text-decoration: none;
  font-size: 14px;
  transition: 0.2s;
}

.attachment-link:hover {
  color: #125ea8;
  text-decoration: underline;
}

.file-size {
  color: #999;
  font-size: 12px;
}

.actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  padding-top: 20px;
  border-top: 1px solid #f0f0f0;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 10px 18px;
  font-size: 14px;
  border-radius: 8px;
  cursor: pointer;
  text-decoration: none;
  border: none;
  transition: 0.2s;
}

.btn-primary {
  background-color: #1976d2;
  color: #fff;
}

.btn-primary:hover {
  background-color: #125ea8;
}

.btn-edit {
  background-color: #fff3e0;
  color: #f57c00;
}

.btn-edit:hover {
  background-color: #ffe0b2;
}

.btn-delete {
  background-color: #ffebee;
  color: #d32f2f;
}

.btn-delete:hover {
  background-color: #ffcdd2;
}

.btn-secondary {
  background-color: #f1f1f1;
  color: #444;
}

.btn-secondary:hover {
  background-color: #e1e1e1;
}
</style>
