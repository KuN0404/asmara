<template>
  <AdminLayout>
    <div class="page-header">
      <h2 class="page-title">Tambah Partisipan</h2>
      <router-link to="/participants" class="btn btn-secondary">
        <ArrowLeft :size="18" /> Kembali
      </router-link>
    </div>

    <div class="form-container">
      <form @submit.prevent="handleSubmit">
        <div class="form-group">
          <label class="form-label">Nama *</label>
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
            placeholder="email@example.com"
          />
        </div>

        <div class="form-group">
          <label class="form-label">Nomor Telepon</label>
          <input type="text" class="form-input" v-model="form.phone" placeholder="08xxxxxxxxxx" />
        </div>

        <div class="form-group">
          <label class="form-label">Organisasi</label>
          <input
            type="text"
            class="form-input"
            v-model="form.organization"
            placeholder="Nama organisasi"
          />
        </div>

        <div class="form-actions">
          <router-link to="/participants" class="btn btn-secondary">Batal</router-link>
          <button type="submit" class="btn btn-primary" :disabled="loading">
            <Save :size="18" v-if="!loading" />
            <span v-if="!loading">Simpan</span>
            <span v-else>Menyimpan...</span>
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { ArrowLeft, Save } from 'lucide-vue-next'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { useNotificationStore } from '@/stores/notification'
import participantService from '@/services/participantService'
import { handleError } from '@/utils/helpers'

const router = useRouter()
const notificationStore = useNotificationStore()

const loading = ref(false)
const form = ref({
  name: '',
  email: '',
  phone: '',
  organization: '',
})

const handleSubmit = async () => {
  if (!form.value.name.trim()) {
    notificationStore.error('Nama partisipan wajib diisi')
    return
  }

  loading.value = true
  try {
    await participantService.create(form.value)
    notificationStore.success('Partisipan berhasil ditambahkan')
    router.push('/participants')
  } catch (error) {
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
  max-width: 600px;
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

.form-input:focus {
  outline: none;
  border-color: #1976d2;
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
