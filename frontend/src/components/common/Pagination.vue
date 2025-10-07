<template>
  <div class="pagination" v-if="pagination">
    <div class="pagination-info">
      Menampilkan {{ pagination.from }} - {{ pagination.to }} dari {{ pagination.total }} data
    </div>
    <div class="pagination-controls">
      <button
        class="pagination-btn"
        :disabled="pagination.current_page === 1"
        @click="$emit('change-page', pagination.current_page - 1)"
      >
        ← Sebelumnya
      </button>

      <span class="pagination-current">
        Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}
      </span>

      <button
        class="pagination-btn"
        :disabled="pagination.current_page === pagination.last_page"
        @click="$emit('change-page', pagination.current_page + 1)"
      >
        Selanjutnya →
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  pagination: Object,
})

defineEmits(['change-page'])
</script>

<style scoped>
.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  background: white;
  border-top: 1px solid #e2e8f0;
}

.pagination-info {
  color: #64748b;
  font-size: 0.9rem;
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 15px;
}

.pagination-btn {
  padding: 8px 16px;
  background: #1e40af;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: background 0.2s;
}

.pagination-btn:hover:not(:disabled) {
  background: #1e3a8a;
}

.pagination-btn:disabled {
  background: #cbd5e1;
  cursor: not-allowed;
}

.pagination-current {
  color: #1e293b;
  font-weight: 500;
}
</style>
