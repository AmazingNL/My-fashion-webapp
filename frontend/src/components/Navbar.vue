<script setup>
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import { authState, isAdmin, logout } from '../stores/authStore'
import { cartState } from '../stores/cartStore'
import { favouriteState } from '../stores/favouriteStore'

const mobileOpen = ref(false)

const navLinks = [
	{ label: 'Home', to: '/' },
	{ label: 'Shop', to: '/products' },
	{ label: 'Custom Made', to: '/#craft' },
	{ label: 'Orders', to: '/orders' },
]

function closeMenu() {
	mobileOpen.value = false
}

function handleLogout() {
	closeMenu()
	logout()
}
</script>

<template>
	<header
		class="sticky top-0 z-20 border-b border-line bg-paper/95 backdrop-blur"
		aria-label="Primary navigation"
	>
		<div class="mx-auto flex h-12 max-w-[1120px] items-center justify-between gap-4 px-4 md:px-7">
			<RouterLink to="/" class="brand" aria-label="Nuella Signet home" @click="closeMenu">
				<span class="brand-mark"></span>
				<span>Nuella Signet</span>
			</RouterLink>

			<!-- Desktop menu -->
			<nav class="hidden items-center gap-7 text-[0.73rem] text-[#4f4a45] md:flex" aria-label="Main menu">
				<RouterLink
					v-for="link in navLinks"
					:key="link.to"
					:to="link.to"
					class="transition-colors hover:text-rose focus-visible:text-rose"
				>
					{{ link.label }}
				</RouterLink>
				<RouterLink v-if="isAdmin()" to="/admin" class="transition-colors hover:text-rose">Admin</RouterLink>
			</nav>

			<div class="flex items-center gap-3.5" aria-label="Account actions">
				<RouterLink to="/products" class="inline-flex items-center text-ink" aria-label="Search">
					<span class="icon-search"></span>
				</RouterLink>

				<RouterLink to="/products#favourites" class="relative inline-flex items-center" aria-label="Favourites">
					<span class="icon-favourite" aria-hidden="true">♥</span>
					<span
						v-if="favouriteState.items.length"
						class="absolute -top-2.5 -right-3 grid h-[15px] min-w-[15px] place-items-center rounded-full bg-rose px-1 text-[0.55rem] leading-none text-white"
					>{{ favouriteState.items.length }}</span>
				</RouterLink>

				<RouterLink to="/cart" class="relative inline-flex items-center text-ink" aria-label="Shopping bag">
					<span class="icon-bag"></span>
					<span
						v-if="cartState.cart.itemCount"
						class="absolute -top-2.5 -right-2.5 grid h-[15px] min-w-[15px] place-items-center rounded-full bg-rose px-1 text-[0.55rem] leading-none text-white"
					>{{ cartState.cart.itemCount }}</span>
				</RouterLink>

				<!-- Text actions: desktop only -->
				<RouterLink to="/settings" class="hidden text-[0.68rem] font-bold text-ink md:inline">Settings</RouterLink>
				<button
					v-if="authState.user"
					type="button"
					class="hidden text-[0.68rem] font-bold text-ink md:inline"
					@click="handleLogout"
				>Logout</button>
				<RouterLink v-else to="/login" class="hidden text-[0.68rem] font-bold text-ink md:inline">Login</RouterLink>

				<!-- Hamburger: mobile only -->
				<button
					type="button"
					class="inline-flex items-center justify-center p-1 text-ink md:hidden"
					:aria-expanded="mobileOpen"
					aria-label="Toggle menu"
					@click="mobileOpen = !mobileOpen"
				>
					<svg v-if="!mobileOpen" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
						<line x1="3" y1="6" x2="21" y2="6" />
						<line x1="3" y1="12" x2="21" y2="12" />
						<line x1="3" y1="18" x2="21" y2="18" />
					</svg>
					<svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
						<line x1="6" y1="6" x2="18" y2="18" />
						<line x1="6" y1="18" x2="18" y2="6" />
					</svg>
				</button>
			</div>
		</div>

		<!-- Mobile slide-down panel -->
		<nav
			v-if="mobileOpen"
			class="border-t border-line bg-paper px-4 py-3 md:hidden"
			aria-label="Mobile menu"
		>
			<RouterLink
				v-for="link in navLinks"
				:key="link.to"
				:to="link.to"
				class="block rounded-lg px-2 py-2.5 text-sm font-semibold text-ink transition-colors hover:bg-blush hover:text-rose"
				@click="closeMenu"
			>
				{{ link.label }}
			</RouterLink>
			<RouterLink
				v-if="isAdmin()"
				to="/admin"
				class="block rounded-lg px-2 py-2.5 text-sm font-semibold text-ink transition-colors hover:bg-blush hover:text-rose"
				@click="closeMenu"
			>Admin</RouterLink>

			<hr class="my-2 border-line" />

			<RouterLink
				to="/settings"
				class="block rounded-lg px-2 py-2.5 text-sm font-semibold text-ink transition-colors hover:bg-blush hover:text-rose"
				@click="closeMenu"
			>Settings</RouterLink>
			<button
				v-if="authState.user"
				type="button"
				class="block w-full rounded-lg px-2 py-2.5 text-left text-sm font-semibold text-ink transition-colors hover:bg-blush hover:text-rose"
				@click="handleLogout"
			>Logout</button>
			<RouterLink
				v-else
				to="/login"
				class="block rounded-lg px-2 py-2.5 text-sm font-semibold text-ink transition-colors hover:bg-blush hover:text-rose"
				@click="closeMenu"
			>Login</RouterLink>
		</nav>
	</header>
</template>
