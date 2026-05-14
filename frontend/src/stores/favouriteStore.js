import { reactive } from 'vue'
import { addFlash } from './flashStore'

const STORAGE_KEY = 'favourites'

function readSavedFavourites() {
	try {
		const saved = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]')
		return Array.isArray(saved) ? saved : []
	} catch {
		return []
	}
}

function saveFavourites() {
	localStorage.setItem(STORAGE_KEY, JSON.stringify(favouriteState.items))
}

function normalizeProduct(product) {
	return {
		productId: product.productId,
		productName: product.productName,
		category: product.category || '',
		price: product.price || 0,
		image: product.image || product.displayImage || '',
	}
}

export const favouriteState = reactive({
	items: readSavedFavourites(),
})

export function isFavourite(productId) {
	return favouriteState.items.some(item => String(item.productId) === String(productId))
}

export function toggleStoredFavourite(product) {
	if (!product?.productId) return false

	const existingIndex = favouriteState.items.findIndex(item => String(item.productId) === String(product.productId))

	if (existingIndex >= 0) {
		favouriteState.items.splice(existingIndex, 1)
		saveFavourites()
		addFlash('Removed from favourites.', 'success')
		return false
	}

	favouriteState.items.push(normalizeProduct(product))
	saveFavourites()
	addFlash('Added to favourites.', 'success')
	return true
}

export function clearStoredFavourites() {
	favouriteState.items = []
	saveFavourites()
	addFlash('Favourites cleared.', 'success')
}
