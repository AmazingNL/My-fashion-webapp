<script setup>
import { RouterLink } from 'vue-router'
</script>

<template>
	<footer id="about" class="grid gap-[42px] bg-charcoal px-6 py-[30px] text-white/70 md:grid-cols-[1.4fr_1fr_1fr] md:px-[92px]">
		<div>
			<RouterLink to="/" class="brand mb-2.5 text-white">
				<span class="brand-mark"></span>
				<span>Nuella Signet</span>
			</RouterLink>
			<p class="mb-1.5 text-[0.66rem] text-white/65">Heritage-inspired clothing made for modern celebrations.</p>
		</div>
		<div>
			<h3 class="mb-2.5 text-[0.72rem] text-white">Quick links</h3>
			<RouterLink to="/products" class="mb-1.5 block text-[0.66rem] text-white/65 hover:text-white">Shop</RouterLink>
			<a href="/#craft" class="mb-1.5 block text-[0.66rem] text-white/65 hover:text-white">Custom Made</a>
			<a href="mailto:appointments@nuellasignet.com" class="mb-1.5 block text-[0.66rem] text-white/65 hover:text-white">Appointments</a>
		</div>
		<div>
			<h3 class="mb-2.5 text-[0.72rem] text-white">Contact us</h3>
			<a href="mailto:appointments@nuellasignet.com" class="mb-1.5 block text-[0.66rem] text-white/65 hover:text-white">appointments@nuellasignet.com</a>
			<span class="mb-1.5 block text-[0.66rem] text-white/65">Lagos and Dublin</span>
		</div>
	</footer>
</template>
