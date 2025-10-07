import api from './api'

export default {
  async getAll(params = {}) {
    const response = await api.get('/my-agendas', { params })
    return response.data
  },

  async getById(id) {
    const response = await api.get(`/my-agendas/${id}`)
    return response.data
  },

  async create(data) {
    const response = await api.post('/my-agendas', data)
    return response.data
  },

  async update(id, data) {
    const response = await api.put(`/my-agendas/${id}`, data)
    return response.data
  },

  async delete(id) {
    const response = await api.delete(`/my-agendas/${id}`)
    return response.data
  },

  async getPublicAgendas(params = {}) {
    const response = await api.get('/public-agendas', { params })
    return response.data
  },
}
