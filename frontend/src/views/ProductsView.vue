<script setup>
import { computed, onMounted, ref } from 'vue'
import { getProducts } from '../api/productApi'
import { clearStoredFavourites, favouriteState, isFavourite, toggleStoredFavourite } from '../stores/favouriteStore'
import Navbar from '../components/Navbar.vue'
import ProductCard from '../components/ProductCard.vue'
import SiteFooter from '../components/SiteFooter.vue'

const products = ref([])
const loading = ref(true)
const error = ref('')
const selectedCategory = ref('All')
const searchTerm = ref('')
const favourites = computed(() => favouriteState.items)

const fallbackImages = [
    '/images/products/0d3e4940b879d61f15b964fa9c3f0365.png',
    '/images/products/3241fd30ade02b6c4cd86c65ab23404e.jpg',
    '/images/products/0f35e2b485e40dd6af8ac45281a12bb6.png',
    '/images/products/3241fd30ade02b6c4cd86c65ab23404e.jpg',
]

const fallbackProducts = [
    {
        productId: 'sample-1',
        productName: 'Bloom Ankara Peplum Gown',
        description: 'A sculpted floral occasion set with matching gele styling.',
        price: 299.99,
        category: 'Evening Wear',
        stock: 15,
        image: fallbackImages[0],
    },
    {
        productId: 'sample-2',
        productName: 'Royal Lace Ceremony Dress',
        description: 'Intricate blue lace with a fitted formal silhouette.',
        price: 349.99,
        category: 'Bridal Guest',
        stock: 8,
        image: fallbackImages[1],
    },
    {
        productId: 'sample-3',
        productName: 'Heritage Print Column Dress',
        description: 'Vibrant print tailoring for receptions, birthdays, and galas.',
        price: 189.99,
        category: 'Cocktail',
        stock: 12,
        image: fallbackImages[2],
    },
    {
        productId: 'sample-4',
        productName: 'Signature Occasion Ensemble',
        description: 'A polished made-to-measure look with coordinated headwrap.',
        price: 249.99,
        category: 'Custom',
        stock: 6,
        image: fallbackImages[3],
    },
]

const displayProducts = computed(() => {
    const source = products.value.length ? products.value : fallbackProducts

    return source.map((product, index) => ({
        ...product,
        displayImage: product.image || fallbackImages[index % fallbackImages.length],
    }))
})
const categories = computed(() => ['All', ...new Set(displayProducts.value.map(product => product.category).filter(Boolean))])
const filteredProducts = computed(() => {
    const term = searchTerm.value.trim().toLowerCase()

    return displayProducts.value.filter(product => {
        const matchesCategory = selectedCategory.value === 'All' || product.category === selectedCategory.value
        const matchesSearch = !term || [product.productName, product.category, product.description]
            .filter(Boolean)
            .some(value => String(value).toLowerCase().includes(term))

        return matchesCategory && matchesSearch
    })
})

const filterGroups = [
    {
        label: 'Collections',
        options: ['New In', 'Best Sellers', 'Wedding Guest', 'Custom Made'],
    },
    {
        label: 'Color',
        options: ['Emerald', 'Rust', 'Gold', 'Black'],
    },
    {
        label: 'Price',
        options: ['Under €150', '€150 - €300', 'Above €300'],
    },
]

onMounted(async () => {
    try {
        const response = await getProducts()
        const payload = response.data?.data?.products ?? response.data?.products ?? []

        products.value = Array.isArray(payload) ? payload : []
    } catch (err) {
        console.error(err)
        error.value = 'Live collection is unavailable, showing the preview collection.'
    } finally {
        loading.value = false
    }
})

function submitFavourite(product) {
    toggleStoredFavourite(product)
}

function submitClearFavourites() {
    clearStoredFavourites()
}

</script>

<template>
    <main class="figma-page products-page">
        <Navbar />

        <section class="shop-hero" aria-labelledby="collection-title">
            <h1 id="collection-title">Shop Our Collection</h1>
            <p>Find your perfect occasion piece from our heritage-inspired edits.</p>
        </section>

        <section class="shop-shell" aria-label="Product collection">
            <aside class="filter-sidebar" aria-label="Collection filters">
                <h2>Filters</h2>

                <label class="search-control">
                    <span>Search</span>
                    <input v-model="searchTerm" type="search" placeholder="Search styles">
                </label>

                <div class="filter-block">
                    <h3>Categories</h3>
                    <button
                        v-for="category in categories"
                        :key="category"
                        type="button"
                        :class="{ active: selectedCategory === category }"
                        @click="selectedCategory = category"
                    >
                        {{ category }}
                    </button>
                </div>

                <div v-for="group in filterGroups" :key="group.label" class="filter-block">
                    <h3>{{ group.label }}</h3>
                    <label v-for="option in group.options" :key="option" class="check-row">
                        <input type="checkbox">
                        <span>{{ option }}</span>
                    </label>
                </div>
            </aside>

            <div class="shop-content">
                <div class="shop-toolbar">
                    <p>{{ filteredProducts.length }} items</p>
                    <select aria-label="Sort products">
                        <option>Featured</option>
                        <option>Newest</option>
                        <option>Price low to high</option>
                    </select>
                </div>

                <p v-if="loading" class="status-message">Loading collection...</p>
                <p v-else-if="error" class="status-message">{{ error }}</p>

                <div class="grid grid-cols-1 gap-x-[18px] gap-y-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <ProductCard
                        v-for="(product, index) in filteredProducts"
                        :key="product.productId || product.productName"
                        :product="product"
                        :fallback-image="fallbackImages[index % fallbackImages.length]"
                        :is-favourite="isFavourite(product.productId)"
                        @favourite="submitFavourite"
                    />
                </div>

                <p v-if="!filteredProducts.length" class="status-message">No styles match that selection.</p>

                <div id="favourites" class="favourites-tray">
                    <div class="section-row">
                        <h2>Favourites</h2>
                        <button v-if="favourites.length" class="soft-button" @click="submitClearFavourites">Clear</button>
                    </div>
                    <div v-if="favourites.length" class="mini-grid">
                        <a v-for="item in favourites" :key="item.productId" :href="`/products/${item.productId}`">{{ item.productName }}</a>
                    </div>
                    <p v-else class="empty-state">Favourite pieces will appear here.</p>
                </div>
            </div>
        </section>

        <SiteFooter />
    </main>
</template>