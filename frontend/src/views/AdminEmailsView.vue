<script setup>
import { computed, onMounted, ref } from 'vue'
import AdminTabs from '../components/AdminTabs.vue'
import Navbar from '../components/Navbar.vue'
import { getAdminEmail, getAdminEmails } from '../api/adminApi'

const emails = ref([])
const selectedEmail = ref(null)
const selectedFileName = ref('')
const message = ref('')
const loading = ref(true)
const previewMode = ref('preview')

const emailCount = computed(() => emails.value.length)

async function loadEmails() {
	loading.value = true
	message.value = ''

	try {
		const response = await getAdminEmails()
		emails.value = response.data?.data?.emails || []

		if (emails.value.length) {
			await openEmail(emails.value[0].fileName)
		}
	} catch (error) {
		message.value = error.response?.data?.message || 'Could not load saved emails.'
	} finally {
		loading.value = false
	}
}

async function openEmail(fileName) {
	selectedFileName.value = fileName
	message.value = ''

	try {
		const response = await getAdminEmail(fileName)
		selectedEmail.value = response.data?.data?.email || null
	} catch (error) {
		selectedEmail.value = null
		message.value = error.response?.data?.message || 'Could not open email.'
	}
}

function formatSize(size) {
	const value = Number(size || 0)
	return value < 1024 ? `${value} B` : `${(value / 1024).toFixed(1)} KB`
}

onMounted(loadEmails)
</script>

<template>
	<main class="figma-page admin-page">
		<Navbar />
		<section class="admin-hero-panel">
			<p class="section-kicker">Email service</p>
			<h1>Email Log Viewer</h1>
			<p>Open the confirmation, password reset, and appointment emails saved by the app.</p>
		</section>
		<AdminTabs />
		<p v-if="message" class="admin-message">{{ message }}</p>

		<section class="email-service-shell">
			<aside class="admin-card email-list-panel">
				<div class="section-row">
					<h2>Saved emails</h2>
					<span class="admin-count-chip">{{ emailCount }} emails</span>
				</div>
				<p v-if="loading" class="status-message">Loading emails...</p>
				<div v-else-if="emails.length" class="email-log-list">
					<button
						v-for="email in emails"
						:key="email.fileName"
						type="button"
						class="email-log-item"
						:class="{ active: selectedFileName === email.fileName }"
						@click="openEmail(email.fileName)"
					>
						<strong>{{ email.subject || 'No subject' }}</strong>
						<span>{{ email.to || 'No recipient' }}</span>
						<small>{{ email.createdAt }} · {{ formatSize(email.size) }}</small>
					</button>
				</div>
				<p v-else class="empty-state">No saved emails yet.</p>
			</aside>

			<section class="admin-card email-preview-panel">
				<div v-if="selectedEmail" class="email-preview-header">
					<div>
						<h2>{{ selectedEmail.subject || 'No subject' }}</h2>
						<p>{{ selectedEmail.to || 'No recipient' }}</p>
						<span>{{ selectedEmail.fileName }}</span>
					</div>
					<div class="segmented-actions" aria-label="Email view mode">
						<button type="button" :class="{ active: previewMode === 'preview' }" @click="previewMode = 'preview'">Preview</button>
						<button type="button" :class="{ active: previewMode === 'source' }" @click="previewMode = 'source'">Source</button>
					</div>
				</div>

				<iframe
					v-if="selectedEmail && previewMode === 'preview'"
					class="email-frame"
					:title="selectedEmail.subject || 'Email preview'"
					:srcdoc="selectedEmail.html"
				></iframe>
				<pre v-else-if="selectedEmail" class="email-source">{{ selectedEmail.raw }}</pre>
				<p v-else class="empty-state">Select an email to preview it.</p>
			</section>
		</section>
	</main>
</template>
