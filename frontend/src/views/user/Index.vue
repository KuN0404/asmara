<template>
  <AdminLayout>
    <!-- Header -->
    <div class="page-header">
      <h2 class="page-title">Pengguna</h2>
      <router-link to="/users/create" class="btn btn-primary" v-if="canCreate">
        ➕ Tambah Pengguna
      </router-link>
    </div>

    <div class="user-table-container">
      <div class="filter-container">
        <label for="status-filter">Status:</label>
        <select id="status-filter" v-model="statusFilter" @change="loadUsers" class="filter-select">
          <option value="active">Aktif</option>
          <option value="inactive">Nonaktif</option>
          <option value="all">Semua</option>
        </select>
      </div>

      <!-- Loader -->
      <LoadingSpinner v-if="loading" text="Memuat pengumuman..." />

      <!-- Table Card -->
      <div v-else class="table-card">
        <table class="user-table">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama</th>
              <th>No Hp</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(user, index) in users" :key="user.id">
              <td>{{ (filters.page - 1) * filters.per_page + index + 1 }}</td>
              <td>{{ user.name }}</td>
              <td>{{ user.whatsapp_number || '-' }}</td>
              <td>
                <span :class="user.deleted_at ? 'badge-inactive' : 'badge-active'">
                  {{ user.deleted_at ? 'Nonaktif' : 'Aktif' }}
                </span>
              </td>
              <td>
                <div class="action-buttons">
                  <router-link :to="`/users/${user.id}`" class="btn-sm btn-info">
                    👁️ Detail
                  </router-link>
                  <router-link
                    :to="`/users/${user.id}/edit`"
                    class="btn-sm btn-edit"
                    v-if="canEdit"
                  >
                    ✏️ Ubah
                  </router-link>
                  <!-- Soft Delete / Restore -->
                  <button
                    v-if="!user.deleted_at && canDelete"
                    @click="deleteUser(user)"
                    class="btn-sm btn-delete"
                  >
                    🗑️ Nonaktifkan
                  </button>
                  <button
                    v-else-if="user.deleted_at && canEdit"
                    @click="restoreUser(user)"
                    class="btn-sm btn-restore"
                  >
                    🔄 Aktifkan
                  </button>
                </div>
              </td>
            </tr>

            <!-- State kosong -->
            <tr v-if="users.length === 0">
              <td colspan="6" class="empty-state">Tidak ada pengguna</td>
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
import userService from '@/services/userService.js'
import { handleError } from '@/utils/helpers'

// Store
const authStore = useAuthStore()
const notificationStore = useNotificationStore()

// State
const loading = ref(true)
const users = ref([])
const statusFilter = ref('active') // default: aktif
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
const loadUsers = async () => {
  loading.value = true
  try {
    const params = { ...filters.value }

    // Kirim filter status ke backend
    if (statusFilter.value === 'active') {
      params.status = 'active'
    } else if (statusFilter.value === 'inactive') {
      params.status = 'inactive'
    } else {
      params.status = 'all'
    }

    const response = await userService.getAll(params)
    users.value = response.data
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
  loadUsers()
}

const deleteUser = async (user) => {
  if (!confirm(`Apakah Anda yakin ingin menghapus "${user.name}"?`)) return
  try {
    await userService.delete(user.id)
    notificationStore.success('Pengguna berhasil dihapus')
    loadUsers()
  } catch (error) {
    notificationStore.error(handleError(error))
  }
}

const restoreUser = async (user) => {
  if (!confirm(`Apakah Anda yakin ingin mengaktifkan kembali "${user.name}"?`)) return
  try {
    await userService.restore(user.id)
    notificationStore.success('Pengguna berhasil diaktifkan kembali')
    loadUsers()
  } catch (error) {
    notificationStore.error(handleError(error))
  }
}

// Lifecycle
onMounted(() => {
  loadUsers()
})
</script>

<style scoped>
.filter-container {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
  background: #fff;
  padding: 16px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.filter-container label {
  font-size: 14px;
  font-weight: 600;
  color: #555;
}

.filter-select {
  padding: 10px 16px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 14px;
  color: #333;
  background-color: #fff;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 150px;
  font-weight: 500;
}

.filter-select:hover {
  border-color: #1976d2;
  background-color: #f8f9fa;
}

.filter-select:focus {
  outline: none;
  border-color: #1976d2;
  box-shadow: 0 0 0 4px rgba(25, 118, 210, 0.1);
}

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

.user-table {
  width: 100%;
  border-collapse: collapse;
}

.user-table thead {
  background-color: #f8f9fa;
}

.user-table th,
.user-table td {
  padding: 14px 16px;
  text-align: left;
}

.user-table th {
  font-weight: 600;
  font-size: 14px;
  color: #555;
}

.user-table tbody tr {
  transition: background 0.2s ease;
}

.user-table tbody tr:hover {
  background-color: #f5f7fa;
}

.user-table tbody td {
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

.btn-sm.btn-restore {
  background: #e8f5e9;
  color: #2e7d32;
}

.btn-sm:hover {
  opacity: 0.8;
  transition: 0.2s;
}

.badge-active {
  background: #e8f5e9;
  color: #2e7d32;
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 500;
  display: inline-block;
  border: 1px solid #a5d6a7;
}

.badge-inactive {
  background: #f5f5f5;
  color: #757575;
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 500;
  display: inline-block;
  border: 1px solid #e0e0e0;
}
</style>
