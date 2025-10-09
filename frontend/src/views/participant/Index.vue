<template>
  <AdminLayout>
    <!-- Header -->
    <div class="page-header">
      <h2 class="page-title">Partisipan Luar</h2>
      <router-link to="/participants/create" class="btn btn-primary" v-if="canCreate">
        ➕ Tambah Partisipan
      </router-link>
    </div>

    <div class="anouncement-table-container">
      <!-- Loader -->
      <LoadingSpinner v-if="loading" text="Memuat pengumuman..." />

      <!-- Table Card -->
      <div v-else class="table-card">
        <table class="anouncement-table">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama</th>
              <th>Email</th>
              <th>No HP</th>
              <th>Organisasi</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(participant, index) in participants" :key="participant.id">
              <td>{{ (filters.page - 1) * filters.per_page + index + 1 }}</td>
              <td>{{ participant.name }}</td>
              <td>{{ participant.email || '-' }}</td>
              <td>{{ participant.phone || '-' }}</td>
              <td>{{ participant.organization || '-' }}</td>
              <td>
                <div class="action-buttons">
                  <router-link :to="`/participants/${participant.id}`" class="btn-sm btn-info">
                    👁️ Detail
                  </router-link>
                  <router-link
                    :to="`/participants/${participant.id}/edit`"
                    class="btn-sm btn-edit"
                    v-if="canEdit"
                  >
                    ✏️ Ubah
                  </router-link>
                  <button
                    @click="deleteParticipant(participant)"
                    class="btn-sm btn-delete"
                    v-if="canDelete"
                  >
                    🗑️ Hapus
                  </button>
                </div>
              </td>
            </tr>

            <!-- State kosong -->
            <tr v-if="participants.length === 0">
              <td colspan="6" class="empty-state">Tidak ada partisipan luar</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
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
import participantService from '@/services/participantService'
import { handleError } from '@/utils/helpers'

// Store
const authStore = useAuthStore()
const notificationStore = useNotificationStore()

// State
const loading = ref(true)
const participants = ref([])
const pagination = ref(null)
const filters = ref({
  page: 1,
  per_page: 15,
})

// Hak akses
const canCreate = computed(() => authStore.hasRole('super_admin') || authStore.hasRole('admin'))
const canEdit = computed(() => canCreate.value)
const canDelete = computed(() => canCreate.value)

// Ambil data
const loadParticipants = async () => {
  loading.value = true
  try {
    const response = await participantService.getAll(filters.value)
    participants.value = response.data
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
  loadParticipants()
}

const deleteParticipant = async (participant) => {
  if (!confirm(`Apakah Anda yakin ingin menghapus "${participant.name}"?`)) return
  try {
    await participantService.delete(participant.id)
    notificationStore.success('Partisipan berhasil dihapus')
    loadParticipants()
  } catch (error) {
    notificationStore.error(handleError(error))
  }
}

// Lifecycle
onMounted(() => {
  loadParticipants()
})
</script>

<style scoped>
.table-card {
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  margin-top: 1rem;
  transition: all 0.3s ease;
}

.table-card:hover {
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
}

.anouncement-table {
  width: 100%;
  border-collapse: collapse;
}

.anouncement-table thead {
  background-color: #f8f9fa;
}

.anouncement-table th,
.anouncement-table td {
  padding: 14px 16px;
  text-align: left;
}

.anouncement-table th {
  font-weight: 600;
  font-size: 14px;
  color: #555;
}

.anouncement-table tbody tr {
  transition: background 0.2s ease;
}

.anouncement-table tbody tr:hover {
  background-color: #f5f7fa;
}

.anouncement-table tbody td {
  font-size: 14px;
  color: #333;
  border-top: 1px solid #eee;
}

.empty-state {
  text-align: center;
  padding: 20px;
  color: #888;
  font-style: italic;
}

.action-buttons {
  display: flex;
  gap: 8px;
}

.btn-sm {
  padding: 6px 10px;
  border-radius: 6px;
  font-size: 13px;
  cursor: pointer;
  text-decoration: none;
  border: none;
}

.btn-sm.btn-info {
  background: #e3f2fd;
  color: #1976d2;
}

.btn-sm.btn-edit {
  background: #fff3e0;
  color: #f57c00;
}

.btn-sm.btn-delete {
  background: #ffebee;
  color: #d32f2f;
}

.btn-sm:hover {
  opacity: 0.8;
  transition: 0.2s;
}
</style>
