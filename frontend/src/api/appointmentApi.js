import http from './http'

export const getAppointments = () => http.get('/appointments')
export const getAppointmentSlots = date => http.get('/appointments/slots', { params: { date } })
export const bookAppointment = payload => http.post('/appointments', payload)
export const getAppointment = (id, date) => http.get(`/appointments/${id}`, { params: { date } })
export const updateAppointmentSlot = (id, payload) => http.patch(`/appointments/${id}/slot`, payload)
export const updateAppointmentDetails = (id, payload) => http.patch(`/appointments/${id}`, payload)
export const cancelAppointment = id => http.delete(`/appointments/${id}`)