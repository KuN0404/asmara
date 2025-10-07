export const AGENDA_STATUS = {
  COMMING_SOON: 'comming_soon',
  IN_PROGRESS: 'in_progress',
  SCHEDULE_CHANGE: 'schedule_change',
  COMPLETED: 'completed',
  CANCELLED: 'cancelled',
}

export const USER_ROLES = {
  SUPER_ADMIN: 'super_admin',
  ADMIN: 'admin',
  STAFF: 'staff',
}

export const AGENDA_TYPES = [
  { value: 'rapat', label: 'Rapat' },
  { value: 'presentasi', label: 'Presentasi' },
  { value: 'pelatihan', label: 'Pelatihan' },
  { value: 'kunjungan', label: 'Kunjungan' },
  { value: 'lainnya', label: 'Lainnya' },
]

export const ACTIVITY_TYPES = [
  { value: 'internal', label: 'Internal' },
  { value: 'eksternal', label: 'Eksternal' },
  { value: 'hybrid', label: 'Hybrid' },
]
