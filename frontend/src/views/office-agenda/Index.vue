<template>
  <AdminLayout>
    <div class="page-header">
      <h2 class="page-title">Office Agenda</h2>
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

      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p>Memuat agenda...</p>
      </div>

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
                :title="`${formatTime(agenda.start_at)} - ${agenda.title}`"
              >
                <span class="event-time">{{ formatTime(agenda.start_at) }}</span>
                <span class="event-title">{{ truncateTitle(agenda.title, 15) }}</span>
                <span class="event-type">{{ getAgendaTypeIcon(agenda.agenda_type) }}</span>
              </div>

              <div v-if="day.agendas.length > 3" class="event-more">
                +{{ day.agendas.length - 3 }} lainnya
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Agenda List Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showAgendaListModal" class="modal-overlay" @click="closeAgendaListModal">
          <div class="modal-container modal-agenda-list" @click.stop>
            <div class="modal-header">
              <h3>Agenda - {{ formatDateDisplay(selectedDate) }}</h3>
              <button class="modal-close" @click="closeAgendaListModal">✕</button>
            </div>
            <div class="modal-body">
              <div class="agenda-list">
                <div
                  v-for="agenda in selectedDateAgendas"
                  :key="agenda.id"
                  class="agenda-list-item"
                  :class="`agenda-${agenda.status}`"
                  @click="viewAgendaFromList(agenda)"
                >
                  <div class="agenda-list-header">
                    <span class="agenda-list-time">{{ formatTime(agenda.start_at) }}</span>
                    <span :class="getStatusClass(agenda.status)">
                      {{ getStatusText(agenda.status) }}
                    </span>
                  </div>
                  <h4 class="agenda-list-title">{{ agenda.title }}</h4>
                  <div class="agenda-list-meta">
                    <span class="meta-item">
                      {{ getAgendaTypeIcon(agenda.agenda_type) }} {{ agenda.agenda_type }}
                    </span>
                    <span class="meta-item">📍 {{ agenda.location }}</span>
                  </div>
                  <p v-if="agenda.description" class="agenda-list-desc">
                    {{ agenda.description }}
                  </p>
                </div>
              </div>
              <div class="form-actions">
                <button @click="addAgendaToSelectedDate" class="btn btn-primary">
                  ➕ Tambah Agenda di Tanggal Ini
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Create/Edit Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showFormModal" class="modal-overlay" @click="closeFormModal">
          <div class="modal-container modal-large" @click.stop>
            <div class="modal-header">
              <h3>{{ modalTitle }}</h3>
              <button class="modal-close" @click="closeFormModal">✕</button>
            </div>
            <div class="modal-body">
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
                    <input
                      type="datetime-local"
                      class="form-input"
                      v-model="form.start_at"
                      required
                    />
                  </div>
                  <div class="form-group">
                    <label class="form-label">Tanggal & Waktu Selesai *</label>
                    <input
                      type="datetime-local"
                      class="form-input"
                      v-model="form.until_at"
                      :min="form.start_at"
                      required
                    />
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group">
                    <label class="form-label">Tipe Agenda *</label>
                    <select class="form-select" v-model="form.agenda_type" required>
                      <option value="">Pilih Tipe</option>
                      <option value="meeting">Meeting</option>
                      <option value="event">Event</option>
                      <option value="training">Training</option>
                      <option value="conference">Conference</option>
                      <option value="other">Lainnya</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label class="form-label">Jenis Aktivitas *</label>
                    <select class="form-select" v-model="form.activity_type" required>
                      <option value="">Pilih Jenis</option>
                      <option value="online">Online</option>
                      <option value="offline">Offline</option>
                      <option value="hybrid">Hybrid</option>
                    </select>
                  </div>
                </div>

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

                <div
                  class="form-group"
                  v-if="form.activity_type === 'offline' || form.activity_type === 'hybrid'"
                >
                  <label class="form-label"
                    >Ruangan {{ form.activity_type === 'offline' ? '*' : '(Opsional)' }}</label
                  >
                  <select
                    class="form-select"
                    v-model="form.room_id"
                    :required="form.activity_type === 'offline'"
                  >
                    <option value="">Pilih Ruangan</option>
                    <option v-for="room in rooms" :key="room.id" :value="room.id">
                      {{ room.name }} - {{ room.capacity }} orang
                    </option>
                  </select>
                </div>

                <div
                  class="form-group"
                  v-if="form.activity_type === 'online' || form.activity_type === 'hybrid'"
                >
                  <label class="form-label"
                    >Link Meeting {{ form.activity_type === 'online' ? '*' : '(Opsional)' }}</label
                  >
                  <input
                    type="url"
                    class="form-input"
                    v-model="form.metting_link"
                    placeholder="https://zoom.us/j/... atau https://meet.google.com/..."
                    :required="form.activity_type === 'online'"
                  />
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

                <!-- Peserta Internal (WAJIB) -->
                <div class="form-group">
                  <div class="participant-header">
                    <label class="form-label">Peserta Internal (Wajib) *</label>
                    <button
                      type="button"
                      @click="selectAllInternalParticipants"
                      class="btn-select-all"
                    >
                      {{ allInternalSelected ? '✓ Semua Dipilih' : '☐ Pilih Semua' }}
                    </button>
                  </div>
                  <div class="participant-grid">
                    <label
                      v-for="user in users"
                      :key="user.id"
                      class="participant-checkbox"
                      :class="{ selected: form.user_participant_ids.includes(user.id) }"
                    >
                      <input type="checkbox" :value="user.id" v-model="form.user_participant_ids" />
                      <div class="participant-info">
                        <span class="participant-name">{{ user.name }}</span>
                        <span class="participant-email">{{ user.email }}</span>
                      </div>
                    </label>
                  </div>
                  <small class="form-hint">Minimal 1 peserta internal harus dipilih</small>
                </div>

                <!-- Peserta Eksternal (OPSIONAL) -->
                <div class="form-group">
                  <label class="form-label">Peserta Eksternal (Opsional)</label>
                  <div class="participant-grid">
                    <label
                      v-for="participant in participants"
                      :key="participant.id"
                      class="participant-checkbox"
                      :class="{ selected: form.participant_ids.includes(participant.id) }"
                    >
                      <input
                        type="checkbox"
                        :value="participant.id"
                        v-model="form.participant_ids"
                      />
                      <div class="participant-info">
                        <span class="participant-name">{{ participant.name }}</span>
                        <span class="participant-email">
                          {{ participant.organization || participant.email }}
                        </span>
                      </div>
                    </label>
                  </div>
                </div>

                <!-- Lampiran Multi File -->
                <div class="form-group">
                  <label class="form-label">Lampiran (Dokumen)</label>
                  <div class="file-upload-area">
                    <input
                      type="file"
                      ref="fileInput"
                      class="file-input-hidden"
                      @change="handleFileUpload"
                      multiple
                      accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png"
                    />
                    <button type="button" @click="$refs.fileInput.click()" class="btn-upload">
                      📎 Pilih File
                    </button>
                    <span class="upload-hint"
                      >Maks 10MB per file. Format: PDF, DOC, XLS, PPT, JPG, PNG</span
                    >
                  </div>

                  <!-- Preview File yang Akan Diupload -->
                  <div
                    v-if="form.attachments && form.attachments.length > 0"
                    class="file-preview-list"
                  >
                    <div
                      v-for="(file, index) in form.attachments"
                      :key="index"
                      class="file-preview-item"
                    >
                      <span class="file-icon">{{ getFileIconByName(file.name) }}</span>
                      <div class="file-info">
                        <span class="file-name">{{ file.name }}</span>
                        <span class="file-size">{{ formatFileSize(file.size) }}</span>
                      </div>
                      <button type="button" @click="removeFile(index)" class="btn-remove-file">
                        ✕
                      </button>
                    </div>
                  </div>

                  <!-- File yang Sudah Ada (Edit Mode) -->
                  <div
                    v-if="editMode && selectedAgenda?.attachments?.length > 0"
                    class="existing-files"
                  >
                    <p class="existing-files-title">Lampiran yang sudah ada:</p>
                    <div class="file-preview-list">
                      <div
                        v-for="attachment in selectedAgenda.attachments"
                        :key="attachment.id"
                        class="file-preview-item existing"
                      >
                        <span class="file-icon">{{ getFileIcon(attachment.file_type) }}</span>
                        <div class="file-info">
                          <a
                            :href="`/storage/${attachment.file_path}`"
                            target="_blank"
                            class="file-name-link"
                          >
                            {{ attachment.file_name }}
                          </a>
                          <span class="file-size">{{ formatFileSize(attachment.file_size) }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="form-actions">
                  <button type="button" class="btn btn-secondary" @click="closeFormModal">
                    Batal
                  </button>
                  <button type="submit" class="btn btn-primary" :disabled="submitting">
                    {{ submitting ? 'Menyimpan...' : '💾 Simpan' }}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Detail Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showDetailModal" class="modal-overlay" @click="closeDetailModal">
          <div class="modal-container modal-large" @click.stop>
            <div class="modal-header">
              <h3>Detail Agenda</h3>
              <button class="modal-close" @click="closeDetailModal">✕</button>
            </div>
            <div class="modal-body">
              <div v-if="selectedAgenda" class="detail-container">
                <div
                  class="status-actions"
                  v-if="canChangeStatus(selectedAgenda) && canEditOrDelete(selectedAgenda)"
                >
                  <button
                    @click="changeStatus(selectedAgenda, 'schedule_change')"
                    class="btn-status btn-status-reschedule"
                  >
                    📅 Ganti Jadwal
                  </button>
                  <button
                    @click="changeStatus(selectedAgenda, 'cancelled')"
                    class="btn-status btn-status-cancel"
                  >
                    ❌ Batalkan
                  </button>
                </div>

                <div class="detail-grid">
                  <div class="detail-item">
                    <label class="detail-label">Judul:</label>
                    <div class="detail-value">{{ selectedAgenda.title }}</div>
                  </div>

                  <div class="detail-item">
                    <label class="detail-label">Status:</label>
                    <div class="detail-value">
                      <span :class="getStatusClass(selectedAgenda.status)">
                        {{ getStatusText(selectedAgenda.status) }}
                      </span>
                    </div>
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
                    <div class="detail-value">
                      {{ getAgendaTypeIcon(selectedAgenda.agenda_type) }}
                      {{ selectedAgenda.agenda_type }}
                    </div>
                  </div>

                  <div class="detail-item">
                    <label class="detail-label">Jenis Aktivitas:</label>
                    <div class="detail-value">{{ selectedAgenda.activity_type }}</div>
                  </div>

                  <div class="detail-item">
                    <label class="detail-label">Lokasi:</label>
                    <div class="detail-value">📍 {{ selectedAgenda.location }}</div>
                  </div>

                  <div class="detail-item" v-if="selectedAgenda.room">
                    <label class="detail-label">Ruangan:</label>
                    <div class="detail-value">{{ selectedAgenda.room.name }}</div>
                  </div>

                  <div class="detail-item" v-if="selectedAgenda.metting_link">
                    <label class="detail-label">Link Meeting:</label>
                    <div class="detail-value">
                      <a :href="selectedAgenda.metting_link" target="_blank" class="link">
                        {{ selectedAgenda.metting_link }}
                      </a>
                    </div>
                  </div>

                  <div class="detail-item full-width" v-if="selectedAgenda.description">
                    <label class="detail-label">Deskripsi:</label>
                    <div class="detail-value">{{ selectedAgenda.description }}</div>
                  </div>

                  <!-- <div
                    class="detail-item full-width"
                    v-if="selectedAgenda.userParticipants?.length > 0"
                  >
                    <label class="detail-label">Peserta Internal:</label>
                    <div class="detail-value">
                      <div class="participant-list">
                        <span
                          v-for="u in selectedAgenda.userParticipants"
                          :key="u.id"
                          class="participant-tag participant-internal"
                        >
                          {{ u.name }} - {{ u.email }}
                        </span>
                      </div>
                    </div>
                  </div> -->

                  <div
                    class="detail-item full-width"
                    v-if="selectedAgenda.user_participants?.length > 0"
                  >
                    <label class="detail-label">Peserta Internal:</label>
                    <div class="detail-value">
                      <div class="participant-list">
                        <span
                          v-for="u in selectedAgenda.user_participants"
                          :key="u.id"
                          class="participant-tag participant-internal"
                        >
                          {{ u.name }} - {{ u.email }}
                        </span>
                      </div>
                    </div>
                  </div>

                  <div
                    class="detail-item full-width"
                    v-if="selectedAgenda.participants?.length > 0"
                  >
                    <label class="detail-label">Peserta Eksternal:</label>
                    <div class="detail-value">
                      <div class="participant-list">
                        <span
                          v-for="p in selectedAgenda.participants"
                          :key="p.id"
                          class="participant-tag participant-external"
                        >
                          {{ p.name }}
                          <small v-if="p.organization">- {{ p.organization }}</small>
                          <small v-else-if="p.email">- {{ p.email }}</small>
                        </span>
                      </div>
                    </div>
                  </div>

                  <div class="detail-item full-width" v-if="selectedAgenda.attachments?.length > 0">
                    <label class="detail-label">Lampiran:</label>
                    <div class="detail-value">
                      <div class="attachment-list">
                        <!-- <a
                          v-for="attachment in selectedAgenda.attachments"
                          :key="attachment.id"
                          :href="`/storage/${attachment.file_path}`"
                          target="_blank"
                          class="attachment-item"
                        >
                          <span class="attachment-icon">{{
                            getFileIcon(attachment.file_type)
                          }}</span>
                          <span class="attachment-name">{{ attachment.file_name }}</span>
                          <span class="attachment-size"
                            >({{ formatFileSize(attachment.file_size) }})</span
                          >
                        </a> -->
                        <a
                          v-for="attachment in selectedAgenda.attachments"
                          :key="attachment.id"
                          :href="`/storage/${attachment.file_path}`"
                          :download="attachment.file_name"
                          class="attachment-item"
                        >
                          <span class="attachment-icon">{{
                            getFileIcon(attachment.file_type)
                          }}</span>
                          <span class="attachment-name">{{ attachment.file_name }}</span>
                          <span class="attachment-size">
                            ({{ formatFileSize(attachment.file_size) }})
                          </span>
                        </a>
                      </div>
                    </div>
                  </div>

                  <div class="detail-item" v-if="selectedAgenda.creator">
                    <label class="detail-label">Dibuat Oleh:</label>
                    <div class="detail-value creator-info">
                      <span class="creator-name">{{ selectedAgenda.creator.name }}</span>
                      <span class="creator-email">{{ selectedAgenda.creator.email }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="form-actions" v-if="selectedAgenda && canEditOrDelete(selectedAgenda)">
                <button @click="editAgenda(selectedAgenda)" class="btn btn-primary">✏️ Ubah</button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import officeAgendaService from '@/services/officeAgendaService'
import api from '@/services/api'

const authStore = useAuthStore()
const notificationStore = useNotificationStore()

const loading = ref(true)
const submitting = ref(false)
const agendas = ref([])
const rooms = ref([])
const participants = ref([])
const users = ref([])
const fileInput = ref(null)

const truncateTitle = (title, maxLength = 15) => {
  if (!title) return ''
  if (title.length <= maxLength) return title
  return title.substring(0, maxLength) + '...'
}

const getAgendaTypeIcon = (type) => {
  const icons = {
    meeting: '👥',
    event: '🎉',
    training: '📚',
    conference: '🎤',
    other: '📋',
  }
  return icons[type] || '📋'
}

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
const showAgendaListModal = ref(false)
const modalTitle = ref('Tambah Agenda')
const selectedAgenda = ref(null)
const selectedDate = ref('')
const selectedDateAgendas = ref([])
const editMode = ref(false)

const form = ref({
  title: '',
  start_at: '',
  until_at: '',
  agenda_type: '',
  activity_type: '',
  metting_link: '',
  location: '',
  room_id: '',
  status: 'comming_soon',
  description: '',
  participant_ids: [],
  user_participant_ids: [],
  attachments: [],
})

// Search filters
const internalSearch = ref('')
const externalSearch = ref('')

// Filtered participants based on search
const filteredInternalUsers = computed(() => {
  if (!internalSearch.value) return users.value
  const search = internalSearch.value.toLowerCase()
  return users.value.filter(
    (u) => u.name.toLowerCase().includes(search) || u.email.toLowerCase().includes(search),
  )
})

const filteredExternalParticipants = computed(() => {
  if (!externalSearch.value) return participants.value
  const search = externalSearch.value.toLowerCase()
  return participants.value.filter(
    (p) =>
      p.name.toLowerCase().includes(search) ||
      (p.email && p.email.toLowerCase().includes(search)) ||
      (p.organization && p.organization.toLowerCase().includes(search)),
  )
})

// Computed untuk cek apakah semua internal participant dipilih
const allInternalSelected = computed(() => {
  return (
    filteredInternalUsers.value.length > 0 &&
    filteredInternalUsers.value.every((u) => form.value.user_participant_ids.includes(u.id))
  )
})

// Function untuk select/deselect semua internal participants
const selectAllInternalParticipants = () => {
  if (allInternalSelected.value) {
    // Unselect all filtered users
    filteredInternalUsers.value.forEach((u) => {
      const index = form.value.user_participant_ids.indexOf(u.id)
      if (index > -1) {
        form.value.user_participant_ids.splice(index, 1)
      }
    })
  } else {
    // Select all filtered users
    filteredInternalUsers.value.forEach((u) => {
      if (!form.value.user_participant_ids.includes(u.id)) {
        form.value.user_participant_ids.push(u.id)
      }
    })
  }
}

const handleFileUpload = (event) => {
  const files = Array.from(event.target.files)
  // const maxSize = 10 * 1024 * 1024 // 10MB
  const maxSize = 2.5 * 1024 * 1024 // 2.5MB

  const validFiles = files.filter((file) => {
    if (file.size > maxSize) {
      notificationStore.error(`File ${file.name} terlalu besar (maks 10MB)`)
      return false
    }
    return true
  })

  form.value.attachments = [...(form.value.attachments || []), ...validFiles]
  // Reset input agar bisa upload file yang sama lagi
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const removeFile = (index) => {
  form.value.attachments.splice(index, 1)
}

const formatFileSize = (bytes) => {
  if (!bytes) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i]
}

const getFileIcon = (mimeType) => {
  if (!mimeType) return '📄'
  if (mimeType.includes('pdf')) return '📕'
  if (mimeType.includes('word') || mimeType.includes('document')) return '📘'
  if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return '📗'
  if (mimeType.includes('powerpoint') || mimeType.includes('presentation')) return '📙'
  if (mimeType.includes('image')) return '🖼️'
  return '📄'
}

const getFileIconByName = (filename) => {
  if (!filename) return '📄'
  const ext = filename.split('.').pop().toLowerCase()
  if (ext === 'pdf') return '📕'
  if (['doc', 'docx'].includes(ext)) return '📘'
  if (['xls', 'xlsx'].includes(ext)) return '📗'
  if (['ppt', 'pptx'].includes(ext)) return '📙'
  if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) return '🖼️'
  return '📄'
}

const calculateStatus = (startDate, endDate) => {
  const now = new Date()
  now.setHours(0, 0, 0, 0)
  const start = new Date(startDate)
  start.setHours(0, 0, 0, 0)
  const end = new Date(endDate)
  end.setHours(23, 59, 59, 999)
  const today = new Date()
  today.setHours(0, 0, 0, 0)

  if (start.getTime() === today.getTime()) return 'in_progress'
  if (now >= start && now <= end) return 'in_progress'
  if (now < start) return 'comming_soon'
  return 'completed'
}

const canChangeStatus = (agenda) => {
  return ['comming_soon', 'in_progress'].includes(agenda.status)
}

const canEditOrDelete = (agenda) => {
  if (!agenda) return false
  const isOwner = agenda.created_by === authStore.user?.id
  const isAdmin = ['super_admin', 'admin'].includes(authStore.user?.role)
  const canModify = !['cancelled', 'completed', 'schedule_change'].includes(agenda.status)
  return (isOwner || isAdmin) && canModify
}

const changeStatus = async (agenda, newStatus) => {
  const confirmMessages = {
    schedule_change: 'Apakah Anda yakin ingin mengganti jadwal agenda ini?',
    cancelled: 'Apakah Anda yakin ingin membatalkan agenda ini?',
  }

  if (!confirm(confirmMessages[newStatus])) return

  try {
    const updateData = { status: newStatus }
    await officeAgendaService.update(agenda.id, updateData)
    notificationStore.success('Status berhasil diubah')
    showDetailModal.value = false
    await loadAgendas()
  } catch (error) {
    notificationStore.error(error.response?.data?.message || 'Terjadi kesalahan')
  }
}

const formatDateLocal = (date) => {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

const formatDateTime = (datetime) => {
  if (!datetime) return ''
  const d = new Date(datetime)
  const date = formatDateLocal(d)
  const time = `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`
  return `${date} ${time}`
}

const formatTime = (datetime) => {
  if (!datetime) return ''
  const d = new Date(datetime)
  return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`
}

const getStatusText = (status) => {
  const statusMap = {
    comming_soon: 'Akan Datang',
    in_progress: 'Sedang Berlangsung',
    schedule_change: 'Perubahan Jadwal',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
  }
  return statusMap[status] || status
}

const getStatusClass = (status) => {
  return `status-badge status-${status}`
}

const calendarDays = computed(() => {
  const firstDay = new Date(currentYear.value, currentMonth.value, 1)
  const lastDay = new Date(currentYear.value, currentMonth.value + 1, 0)
  const prevLastDay = new Date(currentYear.value, currentMonth.value, 0)
  const firstDayOfWeek = firstDay.getDay()
  const lastDateOfMonth = lastDay.getDate()
  const prevLastDate = prevLastDay.getDate()
  const days = []

  for (let i = firstDayOfWeek - 1; i >= 0; i--) {
    const date = new Date(currentYear.value, currentMonth.value - 1, prevLastDate - i)
    days.push(createDayObject(date, false))
  }

  for (let i = 1; i <= lastDateOfMonth; i++) {
    const date = new Date(currentYear.value, currentMonth.value, i)
    days.push(createDayObject(date, true))
  }

  const remainingDays = 42 - days.length
  for (let i = 1; i <= remainingDays; i++) {
    const date = new Date(currentYear.value, currentMonth.value + 1, i)
    days.push(createDayObject(date, false))
  }

  return days
})

const createDayObject = (date, isCurrentMonth) => {
  const today = new Date()
  const dateStr = formatDateLocal(date)

  return {
    date: dateStr,
    day: date.getDate(),
    isCurrentMonth,
    isToday: dateStr === formatDateLocal(today),
    agendas: agendas.value.filter((a) => {
      if (!a.start_at) return false
      const agendaDate = new Date(a.start_at)
      return formatDateLocal(agendaDate) === dateStr
    }),
  }
}

const previousMonth = async () => {
  if (currentMonth.value === 0) {
    currentMonth.value = 11
    currentYear.value--
  } else {
    currentMonth.value--
  }
  await loadAgendas()
}

const nextMonth = async () => {
  if (currentMonth.value === 11) {
    currentMonth.value = 0
    currentYear.value++
  } else {
    currentMonth.value++
  }
  await loadAgendas()
}

const goToToday = async () => {
  const today = new Date()
  currentYear.value = today.getFullYear()
  currentMonth.value = today.getMonth()
  await loadAgendas()
}

const selectDate = (day) => {
  if (day.agendas.length === 0) {
    openCreateModalWithDate(day.date)
  } else {
    selectedDate.value = day.date
    selectedDateAgendas.value = day.agendas
    showAgendaListModal.value = true
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

  const startDate = new Date(agenda.start_at)
  const untilDate = new Date(agenda.until_at)

  form.value = {
    title: agenda.title,
    start_at: startDate.toISOString().substring(0, 16),
    until_at: untilDate.toISOString().substring(0, 16),
    agenda_type: agenda.agenda_type,
    activity_type: agenda.activity_type,
    metting_link: agenda.metting_link || '',
    location: agenda.location,
    room_id: agenda.room_id || '',
    status: agenda.status,
    description: agenda.description || '',
    participant_ids: agenda.participants?.map((p) => p.id) || [],
    user_participant_ids: agenda.userParticipants?.map((u) => u.id) || [],
    attachments: [],
  }

  showFormModal.value = true
}

const closeFormModal = () => {
  showFormModal.value = false
  resetForm()
}

const closeDetailModal = () => {
  showDetailModal.value = false
  selectedAgenda.value = null
}

const closeAgendaListModal = () => {
  showAgendaListModal.value = false
  selectedDate.value = ''
  selectedDateAgendas.value = []
}

const viewAgendaFromList = (agenda) => {
  showAgendaListModal.value = false
  viewAgenda(agenda)
}

const addAgendaToSelectedDate = () => {
  showAgendaListModal.value = false
  openCreateModalWithDate(selectedDate.value)
}

const formatDateDisplay = (dateStr) => {
  if (!dateStr) return ''
  const [year, month, day] = dateStr.split('-')
  const monthName = monthNames[parseInt(month) - 1]
  return `${parseInt(day)} ${monthName} ${year}`
}

const resetForm = () => {
  form.value = {
    title: '',
    start_at: '',
    until_at: '',
    agenda_type: '',
    activity_type: '',
    metting_link: '',
    location: '',
    room_id: '',
    status: 'comming_soon',
    description: '',
    participant_ids: [],
    user_participant_ids: [],
    attachments: [],
  }
  selectedAgenda.value = null
}

const handleSubmit = async () => {
  // Validasi peserta internal wajib
  if (form.value.user_participant_ids.length === 0) {
    notificationStore.error('Minimal 1 peserta internal harus dipilih')
    return
  }

  // Validasi tanggal tidak boleh di masa lalu
  const now = new Date()
  const startDate = new Date(form.value.start_at)

  if (startDate < now && !editMode.value) {
    notificationStore.error('Tanggal dan waktu tidak valid')
    return
  }

  submitting.value = true
  try {
    form.value.status = calculateStatus(form.value.start_at, form.value.until_at)

    // Gunakan FormData untuk upload file
    const formData = new FormData()
    formData.append('title', form.value.title)
    formData.append('start_at', form.value.start_at)
    formData.append('until_at', form.value.until_at)
    formData.append('agenda_type', form.value.agenda_type)
    formData.append('activity_type', form.value.activity_type)
    formData.append('location', form.value.location)
    formData.append('status', form.value.status)

    if (form.value.metting_link) formData.append('metting_link', form.value.metting_link)
    if (form.value.room_id) formData.append('room_id', form.value.room_id)
    if (form.value.description) formData.append('description', form.value.description)

    // Tambahkan participant IDs
    form.value.participant_ids.forEach((id) => {
      formData.append('participant_ids[]', id)
    })

    form.value.user_participant_ids.forEach((id) => {
      formData.append('user_participant_ids[]', id)
    })

    // Tambahkan files
    form.value.attachments.forEach((file) => {
      formData.append('attachments[]', file)
    })

    if (editMode.value) {
      // Untuk update, tambahkan _method PUT
      formData.append('_method', 'PUT')
      await api.post(`/office-agendas/${selectedAgenda.value.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      notificationStore.success('Agenda berhasil diperbarui')
    } else {
      await api.post('/office-agendas', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      notificationStore.success('Agenda berhasil ditambahkan')
    }

    await loadAgendas()
    closeFormModal()
  } catch (error) {
    notificationStore.error(error.response?.data?.message || 'Terjadi kesalahan')
    console.error('Submit error:', error)
  } finally {
    submitting.value = false
  }
}

const loadAgendas = async () => {
  loading.value = true
  try {
    const startDate = new Date(currentYear.value, currentMonth.value - 1, 1)
    const endDate = new Date(currentYear.value, currentMonth.value + 2, 0)

    const response = await officeAgendaService.getAll({ per_page: 500 })

    let agendasData = []
    if (response.data && Array.isArray(response.data)) {
      agendasData = response.data
    } else if (response.data && response.data.data && Array.isArray(response.data.data)) {
      agendasData = response.data.data
    } else if (Array.isArray(response)) {
      agendasData = response
    }

    agendasData = agendasData.filter((agenda) => {
      if (!agenda.start_at) return false
      const agendaDate = new Date(agenda.start_at)
      return agendaDate >= startDate && agendaDate <= endDate
    })

    agendas.value = agendasData
  } catch (error) {
    notificationStore.error('Gagal memuat agenda')
    agendas.value = []
  } finally {
    loading.value = false
  }
}

const loadRooms = async () => {
  try {
    const response = await api.get('/rooms', { params: { per_page: 100 } })
    rooms.value = response.data.data || response.data || []
  } catch (error) {
    console.error('Failed to load rooms:', error)
    rooms.value = []
  }
}

const loadParticipants = async () => {
  try {
    const response = await api.get('/participants', { params: { per_page: 100 } })
    participants.value = response.data.data || response.data || []
  } catch (error) {
    console.error('Failed to load participants:', error)
    participants.value = []
  }
}

const loadUsers = async () => {
  try {
    const response = await api.get('/users', { params: { per_page: 100 } })
    users.value = response.data.data || response.data || []
  } catch (error) {
    console.error('Failed to load users:', error)
    users.value = []
  }
}

onMounted(async () => {
  await Promise.all([loadAgendas(), loadRooms(), loadParticipants(), loadUsers()])
})
</script>

<style scoped>
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.page-title {
  font-size: 1.8rem;
  font-weight: 600;
  color: #1e293b;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary {
  background: #1e40af;
  color: white;
}

.btn-primary:hover {
  background: #1e3a8a;
}

.btn-secondary {
  background: #e5e7eb;
  color: #374151;
}

.btn-secondary:hover {
  background: #d1d5db;
}

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
}

.nav-btn:hover {
  background: rgba(255, 255, 255, 0.3);
}

.calendar-title {
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0;
}

.loading-container {
  padding: 40px;
  text-align: center;
}

.spinner {
  border: 3px solid #f3f4f6;
  border-top: 3px solid #1e40af;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
  margin: 0 auto 10px;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
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
  min-width: 0;
  overflow: hidden;
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
  text-decoration: line-through;
  opacity: 0.8;
}

.event-schedule_change {
  background: #fef3c7;
  border-left-color: #f59e0b;
  color: #f59e0b;
}

.event-time {
  font-weight: 600;
  white-space: nowrap;
  flex-shrink: 0;
}

.event-title {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  flex: 1;
  min-width: 0;
}

.event-type {
  flex-shrink: 0;
}

.event-more {
  font-size: 0.7rem;
  color: #64748b;
  padding: 2px 6px;
  text-align: center;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  padding: 20px;
}

.modal-container {
  background: white;
  border-radius: 12px;
  max-width: 600px;
  width: 100%;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.modal-large {
  max-width: 800px;
}

.modal-agenda-list {
  max-width: 500px;
}

.modal-header {
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: #1e293b;
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #6b7280;
  cursor: pointer;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.modal-close:hover {
  background: #f3f4f6;
}

.modal-body {
  padding: 24px;
  overflow-y: auto;
}

.agenda-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 20px;
}

.agenda-list-item {
  padding: 16px;
  border-radius: 8px;
  border-left: 4px solid;
  cursor: pointer;
  transition: all 0.2s;
  background: #f9fafb;
}

.agenda-list-item:hover {
  transform: translateX(4px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.agenda-comming_soon {
  border-left-color: #1e40af;
  background: #eff6ff;
}

.agenda-in_progress {
  border-left-color: #059669;
  background: #f0fdf4;
}

.agenda-completed {
  border-left-color: #6b7280;
  background: #f9fafb;
}

.agenda-cancelled {
  border-left-color: #dc2626;
  background: #fef2f2;
  opacity: 0.85;
}

.agenda-cancelled .agenda-list-title {
  text-decoration: line-through;
  color: #991b1b;
}

.agenda-schedule_change {
  border-left-color: #f59e0b;
  background: #fffbeb;
}

.agenda-list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.agenda-list-time {
  font-weight: 600;
  color: #374151;
  font-size: 0.9rem;
}

.agenda-list-title {
  margin: 0 0 6px 0;
  font-size: 1.05rem;
  font-weight: 600;
  color: #1e293b;
}

.agenda-list-meta {
  display: flex;
  gap: 12px;
  margin-bottom: 8px;
  flex-wrap: wrap;
}

.meta-item {
  font-size: 0.85rem;
  color: #64748b;
  background: #f1f5f9;
  padding: 4px 8px;
  border-radius: 4px;
}

.agenda-list-desc {
  margin: 0;
  font-size: 0.9rem;
  color: #64748b;
  line-height: 1.4;
}

.form-group {
  margin-bottom: 16px;
}

.form-label {
  display: block;
  margin-bottom: 6px;
  font-weight: 500;
  color: #374151;
  font-size: 0.9rem;
}

.form-input,
.form-select,
.form-textarea {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.95rem;
  transition: border-color 0.2s;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
  outline: none;
  border-color: #1e40af;
}

.form-textarea {
  resize: vertical;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-hint {
  display: block;
  margin-top: 4px;
  font-size: 0.8rem;
  color: #64748b;
}

.form-actions {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid #e5e7eb;
}

/* Participant Header dengan tombol Select All */
.participant-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.btn-select-all {
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  color: #1e40af;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-select-all:hover {
  background: #dbeafe;
  border-color: #93c5fd;
}

/* Participant Grid dengan Checkbox */
.participant-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 10px;
  max-height: 300px;
  overflow-y: auto;
  padding: 12px;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
}

.participant-checkbox {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 10px;
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.participant-checkbox:hover {
  border-color: #bfdbfe;
  background: #f8fafc;
}

.participant-checkbox.selected {
  border-color: #1e40af;
  background: #eff6ff;
}

.participant-checkbox input[type='checkbox'] {
  margin-top: 2px;
  cursor: pointer;
  width: 18px;
  height: 18px;
  flex-shrink: 0;
}

.participant-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
  flex: 1;
  min-width: 0;
}

.participant-name {
  font-weight: 500;
  color: #1e293b;
  font-size: 0.9rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.participant-email {
  font-size: 0.8rem;
  color: #64748b;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* File Upload Area */
.file-upload-area {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.file-input-hidden {
  display: none;
}

.btn-upload {
  background: #eff6ff;
  border: 2px dashed #bfdbfe;
  color: #1e40af;
  padding: 10px 20px;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-upload:hover {
  background: #dbeafe;
  border-color: #93c5fd;
}

.upload-hint {
  font-size: 0.8rem;
  color: #64748b;
}

/* File Preview List */
.file-preview-list {
  margin-top: 12px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.file-preview-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 0.9rem;
  transition: all 0.2s;
}

.file-preview-item:hover {
  background: #e2e8f0;
}

.file-preview-item.existing {
  background: #f0fdf4;
  border-color: #bbf7d0;
}

.file-icon {
  font-size: 1.5rem;
  flex-shrink: 0;
}

.file-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}

.file-name {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-weight: 500;
  color: #1e293b;
}

.file-name-link {
  color: #1e40af;
  text-decoration: none;
  font-weight: 500;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.file-name-link:hover {
  text-decoration: underline;
}

.file-size {
  color: #64748b;
  font-size: 0.8rem;
}

.btn-remove-file {
  background: #fee2e2;
  border: none;
  color: #dc2626;
  padding: 4px 10px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
  transition: background 0.2s;
  flex-shrink: 0;
}

.btn-remove-file:hover {
  background: #fecaca;
}

.existing-files {
  margin-top: 16px;
  padding: 12px;
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 8px;
}

.existing-files-title {
  font-weight: 600;
  color: #166534;
  margin-bottom: 10px;
  font-size: 0.9rem;
}

.status-actions {
  display: flex;
  gap: 12px;
  padding: 16px;
  background: #f8fafc;
  border-radius: 8px;
  margin-bottom: 16px;
}

.btn-status {
  flex: 1;
  padding: 10px 16px;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.9rem;
}

.btn-status-reschedule {
  background: #fef3c7;
  color: #92400e;
  border: 1px solid #fbbf24;
}

.btn-status-reschedule:hover {
  background: #fde68a;
}

.btn-status-cancel {
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #f87171;
}

.btn-status-cancel:hover {
  background: #fecaca;
}

.detail-container {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.detail-item.full-width {
  grid-column: 1 / -1;
}

.detail-label {
  font-weight: 600;
  color: #64748b;
  font-size: 0.85rem;
}

.detail-value {
  color: #1e293b;
  font-size: 0.95rem;
}

.link {
  color: #1e40af;
  text-decoration: none;
  word-break: break-all;
}

.link:hover {
  text-decoration: underline;
}

.participant-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.participant-tag {
  background: #eff6ff;
  color: #1e40af;
  padding: 4px 12px;
  border-radius: 16px;
  font-size: 0.85rem;
  border: 1px solid #bfdbfe;
}

.participant-external {
  background: #fef3c7;
  color: #92400e;
  border-color: #fbbf24;
}

.participant-internal {
  background: #eff6ff;
  color: #1e40af;
  border-color: #bfdbfe;
}

.attachment-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.attachment-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 12px;
  background: #f1f5f9;
  border-radius: 6px;
  text-decoration: none;
  color: #1e293b;
  transition: all 0.2s;
}

.attachment-item:hover {
  background: #e2e8f0;
  transform: translateX(2px);
}

.attachment-icon {
  font-size: 1.2rem;
}

.attachment-name {
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-weight: 500;
}

.attachment-size {
  color: #64748b;
  font-size: 0.8rem;
}

.creator-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.creator-name {
  font-weight: 600;
  color: #1e40af;
}

.creator-email {
  font-size: 0.85rem;
  color: #64748b;
}

.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 500;
}

.status-comming_soon {
  background: #dbeafe;
  color: #1e40af;
}

.status-in_progress {
  background: #dcfce7;
  color: #059669;
}

.status-completed {
  background: #f3f4f6;
  color: #6b7280;
}

.status-cancelled {
  background: #fee2e2;
  color: #dc2626;
  font-weight: 600;
}

.status-schedule_change {
  background: #fef3c7;
  color: #f59e0b;
}

.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .modal-container,
.modal-leave-active .modal-container {
  transition: transform 0.3s ease;
}

.modal-enter-from .modal-container,
.modal-leave-to .modal-container {
  transform: scale(0.95);
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    gap: 12px;
    align-items: stretch;
  }

  .calendar-header {
    padding: 15px 20px;
    flex-direction: column;
    gap: 12px;
  }

  .calendar-nav {
    width: 100%;
    justify-content: center;
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

  .form-row {
    grid-template-columns: 1fr;
  }

  .detail-grid {
    grid-template-columns: 1fr;
  }

  .agenda-list-item {
    padding: 12px;
  }

  .agenda-list-title {
    font-size: 1rem;
  }

  .status-actions {
    flex-direction: column;
  }

  .modal-container {
    max-height: 95vh;
  }

  .modal-header {
    padding: 16px 20px;
  }

  .modal-body {
    padding: 20px;
  }

  .participant-grid {
    grid-template-columns: 1fr;
  }

  .file-upload-area {
    flex-direction: column;
    align-items: stretch;
  }
}
</style>
