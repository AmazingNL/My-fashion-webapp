<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { formatMoney } from '../utils/format'

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

function useFallbackImage(event) {
	if (event.currentTarget.src.endsWith(props.fallbackImage)) {
		return
	}

	event.currentTarget.src = props.fallbackImage
}
</script>

<template>
	<article class="group min-w-0">
		<RouterLink
			class="block aspect-[0.76] overflow-hidden rounded-[10px] bg-[#f3efe8]"
			:to="`/products/${product.productId}`"
			:aria-label="`View ${product.productName}`"
		>
			<img
				class="h-full w-full object-cover object-top transition-transform duration-200 group-hover:scale-[1.025]"
				:src="imageSource"
				:alt="product.productName"
				loading="lazy"
				@error="useFallbackImage"
			>
		</RouterLink>

		<div class="pt-2">
			<div>
				<h3 class="mb-0.5 text-[0.68rem] font-bold text-ink">{{ product.productName }}</h3>
				<p class="mb-1 text-[0.62rem] text-muted">{{ product.category || 'Signature collection' }}</p>
			</div>

			<div class="flex items-center justify-between gap-2">
				<strong class="text-[0.68rem] text-ink">{{ formatMoney(product.price) }}</strong>
				<span class="text-[0.62rem] tracking-[0.02em] text-gold" aria-label="Rating">★★★★★</span>
			</div>

			<button
				class="mt-2 inline-flex w-max items-center gap-1.5 rounded-full border px-[11px] py-[7px] text-[0.72rem] font-extrabold transition-colors"
				:class="isFavourite
					? 'border-rose/35 bg-rose text-white'
					: 'border-rose/25 bg-blush text-rose hover:border-rose/40'"
				type="button"
				:aria-pressed="isFavourite"
				:aria-label="isFavourite ? `Remove ${product.productName} from favourites` : `Add ${product.productName} to favourites`"
				@click="emit('favourite', product)"
			>
				<span class="text-base leading-none" aria-hidden="true">{{ isFavourite ? '♥' : '♡' }}</span>
				<span>{{ isFavourite ? 'Saved' : 'Save' }}</span>
			</button>
		</div>
	</article>
</template>
