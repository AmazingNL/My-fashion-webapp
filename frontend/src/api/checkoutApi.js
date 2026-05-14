import http from './http'

export const getCheckout = () => http.get('/checkout')
export const placeOrder = payload => http.post('/checkout', payload)
export const confirmPayment = payload => http.post('/checkout/payments/confirm', payload)
export const getConfirmation = orderId => http.get(`/checkout/confirmation/${orderId}`)