<script setup>
import { computed, onMounted, ref } from 'vue'
import AdminTabs from '../components/AdminTabs.vue'
import Navbar from '../components/Navbar.vue'
import { getAdminAppointments, getAdminDashboard, getAdminEmails, getAdminOrders, getAdminProducts, getAdminUsers } from '../api/adminApi'

const stats = ref({})
const products = ref([])
const users = ref([])
const orders = ref([])
const appointments = ref([])
const emails = ref([])
const message = ref('')

const latestOrders = computed(() => orders.value.slice(0, 4))
const pendingAppointments = computed(() => appointments.value.filter(appointment => appointment.status === 'pending').slice(0, 4))
const latestProducts = computed(() => products.value.slice(0, 3))

async function loadAdmin() {
	try {
		const [dashboardResponse, productsResponse, usersResponse, ordersResponse, appointmentsResponse, emailsResponse] = await Promise.all([
			getAdminDashboard(),
			getAdminProducts(),
			getAdminUsers(),
			getAdminOrders(),
			getAdminAppointments(),
			getAdminEmails(),
		])
		stats.value = dashboardResponse.data?.data?.stats || {}
		products.value = productsResponse.data?.data?.products || []
		users.value = usersResponse.data?.data?.users || []
		orders.value = ordersResponse.data?.data?.orders || []
		appointments.value = appointmentsResponse.data?.data?.appointments || []
		emails.value = emailsResponse.data?.data?.emails || []
	} catch (error) {
		message.value = error.response?.data?.message || 'Admin access requires an admin login.'
	}
}

onMounted(loadAdmin)
</script>

<template>
	<main class="figma-page admin-page">
		<Navbar />
		<section class="admin-hero-panel">
			<p class="section-kicker">Admin studio</p>
			<h1>Dashboard</h1>
			<p>Manage the boutique from focused workspaces for catalogue, orders, users, and appointments.</p>
		</section>
		<AdminTabs />
		<p v-if="message" class="admin-message">{{ message }}</p>

		<section class="stat-grid dashboard-stat-grid">
			<RouterLink to="/admin/products"><strong>{{ stats.totalProducts || 0 }}</strong><span>Products</span></RouterLink>
			<RouterLink to="/admin/users"><strong>{{ stats.totalUsers || 0 }}</strong><span>Users</span></RouterLink>
			<RouterLink to="/admin/orders"><strong>{{ stats.totalOrders || 0 }}</strong><span>Orders</span></RouterLink>
			<RouterLink to="/admin/appointments"><strong>{{ stats.pendingAppointments || 0 }}</strong><span>Pending appointments</span></RouterLink>
			<RouterLink to="/admin/emails"><strong>{{ emails.length }}</strong><span>Saved emails</span></RouterLink>
		</section>

		<section class="admin-overview-grid">
			<RouterLink class="admin-feature-card" to="/admin/products">
				<span>Catalogue</span>
				<h2>Products Management</h2>
				<p>Add product images, variants, stock, pricing, and edit the live boutique catalogue.</p>
			</RouterLink>
			<RouterLink class="admin-feature-card" to="/admin/orders">
				<span>Fulfilment</span>
				<h2>Orders Management</h2>
				<p>Review orders, inspect items, and move customer purchases through their status flow.</p>
			</RouterLink>
			<RouterLink class="admin-feature-card" to="/admin/users">
				<span>Customers</span>
				<h2>Users Management</h2>
				<p>See customer accounts and keep the user list tidy.</p>
			</RouterLink>
			<RouterLink class="admin-feature-card" to="/admin/appointments">
				<span>Calendar</span>
				<h2>Appointments</h2>
				<p>Create fitting slots and manage consultation bookings.</p>
			</RouterLink>
			<RouterLink class="admin-feature-card" to="/admin/emails">
				<span>Email service</span>
				<h2>Email Log Viewer</h2>
				<p>Preview saved order confirmations, password reset emails, and appointment messages.</p>
			</RouterLink>
		</section>

		<section class="admin-insight-grid">
			<div class="admin-card">
				<h2>Recent orders</h2>
				<div v-for="order in latestOrders" :key="order.orderId" class="mini-record"><strong>#{{ order.orderId }} · €{{ Number(order.totalAmount || 0).toFixed(2) }}</strong><span>{{ order.status }}</span></div>
				<RouterLink class="soft-button" to="/admin/orders">Open orders</RouterLink>
			</div>
			<div class="admin-card">
				<h2>Pending appointments</h2>
				<div v-for="appointment in pendingAppointments" :key="appointment.appointmentId" class="mini-record"><strong>{{ appointment.appointmentDate }} {{ appointment.startTime }}</strong><span>{{ appointment.designType || 'Consultation' }}</span></div>
				<RouterLink class="soft-button" to="/admin/appointments">Open appointments</RouterLink>
			</div>
			<div class="admin-card">
				<h2>Latest products</h2>
				<div v-for="product in latestProducts" :key="product.productId" class="mini-record"><strong>{{ product.productName }}</strong><span>{{ product.category }} · €{{ Number(product.price || 0).toFixed(2) }}</span></div>
				<RouterLink class="soft-button" to="/admin/products">Open products</RouterLink>
			</div>
		</section>
	</main>
</template>
