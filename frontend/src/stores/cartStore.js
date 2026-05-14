import { reactive } from 'vue'
import { addCartItem, clearCart, getCart, removeCartItem, updateCartItem } from '../api/cartApi'

export const cartState = reactive({
	cart: { items: [], total: 0, itemCount: 0, isEmpty: true },
	loading: false,
	error: '',
})

function normalizeCart(payload) {
	const cart = payload?.data?.cart || payload?.data || payload?.cart || payload || {}

	return {
		items: cart.items || cart.cartItems || [],
		total: Number(cart.total || 0),
		itemCount: Number(cart.itemCount || cart.items?.length || 0),
		isEmpty: Boolean(cart.isEmpty ?? !(cart.items || []).length),
	}
}

async function mutateCart(request) {
	cartState.loading = true
	cartState.error = ''

	try {
		const response = await request()
		cartState.cart = normalizeCart(response.data)
		return response.data
	} catch (error) {
		cartState.error = error.response?.data?.message || 'Cart action failed.'
		throw error
	} finally {
		cartState.loading = false
	}
}

export const loadCart = () => mutateCart(() => getCart())
export const addToCart = payload => mutateCart(() => addCartItem(payload))
export const updateQuantity = payload => mutateCart(() => updateCartItem(payload))
export const removeFromCart = payload => mutateCart(() => removeCartItem(payload))
export const emptyCart = () => mutateCart(() => clearCart())
