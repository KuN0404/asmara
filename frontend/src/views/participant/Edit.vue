<template>
  <AdminLayout>
    <div class="page-header">
      <h2 class="page-title">Edit Pengumuman</h2>
      <router-link to="/announcements" class="btn btn-secondary">← Kembali</router-link>
    </div>

    <LoadingSpinner v-if="loadingData" text="Memuat data..." />

    <div v-else-if="announcement" class="form-container">
      <form @submit.prevent="handleSubmit">
        <!-- Judul -->
        <div class="form-group">
          <label class="form-label">Judul Pengumuman *</label>
          <input
            type="text"
            class="form-input"
            v-model="form.title"
            placeholder="Masukkan judul pengumuman"
            required
          />
        </div>

        <!-- Editor Quill -->
        <div class="form-group">
          <label class="form-label">Isi Pengumuman *</label>
          <QuillEditor
            v-model:content="form.content"
            content-type="html"
            theme="snow"
            toolbar="full"
            class="quill-editor"
          />
        </div>

        <!-- Lampiran Lama -->
        <div v-if="existingAttachments.length" class="form-group">
          <label class="form-label">Lampiran Saat Ini</label>
          <ul class="existing-attachment-list">
            <li v-for="att in existingAttachments" :key="att.id">
              <a :href="`http://localhost:8000/storage/${att.file_path}`" target="_blank">
                📎 {{ att.file_name }}
              </a>
              <button type="button" class="remove-btn" @click="removeExisting(att.id)">❌</button>
            </li>
          </ul>
        </div>

        <!-- Lampiran Baru -->
        <div class="form-group">
          <label class="form-label">Tambah Lampiran Baru (optional)</label>
          <input
            type="file"
            class="form-input"
            multiple
            @change="handleAttachmentsUpload"
            accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
          />
          <ul v-if="newAttachmentNames.length" class="attachment-list">
            <li v-for="(name, index) in newAttachmentNames" :key="index">
              📎 {{ name }}
              <button type="button" class="remove-file-btn" @click="removeNewAttachment(index)">
                ❌
              </button>
            </li>
          </ul>
        </div>

        <!-- Checkbox tampilkan -->
        <div class="form-group">
          <label class="checkbox-label">
            <input type="checkbox" v-model="form.is_displayed" class="checkbox-input" />
            <span>Tampilkan ke pengguna lain</span>
          </label>
        </div>

        <!-- Actions -->
        <div class="form-actions">
          <router-link to="/announcements" class="btn btn-secondary">Batal</router-link>
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
import announcementService from '@/services/announcementService'
import { handleError } from '@/utils/helpers'
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css'

const route = useRoute()
const router = useRouter()
const notificationStore = useNotificationStore()

const loadingData = ref(true)
const loading = ref(false)
const announcement = ref(null)
const existingAttachments = ref([])
const newAttachmentNames = ref([])
const removedAttachmentIds = ref([])

const form = ref({
  title: '',
  content: '',
  attachments: [],
  is_displayed: false,
})

onMounted(async () => {
  try {
    const data = await announcementService.getById(route.params.id)
    announcement.value = data

    form.value.title = data.title
    form.value.content = data.content
    form.value.is_displayed = data.is_displayed
    existingAttachments.value = data.attachments || []
  } catch (error) {
    notificationStore.error(handleError(error))
    router.push('/announcements')
  } finally {
    loadingData.value = false
  }
})

const handleAttachmentsUpload = (e) => {
  const files = Array.from(e.target.files)
  if (files.length > 0) {
    form.value.attachments.push(...files)
    newAttachmentNames.value.push(...files.map((f) => f.name))
  }
}

const removeNewAttachment = (index) => {
  form.value.attachments.splice(index, 1)
  newAttachmentNames.value.splice(index, 1)
}

const removeExisting = (attId) => {
  if (!confirm('Hapus lampiran ini?')) return
  existingAttachments.value = existingAttachments.value.filter((a) => a.id !== attId)
  removedAttachmentIds.value.push(attId)
}

const handleSubmit = async () => {
  if (!form.value.title.trim()) {
    notificationStore.error('Judul pengumuman wajib diisi ❌')
    return
  }

  if (!form.value.content || form.value.content === '<p><br></p>') {
    notificationStore.error('Isi pengumuman wajib diisi ❌')
    return
  }

  const formData = new FormData()
  formData.append('title', form.value.title)
  formData.append('content', form.value.content)
  formData.append('is_displayed', form.value.is_displayed ? '1' : '0')

  // Lampiran baru
  form.value.attachments.forEach((file) => {
    formData.append('attachments[]', file)
  })

  // ID lampiran yang tetap (tidak dihapus)
  const keepAttachmentIds = existingAttachments.value.map((a) => a.id)
  keepAttachmentIds.forEach((id) => {
    formData.append('keep_attachment_ids[]', id)
  })

  loading.value = true
  try {
    await announcementService.update(route.params.id, formData)
    notificationStore.success('Pengumuman berhasil diperbarui ✅')
    router.push('/announcements')
  } catch (error) {
    console.error('❌ Error update:', error)
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

.quill-editor .ql-container {
  min-height: 300px;
}

.quill-editor .ql-editor {
  min-height: 300px;
  padding-bottom: 50px;
}

.existing-attachment-list {
  list-style: none;
  padding: 0;
  background: #f9f9f9;
  padding: 12px;
  border-radius: 8px;
}

.existing-attachment-list li {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #eee;
}

.existing-attachment-list li:last-child {
  border-bottom: none;
}

.existing-attachment-list a {
  color: #1976d2;
  text-decoration: none;
  font-size: 14px;
}

.remove-btn {
  background: transparent;
  border: none;
  color: #d32f2f;
  cursor: pointer;
  font-size: 16px;
}

.attachment-list {
  list-style: none;
  padding-left: 0;
  margin-top: 8px;
}

.attachment-list li {
  margin-top: 6px;
  font-size: 13px;
  color: #555;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.remove-file-btn {
  background: transparent;
  border: none;
  color: red;
  font-size: 14px;
  cursor: pointer;
  margin-left: 8px;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
}

.checkbox-input {
  width: 20px;
  height: 20px;
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
