<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import Navbar from '../components/Navbar.vue'
import { login, authState } from '../stores/authStore'
import { requestPasswordReset, verifyPasswordReset } from '../api/authApi'

const router = useRouter()
const form = reactive({ email: '', password: '' })
const resetForm = reactive({ email: '', token: '', code: '', newPassword: '', confirmPassword: '' })
const message = ref('')
const resetMessage = ref('')
const resetStep = ref('request')
const showReset = ref(false)
const resetting = ref(false)

async function submitLogin() {
	message.value = ''
	try {
		await login(form)
		router.push(authState.user?.role === 'admin' ? '/admin' : '/products')
	} catch (error) {
		message.value = error.response?.data?.message || 'Unable to sign in.'
	}
}

function openReset() {
	showReset.value = true
	resetStep.value = 'request'
	resetMessage.value = ''
	resetForm.email = form.email
}

async function submitResetRequest() {
	resetting.value = true
	resetMessage.value = ''
	try {
		const response = await requestPasswordReset({ email: resetForm.email })
		resetForm.token = response.data?.data?.token || ''
		resetStep.value = 'verify'
		resetMessage.value = response.data?.message || 'Verification code sent. Check your email.'
	} catch (error) {
		resetMessage.value = error.response?.data?.message || 'Could not send reset code.'
	} finally {
		resetting.value = false
	}
}

async function submitResetVerify() {
	resetting.value = true
	resetMessage.value = ''
	try {
		const response = await verifyPasswordReset(resetForm)
		resetMessage.value = response.data?.message || 'Password updated. Please log in.'
		resetStep.value = 'request'
		showReset.value = false
		form.email = resetForm.email
		form.password = ''
		Object.assign(resetForm, { email: resetForm.email, token: '', code: '', newPassword: '', confirmPassword: '' })
	} catch (error) {
		resetMessage.value = error.response?.data?.message || 'Could not reset password.'
	} finally {
		resetting.value = false
	}
}
</script>

<template>
	<main class="figma-page auth-page">
		<Navbar />
		<section class="auth-shell">
			<form class="panel-form" @submit.prevent="submitLogin">
				<p class="section-kicker">Welcome back</p>
				<h1>Sign in to continue</h1>
				<label>Email<input v-model="form.email" type="email" required></label>
				<label>Password<input v-model="form.password" type="password" required></label>
				<button class="figma-button figma-button-primary" :disabled="authState.loading">Sign in</button>
				<p v-if="message || authState.error" class="status-message">{{ message || authState.error }}</p>
				<p v-if="resetMessage && !showReset" class="status-message">{{ resetMessage }}</p>
				<button class="text-link-button" type="button" @click="openReset">Forgot password?</button>
				<a href="/register">Create a customer account</a>
			</form>

			<form v-if="showReset" class="panel-form reset-panel" @submit.prevent="resetStep === 'request' ? submitResetRequest() : submitResetVerify()">
				<p class="section-kicker">Account recovery</p>
				<h2>Reset password</h2>
				<template v-if="resetStep === 'request'">
					<label>Email<input v-model="resetForm.email" type="email" required></label>
					<button class="figma-button figma-button-primary" :disabled="resetting">Send reset code</button>
				</template>
				<template v-else>
					<label>Reset token<input v-model="resetForm.token" required></label>
					<label>Code from email<input v-model="resetForm.code" required></label>
					<label>New password<input v-model="resetForm.newPassword" type="password" required></label>
					<label>Confirm password<input v-model="resetForm.confirmPassword" type="password" required></label>
					<button class="figma-button figma-button-primary" :disabled="resetting">Update password</button>
				</template>
				<p v-if="resetMessage" class="status-message">{{ resetMessage }}</p>
			</form>
		</section>
	</main>
</template>
