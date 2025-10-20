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
      if (key === 'attachments' && data[key] && data[key].length > 0) {
        data[key].forEach((file) => formData.append('attachments[]', file))
      } else if (key === 'participant_ids' && data[key] && data[key].length > 0) {
        data[key].forEach((id) => formData.append(`${key}[]`, id))
      } else if (key === 'user_participant_ids' && data[key] && data[key].length > 0) {
        data[key].forEach((id) => formData.append(`${key}[]`, id))
      } else if (data[key] !== null && data[key] !== undefined && data[key] !== '') {
        formData.append(key, data[key])
      }
    })

    const response = await api.post('/office-agendas', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    return response.data
  },

  async update(id, data) {
    // Jika data berisi file, gunakan FormData
    if (data.attachments && data.attachments.length > 0) {
      const formData = new FormData()
      formData.append('_method', 'PUT')

      Object.keys(data).forEach((key) => {
        if (key === 'attachments' && data[key] && data[key].length > 0) {
          data[key].forEach((file) => formData.append('attachments[]', file))
        } else if (key === 'participant_ids' && data[key] && data[key].length > 0) {
          data[key].forEach((id) => formData.append(`${key}[]`, id))
        } else if (key === 'user_participant_ids' && data[key] && data[key].length > 0) {
          data[key].forEach((id) => formData.append(`${key}[]`, id))
        } else if (data[key] !== null && data[key] !== undefined && data[key] !== '') {
          formData.append(key, data[key])
        }
      })

      const response = await api.post(`/office-agendas/${id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      return response.data
    }

    // Jika tidak ada file, gunakan PUT biasa
    const response = await api.put(`/office-agendas/${id}`, data)
    return response.data
  },

  async delete(id) {
    const response = await api.delete(`/office-agendas/${id}`)
    return response.data
  },

  async deleteAttachment(agendaId, attachmentId) {
    const response = await api.delete(`/office-agendas/${agendaId}/attachments`, {
      data: { attachment_id: attachmentId },
    })
    return response.data
  },
}
