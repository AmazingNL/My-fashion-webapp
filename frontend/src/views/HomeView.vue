<script setup>
import { RouterLink } from 'vue-router'
import Navbar from '../components/Navbar.vue'
import SiteFooter from '../components/SiteFooter.vue'

const collections = [
	{
		title: 'Urban Collection',
		image: '/images/products/122b577ca585a0dc7aa0e9381f63a58a.png',
	},
	{
		title: 'Rare Collection',
		image: '/images/products/1d7aff0607673a10d849f4d7ea918f7e.png',
	},
	{
		title: 'Noir Collection',
		image: '/images/products/0f35e2b485e40dd6af8ac45281a12bb6.png',
	},
]

const pillBtn = 'inline-flex min-h-[38px] items-center justify-center rounded-full px-[18px] text-[0.84rem] font-bold transition hover:-translate-y-px'
const primaryBtn = `${pillBtn} bg-[linear-gradient(135deg,var(--color-rose),#d46f78)] text-white shadow-[0_10px_22px_rgba(168,62,104,0.2)]`
const ghostBtn = `${pillBtn} border border-white/55 text-white`
</script>

<template>
	<main class="figma-page">
		<Navbar />

		<!-- Hero -->
		<section class="relative min-h-[350px] overflow-hidden bg-[#1f1d1a] text-white" aria-labelledby="home-hero-title">
			<img
				class="h-[350px] w-full scale-[1.18] object-cover object-[50%_16%] [filter:saturate(0.9)_contrast(1.06)_brightness(0.78)]"
				:src="'/images/products/3241fd30ade02b6c4cd86c65ab23404e.jpg'"
				alt="Model wearing detailed couture jewelry"
			>
			<div class="pointer-events-none absolute inset-0 bg-[linear-gradient(90deg,rgba(28,18,12,0.74),rgba(28,18,12,0.3)_48%,rgba(28,18,12,0.04))]"></div>

			<div class="absolute top-[64px] left-[22px] z-[1] w-[calc(100%-44px)] md:top-[76px] md:left-[26%] md:w-[min(360px,52%)]">
				<p class="mb-2 text-[0.76rem] font-bold uppercase tracking-[0.08em] text-gold">Limited atelier pieces</p>
				<h1 id="home-hero-title" class="mb-3 max-w-[300px] font-serif text-[2rem] font-medium leading-[1.02] text-white">Where Heritage Meets Haute Couture</h1>
				<span class="mb-[18px] block max-w-[330px] text-[0.92rem] text-white/80">Adorn your story with pieces inspired by African artistry and refined for modern elegance.</span>
				<div class="flex gap-2">
					<RouterLink to="/products" :class="primaryBtn">Shop now</RouterLink>
					<a href="#featured" :class="ghostBtn">View story</a>
				</div>
			</div>
		</section>

		<!-- Featured collections -->
		<section id="featured" class="px-6 pt-[34px] pb-[42px] text-center" aria-labelledby="featured-title">
			<p class="mb-2 text-[0.76rem] font-bold tracking-[0.08em] text-rust">Featured Collections</p>
			<h2 id="featured-title" class="mx-auto mb-6 max-w-[440px] font-serif text-[1.7rem] font-medium leading-[1.45] text-[#5a514b]">Curated for celebrations, portraits, and everyday elegance</h2>

			<div class="flex flex-wrap justify-center gap-[18px]">
				<RouterLink
					v-for="collection in collections"
					:key="collection.title"
					to="/products"
					class="relative aspect-[0.78] w-[150px] overflow-hidden rounded-[10px] shadow-[0_12px_26px_rgba(0,0,0,0.16)]"
				>
					<img class="h-full w-full object-cover" :src="collection.image" :alt="collection.title">
					<span class="pointer-events-none absolute inset-0 bg-[linear-gradient(180deg,transparent_38%,rgba(0,0,0,0.7))]"></span>
					<span class="absolute right-2.5 bottom-3 left-2.5 z-[1] font-serif text-[0.82rem] text-white">{{ collection.title }}</span>
				</RouterLink>
			</div>
		</section>

		<!-- Craft -->
		<section id="craft" class="grid justify-center gap-7 border-t border-[#f0ece5] bg-white px-6 pt-11 pb-14 md:grid-cols-[230px_420px]" aria-labelledby="craft-title">
			<img
				class="h-[250px] w-[230px] rounded-[10px] object-cover shadow-[0_22px_48px_rgba(121,78,77,0.16)]"
				:src="'/images/products/0d3e4940b879d61f15b964fa9c3f0365.png'"
				alt="Portrait in a floral occasion outfit"
			>
			<div>
				<p class="mb-2 text-[0.76rem] font-bold tracking-[0.08em] text-rust">Our craft</p>
				<h2 id="craft-title" class="mb-3 max-w-[360px] font-serif text-[1.7rem] font-medium leading-[1.1] text-ink">Crafted with Love, Designed with Purpose</h2>
				<p class="mb-[18px] max-w-[420px] text-[0.76rem] text-muted">
					Every piece balances contemporary tailoring with expressive fabric, thoughtful finishing,
					and personal styling for the woman who wants her outfit to carry meaning.
				</p>
				<div class="flex flex-wrap gap-6 text-[0.64rem] text-rust" aria-label="Atelier statistics">
					<span><strong class="block text-base text-rust">150+</strong> pieces made</span>
					<span><strong class="block text-base text-rust">37+</strong> events styled</span>
					<span><strong class="block text-base text-rust">100%</strong> personal fit</span>
				</div>
			</div>
		</section>

		<!-- Join -->
		<section class="bg-[linear-gradient(90deg,#496f5a,#c45f62_52%,#d8967f)] px-6 py-7 text-center text-white" aria-labelledby="join-title">
			<h2 id="join-title" class="mb-1.5 font-serif text-[1.7rem] font-medium text-white">Join Our Community of Style Enthusiasts</h2>
			<p class="mx-auto mb-3.5 max-w-[520px] text-[0.72rem] text-white/80">Receive new drops, fitting dates, and styling notes from Nuella Signet.</p>
			<a class="inline-flex min-h-[38px] items-center justify-center rounded-full bg-white px-[18px] text-[0.84rem] font-bold text-ink transition hover:-translate-y-px" href="mailto:appointments@nuellasignet.com">Join now</a>
		</section>

		<!-- Footer -->
		<SiteFooter />
	</main>
</template>
