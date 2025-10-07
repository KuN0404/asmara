<template>
  <AdminLayout>
    <div class="page-header">
      <h2 class="page-title">Agenda Saya</h2>
      <router-link to="/my-agenda/create" class="btn btn-primary"> ➕ Tambah Agenda </router-link>
    </div>

    <div class="agenda-table-container">
      <LoadingSpinner v-if="loading" text="Memuat agenda..." />

      <table v-else class="agenda-table">
        <thead>
          <tr>
            <th>Judul</th>
            <th>Tanggal Mulai</th>
            <th>Tanggal Selesai</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="agenda in agendas" :key="agenda.id">
            <td>{{ agenda.title }}</td>
            <td>{{ formatDateTime(agenda.start_at) }}</td>
            <td>{{ formatDateTime(agenda.until_at) }}</td>
            <td>
              <span :class="getStatusBadgeClass(agenda.status)">
                {{ getStatusText(agenda.status) }}
              </span>
            </td>
            <td>
              <div class="action-buttons">
                <router-link :to="`/my-agenda/${agenda.id}/edit`" class="btn-sm btn-edit">
                  ✏️ Ubah
                </router-link>
                <button @click="deleteAgenda(agenda)" class="btn-sm btn-delete">🗑️ Hapus</button>
              </div>
            </td>
          </tr>
          <tr v-if="agendas.length === 0">
            <td colspan="5" class="empty-state">Tidak ada data agenda</td>
          </tr>
        </tbody>
      </table>

      <Pagination v-if="pagination" :pagination="pagination" @change-page="changePage" />
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import Pagination from '@/components/common/Pagination.vue'
import { useNotificationStore } from '@/stores/notification'
import myAgendaService from '@/services/myAgendaService'
import { formatDateTime, getStatusBadgeClass, getStatusText, handleError } from '@/utils/helpers'

const notificationStore = useNotificationStore()

const loading = ref(true)
const agendas = ref([])
const pagination = ref(null)
const filters = ref({
  page: 1,
  per_page: 15,
})

const loadAgendas = async () => {
  loading.value = true
  try {
    const response = await myAgendaService.getAll(filters.value)
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
    await myAgendaService.delete(agenda.id)
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
