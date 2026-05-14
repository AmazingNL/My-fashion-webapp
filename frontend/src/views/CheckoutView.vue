<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Navbar from '../components/Navbar.vue'
import { confirmPayment, getCheckout, placeOrder } from '../api/checkoutApi'

const route = useRoute()
const router = useRouter()
const checkout = ref({ cartItems: [], total: 0 })
const form = reactive({ shippingAddress: '', billingAddress: '', paymentMethod: 'stripe' })
const message = ref('')
const processing = ref(false)

const paymentOptions = [
	{ value: 'stripe', title: 'Stripe', text: 'Pay securely by card through Stripe Checkout.' },
	{ value: 'paypal', title: 'PayPal', text: 'Use your PayPal account or PayPal-supported cards.' },
	{ value: 'bank_transfer', title: 'Bank transfer', text: 'Place the order now and complete payment offline.' },
]

onMounted(async () => {
	if (route.query.payment === 'success') {
		await confirmReturnedPayment()
		return
	}

	if (route.query.payment === 'cancelled') {
		message.value = 'Payment was cancelled. You can place the order again when ready.'
	}

	try {
		const response = await getCheckout()
		checkout.value = response.data?.data || checkout.value
	} catch (error) {
		message.value = error.response?.data?.message || 'Sign in and add items to checkout.'
	}
})

async function confirmReturnedPayment() {
	processing.value = true
	try {
		const provider = String(route.query.provider || '')
		const paymentReference = provider === 'paypal'
			? String(route.query.token || '')
			: String(route.query.session_id || '')
		const response = await confirmPayment({
			provider,
			paymentReference,
			orderId: Number(route.query.orderId || 0),
		})

		const orderId = response.data?.data?.orderId || route.query.orderId
		message.value = response.data?.message || 'Payment confirmed.'
		if (orderId) router.push(`/orders?success=${orderId}`)
	} catch (error) {
		message.value = error.response?.data?.message || 'Payment confirmation failed.'
	} finally {
		processing.value = false
	}
}

async function submitCheckout() {
	processing.value = true
	try {
		const response = await placeOrder({
			...form,
			returnUrl: `${window.location.origin}/checkout`,
		})
		const orderId = response.data?.data?.orderId
		const redirectUrl = response.data?.data?.payment?.redirectUrl
		message.value = response.data?.message || 'Order placed.'

		if (redirectUrl) {
			window.location.href = redirectUrl
			return
		}

		if (orderId) router.push(`/orders?success=${orderId}`)
	} catch (error) {
		message.value = error.response?.data?.message || 'Checkout failed.'
	} finally {
		processing.value = false
	}
}
</script>

<template>
	<main class="figma-page utility-page">
		<Navbar />
		<section class="shop-hero"><h1>Checkout</h1><p>Complete your order details.</p></section>
		<section class="checkout-shell">
			<form class="panel-form" @submit.prevent="submitCheckout">
				<label>Shipping address<textarea v-model="form.shippingAddress" required></textarea></label>
				<label>Billing address<textarea v-model="form.billingAddress" placeholder="Leave blank to match shipping"></textarea></label>
				<div class="payment-choice-group" aria-label="Payment method">
					<button
						v-for="option in paymentOptions"
						:key="option.value"
						type="button"
						class="payment-choice"
						:class="{ active: form.paymentMethod === option.value }"
						:aria-pressed="form.paymentMethod === option.value"
						@click="form.paymentMethod = option.value"
					>
						<strong>{{ option.title }}</strong>
						<span>{{ option.text }}</span>
					</button>
				</div>
				<button class="figma-button figma-button-primary" :disabled="processing">
					{{ processing ? 'Processing...' : 'Place order' }}
				</button>
				<p v-if="message" class="status-message">{{ message }}</p>
			</form>
			<aside class="checkout-summary"><h2>Order summary</h2><p>{{ checkout.cartItems?.length || 0 }} items</p><strong>€{{ Number(checkout.total || 0).toFixed(2) }}</strong><span>Confirmation email is sent after payment is confirmed.</span></aside>
		</section>
	</main>
</template>
