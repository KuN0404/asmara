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
    const response = await api.post('/announcements', data, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    return response.data
  },

  // async update(id, data) {
  //   const formData = new FormData()

  //   Object.keys(data).forEach((key) => {
  //     if (key === 'attachments' && Array.isArray(data[key])) {
  //       data[key].forEach((file) => {
  //         formData.append('attachments[]', file)
  //       })
  //     } else if (data[key] !== null && data[key] !== undefined) {
  //       formData.append(key, data[key])
  //     }
  //   })

  //   formData.append('_method', 'PUT') // ← Tambahkan di sini

  //   const response = await api.post(`/announcements/${id}`, formData, {
  //     headers: { 'Content-Type': 'multipart/form-data' },
  //   })
  //   return response.data
  // },
  async update(id, data) {
    // Jika data sudah FormData, gunakan langsung
    if (data instanceof FormData) {
      data.append('_method', 'PUT')
      const response = await api.post(`/announcements/${id}`, data, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      return response.data
    }

    // Jika belum FormData, buat FormData baru
    const formData = new FormData()

    Object.keys(data).forEach((key) => {
      if (key === 'attachments' && Array.isArray(data[key])) {
        data[key].forEach((file) => {
          formData.append('attachments[]', file)
        })
      } else if (Array.isArray(data[key])) {
        data[key].forEach((item) => {
          formData.append(`${key}[]`, item)
        })
      } else if (data[key] !== null && data[key] !== undefined) {
        formData.append(key, data[key])
      }
    })

    formData.append('_method', 'PUT')

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
