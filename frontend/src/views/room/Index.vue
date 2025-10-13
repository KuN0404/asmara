<template>
  <AdminLayout>
    <!-- Header -->
    <div class="page-header">
      <h2 class="page-title">Ruangan</h2>
      <router-link to="/rooms/create" class="btn btn-primary" v-if="canCreate">
        ➕ Tambah Ruangan
      </router-link>
    </div>

    <div class="room-table-container">
      <!-- Loader -->
      <LoadingSpinner v-if="loading" text="Memuat pengumuman..." />

      <!-- Table Card -->
      <div v-else class="table-card">
        <table class="room-table">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama</th>
              <th>Lokasi</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(room, index) in rooms" :key="room.id">
              <td>{{ (filters.page - 1) * filters.per_page + index + 1 }}</td>
              <td>{{ room.name }}</td>
              <td>{{ room.location || '-' }}</td>
              <td>
                <span :class="room.is_available ? 'badge-active' : 'badge-inactive'">
                  {{ room.is_available ? 'Tersedia' : 'Tidak Tersedia' }}
                </span>
              </td>
              <td>
                <div class="action-buttons">
                  <router-link :to="`/rooms/${room.id}`" class="btn-sm btn-info">
                    👁️ Detail
                  </router-link>
                  <button
                    @click="toggleAvailability(room)"
                    class="btn-sm"
                    :class="room.is_available ? 'btn-unavailable' : 'btn-available'"
                    v-if="canEdit"
                  >
                    {{ room.is_available ? 'Set Tidak Tersedia' : 'Set Tersedia' }}
                  </button>
                  <router-link
                    :to="`/rooms/${room.id}/edit`"
                    class="btn-sm btn-edit"
                    v-if="canEdit"
                  >
                    ✏️ Ubah
                  </router-link>
                  <button @click="deleteRoom(room)" class="btn-sm btn-delete" v-if="canDelete">
                    🗑️ Hapus
                  </button>
                </div>
              </td>
            </tr>

            <!-- State kosong -->
            <tr v-if="rooms.length === 0">
              <td colspan="6" class="empty-state">Tidak ada ruangan</td>
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
import roomService from '@/services/roomService'
import { handleError } from '@/utils/helpers'

// Store
const authStore = useAuthStore()
const notificationStore = useNotificationStore()

// State
const loading = ref(true)
const rooms = ref([])
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
const loadRooms = async () => {
  loading.value = true
  try {
    const response = await roomService.getAll(filters.value)
    rooms.value = response.data
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
  loadRooms()
}

const toggleAvailability = async (room) => {
  const newStatus = !room.is_available
  const confirmMsg = `Apakah Anda yakin ingin mengubah status "${room.name}" menjadi ${newStatus ? 'Tersedia' : 'Tidak Tersedia'}?`
  if (!confirm(confirmMsg)) return

  try {
    await roomService.update(room.id, { is_available: newStatus })
    notificationStore.success(`Status "${room.name}" berhasil diubah ✅`)
    loadRooms() // refresh data
  } catch (error) {
    notificationStore.error(handleError(error))
  }
}

const deleteRoom = async (room) => {
  if (!confirm(`Apakah Anda yakin ingin menghapus "${room.name}"?`)) return
  try {
    await roomService.delete(room.id)
    notificationStore.success('Ruangan berhasil dihapus')
    loadRooms()
  } catch (error) {
    notificationStore.error(handleError(error))
  }
}

// Lifecycle
onMounted(() => {
  loadRooms()
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

.room-table {
  width: 100%;
  border-collapse: collapse;
}

.room-table thead {
  background-color: #f8f9fa;
}

.room-table th,
.room-table td {
  padding: 14px 16px;
  text-align: left;
}

.room-table th {
  font-weight: 600;
  font-size: 14px;
  color: #555;
}

.room-table tbody tr {
  transition: background 0.2s ease;
}

.room-table tbody tr:hover {
  background-color: #f5f7fa;
}

.room-table tbody td {
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
</style>
