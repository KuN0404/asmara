export const formatDate = (date) => {
  if (!date) return ''
  const d = new Date(date)
  const day = String(d.getDate()).padStart(2, '0')
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const year = d.getFullYear()
  return `${day}/${month}/${year}`
}

export const formatDateTime = (datetime) => {
  if (!datetime) return ''
  const d = new Date(datetime)
  return `${formatDate(d)} ${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`
}

export const getStatusBadgeClass = (status) => {
  const classes = {
    comming_soon: 'status-badge upcoming',
    in_progress: 'status-badge today',
    schedule_change: 'status-badge warning',
    completed: 'status-badge past',
    cancelled: 'status-badge danger',
  }
  return classes[status] || 'status-badge'
}

export const getStatusText = (status) => {
  const texts = {
    comming_soon: 'Akan Datang',
    in_progress: 'Sedang Berlangsung',
    schedule_change: 'Perubahan Jadwal',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
  }
  return texts[status] || status
}

export const handleError = (error) => {
  if (error.response) {
    return error.response.data.message || 'Terjadi kesalahan'
  }
  return error.message || 'Terjadi kesalahan'
}
