import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useNotificationStore = defineStore('notification', () => {
  const notifications = ref([])

  const addNotification = (notification) => {
    const id = Date.now()
    notifications.value.push({
      id,
      type: notification.type || 'info',
      message: notification.message,
      duration: notification.duration || 3000,
    })

    setTimeout(() => {
      removeNotification(id)
    }, notification.duration || 3000)
  }

  const removeNotification = (id) => {
    notifications.value = notifications.value.filter((n) => n.id !== id)
  }

  const success = (message) => {
    addNotification({ type: 'success', message })
  }

  const error = (message) => {
    addNotification({ type: 'error', message })
  }

  const info = (message) => {
    addNotification({ type: 'info', message })
  }

  const warning = (message) => {
    addNotification({ type: 'warning', message })
  }

  return {
    notifications,
    addNotification,
    removeNotification,
    success,
    error,
    info,
    warning,
  }
})
