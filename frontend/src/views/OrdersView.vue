<script setup>
import { onMounted, reactive, ref } from 'vue'
import Navbar from '../components/Navbar.vue'
import { cancelOrder, getOrder, getOrders } from '../api/orderApi'
import { bookAppointment, cancelAppointment, getAppointments, getAppointmentSlots } from '../api/appointmentApi'

const orders = ref([])
const orderItemsById = reactive({})
const expandedOrderId = ref(null)
const appointments = ref([])
const slots = ref([])
const message = ref('')
const filters = reactive({ status: '', q: '' })
const appointmentForm = reactive({ date: '', slotId: 0, designType: '', notes: '' })

async function loadOrders() {
	try {
		const response = await getOrders(filters)
		orders.value = response.data?.data?.orders || []
	} catch (error) {
		message.value = error.response?.data?.message || 'Sign in to view orders.'
	}
}

async function loadAppointments() {
	try {
		const response = await getAppointments()
		appointments.value = response.data?.data?.appointments || []
	} catch {
		appointments.value = []
	}
}

async function loadSlots() {
	if (!appointmentForm.date) return
	const response = await getAppointmentSlots(appointmentForm.date)
	slots.value = response.data?.data?.slots || []
	appointmentForm.slotId = slots.value[0]?.slotId || 0
}

async function submitAppointment() {
	try {
		await bookAppointment({ slotId: appointmentForm.slotId, designType: appointmentForm.designType, notes: appointmentForm.notes })
		message.value = 'Appointment booked.'
		await loadAppointments()
	} catch (error) {
		message.value = error.response?.data?.message || 'Could not book appointment.'
	}
}

async function submitCancelOrder(id) {
	await cancelOrder(id)
	await loadOrders()
}

async function toggleOrderItems(id) {
	if (expandedOrderId.value === id) {
		expandedOrderId.value = null
		return
	}

	expandedOrderId.value = id
	if (orderItemsById[id]) return

	try {
		const response = await getOrder(id)
		orderItemsById[id] = response.data?.data?.items || []
	} catch (error) {
		message.value = error.response?.data?.message || 'Could not load order items.'
		orderItemsById[id] = []
	}
}

async function submitCancelAppointment(id) {
	await cancelAppointment(id)
	await loadAppointments()
}

onMounted(() => {
	loadOrders()
	loadAppointments()
})
</script>

<template>
	<main class="figma-page utility-page">
		<Navbar />
		<section class="shop-hero"><h1>Orders and Appointments</h1><p>Track purchases and fitting sessions.</p></section>
		<section class="customer-orders-section">
			<div class="admin-card orders-card">
				<div class="section-row">
					<h2>My orders</h2>
					<form class="inline-tools" @submit.prevent="loadOrders">
						<input v-model="filters.q" placeholder="Search orders">
						<select v-model="filters.status"><option value="">All statuses</option><option>pending</option><option>processing</option><option>shipped</option><option>delivered</option><option>cancelled</option></select>
						<button class="soft-button">Filter</button>
					</form>
				</div>
				<div class="data-table">
					<div class="table-row table-head"><span>Order</span><span>Status</span><span>Total</span><span>Action</span></div>
					<div v-for="order in orders" :key="order.orderId" class="order-table-block">
						<div class="table-row">
							<span>#{{ order.orderId }}</span>
							<span>{{ order.status }}</span>
							<span>€{{ Number(order.totalAmount || 0).toFixed(2) }}</span>
							<span class="row-actions">
								<button @click="toggleOrderItems(order.orderId)">{{ expandedOrderId === order.orderId ? 'Hide items' : 'View items' }}</button>
								<button class="text-danger" @click="submitCancelOrder(order.orderId)">Cancel</button>
							</span>
						</div>
						<div v-if="expandedOrderId === order.orderId" class="order-items-panel">
							<div v-if="orderItemsById[order.orderId]?.length" class="order-item-grid">
								<div v-for="item in orderItemsById[order.orderId]" :key="item.orderItemId" class="order-item-card">
									<strong>Product #{{ item.productId }}</strong>
									<span>Variant #{{ item.variantId }}</span>
									<span>Quantity: {{ item.quantity }}</span>
									<span>Price: €{{ Number(item.price || 0).toFixed(2) }}</span>
								</div>
							</div>
							<p v-else class="empty-state">No items found for this order.</p>
						</div>
					</div>
				</div>
			</div>
		</section>

		<section class="appointments-section" aria-labelledby="appointments-title">
			<div class="section-heading-soft">
				<p class="section-kicker">Appointments</p>
				<h2 id="appointments-title">Fittings and consultations</h2>
			</div>
			<form class="admin-card panel-form" @submit.prevent="submitAppointment">
				<h2>Book fitting</h2>
				<label>Date<input v-model="appointmentForm.date" type="date" @change="loadSlots"></label>
				<label>Available slot<select v-model.number="appointmentForm.slotId"><option v-for="slot in slots" :key="slot.slotId" :value="slot.slotId">{{ slot.startTime }} - {{ slot.endTime }}</option></select></label>
				<label>Design type<input v-model="appointmentForm.designType" placeholder="Wedding dress, aso ebi, custom suit"></label>
				<label>Notes<textarea v-model="appointmentForm.notes"></textarea></label>
				<button class="figma-button figma-button-primary">Book appointment</button>
				<p v-if="message" class="status-message">{{ message }}</p>
			</form>

			<div class="admin-card">
				<h2>My appointments</h2>
				<div v-for="appointment in appointments" :key="appointment.appointmentId" class="mini-record">
					<strong>{{ appointment.appointmentDate || 'Scheduled' }}</strong>
					<span>{{ appointment.startTime }} {{ appointment.status }}</span>
					<button class="text-danger" @click="submitCancelAppointment(appointment.appointmentId)">Cancel</button>
				</div>
			</div>
		</section>
	</main>
</template>
