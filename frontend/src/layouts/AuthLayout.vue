<template>
  <div class="auth-layout">
    <div class="auth-container">
      <div class="auth-card">
        <div class="auth-header">
          <div class="logo">
            <div class="logo-icon">BPS</div>
            <div class="logo-text">
              <h1>BPS Admin</h1>
              <p>Sistem Agenda Kantor</p>
            </div>
          </div>
        </div>
        <slot />
      </div>
    </div>
  </div>
</template>

<style scoped>
.auth-layout {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
  padding: 20px;
}

.auth-container {
  width: 100%;
  max-width: 450px;
}

.auth-card {
  background: white;
  border-radius: 16px;
  padding: 40px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.auth-header {
  text-align: center;
  margin-bottom: 30px;
}

.logo {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 15px;
  margin-bottom: 10px;
}

.logo-icon {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  color: white;
  font-size: 1.5rem;
}

.logo-text h1 {
  margin: 0;
  font-size: 1.8rem;
  font-weight: 700;
  color: #1e40af;
}

.logo-text p {
  margin: 0;
  font-size: 0.95rem;
  color: #64748b;
}
</style>
