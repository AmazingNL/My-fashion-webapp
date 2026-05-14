<script setup>
import { computed } from 'vue'

const props = defineProps({
	product: {
		type: Object,
		required: true,
	},
	fallbackImage: {
		type: String,
		required: true,
	},
	isFavourite: {
		type: Boolean,
		default: false,
	},
})

const emit = defineEmits(['favourite'])

const imageSource = computed(() => props.product.image || props.product.displayImage || props.fallbackImage)

function formatPrice(price) {
	return new Intl.NumberFormat('en-IE', {
		style: 'currency',
		currency: 'EUR',
	}).format(Number(price || 0))
}

function useFallbackImage(event) {
	if (event.currentTarget.src.endsWith(props.fallbackImage)) {
		return
	}

	event.currentTarget.src = props.fallbackImage
}
</script>

<template>
	<article class="product-card">
		<a class="product-media" :href="`/products/${product.productId}`" :aria-label="`View ${product.productName}`">
			<img
				:src="imageSource"
				:alt="product.productName"
				loading="lazy"
				@error="useFallbackImage"
			>
		</a>

		<div class="product-info">
			<div>
				<h3>{{ product.productName }}</h3>
				<p>{{ product.category || 'Signature collection' }}</p>
			</div>

			<div class="product-meta">
				<strong>{{ formatPrice(product.price) }}</strong>
				<span aria-label="Rating">★★★★★</span>
			</div>
			<button
				class="favourite-chip"
				:class="{ active: isFavourite }"
				type="button"
				:aria-pressed="isFavourite"
				:aria-label="isFavourite ? `Remove ${product.productName} from favourites` : `Add ${product.productName} to favourites`"
				@click="emit('favourite', product)"
			>
				<span class="heart-glyph" aria-hidden="true">{{ isFavourite ? '♥' : '♡' }}</span>
				<span>{{ isFavourite ? 'Saved' : 'Save' }}</span>
			</button>
		</div>
	</article>
</template>
