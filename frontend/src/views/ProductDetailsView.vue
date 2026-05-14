<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import Navbar from '../components/Navbar.vue'
import { getProduct } from '../api/productApi'
import { addToCart } from '../stores/cartStore'
import { isFavourite, toggleStoredFavourite } from '../stores/favouriteStore'

const route = useRoute()
const product = ref(null)
const variants = ref([])
const selectedVariantId = ref(0)
const quantity = ref(1)
const loading = ref(true)
const message = ref('')

const selectedVariant = computed(() => variants.value.find(variant => variant.variantId === selectedVariantId.value))
const productIsFavourite = computed(() => product.value ? isFavourite(product.value.productId) : false)

onMounted(async () => {
	try {
		const response = await getProduct(route.params.id)
		product.value = response.data?.data?.product || null
		variants.value = response.data?.data?.variants || []
		selectedVariantId.value = variants.value[0]?.variantId || 0
	} catch (error) {
		message.value = error.response?.data?.message || 'Product not found.'
	} finally {
		loading.value = false
	}
})

async function submitCart() {
	if (!product.value) return
	message.value = ''
	try {
		await addToCart({ productId: product.value.productId, variantId: selectedVariantId.value, quantity: quantity.value })
		message.value = 'Added to cart.'
	} catch (error) {
		message.value = error.response?.data?.message || 'Please sign in and select an available variant.'
	}
}

function submitFavourite() {
	if (!product.value) return
	toggleStoredFavourite(product.value)
}
</script>

<template>
	<main class="figma-page detail-page">
		<Navbar />
		<section v-if="loading" class="shop-hero"><h1>Loading piece...</h1></section>
		<section v-else-if="product" class="detail-shell">
			<figure class="detail-figure">
				<img class="detail-image" :src="product.image || '/images/products/3241fd30ade02b6c4cd86c65ab23404e.jpg'" :alt="product.productName">
				<figcaption>Tailored in expressive prints with a graceful occasion fit.</figcaption>
			</figure>
			<div class="detail-copy">
				<p class="section-kicker">{{ product.category }}</p>
				<h1>{{ product.productName }}</h1>
				<p>{{ product.description }}</p>
				<strong>€{{ Number(product.price || 0).toFixed(2) }}</strong>
				<div class="detail-badges" aria-label="Product highlights">
					<span>Soft tailoring</span>
					<span>Event ready</span>
					<span>Atelier finish</span>
				</div>

				<label v-if="variants.length">Size and colour
					<select v-model.number="selectedVariantId">
						<option v-for="variant in variants" :key="variant.variantId" :value="variant.variantId">
							{{ variant.size }} / {{ variant.colour }} - {{ variant.stockQuantity }} left
						</option>
					</select>
				</label>
				<p v-else class="status-message">No variants available for this piece yet.</p>

				<label>Quantity<input v-model.number="quantity" type="number" min="1" :max="selectedVariant?.stockQuantity || product.stock || 99"></label>
				<div class="detail-actions">
					<button class="figma-button figma-button-primary" @click="submitCart">Add to bag</button>
					<button
						class="figma-button soft-button heart-action"
						:class="{ active: productIsFavourite }"
						:aria-pressed="productIsFavourite"
						@click="submitFavourite"
					>
						<span class="heart-glyph" aria-hidden="true">{{ productIsFavourite ? '♥' : '♡' }}</span>
						<span>{{ productIsFavourite ? 'Saved' : 'Save' }}</span>
					</button>
				</div>
				<p class="detail-note">Complimentary styling advice is included before your fitting or dispatch.</p>
				<p v-if="message" class="status-message">{{ message }}</p>
			</div>
		</section>
		<section v-else class="shop-hero"><h1>{{ message }}</h1></section>
	</main>
</template>
