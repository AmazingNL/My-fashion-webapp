<script setup>
import { reactive, ref } from 'vue'
import Navbar from '../components/Navbar.vue'
import { register } from '../stores/authStore'

const form = reactive({ firstName: '', lastName: '', email: '', phone: '', password: '' })
const loading = ref(false)
const message = ref('')

async function submitRegister() {
	loading.value = true
	message.value = ''
	try {
		await register(form)
		message.value = 'Account created. You can sign in now.'
	} catch (error) {
		message.value = error.response?.data?.message || 'Registration failed.'
	} finally {
		loading.value = false
	}
}
</script>

<template>
	<main class="figma-page auth-page">
		<Navbar />
		<section class="auth-shell">
			<form class="panel-form" @submit.prevent="submitRegister">
				<p class="section-kicker">Join Nuella Signet</p>
				<h1>Create your account</h1>
				<div class="two-col">
					<label>First name<input v-model="form.firstName" required></label>
					<label>Last name<input v-model="form.lastName" required></label>
				</div>
				<label>Email<input v-model="form.email" type="email" required></label>
				<label>Phone<input v-model="form.phone" type="tel"></label>
				<label>Password<input v-model="form.password" type="password" required></label>
				<button class="figma-button figma-button-primary" :disabled="loading">Create account</button>
				<p v-if="message" class="status-message">{{ message }}</p>
				<a href="/login">Already have an account?</a>
			</form>
		</section>
	</main>
</template>
