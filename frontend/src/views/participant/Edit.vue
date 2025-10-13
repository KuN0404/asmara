<template>
  <AdminLayout>
    <div class="page-header">
      <h2 class="page-title">Edit Partisipan Luar</h2>
      <router-link to="/participants" class="btn btn-secondary">← Kembali</router-link>
    </div>

    <LoadingSpinner v-if="loadingData" text="Memuat data..." />

    <div v-else-if="participant" class="form-container">
      <form @submit.prevent="handleSubmit">
        <!-- Judul -->
        <div class="form-group">
          <label class="form-label">Nama Partisipan *</label>
          <input
            type="text"
            class="form-input"
            v-model="form.name"
            placeholder="Masukkan nama partisipan"
            required
          />
        </div>

        <div class="form-group">
          <label class="form-label">Email</label>
          <input
            type="email"
            class="form-input"
            v-model="form.email"
            placeholder="Masukkan email partisipan"
          />
        </div>

        <div class="form-group">
          <label class="form-label">No HP</label>
          <input
            type="text"
            class="form-input"
            v-model="form.phone"
            placeholder="Masukkan no hp partisipan"
          />
        </div>

        <div class="form-group">
          <label class="form-label">Organisasi</label>
          <input
            type="text"
            class="form-input"
            v-model="form.organization"
            placeholder="Masukkan organisasi partisipan"
          />
        </div>

        <!-- Actions -->
        <div class="form-actions">
          <router-link to="/participants" class="btn btn-secondary">Batal</router-link>
          <button type="submit" class="btn btn-primary" :disabled="loading">
            <span v-if="!loading">💾 Simpan Perubahan</span>
            <span v-else>Menyimpan...</span>
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AdminLayout from '@/layouts/AdminLayout.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import { useNotificationStore } from '@/stores/notification'
import participantService from '@/services/participantService'
import { handleError } from '@/utils/helpers'
import '@vueup/vue-quill/dist/vue-quill.snow.css'

const route = useRoute()
const router = useRouter()
const notificationStore = useNotificationStore()

const loadingData = ref(true)
const loading = ref(false)
const participant = ref(null)

const form = ref({
  name: '',
  email: '',
  phone: '',
  organization: '',
})

onMounted(async () => {
  try {
    const data = await participantService.getById(route.params.id)
    participant.value = data

    form.value.name = data.name
    form.value.email = data.email
    form.value.phone = data.phone
    form.value.organization = data.organization
  } catch (error) {
    notificationStore.error(handleError(error))
    router.push('/participants')
  } finally {
    loadingData.value = false
  }
})

const handleSubmit = async () => {
  if (!form.value.name.trim()) {
    notificationStore.error('Nama partisipan wajib diisi')
    return
  }
  if (form.value.email && !/\S+@\S+\.\S+/.test(form.value.email)) {
    notificationStore.error('Email tidak valid')
    return
  }

  if (form.value.phone) {
    if (!/^\d+$/.test(form.value.phone)) {
      notificationStore.error('No HP hanya boleh berisi angka')
      return
    }
    if (form.value.phone.length > 13) {
      notificationStore.error('No HP maksimal 13 digit')
      return
    }
    if (form.value.phone.length < 11) {
      notificationStore.error('No HP minimal 11 digit')
      return
    }
  }

  const formData = new FormData()
  formData.append('name', form.value.name)
  formData.append('email', form.value.email || '')
  formData.append('phone', form.value.phone || '')
  formData.append('organization', form.value.organization || '')

  loading.value = true
  try {
    await participantService.update(route.params.id, formData)
    notificationStore.success('Partisipan Luar berhasil diperbarui ✅')
    router.push('/participants')
  } catch (error) {
    console.error('❌ Error update:', error)
    notificationStore.error(handleError(error))
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.form-container {
  background: #fff;
  border-radius: 12px;
  padding: 24px;
  margin-top: 1rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  max-width: 750px;
  margin-left: auto;
  margin-right: auto;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-label {
  display: block;
  font-weight: 600;
  margin-bottom: 6px;
  color: #444;
}

.form-input {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
}

.quill-editor .ql-container {
  min-height: 300px;
}

.quill-editor .ql-editor {
  min-height: 300px;
  padding-bottom: 50px;
}

.existing-attachment-list {
  list-style: none;
  padding: 0;
  background: #f9f9f9;
  padding: 12px;
  border-radius: 8px;
}

.existing-attachment-list li {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #eee;
}

.existing-attachment-list li:last-child {
  border-bottom: none;
}

.existing-attachment-list a {
  color: #1976d2;
  text-decoration: none;
  font-size: 14px;
}

.remove-btn {
  background: transparent;
  border: none;
  color: #d32f2f;
  cursor: pointer;
  font-size: 16px;
}

.attachment-list {
  list-style: none;
  padding-left: 0;
  margin-top: 8px;
}

.attachment-list li {
  margin-top: 6px;
  font-size: 13px;
  color: #555;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.remove-file-btn {
  background: transparent;
  border: none;
  color: red;
  font-size: 14px;
  cursor: pointer;
  margin-left: 8px;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
}

.checkbox-input {
  width: 20px;
  height: 20px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 20px;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 4px;
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

.btn-secondary {
  background-color: #f1f1f1;
  color: #444;
}

.btn-secondary:hover {
  background-color: #e1e1e1;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
