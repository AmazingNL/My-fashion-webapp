import http from './http'

export const login = credentials => http.post('/auth/login', credentials)
export const logout = () => http.post('/auth/logout')
export const register = payload => http.post('/users', payload)
export const requestPasswordReset = payload => http.post('/auth/password-reset', payload)
export const verifyPasswordReset = payload => http.post('/auth/password-reset/verify', payload)
