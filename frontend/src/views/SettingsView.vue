<script setup>
import Navbar from '../components/Navbar.vue'
import { setTheme, themeState } from '../stores/themeStore'

const themeOptions = [
	{
		value: 'bright',
		title: 'Bright theme',
		description: 'Soft blush, warm paper, and clear daytime contrast.',
	},
	{
		value: 'dark',
		title: 'Dark theme',
		description: 'Deep boutique tones with rose accents for evening browsing.',
	},
]
</script>

<template>
	<main class="figma-page utility-page">
		<Navbar />
		<section class="admin-hero-panel settings-hero">
			<p class="section-kicker">Preferences</p>
			<h1>User Settings</h1>
			<p>Choose the visual theme that feels best for browsing, shopping, and managing your account.</p>
		</section>

		<section class="settings-shell">
			<div class="admin-card settings-card">
				<div class="section-row">
					<h2>Appearance</h2>
					<span class="admin-count-chip">{{ themeState.theme === 'dark' ? 'Dark' : 'Bright' }}</span>
				</div>
				<div class="theme-choice-grid" role="radiogroup" aria-label="Theme selection">
					<button
						v-for="option in themeOptions"
						:key="option.value"
						type="button"
						class="theme-choice"
						:class="{ active: themeState.theme === option.value }"
						:aria-checked="themeState.theme === option.value"
						role="radio"
						@click="setTheme(option.value)"
					>
						<span class="theme-swatch" :class="`theme-swatch-${option.value}`"></span>
						<strong>{{ option.title }}</strong>
						<small>{{ option.description }}</small>
					</button>
				</div>
			</div>
		</section>
	</main>
</template>
