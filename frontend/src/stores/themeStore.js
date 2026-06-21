import { reactive } from 'vue'

const savedTheme = localStorage.getItem('theme') || 'bright'

export const themeState = reactive({
	theme: savedTheme === 'dark' ? 'dark' : 'bright',
})

export function applyTheme(theme = themeState.theme) {
	const nextTheme = theme === 'dark' ? 'dark' : 'bright'
	themeState.theme = nextTheme
	document.documentElement.dataset.theme = nextTheme
	localStorage.setItem('theme', nextTheme)
}

export function setTheme(theme) {
	applyTheme(theme)
}

applyTheme(themeState.theme)
