<template>
  <AdminLayout>
    <div class="page-header">
      <h2 class="page-title">Tambah Agenda Kantor</h2>
      <router-link to="/office-agenda" class="btn btn-secondary">← Kembali</router-link>
    </div>

    <div class="form-container">
      <form @submit.prevent="handleSubmit">
        <div class="form-row">
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

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Tipe Agenda *</label>
            <select class="form-select" v-model="form.agenda_type" required>
              <option value="">Pilih Tipe</option>
              <option value="rapat">Rapat</option>
              <option value="presentasi">Presentasi</option>
              <option value="pelatihan">Pelatihan</option>
              <option value="kunjungan">Kunjungan</option>
              <option value="lainnya">Lainnya</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Tipe Aktivitas *</label>
            <select class="form-select" v-model="form.activity_type" required>
              <option value="">Pilih Tipe</option>
              <option value="internal">Internal</option>
              <option value="eksternal">Eksternal</option>
              <option value="hybrid">Hybrid</option>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Lokasi *</label>
            <input
              type="text"
              class="form-input"
              v-model="form.location"
              placeholder="Masukkan lokasi"
              required
            />
          </div>
          <div class="form-group">
            <label class="form-label">Ruangan</label>
            <select class="form-select" v-model="form.room_id">
              <option value="">Pilih Ruangan</option>
              <option v-for="room in rooms" :key="room.id" :value="room.id">
                {{ room.name }} - {{ room.location }}
              </option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Link Meeting</label>
          <input
            type="url"
            class="form-input"
            v-model="form.metting_link"
            placeholder="https://meet.google.com/..."
          />
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
          <label class="form-label">Lampiran</label>
          <input
            type="file"
            class="form-input"
            @change="handleFileChange"
            multiple
            accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
          />
          <small class="form-hint">Max 10MB per file</small>
        </div>

        <div class="form-actions">
          <router-link to="/office-agenda" class="btn btn-secondary"> Batal </router-link>
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
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { useNotificationStore } from '@/stores/notification'
import officeAgendaService from '@/services/officeAgendaService'
import roomService from '@/services/roomService'
import { handleError } from '@/utils/helpers'

const router = useRouter()
const notificationStore = useNotificationStore()

const loading = ref(false)
const rooms = ref([])
const form = ref({
  title: '',
  start_at: '',
  until_at: '',
  agenda_type: '',
  activity_type: '',
  location: '',
  room_id: '',
  metting_link: '',
  status: 'comming_soon',
  description: '',
  attachments: [],
})

const handleFileChange = (e) => {
  form.value.attachments = Array.from(e.target.files)
}

const handleSubmit = async () => {
  loading.value = true
  try {
    await officeAgendaService.create(form.value)
    notificationStore.success('Agenda berhasil ditambahkan')
    router.push('/office-agenda')
  } catch (error) {
    notificationStore.error(handleError(error))
  } finally {
    loading.value = false
  }
}

const loadRooms = async () => {
  try {
    const response = await roomService.getAll({ is_available: 1 })
    rooms.value = response.data
  } catch (error) {
    console.error('Error loading rooms:', error)
  }
}

onMounted(() => {
  loadRooms()
})
</script>
