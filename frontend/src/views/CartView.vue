<script setup>
import { onMounted } from 'vue'
import Navbar from '../components/Navbar.vue'
import { cartState, emptyCart, loadCart, removeFromCart, updateQuantity } from '../stores/cartStore'

onMounted(() => loadCart().catch(() => {}))
</script>

<template>
	<main class="figma-page utility-page">
		<Navbar />
		<section class="shop-hero"><h1>Your Cart</h1><p>Review your pieces before checkout.</p></section>
		<section class="table-shell">
			<p v-if="cartState.error" class="status-message">{{ cartState.error }}</p>
			<article v-for="item in cartState.cart.items" :key="`${item.productId}-${item.variantId}`" class="line-item">
				<img :src="item.image || '/images/products/3241fd30ade02b6c4cd86c65ab23404e.jpg'" :alt="item.name">
				<div><h2>{{ item.name }}</h2><p>{{ item.size }} {{ item.color || item.colour }}</p></div>
				<input :value="item.quantity" type="number" min="1" @change="updateQuantity({ productId: item.productId, variantId: item.variantId, quantity: Number($event.target.value) })">
				<strong>€{{ Number(item.subtotal || item.price || 0).toFixed(2) }}</strong>
				<button class="text-danger" @click="removeFromCart({ productId: item.productId, variantId: item.variantId, quantity: 0 })">Remove</button>
			</article>
			<div v-if="cartState.cart.items.length" class="summary-bar">
				<button class="soft-button" @click="emptyCart">Clear cart</button>
				<strong>Total €{{ Number(cartState.cart.total || 0).toFixed(2) }}</strong>
				<a class="figma-button figma-button-primary" href="/checkout">Checkout</a>
			</div>
			<p v-else class="empty-state">Your cart is empty.</p>
		</section>
	</main>
</template>
