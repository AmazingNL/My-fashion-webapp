<script setup>
import { onMounted, reactive, ref } from 'vue'
import AdminTabs from '../components/AdminTabs.vue'
import Navbar from '../components/Navbar.vue'
import { getAdminOrderItems, getAdminOrders, updateAdminOrderStatus } from '../api/adminApi'
import { formatMoney } from '../utils/format'

const orders = ref([])
const adminOrderItemsById = reactive({})
const expandedAdminOrderId = ref(null)
const message = ref('')

async function loadOrders() {
	try {
		const response = await getAdminOrders()
		orders.value = response.data?.data?.orders || []
	} catch (error) {
		message.value = error.response?.data?.message || 'Could not load orders.'
	}
}

async function setOrderStatus(id, status) {
	await updateAdminOrderStatus(id, status)
	await loadOrders()
}

async function toggleAdminOrderItems(id) {
	if (expandedAdminOrderId.value === id) {
		expandedAdminOrderId.value = null
		return
	}

	expandedAdminOrderId.value = id
	if (adminOrderItemsById[id]) return

	try {
		const response = await getAdminOrderItems(id)
		adminOrderItemsById[id] = response.data?.data?.items || []
	} catch (error) {
		message.value = error.response?.data?.message || 'Could not load order items.'
		adminOrderItemsById[id] = []
	}
}

onMounted(loadOrders)
</script>

<template>
	<main class="figma-page admin-page">
		<Navbar />
		<section class="admin-hero-panel">
			<p class="section-kicker">Fulfilment</p>
			<h1>Orders Management</h1>
			<p>Review customer orders, update statuses, and open each order to inspect its items.</p>
		</section>
		<AdminTabs />
		<p v-if="message" class="admin-message">{{ message }}</p>

		<section class="admin-single-section">
			<div class="admin-card orders-management-card">
				<div class="section-row"><h2>Orders</h2><span class="admin-count-chip">{{ orders.length }} orders</span></div>
				<div class="data-table">
					<div class="table-row table-head"><span>Order</span><span>Total</span><span>Status</span><span>Update</span></div>
					<div v-for="order in orders" :key="order.orderId" class="order-table-block">
						<div class="table-row">
							<span>#{{ order.orderId }}</span>
							<span>{{ formatMoney(order.totalAmount) }}</span>
							<span>{{ order.status }}</span>
							<span class="row-actions">
								<select :value="order.status" @change="setOrderStatus(order.orderId, $event.target.value)"><option>pending</option><option>processing</option><option>shipped</option><option>delivered</option><option>cancelled</option></select>
								<button @click="toggleAdminOrderItems(order.orderId)">{{ expandedAdminOrderId === order.orderId ? 'Hide items' : 'View items' }}</button>
							</span>
						</div>
						<div v-if="expandedAdminOrderId === order.orderId" class="order-items-panel">
							<div v-if="adminOrderItemsById[order.orderId]?.length" class="order-item-grid">
								<div v-for="item in adminOrderItemsById[order.orderId]" :key="item.orderItemId" class="order-item-card">
									<strong>Product #{{ item.productId }}</strong>
									<span>Variant #{{ item.variantId }}</span>
									<span>Quantity: {{ item.quantity }}</span>
									<span>Price: {{ formatMoney(item.price) }}</span>
								</div>
							</div>
							<p v-else class="empty-state">No items found for this order.</p>
						</div>
					</div>
				</div>
			</div>
		</section>
	</main>
</template>
