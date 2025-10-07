<template>
  <AdminLayout>
    <div class="page-header">
      <h2 class="page-title">Agenda Kantor</h2>
      <router-link to="/office-agenda/create" class="btn btn-primary" v-if="canCreate">
        ➕ Tambah Agenda
      </router-link>
    </div>

    <div class="filter-section">
      <div class="form-group">
        <select v-model="filters.status" @change="loadAgendas" class="form-select">
          <option value="">Semua Status</option>
          <option value="comming_soon">Akan Datang</option>
          <option value="in_progress">Sedang Berlangsung</option>
          <option value="schedule_change">Perubahan Jadwal</option>
          <option value="completed">Selesai</option>
          <option value="cancelled">Dibatalkan</option>
        </select>
      </div>
      <div class="form-group">
        <select v-model="filters.agenda_type" @change="loadAgendas" class="form-select">
          <option value="">Semua Tipe</option>
          <option value="rapat">Rapat</option>
          <option value="presentasi">Presentasi</option>
          <option value="pelatihan">Pelatihan</option>
          <option value="kunjungan">Kunjungan</option>
          <option value="lainnya">Lainnya</option>
        </select>
      </div>
    </div>

    <div class="agenda-table-container">
      <LoadingSpinner v-if="loading" text="Memuat agenda..." />

      <table v-else class="agenda-table">
        <thead>
          <tr>
            <th>Judul</th>
            <th>Tanggal Mulai</th>
            <th>Tanggal Selesai</th>
            <th>Lokasi</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="agenda in agendas" :key="agenda.id">
            <td>{{ agenda.title }}</td>
            <td>{{ formatDateTime(agenda.start_at) }}</td>
            <td>{{ formatDateTime(agenda.until_at) }}</td>
            <td>{{ agenda.location }}</td>
            <td>
              <span :class="getStatusBadgeClass(agenda.status)">
                {{ getStatusText(agenda.status) }}
              </span>
            </td>
            <td>
              <div class="action-buttons">
                <router-link :to="`/office-agenda/${agenda.id}`" class="btn-sm btn-info">
                  👁️ Detail
                </router-link>
                <router-link
                  :to="`/office-agenda/${agenda.id}/edit`"
                  class="btn-sm btn-edit"
                  v-if="canEdit"
                >
                  ✏️ Ubah
                </router-link>
                <button @click="deleteAgenda(agenda)" class="btn-sm btn-delete" v-if="canDelete">
                  🗑️ Hapus
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="agendas.length === 0">
            <td colspan="6" class="empty-state">Tidak ada data agenda</td>
          </tr>
        </tbody>
      </table>

      <Pagination v-if="pagination" :pagination="pagination" @change-page="changePage" />
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import Pagination from '@/components/common/Pagination.vue'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import officeAgendaService from '@/services/officeAgendaService'
import { formatDateTime, getStatusBadgeClass, getStatusText, handleError } from '@/utils/helpers'

const authStore = useAuthStore()
const notificationStore = useNotificationStore()

const loading = ref(true)
const agendas = ref([])
const pagination = ref(null)
const filters = ref({
  status: '',
  agenda_type: '',
  page: 1,
  per_page: 15,
})

const canCreate = computed(() => authStore.hasRole('super_admin') || authStore.hasRole('admin'))
const canEdit = computed(() => canCreate.value)
const canDelete = computed(() => canCreate.value)

const loadAgendas = async () => {
  loading.value = true
  try {
    const response = await officeAgendaService.getAll(filters.value)
    agendas.value = response.data
    pagination.value = {
      current_page: response.current_page,
      last_page: response.last_page,
      from: response.from,
      to: response.to,
      total: response.total,
    }
  } catch (error) {
    notificationStore.error(handleError(error))
  } finally {
    loading.value = false
  }
}

const changePage = (page) => {
  filters.value.page = page
  loadAgendas()
}

const deleteAgenda = async (agenda) => {
  if (!confirm(`Apakah Anda yakin ingin menghapus agenda "${agenda.title}"?`)) return

  try {
    await officeAgendaService.delete(agenda.id)
    notificationStore.success('Agenda berhasil dihapus')
    loadAgendas()
  } catch (error) {
    notificationStore.error(handleError(error))
  }
}

onMounted(() => {
  loadAgendas()
})
</script>
