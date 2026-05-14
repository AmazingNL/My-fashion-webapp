import { reactive } from 'vue'
import { login as loginRequest, logout as logoutRequest, register as registerRequest } from '../api/authApi'

const savedUser = localStorage.getItem('user')

export const authState = reactive({
	token: localStorage.getItem('token') || '',
	user: savedUser ? JSON.parse(savedUser) : null,
	loading: false,
	error: '',
})

export function isAdmin() {
	return String(authState.user?.role || '').toLowerCase() === 'admin'
}

export async function login(payload) {
	authState.loading = true
	authState.error = ''

	try {
		const response = await loginRequest(payload)
		const token = response.data?.data?.token || ''
		const user = response.data?.data?.user || null

		authState.token = token
		authState.user = user
		localStorage.setItem('token', token)
		localStorage.setItem('user', JSON.stringify(user))
		return response.data
	} catch (error) {
		authState.error = error.response?.data?.message || 'Login failed.'
		throw error
	} finally {
		authState.loading = false
	}
}

export async function register(payload) {
	return registerRequest(payload)
}

export async function logout() {
	try {
		await logoutRequest()
	} finally {
		authState.token = ''
		authState.user = null
		localStorage.removeItem('token')
		localStorage.removeItem('user')
	}
}
