<script setup>
import { reactive, ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import Navbar from '../components/Navbar.vue'
import { login, authState } from '../stores/authStore'
import { requestPasswordReset, verifyPasswordReset } from '../api/authApi'
import { labelClass, fieldClass, primaryBtn } from '../utils/formClasses'

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
	<main class="figma-page">
		<Navbar />
		<section class="grid min-h-[520px] place-items-center gap-[18px] bg-[#fffaf8] px-[22px] py-9 md:px-[92px] md:pt-[34px] md:pb-12">
			<form class="grid w-[min(100%,420px)] gap-[13px]" @submit.prevent="submitLogin">
				<p class="mb-2 text-[0.62rem] font-bold uppercase tracking-[0.08em] text-gold">Welcome back</p>
				<h1 class="mb-0.5 font-serif text-3xl font-medium leading-[1.08] text-ink">Sign in to continue</h1>

				<label :class="labelClass">Email<input v-model="form.email" type="email" :class="fieldClass" required></label>
				<label :class="labelClass">Password<input v-model="form.password" type="password" :class="fieldClass" required></label>

				<button :class="primaryBtn" :disabled="authState.loading">Sign in</button>
				<p v-if="message || authState.error" class="text-[0.72rem] font-bold text-rust">{{ message || authState.error }}</p>
				<p v-if="resetMessage && !showReset" class="text-[0.72rem] font-bold text-rust">{{ resetMessage }}</p>

				<button type="button" class="w-max text-left text-[0.82rem] font-extrabold text-rose hover:underline" @click="openReset">Forgot password?</button>
				<RouterLink to="/register" class="text-[0.82rem] font-semibold text-rose hover:underline">Create a customer account</RouterLink>
			</form>

			<form
				v-if="showReset"
				class="grid w-[min(100%,420px)] gap-[13px] border-t border-line pt-[18px]"
				@submit.prevent="resetStep === 'request' ? submitResetRequest() : submitResetVerify()"
			>
				<p class="mb-2 text-[0.62rem] font-bold uppercase tracking-[0.08em] text-gold">Account recovery</p>
				<h2 class="mb-0.5 font-serif text-2xl font-medium leading-[1.08] text-ink">Reset password</h2>

				<template v-if="resetStep === 'request'">
					<label :class="labelClass">Email<input v-model="resetForm.email" type="email" :class="fieldClass" required></label>
					<button :class="primaryBtn" :disabled="resetting">Send reset code</button>
				</template>
				<template v-else>
					<label :class="labelClass">Reset token<input v-model="resetForm.token" :class="fieldClass" required></label>
					<label :class="labelClass">Code from email<input v-model="resetForm.code" :class="fieldClass" required></label>
					<label :class="labelClass">New password<input v-model="resetForm.newPassword" type="password" :class="fieldClass" required></label>
					<label :class="labelClass">Confirm password<input v-model="resetForm.confirmPassword" type="password" :class="fieldClass" required></label>
					<button :class="primaryBtn" :disabled="resetting">Update password</button>
				</template>
				<p v-if="resetMessage" class="text-[0.72rem] font-bold text-rust">{{ resetMessage }}</p>
			</form>
		</section>
	</main>
</template>
