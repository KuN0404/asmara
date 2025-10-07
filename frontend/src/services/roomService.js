import api from './api'

export default {
  async getAll(params = {}) {
    const response = await api.get('/rooms', { params })
    return response.data
  },

  async getById(id) {
    const response = await api.get(`/rooms/${id}`)
    return response.data
  },

  async create(data) {
    const response = await api.post('/rooms', data)
    return response.data
  },

  async update(id, data) {
    const response = await api.put(`/rooms/${id}`, data)
    return response.data
  },

  async delete(id) {
    const response = await api.delete(`/rooms/${id}`)
    return response.data
  },
}
