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
            <div class="stat-title">Total Agenda Kantor yang akan datang</div>
            <div class="stat-icon">📋</div>
          </div>
          <div class="stat-value">{{ stats.totalOfficeAgendas }}</div>
          <div class="stat-change positive"><span>↗</span> Semua agenda</div>
        </div>

        <div class="stat-card success">
          <div class="stat-header">
            <div class="stat-title">Agenda kantor Hari Ini</div>
            <div class="stat-icon">📅</div>
          </div>
          <div class="stat-value">{{ stats.todayAgendas }}</div>
          <div class="stat-change positive"><span>↗</span> Agenda aktif</div>
        </div>

        <div class="stat-card warning">
          <div class="stat-header">
            <div class="stat-title">Total Agenda Saya yang akan datang</div>
            <div class="stat-icon">⏰</div>
          </div>
          <div class="stat-value">{{ stats.myAgendas }}</div>
          <div class="stat-change positive"><span>↗</span> Total agenda pribadi</div>
        </div>

        <div class="stat-card danger">
          <div class="stat-header">
            <div class="stat-title">Agenda Saya Hari Ini</div>
            <div class="stat-icon">✅</div>
          </div>
          <div class="stat-value">{{ stats.myTodayAgendas }}</div>
          <div class="stat-change positive"><span>↗</span> Agenda pribadi hari ini</div>
        </div>
      </div>

      <!-- Dashboard Grid -->
      <div class="dashboard-grid">
        <div class="recent-activities">
          <div class="card-header">
            <h3 class="card-title">Pengumuman Terbaru</h3>
          </div>
          <div class="activity-list">
            <router-link
              v-for="announcement in recentAnnouncements"
              :key="announcement.id"
              :to="`/announcements/${announcement.id}`"
              class="activity-item-link"
            >
              <div class="activity-item">
                <div class="activity-icon add">📢</div>
                <div class="activity-content">
                  <div class="activity-title">{{ announcement.title }}</div>
                  <div class="activity-time">{{ formatDateTime(announcement.created_at) }}</div>
                </div>
              </div>
            </router-link>
            <div v-if="recentAnnouncements.length === 0" class="empty-state">
              Belum ada pengumuman
            </div>
          </div>
        </div>

        <div class="quick-actions">
          <div class="card-header">
            <h3 class="card-title">Aksi Cepat</h3>
          </div>
          <router-link to="/office-agenda" class="action-btn" v-if="canCreateOfficeAgenda">
            <span>➕</span>
            <div>
              <div style="font-weight: 500">Tambah Agenda Kantor</div>
              <div style="font-size: 0.85rem; color: #64748b">Buat agenda kantor baru</div>
            </div>
          </router-link>
          <router-link to="/my-agenda" class="action-btn">
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
  myTodayAgendas: 0,
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
    const today = new Date().toISOString().split('T')[0]
    const currentUserId = authStore.user?.id

    // Load data
    const [officeAgendasResponse, myAgendasResponse, announcementsResponse] = await Promise.all([
      officeAgendaService.getAll({ per_page: 1000 }),
      myAgendaService.getAll({ per_page: 1000 }),
      announcementService.getAll({ per_page: 5, sort_by: 'created_at', sort_order: 'desc' }),
    ])

    const officeAgendas = officeAgendasResponse.data || []
    const myAgendas = myAgendasResponse.data || []

    // Status yang tidak ditampilkan
    const excludedStatuses = ['cancelled', 'completed', 'schedule_change']

    // ========== AGENDA KANTOR ==========
    // Filter agenda kantor dimana user diajak sebagai participant
    const myOfficeAgendas = officeAgendas.filter((agenda) => {
      // Cek apakah user ada di user_participants
      const isUserParticipant = agenda.user_participants?.some(
        (participant) => participant.id === currentUserId,
      )
      return isUserParticipant && !excludedStatuses.includes(agenda.status)
    })

    // Agenda Kantor Yang Akan Datang (status: comming_soon)
    stats.value.totalOfficeAgendas = myOfficeAgendas.filter(
      (a) => a.status === 'comming_soon',
    ).length

    // Agenda Kantor Hari Ini (HANYA tanggal hari ini DAN status in_progress)
    stats.value.todayAgendas = myOfficeAgendas.filter((a) => {
      const agendaDate = a.start_at?.split('T')[0] || a.start_at?.split(' ')[0]
      const isToday = agendaDate === today
      const isInProgress = a.status === 'in_progress'

      // Harus hari ini DAN sedang berlangsung
      return isToday && isInProgress
    }).length

    // ========== AGENDA SAYA (MY AGENDA) ==========
    // Filter agenda pribadi yang dibuat oleh user yang sedang login
    const activeMyAgendas = myAgendas.filter(
      (a) => a.created_by === currentUserId && !excludedStatuses.includes(a.status),
    )

    // Agenda Saya Yang Akan Datang (status: comming_soon)
    stats.value.myAgendas = activeMyAgendas.filter((a) => a.status === 'comming_soon').length

    // Agenda Saya Hari Ini (HANYA tanggal hari ini DAN status in_progress)
    stats.value.myTodayAgendas = activeMyAgendas.filter((a) => {
      const agendaDate = a.start_at?.split('T')[0] || a.start_at?.split(' ')[0]
      const isToday = agendaDate === today
      const isInProgress = a.status === 'in_progress'

      // Harus hari ini DAN sedang berlangsung
      return isToday && isInProgress
    }).length

    // ========== PENGUMUMAN ==========
    let announcements = announcementsResponse.data || []
    announcements.sort((a, b) => {
      const dateA = new Date(a.created_at)
      const dateB = new Date(b.created_at)
      return dateB - dateA
    })
    recentAnnouncements.value = announcements.slice(0, 4)
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

<style scoped>
.activity-item-link {
  text-decoration: none;
  color: inherit;
  display: block;
  transition: all 0.2s ease;
}

.activity-item-link:hover {
  background-color: #f8fafc;
  border-radius: 8px;
}

.activity-item-link:hover .activity-item {
  transform: translateX(4px);
}

.activity-item {
  transition: transform 0.2s ease;
}
</style>
