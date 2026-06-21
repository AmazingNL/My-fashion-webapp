import http from './http'

export const getOrders = params => http.get('/orders', { params })
export const getOrder = orderId => http.get(`/orders/${orderId}`)
export const cancelOrder = orderId => http.post(`/orders/${orderId}/cancel`)