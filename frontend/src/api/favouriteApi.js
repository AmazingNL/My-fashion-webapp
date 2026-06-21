import http from './http'

export const getFavourites = () => http.get('/favourites')
export const toggleFavourite = productId => http.post('/favourites/items', { productId })
export const clearFavourites = () => http.delete('/favourites')