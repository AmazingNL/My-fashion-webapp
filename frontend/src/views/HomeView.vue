<script setup>
import Navbar from '../components/Navbar.vue'

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
</script>

<template>
	<main class="figma-page home-page">
		<Navbar />

		<section class="home-hero" aria-labelledby="home-hero-title">
			<img :src="'/images/products/3241fd30ade02b6c4cd86c65ab23404e.jpg'" alt="Model wearing detailed couture jewelry">
			<div class="home-hero-copy">
				<p>Limited atelier pieces</p>
				<h1 id="home-hero-title">Where Heritage Meets Haute Couture</h1>
				<span>Adorn your story with pieces inspired by African artistry and refined for modern elegance.</span>
				<div class="home-actions">
					<a class="figma-button figma-button-primary" href="/products">Shop now</a>
					<a class="figma-button figma-button-ghost" href="#featured">View story</a>
				</div>
			</div>
		</section>

		<section id="featured" class="featured-section" aria-labelledby="featured-title">
			<p class="section-kicker">Featured Collections</p>
			<h2 id="featured-title">Curated for celebrations, portraits, and everyday elegance</h2>

			<div class="collection-card-row">
				<a v-for="collection in collections" :key="collection.title" class="collection-card" href="/products">
					<img :src="collection.image" :alt="collection.title">
					<span>{{ collection.title }}</span>
				</a>
			</div>
		</section>

		<section id="craft" class="craft-section" aria-labelledby="craft-title">
			<img :src="'/images/products/0d3e4940b879d61f15b964fa9c3f0365.png'" alt="Portrait in a floral occasion outfit">
			<div>
				<p class="section-kicker">Our craft</p>
				<h2 id="craft-title">Crafted with Love, Designed with Purpose</h2>
				<p>
					Every piece balances contemporary tailoring with expressive fabric, thoughtful finishing,
					and personal styling for the woman who wants her outfit to carry meaning.
				</p>
				<div class="craft-stats" aria-label="Atelier statistics">
					<span><strong>150+</strong> pieces made</span>
					<span><strong>37+</strong> events styled</span>
					<span><strong>100%</strong> personal fit</span>
				</div>
			</div>
		</section>

		<section class="join-section" aria-labelledby="join-title">
			<h2 id="join-title">Join Our Community of Style Enthusiasts</h2>
			<p>Receive new drops, fitting dates, and styling notes from Nuella Signet.</p>
			<a class="figma-button figma-button-light" href="mailto:appointments@nuellasignet.com">Join now</a>
		</section>

		<footer id="about" class="figma-footer">
			<div>
				<a class="brand brand-footer" href="/">
					<span class="brand-mark"></span>
					<span>Nuella Signet</span>
				</a>
				<p>Heritage-inspired clothing made for modern celebrations.</p>
			</div>
			<div>
				<h3>Quick links</h3>
				<a href="/products">Shop</a>
				<a href="#craft">Custom Made</a>
				<a href="mailto:appointments@nuellasignet.com">Appointments</a>
			</div>
			<div>
				<h3>Contact us</h3>
				<a href="mailto:appointments@nuellasignet.com">appointments@nuellasignet.com</a>
				<span>Lagos and Dublin</span>
			</div>
		</footer>
	</main>
</template>
