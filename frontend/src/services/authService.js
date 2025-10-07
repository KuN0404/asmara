import api from './api'

export default {
  async login(credentials) {
    const response = await api.post('/login', credentials)
    return response.data
  },

  async logout() {
    const response = await api.post('/logout')
    return response.data
  },

  async me() {
    const response = await api.get('/me')
    return response.data
  },
}
