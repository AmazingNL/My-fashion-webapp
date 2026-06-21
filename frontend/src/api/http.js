import axios from 'axios'
import { addFlash } from '../stores/flashStore'

const http = axios.create({
    baseURL: '/api',
})

http.interceptors.request.use(config => {
    const token = localStorage.getItem('token')

    if(token){
        config.headers.Authorization = `Bearer ${token}`
    }

    return config
})

http.interceptors.response.use(
    response => {
        const method = String(response.config?.method || 'get').toLowerCase()
        const isAction = !['get', 'head', 'options'].includes(method)
        const message = response.data?.message

        if (isAction && message && message !== 'success') {
            addFlash(message, 'success')
        }

        return response
    },
    error => {
        const message = error.response?.data?.message || error.message || 'Something went wrong.'
        addFlash(message, 'error')
        return Promise.reject(error)
    }
)

export default http