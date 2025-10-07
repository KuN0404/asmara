<template>
  <AuthLayout>
    <form @submit.prevent="handleLogin" class="login-form">
      <h2 class="form-title">Login</h2>
      <p class="form-subtitle">Masuk ke sistem agenda BPS</p>

      <div class="form-group">
        <label class="form-label">Username</label>
        <input
          type="text"
          class="form-input"
          v-model="form.username"
          placeholder="Masukkan username"
          required
        />
      </div>

      <div class="form-group">
        <label class="form-label">Password</label>
        <input
          type="password"
          class="form-input"
          v-model="form.password"
          placeholder="Masukkan password"
          required
        />
      </div>

      <button type="submit" class="btn-login" :disabled="loading">
        <span v-if="!loading">Login</span>
        <span v-else>Loading...</span>
      </button>
    </form>
  </AuthLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import AuthLayout from '@/layouts/AuthLayout.vue'
import { handleError } from '@/utils/helpers'

const router = useRouter()
const authStore = useAuthStore()
const notificationStore = useNotificationStore()

const form = ref({
  username: '',
  password: '',
})

const loading = ref(false)

const handleLogin = async () => {
  loading.value = true
  try {
    await authStore.login(form.value)
    notificationStore.success('Login berhasil!')
    router.push('/')
  } catch (error) {
    notificationStore.error(handleError(error))
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-form {
  margin-top: 20px;
}

.form-title {
  font-size: 1.8rem;
  font-weight: 700;
  color: #1e40af;
  margin: 0 0 10px 0;
  text-align: center;
}

.form-subtitle {
  color: #64748b;
  text-align: center;
  margin: 0 0 30px 0;
}

.form-group {
  margin-bottom: 20px;
}

.form-label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #374151;
}

.form-input {
  width: 100%;
  padding: 12px 16px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.2s;
}

.form-input:focus {
  outline: none;
  border-color: #1e40af;
}

.btn-login {
  width: 100%;
  padding: 14px;
  background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s;
}

.btn-login:hover:not(:disabled) {
  transform: translateY(-2px);
}

.btn-login:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
