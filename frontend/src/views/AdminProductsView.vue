<script setup>
import { onMounted, reactive, ref } from 'vue'
import AdminTabs from '../components/AdminTabs.vue'
import Navbar from '../components/Navbar.vue'
import {
	createAdminProduct,
	deleteAdminProduct,
	getAdminProduct,
	getAdminProducts,
	updateAdminProduct,
} from '../api/adminApi'
import { formatMoney } from '../utils/format'

const products = ref([])
const message = ref('')
const editingId = ref(null)

function blankVariant() {
	return { variantId: 0, size: '', colour: '', stock: 0, price: 0 }
}

const productForm = reactive({
	productName: '',
	description: '',
	price: 0,
	category: '',
	stock: 0,
	image: '',
	imageFile: null,
	variants: [{ size: 'M', colour: 'Gold', stock: 5, price: 0, variantId: 0 }],
	deletedVariantIds: [],
})

async function loadProducts() {
	try {
		const response = await getAdminProducts()
		products.value = response.data?.data?.products || []
	} catch (error) {
		message.value = error.response?.data?.message || 'Could not load products.'
	}
}

async function editProduct(product) {
	try {
		const response = await getAdminProduct(product.productId)
		const details = response.data?.data || {}
		const productDetails = details.product || product
		const variants = details.variants?.length
			? details.variants.map(variant => ({
				variantId: variant.variantId || 0,
				size: variant.size || '',
				colour: variant.colour || '',
				stock: variant.stockQuantity ?? variant.stock ?? 0,
				price: productDetails.price || 0,
			}))
			: [{ size: 'M', colour: 'Gold', stock: productDetails.stock || 1, price: productDetails.price || 0, variantId: 0 }]

		editingId.value = productDetails.productId
		Object.assign(productForm, {
			productName: productDetails.productName || '',
			description: productDetails.description || '',
			price: productDetails.price || 0,
			category: productDetails.category || '',
			stock: productDetails.stock || 0,
			image: productDetails.image || '',
			imageFile: null,
			variants,
			deletedVariantIds: [],
		})
	} catch (error) {
		message.value = error.response?.data?.message || 'Could not load product details.'
	}
}

function resetProductForm() {
	editingId.value = null
	Object.assign(productForm, {
		productName: '',
		description: '',
		price: 0,
		category: '',
		stock: 0,
		image: '',
		imageFile: null,
		variants: [{ size: 'M', colour: 'Gold', stock: 5, price: 0, variantId: 0 }],
		deletedVariantIds: [],
	})
}

function setProductImage(event) {
	productForm.imageFile = event.target.files?.[0] || null
	if (productForm.imageFile) {
		productForm.image = URL.createObjectURL(productForm.imageFile)
	}
}

function addVariant() {
	productForm.variants.push(blankVariant())
}

function removeVariant(index) {
	const [variant] = productForm.variants.splice(index, 1)
	if (editingId.value && variant?.variantId) {
		productForm.deletedVariantIds.push(variant.variantId)
	}

	if (!productForm.variants.length) {
		productForm.variants.push(blankVariant())
	}
}

function buildCreateProductData() {
	const formData = buildProductFields()
	productForm.variants.forEach((variant, index) => {
		formData.append(`variants[${index}][size]`, variant.size)
		formData.append(`variants[${index}][colour]`, variant.colour)
		formData.append(`variants[${index}][stock]`, variant.stock)
		formData.append(`variants[${index}][price]`, variant.price || productForm.price || 0)
	})
	return formData
}

function buildUpdateProductData() {
	const formData = buildProductFields()
	productForm.variants.forEach(variant => {
		formData.append('variantId[]', variant.variantId || 0)
		formData.append('variantSize[]', variant.size)
		formData.append('variantColour[]', variant.colour)
		formData.append('variantStock[]', variant.stock)
		formData.append('variantPrice[]', variant.price || productForm.price || 0)
	})
	productForm.deletedVariantIds.forEach(id => formData.append('variantDeleteIds[]', id))
	return formData
}

function buildProductFields() {
	const formData = new FormData()
	formData.append('productName', productForm.productName)
	formData.append('description', productForm.description)
	formData.append('price', productForm.price)
	formData.append('category', productForm.category)
	formData.append('stock', productForm.stock)
	if (productForm.imageFile) {
		formData.append('image', productForm.imageFile)
	}
	return formData
}

async function submitProduct() {
	try {
		if (editingId.value) {
			await updateAdminProduct(editingId.value, buildUpdateProductData())
			message.value = 'Product updated.'
		} else {
			await createAdminProduct(buildCreateProductData())
			message.value = 'Product created.'
		}
		resetProductForm()
		await loadProducts()
	} catch (error) {
		message.value = error.response?.data?.message || 'Product save failed.'
	}
}

async function removeProduct(id) {
	await deleteAdminProduct(id)
	await loadProducts()
}

onMounted(loadProducts)
</script>

<template>
	<main class="figma-page admin-page">
		<Navbar />
		<section class="admin-hero-panel">
			<p class="section-kicker">Catalogue</p>
			<h1>Products Management</h1>
			<p>Create pieces, upload imagery, and manage every product variant in one focused workspace.</p>
		</section>
		<AdminTabs />
		<p v-if="message" class="admin-message">{{ message }}</p>

		<section class="admin-workspace-grid">
			<form class="admin-card panel-form product-editor-card" @submit.prevent="submitProduct">
				<h2>{{ editingId ? 'Edit product' : 'Add product' }}</h2>
				<label>Name<input v-model="productForm.productName" required></label>
				<label>Description<textarea v-model="productForm.description"></textarea></label>
				<div class="two-col"><label>Price<input v-model.number="productForm.price" type="number" step="0.01"></label><label>Stock<input v-model.number="productForm.stock" type="number"></label></div>
				<label>Category<input v-model="productForm.category"></label>
				<label>Product image<input type="file" accept="image/jpeg,image/png,image/webp" @change="setProductImage"></label>
				<img v-if="productForm.image" class="product-form-preview" :src="productForm.image" :alt="productForm.productName || 'Product image'">
				<div class="variant-editor">
					<div class="section-row">
						<h3>Variants</h3>
						<button type="button" class="soft-button" @click="addVariant">Add variant</button>
					</div>
					<div v-for="(variant, index) in productForm.variants" :key="variant.variantId || index" class="variant-row">
						<label>Size<input v-model="variant.size" required></label>
						<label>Colour<input v-model="variant.colour" required></label>
						<label>Stock<input v-model.number="variant.stock" type="number" min="0"></label>
						<button type="button" class="text-danger" @click="removeVariant(index)">Remove</button>
					</div>
				</div>
				<button class="figma-button figma-button-primary">{{ editingId ? 'Save product' : 'Create product' }}</button>
				<button v-if="editingId" type="button" class="soft-button" @click="resetProductForm">Cancel edit</button>
			</form>

			<div class="admin-card admin-list-card">
				<div class="section-row"><h2>Products</h2><span class="admin-count-chip">{{ products.length }} products</span></div>
				<div class="data-table admin-product-table">
					<div class="table-row table-head"><span>Name</span><span>Category</span><span>Price</span><span>Actions</span></div>
					<div v-for="product in products" :key="product.productId" class="table-row">
						<span>{{ product.productName }}</span>
						<span>{{ product.category }}</span>
						<span>{{ formatMoney(product.price) }}</span>
						<span class="row-actions"><button @click="editProduct(product)">Edit</button><button class="text-danger" @click="removeProduct(product.productId)">Delete</button></span>
					</div>
				</div>
			</div>
		</section>
	</main>
</template>
