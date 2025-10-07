import api from './api'

export default {
  async getAll(params = {}) {
    const response = await api.get('/office-agendas', { params })
    return response.data
  },

  async getById(id) {
    const response = await api.get(`/office-agendas/${id}`)
    return response.data
  },

  async create(data) {
    const formData = new FormData()

    Object.keys(data).forEach((key) => {
      if (key === 'attachments' && data[key]) {
        data[key].forEach((file) => formData.append('attachments[]', file))
      } else if (key === 'participant_ids' || key === 'user_participant_ids') {
        data[key].forEach((id) => formData.append(`${key}[]`, id))
      } else if (data[key] !== null && data[key] !== undefined) {
        formData.append(key, data[key])
      }
    })

    const response = await api.post('/office-agendas', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    return response.data
  },

  async update(id, data) {
    const response = await api.put(`/office-agendas/${id}`, data)
    return response.data
  },

  async delete(id) {
    const response = await api.delete(`/office-agendas/${id}`)
    return response.data
  },
}
