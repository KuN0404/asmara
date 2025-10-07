<template>
  <AdminLayout>
    <div v-if="loading">
      <LoadingSpinner text="Memuat dashboard..." />
    </div>

    <div v-else>
      <!-- Stats Grid -->
      <div class="stats-grid">
        <div class="stat-card primary">
          <div class="stat-header">
            <div class="stat-title">Total Agenda Kantor</div>
            <div class="stat-icon">📋</div>
          </div>
          <div class="stat-value">{{ stats.totalOfficeAgendas }}</div>
          <div class="stat-change positive"><span>↗</span> Semua agenda</div>
        </div>

        <div class="stat-card success">
          <div class="stat-header">
            <div class="stat-title">Agenda Hari Ini</div>
            <div class="stat-icon">📅</div>
          </div>
          <div class="stat-value">{{ stats.todayAgendas }}</div>
          <div class="stat-change positive"><span>↗</span> Agenda aktif</div>
        </div>

        <div class="stat-card warning">
          <div class="stat-header">
            <div class="stat-title">Agenda Saya</div>
            <div class="stat-icon">⏰</div>
          </div>
          <div class="stat-value">{{ stats.myAgendas }}</div>
          <div class="stat-change positive"><span>↗</span> Total agenda pribadi</div>
        </div>

        <div class="stat-card danger">
          <div class="stat-header">
            <div class="stat-title">Pengumuman Aktif</div>
            <div class="stat-icon">✅</div>
          </div>
          <div class="stat-value">{{ stats.activeAnnouncements }}</div>
          <div class="stat-change positive"><span>↗</span> Sedang ditampilkan</div>
        </div>
      </div>

      <!-- Dashboard Grid -->
      <div class="dashboard-grid">
        <div class="recent-activities">
          <div class="card-header">
            <h3 class="card-title">Pengumuman Terbaru</h3>
          </div>
          <div class="activity-list">
            <div
              v-for="announcement in recentAnnouncements"
              :key="announcement.id"
              class="activity-item"
            >
              <div class="activity-icon add">📢</div>
              <div class="activity-content">
                <div class="activity-title">{{ announcement.title }}</div>
                <div class="activity-time">{{ formatDateTime(announcement.created_at) }}</div>
              </div>
            </div>
            <div v-if="recentAnnouncements.length === 0" class="empty-state">
              Belum ada pengumuman
            </div>
          </div>
        </div>

        <div class="quick-actions">
          <div class="card-header">
            <h3 class="card-title">Aksi Cepat</h3>
          </div>
          <router-link to="/office-agenda/create" class="action-btn" v-if="canCreateOfficeAgenda">
            <span>➕</span>
            <div>
              <div style="font-weight: 500">Tambah Agenda Kantor</div>
              <div style="font-size: 0.85rem; color: #64748b">Buat agenda kantor baru</div>
            </div>
          </router-link>
          <router-link to="/my-agenda/create" class="action-btn">
            <span>📝</span>
            <div>
              <div style="font-weight: 500">Tambah Agenda Saya</div>
              <div style="font-size: 0.85rem; color: #64748b">Buat agenda pribadi</div>
            </div>
          </router-link>
          <router-link to="/announcements/create" class="action-btn" v-if="canCreateAnnouncement">
            <span>📢</span>
            <div>
              <div style="font-weight: 500">Buat Pengumuman</div>
              <div style="font-size: 0.85rem; color: #64748b">Tambah pengumuman baru</div>
            </div>
          </router-link>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import { useAuthStore } from '@/stores/auth'
import officeAgendaService from '@/services/officeAgendaService'
import myAgendaService from '@/services/myAgendaService'
import announcementService from '@/services/announcementService'
import { formatDateTime } from '@/utils/helpers'

const authStore = useAuthStore()
const loading = ref(true)
const stats = ref({
  totalOfficeAgendas: 0,
  todayAgendas: 0,
  myAgendas: 0,
  activeAnnouncements: 0,
})
const recentAnnouncements = ref([])

const canCreateOfficeAgenda = computed(
  () => authStore.hasRole('super_admin') || authStore.hasRole('admin'),
)

const canCreateAnnouncement = computed(
  () => authStore.hasRole('super_admin') || authStore.hasRole('admin'),
)

const loadDashboardData = async () => {
  try {
    const [officeAgendas, myAgendas, announcements] = await Promise.all([
      officeAgendaService.getAll({ per_page: 100 }),
      myAgendaService.getAll({ per_page: 100 }),
      announcementService.getAll({ per_page: 5, is_displayed: 1 }),
    ])

    stats.value.totalOfficeAgendas = officeAgendas.total || 0
    stats.value.myAgendas = myAgendas.total || 0
    stats.value.activeAnnouncements = announcements.total || 0

    const today = new Date().toISOString().split('T')[0]
    stats.value.todayAgendas = officeAgendas.data.filter((a) => a.start_at.startsWith(today)).length

    recentAnnouncements.value = announcements.data || []
  } catch (error) {
    console.error('Error loading dashboard:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadDashboardData()
})
</script>
