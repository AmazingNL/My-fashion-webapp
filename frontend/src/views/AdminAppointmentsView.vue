<script setup>
import { onMounted, reactive, ref } from 'vue'
import AdminTabs from '../components/AdminTabs.vue'
import Navbar from '../components/Navbar.vue'
import { createAdminAppointmentSlot, getAdminAppointments, updateAdminAppointmentStatus } from '../api/adminApi'

const appointments = ref([])
const message = ref('')
const slotForm = reactive({ appointmentDate: '', startTime: '10:00', endTime: '10:30', bulkMonth: '0', secondStartTime: '', secondEndTime: '' })

async function loadAppointments() {
	try {
		const response = await getAdminAppointments()
		appointments.value = response.data?.data?.appointments || []
	} catch (error) {
		message.value = error.response?.data?.message || 'Could not load appointments.'
	}
}

async function setAppointmentStatus(id, status) {
	await updateAdminAppointmentStatus(id, status)
	await loadAppointments()
}

async function submitSlot() {
	try {
		await createAdminAppointmentSlot(slotForm)
		message.value = 'Slot created.'
		await loadAppointments()
	} catch (error) {
		message.value = error.response?.data?.message || 'Slot creation failed.'
	}
}

onMounted(loadAppointments)
</script>

<template>
	<main class="figma-page admin-page">
		<Navbar />
		<section class="admin-hero-panel">
			<p class="section-kicker">Studio calendar</p>
			<h1>Appointments Management</h1>
			<p>Create fitting slots and manage customer consultation statuses.</p>
		</section>
		<AdminTabs />
		<p v-if="message" class="admin-message">{{ message }}</p>

		<section class="admin-workspace-grid">
			<form class="admin-card panel-form" @submit.prevent="submitSlot">
				<h2>Add appointment slot</h2>
				<label>Date<input v-model="slotForm.appointmentDate" type="date" required></label>
				<div class="two-col"><label>Start<input v-model="slotForm.startTime" type="time"></label><label>End<input v-model="slotForm.endTime" type="time"></label></div>
				<label class="check-row"><input v-model="slotForm.bulkMonth" true-value="1" false-value="0" type="checkbox"><span>Create monthly slots</span></label>
				<button class="figma-button figma-button-primary">Create slot</button>
			</form>

			<div class="admin-card admin-list-card">
				<div class="section-row"><h2>Appointments</h2><span class="admin-count-chip">{{ appointments.length }} bookings</span></div>
				<div class="data-table appointments-management-table">
					<div class="table-row table-head"><span>Date</span><span>Design</span><span>Status</span><span>Update</span></div>
					<div v-for="appointment in appointments" :key="appointment.appointmentId" class="table-row">
						<span>{{ appointment.appointmentDate }} {{ appointment.startTime }}</span>
						<span>{{ appointment.designType || 'Consultation' }}</span>
						<span>{{ appointment.status }}</span>
						<select :value="appointment.status" @change="setAppointmentStatus(appointment.appointmentId, $event.target.value)"><option>pending</option><option>confirmed</option><option>completed</option><option>cancelled</option></select>
					</div>
				</div>
			</div>
		</section>
	</main>
</template>
