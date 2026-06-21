import http from './http'

export function getProducts(params = {}){
    return http.get('/products', { params })
}

export function getProduct(id){
    return http.get(`/products/${id}`)
}