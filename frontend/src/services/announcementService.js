import api from './api'

export default {
  async getAll(params = {}) {
    const response = await api.get('/announcements', { params })
    return response.data
  },

  async getById(id) {
    const response = await api.get(`/announcements/${id}`)
    return response.data
  },

  async create(data) {
    const formData = new FormData()

    Object.keys(data).forEach((key) => {
      if (key === 'media' && data[key]) {
        formData.append('media', data[key])
      } else if (key === 'attachments' && data[key]) {
        data[key].forEach((file) => formData.append('attachments[]', file))
      } else if (data[key] !== null && data[key] !== undefined) {
        formData.append(key, data[key])
      }
    })

    const response = await api.post('/announcements', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    return response.data
  },

  async update(id, data) {
    const formData = new FormData()

    Object.keys(data).forEach((key) => {
      if (key === 'media' && data[key] instanceof File) {
        formData.append('media', data[key])
      } else if (data[key] !== null && data[key] !== undefined) {
        formData.append(key, data[key])
      }
    })

    const response = await api.post(`/announcements/${id}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    return response.data
  },

  async delete(id) {
    const response = await api.delete(`/announcements/${id}`)
    return response.data
  },
}
