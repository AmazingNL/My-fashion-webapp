<script setup>
import { authState, isAdmin, logout } from '../stores/authStore'
import { cartState } from '../stores/cartStore'
import { favouriteState } from '../stores/favouriteStore'
</script>

<template>
	<header class="site-header" aria-label="Primary navigation">
		<a class="brand" href="/" aria-label="Nuella Signet home">
			<span class="brand-mark"></span>
			<span>Nuella Signet</span>
		</a>

		<nav class="nav-links" aria-label="Main menu">
			<a href="/">Home</a>
			<a href="/products">Shop</a>
			<a href="/#craft">Custom Made</a>
			<a href="/orders">Orders</a>
			<a v-if="isAdmin()" href="/admin">Admin</a>
		</nav>

		<div class="nav-actions" aria-label="Account actions">
			<a href="/products" aria-label="Search"><span class="icon-search"></span></a>
			<a href="/products#favourites" aria-label="Favourites" class="favourite-link">
				<span class="icon-favourite" aria-hidden="true">♥</span>
				<span v-if="favouriteState.items.length" class="bag-count nav-heart-count">{{ favouriteState.items.length }}</span>
			</a>
			<a href="/cart" aria-label="Shopping bag" class="bag-link">
				<span class="icon-bag"></span>
				<span v-if="cartState.cart.itemCount" class="bag-count">{{ cartState.cart.itemCount }}</span>
			</a>
			<a class="text-action" href="/settings">Settings</a>
			<button v-if="authState.user" type="button" class="text-action" @click="logout">Logout</button>
			<a v-else class="text-action" href="/login">Login</a>
		</div>
	</header>
</template>
