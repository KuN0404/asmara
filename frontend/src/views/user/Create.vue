<template>
  <AdminLayout>
    <div class="page-header">
      <h2 class="page-title">Tambah Pengguna</h2>
      <router-link to="/users" class="btn btn-secondary">
        <ArrowLeft :size="18" /> Kembali
      </router-link>
    </div>

    <div class="form-container">
      <form @submit.prevent="handleSubmit">
        <!-- Username -->
        <div class="form-group">
          <label class="form-label">Username *</label>
          <input
            type="text"
            class="form-input"
            v-model.trim="form.username"
            placeholder="Masukkan username pengguna"
            required
          />
        </div>

        <!-- Password -->
        <div class="form-group">
          <label class="form-label">Password *</label>
          <input
            type="password"
            class="form-input"
            v-model="form.password"
            placeholder="Minimal 8 karakter"
            required
          />
        </div>

        <!-- Nama -->
        <div class="form-group">
          <label class="form-label">Nama *</label>
          <input
            type="text"
            class="form-input"
            v-model.trim="form.name"
            placeholder="Masukkan nama pengguna"
            required
          />
        </div>

        <!-- No WhatsApp -->
        <div class="form-group">
          <label class="form-label">No Whatsapp *</label>
          <input
            type="text"
            class="form-input"
            v-model.trim="form.whatsapp_number"
            placeholder="Masukkan no whatsapp pengguna"
            required
          />
        </div>

        <!-- Email -->
        <div class="form-group">
          <label class="form-label">Email *</label>
          <input
            type="email"
            class="form-input"
            v-model.trim="form.email"
            placeholder="Masukkan email pengguna"
            required
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
          <label class="form-label">Role *</label>
          <select class="form-input" v-model="form.role" required>
            <option value="">-- Pilih Role --</option>
            <option value="admin">Admin</option>
            <option value="staff">Staff</option>
          </select>
        </div>

        <!-- Foto -->
        <div class="form-group">
          <label class="form-label">Foto Profile</label>
          <input type="file" class="form-input" @change="handleFileChange" accept="image/*" />
        </div>

        <!-- Actions -->
        <div class="form-actions">
          <router-link to="/users" class="btn btn-secondary">Batal</router-link>
          <button type="submit" class="btn btn-primary" :disabled="loading">
            <Save :size="18" v-if="!loading" />
            <span v-if="!loading">Simpan</span>
            <span v-else>Menyimpan...</span>
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { ArrowLeft, Save } from 'lucide-vue-next'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { useNotificationStore } from '@/stores/notification'
import userService from '@/services/userService.js'
import { handleError } from '@/utils/helpers'

const router = useRouter()
const notificationStore = useNotificationStore()
const loading = ref(false)

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
  // ✨ Frontend validation sesuai BE
  if (!form.value.username) return notificationStore.error('Username wajib diisi')
  if (!form.value.password || form.value.password.length < 8)
    return notificationStore.error('Password minimal 8 karakter')
  if (!form.value.name) return notificationStore.error('Nama wajib diisi')
  if (!form.value.whatsapp_number) return notificationStore.error('Nomor WhatsApp wajib diisi')
  if (!form.value.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email))
    return notificationStore.error('Email tidak valid')
  if (!form.value.role) return notificationStore.error('Role wajib dipilih')

  loading.value = true
  try {
    await userService.create(form.value)
    notificationStore.success('Pengguna berhasil ditambahkan')
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
  max-width: 600px;
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

.form-input:focus {
  outline: none;
  border-color: #1976d2;
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
  gap: 6px;
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
