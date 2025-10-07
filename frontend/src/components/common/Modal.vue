<template>
  <div class="loading-spinner">
    <div class="spinner"></div>
    <p v-if="text">{{ text }}</p>
  </div>
</template>

<script setup>
defineProps({
  text: String,
})
</script>

<style scoped>
.loading-spinner {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #e2e8f0;
  border-top-color: #1e40af;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.loading-spinner p {
  margin-top: 15px;
  color: #64748b;
  font-size: 0.9rem;
}
</style>
