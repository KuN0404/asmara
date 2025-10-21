<template>
  <AdminLayout>
    <div v-if="loading">
      <LoadingSpinner text="Memuat profil..." />
    </div>

    <div v-else class="profile-container">
      <!-- Profile Header -->
      <div class="profile-header">
        <h2 class="page-title">Profil Saya</h2>
        <button @click="showEditModal = true" class="btn-edit">
          <span>✏️</span>
          Edit Profil
        </button>
      </div>

      <!-- Profile Card -->
      <div class="profile-card">
        <div class="profile-photo-section">
          <div class="profile-photo">
            <img v-if="photoUrl" :src="photoUrl" :alt="profile.name" />
            <div v-else class="photo-placeholder">
              {{ profile.name ? profile.name.charAt(0).toUpperCase() : '?' }}
            </div>
          </div>
          <div class="profile-info-summary">
            <h3 class="profile-name">{{ profile.name }}</h3>
            <div class="profile-role">{{ getRoleName(profile.roles?.[0]?.name) }}</div>
          </div>
        </div>

        <div class="profile-details">
          <div class="detail-item">
            <div class="detail-label">Username</div>
            <div class="detail-value">{{ profile.username || '-' }}</div>
          </div>

          <div class="detail-item">
            <div class="detail-label">Email</div>
            <div class="detail-value">{{ profile.email || '-' }}</div>
          </div>

          <div class="detail-item">
            <div class="detail-label">WhatsApp</div>
            <div class="detail-value">{{ profile.whatsapp_number || '-' }}</div>
          </div>

          <div class="detail-item">
            <div class="detail-label">Alamat</div>
            <div class="detail-value">{{ profile.address || '-' }}</div>
          </div>
        </div>
      </div>

      <!-- Edit Modal -->
      <div v-if="showEditModal" class="modal-overlay" @click="closeModal">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h3>Edit Profil</h3>
            <button @click="closeModal" class="modal-close">×</button>
          </div>

          <form @submit.prevent="handleSubmit" class="modal-form">
            <div class="photo-section">
              <div class="photo-preview">
                <img v-if="photoPreview" :src="photoPreview" alt="Preview" />
                <div v-else class="photo-placeholder">
                  {{ form.name ? form.name.charAt(0).toUpperCase() : '?' }}
                </div>
              </div>
              <input
                type="file"
                ref="photoInput"
                @change="handlePhotoChange"
                accept="image/*"
                style="display: none"
              />
              <button type="button" @click="$refs.photoInput.click()" class="btn-upload">
                Ubah Foto
              </button>
            </div>

            <div class="form-group">
              <label>Nama Lengkap <span class="required">*</span></label>
              <input v-model="form.name" type="text" required />
            </div>

            <div class="form-group">
              <label>Email <span class="required">*</span></label>
              <input v-model="form.email" type="email" required />
            </div>

            <div class="form-group">
              <label>WhatsApp</label>
              <input v-model="form.whatsapp_number" type="text" />
            </div>

            <div class="form-group">
              <label>Alamat</label>
              <textarea v-model="form.address" rows="3"></textarea>
            </div>

            <div class="form-divider"></div>

            <h4 class="section-title">Ubah Password (Opsional)</h4>

            <div class="form-group">
              <label>Password Saat Ini</label>
              <input v-model="form.current_password" type="password" />
            </div>

            <div class="form-group">
              <label>Password Baru</label>
              <input v-model="form.new_password" type="password" />
            </div>

            <div class="form-group">
              <label>Konfirmasi Password Baru</label>
              <input v-model="form.new_password_confirmation" type="password" />
            </div>

            <div class="modal-actions">
              <button type="button" @click="closeModal" class="btn-cancel">Batal</button>
              <button type="submit" :disabled="submitting" class="btn-save">
                {{ submitting ? 'Menyimpan...' : 'Simpan Perubahan' }}
              </button>
            </div>
          </form>
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
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'

const authStore = useAuthStore()
const notificationStore = useNotificationStore()
const loading = ref(true)
const submitting = ref(false)
const showEditModal = ref(false)
const photoInput = ref(null)
const photoFile = ref(null)

const profile = ref({
  username: '',
  name: '',
  email: '',
  whatsapp_number: '',
  address: '',
  photo: '',
  roles: [],
})

const form = ref({
  name: '',
  email: '',
  whatsapp_number: '',
  address: '',
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
})

const photoUrl = computed(() => {
  if (!profile.value.photo) return null

  const photo = profile.value.photo

  if (photo.startsWith('http://') || photo.startsWith('https://')) {
    return photo
  }

  const baseURL = import.meta.env.VITE_API_BASE_URL?.replace('/api', '') || 'http://localhost:8000'
  const photoPath = photo.startsWith('storage/') ? photo : `storage/${photo}`

  return `${baseURL}/${photoPath}`
})

const photoPreview = computed(() => {
  if (photoFile.value) {
    return URL.createObjectURL(photoFile.value)
  }
  return photoUrl.value
})

const getRoleName = (role) => {
  const roles = {
    super_admin: 'Super Admin',
    admin: 'Admin',
    staff: 'Staff',
  }
  return roles[role] || role || '-'
}

const handlePhotoChange = (e) => {
  const file = e.target.files[0]
  if (file) {
    if (file.size > 2048000) {
      notificationStore.error('Ukuran file maksimal 2MB')
      e.target.value = ''
      return
    }

    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif']
    if (!validTypes.includes(file.type)) {
      notificationStore.error('Format file harus JPG, PNG, atau GIF')
      e.target.value = ''
      return
    }

    photoFile.value = file
  }
}

const closeModal = () => {
  showEditModal.value = false
  photoFile.value = null
  form.value.current_password = ''
  form.value.new_password = ''
  form.value.new_password_confirmation = ''
}

const handleSubmit = async () => {
  // Validasi password jika diisi
  if (
    form.value.current_password ||
    form.value.new_password ||
    form.value.new_password_confirmation
  ) {
    if (!form.value.current_password) {
      notificationStore.warning('Password saat ini harus diisi')
      return
    }

    if (!form.value.new_password) {
      notificationStore.warning('Password baru harus diisi')
      return
    }

    if (form.value.new_password !== form.value.new_password_confirmation) {
      notificationStore.warning('Konfirmasi password tidak cocok')
      return
    }

    if (form.value.new_password.length < 8) {
      notificationStore.warning('Password baru minimal 8 karakter')
      return
    }
  }

  submitting.value = true
  try {
    const formData = new FormData()

    formData.append('name', form.value.name.trim())
    formData.append('email', form.value.email.trim())

    // Kirim empty string jika tidak ada nilai (sesuai validasi backend nullable)
    formData.append('whatsapp_number', form.value.whatsapp_number?.trim() || '')
    formData.append('address', form.value.address?.trim() || '')

    if (photoFile.value) {
      formData.append('photo', photoFile.value)
    }

    // Hanya kirim password jika semua field password terisi
    if (form.value.current_password && form.value.new_password) {
      formData.append('current_password', form.value.current_password)
      formData.append('new_password', form.value.new_password)
      formData.append('new_password_confirmation', form.value.new_password_confirmation)
    }

    const response = await api.post('/profile', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    // console.log('Profile update response:', response.data)

    // Update profile data
    profile.value = response.data.data

    // Update auth store
    if (authStore.setUser) {
      authStore.setUser(response.data.data)
    }

    // Show success notification
    notificationStore.success(response.data.message || 'Profil berhasil diperbarui')

    // Close modal
    closeModal()
  } catch (error) {
    console.error('Error updating profile:', error)

    if (error.response?.status === 422) {
      const errors = error.response?.data?.errors
      if (errors) {
        // Tampilkan error pertama dari setiap field
        const firstError = Object.values(errors)[0][0]
        notificationStore.error(firstError)
      } else {
        notificationStore.error(error.response?.data?.message || 'Data tidak valid')
      }
    } else if (error.response?.status === 401) {
      notificationStore.error('Password saat ini tidak sesuai')
    } else {
      notificationStore.error(error.response?.data?.message || 'Gagal memperbarui profil')
    }
  } finally {
    submitting.value = false
  }
}

const loadProfile = async () => {
  loading.value = true
  try {
    const response = await api.get('/profile')

    // Backend mengembalikan response.data.data
    const userData = response.data.data || response.data
    profile.value = userData

    form.value = {
      name: userData.name || '',
      email: userData.email || '',
      whatsapp_number: userData.whatsapp_number || '',
      address: userData.address || '',
      current_password: '',
      new_password: '',
      new_password_confirmation: '',
    }
  } catch (error) {
    console.error('Failed to load profile:', error)
    notificationStore.error('Gagal memuat profil')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadProfile()
})
</script>

<style scoped>
.profile-container {
  max-width: 900px;
  margin: 0 auto;
}

.profile-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.page-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #111827;
  margin: 0;
}

.btn-edit {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.95rem;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-edit:hover {
  background: #5568d3;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.profile-card {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.profile-photo-section {
  display: flex;
  align-items: center;
  gap: 2rem;
  padding-bottom: 2rem;
  border-bottom: 1px solid #e5e7eb;
  margin-bottom: 2rem;
}

.profile-photo {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  overflow: hidden;
  border: 4px solid #667eea;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.profile-photo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.photo-placeholder {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  color: white;
  font-weight: 600;
}

.profile-info-summary {
  flex: 1;
}

.profile-name {
  font-size: 1.75rem;
  font-weight: 600;
  color: #111827;
  margin: 0 0 0.5rem 0;
}

.profile-role {
  display: inline-block;
  padding: 0.375rem 1rem;
  background: #dbeafe;
  color: #1e40af;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 500;
}

.profile-details {
  display: grid;
  gap: 1.5rem;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.detail-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #6b7280;
}

.detail-value {
  font-size: 1rem;
  color: #111827;
  padding: 0.75rem;
  background: #f9fafb;
  border-radius: 6px;
  border: 1px solid #e5e7eb;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

.modal-content {
  background: white;
  border-radius: 12px;
  max-width: 600px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.modal-header h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #111827;
  margin: 0;
}

.modal-close {
  background: none;
  border: none;
  font-size: 2rem;
  color: #6b7280;
  cursor: pointer;
  line-height: 1;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  transition: all 0.2s;
}

.modal-close:hover {
  background: #f3f4f6;
  color: #111827;
}

.modal-form {
  padding: 1.5rem;
}

.photo-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2rem;
}

.photo-preview {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  overflow: hidden;
  border: 3px solid #667eea;
}

.photo-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.photo-preview .photo-placeholder {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.5rem;
  color: white;
  font-weight: 600;
}

.btn-upload {
  padding: 0.5rem 1.5rem;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: background 0.2s;
}

.btn-upload:hover {
  background: #5568d3;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #374151;
  font-size: 0.95rem;
}

.required {
  color: #dc2626;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.95rem;
  transition: border-color 0.2s;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #667eea;
}

.form-divider {
  height: 1px;
  background: #e5e7eb;
  margin: 2rem 0 1.5rem 0;
}

.section-title {
  font-size: 1rem;
  font-weight: 600;
  color: #111827;
  margin-bottom: 1.25rem;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
}

.btn-cancel {
  padding: 0.75rem 1.5rem;
  background: white;
  color: #374151;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.95rem;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-cancel:hover {
  background: #f9fafb;
}

.btn-save {
  padding: 0.75rem 1.5rem;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.95rem;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-save:hover:not(:disabled) {
  background: #5568d3;
}

.btn-save:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .profile-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .profile-photo-section {
    flex-direction: column;
    text-align: center;
  }

  .modal-overlay {
    padding: 0;
  }

  .modal-content {
    max-height: 100vh;
    border-radius: 0;
  }
}
</style>
