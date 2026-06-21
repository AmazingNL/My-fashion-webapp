import http from './http'

export const getCart = () => http.get('/cart')
export const addCartItem = payload => http.post('/cart/items', payload)
export const updateCartItem = payload => http.patch('/cart/items', payload)
export const removeCartItem = payload => http.delete('/cart/items', { data: payload })
export const clearCart = () => http.delete('/cart')
