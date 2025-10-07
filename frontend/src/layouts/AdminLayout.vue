<template>
  <div class="admin-layout">
    <Sidebar :visible="sidebarVisible" @close="toggleSidebar" />

    <main class="main-content" :class="{ 'sidebar-collapsed': !sidebarVisible }">
      <TopBar @toggle-sidebar="toggleSidebar" />

      <div class="content-area">
        <slot />
      </div>
    </main>

    <Alert />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Sidebar from '@/components/common/Sidebar.vue'
import TopBar from '@/components/common/TopBar.vue'
import Alert from '@/components/common/Alert.vue'

const sidebarVisible = ref(true)

const toggleSidebar = () => {
  sidebarVisible.value = !sidebarVisible.value
}
</script>

<style scoped>
.admin-layout {
  display: flex;
  min-height: 100vh;
  background-color: #f8fafc;
}

.main-content {
  flex: 1;
  margin-left: 280px;
  transition: margin-left 0.3s ease;
}

.main-content.sidebar-collapsed {
  margin-left: 0;
}

.content-area {
  padding: 30px;
}

@media (max-width: 768px) {
  .main-content {
    margin-left: 0;
  }

  .content-area {
    padding: 20px;
  }
}
</style>
