import api from './api'

export default {
  async getAll(params = {}) {
    const response = await api.get('/participants', { params })
    return response.data
  },

  async getById(id) {
    const response = await api.get(`/participants/${id}`)
    return response.data
  },

  async create(data) {
    const response = await api.post('/participants', data)
    return response.data
  },

  async update(id, data) {
    const response = await api.put(`/participants/${id}`, data)
    return response.data
  },

  async delete(id) {
    const response = await api.delete(`/participants/${id}`)
    return response.data
  },
}
