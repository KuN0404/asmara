import api from './api'

export default {
  async getAll(params = {}) {
    const response = await api.get('/users', { params })
    return response.data
  },

  async getById(id) {
    const response = await api.get(`/users/${id}`)
    return response.data
  },

  async create(data) {
    const formData = new FormData()
    Object.keys(data).forEach((key) => {
      if (data[key] !== null && data[key] !== undefined) {
        formData.append(key, data[key])
      }
    })

    const response = await api.post('/users', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    return response.data
  },

  async update(id, data) {
    const formData = new FormData()
    Object.keys(data).forEach((key) => {
      if (data[key] !== null && data[key] !== undefined) {
        formData.append(key, data[key])
      }
    })

    const response = await api.post(`/users/${id}?_method=PUT`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    return response.data
  },

  async delete(id) {
    const response = await api.delete(`/users/${id}`)
    return response.data
  },

  async restore(id) {
    const response = await api.post(`/users/${id}/restore`)
    return response.data
  },
}
