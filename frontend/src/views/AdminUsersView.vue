<script setup>
import { onMounted, ref } from 'vue'
import AdminTabs from '../components/AdminTabs.vue'
import Navbar from '../components/Navbar.vue'
import { deleteAdminUser, getAdminUsers } from '../api/adminApi'

const users = ref([])
const message = ref('')

async function loadUsers() {
	try {
		const response = await getAdminUsers()
		users.value = response.data?.data?.users || []
	} catch (error) {
		message.value = error.response?.data?.message || 'Could not load users.'
	}
}

async function removeUser(id) {
	await deleteAdminUser(id)
	await loadUsers()
}

onMounted(loadUsers)
</script>

<template>
	<main class="figma-page admin-page">
		<Navbar />
		<section class="admin-hero-panel">
			<p class="section-kicker">Customers</p>
			<h1>Users Management</h1>
			<p>Keep customer accounts visible, tidy, and easy to scan.</p>
		</section>
		<AdminTabs />
		<p v-if="message" class="admin-message">{{ message }}</p>

		<section class="admin-single-section">
			<div class="admin-card admin-list-card">
				<div class="section-row"><h2>Users</h2><span class="admin-count-chip">{{ users.length }} users</span></div>
				<div class="admin-record-grid">
					<article v-for="user in users" :key="user.userId" class="admin-record-card">
						<div><strong>{{ user.firstName }} {{ user.lastName }}</strong><span>{{ user.email }}</span></div>
						<span class="admin-count-chip">{{ user.role }}</span>
						<button v-if="user.role !== 'admin'" class="text-danger" @click="removeUser(user.userId)">Delete</button>
					</article>
				</div>
			</div>
		</section>
	</main>
</template>
