<template>
  <Teleport to="body">
    <div class="alert-container">
      <TransitionGroup name="alert">
        <div
          v-for="notification in notificationStore.notifications"
          :key="notification.id"
          class="alert"
          :class="`alert-${notification.type}`"
        >
          <span class="alert-icon">{{ getIcon(notification.type) }}</span>
          <span class="alert-message">{{ notification.message }}</span>
          <button
            class="alert-close"
            @click="notificationStore.removeNotification(notification.id)"
          >
            ×
          </button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { useNotificationStore } from '@/stores/notification'

const notificationStore = useNotificationStore()

const getIcon = (type) => {
  const icons = {
    success: '✓',
    error: '✕',
    warning: '⚠',
    info: 'ℹ',
  }
  return icons[type] || 'ℹ'
}
</script>

<style scoped>
.alert-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.alert {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 15px 20px;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  min-width: 300px;
  max-width: 500px;
  background: white;
}

.alert-success {
  border-left: 4px solid #059669;
}

.alert-error {
  border-left: 4px solid #dc2626;
}

.alert-warning {
  border-left: 4px solid #f59e0b;
}

.alert-info {
  border-left: 4px solid #1e40af;
}

.alert-icon {
  font-size: 1.2rem;
  font-weight: bold;
}

.alert-success .alert-icon {
  color: #059669;
}

.alert-error .alert-icon {
  color: #dc2626;
}

.alert-warning .alert-icon {
  color: #f59e0b;
}

.alert-info .alert-icon {
  color: #1e40af;
}

.alert-message {
  flex: 1;
  color: #1e293b;
}

.alert-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #64748b;
  cursor: pointer;
  padding: 0;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.alert-close:hover {
  color: #1e293b;
}

.alert-enter-active,
.alert-leave-active {
  transition: all 0.3s ease;
}

.alert-enter-from {
  opacity: 0;
  transform: translateX(100px);
}

.alert-leave-to {
  opacity: 0;
  transform: translateX(100px);
}
</style>
