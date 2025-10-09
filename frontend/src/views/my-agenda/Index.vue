<template>
  <AdminLayout>
    <div class="page-header">
      <h2 class="page-title">Agenda Saya</h2>
      <button @click="openCreateModal" class="btn btn-primary">➕ Tambah Agenda</button>
    </div>

    <div class="calendar-container">
      <div class="calendar-header">
        <div class="calendar-nav">
          <button class="nav-btn" @click="previousMonth">‹</button>
          <h3 class="calendar-title">{{ monthNames[currentMonth] }} {{ currentYear }}</h3>
          <button class="nav-btn" @click="nextMonth">›</button>
        </div>
        <button class="nav-btn" @click="goToToday">Hari Ini</button>
      </div>

      <LoadingSpinner v-if="loading" text="Memuat agenda..." />

      <div v-else class="calendar-grid">
        <div class="weekdays">
          <div class="weekday" v-for="day in weekdays" :key="day">{{ day }}</div>
        </div>

        <div class="days-grid">
          <div
            v-for="day in calendarDays"
            :key="day.date"
            class="day-cell"
            :class="{
              'other-month': !day.isCurrentMonth,
              today: day.isToday,
              'has-events': day.agendas.length > 0,
            }"
            @click="selectDate(day)"
          >
            <div class="day-number">{{ day.day }}</div>
            <div class="day-events">
              <div
                v-for="agenda in day.agendas.slice(0, 3)"
                :key="agenda.id"
                class="event-item"
                :class="`event-${agenda.status}`"
                @click.stop="viewAgenda(agenda)"
              >
                <span class="event-time">{{ formatTime(agenda.start_at) }}</span>
                <span class="event-title">{{ agenda.title }}</span>
              </div>
              <div v-if="day.agendas.length > 3" class="event-more">
                +{{ day.agendas.length - 3 }} lainnya
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Modal v-model="showFormModal" :title="modalTitle" width="600px">
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
            rows="3"
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
          <button type="button" class="btn btn-secondary" @click="closeFormModal">Batal</button>
          <button type="submit" class="btn btn-primary" :disabled="submitting">
            <span v-if="!submitting">💾 Simpan</span>
            <span v-else>Menyimpan...</span>
          </button>
        </div>
      </form>
    </Modal>

    <!-- Detail Modal -->
    <Modal v-model="showDetailModal" title="Detail Agenda" width="600px">
      <div v-if="selectedAgenda" class="detail-container">
        <div class="detail-item">
          <label class="detail-label">Judul:</label>
          <div class="detail-value">{{ selectedAgenda.title }}</div>
        </div>
        <div class="detail-item">
          <label class="detail-label">Tanggal Mulai:</label>
          <div class="detail-value">{{ formatDateTime(selectedAgenda.start_at) }}</div>
        </div>
        <div class="detail-item">
          <label class="detail-label">Tanggal Selesai:</label>
          <div class="detail-value">{{ formatDateTime(selectedAgenda.until_at) }}</div>
        </div>
        <div class="detail-item">
          <label class="detail-label">Tipe Agenda:</label>
          <div class="detail-value">{{ selectedAgenda.agenda_type }}</div>
        </div>
        <div class="detail-item">
          <label class="detail-label">Tipe Aktivitas:</label>
          <div class="detail-value">{{ selectedAgenda.activity_type }}</div>
        </div>
        <div class="detail-item">
          <label class="detail-label">Lokasi:</label>
          <div class="detail-value">{{ selectedAgenda.location }}</div>
        </div>
        <div class="detail-item" v-if="selectedAgenda.room">
          <label class="detail-label">Ruangan:</label>
          <div class="detail-value">
            {{ selectedAgenda.room.name }} - {{ selectedAgenda.room.location }}
          </div>
        </div>
        <div class="detail-item" v-if="selectedAgenda.metting_link">
          <label class="detail-label">Link Meeting:</label>
          <div class="detail-value">
            <a :href="selectedAgenda.metting_link" target="_blank">{{
              selectedAgenda.metting_link
            }}</a>
          </div>
        </div>
        <div class="detail-item">
          <label class="detail-label">Status:</label>
          <div class="detail-value">
            <span :class="getStatusBadgeClass(selectedAgenda.status)">
              {{ getStatusText(selectedAgenda.status) }}
            </span>
          </div>
        </div>
        <div class="detail-item" v-if="selectedAgenda.description">
          <label class="detail-label">Deskripsi:</label>
          <div class="detail-value">{{ selectedAgenda.description }}</div>
        </div>
        <div class="detail-item">
          <label class="detail-label">Tampilkan ke Pengguna Lain:</label>
          <div class="detail-value">{{ selectedAgenda.is_show_to_other ? 'Ya' : 'Tidak' }}</div>
        </div>
      </div>

      <div class="form-actions">
        <button @click="deleteAgenda(selectedAgenda)" class="btn btn-delete">🗑️ Hapus</button>
        <button @click="editAgenda(selectedAgenda)" class="btn btn-primary">✏️ Ubah</button>
      </div>
    </Modal>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import Modal from '@/components/common/Modal.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import myAgendaService from '@/services/myAgendaService'
import { formatDateTime, getStatusBadgeClass, getStatusText, handleError } from '@/utils/helpers'

const authStore = useAuthStore()
const notificationStore = useNotificationStore()

const loading = ref(true)
const submitting = ref(false)
const agendas = ref([])

const currentYear = ref(new Date().getFullYear())
const currentMonth = ref(new Date().getMonth())
const monthNames = [
  'Januari',
  'Februari',
  'Maret',
  'April',
  'Mei',
  'Juni',
  'Juli',
  'Agustus',
  'September',
  'Oktober',
  'November',
  'Desember',
]
const weekdays = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']

const showFormModal = ref(false)
const showDetailModal = ref(false)
const modalTitle = ref('Tambah Agenda')
const selectedAgenda = ref(null)
const editMode = ref(false)

const form = ref({
  title: '',
  start_at: '',
  until_at: '',
  status: 'comming_soon',
  description: '',
  is_show_to_other: false,
})

const calendarDays = computed(() => {
  const firstDay = new Date(currentYear.value, currentMonth.value, 1)
  const lastDay = new Date(currentYear.value, currentMonth.value + 1, 0)
  const prevLastDay = new Date(currentYear.value, currentMonth.value, 0)

  const firstDayOfWeek = firstDay.getDay()
  const lastDateOfMonth = lastDay.getDate()
  const prevLastDate = prevLastDay.getDate()

  const days = []

  // Previous month days
  for (let i = firstDayOfWeek - 1; i >= 0; i--) {
    const date = new Date(currentYear.value, currentMonth.value - 1, prevLastDate - i)
    days.push(createDayObject(date, false))
  }

  // Current month days
  for (let i = 1; i <= lastDateOfMonth; i++) {
    const date = new Date(currentYear.value, currentMonth.value, i)
    days.push(createDayObject(date, true))
  }

  // Next month days
  const remainingDays = 42 - days.length
  for (let i = 1; i <= remainingDays; i++) {
    const date = new Date(currentYear.value, currentMonth.value + 1, i)
    days.push(createDayObject(date, false))
  }

  return days
})

const createDayObject = (date, isCurrentMonth) => {
  const today = new Date()
  const dateStr = date.toISOString().split('T')[0]

  return {
    date: dateStr,
    day: date.getDate(),
    isCurrentMonth,
    isToday: dateStr === today.toISOString().split('T')[0],
    agendas: agendas.value.filter((a) => a.start_at.startsWith(dateStr)),
  }
}

const formatTime = (datetime) => {
  if (!datetime) return ''
  const d = new Date(datetime)
  return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`
}

const previousMonth = () => {
  if (currentMonth.value === 0) {
    currentMonth.value = 11
    currentYear.value--
  } else {
    currentMonth.value--
  }
}

const nextMonth = () => {
  if (currentMonth.value === 11) {
    currentMonth.value = 0
    currentYear.value++
  } else {
    currentMonth.value++
  }
}

const goToToday = () => {
  const today = new Date()
  currentYear.value = today.getFullYear()
  currentMonth.value = today.getMonth()
}

const selectDate = (day) => {
  if (day.agendas.length === 0) {
    openCreateModalWithDate(day.date)
  }
}

const openCreateModal = () => {
  editMode.value = false
  modalTitle.value = 'Tambah Agenda'
  resetForm()
  showFormModal.value = true
}

const openCreateModalWithDate = (date) => {
  editMode.value = false
  modalTitle.value = 'Tambah Agenda'
  resetForm()
  form.value.start_at = `${date}T09:00`
  form.value.until_at = `${date}T10:00`
  showFormModal.value = true
}

const viewAgenda = (agenda) => {
  selectedAgenda.value = agenda
  showDetailModal.value = true
}

const editAgenda = (agenda) => {
  showDetailModal.value = false
  editMode.value = true
  modalTitle.value = 'Ubah Agenda'
  selectedAgenda.value = agenda

  form.value = {
    title: agenda.title,
    start_at: agenda.start_at.substring(0, 16),
    until_at: agenda.until_at.substring(0, 16),
    status: agenda.status,
    description: agenda.description || '',
    is_show_to_other: agenda.is_show_to_other || false,
  }

  showFormModal.value = true
}

const closeFormModal = () => {
  showFormModal.value = false
  resetForm()
}

const resetForm = () => {
  form.value = {
    title: '',
    start_at: '',
    until_at: '',
    status: 'comming_soon',
    description: '',
    is_show_to_other: false,
  }
  selectedAgenda.value = null
}

const handleSubmit = async () => {
  submitting.value = true
  try {
    if (editMode.value) {
      await myAgendaService.update(selectedAgenda.value.id, form.value)
      notificationStore.success('Agenda berhasil diperbarui')
    } else {
      await myAgendaService.create(form.value)
      notificationStore.success('Agenda berhasil ditambahkan')
    }
    closeFormModal()
    loadAgendas()
  } catch (error) {
    notificationStore.error(handleError(error))
  } finally {
    submitting.value = false
  }
}

const deleteAgenda = async (agenda) => {
  if (!confirm(`Apakah Anda yakin ingin menghapus agenda "${agenda.title}"?`)) return

  try {
    await myAgendaService.delete(agenda.id)
    notificationStore.success('Agenda berhasil dihapus')
    showDetailModal.value = false
    loadAgendas()
  } catch (error) {
    notificationStore.error(handleError(error))
  }
}

const loadAgendas = async () => {
  loading.value = true
  try {
    const response = await myAgendaService.getAll({ per_page: 1000 })
    agendas.value = response.data
  } catch (error) {
    notificationStore.error(handleError(error))
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadAgendas()
})
</script>

<style scoped>
/* Sama dengan style office-agenda */
.calendar-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.calendar-header {
  background: #1e40af;
  color: white;
  padding: 20px 30px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.calendar-nav {
  display: flex;
  align-items: center;
  gap: 20px;
}

.nav-btn {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.2s;
  font-size: 1rem;
}

.nav-btn:hover {
  background: rgba(255, 255, 255, 0.3);
}

.calendar-title {
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0;
}

.calendar-grid {
  padding: 20px;
}

.weekdays {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 1px;
  margin-bottom: 10px;
}

.weekday {
  padding: 12px;
  text-align: center;
  font-weight: 600;
  color: #64748b;
  background: #f1f5f9;
  font-size: 0.9rem;
}

.days-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 1px;
  background: #e2e8f0;
}

.day-cell {
  background: white;
  min-height: 120px;
  padding: 8px;
  cursor: pointer;
  transition: background-color 0.2s;
  position: relative;
  overflow: hidden;
}

.day-cell:hover {
  background: #f8fafc;
}

.day-cell.other-month {
  background: #f9fafb;
}

.day-cell.other-month .day-number {
  color: #cbd5e1;
}

.day-cell.today {
  background: #dbeafe;
}

.day-cell.today .day-number {
  color: #1e40af;
  font-weight: 700;
}

.day-number {
  font-weight: 600;
  margin-bottom: 5px;
  font-size: 0.9rem;
}

.day-events {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.event-item {
  padding: 3px 6px;
  border-radius: 3px;
  font-size: 0.75rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  gap: 4px;
  align-items: center;
  border-left: 3px solid;
}

.event-item:hover {
  transform: translateX(2px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.event-comming_soon {
  background: #dbeafe;
  border-left-color: #1e40af;
  color: #1e40af;
}

.event-in_progress {
  background: #dcfce7;
  border-left-color: #059669;
  color: #059669;
}

.event-completed {
  background: #f3f4f6;
  border-left-color: #6b7280;
  color: #6b7280;
}

.event-cancelled {
  background: #fee2e2;
  border-left-color: #dc2626;
  color: #dc2626;
}

.event-time {
  font-weight: 600;
  white-space: nowrap;
}

.event-title {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.event-more {
  font-size: 0.7rem;
  color: #64748b;
  padding: 2px 6px;
  text-align: center;
}

.detail-container {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.detail-label {
  font-weight: 600;
  color: #64748b;
  font-size: 0.85rem;
}

.detail-value {
  color: #1e293b;
}

.btn-delete {
  background: #ef4444;
  color: white;
}

.btn-delete:hover {
  background: #dc2626;
}

@media (max-width: 768px) {
  .calendar-header {
    padding: 15px 20px;
  }

  .calendar-grid {
    padding: 10px;
  }

  .day-cell {
    min-height: 80px;
    padding: 5px;
  }

  .event-item {
    font-size: 0.7rem;
    padding: 2px 4px;
  }

  .event-time {
    display: none;
  }
}
</style>
