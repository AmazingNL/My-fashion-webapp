<script setup>
import { reactive, ref } from 'vue'
import { RouterLink } from 'vue-router'
import Navbar from '../components/Navbar.vue'
import { register } from '../stores/authStore'
import { labelClass, fieldClass, primaryBtn } from '../utils/formClasses'

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
	<main class="figma-page">
		<Navbar />
		<section class="grid min-h-[520px] place-items-center gap-[18px] bg-[#fffaf8] px-[22px] py-9 md:px-[92px] md:pt-[34px] md:pb-12">
			<form class="grid w-[min(100%,420px)] gap-[13px]" @submit.prevent="submitRegister">
				<p class="mb-2 text-[0.62rem] font-bold uppercase tracking-[0.08em] text-gold">Join Nuella Signet</p>
				<h1 class="mb-0.5 font-serif text-3xl font-medium leading-[1.08] text-ink">Create your account</h1>

				<div class="grid grid-cols-2 gap-3">
					<label :class="labelClass">First name<input v-model="form.firstName" :class="fieldClass" required></label>
					<label :class="labelClass">Last name<input v-model="form.lastName" :class="fieldClass" required></label>
				</div>
				<label :class="labelClass">Email<input v-model="form.email" type="email" :class="fieldClass" required></label>
				<label :class="labelClass">Phone<input v-model="form.phone" type="tel" :class="fieldClass"></label>
				<label :class="labelClass">Password<input v-model="form.password" type="password" :class="fieldClass" required></label>

				<button :class="primaryBtn" :disabled="loading">Create account</button>
				<p v-if="message" class="text-[0.72rem] font-bold text-rust">{{ message }}</p>
				<RouterLink to="/login" class="text-[0.82rem] font-semibold text-rose hover:underline">Already have an account?</RouterLink>
			</form>
		</section>
	</main>
</template>
