<template>
  <AdminLayout>
    <div class="page-header">
      <h2 class="page-title">Tambah Agenda Saya</h2>
      <router-link to="/my-agenda" class="btn btn-secondary">← Kembali</router-link>
    </div>

    <div class="form-container">
      <form @submit.prevent="handleSubmit">
        <div class="form-group">
          <label class="form-label">Judul Agenda *</label>
          <input
            type="text"
            class="form-input"
            v-model="form.title"
            placeholder="Masukkan judul agenda"
            required
          />
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Tanggal & Waktu Mulai *</label>
            <input type="datetime-local" class="form-input" v-model="form.start_at" required />
          </div>
          <div class="form-group">
            <label class="form-label">Tanggal & Waktu Selesai *</label>
            <input type="datetime-local" class="form-input" v-model="form.until_at" required />
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Status *</label>
          <select class="form-select" v-model="form.status" required>
            <option value="comming_soon">Akan Datang</option>
            <option value="in_progress">Sedang Berlangsung</option>
            <option value="schedule_change">Perubahan Jadwal</option>
            <option value="completed">Selesai</option>
            <option value="cancelled">Dibatalkan</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Deskripsi</label>
          <textarea
            class="form-textarea"
            v-model="form.description"
            placeholder="Masukkan deskripsi agenda"
            rows="4"
          ></textarea>
        </div>

        <div class="form-group">
          <label style="display: flex; align-items: center; gap: 10px; cursor: pointer">
            <input
              type="checkbox"
              v-model="form.is_show_to_other"
              style="width: 20px; height: 20px"
            />
            <span>Tampilkan ke pengguna lain</span>
          </label>
        </div>

        <div class="form-actions">
          <router-link to="/my-agenda" class="btn btn-secondary"> Batal </router-link>
          <button type="submit" class="btn btn-primary" :disabled="loading">
            <span v-if="!loading">💾 Simpan</span>
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
import AdminLayout from '@/layouts/AdminLayout.vue'
import { useNotificationStore } from '@/stores/notification'
import myAgendaService from '@/services/myAgendaService'
import { handleError } from '@/utils/helpers'

const router = useRouter()
const notificationStore = useNotificationStore()

const loading = ref(false)
const form = ref({
  title: '',
  start_at: '',
  until_at: '',
  status: 'comming_soon',
  description: '',
  is_show_to_other: false,
})

const handleSubmit = async () => {
  loading.value = true
  try {
    await myAgendaService.create(form.value)
    notificationStore.success('Agenda berhasil ditambahkan')
    router.push('/my-agenda')
  } catch (error) {
    notificationStore.error(handleError(error))
  } finally {
    loading.value = false
  }
}
</script>
