<template>
  <AdminLayout>
    <div class="page-header">
      <h2 class="page-title">Edit Pengguna</h2>
      <router-link to="/users" class="btn btn-secondary">← Kembali</router-link>
    </div>

    <LoadingSpinner v-if="loadingData" text="Memuat data..." />

    <div v-else-if="user" class="form-container">
      <form @submit.prevent="handleSubmit">
        <!-- Username -->
        <div class="form-group">
          <label class="form-label">Username</label>
          <input
            type="text"
            class="form-input"
            v-model.trim="form.username"
            placeholder="Masukkan username"
          />
        </div>

        <!-- Password -->
        <div class="form-group">
          <label class="form-label">Password</label>
          <input
            type="password"
            class="form-input"
            v-model="form.password"
            placeholder="Kosongkan jika tidak ingin mengubah password"
          />
        </div>

        <!-- Nama -->
        <div class="form-group">
          <label class="form-label">Nama</label>
          <input
            type="text"
            class="form-input"
            v-model.trim="form.name"
            placeholder="Masukkan nama pengguna"
          />
        </div>

        <!-- No WhatsApp -->
        <div class="form-group">
          <label class="form-label">No WhatsApp</label>
          <input
            type="text"
            class="form-input"
            v-model.trim="form.whatsapp_number"
            placeholder="Masukkan nomor WhatsApp"
          />
        </div>

        <!-- Email -->
        <div class="form-group">
          <label class="form-label">Email</label>
          <input
            type="email"
            class="form-input"
            v-model.trim="form.email"
            placeholder="Masukkan email pengguna"
          />
        </div>

        <!-- Alamat -->
        <div class="form-group">
          <label class="form-label">Alamat</label>
          <input
            type="text"
            class="form-input"
            v-model.trim="form.address"
            placeholder="Masukkan alamat pengguna"
          />
        </div>

        <!-- Role -->
        <div class="form-group">
          <label class="form-label">Role</label>
          <select class="form-input" v-model="form.role">
            <option value="">-- Pilih Role --</option>
            <!-- Hapus Super Admin di sini -->
            <option value="admin">Admin</option>
            <option value="staff">Staff</option>
          </select>
        </div>

        <!-- Foto -->
        <div class="form-group">
          <label class="form-label">Foto Profil</label>
          <input type="file" class="form-input" @change="handleFileChange" accept="image/*" />
          <div v-if="user.photo" style="margin-top: 8px">
            <img
              :src="`/storage/${user.photo}`"
              alt="Foto Profil"
              style="width: 100px; border-radius: 6px; border: 1px solid #ddd"
            />
          </div>
        </div>

        <!-- Actions -->
        <div class="form-actions">
          <router-link to="/users" class="btn btn-secondary">Batal</router-link>
          <button type="submit" class="btn btn-primary" :disabled="loading">
            <span v-if="!loading">💾 Simpan Perubahan</span>
            <span v-else>Menyimpan...</span>
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AdminLayout from '@/layouts/AdminLayout.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import { useNotificationStore } from '@/stores/notification'
import userService from '@/services/userService.js'
import { handleError } from '@/utils/helpers'

const route = useRoute()
const router = useRouter()
const notificationStore = useNotificationStore()

const loadingData = ref(true)
const loading = ref(false)
const user = ref(null)

const form = ref({
  username: '',
  password: '',
  name: '',
  whatsapp_number: '',
  email: '',
  address: '',
  role: '',
  photo: null,
})

onMounted(async () => {
  try {
    const data = await userService.getById(route.params.id)
    user.value = data

    form.value.username = data.username || ''
    form.value.name = data.name || ''
    form.value.whatsapp_number = data.whatsapp_number || ''
    form.value.email = data.email || ''
    form.value.address = data.address || ''
    form.value.role = data.roles?.[0]?.name || ''
  } catch (error) {
    notificationStore.error(handleError(error))
    router.push('/users')
  } finally {
    loadingData.value = false
  }
})

const handleFileChange = (e) => {
  const file = e.target.files[0]
  if (file) {
    if (!file.type.startsWith('image/')) {
      notificationStore.error('File harus berupa gambar')
      return
    }
    if (file.size > 2 * 1024 * 1024) {
      notificationStore.error('Ukuran file maksimal 2MB')
      return
    }
    form.value.photo = file
  }
}

const handleSubmit = async () => {
  if (!form.value.username.trim()) {
    notificationStore.error('Username wajib diisi')
    return
  }

  if (!form.value.name.trim()) {
    notificationStore.error('Nama wajib diisi')
    return
  }

  if (!form.value.email.trim()) {
    notificationStore.error('Email wajib diisi')
    return
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(form.value.email)) {
    notificationStore.error('Format email tidak valid')
    return
  }

  if (!form.value.whatsapp_number.trim()) {
    notificationStore.error('Nomor Whatsapp wajib diisi')
    return
  }

  if (!form.value.role) {
    notificationStore.error('Role wajib dipilih')
    return
  }

  const payload = {
    username: form.value.username.trim(),
    name: form.value.name.trim(),
    email: form.value.email.trim(),
    whatsapp_number: form.value.whatsapp_number.trim(),
    address: form.value.address?.trim() || '',
    role: form.value.role,
  }

  if (form.value.password) {
    if (form.value.password.length < 8) {
      notificationStore.error('Password minimal 8 karakter')
      return
    }
    payload.password = form.value.password
  }

  if (form.value.photo) payload.photo = form.value.photo

  loading.value = true
  try {
    await userService.update(route.params.id, payload)
    notificationStore.success('Pengguna berhasil diperbarui ✅')
    router.push('/users')
  } catch (error) {
    notificationStore.error(handleError(error))
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.form-container {
  background: #fff;
  border-radius: 12px;
  padding: 24px;
  margin-top: 1rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  max-width: 750px;
  margin-left: auto;
  margin-right: auto;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-label {
  display: block;
  font-weight: 600;
  margin-bottom: 6px;
  color: #444;
}

.form-input {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 20px;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 10px 18px;
  font-size: 14px;
  border-radius: 8px;
  cursor: pointer;
  text-decoration: none;
  border: none;
  transition: 0.2s;
}

.btn-primary {
  background-color: #1976d2;
  color: #fff;
}

.btn-primary:hover {
  background-color: #125ea8;
}

.btn-secondary {
  background-color: #f1f1f1;
  color: #444;
}

.btn-secondary:hover {
  background-color: #e1e1e1;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
